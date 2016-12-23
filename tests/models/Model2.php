<?php

namespace Phormium\Tests\Models;

/**
 * A test model with an explicit primary key column.
 */
class Model2 extends \Phormium\Model
{
    protected static $_meta = [
        'pk' => 'foo',
        'database' => 'database1',
        'table' => 'model2'
    ];

    public $foo;
    public $bar;
    public $baz;
}
