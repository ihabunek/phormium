<?php

namespace Phormium\Tests;

use Phormium\Config;

use \Phormium\DB;

class DBTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Config::reset();
    }

    public function testConfigure()
    {
        $config = realpath(__DIR__ . '/../../config/config.json');
        DB::configure($config);

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:target/temp/test.db',
                'username' => '',
                'password' => '',
            )
        );
        self::assertEquals($expected, Config::getDatabases());
        self::assertFalse(Config::isLoggingEnabled());
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

    public function testReset()
    {
        $config = realpath(__DIR__ . '/../../config/config.json');
        self::assertEmpty(Config::getDatabases());
        Config::load($config);
        self::assertNotEmpty(Config::getDatabases());
        Config::reset();
        self::assertEmpty(Config::getDatabases());
    }
}
