<?php

/**
 * Demonstrates using Model relations.
 *
 * @see https://phormium.readthedocs.org/en/latest/relations.html
 */

use Phormium\Orm;

// Include Phormium and models
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Contact.php";
require __DIR__ . "/models/Person.php";
require __DIR__ . "/models/Post.php";
require __DIR__ . "/models/Tag.php";

$date = date('Y-m-d');

// Configure Phormium
Orm::configure('config.json');

// Create a person and three contacts
Person::fromArray(["id" => 1, "name" => "Ivan"])->save();
Contact::fromArray(["id" => 1, "person_id" => 1, "value" => "foo"])->save();
Contact::fromArray(["id" => 2, "person_id" => 1, "value" => "bar"])->save();
Contact::fromArray(["id" => 3, "person_id" => 1, "value" => "baz"])->save();

// Fetch the person, then get her contacts
$person = Person::get(1);
$contacts = $person->contacts()->fetch();
print_r($contacts);

// Fetch the contact, then get the person it belongs to
$contact = Contact::get(1);
$person = $contact->person()->single();
print_r($person);

// Create a post and three tags
Post::fromArray(["date" => $date, "no" => 1, "title" => "Post #1"])->save();
Tag::fromArray(["id" => 1, "post_date" => $date, "post_no" => 1, "value" => "Tag #1"])->save();
Tag::fromArray(["id" => 2, "post_date" => $date, "post_no" => 1, "value" => "Tag #2"])->save();
Tag::fromArray(["id" => 3, "post_date" => $date, "post_no" => 1, "value" => "Tag #3"])->save();

// Fetch a post, then fetch it's tags
$post = Post::get($date, 1);
$tags = $post->tags()->fetch();
print_r($tags);

// Fetch a tag, then fetch the post it belongs to
$tag = Tag::get(2);
$post = $tag->post()->single();
print_r($post);
