<?php

namespace Phormium\Tests\Models;

/**
 * @connection testdb
 * @table person
 */
class Person extends \Phormium\Model
{
    /** @pk */
    public $id;
    public $name;
    public $email;
    public $birthday;
    public $created;
    public $income;
}
