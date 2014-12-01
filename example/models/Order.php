<?php

/**
 * A stock exchange trade.
 * Demonstrates usage of composite primary key.
 */
class Order extends \Phormium\Model
{
    protected static $_meta = array(
        'database' => 'exampledb',
        'table' => 'oorder',
        'pk' => array('orderdate', 'orderno')
    );

    public $orderdate;
    public $orderno;
    public $price;
    public $quantity;

    public function trades()
    {
        return $this->hasMany("Trade", ['orderdate', 'orderno'], ['orderdate', 'orderno']);
    }
}
