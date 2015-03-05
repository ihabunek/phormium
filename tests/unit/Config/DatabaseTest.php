<?php

namespace Phormium\Tests;

use Mockery as m;

use Phormium\Database\Database;

use PDO;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    private $config1 = [
        "db1" => [
            "dsn" => "sqlite:tmp/test.db",
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

    public function testSetConnection()
    {
        $conn = m::mock("Phormium\\Database\\Connection");
        $emitter = m::mock("Evenement\EventEmitter");

        $database = new Database([], $emitter);
        $database->setConnection('foo', $conn);

        $actual = $database->getConnection('foo');
        $this->assertSame($actual, $conn);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Connection "foo" is already connected.
     */
    public function testSetConnectionError()
    {
        $conn = m::mock("Phormium\\Database\\Connection");
        $emitter = m::mock("Evenement\EventEmitter");

        $database = new Database([], $emitter);
        $database->setConnection('foo', $conn);
        $database->setConnection('foo', $conn);
    }

    /**
     * @depends testSetConnectionError
     */
    public function testDisconnect()
    {
        $conn = m::mock("Phormium\\Database\\Connection");
        $emitter = m::mock("Evenement\EventEmitter");

        $database = new Database([], $emitter);
        $database->setConnection('foo', $conn);

        $this->assertTrue($database->isConnected('foo'));

        $conn->shouldReceive('inTransaction')
            ->once()
            ->andReturn(false);

        $database->disconnect('foo');
        $this->assertFalse($database->isConnected('foo'));

        $database->disconnect('foo');
        $this->assertFalse($database->isConnected('foo'));
    }

    public function testNewConnectionWithAttributes1()
    {
        $emitter = m::mock("Evenement\EventEmitter");

        $config = $this->config1;
        $config['db1']['attributes'] = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $database = new Database($config, $emitter);

        $conn = $database->getConnection("db1");
        $pdo = $conn->getPDO();

        $expected = PDO::FETCH_ASSOC;
        $actual = $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
        $this->assertSame($expected, $actual);
    }

    public function testNewConnectionWithAttributes2()
    {
        $emitter = m::mock("Evenement\EventEmitter");

        $config = $this->config1;
        $config['db1']['attributes'] = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH
        ];

        $database = new Database($config, $emitter);

        $conn = $database->getConnection("db1");
        $pdo = $conn->getPDO();

        $this->assertSame(PDO::FETCH_BOTH, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testAttributesCannotChange()
    {
        $emitter = m::mock("Evenement\EventEmitter");

        $config = $this->config1;
        $config['db1']['attributes'] = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
        ];

        $database = new Database($config, $emitter);

        // Suppress the warning which breaks the test
        $conn = @$database->getConnection("db1");
        $pdo = $conn->getPDO();

        // Error mode should be exception, even though it is set to a different
        // value in the settings
        $this->assertSame(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Attribute PDO::ATTR_ERRMODE is set to something other than PDO::ERRMODE_EXCEPTION for database "db1". This is not allowed because Phormium depends on this setting. Skipping attribute definition.
     */
    public function testAttributesCannotChangeError()
    {
        $emitter = m::mock("Evenement\EventEmitter");

        $config = $this->config1;
        $config['db1']['attributes'] = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
        ];

        $database = new Database($config, $emitter);

        // Test the warning is emitted
        $database->getConnection("db1");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed setting PDO attribute "foo" to "bar" on database "db1".
     */
    public function testInvalidAttribute()
    {
        $emitter = m::mock("Evenement\EventEmitter");

        $config = $this->config1;
        $config['db1']['attributes'] = ["foo" => "bar"];

        $database = new Database($config, $emitter);

        @$database->getConnection("db1");
    }
}
