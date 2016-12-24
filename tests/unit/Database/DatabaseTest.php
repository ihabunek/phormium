<?php

namespace Phormium\Tests\Unit\Database;

use Evenement\EventEmitter;
use Mockery as m;
use Phormium\Database\Database;
use Phormium\Database\Factory;
use Phormium\Event;

/**
 * @group unit
 * @group database
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    protected function getMockEmitter()
    {
        $emitter = m::mock("Evenement\\EventEmitter");

        $emitter->shouldReceive('on')->once()
            ->with(Event::QUERY_STARTED, m::type('callable'));

        $emitter->shouldReceive('emit');

        return $emitter;
    }

    protected function newDatabase(Factory $factory = null, EventEmitter $emitter = null)
    {
        if (!isset($factory)) {
            $factory = m::mock("Phormium\\Database\\Factory");
        }

        if (!isset($emitter)) {
            $emitter = $this->getMockEmitter();
        }

        return new Database($factory, $emitter);
    }

    public function testSetConnection()
    {
        $conn = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('foo', $conn);

        $actual = $database->getConnection('foo');
        $this->assertSame($actual, $conn);
    }

    /**
     * @expectedException Phormium\Exception\DatabaseException
     * @expectedExceptionMessage Connection "foo" is already connected.
     */
    public function testSetConnectionError()
    {
        $conn = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('foo', $conn);
        $database->setConnection('foo', $conn);
    }

    /**
     * @depends testSetConnectionError
     */
    public function testDisconnect()
    {
        $conn = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('foo', $conn);

        $this->assertTrue($database->isConnected('foo'));

        $conn->shouldReceive('inTransaction')->once()->andReturn(false);

        $database->disconnect('foo');
        $this->assertFalse($database->isConnected('foo'));

        $database->disconnect('foo');
        $this->assertFalse($database->isConnected('foo'));

        // Check rollback
        $conn->shouldReceive('inTransaction')->once()->andReturn(true);
        $conn->shouldReceive('rollback')->once();
        $database->setConnection('foo', $conn);
        $database->disconnect('foo');
    }

    public function testDisconnectAll()
    {
        $conn1 = m::mock("Phormium\\Database\\Connection");
        $conn2 = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('db1', $conn1);
        $database->setConnection('db2', $conn2);
        $database->begin();

        // In transaction is checked twice, once by disconnectAll(), once by disconnect()
        $conn1->shouldReceive('inTransaction')->once()->andReturn(false);
        $conn1->shouldReceive('inTransaction')->once()->andReturn(false);
        $conn2->shouldReceive('inTransaction')->once()->andReturn(true);
        $conn2->shouldReceive('inTransaction')->once()->andReturn(false);
        $conn2->shouldReceive('rollback')->once();

        $database->disconnectAll();
    }

    /**
     * @expectedException Phormium\Exception\DatabaseException
     * @expectedExceptionMessage Already in transaction.
     */
    public function testBeginTwice()
    {
        $database = $this->newDatabase();
        $database->begin();
        $database->begin();
    }

    public function testTransactionCommit()
    {
        $conn1 = m::mock("Phormium\\Database\\Connection");
        $conn2 = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('db1', $conn1);
        $database->setConnection('db2', $conn2);

        $this->assertFalse($database->beginTriggered());
        $database->begin();
        $this->assertTrue($database->beginTriggered());

        $conn1->shouldReceive('inTransaction')->once()->andReturn(false);
        $conn2->shouldReceive('inTransaction')->once()->andReturn(true);
        $conn2->shouldReceive('commit')->once();

        $this->assertTrue($database->beginTriggered());
        $database->commit();
        $this->assertFalse($database->beginTriggered());
    }

    /**
     * @expectedException Phormium\Exception\DatabaseException
     * @expectedExceptionMessage Cannot commit. Not in transaction.
     */
    public function testCommitOutsideOfTransaction()
    {
        $database = $this->newDatabase();
        $database->commit();
    }

    public function testTransactionRollback()
    {
        $conn1 = m::mock("Phormium\\Database\\Connection");
        $conn2 = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('db1', $conn1);
        $database->setConnection('db2', $conn2);

        $this->assertFalse($database->beginTriggered());
        $database->begin();
        $this->assertTrue($database->beginTriggered());

        $conn1->shouldReceive('inTransaction')->once()->andReturn(false);
        $conn2->shouldReceive('inTransaction')->once()->andReturn(true);
        $conn2->shouldReceive('rollback')->once();

        $this->assertTrue($database->beginTriggered());
        $database->rollback();
        $this->assertFalse($database->beginTriggered());
    }

    /**
     * @expectedException Phormium\Exception\DatabaseException
     * @expectedExceptionMessage Cannot roll back. Not in transaction.
     */
    public function testRollbackOutsideOfTransaction()
    {
        $database = $this->newDatabase();
        $database->rollback();
    }

    public function testTransactionCallback()
    {
        $conn = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('db', $conn);

        $conn->shouldReceive('inTransaction')->once()->andReturn(true);
        $conn->shouldReceive('commit')->once();

        $this->assertFalse($database->beginTriggered());

        $database->transaction(function () use ($database) {
            $this->assertTrue($database->beginTriggered());
        });

        $this->assertFalse($database->beginTriggered());
    }

    /**
     * @expectedException Phormium\Exception\DatabaseException
     * @expectedExceptionMessage Transaction failed. Rolled back.
     */
    public function testTransactionCallbackRollback()
    {
        $conn = m::mock("Phormium\\Database\\Connection");

        $database = $this->newDatabase();
        $database->setConnection('db', $conn);

        $conn->shouldReceive('inTransaction')->once()->andReturn(true);
        $conn->shouldReceive('rollback')->once();

        $this->assertFalse($database->beginTriggered());

        $database->transaction(function () use ($database) {
            $this->assertTrue($database->beginTriggered());
            throw new \Exception("#fail");
        });
    }

    public function testGetConnection()
    {
        $conn = m::mock("Phormium\\Database\\Connection");

        $factory = m::mock("Phormium\\Database\\Factory");
        $factory->shouldReceive('newConnection')
            ->once()
            ->with('db1')
            ->andReturn($conn);

        $database = $this->newDatabase($factory);

        $conn1 = $database->getConnection("db1");
        $this->assertSame($conn, $conn1);

        $conn2 = $database->getConnection("db1");
        $this->assertSame($conn, $conn2);
    }
}
