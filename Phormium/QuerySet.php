<?php

namespace Phormium;

/**
 * Performs lazy database lookup for sets of objects.
 */
class QuerySet
{
    /**
     * Holds the model which this QuerySet is handling.
     * @var Model
     */
    private $model;

    /**
     * The SQL query for fetching data.
     * Constructed by {@link constructQueries}.
     */
    private $selectQuery;

    /**
     * The SQL query for counting records.
     * Constructed by {@link constructQueries}.
     */
    private $countQuery;

    /**
     * Arguments for executing the prepared statemnt with ? placeholders.
     */
    private $args;

    /**
     * Order by clauses.
     */
    private $order = array();

    /**
     *
     */
    private $filters = array();

    public function __construct(Model $model) {
        $this->model = $model;
    }

    // ******************************************
    // *** Public methods                     ***
    // ******************************************

    /**
     * Returns a new QuerySet that is a copy of the current one.
     * @return QuerySet
     */
    public function all()
    {
        return clone $this;
    }

    /**
     * Returns a new query set with the filter AND-ed to the existing one.
     * @return QuerySet
     */
    public function filter(Filter $filter)
    {
        $qs = clone $this;
        $qs->addFilter($filter);
        return $qs;
    }

    /**
     * Returns a new QuerySet instance with the ordering changed.
     *
     * @param string $column Name of the column to order by.
     * @param string $direction Direction to sort by: 'asc' (default)
     *      or 'desc'. Optional.
     */
    public function orderBy($column, $direction = 'asc')
    {
        $qs = clone $this;
        $qs->addOrder($column, $direction);
        return $qs;
    }

    /**
     * Performs a SELECT COUNT() and returns the number of records matching
     * the current filter.
     *
     * @return integer
     */
    public function count()
    {
        $this->constructQueries();

        $conn = DB::getConnection($this->model->connection);
        $data = $conn->execute($this->countQuery, $this->args, DB::FETCH_ARRAY);
        return (integer) $data[0]['count'];
    }

    /**
     * Performs a SELECT query on the table, and returns rows matching the
     * current filter.
     */
    public function fetch($type = DB::FETCH_OBJECT)
    {
        $this->constructQueries();
        $conn = DB::getConnection($this->model->connection);
        return $conn->execute($this->selectQuery, $this->args, $type, $this->model->class);
    }

    public function single($allowEmpty = false)
    {
        $data = $this->fetch();
        $count = count($data);

        if($count > 1) {
            throw new \Exception("Query returned multiple rows ($count). Requested a single row.");
        }

        if(!$allowEmpty && $count == 0) {
            throw new \Exception("Query returned 0 rows. Requested a single row.");
        }

        return isset($data[0]) ? $data[0] : null;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    // ******************************************
    // *** Private methods                    ***
    // ******************************************

    private function addFilter(Filter $filter)
    {
        $column = $filter->column;
        if (isset($filter->column) && !isset($this->model->columns[$column])) {
            $table = $this->model->table;
            throw new \Exception("Invalid filter: Column [$column] does not exist in table [$table].");
        }
        $this->filters[] = $filter;
    }

    public function addOrder($column, $direction)
    {
        if ($direction !== 'asc' && $direction !== 'desc') {
            throw new \Exception("Invalid direction given: [$direction]. Expected 'asc' or 'desc'.");
        }

        if (!isset($this->model->columns[$column])) {
            $table = $this->model->table;
            throw new \Exception("Cannot order by column [$column] because it does not exist in table [$table].");
        }

        $this->order[] = "{$column} {$direction}";
    }

    private function constructQueries()
    {
        $columns = implode(", ", array_keys($this->model->columns));
        $table = $this->model->table;

        list($where, $args) = $this->constructWhere();
        $order = $this->constructOrder();

        $this->selectQuery = "SELECT {$columns} FROM {$table}{$where}{$order};";
        $this->countQuery = "SELECT count(*) AS count FROM {$table}{$where};";
        $this->args = $args;
    }

    /** Constructs an ORDER BY clause based on data in $this->order. */
    private function constructOrder()
    {
        if (empty($this->order)) {
            return "";
        }
        return " ORDER BY " . implode(', ', $this->order);
    }

    /** Constructs a WHERE clause based on data in $this->filters. */
    private function constructWhere()
    {
        if (empty($this->filters)) {
            return array("", array());
        }

        // Accumulate the where clauses and arguments from each filter
        $where = array();
        $args = array();
        foreach($this->filters as $filter) {
            list($w, $a) = $filter->render($this->model);
            $where[] = $w;
            $args = array_merge($args, $a);
        }
        $where = " WHERE " . implode(" AND ", $where);
        return array($where, $args);
    }
}
