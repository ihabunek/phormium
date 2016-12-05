<?php

namespace Phormium\Tests;

use Phormium\Filter\ColumnFilter;
use Phormium\Filter\Filter;
use Phormium\Orm;
use Phormium\QuerySet;

use Phormium\Tests\Models\Person;

/**
 * @group queryset
 */
class QuerySetTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        Orm::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testCloneQS()
    {
        $qs1 = Person::objects();
        $qs2 = $qs1->all();

        $this->assertEquals($qs1, $qs2);
        $this->assertNotSame($qs1, $qs2);
        $this->assertNull($qs1->getFilter());
        $this->assertNull($qs2->getFilter());
        $this->assertEmpty($qs1->getOrder());
        $this->assertEmpty($qs2->getOrder());
    }

    public function testDeepCloneQS()
    {
        $qs1 = Person::objects();
        $qs2 = $qs1->filter("1=1");
        $qs3 = $qs2->filter("1=2");

        $this->assertNull($qs1->getFilter());
        $this->assertNotNull($qs2->getFilter());
        $this->assertNotNull($qs3->getFilter());

        // Check that a deep clone has been made
        $this->assertNotSame($qs2->getFilter(), $qs3->getFilter());
    }

    public function testFilterQS()
    {
        $f = new ColumnFilter('name', '=', 'x');
        $qs1 = Person::objects();
        $qs2 = $qs1->filter('name', '=', 'x');

        $this->assertNotEquals($qs1, $qs2);
        $this->assertNotSame($qs1, $qs2);

        $this->assertInstanceOf("\\Phormium\\Filter\\CompositeFilter", $qs2->getFilter());
        $this->assertCount(1, $qs2->getFilter()->filters());

        $this->assertEmpty($qs1->getOrder());
        $this->assertEmpty($qs2->getOrder());

        $expected = Filter::_and($f);
        $actual = $qs2->getFilter();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid filter: Column [x] does not exist in table [person].
     */
    public function testFilterInvalidColumn()
    {
        Person::objects()->filter('x', '=', 'x');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown filter operation [!!!].
     */
    public function testFilterInvalidOperation()
    {
        Person::objects()->filter('name', '!!!', 'x')->fetch();
    }

    public function testOrderQS()
    {
        $qs1 = Person::objects();
        $qs2 = $qs1->orderBy('name', 'desc');

        $this->assertNotEquals($qs1, $qs2);
        $this->assertNotSame($qs1, $qs2);

        $this->assertNull($qs1->getFilter());
        $this->assertNull($qs2->getFilter());

        $this->assertEmpty($qs1->getOrder());

        $expected = ['name desc'];
        $actual = $qs2->getOrder();
        $this->assertSame($expected, $actual);

        $qs3 = $qs2->orderBy('id');
        $expected = ['name desc', 'id asc'];
        $actual = $qs3->getOrder();
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid order direction [!!!]. Expected 'asc' or 'desc'.
     */
    public function testOrderInvalidDirection()
    {
        Person::objects()->orderBy('name', '!!!')->fetch();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot order by column [xxx] because it does not exist in table [person].
     */
    public function testOrderInvalidColumn()
    {
        Person::objects()->orderBy('xxx', 'asc')->fetch();
    }

    public function testBatch()
    {
        // Create some sample data
        $uniq = uniqid('batch');

        $p1 = [
            'name' => "{$uniq}_1",
            'income' => 10000
        ];

        $p2 = [
            'name' => "{$uniq}_2",
            'income' => 20000
        ];

        $p3 = [
            'name' => "{$uniq}_3",
            'income' => 30000
        ];

        $qs = Person::objects()->filter('name', 'like', "{$uniq}%");

        $this->assertFalse($qs->exists());
        $this->assertSame(0, $qs->count());

        Person::fromArray($p1)->save();
        Person::fromArray($p2)->save();
        Person::fromArray($p3)->save();

        $this->assertTrue($qs->exists());
        $this->assertSame(3, $qs->count());

        // Give everybody a raise!
        $count = $qs->update(
            [
                'income' => 5000
            ]
        );

        $this->assertSame(3, $count);

        $persons = $qs->fetch();
        foreach ($persons as $person) {
            $this->assertEquals(5000, $person->income);
        }

        // Delete
        $count = $qs->delete();
        $this->assertSame(3, $count);

        // Check deleted
        $this->assertFalse($qs->exists());
        $this->assertSame(0, $qs->count());

        // Repeated delete should yield 0 count
        $count = $qs->delete();
        $this->assertSame(0, $count);
    }

    public function testLimitedFetch()
    {
        // Create some sample data
        $uniq = uniqid('limit');

        $persons = [
            Person::fromArray(['name' => "{$uniq}_1"]),
            Person::fromArray(['name' => "{$uniq}_2"]),
            Person::fromArray(['name' => "{$uniq}_3"]),
            Person::fromArray(['name' => "{$uniq}_4"]),
            Person::fromArray(['name' => "{$uniq}_5"]),
        ];

        foreach ($persons as $person) {
            $person->save();
        }

        $qs = Person::objects()
            ->filter('name', 'like', "{$uniq}%")
            ->orderBy('name');

        $this->assertEquals(array_slice($persons, 0, 2), $qs->limit(2)->fetch());
        $this->assertEquals(array_slice($persons, 0, 2), $qs->limit(2, 0)->fetch());
        $this->assertEquals(array_slice($persons, 1, 2), $qs->limit(2, 1)->fetch());
        $this->assertEquals(array_slice($persons, 2, 2), $qs->limit(2, 2)->fetch());
        $this->assertEquals(array_slice($persons, 3, 2), $qs->limit(2, 3)->fetch());
        $this->assertEquals(array_slice($persons, 0, 1), $qs->limit(1)->fetch());
        $this->assertEquals(array_slice($persons, 0, 1), $qs->limit(1, 0)->fetch());
        $this->assertEquals(array_slice($persons, 1, 1), $qs->limit(1, 1)->fetch());
        $this->assertEquals(array_slice($persons, 2, 1), $qs->limit(1, 2)->fetch());
        $this->assertEquals(array_slice($persons, 3, 1), $qs->limit(1, 3)->fetch());
        $this->assertEquals(array_slice($persons, 4, 1), $qs->limit(1, 4)->fetch());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Limit must be an integer or null.
     */
    public function testLimitedFetchWrongLimit1()
    {
        Person::objects()->limit(1.1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Limit must be an integer or null.
     */
    public function testLimitedFetchWrongLimit2()
    {
        Person::objects()->limit("a");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Offset must be an integer or null.
     */
    public function testLimitedFetchWrongOffset1()
    {
        Person::objects()->limit(1, 1.1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Offset must be an integer or null.
     */
    public function testLimitedFetchWrongOffset2()
    {
        Person::objects()->limit(1, "a");
    }

    public function testLimitOffsetDistinctQuery()
    {
        $p1 = Person::fromArray(['name' => 'Foo Bar']);
        $p2 = Person::fromArray(['name' => 'Foo Bar']);
        $p3 = Person::fromArray(['name' => 'Foo Bar']);
        $p4 = Person::fromArray(['name' => 'Foo Bar']);
        $p5 = Person::fromArray(['name' => 'Foo Bar']);

        $p1->save();
        $p2->save();
        $p3->save();
        $p4->save();
        $p5->save();

        $ids = [$p1->id, $p2->id, $p3->id, $p4->id, $p5->id];

        $res = Person::objects()
            ->filter('id', 'in', $ids)
            ->orderBy('id', 'desc')
            ->limit(3, 1)
            ->distinct('id');

        $this->assertCount(3, $res);
        $this->assertSame($p4->id, $res[0]);
        $this->assertSame($p3->id, $res[1]);
        $this->assertSame($p2->id, $res[2]);
    }
}
