<?php

namespace Phormium\Tests;

use Phormium\Connection;
use \Phormium\DB;
use \Phormium\Meta;
use \Phormium\QuerySet;
use \Phormium\Tests\Models\Person;
use \Phormium\Tests\Models\Trade;
use \Phormium\Tests\Models\PkLess;

/**
 * @group connection
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testExecute()
    {
        $connection = DB::getConnection('testdb');
        $this->recreatePersons($connection);

        $p = new Person();
        $p->name = 'Test Person Execution';
        $p->email = 'test.person.exec@example.com';
        $p->save();
        self::assertNotNull($p->id);

        $updateId = $p->id;

        $connection->execute("UPDATE person SET income = 100 WHERE id = $updateId");

        /** @var Person $person */
        $person = Person::get($updateId);

        self::assertEquals(100, $person->income);
    }

    public function testQuery()
    {
        $connection = DB::getConnection('testdb');
        $this->recreatePersons($connection);

        $result = $connection->query("SELECT count(*) as ct FROM person");
        $result = current($result);
        self::assertEquals(4, $result['ct']);
    }

    public function testPreparedQuery()
    {
        $connection = DB::getConnection('testdb');

        $this->recreatePersons($connection);

        $p = new Person();
        $p->name = 'Test Person Execution';
        $p->email = 'test.person.exec@example.com';
        $p->save();
        self::assertNotNull($p->id);

        $arguments = array("Test Person Execution");

        $result = $connection->preparedQuery("SELECT * FROM person WHERE name like ?", $arguments);
        $result = current($result);
        self::assertEquals("test.person.exec@example.com", $result['email']);
    }

    /**
     * @param $connection
     */
    public function recreatePersons(Connection $connection) {
        $connection->execute("DELETE FROM person");

        $p = new Person();
        $p->name = 'Test Person';
        $p->email = 'test.person@example.com';
        $p->save();
        self::assertNotNull($p->id);

        $p = new Person();
        $p->name = 'Test Person 2';
        $p->email = 'test.person2@example.com';
        $p->save();
        self::assertNotNull($p->id);

        $p = new Person();
        $p->name = 'Test Person 3';
        $p->email = 'test.person3@example.com';
        $p->save();
        self::assertNotNull($p->id);

        $p = new Person();
        $p->name = 'Test Person 4';
        $p->email = 'test.person4@example.com';
        $p->save();
        self::assertNotNull($p->id);
    }
}
