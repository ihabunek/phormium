<?php

namespace Phormium\Query;

/**
 * Value object representing a SQL query fragment (or a whole query).
 */
class QuerySegment
{
    private $query;
    private $args;

    /**
     * @param string $query SQL code
     * @param array  $args  Values to be bound to placeholders in $query
     */
    public function __construct($query, array $args)
    {
        $this->query = $query;
        $this->args = $args;
    }

    public function query()
    {
        return $this->query;
    }

    public function args()
    {
        return $this->args;
    }

    /**
     * Combines two segments into a larger one.
     *
     * @param  QuerySegment $one
     * @param  QuerySegment $other
     * @return QuerySegment
     */
    public static function combine(QuerySegment $one, QuerySegment $other)
    {
        $query = trim($one->query() . " " . $other->query());
        $args = array_merge($one->args, $other->args);

        return new QuerySegment($query, $args);
    }

    /**
     * Constructs an empty QuerySegment.
     *
     * @return QuerySegment
     */
    public static function makeEmpty()
    {
        return new QuerySegment("", []);
    }

    /**
     * Reduces an array of QuerySegments into one Query segment.
     *
     * @param  QuerySegment[]  $segments
     *
     * @return QuerySegment
     */
    public static function reduce(array $segments)
    {
        return array_reduce($segments, [get_called_class(), "combine"], QuerySegment::makeEmpty());
    }
}