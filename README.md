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

Current restrictions:

* no relationships between models
* no transactions
* cannot use OR in filters

Documentation
-------------

[The documentation](http://phormium.readthedocs.org/en/latest/) is hosted by
ReadTheDocs.org.

Also have a look at some quick examples in the `examples` directory.

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
