<?php

/**
 * @connection myconnection
 * @table person
 */
class Person extends Phormium\Entity
{
    /** @pk */
    public $id;
    
    public $name;
    
    public $birthday;
}
