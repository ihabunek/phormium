<?php

namespace Phormium;

use \PDO;

/**
 * Generates and executes SQL queries.
 */
class Query
{
    /**
     * Meta data of the model which the Query will handle.
     * @var \Phormium\Meta
     */
    private $meta;

    /** The database driver used. Used to tailor custom queries when needed.*/
    private $driver;

    public function __construct(Meta $meta)
    {
        $database = Config::getDatabase($meta->database);
        $this->driver = $this->getDriver($database['dsn']);
        $this->meta = $meta;
    }

    /**
     * Constructs and executes a SELECT query.
     *
     * @param array $filters Array of {@link Filter} instances used to form
     *      the WHERE clause.
     * @param array $order Array of strings used to form the ORDER BY clause.
     *
     * @return array An array of {@link Model} instances corresponing to given
     *      criteria.
     */
    public function select($filters, $order, array $columns = null, $limit = null, $offset = null, $fetchType = PDO::FETCH_CLASS)
    {
        if (isset($columns)) {
            $this->checkColumnsExist($columns);
        } else {
            $columns = $this->meta->columns;
        }

        $columns = implode(", ", $columns);
        $table = $this->meta->table;
        $class = $this->meta->class;

        list($limit1, $limit2) = $this->renderLimitOffset($limit, $offset);
        list($where, $args) = $this->constructWhere($filters);
        $order = $this->constructOrder($order);

        $query = "SELECT{$limit1} {$columns} FROM {$table}{$where}{$order}{$limit2};";
        $conn = DB::getConnection($this->meta->database);

        $stmt = $this->prepare($conn, $query, $fetchType, $class);
        $this->execute($stmt, $args);
        return $this->fetchAll($stmt, $fetchType);
    }

    /**
     * Constructs and executes a SELECT DISTINCT query.
     *
     * @param array $filters Array of {@link Filter} instances used to form
     *      the WHERE clause.
     * @param array $order Array of strings used to form the ORDER BY clause.
     *
     * @return array An array distinct values. If multiple columns are given,
	 *      will return an array of arrays, and each of these will have
	 *      the distinct values indexed by column name. If a single column is
	 *      given will return an array of distinct values for that column.
     */
    public function selectDistinct($filters, $order, array $columns)
    {
        $table = $this->meta->table;
        $fetchType = PDO::FETCH_ASSOC;
        if (empty($columns)) {
            throw new \Exception("No columns given");
        }

        $this->checkColumnsExist($columns);

        $sqlColumns = implode(', ', $columns);

        list($where, $args) = $this->constructWhere($filters);
        $order = $this->constructOrder($order);

        $query = "SELECT DISTINCT {$sqlColumns} FROM {$table}{$where}{$order};";
        $conn = DB::getConnection($this->meta->database);

        $stmt = $this->prepare($conn, $query, $fetchType);
        $this->execute($stmt, $args);

        // If multiple columns, return array of arrays
        if (count($columns) > 1) {
            return $this->fetchAll($stmt, $fetchType);
        }

        // If it's a single column then return a single array of values
        $column = reset($columns);
        $data = array();
        while ($row = $stmt->fetch($fetchType)) {
            $data[] = $row[$column];
        }
        return $data;
    }

    /**
     * Constructs and executes a SELECT COUNT(*) query.
     *
     * @param array $filters Array of {@link Filter} instances used to form
     *      the WHERE clause.
     *
     * @return integer Number of records which match the given filter.
     */
    public function count($filters)
    {
        $table = $this->meta->table;
        list($where, $args) = $this->constructWhere($filters);

        $query = "SELECT COUNT(*) AS count FROM {$table}{$where};";
        $conn = DB::getConnection($this->meta->database);

        $fetchType = PDO::FETCH_ASSOC;

        $stmt = $this->prepare($conn, $query, $fetchType);
        $this->execute($stmt, $args);
        $data = $this->fetchAll($stmt, $fetchType);
        return (integer) $data[0]['count'];
    }

    /**
     * Constructs and executes a SELECT aggregate query.
     *
     * @param array $filters Array of {@link Filter} instances used to form
     *      the WHERE clause.
     * @param Aggregate $aggregate The aggregate to perform.
     * @return string Result of the aggregate query.
     */
    public function aggregate($filters, $aggregate)
    {
        $table = $this->meta->table;
        $type = $aggregate->type;

        $column = $aggregate->column;
        if (!in_array($column, $this->meta->columns)) {
            throw new \Exception("Error forming aggregate query. Column [$column] does not exist in table [$table].");
        }

        list($where, $args) = $this->constructWhere($filters);
        $select = $aggregate->render();

        $query = "SELECT {$select} as aggregate FROM {$table}{$where};";
        $conn = DB::getConnection($this->meta->database);

        $fetchType = PDO::FETCH_ASSOC;

        $stmt = $this->prepare($conn, $query, $fetchType);
        $this->execute($stmt, $args);
        $data = $this->fetchAll($stmt, $fetchType);
        return $data[0]['aggregate'];
    }

    /**
     * Constructs and executes an INSERT statement for a single Model instance.
     */
    public function insert(Model $model)
    {
        $meta = $this->meta;

        // Determine whether to allow PK to be generated by the database. Conditions:
        // 1. Only single-column primary keys
        //    TODO: consider if this should be considered for composite keys
        // 2. The pk column must not be populated
        $pkAutogen = false;
        if (count($meta->pk) === 1) {
            $pkColumn = $meta->pk[0];
            if (!isset($model->{$pkColumn})) {
                $pkAutogen = true;
            }
        }

        // If PK is auto-generated, exclude it from the insert query
        $columns = $pkAutogen ? $meta->nonPK : $meta->columns;

        // If PK is not auto-generated, make sure all PK columns are populated
        // TODO: Maybe this is not necessary?
        if (!$pkAutogen) {
            foreach ($meta->pk as $column) {
                if (!isset($model->{$column})) {
                    throw new \Exception("Cannot insert. Primary key column(s) not set.");
                }
            }
        }

        // Collect query arguments
        $args = array();
        foreach ($columns as $column) {
            $args[] = $model->{$column};
        }

        // PostgreSQL needs a RETURNING clause to get the inserted ID
        $returning = "";
        if ($this->driver == 'pgsql' && $pkAutogen) {
            $pkColumn = $meta->pk[0];
            $returning = " RETURNING $pkColumn";
        }

        // Construct the query
        $query = "INSERT INTO {$meta->table} (";
        $query .= implode(', ', $columns);
        $query .= ") VALUES (";
        $query .= implode(', ', array_fill(0, count($columns), '?'));
        $query .= "){$returning};";

        // Run query
        $conn = DB::getConnection($meta->database);
        $stmt = $this->prepare($conn, $query);
        $this->execute($stmt, $args);

        // If PK is auto-generated, populate it
        if ($pkAutogen) {
            $pkColumn = $meta->pk[0];
            if ($this->driver == 'pgsql') {
                $data = $this->fetchAll($stmt, PDO::FETCH_ASSOC);
                $id = $data[0][$pkColumn];
            } else {
                $id = $conn->lastInsertId();
            }

            $model->{$pkColumn} = $id;
        }
    }

    /**
     * Constructs and executes an UPDATE statement for a single Model instance.
     */
    public function update(Model $model)
    {
        $meta = $this->meta;

        if (!isset($meta->pk)) {
            throw new \Exception("Cannot update, model does not have a primary key defined in _meta.");
        }

        // All pk fields must be set to attempt an update
        foreach ($meta->pk as $column) {
            if (!isset($model->{$column})) {
                throw new \Exception("Cannot update model because primary key column [$pk] is not set.");
            }
        }

        // Collect query arguments (primary key goes last, skip it here)
        $args = array();
        $updates = array();
        foreach ($meta->nonPK as $column) {
            $updates[] = "$column = ?";
            $args[] = $model->{$column};
        }

        // Add primary key to where and arguments
        $where = array();
        foreach ($meta->pk as $column) {
            $where[] = "{$column} = ?";
            $args[] = $model->$column;
        }

        // Construct the query
        $query  = "UPDATE {$meta->table} SET ";
        $query .= implode(', ', $updates);
        $query .= " WHERE ";
        $query .= implode(' AND ', $where);

        // Run the query
        $conn = DB::getConnection($meta->database);
        $stmt = $this->prepare($conn, $query);
        $this->execute($stmt, $args);
        return $stmt->rowCount();
    }

    /**
     * Deletes a single model from the database.
     */
    public function delete(Model $model)
    {
        $pk = $model->getPK();

        // Construct where clause based on primary key
        $args = array();
        $where = array();
        foreach ($pk as $column => $value) {

            // All PK fields must be set
            if (!isset($value)) {
                throw new \Exception("Cannot delete. Primary key column [$key] is not set.");
            }

            $where[] = "{$column} = ?";
            $args[] = $value;
        }
        $where = implode(' AND ', $where);
        $query = "DELETE FROM {$this->meta->table} WHERE {$where}";

        // Run the query
        $conn = DB::getConnection($this->meta->database);
        $stmt = $this->prepare($conn, $query);
        $this->execute($stmt, $args);
        return $stmt->rowCount();
    }

    /**
     * Constructs and executes an UPDATE statement for all records matching
     * the given filters.
     */
    public function batchUpdate($filters, $updates)
    {
        // Check columns exist
        $updateBits = array();
        foreach ($updates as $column => $value) {
            if (!in_array($column, $this->meta->columns)) {
                throw new \Exception("Column [$column] does not exist in table [{$this->meta->table}].");
            }

            $updateBits[] = "{$column} = ?";
        }

        // Construct the query
        list($where, $args) = $this->constructWhere($filters);
        $args = array_merge(array_values($updates), $args);

        $query  = "UPDATE {$this->meta->table} ";
        $query .= "SET " . implode(', ', $updateBits);
        $query .= $where;

        // Run the query
        $conn = DB::getConnection($this->meta->database);
        $stmt = $this->prepare($conn, $query);
        $this->execute($stmt, $args);
        return $stmt->rowCount();
    }

    /**
     * Constructs and executes a DELETE statement for all records matching
     * the given filters.
     */
    public function batchDelete($filters)
    {
        list($where, $args) = $this->constructWhere($filters);
        $query = "DELETE FROM {$this->meta->table}{$where}";

        // Run the query
        $conn = DB::getConnection($this->meta->database);
        $stmt = $this->prepare($conn, $query);
        $this->execute($stmt, $args);
        return $stmt->rowCount();
    }

    // ******************************************
    // *** Private methods                    ***
    // ******************************************

    /**
     * Checks that each of the columns in $columns exists in the uderlying
     * model.
     */
    private function checkColumnsExist(array $columns)
    {
        foreach($columns as $column) {
            if (!in_array($column, $this->meta->columns)) {
                throw new \Exception("Column [$column] does not exist in table [$table].");
            }
        }
    }

    /** Constructs a WHERE clause for given filters. */
    private function constructWhere($filters)
    {
        if (empty($filters)) {
            return array("", array());
        }

        // Accumulate the where clauses and arguments from each filter
        $where = array();
        $args = array();
        foreach ($filters as $filter) {
            list($w, $a) = $filter->render();
            $where[] = $w;
            $args = array_merge($args, $a);
        }
        $where = " WHERE " . implode(" AND ", $where);
        return array($where, $args);
    }

    /** Constructs an ORDER BY clause. */
    private function constructOrder($order)
    {
        if (empty($order)) {
            return "";
        }
        return " ORDER BY " . implode(', ', $order);
    }

    private function prepare(PDO $conn, $query, $fetchType = null, $class = null)
    {
        if (Config::isLoggingEnabled()) {
            echo date('Y-m-d H:i:s') . " Preparing query: $query\n";
        }
        $stmt = $conn->prepare($query);
        if ($fetchType === PDO::FETCH_CLASS) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        }
        return $stmt;
    }

    private function execute($stmt, $args)
    {
        $this->logExecute($args);
        $stmt->execute($args);

        if (Config::isLoggingEnabled()) {
            $rc = $stmt->rowCount();
            echo date('Y-m-d H:i:s') . " Finished execution. Row count: $rc.\n";
        }
    }

    private function logExecute($args)
    {
        if (!Config::isLoggingEnabled()) {
            return;
        }

        foreach($args as &$arg) {
            if ($arg === null) {
                $arg = "NULL";
            } elseif (is_string($arg)) {
                $arg = '"' . $arg . '"';
            }
        }

        echo date('Y-m-d H:i:s') . " ";
        if(empty($args)) {
            echo "Executing query with no args.";
        } else {
            echo "Executing query with args: ";
            echo implode(', ', $args);
        }
        echo "\n";
    }

    private function fetchAll($stmt, $fetchType)
    {
        if (Config::isLoggingEnabled()) {
            echo date('Y-m-d H:i:s') . " Fetching data...";
        }
        $data = array();
        while ($row = $stmt->fetch($fetchType)) {
            $data[] = $row;
        }
        return $data;
    }

    private function renderLimitOffset($limit, $offset)
    {
        // Checks
        if (isset($offset) && !is_numeric($offset)) {
            throw new \InvalidArgumentException("Invalid offset given [$offset].");
        }
        if (isset($limit) && !is_numeric($limit)) {
            throw new \InvalidArgumentException("Invalid limit given [$limit].");
        }

        // Offset should not be set without a limit
        if (isset($offset) && !isset($limit)) {
            throw new \InvalidArgumentException("Offset given without a limit.");
        }

        $limit1 = ""; // Inserted after SELECT (for informix)
        $limit2 = ""; // Inserted at end of query (for others)

        // Construct the query part (database dependant)
        switch($this->driver)
        {
            case "informix":
                if (isset($offset)) {
                    $limit1 .= " SKIP $offset";
                }
                if (isset($limit)) {
                    $limit1 .= " LIMIT $limit";
                }
                break;

            // Compatible with mysql, pgsql, sqlite, and possibly others
            default:
                if (isset($limit)) {
                    $limit2 .= " LIMIT $limit";
                }
                if (isset($offset)) {
                    $limit2 .= " OFFSET $offset";
                }
                break;
        }

        return array($limit1, $limit2);
    }

    private function getDriver($dns)
    {
        $count = preg_match('/^([a-z]+):/', $dns, $matches);

        if ($count !== 1) {
            throw new \Exception("DNS should start with '<driver>:'");
        }

        return $matches[1];
    }
}
