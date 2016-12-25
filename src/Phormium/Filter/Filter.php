<?php

namespace Phormium\Filter;

use Phormium\Exception\InvalidQueryException;

/**
 * Base class for filters.
 *
 * Contains various filter factory methods.
 */
abstract class Filter
{
    /**
     * Creates a new AND composite filter from given filter components.
     *
     * @param mixed One or more Filter objects or array($column, $op, $value)
     *      triplets which will be coverted to corresponding ColumnFilter
     *      objects.
     *
     * @return CompositeFilter
     */
    public static function _and(...$filters)
    {
        return new CompositeFilter(CompositeFilter::OP_AND, $filters);
    }

    /**
     * Creates a new OR composite filter from given filter components.
     *
     * @param mixed One or more Filter objects or array($column, $op, $value)
     *      triplets which will be coverted to corresponding ColumnFilter
     *      objects.
     *
     * @return CompositeFilter
     */
    public static function _or(...$filters)
    {
        return new CompositeFilter(CompositeFilter::OP_OR, $filters);
    }

    /**
     * Creates a new column filter.
     *
     * @param string $column Column to filter by.
     * @param string $operation Filter opration, see {@link ColumnFilter}
     * @param string $value Optional filter value.
     *
     * @return ColumnFilter
     */
    public static function col($column, $operation, $value = null)
    {
        return new ColumnFilter($column, $operation, $value);
    }

    /**
     * Creates a new raw filter.
     *
     * @param string $condition The SQL condition.
     * @param array $arguments Array of named arguments (optional).
     *
     * @return RawFilter
     */
    public static function raw($condition, $arguments = [])
    {
        return new RawFilter($condition, $arguments);
    }

    /**
     * A quasi-smart method for creating filters from a variable number of
     * arguments. Used primarily for QuerySet->filter().
     *
     * Here are the possibilities:
     *
     * 1. One argument given
     *
     * a) If it's a Filter object, just return it as-is.
     *    e.g. `->filter(new Filter(...))`
     *
     * b) If it's an array, use it to construct a ColumnFilter.
     *    e.g. `->filter(['foo', 'isnull'])
     *
     * c) If it's a string, use it to construct a RawFilter.
     *    e.g. `->filter('foo = lower(bar)')`
     *
     * 2. Two arguments given
     *
     * a) If both are strings, use them to construct a ColumnFilter.
     *    e.g. `->filter('foo', 'isnull')
     *
     * b) If one is string and the other an array, use it to construct a
     *    Raw filter (first is SQL filter, the second is arguments).
     *    e.g. `->filter('foo = concat(?, ?)', ['bar', 'baz'])
     *
     * 3. Three arguments given
     *
     * a) Use them to construct a ColumnFilter.
     *    e.g. `->filter('foo', '=', 'bar')
     *
     * @return Phormium\Filter\Filter
     * @param  [type] $args [description]
     * @return [type]       [description]
     */
    public static function factory(...$args)
    {
        $count = count($args);

        if ($count === 1) {
            $arg = $args[0];

            if ($arg instanceof Filter) {
                return $arg;
            } elseif (is_array($arg)) {
                return ColumnFilter::fromArray($arg);
            } elseif (is_string($arg)) {
                return new RawFilter($arg);
            }
        } elseif ($count === 2) {
            if (is_string($args[0])) {
                if (is_string($args[1])) {
                    return ColumnFilter::fromArray($args);
                } elseif (is_array($args[1])) {
                    return new RawFilter($args[0], $args[1]);
                }
            }
        } elseif ($count === 3) {
            return ColumnFilter::fromArray($args);
        }

        throw new InvalidQueryException("Invalid filter arguments.");
    }
}
