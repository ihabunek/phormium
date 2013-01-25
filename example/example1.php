<?php

/**
 * Demonstrates various ways of fetching data using QuerySets.
 */

require __DIR__ . '/../src/Phormium/Autoloader.php';
Phormium\Autoloader::register();

// Include the database mappings
require 'Person.php';

use \Phormium\DB;
use \Phormium\f;

// Configure the database connections
DB::configure('config.json');

// Select a single record by primary key
$t = Person::get(31);

// Get number of people born before 2012
$c = Person::objects()
    ->filter('birthday', '<', '2012-01-01')
    ->count();

echo "Count: $c\n";

// Fetch all people whose name begins with G
$a = Person::objects()
    ->filter('name', 'like', 'G%')
    ->fetch();

print_r($a);

// Fetch all people with id between 54 and 57 as arrays
$a = Person::objects()
    ->filter('id', 'between', [54, 57])
    ->fetch(DB::FETCH_ARRAY);
    
print_r($a);
