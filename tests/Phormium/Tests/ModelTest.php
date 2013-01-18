<?php

namespace Phormium\Tests;

use \Phormium\DB;
use \Phormium\f;
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
        $p2 = Person::objects()->filter(f::pk($id))->single();
        self::assertEquals($p, $p2);

        $count = Person::objects()->filter(f::pk($id))->count();
        self::assertEquals(1, $count);
    }

    public function testNewPersonAssignedPK()
    {
        $id = 100;

        // Delete if person id 100 already exists
        Person::objects()->filter(f::pk(100))->delete();

        $p = new Person();
        $p->id = $id;
        $p->name = 'Test Person';
        $p->email = 'test.person@example.com';
        $p->save();

        self::assertEquals($id, $p->id);

        // Load it from the database
        $p2 = Person::objects()->filter(f::pk($id))->single();
        self::assertEquals($p, $p2);

        $count = Person::objects()->filter(f::pk($id))->count();
        self::assertEquals(1, $count);
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
        $p2 = Person::objects()->filter(f::pk($id))->single();
        self::assertEquals($p, $p2);

        $count = Person::objects()->filter(f::pk($id))->count();
        self::assertEquals(1, $count);

        // Perform UPDATE
        $p2->email = 'peter2@peterson.com';
        $p2->save();

        // Load from DB
        $p3 = Person::objects()->filter(f::pk($id))->single();
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
            )
        );
    }

    public function testDelete()
    {
        $p = new Person();
        $p->name = 'Short Lived Person';
        $p->save();

        $qs = Person::objects()->filter(f::pk($p->id));

        $p2 = $qs->single();
        self::assertEquals($p, $p2);

        self::assertSame(1, $qs->count());
        $p->delete();
        self::assertSame(0, $qs->count());
    }
}
