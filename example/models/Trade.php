<?php

/**
 * A stock exchange trade.
 * Demonstrates usage of composite primary key.
 */
class Trade extends \Phormium\Model
{
    protected static $_meta = array(
        'database' => 'exampledb',
        'table' => 'trade',
        'pk' => array('date', 'number')
    );

    public $date;
    public $number;
    public $price;
    public $quantity;

    public function tags()
    {
        return $this->hasMany('TradeTag');
    }
}
