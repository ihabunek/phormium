<?php

namespace Phormium\Tests;

use \Phormium\DB;
use \Phormium\Meta;
use \Phormium\QuerySet;
use \Phormium\Tests\Models\Person;
use \Phormium\Tests\Models\Trade;
use \Phormium\Tests\Models\PkLess;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testPersonMeta()
    {
        $expected = new Meta();
        $expected->table = 'person';
        $expected->class = 'Phormium\\Tests\\Models\\Person';
        $expected->database = 'testdb';
        $expected->columns = array('id', 'name', 'email', 'birthday', 'created', 'income');
        $expected->pk = array('id');
        $expected->nonPK = array('name', 'email', 'birthday', 'created', 'income');

        $actual = Person::getMeta();
        self::assertEquals($expected, $actual);
    }

    public function testTradeMeta()
    {
        $expected = new Meta();
        $expected->table = 'trade';
        $expected->class = 'Phormium\\Tests\\Models\\Trade';
        $expected->database = 'testdb';
        $expected->columns = array('tradedate', 'tradeno', 'datetime', 'price', 'quantity');
        $expected->pk = array('tradedate', 'tradeno');
        $expected->nonPK = array('datetime', 'price', 'quantity');

        $actual = Trade::getMeta();
        self::assertEquals($expected, $actual);
    }

    public function testPkLessMeta()
    {
        $expected = new Meta();
        $expected->table = 'pkless';
        $expected->class = 'Phormium\\Tests\\Models\\PkLess';
        $expected->database = 'testdb';
        $expected->columns = array('foo', 'bar', 'baz');
        $expected->pk = null;
        $expected->nonPK = array('foo', 'bar', 'baz');

        $actual = PkLess::getMeta();
        self::assertEquals($expected, $actual);
    }

    public function testNewPerson()
    {
        $p = new Person();
        $p->name = 'Test Person';
        $p->email = 'test.person@example.com';

        self::assertNull($p->id);
        $p->save();
        self::assertNotNull($p->id);

        $id = $p->id;

        // Load it from the database
        $p2 = Person::get($id);
        self::assertEquals($p, $p2);
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

        self::assertEquals($id, $p->id);

        // Load it from the database
        $p2 = Person::get($id);
        self::assertEquals($p, $p2);
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
        self::assertNotNull($p->id);

        $id = $p->id;

        // Load it from the database
        $p2 = Person::get($id);
        self::assertEquals($p, $p2);

        // Perform UPDATE
        $p2->email = 'peter2@peterson.com';
        $p2->save();

        // Load from DB
        $p3 = Person::get($id);
        self::assertEquals($p2, $p3);
        self::assertEquals($id, $p3->id);
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

        self::assertEquals($expected, $actual);
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

        self::assertEquals($person1, $person2);
        self::assertTrue($qs->exists());

        $count = $person1->delete();
        self::assertFalse($qs->exists());
        self::assertSame(1, $count);

        // On repeated delete, 0 count should be returned
        $count = $person1->delete();
        self::assertSame(0, $count);
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

        self::assertEquals($expected, $actual);
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

        self::assertSame(0, $qs->count());
        self::assertFalse($qs->exists());

        Person::objects()->filter('name', '=', 'Hrvoje')->single();
    }

    public function testSingleZeroAllowed()
    {
        $qs = Person::objects()->filter('name', '=', 'Hrvoje');
        $qs->delete();

        self::assertSame(0, $qs->count());
        self::assertFalse($qs->exists());

        $actual = Person::objects()->filter('name', '=', 'Hrvoje')->single(true);
        self::assertNull($actual);
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

        self::assertSame(0, $qs->count());
        self::assertFalse($qs->exists());

        Person::fromArray(array('name' => 'Hrvoje'))->save();
        Person::fromArray(array('name' => 'Hrvoje'))->save();
        Person::fromArray(array('name' => 'Hrvoje'))->save();

        self::assertSame(3, $qs->count());
        self::assertTrue($qs->exists());

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

        self::assertEquals($p1, $p2);
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

    public function testGetPK()
    {
        $foo = new Person();
        self::assertCount(1, $foo->getPK());

        $foo = new PkLess();
        self::assertCount(0, $foo->getPK());

        $foo = new Trade();
        self::assertCount(2, $foo->getPK());
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
        self::assertEquals($expected, $actual);

        $actual = Person::objects()
            ->filter('name', '=', $name)
            ->orderBy('income', 'asc')
            ->distinct('income');

        $expected = array(100, 200);
        self::assertEquals($expected, $actual);
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

        self::assertEquals($expected, $actual);

        $actual = Person::objects()
            ->filter('name', 'LIKE', "$name%")
            ->orderBy('name', 'asc')
            ->valuesList('name', 'income');

        $expected = array(
            array("$name-1", 100),
            array("$name-2", 200),
            array("$name-3", 300),
        );

        self::assertEquals($expected, $actual);

        $actual = Person::objects()
            ->filter('name', 'LIKE', "$name%")
            ->orderBy('name', 'asc')
            ->valuesFlat('name');

         $expected = array(
            "$name-1",
            "$name-2",
            "$name-3",
        );

        self::assertEquals($expected, $actual);
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

        self::assertSame($json, $person->toJSON());
        self::assertSame($array, $person->toArray());
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
}
