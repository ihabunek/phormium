<?php

namespace Phormium\Tests;

use Phormium\Phormium;

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

    public function testConfigureJson()
    {
        $config = $this->configDir . '/config.json';
        Phormium::configure($config);

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:target/temp/test.db',
                'username' => '',
                'password' => '',
            )
        );

        $this->assertEquals($expected, Phormium::db()->getConfig());
    }

    public function testConfigureYaml()
    {
        $config = $this->configDir . '/config.yaml';
        Phormium::configure($config);

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:target/temp/test.db',
                'username' => '',
                'password' => '',
            )
        );

        $this->assertEquals($expected, Phormium::db()->getConfig());
    }

    public function testConfigureArray()
    {
        Phormium::configure(array(
            'databases' => array(
                'mydb' => array(
                    'dsn' => 'sqlite:target/temp/test.db'
                )
            )
        ));

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:target/temp/test.db',
                'username' => '',
                'password' => '',
            )
        );

        $this->assertEquals($expected, Phormium::db()->getConfig());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed parsing JSON
     */
    public function testFailInvalidSyntax()
    {
        $config = $this->configDir . '/invalid.json';
        Phormium::configure($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Config file not found at "/should/not/exist.json"
     */
    public function testFailFileDoesNotExist()
    {
        $config = '/should/not/exist.json';
        Phormium::configure($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unsupported configuration format
     */
    public function testFailUnknownExtension()
    {
        $config = $this->configDir . '/config.xxx';
        Phormium::configure($config);
    }

    /**
     * @expectedException \Exception
     */
    public function testFileExistsButIsADirectory()
    {
        $config = __DIR__;
        @Phormium::configure($config);
    }

    public function testReset()
    {
        Phormium::reset();

        $this->assertFalse(Phormium::isConfigured());

        $config = $this->configDir . '/config.json';
        Phormium::configure($config);

        $this->assertTrue(Phormium::isConfigured());
        $this->assertNotEmpty(Phormium::db()->getConfig());

        Phormium::reset();

        $this->assertFalse(Phormium::isConfigured());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Database "xxx" is not configured
     */
    public function testDatabaseDoesNotExist()
    {
        $config = $this->configDir . '/config.json';
        Phormium::configure($config);

        Phormium::db()->getConnection('xxx');
    }

    public function testAddDatabase()
    {
        $dsn = 'dsn goes here';
        Phormium::db()->setConnectionConfig('xxx', $dsn);
        $actual = Phormium::db()->getConnectionConfig('xxx');

        $expected = array(
            'dsn' => $dsn,
            'username' => null,
            'password' => null,
        );

        $this->assertSame($expected, $actual);
    }
}
