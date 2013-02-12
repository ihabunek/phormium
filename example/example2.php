<?php

/**
 * Demonstrates creating and updating objects.
 */

require __DIR__ . '/../src/Phormium/Autoloader.php';
Phormium\Autoloader::register();

// Include the database mappings
require 'Person.php';

use \Phormium\DB;

// Configure the database connections
DB::configure('config.json');

// Create a new person and save it to the database
$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-20";
$person->save();

// See that the ID was populated by save()
print_r($person);

// Fetch the newly created person from the database
$person = Person::get($person->id);

// Perform a change and save
$person->birthday = "1940-12-21";
$person->save(); 

// Fetch the updated person from the database
$person = Person::get($person->id);

print_r($person);
