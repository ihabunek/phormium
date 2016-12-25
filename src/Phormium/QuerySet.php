<?php

namespace Phormium;

use PDO;
use Phormium\Exception\InvalidQueryException;
use Phormium\Exception\OrmException;
use Phormium\Filter\ColumnFilter;
use Phormium\Filter\CompositeFilter;
use Phormium\Filter\Filter;
use Phormium\Filter\RawFilter;
use Phormium\Meta;
use Phormium\Query;
use Phormium\Query\Aggregate;
use Phormium\Query\ColumnOrder;
use Phormium\Query\LimitOffset;
use Phormium\Query\OrderBy;

/**
 * Performs lazy database lookup for sets of objects.
 */
class QuerySet implements \IteratorAggregate
{
    /**
     * Meta data of the Model this QuerySet is handling.
     *
     * @var Meta
     */
    private $meta;

    /**
     * The Query object used for constructing queries.
     *
     * @var Query
     */
    private $query;

    /**
     * Order by clauses.
     *
     * @var OrderBy
     */
    private $order;

    /**
     * The root filter.
     *
     * @var Filter
     */
    private $filter;

    /**
     * Fetching limitations.
     *
     * @var LimitOffset
     */
    private $limitOffset;

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
    public function filter(...$args)
    {
        $filter = Filter::factory(...$args);

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
        $qs = clone $this;
        $qs->limitOffset = new LimitOffset($limit, $offset);
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
        return (integer) $this->query->aggregate($agg, $this->filter);
    }

    /**
     * Returns the AVG aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function avg($column)
    {
        $agg = new Aggregate(Aggregate::AVERAGE, $column);
        return $this->query->aggregate($agg, $this->filter);
    }

    /**
     * Returns the MAX aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function max($column)
    {
        $agg = new Aggregate(Aggregate::MAX, $column);
        return $this->query->aggregate($agg, $this->filter);
    }

    /**
     * Returns the MIN aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function min($column)
    {
        $agg = new Aggregate(Aggregate::MIN, $column);
        return $this->query->aggregate($agg, $this->filter);
    }

    /**
     * Returns the SUM aggregate on the given column, using the current filters.
     * @param string $column
     */
    public function sum($column)
    {
        $agg = new Aggregate(Aggregate::SUM, $column);
        return $this->query->aggregate($agg, $this->filter);
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
            $this->limitOffset,
            PDO::FETCH_CLASS
        );
    }

    /**
     * Similar to `fetch()` but returns a generator instead of all the fetched
     * data as an array.
     */
    public function fetchLazy()
    {
        return $this->query->select(
            $this->filter,
            $this->order,
            null,
            $this->limitOffset,
            PDO::FETCH_CLASS,
            true
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
     * @throws OrmException If multiple rows are found.
     * @throws OrmException If no rows are found and $allowEmpty is FALSE.
     *
     * @return Model
     */
    public function single($allowEmpty = false)
    {
        $data = $this->fetch();
        $count = count($data);

        if ($count > 1) {
            throw new OrmException("Query returned $count rows. Requested a single row.");
        }

        if ($count == 0 && !$allowEmpty) {
            throw new OrmException("Query returned 0 rows. Requested a single row.");
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
    public function values(...$columns)
    {
        if (empty($columns)) {
            $columns = null;
        }

        return $this->query->select(
            $this->filter,
            $this->order,
            $columns,
            $this->limitOffset,
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
    public function valuesList(...$columns)
    {
        if (empty($columns)) {
            $columns = null;
        }

        return $this->query->select(
            $this->filter,
            $this->order,
            $columns,
            $this->limitOffset,
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
            $this->limitOffset,
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
    public function distinct(...$columns)
    {
        return $this->query->selectDistinct(
            $this->filter,
            $this->order,
            $columns,
            $this->limitOffset
        );
    }

    /**
     * Performs an UPDATE query on all records matching the current filter.
     */
    public function update($updates)
    {
        return $this->query->batchUpdate($updates, $this->filter);
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
        $this->filter = $this->filter->withAdded($filter);
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
        $column = $filter->column();

        if (isset($column) && !$this->meta->columnExists($column)) {
            $table = $this->meta->getTable();
            throw new InvalidQueryException("Invalid filter: Column [$column] does not exist in table [$table].");
        }
    }

    private function checkCompositeFilter(CompositeFilter $filter)
    {
        foreach ($filter->filters() as $filter) {
            $this->checkFilter($filter);
        }
    }

    public function addOrder($column, $direction)
    {
        if (!$this->meta->columnExists($column)) {
            $table = $this->meta->getTable();
            throw new InvalidQueryException("Cannot order by column [$column] because it does not exist in table [$table].");
        }

        $order = new ColumnOrder($column, $direction);

        $this->order = isset($this->order) ?
            $this->order->withAdded($order) : new OrderBy([$order]);
    }

    // ******************************************
    // *** Accessors                          ***
    // ******************************************

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return OrderBy
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Meta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return LimitOffset
     */
    public function getLimitOffset()
    {
        return $this->limitOffset;
    }

    // ******************************************
    // *** IteratorAggregate interface        ***
    // ******************************************

    public function getIterator()
    {
        return $this->fetchLazy();
    }
}
