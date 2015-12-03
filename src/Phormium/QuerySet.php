<?php

namespace Phormium;

use PDO;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;
use Phormium\Filter\RawFilter;

/**
 * Performs lazy database lookup for sets of objects.
 */
class QuerySet
{
    /**
     * Meta data of the Model this QuerySet is handling.
     *
     * @var Phormium\Meta
     */
    private $meta;

    /**
     * The Query object used for constructing queries.
     *
     * @var Phormium\Query
     */
    private $query;

    /**
     * Order by clauses.
     */
    private $order = [];

    /**
     * The root filter.
     *
     * @var Phormium\Filter\Filter
     */
    private $filter;

    /**
     * Maximum number of rows to fetch.
     *
     * @var integer
     */
    private $limit;

    /**
     * Offset of the first row to return.
     *
     * @var integer
     */
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
     *
     * @return Phormium\QuerySet
     */
    public function all()
    {
        return clone $this;
    }

    /**
     * Returns a new query set with the given filter AND-ed to the existing
     * ones.
     *
     * @param mixed Accepts either:
     *   - an instance of the Filter class
     *   - an array with two values or three values [$column, $operation,
     *     $value] will be converted to a ColumnFilter object.
     *
     * @return Phormium\QuerySet
     */
    public function filter()
    {
        $filter = $this->parseFilterArgs(
            func_get_args(),
            func_num_args()
        );

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
     * Performs a SELECT COUNT() and returns the number of records matching
     * the current filter.
     *
     * @param string $column If given, will query COUNT($column), if not will
     *      query COUNT(*).
     *
     * @return integer
     */
    public function count($column = null)
    {
        $agg = new Aggregate(Aggregate::COUNT, $column);
        return (integer) $this->query->aggregate($this->filter, $agg);
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
        return $this->count() > 0;
    }

    /**
     * Performs a SELECT query on the table, and returns rows matching the
     * current filter.
     */
    public function fetch()
    {
        return $this->query->select(
            $this->filter,
            $this->order,
            null,
            $this->limit,
            $this->offset,
            PDO::FETCH_CLASS
        );
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

        return $this->query->select(
            $this->filter,
            $this->order,
            $columns,
            $this->limit,
            $this->offset,
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Performs a SELECT query on the table, and returns rows matching the
     * current filter as number-indqexed arrays (instead of objects which are
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

        return $this->query->select(
            $this->filter,
            $this->order,
            $columns,
            $this->limit,
            $this->offset,
            PDO::FETCH_NUM
        );
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
        $data = $this->query->select(
            $this->filter,
            $this->order,
            [$column],
            $this->limit,
            $this->offset,
            PDO::FETCH_NUM
        );

        foreach ($data as &$item) {
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

        return $this->query->selectDistinct(
            $this->filter,
            $this->order,
            $columns,
            $this->limit,
            $this->offset
        );
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
        $printer = new Printer();

        return $printer->dump(clone($this), $return);
    }

    // ******************************************
    // *** Private methods                    ***
    // ******************************************

    /**
     * Parses method arguments for `->filter()` and returns a corresponding
     * Filter object. Here are the possibilities:
     *
     * 1. One argument is given
     *
     * a) If it's a Filter object, just return it as-is.
     *    e.g. `->filter(new Filter(...))`
     *
     * b) If it's an array, use it to construct a ColumnFilter.
     *    e.g. `->filter(['foo', 'isnull'])
     *
     * c) If it's a string, use it to construct a RawFilter.
     *    e.g. `->filter('foo = lower(bar)')`
     *
     * 2. Two arguments given
     *
     * a) If both are strings, use them to construct a ColumnFilter.
     *    e.g. `->filter('foo', 'isnull')
     *
     * b) If one is string and the other an array, use it to construct a
     *    Raw filter (first is SQL filter, the second is arguments).
     *    e.g. `->filter('foo = concat(?, ?)', ['bar', 'baz'])
     *
     * 3. Three arguments given
     *
     * a) Use them to construct a ColumnFilter.
     *    e.g. `->filter('foo', '=', 'bar')
     *
     * @return Phormium\Filter\Filter.
     */
    private function parseFilterArgs($argv, $argc)
    {
        if ($argc === 1) {
            $arg = $argv[0];

            if ($arg instanceof Filter) {
                return $arg;
            } elseif (is_array($arg)) {
                return ColumnFilter::fromArray($arg);
            } elseif (is_string($arg)) {
                return new RawFilter($arg);
            }
        } elseif ($argc === 2) {
            if (is_string($argv[0])) {
                if (is_string($argv[1])) {
                    return ColumnFilter::fromArray($args);
                } elseif (is_array($argv[1])) {
                    return new RawFilter($argv[0], $argv[1]);
                }
            }
        } elseif ($argc === 3) {
            return ColumnFilter::fromArray($argv);
        }

        throw new \InvalidArgumentException("Invalid filter arguments.");
    }

    /**
     * Adds a new filter to the queryset. If multiple filters are added, they
     * will be joined by an AND composite filter.
     */
    public function addFilter(Filter $filter)
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
        if (isset($filter->column) && !in_array($filter->column, $this->meta->getColumns())) {
            $table = $this->meta->getTable();
            throw new \Exception("Invalid filter: Column [$filter->column] does not exist in table [$table].");
        }
    }

    private function checkCompositeFilter(CompositeFilter $filter)
    {
        foreach ($filter->getFilters() as $filter) {
            $this->checkFilter($filter);
        }
    }

    public function addOrder($column, $direction)
    {
        if ($direction !== 'asc' && $direction !== 'desc') {
            throw new \Exception("Invalid order direction [$direction]. Expected 'asc' or 'desc'.");
        }

        if (!in_array($column, $this->meta->getColumns())) {
            $table = $this->meta->getTable();
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


    // ******************************************
    // *** Cloning                            ***
    // ******************************************

    /**
     * When cloning a QuerySet, also clone the root Filter.
     *
     * The Query and Meta objects can stay the same, they do not change.
     */
    public function __clone()
    {
        if (isset($this->filter)) {
            $this->filter = clone $this->filter;
        }
    }
}
