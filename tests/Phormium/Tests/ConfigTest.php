<?php

namespace Phormium\Tests;

use Phormium\Config;

use Phormium\DB;

/**
 * @group config
 */
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
        $this->assertEquals($expected, Config::getDatabases());
        $this->assertFalse(Config::isLoggingEnabled());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed parsing json config file
     */
    public function testConfigureFail()
    {
        $config = realpath(__DIR__ . '/../../config/invalid.json');
        DB::configure($config);
        $this->assertTrue(false);
    }

    public function testReset()
    {
        $config = realpath(__DIR__ . '/../../config/config.json');
        $this->assertEmpty(Config::getDatabases());
        Config::load($config);
        $this->assertNotEmpty(Config::getDatabases());
        Config::reset();
        $this->assertEmpty(Config::getDatabases());
    }
}
