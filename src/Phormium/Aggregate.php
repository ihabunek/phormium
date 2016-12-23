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

    /** Aggregate function. */
    private $type;

    /** Column on which to perform the aggregation. */
    private $column;

    public function __construct($type, $column = null)
    {
        $types = [self::AVERAGE, self::COUNT, self::MAX, self::MIN, self::SUM];

        if (!in_array($type, $types)) {
            $types = implode(', ', $types);
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
