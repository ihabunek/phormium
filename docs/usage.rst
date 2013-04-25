=====
Usage
=====

Now that a database table and the corresponding PHP model are created, you can
start using Phormium.

Bootstrap
---------

To bootstrap Phormium, just include `vendor/autoload.php` in your application
and Phormium will be autoloaded.

.. code-block:: php

    require_once __DIR__.'/../vendor/autoload.php';

The second step is to configure Phormium using your `configuration file
<setup.html>`_:

.. code-block:: php

    Phormium\DB::configure('/path/to/config.json');

Querying data
-------------

Fetching a single record by primary key:

.. code-block:: php

    Person::get(13);

Also works for composite primary keys:

.. code-block:: php

    Caption::get('HR', 123);

To fetch all data from a table, run:

.. code-block:: php

    Person::objects()->fetch();

The `objects()` method will return a `QuerySet` object which is used for
querying data, and `fetch()` will form and execute the corresponding SQL query
and return the results as an array of `Person` objects.

Filtering data
--------------

In order to retrieve only selected rows, `QuerySets` can be filtered.

.. code-block:: php

    Person::objects()
        ->filter('birthday', '<' '2000-01-01')
        ->fetch();

This will fetch all Persons born before the year 2000.

Filters can be chained; chanining multiple filters will AND them

.. code-block:: php

    Person::objects()
        ->filter('birthday', '<', '2000-01-01')
        ->filter('income', '>', 10000)
        ->fetch();

This will fetch Persons who are born before year 2000 and who have an income
greater than 10000.

QuerySets are lazy - no queries will be executed on the database until one of
the `fetch methods <#fetching-data>`_ are called.

Each time a filter is added to a `QuerySet`, a new instance is created which is
not bound to the previous instance. Each additional filtering creates a distinct
`QuerySet` object which can be stored and reused.

Available filters:

.. code-block:: php

    Person::objects()
        ->filter($column, '=',  $value)
        ->filter($column, '!=', $value)
        ->filter($column, '>',  $value)
        ->filter($column, '>=', $value)
        ->filter($column, '<',  $value)
        ->filter($column, '<=', $value)
        ->filter($column, 'IN', $array)
        ->filter($column, 'NOT IN', $array)
        ->filter($column, 'LIKE', $value)
        ->filter($column, 'ILIKE', $value)  // case insensitive like
        ->filter($column, 'NOT LIKE', $value)
        ->filter($column, 'BETWEEN', array($low, $high))
        ->filter($column, 'IS NULL')
        ->filter($column, 'NOT NULL')

Fetching data
-------------

There are several methods for fetching data. All these methods perform SQL
queries on the database.

.. list-table:: Fetch methods
   :widths: 20 80
   :header-rows: 1

   * - `fetch()`_
     - Fetches records as objects.
   * - `fetch()`_
     - Fetches a single record as an object.
   * - `values()`_
     - Fetches records as associative arrays (can select columns).
   * - `valuesList()`_
     - Fetches records as number-indexed arrays (can select columns).
   * - `count()`_
     - Returns the number of records matching the filter.
   * - `distinct()`_
     - Returns distinct values of given columns.

fetch()
~~~~~~~

Fetch all records matching the given filter and returns them as an array of
Model objects.

.. code-block:: php

    Person::objects()
        ->filter('birthday', '<', '2000-01-01')
        ->filter('income', '>', 10000)
        ->fetch();

Fetch can also be limited. The following query will fetch first 50 records,
ordered by first_name column:

.. code-block:: php

    Person::objects()
        ->orderBy('first_name', 'asc')
        ->fetch(50);

Offset can be applied as the second argument:

.. code-block:: php

    Person::objects()
        ->orderBy('first_name', 'asc')
        ->fetch(50, 100);

This will produce a SELECT query with `OFFSET 100 LIMIT 50`. In other words, it
will fetch 50 people starting with the 101st person, sorted by first name.

single()
~~~~~~~~

Similar to `fetch()` but expects that the filter will match a single record.
Returns just the single Model object, not an array.

This method will throw an exception if zero or multiple records are matched by
the filter.

For example, to fetch the person with id = 13:

.. code-block:: php

    Person::objects()
        ->filter('id', '=', 13)
        ->single();

This can also be achieved by the `get()` shorthand method:

.. code-block:: php

    Person::get(13);

values()
~~~~~~~~

Similar to fetch(), but returns records as associative arrays instead of
objects.

Additionally, it's possible to specify which columns to fetch from the database:

.. code-block:: php

    Person::objects()->values('id', 'name');

This will return:

.. code-block:: php

    array(
        array('id' => '1', 'name' => 'Ivan'),
        array('id' => '1', 'name' => 'Marko'),
    )

If no columns are specified, all columns in the model will be fetched.

valuesList()
~~~~~~~~~~~~

Similar to fetch(), but returns records as number-indexed arrays instead of
objects.

Additionally, it's possible to specify which columns to fetch from the database:

.. code-block:: php

    Person::objects()->valuesList('id', 'name');

This will return:

.. code-block:: php

    array(
        array('1', 'Ivan'),
        array('1', 'Marko'),
    )

If no columns are specified, all columns in the model will be fetched.

count()
~~~~~~~

Returns the number of records matching the given filter.

.. code-block:: php

    Person::objects()
        ->filter('income', '<', 10000)
        ->count();

This returns the number of Persons with income under 10k.

distinct()
~~~~~~~~~~

Returns the distinct values in given columns matching the current filter.

.. code-block:: php

    Person::objects()
        ->filter('birthday', '>=', '2001-01-01')
        ->distinct('name');

    Person::objects()
        ->filter('birthday', '>=', '2001-01-01')
        ->distinct('name', 'income');

The first query will return an array of distinct names for all people born in
this millenium:

.. code-block:: php

    array('Ivan', 'Marko');

While the second returns the distinct combinations of name and income:

.. code-block:: php

    array(
        array(
            'name' => 'Ivan',
            'income' => '5000'
        ),
        array(
            'name' => 'Ivan',
            'income' => '7000'
        ),
        array(
            'name' => 'Marko',
            'income' => '3000'
        ),
    )

Note that if a single column is requested, the method returns an array of
values from the database, but when multiple columns are requested, then an array
of associative arrays will be returned.

Aggregates
~~~~~~~~~~

The following aggregate functions are implemented on the QuerySet object:

* `avg($column)`
* `min($column)`
* `max($column)`
* `sum($column)`

Aggregates are applied after filtering. For example:

.. code-block:: php

    Person::objects()
        ->filter('birthday', '<', '2000-01-01')
        ->avg('income');

Returns the average income of people born before year 2000.

Writing data
------------

Creating records
~~~~~~~~~~~~~~~~

To create a new record in `person`, just create a new `Person` object and
`save()` it.

.. code-block:: php

    // Create a new person and save it to the database
    $person = new Person();
    $person->name = "Frank Zappa";
    $person->birthday = "1940-12-20";
    $person->save();

If the primary key column is auto-incremented, it is not necessary to manually
assign a value to it. The `save()` method will persist the object to the
database and populate the primary key property of the Person object with the
value assigned by the database.

Updating records
~~~~~~~~~~~~~~~~

To change an single existing record, fetch it from the database, make the
required changes and call `save()`.

.. code-block:: php

    $person = Person::get(37);
    $person->birthday = "1940-12-21";
    $person->save();

To change multiple records at once, use the `QuerySet::update()` function. This
function performs an update query on all records currently selected by the
`QuerySet`.

.. code-block:: php

    $person = Person::objects()
        ->filter('name', 'like', 'X%')
        ->update([
            'name' => 'X-man'
        ]);

This will update all Persons whose name starts with a X and set their name to
'X-man'.

Deleting records
~~~~~~~~~~~~~~~~

Similar for deleting records. To delete a single person:

.. code-block:: php

    Person::get(37)->delete();

To delete multiple records at once, use the `QuerySet::delete()` function. This
will delete all records currently selected by the `QuerySet`.

.. code-block:: php

    $person = Person::objects()
        ->filter('salary', '>', 100000)
        ->delete();

This will delete all Persons whose salary is greater than 100k.
