<?php

namespace Phormium;

class Aggregate
{
    const TYPE_AVERAGE = 'avg';
    const TYPE_MAX = 'max';
    const TYPE_MIN = 'min';
    const TYPE_SUM = 'sum';

    private $types = array(
        self::TYPE_AVERAGE,
        self::TYPE_MAX,
        self::TYPE_MIN,
        self::TYPE_SUM,
    );

    /** Aggregate type. One of TYPE_* constants. */
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

    // ******************************************
    // *** Static factory methods             ***
    // ******************************************

    public static function __callStatic($name, $args)
    {
        throw new \Exception("Invalid aggregate type [$name]");
    }

    public static function avg($column)
    {
        return new Aggregate(Aggregate::TYPE_AVERAGE, $column);
    }

    public static function max($column)
    {
        return new Aggregate(Aggregate::TYPE_MAX, $column);
    }

    public static function min($column)
    {
        return new Aggregate(Aggregate::TYPE_MIN, $column);
    }

    public static function sum($column)
    {
        return new Aggregate(Aggregate::TYPE_SUM, $column);
    }
}
