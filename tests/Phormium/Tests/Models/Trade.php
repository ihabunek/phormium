<?php

namespace Phormium\Tests\Models;

/**
 * A stock exchange trade.
 * Demonstrates usage of composite primary key.
 */
class Trade extends \Phormium\Model
{
    protected static $_meta = array(
        'database' => 'testdb',
        'table' => 'trade',
        'pk' => array('tradedate', 'tradeno')
    );

    public $tradedate;
    public $tradeno;
    public $price;
    public $quantity;
}
