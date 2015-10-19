<?php

/**
 * Demonstrates using Models to write data.
 */

use Phormium\Orm;

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Person.php";

// Configure Phormium
Orm::configure('config.json');

// A separator for cosmetic outuput
define('SEPARATOR', "\n" . str_repeat('-', 50) . "\n");

/**
 * Create a new Person model and save it to the database.
 * This will automatically populate the primary key column if it is assigned by
 * the database.
 */

$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-21";
$person->salary = 1000;
$person->insert();

echo SEPARATOR . "New person inserted:\n";
print_r($person);

/**
 * To create a Model from data contained in an array, use the merge() method to
 * overwrite any data in the model with the data from the array.
 */

$data = array(
    'name' => 'Captain Beefheart',
    'birthday' => '1941-01-15',
    'salary' => 1200
);

$person = new Person();
$person->merge($data);
$person->insert();

echo SEPARATOR . "New person inserted:\n";
print_r($person);

/**
 * To change an existing record, fetch it from the database, perform the
 * required changes and call update().
 */

$personID = $person->id;

echo SEPARATOR . "Person #$personID before changes:\n";
print_r(Person::get($personID));

// Get, change, update
$person = Person::get($personID);
$person->salary += 500;
$person->update();

echo SEPARATOR . "Person #$personID after changes:\n";
print_r(Person::get($personID));

/**
 * The magic save() method will automatically update() the record if it exists
 * and insert() it if it doesn't. It can be used instead of update() and
 * insert(), but it can be sub-optimal since it queries the database to check
 * if a record exists.
 */

// Both of these work:
$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-21";
$person->salary = 1000;
$person->save();

$person = Person::get($personID);
$person->salary += 500;
$person->save();
