TODO
====

Fix models with no Primary Key
------------------------------

Writing those to database seems to be broken at the moment. Reading is fine.

Make QuerySets iterable
-----------------------

Perform lazy iteration over a QuerySet, by implementing the [Iterator](http://php.net/manual/en/class.iterator.php) interface. Each record is fetched when needed, instead of fetching all and then iterating over the array.

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

e.g. Perhaps something like:

```php
Person::objects()
    ->filter(f::or(
        f::gte('birthday', '2012-01-01'),
        f::lt('birthday', '2011-01-01')
    ))
```

e.g. Nesting of f::and() f::or()

```php
Person::objects()
    ->filter(
        f::or(
            f::and(
                f::gte('birthday', '2012-01-01'),
                f::lt('income', 10000)
            ),
            f::and(
                f::lt('birthday', '2012-01-01'),
                f::gt('income', 10000)
            )
        )
    )
```
