<?php

namespace Phormium\Tests;

use Phormium\Meta;

use Phormium\Tests\Models\Person;
use Phormium\Tests\Models\Trade;
use Phormium\Tests\Models\PkLess;

/**
 * @group meta
 */
class MetaTest extends \PHPUnit_Framework_TestCase
{
    private $testMeta = [
        'table' => 'person',
        'database' => 'testdb',
        'pk' => 'id'
    ];

    public function testPersonMeta()
    {
        $table = 'person';
        $class = 'Phormium\\Tests\\Models\\Person';
        $database = 'testdb';
        $columns = ['id', 'name', 'email', 'birthday', 'created', 'income', 'is_cool'];
        $pk = ['id'];
        $nonPK = ['name', 'email', 'birthday', 'created', 'income', 'is_cool'];

        $expected = new Meta($table, $database, $class, $columns, $pk, $nonPK);
        $actual = Person::getMeta();
        $this->assertEquals($expected, $actual);
    }

    public function testTradeMeta()
    {
        $table = 'trade';
        $class = 'Phormium\\Tests\\Models\\Trade';
        $database = 'testdb';
        $columns = ['tradedate', 'tradeno', 'price', 'quantity'];
        $pk = ['tradedate', 'tradeno'];
        $nonPK = ['price', 'quantity'];

        $expected = new Meta($table, $database, $class, $columns, $pk, $nonPK);
        $actual = Trade::getMeta();
        $this->assertEquals($expected, $actual);
    }

    public function testPkLessMeta()
    {
        $table = 'pkless';
        $class = 'Phormium\\Tests\\Models\\PkLess';
        $database = 'testdb';
        $columns = ['foo', 'bar', 'baz'];
        $pk = null;
        $nonPK = ['foo', 'bar', 'baz'];

        $expected = new Meta($table, $database, $class, $columns, $pk, $nonPK);
        $actual = PkLess::getMeta();
        $this->assertEquals($expected, $actual);
    }

    public function testGetMeta()
    {
        // Just to improve code coverage
        $meta1 = Person::getMeta();
        $meta2 = Person::objects()->getMeta();

        $this->assertSame($meta1, $meta2);
    }
}
