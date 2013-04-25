<?php

namespace Phormium;

class Aggregate
{
    const AVERAGE = 'avg';
    const MAX = 'max';
    const MIN = 'min';
    const SUM = 'sum';

    private $types = array(
        self::AVERAGE,
        self::MAX,
        self::MIN,
        self::SUM,
    );

    /** Aggregate type. One of $types constants. */
    public $type;

    /** Column on which to perform the aggregation. */
    public $column;

    public function __construct($type, $column)
    {
        if (!in_array($type, $this->types)) {
            $types = implode(', ', $this->types);
            throw new \Exception("Invalid aggregate type [$type]. Supported types: $types.");
        }

        $this->type = $type;
        $this->column = $column;
    }

    public function render()
    {
        return "{$this->type}({$this->column})";
    }
}
