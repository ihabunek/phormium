Phormium
========
Phormium is a minimalist ORM for PHP.

Works on informix, mysql, posgresql and sqlite. Might work on other databases
with a PDO driver.

[![Build Status](https://travis-ci.org/ihabunek/phormium.png)](https://travis-ci.org/ihabunek/phormium)

Warning: This is a work in progress. Test before using! Report any bugs
[here](https://github.com/ihabunek/phormium/issues).

Features
--------

* CRUD operations made simple
* batch update and delete
* filtering
* ordering
* limiting

Current restrictions:

* no relationships between models
* no transactions
* cannot use OR in filters

Documentation
-------------

[The documentation](http://phormium.readthedocs.org/en/latest/) is hosted by
ReadTheDocs.org.

Example
-------

After initial setup, Phormium is very easy to use.

```php
// Create a new person record
$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-20";
$person->save();

// Fetch, update, save
$person = Person::get(10);
$person->salary += 5000; // give the man a raise!
$person->save();

// Or delete
Person::get(37)->delete();

// Intuitive filtering, ordering and limiting
$persons = Person::objects()
    ->filter('salary', '>', 10000)
    ->filter('birthday', 'between', ['2000-01-01', '2001-01-01'])
    ->orderBy('name', 'desc')
    ->limit(100)
    ->fetch();

// Aggregates
Person::objects()->filter('name', 'like', 'Ivan%')->avg('salary');
Person::objects()->filter('name', 'like', 'Marko%')->min('birthday');
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

License
-------
Licensed under the MIT license. `See LICENSE.md`.
