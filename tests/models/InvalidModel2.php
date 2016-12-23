<?php

namespace Phormium\Tests\Models;

/**
 * A test model with no public properties.
 */
class InvalidModel2 extends \Phormium\Model
{
    protected static $_meta = [
        'database' => 'database1',
        'table' => 'invalid_model_2'
    ];
}
