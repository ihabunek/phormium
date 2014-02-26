<?php

namespace Phormium\Tests;

use Phormium\Config;

use Phormium\DB;

/**
 * @group config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $configDir;

    public function __construct()
    {
        $this->configDir = realpath(__DIR__ . '/../../config');
    }

    public function setUp()
    {
        Config::reset();
    }

    public function testConfigure()
    {
        $config = $this->configDir . '/config.json';
        DB::configure($config);

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:target/temp/test.db',
                'username' => '',
                'password' => '',
            )
        );
        $this->assertEquals($expected, Config::getDatabases());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed parsing JSON configuration
     */
    public function testFailInvalidSyntax()
    {
        $config = $this->configDir . '/invalid.json';
        DB::configure($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Config file not found at "/should/not/exist.json"
     */
    public function testFailFileDoesNotExist()
    {
        $config = '/should/not/exist.json';
        DB::configure($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unsupported configuration format
     */
    public function testFailUnknownExtension()
    {
        $config = $this->configDir . '/config.xxx';
        DB::configure($config);
    }

    /**
     * @expectedException \Exception
     */
    public function testFileExistsButIsADirectory()
    {
        $config = __DIR__;
        @DB::configure($config);
    }

    public function testReset()
    {
        $config = $this->configDir . '/config.json';
        $this->assertEmpty(Config::getDatabases());
        Config::load($config);
        $this->assertNotEmpty(Config::getDatabases());
        Config::reset();
        $this->assertEmpty(Config::getDatabases());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Database "xxx" not defined
     */
    public function testDatabaseDoesNotExist()
    {
        Config::getDatabase('xxx');
    }

    public function testAddDatabase()
    {
        $dsn = 'dsn goes here';
        Config::addDatabase('xxx', $dsn);
        $actual = Config::getDatabase('xxx');

        $expected = array(
            'dsn' => $dsn,
            'username' => null,
            'password' => null,
        );

        $this->assertSame($expected, $actual);
    }
}
