TODO
====

Some ideas for the future.

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

Get SQL which will be executed
------------------------------

Something like:

```
$qs->fetch();     // Executes
$qs->fetchSQL();  // Just returns SQL
```

```
$qs->single();
$qs->singleSQL();
```

etc...


It would return something like:
```
[
    "SELECT ... FROM table WHERE field = :value",
    [
        "value" => 1
    ]
]
```

Enable custom FK guessers
-------------------------

Enable setting a custom ModelRelationsTrait::guessForeignKey() function.

This will enable custome db naming schemas to be guessed by phormium, instead
of having to be set manually.
