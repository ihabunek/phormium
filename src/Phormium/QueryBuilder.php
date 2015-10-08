<?php

namespace Phormium;

use Phormium\Aggregate;
use Phormium\Database\Driver;
use Phormium\Filter\Filter;

class QueryBuilder
{
    protected $aggregate;
    protected $columns;
    protected $driver;
    protected $filter;
    protected $limit;
    protected $offset;
    protected $order;
    protected $table;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    // -- Fluent interface -----------------------------------------------------

    public function filter(Filter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function aggregate(Aggregate $aggregate)
    {
        $this->aggregate = $aggregate;

        return $this;
    }

    public function order(array $order)
    {
        $this->order = $order;

        return $this;
    }

    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    // -- Build methods --------------------------------------------------------

    public function buildSelect($distinct = false)
    {
        $columns = implode(", ", $this->columns);
        $table = $this->table;

        list($limit1, $limit2) = $this->constructLimitOffset($this->limit, $this->offset);
        list($where, $args) = $this->constructWhere($this->filter);
        $order = $this->constructOrder($this->order);

        $distinct = $distinct ? " DISTINCT" : "";

        $query = "SELECT{$distinct}{$limit1} {$columns} FROM {$table}{$where}{$order}{$limit2};";

        return [$query, $args];
    }

    public function buildSelectAggregate()
    {
        $table = $this->table;
        $select = $this->aggregate->render();
        list($where, $args) = $this->constructWhere($this->filter);

        $query = "SELECT {$select} AS aggregate FROM {$table}{$where};";

        return [$query, $args];
    }

    public function buildDelete()
    {
        list($where, $args) = $this->constructWhere($this->filter);

        $query = "DELETE FROM {$this->table}{$where}";

        return [$query, $args];
    }

    // -------------------------------------------------------------------------

    /** Constructs a WHERE clause for a given filter. */
    private function constructWhere(Filter $filter = null)
    {
        if ($filter === null) {
            return ["", []];
        }

        list($where, $args) = $filter->render();

        if (empty($where)) {
            return ["", []];
        }

        $where = " WHERE $where";
        return [$where, $args];
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
            case Driver::INFORMIX:
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
}
