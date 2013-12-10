<?php

/**
 * Demonstrates using Model objects to fetch data.
 */

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Trade.php";
require __DIR__ . "/models/Person.php";

// Configure Phormium
\Phormium\DB::configure('config.json');

// A separator for cosmetic outuput
define('SEPARATOR', "\n" . str_repeat('-', 50) . "\n");

/**
 * To fetch a single record by it's primary key use:
 * 	- Model::get()  - throws an exception if record does not exist
 *  - Model::find() - returns NULL if record does not exist
 */

$person = Person::get(10);
$person = Person::find(10);

/**
 * Also works for composite primary keys.
 */

$trade = Trade::get('2013-10-01', 522);
$trade = Trade::find('2013-10-01', 522);

/**
 * You can pass the composite primary key as an array.
 */

$tradeID = array('2013-10-01', 522);
$trade = Trade::get($tradeID);
$trade = Trade::find($tradeID);

echo SEPARATOR . "This is person #10:\n";
print_r($person);

echo SEPARATOR . "This is trade 2013-10-01 522:\n";
print_r($trade);

/**
 * To check if a model exists by primary key, without fetching it, use
 * Model::exists(). This returns a boolean.
 */

$ex1 = Person::exists(100);
$ex2 = Person::exists(200);

$ex3 = Trade::exists('2013-10-01', 522);
$ex4 = Trade::exists('2013-10-01', 999);

echo SEPARATOR;
echo "Person #100 exists: "; var_dump($ex1);
echo "Person #200 exists: "; var_dump($ex2);
echo "Trade 2013-10-01 522 exists: "; var_dump($ex3);
echo "Trade 2013-10-01 999 exists: "; var_dump($ex4);
