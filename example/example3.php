<?php

/**
 * Demonstrates using aggregates.
 */

require __DIR__ . '/../vendor/autoload.php';

// Include the database mappings
require 'Person.php';

use \Phormium\DB;

// Configure the database connections
DB::configure('config.json');

// Fetch first and last birthday
$min = Person::objects()->min('birthday');
$max = Person::objects()->max('birthday');

echo "min = $min\n";
echo "max = $max\n";

// Fetch average salary of people born before 1980
$avg = Person::objects()
    ->filter('birthday', '<', '1980-01-01')
    ->avg('salary');

echo "avg = $avg\n";

// Fetch sum of salaries of people whose name begins with "G"
$sum = Person::objects()
    ->filter('name', 'like', 'G%')
    ->sum('salary');

echo "sum = $sum\n";
