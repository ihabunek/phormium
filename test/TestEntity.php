<?php

namespace Phormium\Test;

/**
 * @connection test
 * @table test
 */
class TestEntity extends \Phormium\Entity
{
    /** @pk */
    public $id;

    public $string;
}
