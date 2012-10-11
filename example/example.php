<?php

require '../Phormium/Autoloader.php';
Phormium\Autoloader::register();

// Include the database mappings
require 'Person.php';

use \Phormium\DB;
use \Phormium\f;

// Configure the database connections
DB::configure('config.json');

// Select a single record by primary key
$t = Person::objects()
    ->filter(f::pk(31))
    ->single();

print_r($t);

// Get number of thingies born before 2012
$c = Person::objects()
    ->filter(f::lt('birthday', '2012-01-01'))
    ->count();

echo "Count: $c\n";

// Fetch all thingies whose name begins with G
$a = Person::objects()
    ->filter(f::like('name', 'G%'))
    ->fetch();

print_r($a);

// Fetch all thingies with id between 54 and 57, as JSON 
$a = Person::objects()
    ->filter(f::between('id', 54, 57))
    ->fetch(DB::FETCH_JSON);
    
print_r($a);

