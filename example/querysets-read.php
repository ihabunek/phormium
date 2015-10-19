<?php

/**
 * Demonstrates using QuerySets to read data.
 */

use Phormium\Orm;

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Person.php";

// Configure Phormium
Orm::configure('config.json');

// A separator for cosmetic outuput
define('SEPARATOR', str_repeat('-', 50) . "\n");

/**
 * QuerySet is an object returned by Model::objects() which allows querying of
 * data in various ways.
 *
 * The simplest operation is to fetch all records from a table.
 */

/**
 * The simplest operation is to fetch all records from a table.
 */

$persons = Person::objects()->fetch();

echo SEPARATOR . "The person table has " . count($persons) . " records.\n";

/**
 * To limit the output, the results can be filtered.
 */

$persons = Person::objects()
    ->filter('salary', '>', 5000)
    ->fetch();

echo SEPARATOR . "The person table has " . count($persons) . " records with salary over 5000.\n";

/**
 * Note that filter() will return a new instance of QuerySet with the given
 * filter added to it, this allows chaining.
 */

$persons = Person::objects()
    ->filter('salary', '>', 5000)
    ->filter('name', 'like', 'M%')
    ->fetch();

echo SEPARATOR . "The person table has " . count($persons) . " records whose name starts with M and with salary over 5000.\n";
