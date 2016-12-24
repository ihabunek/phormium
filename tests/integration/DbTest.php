<?php

namespace Phormium\Tests\Integration;

use Phormium\Orm;

abstract class DbTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        Orm::configure(PHORMIUM_CONFIG_FILE);
    }

    public static function tearDownAfterClass()
    {
        Orm::reset();
    }
}
