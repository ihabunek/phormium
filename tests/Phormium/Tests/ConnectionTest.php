<?php

namespace Phormium\Tests;

use Phormium\Connection;
use Phormium\Orm;
use Phormium\Event;
use Phormium\Tests\Models\Person;

use PDOException;

/**
 * @group connection
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $driver;

    public $triggeredEvents = [];
    public $triggeredArguments = [];

    private $queryEvents = [
        'query.started',
        'query.preparing',
        'query.prepared',
        'query.executing',
        'query.executed',
        'query.fetching',
        'query.fetched',
        'query.completed',
        'query.error',
    ];

    public static function setUpBeforeClass()
    {
        Orm::configure(PHORMIUM_CONFIG_FILE);
    }

    public function setUp()
    {
        $this->connection = Orm::database()->getConnection('testdb');
        $this->driver = $this->connection->getDriver();

        // Clean up events before every test
        $this->triggeredEvents = [];
        $this->triggeredArguments = [];

        $that = $this;
        foreach ($this->queryEvents as $event) {
            Orm::emitter()->removeAllListeners($event);
            Orm::emitter()->on($event, function() use ($event, $that) {
                $that->triggeredEvents[] = $event;
                $that->triggeredArguments[] = func_get_args();
            });
        }
    }

    public function testExecute()
    {
        $name = uniqid();
        $income = 100;

        $p1 = Person::fromArray(compact('name', 'income'));
        $p2 = Person::fromArray(compact('name', 'income'));
        $p3 = Person::fromArray(compact('name', 'income'));

        $p1->insert();
        $p2->insert();
        $p3->insert();

        $numRows = $this->connection->execute("UPDATE person SET income = income + 1 WHERE name = '$name'");

        $p1a = Person::get($p1->id);
        $p2a = Person::get($p2->id);
        $p3a = Person::get($p3->id);

        $this->assertEquals(101, $p1a->income);
        $this->assertEquals(101, $p2a->income);
        $this->assertEquals(101, $p3a->income);

        $this->assertSame(3, $numRows);
    }

    /**
     * @expectedException PDOException
     */
    public function testExecuteFailure()
    {
        $this->connection->execute("No one would have believed");
    }

    public function testQuery()
    {
        $result = $this->connection->query("SELECT count(*) as ct FROM person");

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertInternalType('array', $result[0]);
        $this->assertArrayHasKey('ct', $result[0]);
        $this->assertTrue(is_numeric($result[0]['ct']));
    }

    /**
     * @expectedException PDOException
     */
    public function testQueryFailure()
    {
        $this->connection->query("in the last years of the nineteenth century");
    }

    public function testPreparedQuery()
    {
        $name = uniqid();
        $p = Person::fromArray(compact('name'));
        $p->insert();

        $actual = $this->connection->preparedQuery("SELECT * FROM person WHERE name like ?", [$name]);

        $expected = [
            [
                'id' => $p->id,
                'name' => $name,
                'email' => NULL,
                'birthday' => NULL,
                'created' => NULL,
                'income' => NULL,
            ]
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException PDOException
     * @group 123
     */
    public function testPreparedQueryFailure()
    {
        $this->connection->preparedQuery("that human affairs were being watched", []);
    }

    // ******************************************
    // *** TESTING EVENTS                     ***
    // ******************************************

    public function testQueryEvents()
    {
        $query = "SELECT * FROM person";
        $arguments = [];

        $this->connection->query($query);

        $expected = [
            'query.started',
            'query.executing',
            'query.executed',
            'query.fetching',
            'query.fetched',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testQueryEventsFailure()
    {
        $query = "from the timeless worlds of space.";
        $arguments = [];

        try {
            $this->connection->query($query);
        } catch (PDOException $ex) {

        }
        $expected = [
            'query.started',
            'query.executing',
            'query.error',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testPreparedQueryEvents()
    {
        $query = "SELECT * FROM person WHERE name like ?";
        $arguments = ['xxx'];

        $this->connection->preparedQuery($query, $arguments);
        $expected = [
            'query.started',
            'query.preparing',
            'query.prepared',
            'query.executing',
            'query.executed',
            'query.fetching',
            'query.fetched',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testPreparedQueryEventsFailure()
    {
        $query = "No one could have dreamed";
        $arguments = [];

        $errored = false;
        try {
            $this->connection->preparedQuery($query);
        } catch (PDOException $ex) {
            $errored = true;
        }
        $this->assertTrue($errored);

        // sqlite and informix fail on prepare, others on execute
        if (in_array($this->driver, ['sqlite', 'informix'])) {
            $expected = [
                'query.started',
                'query.preparing',
                'query.error',
            ];
        } else {
            $expected = [
                'query.started',
                'query.preparing',
                'query.prepared',
                'query.executing',
                'query.error',
            ];
        }

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testExecuteEvents()
    {
        $query = "UPDATE person SET income = income + 1";
        $arguments = [];

        $this->connection->execute($query);
        $expected = [
            'query.started',
            'query.executing',
            'query.executed',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testExecuteEventsFailure()
    {
        $query = "we were being scrutinized";
        $arguments = [];

        $errored = false;
        try {
            $this->connection->execute($query);
        } catch (PDOException $ex) {
            $errored = true;
        }
        $this->assertTrue($errored);

        $expected = [
            'query.started',
            'query.executing',
            'query.error',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testPreparedExecuteEvents()
    {
        $query = "UPDATE person SET income = income + 1 WHERE id = ?";
        $arguments = [1];

        $this->connection->preparedExecute($query, $arguments);
        $expected = [
            'query.started',
            'query.preparing',
            'query.prepared',
            'query.executing',
            'query.executed',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    public function testPreparedExecuteEventsFailure()
    {
        $query = "as someone with a microscope studies creatures" .
            "that swarm and multiply in a drop of water";
        $arguments = [];

        $errored = false;
        try {
            $this->connection->preparedExecute($query, $arguments);
        } catch (PDOException $ex) {
            $errored = true;
        }
        $this->assertTrue($errored);

        // sqlite and informix fail on prepare, others on execute
        if (in_array($this->driver, ['sqlite', 'informix'])) {
            $expected = [
                'query.started',
                'query.preparing',
                'query.error',
            ];
        } else {
            $expected = [
                'query.started',
                'query.preparing',
                'query.prepared',
                'query.executing',
                'query.error',
            ];
        }

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($query, $arguments);
    }

    private function checkTriggeredEvents($query, $arguments)
    {
        $this->assertSame(
            count($this->triggeredEvents),
            count($this->triggeredArguments)
        );

        foreach($this->triggeredEvents as $key => $event) {
            $tArgs = $this->triggeredArguments[$key];

            // Checks valid for all events
            $this->assertSame($query, $tArgs[0]);
            $this->assertSame($arguments, $tArgs[1]);
            $this->assertInstanceOf("Phormium\\Database\\Connection", $tArgs[2]);

            // Check event argument count
            switch ($event) {
                case "query.error":
                case "query.completed":
                    $this->assertCount(4, $tArgs, "Wrong argument count for event $event");
                    break;
                default:
                    $this->assertCount(3, $tArgs, "Wrong argument count for event $event");
                    break;
            }

            // Event specific checks
            if ($event === 'query.error') {
                $this->assertInstanceOf("PDOException", $tArgs[3]);
            }
        }
    }
}
