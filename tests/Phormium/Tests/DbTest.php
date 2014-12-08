<?php

namespace Phormium\Tests;

use Mockery as m;

use Phormium\DB;
use Phormium\Tests\Models\Person;

use PDO;

/**
 * @group transaction
 */
class DbTest extends \PHPUnit_Framework_TestCase
{
    public function testSetConnection()
    {
        $mockConn = m::mock("Phormium\\Connection");
        $mockConn->shouldReceive('inTransaction')
            ->once()
            ->andReturn(false);

        DB::setConnection('foo', $mockConn);

        $actual = DB::getConnection('foo');
        $this->assertSame($actual, $mockConn);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Connection "foo" is already connected.
     */
    public function testSetConnectionError()
    {
        $mockConn = m::mock("Phormium\\Connection");
        $mockConn->shouldReceive('inTransaction')
            ->once()
            ->andReturn(false);

        DB::setConnection('foo', $mockConn);
        DB::setConnection('foo', $mockConn);
    }

    /**
     * @depends testSetConnectionError
     */
    public function testDisconnect()
    {
        $this->assertTrue(DB::isConnected('foo'));
        DB::disconnect('foo');
        $this->assertFalse(DB::isConnected('foo'));
        DB::disconnect('foo');
        $this->assertFalse(DB::isConnected('foo'));
    }

    public function testNewConnectionWithAttributes1()
    {
        DB::configure([
            "databases" => [
                "db1" => [
                    "dsn" => "sqlite:tmp/test.db",
                    "username" => "myuser",
                    "password" => "mypass",
                    "attributes" => [
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                ]
            ]
        ]);

        $conn = DB::getConnection("db1");
        $pdo = $conn->getPDO();

        $this->assertSame(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testNewConnectionWithAttributes2()
    {
        DB::configure([
            "databases" => [
                "db1" => [
                    "dsn" => "sqlite:tmp/test.db",
                    "username" => "myuser",
                    "password" => "mypass",
                    "attributes" => [
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH
                    ]
                ]
            ]
        ]);

        $conn = DB::getConnection("db1");
        $pdo = $conn->getPDO();

        $this->assertSame(PDO::FETCH_BOTH, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testAttributesCannotChange()
    {
        DB::configure([
            "databases" => [
                "db1" => [
                    "dsn" => "sqlite:tmp/test.db",
                    "username" => "myuser",
                    "password" => "mypass",
                    "attributes" => [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
                    ]
                ]
            ]
        ]);

        // Suppress the warning which breaks the test
        $conn = @DB::getConnection("db1");
        $pdo = $conn->getPDO();

        // Error mode should be exception, even though it is set to a different
        // value in the settings
        $this->assertSame(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Phormium: On connection db1, attribute PDO::ATTR_ERRMODE is set to something other than PDO::ERRMODE_EXCEPTION. This is not allowed because Phormium depends on this setting. Skipping attribute definition.
     */
    public function testAttributesCannotChangeError()
    {
        DB::configure([
            "databases" => [
                "db1" => [
                    "dsn" => "sqlite:tmp/test.db",
                    "username" => "myuser",
                    "password" => "mypass",
                    "attributes" => [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
                    ]
                ]
            ]
        ]);

        // Test the warning is emitted
        DB::getConnection("db1");
    }
}
