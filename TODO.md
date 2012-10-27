TODO
====

Some ideas to implement in the future.

Batch update/delete
-------------------

Add update() and delete() to query set. These actions would affect all objects in current filter.

e.g. Change all Johns to Mark.

```php
Person::objects()
    ->filter(f::eq('name', 'John'))
    ->update(['name' => 'Mark']);
```

e.g. Delete all people born before year 2000.

```php
Person::objects()
    ->filter(f::lt('birthday', '2000-01-01'))
    ->delete();
```

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
