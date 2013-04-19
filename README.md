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
* chaining filters
* ordering

Things currently not supported by Phormium:

* no joins of any kind
* cannot use OR in filters

Documentation
-------------

[The documentation]() is hosted by ReadTheDocs.org.

Also have a look at some quick examples in the `examples` directory.

Why?
----

"Why another ORM?!?", I hear you cry.

There are two reasons:

* I work a lot on Informix on my day job and no other ORM I found supports it.
* Writing an ORM is a great experience. You should try it.

Let me know what you think!

Ivan Habunek [@ihabunek](http://twitter.com/ihabunek)

License
-------
Licensed under the MIT license. `See LICENSE.md`.
