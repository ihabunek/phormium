<?php

namespace Phormium\Query;

/**
 * Value object representing a SQL query fragment (or a whole query).
 */
class QuerySegment
{
    /**
     * An SQL query snippet, may include placeholders.
     *
     * @var string
     */
    private $query;

    /**
     * Arguments to be bound to placeholders in the $query.
     *
     * @var array
     */
    private $args;

    /**
     * @param string $query SQL code snippet.
     * @param array  $args  Bound arguments.
     */
    public function __construct($query = "", array $args = [])
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
     * @param  QuerySegment $other
     * @return QuerySegment
     */
    public function combine(QuerySegment $other)
    {
        $query = trim($this->query() . " " . $other->query());
        $args = array_merge($this->args, $other->args);

        return new QuerySegment($query, $args);
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
        $initial = new QuerySegment();

        $reduceFn = function (QuerySegment $one, QuerySegment $other) {
            return $one->combine($other);
        };

        return array_reduce($segments, $reduceFn, $initial);
    }


    /**
     * Implodes an array of QuerySegment by inserting a separator QuerySegment
     * between each two segments in the array, then reducing them.
     *
     * @param  QuerySegment $separator [description]
     * @param  array        $segments  [description]
     * @return [type]                  [description]
     */
    public static function implode(QuerySegment $separator, array $segments)
    {
        if (empty($segments)) {
            return new QuerySegment();
        }

        if (count($segments) === 1) {
            return reset($segments);
        }

        $first = array_shift($segments);

        $imploded = [$first];
        foreach ($segments as $segment) {
            $imploded[] = $separator;
            $imploded[] = $segment;
        }

        return self::reduce($imploded);
    }

    /**
     * Embraces the query in parenthesis, leaving the arguments unchanged.
     *
     * Given "foo AND bar", returns "(foo AND bar)".
     *
     * @param  QuerySegment $segment Segment to embrace.
     *
     * @return QuerySegment Embraced segment.
     */
    public static function embrace(QuerySegment $segment)
    {
        return new QuerySegment("(" . $segment->query() . ")", $segment->args());
    }
}
