<?php

namespace Phormium;

/**
 * Performs lazy database lookup for sets of objects.
 */
class QuerySet
{
    /**
     * Meta data of the Model this QuerySet is handling.
     * @var Meta
     */
    private $meta;

    /** Order by clauses. */
    private $order = array();

    private $filter;

    /** Maximum number of rows to fetch. */
    private $limit;

    /** Offset of the first row to return. */
    private $offset;

    public function __construct(Query $query, Meta $meta)
    {
        $this->meta = $meta;
        $this->query = $query;
    }

    // ******************************************
    // *** Clone methods                      ***
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
     * Returns a new query set with the given filter AND-ed to the existing
     * ones.
     *
     * Accepts either:
     *   - an instance of the Filter class
     *   - an array with two values [$column, $operation] for filters which
     *     don't require an value
     *   - an array with three values [$column, $operation, $value]
     *
     * @return QuerySet
     */
    public function filter()
    {
        $args = func_get_args();
        $count = func_num_args();

        if ($count == 1) {
            $arg = $args[0];

            if ($arg instanceof Filter) {
                $filter = $arg;
            } elseif (is_array($arg)) {
                $filter = ColumnFilter::fromArray($arg);
            } else {
                throw new \Exception("Invalid arguments given.");
            }
        } else {
            $filter = ColumnFilter::fromArray($args);
        }

        $qs = clone $this;
        $qs->addFilter($filter);
        return $qs;
    }

    /**
     * Returns a new QuerySet with the ordering changed.
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
     * Returns a new QuerySet with the limit and offset populated with given
     * values.
     */
    public function limit($limit, $offset = null)
    {
        if (!is_null($limit) && !is_int($limit) && !preg_match('/^[0-9]+$/', $limit)) {
            throw new \Exception("Limit must be an integer or null.");
        }

        if (!is_null($offset) && !is_int($offset) && !preg_match('/^[0-9]+$/', $offset)) {
            throw new \Exception("Offset must be an integer or null.");
        }

        $qs = clone $this;
        $qs->limit = $limit;
        $qs->offset = $offset;
        return $qs;
    }

    // ******************************************
    // *** Query methods                      ***
    // ******************************************

    /**
     * Performs a SELECT COUNT(*) and returns the number of records matching
     * the current filter.
     *
     * @return integer
     */
    public function count()
    {
        return $this->query->count($this->filter);
    }

    /**
     * Returns the AVG aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function avg($column)
    {
        $agg = new Aggregate(Aggregate::AVERAGE, $column);
        return $this->query->aggregate($this->filter, $agg);
    }

    /**
     * Returns the MAX aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function max($column)
    {
        $agg = new Aggregate(Aggregate::MAX, $column);
        return $this->query->aggregate($this->filter, $agg);
    }

    /**
     * Returns the MIN aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function min($column)
    {
        $agg = new Aggregate(Aggregate::MIN, $column);
        return $this->query->aggregate($this->filter, $agg);
    }

    /**
     * Returns the SUM aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function sum($column)
    {
        $agg = new Aggregate(Aggregate::SUM, $column);
        return $this->query->aggregate($this->filter, $agg);
    }

    /**
     * Fetches the count of records matching the current filter and returns
     * TRUE if it's greater than 0, or FALSE otherwise.
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->query->count($this->filter) > 0;
    }

    /**
     * Performs a SELECT query on the table, and returns rows matching the
     * current filter.
     */
    public function fetch()
    {
        return $this->query->select($this->filter, $this->order, null, $this->limit, $this->offset);
    }

    /**
     * Performs a SELECT query on the table, and returns a single row which
     * matches the current filter.
     *
     * @param $allowEmpty boolean If set to FALSE (default), will throw an
     * exception if query matches zero rows. If set to TRUE, will return null if
     * query matches zero rows.
     *
     * @throws \Exception If multiple rows are found.
     * @throws \Exception If no rows are found and $allowEmpty is FALSE.
     * @return Model
     */
    public function single($allowEmpty = false)
    {
        $data = $this->fetch();
        $count = count($data);

        if ($count > 1) {
            throw new \Exception("Query returned $count rows. Requested a single row.");
        }

        if ($count == 0 && !$allowEmpty) {
            throw new \Exception("Query returned 0 rows. Requested a single row.");
        }

        return isset($data[0]) ? $data[0] : null;
    }

    /**
     * Performs a SELECT query on the table, and returns rows matching the
     * current filter as associative arrays (instead of objects which are
     * returned by fetch().
     *
     * One or more column names can be provided as parameters, and only these
     * columns will be fetched. If no parameters are given, all columns are
     * fetched.
     */
    public function values()
    {
        $columns = func_get_args();
        if (empty($columns)) {
            $columns = null;
        }

        return $this->query->select($this->filter, $this->order, $columns, $this->limit, $this->offset, \PDO::FETCH_ASSOC);
    }

    /**
     * Performs a SELECT query on the table, and returns rows matching the
     * current filter as number-indexed arrays (instead of objects which are
     * returned by fetch().
     *
     * One or more column names can be provided as parameters, and only these
     * columns will be fetched. If no parameters are given, all columns are
     * fetched.
     */
    public function valuesList()
    {
        $columns = func_get_args();
        if (empty($columns)) {
            $columns = null;
        }

        return $this->query->select($this->filter, $this->order, $columns, $this->limit, $this->offset, \PDO::FETCH_NUM);
    }

    /**
     * Performs a SELECT query on the table, and returns the values of a single
     * column for rows which match the current filter.
     *
     * Similar to calling values() with a single column, but returns a 1D array,
     * where values() would return a 2D array.
     *
     * @param $column string The name of the column to fetch
     */
    public function valuesFlat($column)
    {
        $data = $this->query->select($this->filter, $this->order, array($column), $this->limit, $this->offset, \PDO::FETCH_NUM);

        foreach($data as &$item) {
            $item = $item[0];
        }
        unset($item);

        return $data;
    }

    /**
     * Performs a SELECT DISTINCT query on the given columns.
     */
    public function distinct()
    {
        $columns = func_get_args();
        return $this->query->selectDistinct($this->filter, $this->order, $columns);
    }

    /**
     * Performs an UPDATE query on all records matching the current filter.
     */
    public function update($updates)
    {
        return $this->query->batchUpdate($this->filter, $updates);
    }

    /**
     * DELETEs all records matching the current filter.
     */
    public function delete()
    {
        return $this->query->batchDelete($this->filter);
    }

    /**
     * Fetches the models matching the current filter and dumps them
     * to the console in a human readable format.
     *
     * @param boolean $return If set to TRUE, will return the dump as a string,
     *     otherwise, it will write it to the console (default).
     */
    public function dump($return = false)
    {
        return Printer::dump(clone($this), $return);
    }

    // ******************************************
    // *** Private methods                    ***
    // ******************************************

    /**
     * Adds a new filter to the queryset. If multiple filters are added, they
     * will be joined by an AND composite filter.
     */
    private function addFilter(Filter $filter)
    {
        // Start with an empty AND composite filter
        if (!isset($this->filter)) {
            $this->filter = Filter::_and();
        }

        $this->checkFilter($filter);
        $this->filter->add($filter);
    }

    private function checkFilter(Filter $filter)
    {
        if ($filter instanceof ColumnFilter) {
            $this->checkColumnFilter($filter);
        }

        if ($filter instanceof CompositeFilter) {
            $this->checkCompositeFilter($filter);
        }
    }

    private function checkColumnFilter(ColumnFilter $filter)
    {
        if (isset($filter->column) && !in_array($filter->column, $this->meta->columns)) {
            $table = $this->meta->table;
            throw new \Exception("Invalid filter: Column [$filter->column] does not exist in table [$table].");
        }
    }

    private function checkCompositeFilter(CompositeFilter $filter)
    {
        foreach($filter->getFilters() as $filter) {
            $this->checkFilter($filter);
        }
    }

    private function addOrder($column, $direction)
    {
        if ($direction !== 'asc' && $direction !== 'desc') {
            throw new \Exception("Invalid order direction [$direction]. Expected 'asc' or 'desc'.");
        }

        if (!in_array($column, $this->meta->columns)) {
            $table = $this->meta->table;
            throw new \Exception("Cannot order by column [$column] because it does not exist in table [$table].");
        }

        $this->order[] = "{$column} {$direction}";
    }

    // ******************************************
    // *** Accessors                          ***
    // ******************************************

    public function getFilter()
    {
        return $this->filter;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
