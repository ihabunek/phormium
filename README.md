Phormium
========

Phormium is a minimalist ORM for PHP.

Tested on Informix, MySQL, PosgreSQL and SQLite. Might work on other databases
with a PDO driver or may require some work.

Warning: This is a work in progress. Test before using! Report any bugs
[here](https://github.com/ihabunek/phormium/issues).

[![Latest Stable Version](https://poser.pugx.org/phormium/phormium/v/stable.png)](https://packagist.org/packages/phormium/phormium) [![Total Downloads](https://poser.pugx.org/phormium/phormium/downloads.png)](https://packagist.org/packages/phormium/phormium) [![Build Status](https://travis-ci.org/ihabunek/phormium.png)](https://travis-ci.org/ihabunek/phormium)

Features
--------

* CRUD operations made simple
* batch update and delete
* filtering
* ordering
* limiting
* transactions
* query logging (requires [Apache log4php](http://logging.apache.org/log4php/))
* custom queries

Not yet implemented:

* relationships between models (joins)

Documentation
-------------

[The documentation](http://phormium.readthedocs.org/en/latest/) is hosted by
ReadTheDocs.org.

Showcase
--------

After initial setup, Phormium is very easy to use. Here's a quick overview of
it's features:

```php
// Create a new person record
$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-20";
$person->save();

// Get record by primary key
Person::get(10);   // Throws exception if the model doesn't exist
Person::find(10);  // Returns null if the model doesn't exist

// Check record exists by primary key
Person::exists(10);

// Also works for composite primary keys
Trade::get('2013-01-01', 100);
Trade::find('2013-01-01', 100);
Trade::exists('2013-01-01', 100);

// Primary keys can also be given as arrays
$tradePK = array('2013-01-01', 100);
Trade::get($tradePK);
Trade::find($tradePK);
Trade::exists($tradePK);

// Fetch, update, save
$person = Person::get(10);
$person->salary += 5000; // give the man a raise!
$person->save();

// Fetch, delete
Person::get(37)->delete();

// Intuitive filtering, ordering and limiting
$persons = Person::objects()
    ->filter('salary', '>', 10000)
    ->filter('birthday', 'between', ['2000-01-01', '2001-01-01'])
    ->orderBy('name', 'desc')
    ->limit(100)
    ->fetch();

// Count records
$count = Person::objects()
    ->filter('salary', '>', 10000)
    ->count();

// Distinct values
$count = Person::objects()
    ->distinct('name', 'email');

// Complex composite filters
$persons = Person::objects()->filter(
    Filter::_or(
        Filter::_and(
            array('id', '>=', 10),
            array('id', '<=', 20)
        ),
        Filter::_and(
            array('id', '>=', 50),
            array('id', '<=', 60)
        ),
        array('id', '>=', 100),
    )
)->fetch();

// Fetch a single record (otherwise throws an exeption)
$person = Person::objects()
    ->filter('email', '=', 'ivan@example.com')
    ->single();

// Batch update
Person::objects()
    ->filter('salary', '>', 10000)
    ->update(['salary' => 5000]);

// Batch delete
Person::objects()
    ->filter('salary', '>', 10000)
    ->delete();

// Aggregates
Person::objects()->filter('name', 'like', 'Ivan%')->avg('salary');
Person::objects()->filter('name', 'like', 'Marko%')->min('birthday');

// Custom queries
$conn = DB::getConnection('myconn');
$data1 = $conn->query("SELECT * FROM mytable;");
$data2 = $conn->preparedQuery("SELECT * FROM mytable WHERE mycol > :value", array("value" => 10))
```

See [documentation](http://phormium.readthedocs.org/en/latest/) for full
reference, also check out the `example` directory for more examples.

Why?
----

"Why another ORM?!?", I hear you cry.

There are two reasons:

* I work a lot on Informix on my day job and no other ORM I found supports it.
* Writing an ORM is a great experience. You should try it.

Phormium is greatly inspired by other ORMs, in particular
[Django ORM](https://docs.djangoproject.com/en/dev/topics/db/) and
Laravel's [Eloquent ORM](http://laravel.com/docs/database/eloquent).

Let me know what you think!

Ivan Habunek [@ihabunek](http://twitter.com/ihabunek)

Praise
------

If you like it, buy me a beer (in Croatia, that's around €2 or $3).

[![Flattr this](http://api.flattr.com/button/flattr-badge-large.png)](http://flattr.com/thing/1204532/ihabunekphormium-on-GitHub)

License
-------
Licensed under the MIT license. `See LICENSE.md`.
