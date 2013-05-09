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

Make OR-able filters
--------------------

Currently all filters are joined with AND. Consider enabling OR operation on filters.

Perhaps something like:

```php
Person::objects()
    ->filter(Filter::or(
        array('birthday', '=', '2012-01-01'),
        array('birthday', '=', '2011-01-01')
    ))
```

Also consider nesting of OR and AND, maybe like:

```php
Person::objects()
    ->filter(
        Filter::or(
            Filter::and(
                ['birthday', '=', '2012-01-01'],
                ['income', '=', 10000)
            ),
            Filter::and(
                ['birthday', '=', '2012-01-01'),
                ['income', '=', 10000]
            )
        )
    )
```
