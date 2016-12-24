<?php

namespace Phormium\Query;

use Phormium\Exception\InvalidQueryException;

/**
 * A value object representing ordering by a column, a part of the ORDER BY
 * SQL clause.
 */
class ColumnOrder
{
    /**
     * Column to order by.
     *
     * @var string
     */
    private $column;

    /**
     * Ordering direction.
     *
     * One of ColumnOrder::ASCENDING, ColumnOrder::DESCENDING.
     *
     * @var string
     */
    private $direction;

    const ASCENDING = 'asc';

    const DESCENDING = 'desc';

    public function __construct($column, $direction)
    {
        $directions = [self::ASCENDING, self::DESCENDING];

        if (!in_array($direction, $directions)) {
            $directions = implode(", ", $directions);
            throw new InvalidQueryException("Invalid \$direction [$direction]. Expected one of [$directions].");
        }

        if (!is_string($column)) {
            $type = gettype($column);
            throw new InvalidQueryException("Invalid \$column type [$type], expected string.");
        }

        $this->column = $column;
        $this->direction = $direction;
    }

    // -- Accessors ------------------------------------------------------------

    public function column()
    {
        return $this->column;
    }

    public function direction()
    {
        return $this->direction;
    }

    // -- Factories ------------------------------------------------------------

    public static function asc($column)
    {
        return new ColumnOrder($column, ColumnOrder::ASCENDING);
    }

    public static function desc($column)
    {
        return new ColumnOrder($column, ColumnOrder::DESCENDING);
    }
}
