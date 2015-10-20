<?php

namespace Phormium\Tests;


use Phormium\Orm;

use Phormium\Tests\Models\Person;

/**
 * @group transaction
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        Orm::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testManualBeginCommit()
    {
        $person = new Person();
        $person->name = 'Bruce Dickinson';
        $person->income = 12345;
        $person->save();

        $id = $person->id;

        Orm::begin();

        $p = Person::get($id);
        $p->income = 54321;
        $p->save();

        Orm::commit();

        $this->assertEquals(54321, Person::get($id)->income);
    }

    public function testManualBeginRollback()
    {
        $person = new Person();
        $person->name = 'Steve Harris';
        $person->income = 12345;
        $person->save();

        $id = $person->id;

        Orm::begin();

        $p = Person::get($id);
        $p->income = 54321;
        $p->save();

        Orm::rollback();

        $this->assertEquals(12345, Person::get($id)->income);
    }

    public function testCallbackTransactionCommit()
    {
        $person = new Person();
        $person->name = 'Dave Murray';
        $person->income = 12345;
        $person->save();

        $id = $person->id;

        Orm::transaction(function() use ($id) {
            $p = Person::get($id);
            $p->income = 54321;
            $p->save();
        });

        $this->assertEquals(54321, Person::get($id)->income);
    }

    public function testCallbackTransactionRollback()
    {
        $person = new Person();
        $person->name = 'Adrian Smith';
        $person->income = 12345;
        $person->save();

        $id = $person->id;

        try {
            Orm::transaction(function() use ($id) {
                $p = Person::get($id);
                $p->income = 54321;
                $p->save();

                throw new \Exception("Aborting");
            });

            self::fail("This code should not be reachable.");

        } catch (\Exception $ex) {
            // Expected. Do nothing.
        }

        // Check changes have been rolled back
        $this->assertEquals(12345, Person::get($id)->income);
    }

    public function testDisconnectRollsBackTransaction()
    {
        $person = new Person();
        $person->name = 'Nicko McBrain';
        $person->income = 12345;
        $person->save();

        $id = $person->id;

        Orm::begin();

        $p = Person::get($id);
        $p->income = 54321;
        $p->save();

        // This should roll back changes
        Orm::database()->disconnect('testdb');

        // So they won't be commited here
        Orm::commit();

        $this->assertEquals(12345, Person::get($id)->income);
    }

    public function testDisconnectAllRollsBackTransaction()
    {
        $person = new Person();
        $person->name = 'Nicko McBrain';
        $person->income = 12345;
        $person->save();

        $id = $person->id;

        Orm::begin();

        $p = Person::get($id);
        $p->income = 54321;
        $p->save();

        Orm::database()->disconnectAll();

        $this->assertEquals(12345, Person::get($id)->income);
    }

    public function testExecuteTransaction()
    {
        $person = new Person();
        $person->name = 'Janick Gers';
        $person->income = 100;
        $person->insert();

        $id = $person->id;
        $conn = Orm::database()->getConnection('testdb');

        Orm::begin();
        $conn->execute("UPDATE person SET income = income + 1");
        Orm::rollback();

        $this->assertEquals(100, Person::get($id)->income);

        Orm::begin();
        $conn->execute("UPDATE person SET income = income + 1");
        Orm::commit();

        $this->assertEquals(101, Person::get($id)->income);
    }

    public function testPreparedExecuteTransaction()
    {
        $person = new Person();
        $person->name = 'Janick Gers';
        $person->income = 100;
        $person->insert();

        $id = $person->id;
        $conn = Orm::database()->getConnection('testdb');

        Orm::begin();
        $conn->preparedExecute("UPDATE person SET income = ?", [200]);
        Orm::rollback();

        $this->assertEquals(100, Person::get($id)->income);

        Orm::begin();
        $conn->preparedExecute("UPDATE person SET income = ?", [200]);
        Orm::commit();

        $this->assertEquals(200, Person::get($id)->income);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot roll back. Not in transaction.
     */
    public function testRollbackBeforeBegin()
    {
        Orm::rollback();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot commit. Not in transaction.
     */
    public function testCommitBeforeBegin()
    {
        Orm::commit();
    }

    public function testDoubleBegin()
    {
        Orm::begin();

        try {
            Orm::begin();
            $this->fail('Expected an exception here.');
        } catch (\Exception $e) {
            $this->assertContains("Already in transaction.", $e->getMessage());
        }

        Orm::rollback();
    }
}
