<?php

namespace Phormium\Tests\Models;

/**
 * A test model with invalid metadata.
 */
class InvalidModel1 extends \Phormium\Model
{
    protected static $_meta = "foo";

    public $foo;
}
