<?php

namespace Phormium;

/**
 * A common interface for filters.
 */
abstract class Filter
{
    /** Renders the filter to SQL. */
    abstract public function render();

    /**
     * Creates a new AND composite filter from given filter components.
     *
     * @param mixed One or more Filter objects or array($column, $op, $value)
     *      triplets which will be converted to corresponding ColumnFilter
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
     *      triplets which will be converted to corresponding ColumnFilter
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
}
