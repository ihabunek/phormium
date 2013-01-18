<?php

/**
 * Demonstrates using aggregates.
 */

require __DIR__ . '/../src/Phormium/Autoloader.php';
Phormium\Autoloader::register();

// Include the database mappings
require 'Person.php';

use \Phormium\DB;
use \Phormium\a;
use \Phormium\f;

// Configure the database connections
DB::configure('config.json');

// Fetch first and last birthday
$min = Person::objects()->aggregate(a::min('birthday'));
$max = Person::objects()->aggregate(a::max('birthday'));

echo "min = $min\n";
echo "max = $max\n";

// Fetch average salary of people born before 1980
$avg = Person::objects()
    ->filter(f::lt('birthday', '1980-01-01'))
    ->aggregate(a::avg('salary'));

echo "avg = $avg\n";

// Fetch sum of salaries of people whose name begins with "G"
$sum = Person::objects()
    ->filter(f::like('name', 'G%'))
    ->aggregate(a::sum('salary'));

echo "sum = $sum\n";
