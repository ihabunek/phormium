<?php

/**
 * Demonstrates using QuerySets to write data.
 */

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Person.php";

// Configure Phormium
\Phormium\DB::configure('config.json');

// A separator for cosmetic outuput
define('SEPARATOR', str_repeat('-', 50) . "\n");

/**
 * The update() method will update all rows matched by the filter and set the
 * given data. It returns the number of affected records.
 */

$count = Person::objects()
    ->filter('salary', '>', 5000)
    ->update(array(
        'salary' => 6000
    ));

echo SEPARATOR . "Updated $count rich people.";

/**
 * Alternatively, you can delete them (commented out because it's destructive).
 */

// Person::objects()
//     ->filter('salary', '>', 5000)
//     ->delete();
