<?php

namespace Phormium\Tests;

use Mockery as m;

use Phormium\Connection;
use Phormium\DB;

use Phormium\Tests\Models\Person;

/**
 * @group transaction
 */
class DbTest extends \PHPUnit_Framework_TestCase
{
    public function testSetConnection()
    {
        $mockConn = m::mock(Connection::class);
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
        $mockConn = m::mock(Connection::class);
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
}
