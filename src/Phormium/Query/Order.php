<?php

namespace Phormium\Query;

class Order
{
    private $column;
    private $direction;

    const ASCENDING = 'ASC';
    const DESCENDING = 'DESC';

    public function __construct($column, $direction)
    {
        $directions = [self::ASCENDING, self::DESCENDING];

        if (!in_array($direction, $directions)) {
            $directions = implode(", ", $directions);
            throw new \InvalidArgumentException("Invalid direction [$direction]. Expected one of [$directions].");
        }

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException("Invalid column name [$column].");
        }

        $this->column = $column;
        $this->direction = $direction;
    }

    public function column()
    {
        return $this->column;
    }

    public function direction()
    {
        return $this->direction;
    }
}
