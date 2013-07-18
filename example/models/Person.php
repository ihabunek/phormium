<?php

class Person extends Phormium\Model
{
    protected static $_meta = array(
        'database' => 'exampledb',
        'table' => 'person',
        'pk' => 'id'
    );

    public $id;
    public $name;
    public $birthday;
    public $salary;
}
