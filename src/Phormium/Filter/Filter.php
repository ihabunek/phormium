<?php

namespace Phormium\Filter;

/**
 * Base class for filters.
 */
abstract class Filter
{
    /** Renders the filter to SQL. */
    abstract public function render();

    /**
     * Creates a new AND composite filter from given filter components.
     *
     * @param mixed One or more Filter objects or array($column, $op, $value)
     *      triplets which will be coverted to corresponding ColumnFilter
     *      objects.
     *
     * @return CompositeFilter
     */
    public static function _and()
    {
        return new CompositeFilter(
            CompositeFilter::OP_AND,
            func_get_args()
        );
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
    public static function _or()
    {
        return new CompositeFilter(
            CompositeFilter::OP_OR,
            func_get_args()
        );
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
}
