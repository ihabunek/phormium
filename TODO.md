TODO
====

Some ideas for the future.

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
