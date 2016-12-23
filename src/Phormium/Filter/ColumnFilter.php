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
        $this->validate($column, $operation, $value);

        $this->operation = strtoupper($operation);
        $this->column = $column;
        $this->value = $value;
    }

    private function validate($column, $operation, $value)
    {
        if (!is_string($column)) {
            $type = gettype($column);
            throw new \InvalidArgumentException("Argument \$column must be a string, $type given.");
        }

        if (!is_string($operation)) {
            $type = gettype($operation);
            throw new \InvalidArgumentException("Argument \$operation must be a string, $type given.");
        }

        $operation = strtoupper($operation);

        switch ($operation) {
            case self::OP_EQUALS:
            case self::OP_NOT_EQUALS:
            case self::OP_NOT_EQUALS_ALT:
                $this->checkIsScalarOrNull($value, $operation);
                break;

            case self::OP_GREATER:
            case self::OP_GREATER_OR_EQUAL:
            case self::OP_LESSER:
            case self::OP_LESSER_OR_EQUAL:
            case self::OP_LIKE:
            case self::OP_LIKE_CASE_INSENSITIVE:
            case self::OP_NOT_LIKE:
                $this->checkIsScalar($value, $operation);
                break;

            case self::OP_IN:
            case self::OP_NOT_IN:
                $this->checkIsArray($value, $operation);
                $this->checkArrayContainsScalars($value, $operation);
                $this->checkArrayNotEmpty($value, $operation);
                break;

            case self::OP_IS_NULL:
            case self::OP_NOT_NULL:
            case self::OP_NOT_NULL_ALT:
                $this->checkIsNull($value, $operation);
                break;

            case self::OP_BETWEEN:
                $this->checkIsArray($value, $operation);
                $this->checkArrayContainsScalars($value, $operation);
                $this->checkArrayCount($value, 2, $operation);
                break;

            default:
                throw new \InvalidArgumentException("Unknown filter operation [$operation].");
        }
    }

    // -- Validation functions -------------------------------------------------

    private function checkIsArray($value, $operation)
    {
        if (!is_array($value)) {
            $type = gettype($value);
            $msg = "Filter $operation requires an array, $type given.";
            throw new \InvalidArgumentException($msg);
        }
    }

    private function checkArrayContainsScalars(array $values, $operation)
    {
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                $type = gettype($value);
                $msg = "Filter $operation requires an array of scalars, array containing $type given.";
                throw new \InvalidArgumentException($msg);
            }
        }
    }

    private function checkIsScalarOrNull($value, $operation)
    {
        if (isset($value) && !is_scalar($value)) {
            $type = gettype($value);
            $msg = "Filter $operation requires a scalar value, $type given.";
            throw new \InvalidArgumentException($msg);
        }
    }

    private function checkIsScalar($value, $operation)
    {
        if (!is_scalar($value)) {
            $type = gettype($value);
            $msg = "Filter $operation requires a scalar value, $type given.";
            throw new \InvalidArgumentException($msg);
        }
    }

    private function checkArrayCount(array $array, $expected, $operation)
    {
        $count = count($array);
        if ($count !== $expected) {
            $msg = "Filter $operation requires an array with $expected values, ";
            $msg .= "given array has $count values.";
            throw new \InvalidArgumentException($msg);
        }
    }

    private function checkArrayNotEmpty(array $array, $operation)
    {
        if (empty($array)) {
            $msg = "Filter $operation requires a non-empty array, empty array given.";
            throw new \InvalidArgumentException($msg);
        }
    }

    private function checkIsNull($value, $operation)
    {
        if ($value !== null) {
            $type = gettype($value);
            $msg = "Filter $operation requires the value to be NULL, $type given.";
            throw new \InvalidArgumentException($msg);
        }
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
