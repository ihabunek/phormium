<?php

namespace Phormium\Tests;

use \Phormium\Tests\Models\Person;
use \Phormium\DB;

class PrinterTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testDumpReturn()
    {
        $name = "Freddy Mercury";

        Person::objects()->filter("name", "=", $name)->delete();

        $person1 = Person::fromArray(array("name" => $name, "income" => 100));
        $person2 = Person::fromArray(array("name" => $name, "income" => 200));
        $person3 = Person::fromArray(array("name" => $name, "income" => 300));

        $person1->save(); $id1 = $person1->id;
        $person2->save(); $id2 = $person2->id;
        $person3->save(); $id3 = $person3->id;

        $actual = Person::objects()->filter("name", "=", $name)->dump(true);
        $lines = explode(PHP_EOL, $actual);

        self::assertRegExp("/^\\s*id\\s+name\\s+email\\s+birthday\\s+created\\s+income\\s*$/", $lines[0]);
        self::assertRegExp("/^=+$/", $lines[1]);
        self::assertRegExp("/^\\s*$id1\\s+Freddy Mercury\\s+100(.00)?\\s*$/", $lines[2]);
        self::assertRegExp("/^\\s*$id2\\s+Freddy Mercury\\s+200(.00)?\\s*$/", $lines[3]);
        self::assertRegExp("/^\\s*$id3\\s+Freddy Mercury\\s+300(.00)?\\s*$/", $lines[4]);
    }

    public function testDumpEcho()
    {
        $name = "Rob Halford";

        Person::objects()->filter("name", "=", $name)->delete();

        $person1 = Person::fromArray(array("name" => $name, "income" => 100));
        $person2 = Person::fromArray(array("name" => $name, "income" => 200));
        $person3 = Person::fromArray(array("name" => $name, "income" => 300));

        $person1->save(); $id1 = $person1->id;
        $person2->save(); $id2 = $person2->id;
        $person3->save(); $id3 = $person3->id;

        ob_start();
        Person::objects()->filter("name", "=", $name)->dump();
        $actual = ob_get_clean();

        $lines = explode(PHP_EOL, $actual);

        self::assertRegExp("/^\\s*id\\s+name\\s+email\\s+birthday\\s+created\\s+income\\s*$/", $lines[0]);
        self::assertRegExp("/^=+$/", $lines[1]);
        self::assertRegExp("/^\\s*$id1\\s+Rob Halford\\s+100(.00)?\\s*$/", $lines[2]);
        self::assertRegExp("/^\\s*$id2\\s+Rob Halford\\s+200(.00)?\\s*$/", $lines[3]);
        self::assertRegExp("/^\\s*$id3\\s+Rob Halford\\s+300(.00)?\\s*$/", $lines[4]);
    }
}