<?php

/**
 * @connection myconnection
 * @table thingy
 */
class Person extends Phormium\Entity
{
    /** @pk */
    public $id;
    
    public $name;
    
    public $birthday;
}
