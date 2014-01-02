<?php

namespace Phormium\Tests;

use \Phormium\DB;
use \Phormium\Meta;
use \Phormium\QuerySet;
use \Phormium\Tests\Models\Person;
use \Phormium\Tests\Models\Trade;
use \Phormium\Tests\Models\PkLess;

/**
 * @group model
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testNewPerson()
    {
        $p = new Person();
        $p->name = 'Test Person';
        $p->email = 'test.person@example.com';

        $this->assertNull($p->id);
        $p->save();
        $this->assertNotNull($p->id);

        $id = $p->id;

        // Load it from the database
        $p2 = Person::get($id);
        $this->assertEquals($p, $p2);

        // Alternative get
        $p3 = Person::get(array($id));
        $this->assertEquals($p, $p3);
    }

    public function testNewTrade()
    {
        $date = '2013-07-17';
        $no = 12345;

        // Delete if it exists
        Trade::objects()
            ->filter('tradedate', '=', $date)
            ->filter('tradeno', '=', $no)
            ->delete();

        $t = new Trade();
        $t->tradedate = $date;
        $t->tradeno = $no;
        $t->price = 123.45;
        $t->quantity = 321;

        // Check insert does not change the object
        $t0 = clone $t;

        $t->insert();

        $this->assertEquals($t, $t0);

        // Load it from the database
        $t2 = Trade::get($date, $no);
        $this->assertEquals($t, $t2);

        // Alternative get
        $t3 = Trade::get(array($date, $no));
        $this->assertEquals($t, $t3);
    }

    public function testNewPersonAssignedPK()
    {
        $id = 100;

        // Delete person id 100 if it already exists
        Person::objects()->filter('id', '=', $id)->delete();

        $p = new Person();
        $p->id = $id;
        $p->name = 'Test Person';
        $p->email = 'test.person@example.com';
        $p->save();

        $this->assertEquals($id, $p->id);

        // Load it from the database
        $p2 = Person::get($id);
        $this->assertEquals($p, $p2);
    }

    public function testNewPersonFromArray()
    {
        $p = Person::fromArray(
            array(
                'name' => 'Peter Peterson',
                'email' => 'peter@peterson.com'
            )
        );

        // Perform INSERT
        $p->save();
        $this->assertNotNull($p->id);

        $id = $p->id;

        // Load it from the database
        $p2 = Person::get($id);
        $this->assertEquals($p, $p2);

        // Perform UPDATE
        $p2->email = 'peter2@peterson.com';
        $p2->save();

        // Load from DB
        $p3 = Person::get($id);
        $this->assertEquals($p2, $p3);
        $this->assertEquals($id, $p3->id);
    }

    public function testFromJSON()
    {
        $actual = Person::fromJSON(
            '{"id":"101","name":"Jack Jackson","email":"jack@jackson.org","birthday":"1980-03-14",' .
            '"created":"2000-03-07 10:45:13","income":"12345.67"}'
        );

        $expected = new Person();
        $expected->id = 101;
        $expected->name = 'Jack Jackson';
        $expected->email = 'jack@jackson.org';
        $expected->birthday = '1980-03-14';
        $expected->created = '2000-03-07 10:45:13';
        $expected->income = 12345.67;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid JSON string
     */
    public function testFromJSONError()
    {
        Person::fromJSON('[[[');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given argument is not an array
     */
    public function testInvalidData()
    {
        $p = Person::fromArray('invalid data');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Property [xxx] does not exist in class [Phormium\Tests\Models\Person]
     */
    public function testInvalidProperty()
    {
        $p = Person::fromArray(
            array(
                'name' => 'Peter Peterson',
                'xxx' => 'peter@peterson.com' // doesn't exist
            ),
            true
        );
    }

    public function testDelete()
    {
        $person1 = new Person();
        $person1->name = 'Short Lived Person';
        $person1->save();

        $qs = Person::objects()->filter('id', '=', $person1->id);
        $person2 = $qs->single();

        $this->assertEquals($person1, $person2);
        $this->assertTrue($qs->exists());

        $count = $person1->delete();
        $this->assertFalse($qs->exists());
        $this->assertSame(1, $count);

        // On repeated delete, 0 count should be returned
        $count = $person1->delete();
        $this->assertSame(0, $count);
    }

    /**
     * This test case currently fails on MySQL.
     * The $update instance holds a "string" id, but
     * update requires an int id.
     *
     * For MySQL it needs to read:
     *
     * $update = Person::get($person1->id);
     * $update->id = (int)$update->id;
     * ...
     * $update->save();
     */
    public function testSelectAndUpdate()
    {
        $person1 = new Person();
        $person1->name = 'Short Lived Person';
        $person1->save();

        $update = Person::get($person1->id);
        $update->name = 'Long Lived Person';
        $update->save();
    }

    public function testLimit()
    {
        $allPeople = Person::objects()
            ->orderBy('id', 'asc')
            ->fetch();

        $limit = 3;
        $offset = 2;

        $expected = array_slice($allPeople, $offset, $limit);
        $actual = Person::objects()
            ->orderBy('id', 'asc')
            ->limit($limit, $offset)
            ->fetch();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Check single() fails when no records match.
     * @expectedException \Exception
     * @expectedExceptionMessage Query returned 0 rows. Requested a single row.
     */
    public function testSingleZero()
    {
        $qs = Person::objects()->filter('name', '=', 'Hrvoje');
        $qs->delete();

        $this->assertSame(0, $qs->count());
        $this->assertFalse($qs->exists());

        Person::objects()->filter('name', '=', 'Hrvoje')->single();
    }

    public function testSingleZeroAllowed()
    {
        $qs = Person::objects()->filter('name', '=', 'The Invisible Man');
        $qs->delete();

        $this->assertSame(0, $qs->count());
        $this->assertFalse($qs->exists());

        $actual = $qs->single(true);
        $this->assertNull($actual);
    }

    /**
     * Check single() fails when multiple records match.
     * @expectedException \Exception
     * @expectedExceptionMessage Query returned 3 rows. Requested a single row.
     */
    public function testSingleMultiple()
    {
        $qs = Person::objects()->filter('name', '=', 'Hrvoje');
        $qs->delete();

        $this->assertSame(0, $qs->count());
        $this->assertFalse($qs->exists());

        $data = array('name' => 'Hrvoje');
        Person::fromArray($data)->save();
        Person::fromArray($data)->save();
        Person::fromArray($data)->save();

        $this->assertSame(3, $qs->count());
        $this->assertTrue($qs->exists());

        $qs->single();
    }

    /**
     * Method fromArray() should also handle stdClass objects.
     */
    public function testFromObject()
    {
        $array = array('name' => 'Kiki', 'income' => 123.45);
        $object = (object) $array;

        $p1 = Person::fromArray($array);
        $p2 = Person::fromArray($object);

        $this->assertEquals($p1, $p2);
    }

    /**
     * Get doesn't work on models without a primary key.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage  Primary key not defined for model [Phormium\Tests\Models\PkLess].
     */
    public function testGetErrorOnPKLess()
    {
        PkLess::get(1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Model [Phormium\Tests\Models\Person] has 1 primary key columns. 3 arguments given.
     */
    public function testGetErrorWrongPKCount()
    {
        Person::get(1, 2, 3);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage [Phormium\Tests\Models\Person] record with primary key [12345678] does not exist.
     */
    public function testGetErrorModelDoesNotExist()
    {
        Person::get(12345678);
    }

    public function testFind()
    {
        $this->assertNull(Person::find(12345678));

        $p = new Person();
        $p->name = "Jimmy Hendrix";
        $p->insert();

        $p2 = Person::find($p->id);
        $this->assertNotNull($p2);
        $this->assertEquals($p, $p2);
    }

    public function testExists()
    {
        $this->assertFalse(Person::exists(12345678));

        $p = new Person();
        $p->name = "Jimmy Page";
        $p->insert();

        $actual = Person::exists($p->id);
        $this->assertTrue($actual);
    }

    public function testGetPK()
    {
        $foo = new Person();
        $this->assertCount(1, $foo->getPK());

        $foo = new PkLess();
        $this->assertCount(0, $foo->getPK());

        $foo = new Trade();
        $this->assertCount(2, $foo->getPK());
    }

    public function testFetchDistinct()
    {
        $name = uniqid();

        Person::fromArray(array('name' => $name, 'income' => 100))->insert();
        Person::fromArray(array('name' => $name, 'income' => 100))->insert();
        Person::fromArray(array('name' => $name, 'income' => 100))->insert();
        Person::fromArray(array('name' => $name, 'income' => 200))->insert();
        Person::fromArray(array('name' => $name, 'income' => 200))->insert();
        Person::fromArray(array('name' => $name, 'income' => 200))->insert();

        $actual = Person::objects()
            ->filter('name', '=', $name)
            ->orderBy('income', 'asc')
            ->distinct('name', 'income');

        $expected = array(
            array(
                'name' => $name,
                'income' => 100,
            ),
            array (
                'name' => $name,
                'income' => 200,
            ),
        );
        $this->assertEquals($expected, $actual);

        $actual = Person::objects()
            ->filter('name', '=', $name)
            ->orderBy('income', 'asc')
            ->distinct('income');

        $expected = array(100, 200);
        $this->assertEquals($expected, $actual);
    }

    public function testFetchValues()
    {
        $name = uniqid();

        Person::fromArray(array('name' => "$name-1", 'income' => 100))->insert();
        Person::fromArray(array('name' => "$name-2", 'income' => 200))->insert();
        Person::fromArray(array('name' => "$name-3", 'income' => 300))->insert();

        $actual = Person::objects()
            ->filter('name', 'LIKE', "$name%")
            ->orderBy('name', 'asc')
            ->values('name', 'income');

        $expected = array(
            array('name' => "$name-1", 'income' => 100),
            array('name' => "$name-2", 'income' => 200),
            array('name' => "$name-3", 'income' => 300),
        );

        $this->assertEquals($expected, $actual);

        $actual = Person::objects()
            ->filter('name', 'LIKE', "$name%")
            ->orderBy('name', 'asc')
            ->valuesList('name', 'income');

        $expected = array(
            array("$name-1", 100),
            array("$name-2", 200),
            array("$name-3", 300),
        );

        $this->assertEquals($expected, $actual);

        $actual = Person::objects()
            ->filter('name', 'LIKE', "$name%")
            ->orderBy('name', 'asc')
            ->valuesFlat('name');

         $expected = array(
            "$name-1",
            "$name-2",
            "$name-3",
        );

        $this->assertEquals($expected, $actual);
    }

    public function testModelToJsonToArray()
    {
        $person = Person::fromArray(array(
            'name' => "Michael Kiske",
            'email' => "miki@example.com",
            'income' => 100000,
        ));

        $json = '{"id":null,"name":"Michael Kiske","email":"miki@example.com","birthday":null,"created":null,"income":100000}';
        $array = array(
            'id' => null,
            'name' => 'Michael Kiske',
            'email' => 'miki@example.com',
            'birthday' => null,
            'created' => null,
            'income' => 100000
        );

        $this->assertSame($json, $person->toJSON());
        $this->assertSame($array, $person->toArray());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Model not writable because primary key is not defined in _meta.
     */
    public function testSaveModelWithoutPrimaryKey()
    {
        $pkl = new PkLess();
        $pkl->save();
    }

    public function testAll()
    {
        Person::objects()->delete();

        $actual = Person::all();
        $this->assertInternalType('array', $actual);
        $this->assertEmpty($actual);

        Person::fromArray(array('name' => "Freddy Mercury"))->insert();
        Person::fromArray(array('name' => "Brian May"))->insert();
        Person::fromArray(array('name' => "Roger Taylor"))->insert();

        $actual = Person::all();
        $this->assertInternalType('array', $actual);
        $this->assertCount(3, $actual);
    }
}
