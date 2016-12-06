<?php

namespace Phormium\Tests\Helper;

use Phormium\Helper\Assert;

/**
 * @group helper
 */
class AssertTest extends \PHPUnit_Framework_TestCase
{

    public function testIsInteger()
    {
        $this->assertTrue(Assert::isInteger(10));
        $this->assertTrue(Assert::isInteger(0));
        $this->assertTrue(Assert::isInteger(-10));
        $this->assertTrue(Assert::isInteger("10"));
        $this->assertTrue(Assert::isInteger("0"));
        $this->assertTrue(Assert::isInteger("-10"));

        $this->assertFalse(Assert::isInteger(10.6));
        $this->assertFalse(Assert::isInteger("10.6"));
        $this->assertFalse(Assert::isInteger("heavy metal"));
        $this->assertFalse(Assert::isInteger([]));
        $this->assertFalse(Assert::isInteger(new \stdClass()));
        $this->assertFalse(Assert::isInteger(""));
        $this->assertFalse(Assert::isInteger("-"));
    }

    public function testIsPositiveInteger()
    {
        $this->assertTrue(Assert::isPositiveInteger(10));
        $this->assertTrue(Assert::isPositiveInteger(0));
        $this->assertTrue(Assert::isPositiveInteger("10"));
        $this->assertTrue(Assert::isPositiveInteger("0"));

        $this->assertFalse(Assert::isPositiveInteger(10.6));
        $this->assertFalse(Assert::isPositiveInteger("10.6"));
        $this->assertFalse(Assert::isPositiveInteger("heavy metal"));
        $this->assertFalse(Assert::isPositiveInteger([]));
        $this->assertFalse(Assert::isPositiveInteger(new \stdClass()));
        $this->assertFalse(Assert::isPositiveInteger(""));
        $this->assertFalse(Assert::isPositiveInteger("-"));
        $this->assertFalse(Assert::isPositiveInteger(-10));
        $this->assertFalse(Assert::isPositiveInteger("-10"));
    }
}
