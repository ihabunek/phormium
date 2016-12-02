<?php

namespace Phormium\QueryBuilder\Common;

use Phormium\Aggregate;
use Phormium\Filter\Filter;
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

    public function renderSelect($columns, $distinct)
    {
        $columns = array_map($this->quoteFn, $columns);

        $query = "SELECT " . ($distinct ? "DISTINCT " : "") .
            implode(", ", $columns);

        return new QuerySegment($query);
    }

    public function renderSelectAggregate(Aggregate $aggregate)
    {
        $column = $aggregate->column !== "*" ?
            $this->quoter->quote($aggregate->column) : $aggregate->column;

        $query = "SELECT {$aggregate->type}({$column}) AS aggregate";

        return new QuerySegment($query);
    }

    public function renderFrom($table)
    {
        $table = $this->quoter->quote($table);

        return new QuerySegment("FROM $table");
    }

    public function renderWhere(Filter $filter = null)
    {
        if (!isset($filter)) {
            return new QuerySegment();
        }

        $where = new QuerySegment("WHERE");

        return $where->combine($this->filterRenderer->renderFilter($filter));
    }

    /** Constructs an ORDER BY clause. */
    public function renderOrderBy($order)
    {
        if (empty($order)) {
            return new QuerySegment();
        }

        $query = "ORDER BY " . implode(', ', $order);

        return new QuerySegment($query);
    }

    /** Constructs the LIMIT/OFFSET clause. */
    public function renderLimitOffset($limit, $offset)
    {
        if (!isset($limit) && !isset($offset)) {
            return new QuerySegment();
        }

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

        $limitSegment = isset($limit) ?
            new QuerySegment("LIMIT ?", [$limit]) :
            new QuerySegment();

        $offsetSegment = isset($offset) ?
            new QuerySegment("OFFSET ?", [$offset]) :
            new QuerySegment();

        return $limitSegment->combine($offsetSegment);
    }

    /** INSERT INTO clause */
    public function renderInsertInto($table, array $columns)
    {
        $table = $this->quoter->quote($table);
        $columns = implode(", ", array_map($this->quoteFn, $columns));

        return new QuerySegment("INSERT INTO $table ($columns)");
    }

    /** VALUES clause */
    public function renderInsertValues($values)
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        return new QuerySegment("VALUES ($placeholders)", $values);
    }

    /** RETURNING clause, only used for Postgres */
    public function renderInsertReturning($column)
    {
        return new QuerySegment();
    }

    public function renderUpdate($table)
    {
        $table = $this->quoter->quote($table);

        return new QuerySegment("UPDATE $table");
    }

    public function renderUpdateSet($columns, $values)
    {
        $setFn = function ($column) {
            return $this->quoter->quote($column) . " = ?";
        };

        $query = "SET " . implode(", ", array_map($setFn, $columns));

        return new QuerySegment($query, $values);
    }

    protected function renderDelete($table)
    {
        $table = $this->quoter->quote($table);

        return new QuerySegment("DELETE FROM $table");
    }


    // -- Build methods --------------------------------------------------------


    public function buildSelect(
        $table,
        array $columns,
        Filter $filter = null,
        $limit = null,
        $offset = null,
        array $order = null,
        $distinct = false
    ) {
        return QuerySegment::reduce([
            $this->renderSelect($columns, $distinct),
            $this->renderFrom($table),
            $this->renderWhere($filter),
            $this->renderOrderBy($order),
            $this->renderLimitOffset($limit, $offset),
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
}
