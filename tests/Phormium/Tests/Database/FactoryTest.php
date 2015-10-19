<?php

namespace Phormium\Tests;


use Mockery as m;

use Phormium\Database\Database;
use Phormium\Database\Factory;
use Phormium\Event;

use PDO;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        "db1" => [
            "dsn" => "sqlite:tmp/db1.db",
            "driver" => "sqlite",
            "username" => null,
            "password" => null,
            "attributes" => []
        ],
        "db2" => [
            "dsn" => "sqlite:tmp/db2.db",
            "driver" => "sqlite",
            "username" => null,
            "password" => null,
            "attributes" => []
        ]
    ];

    public function tearDown()
    {
        m::close();
    }

    protected function getMockEmitter()
    {
        $emitter = m::mock("Evenement\\EventEmitter");

        // $emitter->shouldReceive('on')->once()
        //     ->with(Event::QUERY_STARTED, m::type('callable'));

        // $emitter->shouldReceive('emit');

        return $emitter;
    }

    public function testAttributes1()
    {
        $emitter = $this->getMockEmitter();

        $config = $this->config;
        $config['db1']['attributes'] = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $factory = new Factory($config, $emitter);
        $conn = $factory->newConnection('db1');
        $pdo = $conn->getPDO();

        $expected = PDO::FETCH_ASSOC;
        $actual = $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
        $this->assertSame($expected, $actual);
    }

    public function testAttributes2()
    {
        $emitter = $this->getMockEmitter();

        $config = $this->config;
        $config['db1']['attributes'] = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH
        ];

        $factory = new Factory($config, $emitter);
        $conn = $factory->newConnection('db1');
        $pdo = $conn->getPDO();

        $expected = PDO::FETCH_BOTH;
        $actual = $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
        $this->assertSame($expected, $actual);
    }

    public function testAttributesCannotChange()
    {
        $emitter = $this->getMockEmitter();

        $config = $this->config;
        $config['db1']['attributes'] = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
        ];

        $factory = new Factory($config, $emitter);

        // Suppress the warning which breaks the test
        $conn = @$factory->newConnection("db1");
        $pdo = $conn->getPDO();

        // Error mode should be exception, even though it is set to a different
        // value in the settings
        $expected = PDO::ERRMODE_EXCEPTION;
        $actual = $pdo->getAttribute(PDO::ATTR_ERRMODE);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Attribute PDO::ATTR_ERRMODE is set to something other than PDO::ERRMODE_EXCEPTION for database "db1". This is not allowed because Phormium depends on this setting. Skipping attribute definition.
     */
    public function testAttributesCannotChangeError()
    {
        $emitter = $this->getMockEmitter();

        $config = $this->config;
        $config['db1']['attributes'] = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
        ];

        $factory = new Factory($config, $emitter);
        $factory->newConnection("db1");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed setting PDO attribute "foo" to "bar" on database "db1".
     */
    public function testInvalidAttribute()
    {
        $emitter = $this->getMockEmitter();

        $config = $this->config;
        $config['db1']['attributes'] = ["foo" => "bar"];

        $factory = new Factory($config, $emitter);
        @$factory->newConnection("db1");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Database "db3" is not configured.
     */
    public function testNotConfiguredException()
    {
        $emitter = $this->getMockEmitter();
        $config = $this->config;

        $factory = new Factory($config, $emitter);
        $factory->newConnection("db3");
    }
}
