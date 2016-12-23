<?php

namespace Phormium\QueryBuilder\Common;

use Phormium\Filter\Filter;
use Phormium\Query\Aggregate;
use Phormium\Query\ColumnOrder;
use Phormium\Query\LimitOffset;
use Phormium\Query\OrderBy;
use Phormium\Query\QuerySegment;
use Phormium\QueryBuilder\QueryBuilderInterface;

class QueryBuilder implements QueryBuilderInterface
{
    /**
     * @var Quoter
     */
    protected $quoter;

    /**
     * @var FilterRenderer
     */
    protected $filterRenderer;

    public function __construct(Quoter $quoter, FilterRenderer $filterRenderer)
    {
        $this->quoter = $quoter;
        $this->quoteFn = [$quoter, "quote"];

        $this->filterRenderer = $filterRenderer;
    }

    /**
     * Builds a SELECT query.
     *
     * @param  string           $table       Table name.
     * @param  string[]         $columns     Array of column names.
     * @param  Filter|null      $filter      Expression to filter by.
     * @param  LimitOffset|null $limitOffset Fetch limit and offset.
     * @param  OrderBy|null     $orderBy     Expression to order by.
     * @param  boolean          $distinct    Whether to produce a SELECT DISTINCT query.
     *
     * @return string The SQL query.
     */
    public function buildSelect(
        $table,
        array $columns,
        Filter $filter = null,
        LimitOffset $limitOffset = null,
        OrderBy $orderBy = null,
        $distinct = false
    ) {
        return QuerySegment::reduce([
            $this->renderSelect($columns, $distinct),
            $this->renderFrom($table),
            $this->renderWhere($filter),
            $this->renderOrderBy($orderBy) ,
            $this->renderLimitOffset($limitOffset),
        ]);
    }

    public function buildSelectAggregate($table, Aggregate $aggregate, Filter $filter = null)
    {
        return QuerySegment::reduce([
            $this->renderSelectAggregate($aggregate),
            $this->renderFrom($table),
            $this->renderWhere($filter),
        ]);
    }

    public function buildInsert($table, array $columns, array $values, $returningColumn)
    {
        return QuerySegment::reduce([
            $this->renderInsertInto($table, $columns),
            $this->renderInsertValues($values),
            $this->renderInsertReturning($returningColumn),
        ]);
    }

    public function buildUpdate($table, array $columns, array $values, Filter $filter = null)
    {
        return QuerySegment::reduce([
            $this->renderUpdate($table),
            $this->renderUpdateSet($columns, $values),
            $this->renderWhere($filter),
        ]);
    }

    public function buildDelete($table, Filter $filter = null)
    {
        return QuerySegment::reduce([
            $this->renderDelete($table),
            $this->renderWhere($filter),
        ]);
    }


    // -- Renderer methods -----------------------------------------------------


    /** SELECT clause for selecting columns */
    protected function renderSelect($columns, $distinct)
    {
        $columns = array_map($this->quoteFn, $columns);

        $query = "SELECT " . ($distinct ? "DISTINCT " : "") .
            implode(", ", $columns);

        return new QuerySegment($query);
    }

    /** SELECT clause for selecting an aggregate */
    protected function renderSelectAggregate(Aggregate $aggregate)
    {
        $type = $aggregate->type();
        $column = $aggregate->column();

        if ($column !== '*') {
            $column = $this->quoter->quote($column);
        }

        $query = "SELECT {$type}({$column}) AS aggregate";

        return new QuerySegment($query);
    }

    /** FROM clause */
    protected function renderFrom($table)
    {
        $table = $this->quoter->quote($table);

        return new QuerySegment("FROM $table");
    }

    /** WHERE clause */
    protected function renderWhere(Filter $filter = null)
    {
        if (!isset($filter)) {
            return new QuerySegment();
        }

        $where = new QuerySegment("WHERE");

        return $where->combine($this->filterRenderer->renderFilter($filter));
    }

    /** ORDER BY clause */
    protected function renderOrderBy(OrderBy $orderBy = null)
    {
        if (!isset($orderBy)) {
            return new QuerySegment();
        }

        $orders = array_map(function ($order) {
            return $this->renderColumnOrder($order);
        }, $orderBy->orders());

        $query = "ORDER BY " . implode(', ', $orders);

        return new QuerySegment($query);
    }

    /** ORDER BY clause fragment ordering by one column */
    protected function renderColumnOrder(ColumnOrder $order)
    {
        $column = $this->quoter->quote($order->column());
        $direction = strtoupper($order->direction());

        return "$column $direction";
    }

    /** LIMIT and OFFSET clauses */
    protected function renderLimitOffset(LimitOffset $limitOffset = null)
    {
        if (!isset($limitOffset)) {
            return new QuerySegment();
        }

        $limit = $limitOffset->limit();
        $offset = $limitOffset->offset();

        $limitSegment = isset($limit) ?
            new QuerySegment("LIMIT ?", [$limit]) :
            new QuerySegment();

        $offsetSegment = isset($offset) ?
            new QuerySegment("OFFSET ?", [$offset]) :
            new QuerySegment();

        return $limitSegment->combine($offsetSegment);
    }

    /** INSERT INTO clause */
    protected function renderInsertInto($table, array $columns)
    {
        $table = $this->quoter->quote($table);
        $columns = implode(", ", array_map($this->quoteFn, $columns));

        return new QuerySegment("INSERT INTO $table ($columns)");
    }

    /** VALUES clause for use with INSERT */
    protected function renderInsertValues($values)
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        return new QuerySegment("VALUES ($placeholders)", $values);
    }

    /** RETURNING clause, only used for Postgres */
    protected function renderInsertReturning($column)
    {
        return new QuerySegment();
    }

    /** UPDATE clause */
    protected function renderUpdate($table)
    {
        $table = $this->quoter->quote($table);

        return new QuerySegment("UPDATE $table");
    }

    /* SET clause, for use with UPDATE */
    protected function renderUpdateSet($columns, $values)
    {
        $setFn = function ($column) {
            return $this->quoter->quote($column) . " = ?";
        };

        $query = "SET " . implode(", ", array_map($setFn, $columns));

        return new QuerySegment($query, $values);
    }

    /** DELETE clause */
    protected function renderDelete($table)
    {
        $table = $this->quoter->quote($table);

        return new QuerySegment("DELETE FROM $table");
    }
}
