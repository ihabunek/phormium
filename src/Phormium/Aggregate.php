<?php

namespace Phormium;

/**
 * Generates the SELECT clause for aggregate statements.
 */
class Aggregate
{
    // Available aggregate functions
    const AVERAGE = 'avg';
    const COUNT = 'count';
    const MAX = 'max';
    const MIN = 'min';
    const SUM = 'sum';

    /** Available aggregates. */
    private $types = [
        self::AVERAGE,
        self::COUNT,
        self::MAX,
        self::MIN,
        self::SUM,
    ];

    /** Aggregate type. One of $types constants. */
    private $type;

    /** Column on which to perform the aggregation. */
    private $column;

    public function __construct($type, $column = null)
    {
        if (!in_array($type, $this->types)) {
            $types = implode(', ', $this->types);
            throw new \Exception("Invalid aggregate type [$type]. Supported types: $types.");
        }

        if (!isset($column)) {
            if ($type === self::COUNT) {
                $column = "*";
            } else {
                throw new \Exception("Aggregate type [$type] requires a column to be given.");
            }
        }

        $this->type = $type;
        $this->column = $column;
    }

    // -- Accessors ------------------------------------------------------------

    public function type()
    {
        return $this->type;
    }

    public function column()
    {
        return $this->column;
    }
}
