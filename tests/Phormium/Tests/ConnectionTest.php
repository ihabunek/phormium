<?php

namespace Phormium\Tests;

use Phormium\Connection;
use Phormium\Event;
use Phormium\Orm;
use Phormium\Query\QuerySegment;
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

        $segment = new QuerySegment("UPDATE person SET income = income + 1 WHERE name = '$name'");
        $numRows = $this->connection->execute($segment);

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
        $segment = new QuerySegment("No one would have believed");
        $this->connection->execute($segment);
    }

    public function testQuery()
    {
        $segment = new QuerySegment("SELECT count(*) as ct FROM person");
        $result = $this->connection->query($segment);

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
        $segment = new QuerySegment("in the last years of the nineteenth century");
        $this->connection->query($segment);
    }

    public function testPreparedQuery()
    {
        $name = uniqid();
        $p = Person::fromArray(compact('name'));
        $p->insert();

        $segment = new QuerySegment("SELECT * FROM person WHERE name like ?", [$name]);
        $actual = $this->connection->preparedQuery($segment);

        $expected = [
            [
                'id' => $p->id,
                'name' => $name,
                'email' => null,
                'birthday' => null,
                'created' => null,
                'income' => null,
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
        $segment = new QuerySegment("that human affairs were being watched");
        $this->connection->preparedQuery($segment);
    }

    // ******************************************
    // *** TESTING EVENTS                     ***
    // ******************************************

    public function testQueryEvents()
    {
        $segment = new QuerySegment("SELECT * FROM person");
        $this->connection->query($segment);

        $expected = [
            'query.started',
            'query.executing',
            'query.executed',
            'query.fetching',
            'query.fetched',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($segment);
    }

    public function testQueryEventsFailure()
    {
        $segment = new QuerySegment("from the timeless worlds of space.");

        try {
            $this->connection->query($segment);
        } catch (PDOException $ex) {

        }
        $expected = [
            'query.started',
            'query.executing',
            'query.error',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($segment);
    }

    public function testPreparedQueryEvents()
    {
        $segment = new QuerySegment("SELECT * FROM person WHERE name like ?", ['xxx']);

        $this->assertEmpty($this->triggeredEvents);

        $this->connection->preparedQuery($segment);

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
        $this->checkTriggeredEvents($segment);
    }

    public function testPreparedQueryGeneratorEvents()
    {
        $segment = new QuerySegment("SELECT * FROM person WHERE name like ?", ['xxx']);

        $class = "Phormium\\Tests\\Models\\Person";

        $this->assertEmpty($this->triggeredEvents);

        $generator = $this->connection->preparedQueryGenerator($segment, $class);

        foreach ($generator as $item) {
            $this->assertInstanceOf($class, $item);
        }

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
        $this->checkTriggeredEvents($segment);
    }

    public function testPreparedQueryEventsFailure()
    {
        $segment = new QuerySegment("No one could have dreamed");

        $errored = false;
        try {
            $this->connection->preparedQuery($segment);
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
        $this->checkTriggeredEvents($segment);
    }

    public function testExecuteEvents()
    {
        $segment = new QuerySegment("UPDATE person SET income = income + 1");

        $this->connection->execute($segment);
        $expected = [
            'query.started',
            'query.executing',
            'query.executed',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($segment);
    }

    public function testExecuteEventsFailure()
    {
        $segment = new QuerySegment("we were being scrutinized");

        $errored = false;
        try {
            $this->connection->execute($segment);
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
        $this->checkTriggeredEvents($segment);
    }

    public function testPreparedExecuteEvents()
    {
        $segment = new QuerySegment("UPDATE person SET income = income + 1 WHERE id = ?", [1]);

        $this->connection->preparedExecute($segment);
        $expected = [
            'query.started',
            'query.preparing',
            'query.prepared',
            'query.executing',
            'query.executed',
            'query.completed',
        ];

        $this->assertEquals($expected, $this->triggeredEvents);
        $this->checkTriggeredEvents($segment);
    }

    public function testPreparedExecuteEventsFailure()
    {
        $segment = new QuerySegment("as someone with a microscope studies creatures" .
            "that swarm and multiply in a drop of water");

        $errored = false;
        try {
            $this->connection->preparedExecute($segment);
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
        $this->checkTriggeredEvents($segment);
    }

    private function checkTriggeredEvents(QuerySegment $segment)
    {
        $this->assertSame(
            count($this->triggeredEvents),
            count($this->triggeredArguments)
        );

        foreach($this->triggeredEvents as $key => $event) {
            $tArgs = $this->triggeredArguments[$key];

            // Checks valid for all events
            $this->assertSame($segment->query(), $tArgs[0]);
            $this->assertSame($segment->args(), $tArgs[1]);
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
