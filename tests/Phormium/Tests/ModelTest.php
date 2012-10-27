<?php

namespace Phormium\Tests;

use \Phormium\DB;
use \Phormium\f;
use \Phormium\Meta;
use \Phormium\QuerySet;
use \Phormium\Tests\Models\Person;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    private static $meta;

    public static function setUpBeforeClass()
    {
        self::$meta = new Meta();
        self::$meta->table = 'person';
        self::$meta->class = 'Phormium\\Tests\\Models\\Person';
        self::$meta->database = 'testdb';
        self::$meta->columns = array('id', 'name', 'email', 'birthday', 'created', 'income');
        self::$meta->pk = 'id';
        self::$meta->nonPK = array('name', 'email', 'birthday', 'created', 'income');

        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testGetMeta()
    {
        $actual = Person::getMeta();
        $expected = self::$meta;
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
        $p = new Person(
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

    public function testJSON()
    {
        $data = array(
            'id' => 101,
            'name' => 'Jack Jackson',
            'email' => 'jack@jackson.org',
            'birthday' => '1980-03-14',
            'created' => '2000-03-07 10:45:13',
            'income' => 12345.67
        );

        $person = new Person($data);
        $person->save();

        // Expected JSON representation
        $expected = '{"id":"101","name":"Jack Jackson","email":"jack@jackson.org","birthday":"1980-03-14",' .
            '"created":"2000-03-07 10:45:13","income":"12345.67"}';

        // Fetch from database as JSON
        $dbJSON = Person::objects()
            ->filter(f::pk($data['id']))
            ->single(DB::FETCH_JSON);

        self::assertSame($expected, $dbJSON);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage $data must be an array
     */
    public function testInvalidData()
    {
        $p = new Person('invalid data');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Property [xxx] does not exist in class [Phormium\Tests\Models\Person]
     */
    public function testInvalidProperty()
    {
        $p = new Person(
            array(
                'name' => 'Peter Peterson',
                'xxx' => 'peter@peterson.com' // doesn't exist
            )
        );
    }
}
