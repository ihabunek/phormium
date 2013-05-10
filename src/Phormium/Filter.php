<?php

namespace Phormium;

/**
 * A common interface for filters.
 */
abstract class Filter
{
    /** Renders the filter to SQL. */
    public abstract function render();

    public static function _and()
    {
        return new CompositeFilter(
            CompositeFilter::OP_AND,
            func_get_args()
        );
    }

    public static function _or()
    {
        return new CompositeFilter(
            CompositeFilter::OP_OR,
            func_get_args()
        );
    }
}
