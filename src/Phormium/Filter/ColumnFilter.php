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
    public $operation;

    /** Column on which to filter. */
    public $column;

    /** The value to use in filtering, depends on operation. */
    public $value;

    public function __construct($column, $operation, $value = null)
    {
        $this->operation = strtoupper($operation);
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * Renders a WHERE condition for the given filter.
     */
    public function render()
    {
        switch($this->operation)
        {
            case self::OP_EQUALS:
                // Replace "= null" with "IS NULL"
                if (is_null($this->value)) {
                    return $this->renderIsNull($this->column);
                }

                return $this->renderSimple($this->column, $this->operation, $this->value);

            case self::OP_NOT_EQUALS:
            case self::OP_NOT_EQUALS_ALT:
                // Replace "<> null" with "IS NOT NULL"
                if (is_null($this->value)) {
                    return $this->renderNotNull($this->column);
                }

                return $this->renderSimple($this->column, $this->operation, $this->value);

            case self::OP_LIKE:
            case self::OP_NOT_LIKE:
            case self::OP_GREATER:
            case self::OP_GREATER_OR_EQUAL:
            case self::OP_LESSER:
            case self::OP_LESSER_OR_EQUAL:
                return $this->renderSimple($this->column, $this->operation, $this->value);

            case self::OP_LIKE_CASE_INSENSITIVE:
                return $this->renderLikeCaseInsensitive($this->column, $this->operation, $this->value);

            case self::OP_IN:
                return $this->renderIn($this->column, $this->operation, $this->value);

            case self::OP_NOT_IN:
                return $this->renderNotIn($this->column, $this->operation, $this->value);

            case self::OP_IS_NULL:
                return $this->renderIsNull($this->column);

            case self::OP_NOT_NULL:
            case self::OP_NOT_NULL_ALT:
                return $this->renderNotNull($this->column);

            case self::OP_BETWEEN:
                return $this->renderBetween($this->column, $this->operation, $this->value);

            default:
                throw new \Exception("Unknown filter operation [{$this->operation}].");
        }
    }

    /**
     * Renders a simple condition which can be expressed as:
     *      <column> <operator> <value>
     */
    private function renderSimple($column, $operation, $value)
    {
        $this->checkIsScalar($value, $operation);

        $where = "{$column} {$operation} ?";
        return array($where, array($value));
    }

    private function renderBetween($column, $operation, $values)
    {
        $this->checkIsArray($values, $operation);
        $this->checkArrayCount($values, 2, $operation);

        $where = "{$column} BETWEEN ? AND ?";
        return array($where, $values);
    }

    private function renderIn($column, $operation, $values)
    {
        $this->checkIsArray($values, $operation);
        $this->checkArrayNotEmpty($values, $operation);

        $qs = array_fill(0, count($values), '?');
        $where = "$column IN (" . implode(', ', $qs) . ")";
        return array($where, $values);
    }

    private function renderLikeCaseInsensitive($column, $operation, $value)
    {
        $this->checkIsScalar($value, $operation);

        $where = "lower($column) LIKE lower(?)";
        return array($where, array($value));
    }

    private function renderNotIn($column, $operation, $values)
    {
        $this->checkIsArray($values, $operation);
        $this->checkArrayNotEmpty($values, $operation);

        $qs = array_fill(0, count($values), '?');
        $where = "$column NOT IN (" . implode(', ', $qs) . ")";
        return array($where, $values);
    }

    private function renderIsNull($column)
    {
        $where = "$column IS NULL";
        return array($where, array());
    }

    private function renderNotNull($column)
    {
        $where = "$column IS NOT NULL";
        return array($where, array());
    }

    // ******************************************
    // *** Validation functions               ***
    // ******************************************

    private function checkIsArray($value, $operation)
    {
        if (!is_array($value)) {
            $type = gettype($value);
            $msg = "Filter $operation requires an array, $type given.";
            throw new InvalidArgumentException($msg);
        }
    }

    private function checkIsScalar($value, $operation)
    {
        if (!is_scalar($value)) {
            $type = gettype($value);
            $msg = "Filter $operation requires a scalar value, $type given.";
            throw new InvalidArgumentException($msg);
        }
    }

    private function checkArrayCount(array $array, $expected, $operation)
    {
        $count = count($array);
        if ($count !== $expected) {
            $msg = "Filter $operation requires an array with $expected values, ";
            $msg .= "given array has $count values.";
            throw new InvalidArgumentException($msg);
        }
    }

    private function checkArrayNotEmpty(array $array, $operation)
    {
        if (empty($array)) {
            $msg = "Filter $operation requires a non-empty array, empty array given.";
            throw new InvalidArgumentException($msg);
        }
    }

    // ******************************************
    // *** Statics                            ***
    // ******************************************

    /**
     * Creates a new ColumnFilter from values in an array [$column, $operation,
     * $value] where $value is optional.
     */
    public static function fromArray(array $array)
    {
        $count = count($array);

        switch($count)
        {
            case 2:
                return new ColumnFilter($array[0], $array[1]);
            case 3:
                return new ColumnFilter($array[0], $array[1], $array[2]);
            default:
                throw new \Exception("Invalid filter sepecification.");
        }
    }
}
