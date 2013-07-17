TODO
====

Fix models with no Primary Key
------------------------------

Writing those to database seems to be broken at the moment. Reading is fine.

Make QuerySets iterable
-----------------------

Perform lazy iteration over a QuerySet, by implementing the
[Iterator](http://php.net/manual/en/class.iterator.php) interface. Each record
is fetched when needed, instead of fetching all and then iterating over the
array.

e.g. Iterate over all Person records:

```php
foreach(Person::objects() as $person)
{
    // do something
}
```
