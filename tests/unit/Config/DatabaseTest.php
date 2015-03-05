<?php

namespace Phormium\Tests;

use Evenement\EventEmitter;

use Mockery as m;

use Phormium\Database\Database;
use Phormium\Event;

use PDO;

class DatabaseTest extends \PHPUnit_Framework_TestCase
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

        $emitter->shouldReceive('on')->once()
            ->with(Event::QUERY_STARTED, m::type('callable'));

        $emitter->shouldReceive('emit');

        return $emitter;
    }

    protected function getDatabase(array $config = null, EventEmitter $emitter = null)
    {
        if (!isset($config)) {
            $config = $this->config;
        }

        if (!isset($emitter)) {
            $emitter = $this->getMockEmitter();
        }

        return new Database($config, $emitter);
    }

    public function testSetConnection()
    {
        $conn = m::mock("Phormium\\Database\\Connection");
        $emitter = $this->getMockEmitter();

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
        $emitter = $this->getMockEmitter();

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
        $emitter = $this->getMockEmitter();

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
        $emitter = $this->getMockEmitter();

        $config = $this->config;
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
        $emitter = $this->getMockEmitter();

        $config = $this->config;
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
        $emitter = $this->getMockEmitter();

        $config = $this->config;
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
        $emitter = $this->getMockEmitter();

        $config = $this->config;
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
        $emitter = $this->getMockEmitter();

        $config = $this->config;
        $config['db1']['attributes'] = ["foo" => "bar"];

        $database = new Database($config, $emitter);

        @$database->getConnection("db1");
    }

    public function testTransaction()
    {
        $emitter = new EventEmitter();
        $database = new Database($this->config, $emitter);

        $database->begin();

        $db1 = $database->getConnection('db1');
        $db2 = $database->getConnection('db2');

        // Check db transaction is not started by starting a global transaction
        $this->assertTrue($database->beginTriggered());
        $this->assertFalse($db1->inTransaction());
        $this->assertFalse($db2->inTransaction());

        // Check db transaction is started when executing a query
        $db1->query("SELECT 1");

        $this->assertTrue($database->beginTriggered());
        $this->assertTrue($db1->inTransaction());
        $this->assertFalse($db2->inTransaction());

        $db2->query("SELECT 2");

        $this->assertTrue($database->beginTriggered());
        $this->assertTrue($db1->inTransaction());
        $this->assertTrue($db2->inTransaction());

        $database->commit();

        $this->assertFalse($database->beginTriggered());
        $this->assertFalse($db1->inTransaction());
        $this->assertFalse($db2->inTransaction());
    }
}
