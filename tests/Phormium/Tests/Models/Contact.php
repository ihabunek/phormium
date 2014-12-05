<?php

namespace Phormium\Tests\Models;

use Phormium\Model;

class Contact extends Model
{
    protected static $_meta = array(
        'database' => 'testdb',
        'table' => 'contact',
        'pk' => 'id'
    );

    public $id;
    public $person_id;
    public $value;
}
