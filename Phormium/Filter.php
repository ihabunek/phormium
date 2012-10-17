<?php

namespace Phormium;

/**
 * A filter for SQL queries which converts to a single WHERE condition.
 */
class Filter
{
    // Operation constants
    const OP_BETWEEN = 'between';
    const OP_EQUALS = 'eq';
    const OP_GREATER = 'gt';
    const OP_GREATER_OR_EQUAL = 'gte';
    const OP_IN = 'in';
    const OP_IS_NULL = 'null';
    const OP_LESSER = 'lt';
    const OP_LESSER_OR_EQUAL = 'lte';
    const OP_LIKE = 'like';
    const OP_NOT_LIKE = '!like';
    const OP_NOT_EQUALS = 'neq';
    const OP_NOT_IN = '!in';
    const OP_NOT_NULL = '!null';
    const OP_PK_EQUALS = 'pk';

    /** The filter operation, one of OP_* constants. */
    public $operation;

    /** Column on which to filter. */
    public $column;

    /** The value to use in filtering, depends on operation. */
    public $value;

    public function __construct($operation, $column, $value = null)
    {
        $this->operation = $operation;
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * Renders a WHERE condition for the given filter.
     */
    public function render(Meta $meta)
    {
        switch($this->operation)
        {
            case self::OP_EQUALS:
            case self::OP_NOT_EQUALS:
            case self::OP_LIKE:
            case self::OP_NOT_LIKE:
            case self::OP_GREATER:
            case self::OP_GREATER_OR_EQUAL:
            case self::OP_LESSER:
            case self::OP_LESSER_OR_EQUAL:
                return $this->renderSimple($this->column, $this->operation, $this->value);
            case self::OP_PK_EQUALS:
                return $this->renderSimple($meta->pk, self::OP_EQUALS, $this->value);
            case self::OP_IN:
                return $this->renderIn($this->column, $this->value);
            case self::OP_NOT_IN:
                return $this->renderNotIn($this->column, $this->value);
            case self::OP_IS_NULL:
                return $this->renderIsNull($this->column);
            case self::OP_NOT_NULL:
                return $this->renderNotNull($this->column);
            case self::OP_BETWEEN:
                return $this->renderBetween($this->column, $this->value);
            default:
                throw new \Exception("Render not defined for operation [{$this->operation}].");
        }
    }

    /** Maps simple operations to corresponding operators. */
    private $simpleOps = array(
        self::OP_EQUALS => '=',
        self::OP_NOT_EQUALS => '<>',
        self::OP_GREATER => '>',
        self::OP_GREATER_OR_EQUAL => '>=',
        self::OP_LESSER => '<',
        self::OP_LESSER_OR_EQUAL => '<=',
        self::OP_LIKE => 'LIKE',
        self::OP_NOT_LIKE => 'NOT LIKE',
    );

    /**
     * Renders a simple condition which can be expressed as:
     *      <column> <operator> <value>
     */
    private function renderSimple($column, $operation, $value)
    {
        if (!isset($this->simpleOps[$operation])) {
            throw new \Exception("Operation [$operation] not defined in \$simpleOps.");
        }

        $operator = $this->simpleOps[$operation];
        $where = "{$column} {$operator} ?";
        return array($where, array($value));
    }

    private function renderBetween($column, $values)
    {
        if (!is_array($values) || (count($values) != 2)) {
            throw new \Exception("BETWEEN filter requires an array of two values.");
        }

        $where = "{$column} BETWEEN ? AND ?";
        return array($where, $values);
    }

    private function renderIn($column, $values)
    {
        if (!is_array($values) || empty($values)) {
            throw new \Exception("IN filter requires an array with one or more values.");
        }

        $qs = array_fill(0, count($values), '?');
        $where = "$column IN (" . implode(', ', $qs) . ")";
        return array($where, $values);
    }

    private function renderNotIn($column, $values)
    {
        if (!is_array($values) || empty($values)) {
            throw new \Exception("NOT IN filter requires an array with one or more values.");
        }

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
}
