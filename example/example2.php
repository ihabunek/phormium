<?php

/**
 * Demonstrates creating and updating objects.
 */

require '../Phormium/Autoloader.php';
Phormium\Autoloader::register();

// Include the database mappings
require 'Person.php';

use \Phormium\DB;
use \Phormium\f;

// Configure the database connections
DB::configure('config.json');

// Create a new person and save it to the database
$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-20";
$person->save();

// See that the ID was populated by save()
print_r($person);

$id = $person->id;

// Fetch the newly created person from the database
$person = Person::objects()
    ->filter(f::pk($id))
    ->single();

// Perform a change and save
$person->birthday = "1940-12-21";
$person->save(); 

// Fetch the updated person from the database
$person = Person::objects()
    ->filter(f::pk($id))
    ->single();

print_r($person);