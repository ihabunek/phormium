<?php

namespace Phormium\Tests;

use \Phormium\DB;

class DBTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $config = realpath(__DIR__ . '/../../config/config.json');
        DB::configure($config);
        self::assertTrue(true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed parsing json config file
     */
    public function testConfigureFail()
    {
        $config = realpath(__DIR__ . '/../../config/invalid.json');
        DB::configure($config);
        self::assertTrue(false);
    }
}
