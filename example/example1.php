<?php

/**
 * Demonstrates various ways of fetching data using QuerySets.
 */

require __DIR__ . '/../src/Phormium/Autoloader.php';
Phormium\Autoloader::register();

// Include the database mappings
require 'Person.php';

use \Phormium\DB;

// Configure the database connections
DB::configure('config.json');

// Select a single record by primary key
$person = Person::get(31);

echo "Person #31:\n";
print_r($person);
echo "\n";

// Get number of people born before 2012
$count = Person::objects()
    ->filter('birthday', '<', '2012-01-01')
    ->count();

echo "Number of people born before 2012: $count\n\n";

// Fetch all people whose name begins with G
$people = Person::objects()
    ->filter('name', 'ilike', 'g%')
    ->fetch();

echo "People whose name starts with the letter G:\n";
print_r($people);
echo "\n";

// Fetch all people with salary between 4500 and 5000 (ordered by salary desc)
$people = Person::objects()
    ->filter('salary', 'between', [4500, 5000])
    ->orderBy('salary', 'desc')
    ->fetch();

echo "People with with salary between 4500 and 5000:\n";
print_r($people);
echo "\n";