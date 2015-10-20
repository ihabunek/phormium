<?php

namespace Phormium\Tests\Models;

/**
 * A model with no primary key.
 */
class PkLess extends \Phormium\Model
{
    protected static $_meta = [
        'database' => 'testdb',
        'table' => 'pkless',
    ];

    public $foo;
    public $bar;
    public $baz;
}
