<?php

class TradeTag extends \Phormium\Model
{
    protected static $_meta = array(
        'database' => 'exampledb',
        'table' => 'trade_tag',
        'pk' => 'id'
    );

    public $id;
    public $trade_date;
    public $trade_number;
    public $tag;
}
