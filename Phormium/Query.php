<?php

namespace Phormium;

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

    public function __construct(Meta $meta)
    {
        $this->meta = $meta;
    }

    /**
     * Constructs and executes a SELECT query.
     *
     * @param array $filters Array of {@link Filter} instances used to form
     *      the WHERE clause.
     * @param array $order Array of strings used to form the ORDER BY clause.
     * @param string $fetchType One of DB::FETCH_* constants.
     *
     * @return array An array of {@link Model} instances corresponing to given
     *      criteria.
     */
    public function select($filters, $order, $fetchType)
    {
        $columns = implode(", ", $this->meta->columns);
        $table = $this->meta->table;
        $class = $this->meta->class;

        list($where, $args) = $this->constructWhere($filters);
        $order = $this->constructOrder($order);

        $sql = "SELECT {$columns} FROM {$table}{$where}{$order};";
        $conn = DB::getConnection($this->meta->connection);
        return $conn->execute($sql, $args, $fetchType, $class);
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

        $sql = "SELECT COUNT(*) AS count FROM {$table}{$where};";
        $conn = DB::getConnection($this->meta->connection);
        $data = $conn->execute($sql, $args, DB::FETCH_ARRAY);
        return $data[0]['count'];
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

        $sql = "SELECT {$type}({$column}) as aggregate FROM {$table}{$where};";
        $conn = DB::getConnection($this->meta->connection);
        $data = $conn->execute($sql, $args, DB::FETCH_ARRAY);
        return $data[0]['aggregate'];
    }

    /**
     * Constructs and executes an INSERT statement for a single Model instance.
     */
    public function insert(Model $model)
    {
        $meta = $this->meta;

        // If PK is set, include it in query, otherwise skip for autogen to work
        if (isset($model->{$meta->pk})) {
            $columns = $meta->columns;
        } else {
            $columns = $meta->nonPK;
        }

        // Collect query arguments
        $args = array();
        foreach ($columns as $column) {
            $args[] = $model->{$column};
        }

        // Construct the query
        $query = "INSERT INTO {$meta->table} (";
        $query .= implode(', ', $columns);
        $query .= ") VALUES (";
        $query .= implode(', ', array_fill(0, count($columns), '?'));
        $query .= ");";

        $conn = DB::getConnection($meta->connection);
        $conn->executeNoFetch($query, $args);

        if (!isset($model->{$meta->pk})) {
            $model->{$meta->pk} = $conn->getLastInsertID();
        }
    }

    /**
     * Constructs and executes an UPDATE statement for a single Model instance.
     */
    public function update(Model $model)
    {
        $meta = $this->meta;

        // Just for safety
        if (empty($model->{$meta->pk})) {
            throw new \Exception("Cannot update model if primary key [$pk] is not set.");
        }

        // Collect query arguments (primary key goes last, skip it here)
        $args = array();
        $updates = array();
        foreach ($meta->nonPK as $column) {
            $updates[] = "$column = ?";
            $args[] = $model->{$column};
        }

        // Add primary key to arguments
        $args[] = $model->{$meta->pk};

        // Construct the query
        $query = "UPDATE {$meta->table} SET ";
        $query .= implode(', ', $updates);
        $query .= " WHERE {$meta->pk} = ?;";

        $conn = DB::getConnection($meta->connection);
        $conn->executeNoFetch($query, $args);
        return $conn->getLastRowCount();
    }

    // ******************************************
    // *** Private methods                    ***
    // ******************************************

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
            list($w, $a) = $filter->render($this->meta);
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
}
