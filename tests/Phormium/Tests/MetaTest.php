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
    private $testMeta = array(
        'table' => 'person',
        'database' => 'testdb',
        'pk' => 'id'
    );

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
        $this->assertEquals($expected, $actual);
    }

    public function testTradeMeta()
    {
        $expected = new Meta();
        $expected->table = 'trade';
        $expected->class = 'Phormium\\Tests\\Models\\Trade';
        $expected->database = 'testdb';
        $expected->columns = array('tradedate', 'tradeno', 'price', 'quantity');
        $expected->pk = array('tradedate', 'tradeno');
        $expected->nonPK = array('price', 'quantity');

        $actual = Trade::getMeta();
        $this->assertEquals($expected, $actual);
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
