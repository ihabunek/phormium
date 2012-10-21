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
        self::$meta->connection = 'testdb';
        self::$meta->columns = array (
            'id' => array ('name' => 'id'),
            'name' => array ('name' => 'name'),
            'email' => array ('name' => 'email'),
            'birthday' => array ('name' => 'birthday'),
            'created' => array ('name' => 'created'),
            'income' => array ('name' => 'income'),
        );
        self::$meta->pk = 'id';
        self::$meta->nonPK = array(
            'name',
            'email',
            'birthday',
            'created',
            'income',
        );

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
        self::assertSame(1, $count);
    }
}
