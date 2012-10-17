<?php

namespace Phormium;

/**
 * A helper class for constructing Filter objects.
 *
 * Best used by it's alias <var>f</var>.
 *
 * For example:
 * <pre>
 * f::eq('col', 10)
 * </pre>
 *
 * instead of:
 * <pre>
 * new Filter(Filter::OP_EQUALS, 'col', 10);
 * </pre>
 */
class FilterFactory /* factory LOL */
{
    public static function __callStatic($name, $args)
    {
        throw new \Exception("Filter [$name] is not implemented.");
    }

    public static function pk($value)
    {
        return new Filter(Filter::OP_PK_EQUALS, null, $value);
    }

    public static function eq($column, $value)
    {
        return new Filter(Filter::OP_EQUALS, $column, $value);
    }

    public static function neq($column, $value)
    {
        return new Filter(Filter::OP_NOT_EQUALS, $column, $value);
    }

    public static function in($column, $value)
    {
        return new Filter(Filter::OP_IN, $column, $value);
    }

    public static function nin($column, $value)
    {
        return new Filter(Filter::OP_NOT_IN, $column, $value);
    }

    public static function like($column, $value)
    {
        return new Filter(Filter::OP_LIKE, $column, $value);
    }

    public static function notLike($column, $value)
    {
        return new Filter(Filter::OP_NOT_LIKE, $column, $value);
    }

    public static function gt($column, $value)
    {
        return new Filter(Filter::OP_GREATER, $column, $value);
    }

    public static function gte($column, $value)
    {
        return new Filter(Filter::OP_GREATER_OR_EQUAL, $column, $value);
    }

    public static function lt($column, $value)
    {
        return new Filter(Filter::OP_LESSER, $column, $value);
    }

    public static function lte($column, $value)
    {
        return new Filter(Filter::OP_LESSER_OR_EQUAL, $column, $value);
    }

    public static function between($column, $low, $high)
    {
        return new Filter(Filter::OP_BETWEEN, $column, array($low, $high));
    }

    public static function isNull($column)
    {
        return new Filter(Filter::OP_IS_NULL, $column);
    }

    public static function notNull($column)
    {
        return new Filter(Filter::OP_NOT_NULL, $column);
    }
}
