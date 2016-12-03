<?php

namespace Phormium\Filter;

use InvalidArgumentException;

/**
 * A filter for SQL queries which converts to a single WHERE condition.
 */
class ColumnFilter extends Filter
{
    // Operation constants
    const OP_BETWEEN = 'BETWEEN';
    const OP_EQUALS = '=';
    const OP_GREATER = '>';
    const OP_GREATER_OR_EQUAL = '>=';
    const OP_IN = 'IN';
    const OP_IS_NULL = 'IS NULL';
    const OP_LESSER = '<';
    const OP_LESSER_OR_EQUAL = '<=';
    const OP_LIKE = 'LIKE';
    const OP_LIKE_CASE_INSENSITIVE = 'ILIKE';
    const OP_NOT_LIKE = 'NOT LIKE';
    const OP_NOT_EQUALS = '<>';
    const OP_NOT_EQUALS_ALT = '!=';
    const OP_NOT_IN = 'NOT IN';
    const OP_NOT_NULL = 'NOT NULL';
    const OP_NOT_NULL_ALT = 'IS NOT NULL';

    /** The filter operation, one of OP_* constants. */
    private $operation;

    /** Column on which to filter. */
    private $column;

    /** The value to use in filtering, depends on operation. */
    private $value;


    public function __construct($column, $operation, $value = null)
    {
        $this->operation = strtoupper($operation);
        $this->column = $column;
        $this->value = $value;
    }

    // --- Accessors -----------------------------------------------------------

    public function operation()
    {
        return $this->operation;
    }

    public function column()
    {
        return $this->column;
    }

    public function value()
    {
        return $this->value;
    }

    // --- Factories -----------------------------------------------------------

    /**
     * Creates a new ColumnFilter from values in an array [$column, $operation,
     * $value] where $value is optional.
     */
    public static function fromArray(array $array)
    {
        $count = count($array);

        switch ($count) {
            case 2:
                return new ColumnFilter($array[0], $array[1]);
            case 3:
                return new ColumnFilter($array[0], $array[1], $array[2]);
            default:
                throw new \Exception("Invalid filter sepecification.");
        }
    }
}
