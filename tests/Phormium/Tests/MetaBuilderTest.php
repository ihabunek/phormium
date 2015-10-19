<?php

namespace Phormium\Tests;

use Phormium\Meta;
use Phormium\MetaBuilder;

class MetaBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testParse1()
    {
        $builder = new MetaBuilder();
        $meta = $builder->build("Phormium\\Tests\\Models\\Model1");

        $this->assertSame('model1', $meta->table);
        $this->assertSame('database1', $meta->database);
        $this->assertSame(['id', 'foo', 'bar', 'baz'], $meta->columns);
        $this->assertSame('Phormium\\Tests\\Models\\Model1', $meta->class);
        $this->assertSame(['id'], $meta->pk);
        $this->assertSame(['foo', 'bar', 'baz'], $meta->nonPK);
    }

    public function testParse2()
    {
        $builder = new MetaBuilder();
        $meta = $builder->build("Phormium\\Tests\\Models\\Model2");

        $this->assertSame('model2', $meta->table);
        $this->assertSame('database1', $meta->database);
        $this->assertSame(['foo', 'bar', 'baz'], $meta->columns);
        $this->assertSame('Phormium\\Tests\\Models\\Model2', $meta->class);
        $this->assertSame(['foo'], $meta->pk);
        $this->assertSame(['bar', 'baz'], $meta->nonPK);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid model given
     */
    public function testInvalidClass1()
    {
        $builder = new MetaBuilder();
        $builder->build(123);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Class "Some\Class" does not exist.
     */
    public function testInvalidClass2()
    {
        $builder = new MetaBuilder();
        $builder->build("Some\\Class");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Class "Phormium\Tests\Models\NotModel" is not a subclass of Phormium\Model.
     */
    public function testInvalidClass3()
    {
        $builder = new MetaBuilder();
        $builder->build("Phormium\\Tests\\Models\\NotModel");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid Phormium\Tests\Models\InvalidModel1::$_meta. Not an array.
     */
    public function testParseErrorNotArray()
    {
        $builder = new MetaBuilder();
        $builder->build("Phormium\\Tests\\Models\\InvalidModel1");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Model Phormium\Tests\Models\InvalidModel2 has no defined columns (public properties).
     */
    public function testParseNoColumns()
    {
        $builder = new MetaBuilder();
        $builder->build("Phormium\\Tests\\Models\\InvalidModel2");
    }

    /**
     * @expectedException \Exception
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
     * @expectedException \Exception
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
     * @expectedException \Exception
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
     * @expectedException \Exception
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
     * @expectedException \Exception
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
