<?php

/**
 * @connection myconnection
 * @table person
 */
class Person extends Phormium\Model
{
    /** @pk */
    public $id;
    
    public $name;
    
    public $birthday;
}
