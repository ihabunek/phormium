<?php

namespace Phormium\Tests\Unit;

use Phormium\Meta;
use Phormium\MetaBuilder;

/**
 * @group unit
 */
class MetaBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testPersonMeta()
    {
        $table = 'person';
        $class = 'Phormium\\Tests\\Models\\Person';
        $database = 'testdb';
        $columns = ['id', 'name', 'email', 'birthday', 'created', 'income', 'is_cool'];
        $pk = ['id'];
        $nonPK = ['name', 'email', 'birthday', 'created', 'income', 'is_cool'];

        $expected = new Meta($table, $database, $class, $columns, $pk, $nonPK);

        $builder = new MetaBuilder();
        $actual = $builder->build($class);
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

        $builder = new MetaBuilder();
        $actual = $builder->build($class);
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

        $builder = new MetaBuilder();
        $actual = $builder->build($class);
        $this->assertEquals($expected, $actual);
    }

    public function testParse1()
    {
        $builder = new MetaBuilder();
        $meta = $builder->build("Phormium\\Tests\\Models\\Model1");

        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertSame('model1', $meta->getTable());
        $this->assertSame('database1', $meta->getDatabase());
        $this->assertSame(['id', 'foo', 'bar', 'baz'], $meta->getColumns());
        $this->assertSame('Phormium\\Tests\\Models\\Model1', $meta->getClass());
        $this->assertSame(['id'], $meta->getPkColumns());
        $this->assertSame(['foo', 'bar', 'baz'], $meta->getNonPkColumns());
    }

    public function testParse2()
    {
        $builder = new MetaBuilder();
        $meta = $builder->build("Phormium\\Tests\\Models\\Model2");

        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertSame('model2', $meta->getTable());
        $this->assertSame('database1', $meta->getDatabase());
        $this->assertSame(['foo', 'bar', 'baz'], $meta->getColumns());
        $this->assertSame('Phormium\\Tests\\Models\\Model2', $meta->getClass());
        $this->assertSame(['foo'], $meta->getPkColumns());
        $this->assertSame(['bar', 'baz'], $meta->getNonPkColumns());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid model given
     */
    public function testInvalidClass1()
    {
        $builder = new MetaBuilder();
        $builder->build(123);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class "Some\Class" does not exist.
     */
    public function testInvalidClass2()
    {
        $builder = new MetaBuilder();
        $builder->build("Some\\Class");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Class "Phormium\Tests\Models\NotModel" is not a subclass of Phormium\Model.
     */
    public function testInvalidClass3()
    {
        $builder = new MetaBuilder();
        $builder->build("Phormium\\Tests\\Models\\NotModel");
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Invalid Phormium\Tests\Models\InvalidModel1::$_meta. Not an array.
     */
    public function testParseErrorNotArray()
    {
        $builder = new MetaBuilder();
        $builder->build("Phormium\\Tests\\Models\\InvalidModel1");
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Model Phormium\Tests\Models\InvalidModel2 has no defined columns (public properties).
     */
    public function testParseNoColumns()
    {
        $builder = new MetaBuilder();
        $builder->build("Phormium\\Tests\\Models\\InvalidModel2");
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Invalid Some\Class::$_meta. Missing "database".
     */
    public function testParseErrorMissingDatabase()
    {
        $class = 'Some\\Class';
        $meta = [];

        $builder = new MetaBuilder();
        $method = new \ReflectionMethod($builder, 'getDatabase');
        $method->setAccessible(true);
        $method->invoke($builder, $class, $meta);
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Invalid Some\Class::$_meta. Missing "table".
     */
    public function testParseErrorMissingTable()
    {
        $class = 'Some\\Class';
        $meta = [];

        $builder = new MetaBuilder();
        $method = new \ReflectionMethod($builder, 'getTable');
        $method->setAccessible(true);
        $method->invoke($builder, $class, $meta);
    }

    public function testGetPK()
    {
        $class = 'Some\\Class';
        $columns = ['id', 'foo'];

        $builder = new MetaBuilder();
        $method = new \ReflectionMethod($builder, 'getPK');
        $method->setAccessible(true);

        $meta = ['pk' => 'foo'];
        $expected = ['foo'];
        $actual = $method->invoke($builder, $class, $meta, $columns);
        $this->assertSame($expected, $actual);

        $meta = ['pk' => ['foo']];
        $expected = ['foo'];
        $actual = $method->invoke($builder, $class, $meta, $columns);
        $this->assertSame($expected, $actual);

        $meta = ['pk' => []];
        $expected = [];
        $actual = $method->invoke($builder, $class, $meta, $columns);
        $this->assertSame($expected, $actual);

        $meta = [];
        $expected = ['id'];
        $actual = $method->invoke($builder, $class, $meta, $columns);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Invalid Some\Class::$_meta. Specified primary key column(s) do not exist: bar
     */
    public function testGetPKMissingColumn()
    {
        $class = 'Some\\Class';

        $builder = new MetaBuilder();
        $method = new \ReflectionMethod($builder, 'getPK');
        $method->setAccessible(true);

        $columns = ['foo'];
        $meta = ['pk' => 'bar'];
        $method->invoke($builder, $class, $meta, $columns);
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Invalid primary key given in Some\Class::$_meta. Not a string or array.
     */
    public function testGetPKInvalidPK()
    {
        $class = 'Some\\Class';
        $columns = ['foo'];

        $builder = new MetaBuilder();
        $method = new \ReflectionMethod($builder, 'getPK');
        $method->setAccessible(true);

        $meta = ['pk' => true];
        $expected = ['foo'];
        $method->invoke($builder, $class, $meta, $columns);
    }

    /**
     * @expectedException Phormium\Exception\InvalidModelException
     * @expectedExceptionMessage Invalid primary key given in Some\Class::$_meta. Not a string or array.
     */
    public function testGetColumnsNoColumns()
    {
        $class = 'Some\\Class';
        $columns = ['foo'];

        $builder = new MetaBuilder();
        $method = new \ReflectionMethod($builder, 'getPK');
        $method->setAccessible(true);

        $meta = ['pk' => true];
        $expected = ['foo'];
        $method->invoke($builder, $class, $meta, $columns);
    }
}
