=====
Usage
=====

Now that a database table and the corresponding PHP model are created, you can
start using Phormium.

Bootstrap
---------

If you installed Phormium via Composer, just include `vendor/autoload.php` in
your application and Phormium will be autoloaded. Afterwards, you have to
configure Phormium using your `configuration file <setup.html>`_.

.. code-block:: php

    require 'vendor/autoload.php';

    Phormium\DB::configure('/path/to/config.json');

Alternatively, if you are not using Composer, Phormium has it's own autoloader:

.. code-block:: php

    require '/path/to/phormium/src/Phormium/Autoloader.php';

    Phormium\Autoloader::register();
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

In order to retrieve only selected rows, `QuerySets` can be filtered. Filters
are used to consturct a WHERE clause in the resulting SQL query.

For example:

.. code-block:: php

    Person::objects()
        ->filter('birthday', '<' '2000-01-01')
        ->fetch();

This will result in the following query:

.. code-block:: sql

    SELECT ... FROM person WHERE birthday < ?;

Since Phormium uses
`prepared statements <http://php.net/manual/en/pdo.prepared-statements.php>`_,
the values for each filter are given as `?` and are passed in separately when
executing the query. This prevents any possibility of SQL injection.

Filters can be chained; chanining multiple filters will AND them

.. code-block:: php

    Person::objects()
        ->filter('birthday', '<', '2000-01-01')
        ->filter('income', '>', 10000)
        ->fetch();

This will create:

.. code-block:: sql

    SELECT ... FROM person WHERE birthday < ? AND income > 10000;

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

Composite filters
~~~~~~~~~~~~~~~~~

In order to create complex where clauses, Phormium provides composite filters.
Composite filters are collections of Column filters joined by either AND or OR
operator.

To make creating complex filters easier, two factory methods exist:
`Filter::_and()` and `Filter::_or()`. These are prefixed by `_` because `and`
and `or` are PHP keywords and cannot be used as method names.

Composite filters can be chained and combined. For example:

.. code-block:: php

    Person::objects()->filter(
        Filter::_or(
            Filter::_and(
                array('id', '>=', 10),
                array('id', '<=', 20)
            ),
            Filter::_and(
                array('id', '>=', 50),
                array('id', '<=', 60)
            ),
            array('id', '>=', 100),
        )
    )->fetch();

This will translate to:

.. code-block:: sql

    SELECT
        ...
    FROM
        person
    WHERE ((
        (id >= ? AND id <= ?) OR
        (id >= ? AND id <= ?) OR
        id >= ?
    ));


Ordering data
-------------

QuerySets can also be ordered to determine the order in which matching records
will be returned.

To apply ordering:

.. code-block:: php

    Person::objects()
        ->orderBy('id', 'desc')
        ->fetch();

Ordering by multiple columns:

.. code-block:: php

    Person::objects()
        ->orderBy('id', 'desc')
        ->orderBy('name', 'asc')
        ->fetch();


Fetching data
-------------

There are several methods for fetching data. All these methods perform SQL
queries on the database.

.. list-table:: Fetch methods
   :widths: 20 80

   * - `fetch()`_
     - Fetches records as objects.
   * - `single()`_
     - Fetches a single record as an object.
   * - `values()`_
     - Fetches records as associative arrays (for given columns).
   * - `valuesList()`_
     - Fetches records as number-indexed arrays (for given columns).
   * - `valuesFlat()`_
     - Fetches values from a single column.
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

valuesFlat()
~~~~~~~~~~~~

Fetches values from a single column.

Similar to calling `values()` with a single column, but returns a 1D array,
where `values()` would return a 2D array.

.. code-block:: php

    Person::objects()->valuesFlat('name');

This will return:

.. code-block:: php

    array(
        'Ivan',
        'Marko'
    )

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

Limited fetch
-------------

Limited fetch allows you to retrieve only a portion of results matched by a
`QuerySet`. This will limit the data returned by `fetch()`_, `values()`_ and
`valuesList()`_ methods. `distinct()`_ is currently unaffected.

.. code-block:: php

    QuerySet::limit($limit, $offset)

If a `$limit` is given, that is the maximum number of records which will be
returned by the fetch methods. It is possible fetch will return fewer records
if the query itself yields less rows. Specifying NULL means without limit.

If `$offset` is given, that is the number of rows which will be skipped from
the matched rows.

For example to return a maximum of 10 records:

.. code-block:: php

    Person::objects()
        ->limit(10)
        ->fetch();

It often makes sense to use `limit()`_ in conjunction with `orderBy()`_ because
otherwise you will get un unpredictable set of rows, depending on how the
database decides to order them.

.. code-block:: php

    Person::objects()
        ->orderBy('name')
        ->limit(10, 20)
        ->fetch();

This request returns a maximum of 10 rows, while skipping the first 20 records
ordered by the `name` column.

.. _orderBy(): #ordering-data
.. _limit(): #limited-fetch

Writing data
------------

Creating records
~~~~~~~~~~~~~~~~

To create a new record in `person`, just create a new `Person` object and
`save()` it.

.. code-block:: php

    $person = new Person();
    $person->name = "Frank Zappa";
    $person->birthday = "1940-12-20";
    $person->save();

If the primary key column is auto-incremented, it is not necessary to manually
assign a value to it. The `save()` method will persist the object to the
database and populate the primary key property of the Person object with the
value assigned by the database.

It is also possible to create a model from data contained within an array (or
object) by using the static `fromArray()` method.

.. code-block:: php

    // This is quivalent to the above example
    $personData = array(
        "name" => "Frank Zappa",
        "birthday" => "1940-12-20"
    );
    Person::fromArray($personData)->save();

Updating records
~~~~~~~~~~~~~~~~

To change an single existing record, fetch it from the database, make the
required changes and call `save()`.

.. code-block:: php

    $person = Person::get(37);
    $person->birthday = "1940-12-21";
    $person->salary = 10000;
    $person->save();

If you have an associative array (or object) containing the data which you want
to modify in a model instance, you can use the `merge()` method.

.. code-block:: php

    // This is quivalent to the above example
    $updates = array(
        "birthday" => "1940-12-21"
        "salary" => 10000
    );

    $person = Person::get(37);
    $person->merge($updates);
    $person->save();

To change multiple records at once, use the `QuerySet::update()` function. This
function performs an update query on all records currently selected by the
`QuerySet`.

.. code-block:: php

    $person = Person::objects()
        ->filter('name', 'like', 'X%')
        ->update([
            'name' => 'Xavier'
        ]);

This will update all Persons whose name starts with a X and set their name to
'Xavier'.

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

Custom queries
--------------

Every ORM has it's limits, and that goes double for Phormium. Sometime it's
necessary to write the SQL by hand. This is done by fetching the desired
`Connection` object and using provided methods.

execute()
~~~~~~~~~~~~~~~

.. code-block:: php

    Connection::execute($query)

Executes the given SQL without preparing it. Does not fetch. Useful for INSERT,
UPDATE or DELETE queries which do not return data.

.. code-block:: php

    // Lowercase all names in the person table
    $query = "UPDATE person SET name = LOWER(name);
    $conn = DB::getConnection('myconn');
    $numRows = $conn->execute($query);

Where `myconn` is a connection defined in the config file.

query()
~~~~~~~

.. code-block:: php

    Connection::query($query[, $fetchStyle[, $class]])

Executes the given SQL without preparing it. Fetches all rows returned by the 
query. Useful for SELECT queries without arguments.

* `$fetchStyle` can be set to one of PDO::FETCH_* constants, and it determines
  how data is returned to the user. This argument is optional and defaults to
  `PDO::FETCH_ASSOC`.

* `$class` is used in conjunction with PDO::FETCH_CLASS fetch style. Optional.
  If set, the records will be returned as instances of this class.

For more info, see `PDOStatement`_ documentation.

.. _PDOStatement: http://www.php.net/manual/en/pdostatement.fetch.php

.. code-block:: php

    $query = "SELET * FROM x JOIN y ON x.pk = y.fk";
    $conn = DB::getConnection('myconn');
    $data = $conn->query($query);

preparedQuery()
~~~~~~~~~~~~~~~

.. code-block:: php

    Connection::preparedQuery($query[, $arguments[, $fetchType[, $class]]])

Prepares the given SQL query, and executes it using the provided arguments.
Fetches and returns all data returned by the query. Useful for queries which
have arguments.

* `$arguments` is an array of values with as many elements as there are bound
  parameters in the SQL statement being executed. Can be ommitted if no
  arguments are required.

* `$fetchStyle` and `$class` are the same as for `query()`_.

The arguments can either be unnamed:

.. code-block:: php

    $query = "SELET * FROM x JOIN y ON x.pk = y.fk WHERE col1 > ? AND col2 < ?";
    $arguments = array(10, 20);
    $conn = DB::getConnection('myconn');
    $data = $conn->preparedQuery($query, $arguments);

Or they can be named:

.. code-block:: php

    $query = "SELET * FROM x JOIN y ON x.pk = y.fk WHERE col1 > :val1 AND col2 < :val2";
    $arguments = array(
        "val1" => 10,
        "val2" => 20
    );
    $conn = DB::getConnection('myconn');
    $data = $conn->preparedQuery($query, $arguments);

Direct PDO access
~~~~~~~~~~~~~~~~~

If all else fails, you can fetch the underlying PDO connection object and work
with it as you like.

.. code-block:: php

    $pdo = DB::getConnection('myconn')->getPDO();
    $stmt = $pdo->prepare($query);
    $stmt->execute($args);
    $data = $stmt->fetchAll();
