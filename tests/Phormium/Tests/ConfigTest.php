<?php

namespace Phormium\Tests;

use PDO;

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

    public function testConfigureJson()
    {
        $config = $this->configDir . '/config.json';
        DB::configure($config);

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:tmp/test.db',
                'username' => null,
                'password' => null,
                'attributes' => []
            )
        );

        $this->assertSame($expected, Config::getDatabases());
    }

    public function testConfigureYaml()
    {
        $config = $this->configDir . '/config.yaml';
        DB::configure($config);

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:tmp/test.db',
                'username' => null,
                'password' => null,
                'attributes' => []
            )
        );

        $this->assertSame($expected, Config::getDatabases());
    }

    public function testConfigureArray()
    {
        DB::configure(array(
            'databases' => array(
                'mydb' => array(
                    'dsn' => 'sqlite:tmp/test.db'
                )
            )
        ));

        $expected = array(
            'mydb' => array(
                'dsn' => 'sqlite:tmp/test.db',
                'username' => null,
                'password' => null,
                'attributes' => []
            )
        );

        $this->assertSame($expected, Config::getDatabases());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed parsing JSON
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
        $user = 'foo';
        $pass = 'bar';
        $attrs = ["PDO::ATTR_CASE" => "PDO::CASE_NATURAL"];

        Config::addDatabase('xxx', $dsn, $user, $pass, $attrs);
        $actual = Config::getDatabase('xxx');

        $expected = [
            'dsn' => $dsn,
            'username' => $user,
            'password' => $pass,
            'attributes' => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL
            ]
        ];

        $this->assertSame($expected, $actual);
    }

    public function testAttributesStrings()
    {
        $dsn = 'für immer punk';

        Config::load([
            "databases" => [
                "main" => [
                    "dsn" => $dsn,
                    "attributes" => [
                        "PDO::ATTR_CASE" => "PDO::CASE_NATURAL"
                    ]
                ]
            ]
        ]);

        $expected = [
            "dsn" => $dsn,
            'username' => null,
            'password' => null,
            "attributes" => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL
            ]
        ];

        $actual = Config::getDatabase('main');

        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual);
    }

    public function testAttributesInteger()
    {
        $dsn = 'für immer punk';

        Config::load([
            "databases" => [
                "main" => [
                    "dsn" => $dsn,
                    "attributes" => [
                        PDO::ATTR_CASE => PDO::CASE_NATURAL
                    ]
                ]
            ]
        ]);

        $expected = [
            "dsn" => $dsn,
            'username' => null,
            'password' => null,
            "attributes" => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL
            ]
        ];

        $actual = Config::getDatabase('main');

        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid attribute "FOO" specified in configuration for database "main".
     */
    public function testAttributeNameDoesNotExist()
    {
        $dsn = 'für immer punk';

        Config::load([
            "databases" => [
                "main" => [
                    "dsn" => $dsn,
                    "attributes" => [
                        "FOO" => PDO::CASE_NATURAL
                    ]
                ]
            ]
        ]);
    }
}
