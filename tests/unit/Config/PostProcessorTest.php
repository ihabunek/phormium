<?php

namespace Phormium\Tests;

use PDO;
use Phormium\Config\PostProcessor;

/**
 * @group config
 * @group unit
 */
class PostProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessConstant()
    {
        $processor = new PostProcessor();

        // Constants remain unchanged
        $this->assertSame(PDO::ATTR_SERVER_INFO, $processor->processConstant(PDO::ATTR_SERVER_INFO));
        $this->assertSame(PDO::PARAM_BOOL, $processor->processConstant(PDO::PARAM_BOOL));
        $this->assertSame(PDO::FETCH_LAZY, $processor->processConstant(PDO::FETCH_LAZY));

        // Strings mapped to constant values
        $this->assertSame(PDO::ATTR_SERVER_INFO, $processor->processConstant("PDO::ATTR_SERVER_INFO"));
        $this->assertSame(PDO::PARAM_BOOL, $processor->processConstant("PDO::PARAM_BOOL"));
        $this->assertSame(PDO::FETCH_LAZY, $processor->processConstant("PDO::FETCH_LAZY"));

        // Allowed scalars
        $this->assertSame("foo", $processor->processConstant("foo", true));
        $this->assertSame(123, $processor->processConstant(123, true));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid constant value
     */
    public function testProcessConstantError1()
    {
        $processor = new PostProcessor();
        $processor->processConstant([]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid constant value
     */
    public function testProcessConstantError2()
    {
        $processor = new PostProcessor();
        $processor->processConstant("foo", false);
    }

    public function testProcessConfig()
    {
        $config = [
            "databases" => [
                "one" => [
                    "dsn" => "mysql:host=localhost",
                    "attributes" => [
                        "PDO::ATTR_CASE" => "PDO::CASE_LOWER",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        "PDO::ATTR_STRINGIFY_FETCHES" => false,
                        PDO::ATTR_TIMEOUT => 10
                    ]
                ]
            ]
        ];

        $expected = [
            "databases" => [
                "one" => [
                    "dsn" => "mysql:host=localhost",
                    "username" => null,
                    "password" => null,
                    "driver" => "mysql",
                    "attributes" => [
                        PDO::ATTR_CASE => PDO::CASE_LOWER,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_STRINGIFY_FETCHES => false,
                        PDO::ATTR_TIMEOUT => 10
                    ]
                ]
            ]
        ];

        $processor = new PostProcessor();
        $actual = $processor->processConfig($config);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid attribute "foo" specified in configuration for database "one".
     */
    public function testProcessConfigError1()
    {
        $processor = new PostProcessor();
        $processor->processConfig([
            "databases" => [
                "one" => [
                    "dsn" => "mysql:host=localhost",
                    "attributes" => [
                        "foo" => 10
                    ]
                ]
            ]
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid value given for attribute "PDO::ATTR_TIMEOUT", in configuration for database "one".
     */
    public function testProcessConfigError2()
    {
        $processor = new PostProcessor();
        $processor->processConfig([
            "databases" => [
                "one" => [
                    "dsn" => "mysql:host=localhost",
                    "attributes" => [
                        "PDO::ATTR_TIMEOUT" => []
                    ]
                ]
            ]
        ]);
    }

    public function testParseDriver()
    {
        $proc = new PostProcessor();

        $this->assertSame('informix', $proc->parseDriver('informix:host=localhost'));
        $this->assertSame('mysql', $proc->parseDriver('mysql:host=localhost'));
        $this->assertSame('pgsql', $proc->parseDriver('pgsql:host=localhost'));
        $this->assertSame('sqlite', $proc->parseDriver('sqlite:host=localhost'));
    }
}
