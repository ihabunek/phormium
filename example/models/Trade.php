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
        'pk' => array('tradedate', 'tradeno')
    );

    public $tradedate;
    public $tradeno;
    public $price;
    public $quantity;
}
