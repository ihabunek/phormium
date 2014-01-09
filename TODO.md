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

Raw filters
-----------

Implement raw filters for user to write manually, instead of using existing
ones. Some examples:

Can have literals left of the operator:

```
$qs->rawFilter("1=1");
```

Arguments passed to perform a prepared query:

```
$qs->rawFilter("column = :value", ['value' => 3]);
```

Can use functions and operators:

```
$qs->rawFilter("column = :value * 2", ['value' => 3]);
$qs->rawFilter("lower(column) like lower(:value)", ['value' => "FOO"]);
```

