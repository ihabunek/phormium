<?php

namespace Phormium\Tests\Models;

/**
 * @connection test
 * @table test
 */
class TestModel extends \Phormium\Model
{
    /** @pk */
    public $id;

    public $string;
}
