<?php

namespace Phormium\Tests;

use Phormium\DB;
use Phormium\Printer;

use Phormium\Tests\Models\Person;

/**
 * @group printer
 */
class PrinterTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        DB::configure(PHORMIUM_CONFIG_FILE);
    }

    public function testDumpQSReturn()
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

        $this->assertRegExp("/^\\s*id\\s+name\\s+email\\s+birthday\\s+created\\s+income\\s*$/", $lines[0]);
        $this->assertRegExp("/^=+$/", $lines[1]);
        $this->assertRegExp("/^\\s*$id1\\s+Freddy Mercury\\s+100(.00)?\\s*$/", $lines[2]);
        $this->assertRegExp("/^\\s*$id2\\s+Freddy Mercury\\s+200(.00)?\\s*$/", $lines[3]);
        $this->assertRegExp("/^\\s*$id3\\s+Freddy Mercury\\s+300(.00)?\\s*$/", $lines[4]);
    }

    public function testDumpArrayReturn()
    {
        $name = "Freddy Mercury";

        $data = array(
            array("id" => 1, "name" => $name, "email" => "freddy@queen.org", "income" => 100),
            array("id" => 2, "name" => $name, "email" => "freddy@queen.org", "income" => 200),
            array("id" => 3, "name" => $name, "email" => "freddy@queen.org", "income" => 300),
        );

        $actual = Printer::dump($data, true);
        $lines = explode(PHP_EOL, $actual);

        $this->assertRegExp("/^\\s*id\\s+name\\s+email\\s+income\\s*$/", $lines[0]);
        $this->assertRegExp("/^=+$/", $lines[1]);
        $this->assertRegExp("/^\\s*1\\s+Freddy Mercury\\s+freddy@queen.org\\s+100(.00)?\\s*$/", $lines[2]);
        $this->assertRegExp("/^\\s*2\\s+Freddy Mercury\\s+freddy@queen.org\\s+200(.00)?\\s*$/", $lines[3]);
        $this->assertRegExp("/^\\s*3\\s+Freddy Mercury\\s+freddy@queen.org\\s+300(.00)?\\s*$/", $lines[4]);
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

        $this->assertRegExp("/^\\s*id\\s+name\\s+email\\s+birthday\\s+created\\s+income\\s*$/", $lines[0]);
        $this->assertRegExp("/^=+$/", $lines[1]);
        $this->assertRegExp("/^\\s*$id1\\s+Rob Halford\\s+100(.00)?\\s*$/", $lines[2]);
        $this->assertRegExp("/^\\s*$id2\\s+Rob Halford\\s+200(.00)?\\s*$/", $lines[3]);
        $this->assertRegExp("/^\\s*$id3\\s+Rob Halford\\s+300(.00)?\\s*$/", $lines[4]);
    }

    public function testDumpEchoEmptyQS()
    {
        $name = "Rob Halford";

        Person::objects()->filter("name", "=", $name)->delete();

        ob_start();
        Person::objects()->filter("name", "=", $name)->dump();
        $actual = ob_get_clean();

        $this->assertSame("", $actual);
    }

    public function testDumpEchoEmptyArray()
    {
        ob_start();
        Printer::dump(array());
        $actual = ob_get_clean();

        $this->assertSame("", $actual);
    }
}