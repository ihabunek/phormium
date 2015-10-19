<?php

use Phormium\Orm;

/**
 * Demonstrates using Model objects to fetch data.
 */

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Post.php";
require __DIR__ . "/models/Person.php";

// Configure Phormium
Orm::configure('config.json');

// A separator for cosmetic outuput
define('SEPARATOR', "\n" . str_repeat('-', 50) . "\n");

/**
 * Create some sample data.
 */

$person = Person::fromArray([
    "name" => "Freddy Mercury"
]);
$person->save();
$personID = $person->id;

$postDate = date('Y-m-d');
$postNo = 1;
$post = Post::fromArray([
    'date' => $postDate,
    'no' => $postNo,
    'title' => "My only post"
]);
$post->save();

/**
 * To fetch a single record by it's primary key use:
 * 	- Model::get()  - throws an exception if record does not exist
 *  - Model::find() - returns NULL if record does not exist
 */

$person = Person::get($personID);
$person = Person::find($personID);

/**
 * Also works for composite primary keys.
 */

$post = Post::get($postDate, $postNo);
$post = Post::find($postDate, $postNo);

/**
 * You can pass the composite primary key as an array.
 */

$postID = array($postDate, $postNo);
$post = Post::get($postID);
$post = Post::find($postID);

echo SEPARATOR . "This is person #10:\n";
print_r($person);

echo SEPARATOR . "This is Post $postDate #$postNo:\n";
print_r($post);

/**
 * To check if a model exists by primary key, without fetching it, use
 * Model::exists(). This returns a boolean.
 */

$ex1 = Person::exists($personID);
$ex2 = Person::exists(999);

$ex3 = Post::exists($postDate, $postNo);
$ex4 = Post::exists($postDate, 999);

echo SEPARATOR;

echo "Person #$personID exists: ";
var_dump($ex1);

echo "Person #999 exists: ";
var_dump($ex2);

echo "Post $postDate $postNo exists: ";
var_dump($ex3);

echo "Post $postDate 999 exists: ";
var_dump($ex4);
