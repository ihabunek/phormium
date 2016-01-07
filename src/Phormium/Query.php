<?php

namespace Phormium;

use Phormium\Filter\Filter;

use PDO;

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
     * Constructs and executes a SELECT query based on the given parameters.
     * Returns an array of data fetched from the database.
     *
     * @param Filter $filter A filter instance used to form the WHERE clause.
     * @param array $order Array of [<column>, <direction>] pairs used to form
     *      the ORDER BY clause.
     * @param array $columns Array of columns to fetch, or NULL for all columns.
     * @param integer $limit The max. number of rows to fetch.
     * @param integer $offset The number of rows to skip from beginning.
     * @param integer $fetchType Fetch type; one of PDO::FETCH_* constants.
     *
     * @return array An array of {@link Model} instances when using
     *      PDO::FETCH_CLASS, an array of associative arrays when using
     *      PDO::FETCH_ASSOC.
     */
    public function select(
        Filter $filter = null,
        array $order = null,
        array $columns = null,
        $limit = null,
        $offset = null,
        $fetchType = PDO::FETCH_CLASS
    ) {
        if (isset($columns)) {
            $this->checkColumnsExist($columns);
        } else {
            $columns = $this->meta->columns;
        }

        foreach($columns as $key => $col) {
            $columns[$key] = '`'.$col.'`';
        }

        $columns = implode(", ", $columns);
        $table = $this->meta->table;
        $class = $this->meta->class;

        list($limit1, $limit2) = $this->constructLimitOffset($limit, $offset);
        list($where, $args) = $this->constructWhere($filter);
        $order = $this->constructOrder($order);

        $query = "SELECT{$limit1} {$columns} FROM {$table}{$where}{$order}{$limit2};";

        $conn = DB::getConnection($this->meta->database);

        try {
            $result = $conn->preparedQuery($query, $args, $fetchType, $class);
            return $result;
        }
        catch(\PDOException $e)
        {
            throw(new \PDOException($e->getMessage(), $e->getCode()));
        }
    }

    /**
     * Constructs and executes a SELECT DISTINCT query.
     *
     * @param Filter $filter A filter instance used to form the WHERE clause.
     * @param array $order Array of strings used to form the ORDER BY clause.
     *
     * @return array An array distinct values. If multiple columns are given,
     *      will return an array of arrays, and each of these will have
     *      the distinct values indexed by column name. If a single column is
     *      given will return an array of distinct values for that column.
     */
    public function selectDistinct($filter, $order, array $columns)
    {
        $table = $this->meta->table;

        if (empty($columns)) {
            throw new \Exception("No columns given");
        }

        $this->checkColumnsExist($columns);

        $sqlColumns = implode(', ', $columns);

        list($where, $args) = $this->constructWhere($filter);
        $order = $this->constructOrder($order);

        $query = "SELECT DISTINCT {$sqlColumns} FROM {$table}{$where}{$order};";

        if (count($columns) > 1) {
            // If multiple columns, return array of arrays
            $conn = DB::getConnection($this->meta->database);
            return $conn->preparedQuery($query, $args);
        } else {
            // If single column, return array of strings
            $column = reset($columns);
            return $this->singleColumnQuery($query, $args, $column);
        }
    }

    /**
     * Constructs and executes a SELECT aggregate query.
     *
     * @param  Filter    $filter     A filter instance used to form the WHERE clause.
     * @param  Aggregate $aggregate  The aggregate to perform.
     * @return string                Result of the aggregate query.
     */
    public function aggregate($filter, Aggregate $aggregate)
    {
        $table = $this->meta->table;
        $column = $aggregate->column;
        $type = $aggregate->type;

        if (!in_array($column, $this->meta->columns)) {
            if (!($type === Aggregate::COUNT && $column === '*')) {
                throw new \Exception(
                    "Error forming aggregate query. " .
                    "Column [$column] does not exist in table [$table]."
                );
            }
        }

        list($where, $args) = $this->constructWhere($filter);
        $select = $aggregate->render();

        $query = "SELECT {$select} as aggregate FROM {$table}{$where};";

        $conn = DB::getConnection($this->meta->database);
        $data = $conn->preparedQuery($query, $args);
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

        // PostgreSQL needs a RETURNING clause to get the inserted ID because
        // it does not support PDO->lastInsertId().
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

        $conn = DB::getConnection($meta->database);

        // If primary key is generated by the database, populate it
        if ($pkAutogen) {
            // For Postgres, do fetch to retrieve the generated primary key via
            // the RETURNING clause. For others use PDO->lastInsertId().
            if ($this->driver == 'pgsql') {
                $data = $conn->preparedQuery($query, $args);
                $id = $data[0][$pkColumn];
            } else {
                $conn->preparedExecute($query, $args);
                $id = $conn->getPDO()->lastInsertId();
            }
            $model->{$pkColumn} = $id;
        } else {
            $conn->preparedExecute($query, $args);
        }
    }

    /**
     * Constructs and executes an UPDATE statement for a single Model instance.
     */
    public function update(Model $model)
    {
        $meta = $this->meta;

        if (!isset($meta->pk)) {
            throw new \Exception("Cannot update. Model does not have a primary key defined in _meta.");
        }

        // All pk fields must be set to attempt an update
        foreach ($meta->pk as $column) {
            if (!isset($model->{$column})) {
                throw new \Exception("Cannot update. Primary key column [$column] is not set.");
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
        return $conn->preparedExecute($query, $args);
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
                throw new \Exception("Cannot delete. Primary key column [$column] is not set.");
            }

            $where[] = "{$column} = ?";
            $args[] = $value;
        }
        $where = implode(' AND ', $where);
        $query = "DELETE FROM {$this->meta->table} WHERE {$where}";

        // Run the query
        $conn = DB::getConnection($this->meta->database);
        return $conn->preparedExecute($query, $args);
    }

    /**
     * Constructs and executes an UPDATE statement for all records matching
     * the given filters.
     */
    public function batchUpdate($filter, $updates)
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
        list($where, $args) = $this->constructWhere($filter);
        $args = array_merge(array_values($updates), $args);

        $query  = "UPDATE {$this->meta->table} ";
        $query .= "SET " . implode(', ', $updateBits);
        $query .= $where;

        // Run the query
        $conn = DB::getConnection($this->meta->database);
        return $conn->preparedExecute($query, $args);
    }

    /**
     * Constructs and executes a DELETE statement for all records matching
     * the given filters.
     */
    public function batchDelete($filter)
    {
        // Construct the query
        list($where, $args) = $this->constructWhere($filter);
        $query = "DELETE FROM {$this->meta->table}{$where}";

        // Run the query
        $conn = DB::getConnection($this->meta->database);
        return $conn->preparedExecute($query, $args);
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
        foreach ($columns as $column) {
            if (!in_array($column, $this->meta->columns)) {
                $table = $this->meta->table;
                throw new \Exception("Column [$column] does not exist in table [$table].");
            }
        }
    }

    /** Performs a prepared query and returns only a single column. */
    private function singleColumnQuery($query, $args, $column)
    {
        $conn = DB::getConnection($this->meta->database);
        $pdo = $conn->getPDO();

        $stmt = $pdo->prepare($query);
        $stmt->execute($args);

        $data = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row[$column];
        }

        return $data;
    }

    /** Constructs a WHERE clause for a given filter. */
    private function constructWhere(Filter $filter = null)
    {
        if ($filter === null) {
            return array("", array());
        }

        list($where, $args) = $filter->render();

        if (empty($where)) {
            return array("", array());
        }

        $where = " WHERE $where";
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

    /** Constructs the LIMIT/OFFSET clause. */
    private function constructLimitOffset($limit, $offset)
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

    /** Parses the DSN and extracts the driver name. */
    private function getDriver($dns)
    {
        $count = preg_match('/^([a-z]+):/', $dns, $matches);

        if ($count !== 1) {
            throw new \Exception("DNS should start with '<driver>:'");
        }

        return $matches[1];
    }
}
