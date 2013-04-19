Phormium
========
Phormium is a minimalist ORM for PHP.

Works with most relational databases which have a PDO driver.

This is a work in progress. Things will change. Do not use for anything serious,
yet.

[![Build Status](https://travis-ci.org/ihabunek/phormium.png)](https://travis-ci.org/ihabunek/phormium)

Why?
----

Why another ORM, I hear you cry! Well, first and foremost, no other ORM I found
works with Informix, and I'm tied to Informix on my day job. It's a real pain
writing CRUD functions by hand. Second, It's fun to try and write an ORM. Tell
me how you like it.

Quick start
-----------

### Configure database connections

Phormium works with most relational databases which have a
[PDO driver](http://www.php.net/manual/en/pdo.drivers.php).

Create a JSON configuration file which contains database definitions you wish to
use. Each database must have a DSN string, and optional username and password if
required.

```javascript
{
    "testdb": {
        "dsn": "mysql:host=localhost;dbname=testdb",
        "username": "myuser",
        "password": "mypass"
    }
}
```

For details on database specific DSNs consult the
[PHP documentation](http://www.php.net/manual/en/pdo.construct.php).

### Create a database model

Unlike Django, Phormium will not create your database model for you. You're on your own there.

For example, the following SQL will create a MySQL table called `person`:

```sql
CREATE TABLE person (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100),
    birthday DATE,
    salary DECIMAL
);
```

The table does not have to have a primary key, but if it doesn't the model will not perform
update or delete queries.

### Create a Model class

To map the `person` table onto a PHP class, a corresponding Model class is defined. In this example,
the class is called Person (can be named anything).

```php
class Person extends Phormium\Model
{
    // Mapping meta-data
    protected static $_meta = array(
        'database' => 'testdb',
        'table' => 'person',
        'pk' => 'id'
    );

    // Table columns
    public $id;
    public $name;
    public $birthday;
    public $salary;
}
```

Public properties of the Person class match the column names of the `person` database table.

Additionaly, a protected static $_meta property is required which holds an array with the following
values:

- database - name of the database, as defined in `config.json`
- table - name of the database table
- pk - name of the primary key column (or an array of names for composite primary keys)

Now that a database table and the corresponding PHP model are created, we can begin using Phormium.

Querying data
-------------

Fetching a single record by primary key:

```php
Person::get(13);
```

Also works for composite primary keys:

```php
Caption::get('HR', 123);
```

To fetch all data from a table, run:

```php
Person::objects()->fetch();
```

The `objects()` method will return a `QuerySet` object which is used for
querying data, and `fetch()` will form and execute the corresponding SQL query
and return the results as an array of `Person` objects.

### Filtering data

In order to retrieve only selected rows, `QuerySets` can be filtered.

```php
$records = Person::objects()
    ->filter('birthday', '<' '2000-01-01')
    ->fetch();
```

This will fetch all Persons born before the year 2000.

Filters can be chained; chanining multiple filters will AND them

```php
$records = Person::objects()
    ->filter('birthday', '<', '2000-01-01')
    ->filter('income', '>', 10000)
    ->fetch();
```

This will fetch Persons who are born before year 2000 and who have an income
greater than 10000.

`QuerySet`s are lazy - no queries will be executed on the database until one of
the fetch methods are called: `fetch()`, `count()`, `exists()` or one of the
aggregate functions.

Each time a filter is added to a `QuerySet`, a new instance is created which is
not bound to the previous instance. Each additional filtering creates a distinct
`QuerySet` object which can be stored and reused.

#### Available filters

* `filter(<column>, '=',  <value>)`
* `filter(<column>, '!=', <value>)`
* `filter(<column>, '>',  <value>)`
* `filter(<column>, '>=', <value>)`
* `filter(<column>, '<',  <value>)`
* `filter(<column>, '<=', <value>)`
* `filter(<column>, 'IN', <array>)`
* `filter(<column>, 'NOT IN', <array>)`
* `filter(<column>, 'LIKE', <value>)`
* `filter(<column>, 'NOT LIKE', <value>)`
* `filter(<column>, 'BETWEEN', array(<low>, <high>))`
* `filter(<column>, 'IS NULL')`
* `filter(<column>, 'NOT NULL')`

Fetching data
-------------

There are several methods for fetching data. All these methods perform SQL
queries on the database.

#### fetch()

Fetch all records matching the given filter and returns them as an array of
Model objects.

```php
$records = Person::objects()
    ->filter('birthday', '<', '2000-01-01')
    ->filter('income', '>', 10000)
    ->fetch();
```

#### single()

Similar to `fetch()` but expects that the filter will match a single record.
Returns just the single Model object, not an array.

This method will throw an exception if zero or multiple records are matched by
the filter.

For example, to fetch the person with id = 13:

```php
Person::objects()
    ->filter('id', '=', 13)
    ->single();
```

This can also be achieved by the `get()` shorthand method:

```php
Person::get(13);
```

#### count()

Returns the number of records matching the given filter.

```php
Person::objects()
    ->filter('income', '<', 10000)
    ->count();
```

This returns the number of Persons with income under 10k.

#### distinct()

Returns the distinct values in given columns matching the current filter.

```php
Person::objects()
    ->filter('birthday', '>=', '2001-01-01')
    ->distinct('name');

Person::objects()
    ->filter('birthday', '>=', '2001-01-01')
    ->distinct('name', 'income');
```

The first one will return an array of distinct names for all people born in this
millenium, and the second the distinct combinations of name and income. Sample
results:

```php
// Distinct names
array('Ivan', 'Marko');

// Distinct name + income
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
```

#### Aggregates

The following aggregate functions are implemented on the QuerySet object:
* `avg(<column>)`
* `min(<column>)`
* `max(<column>)`
* `sum(<column>)`

Aggregates are applied after filtering. For example:

```php
Person::objects()
    ->filter('birthday', '<', '2000-01-01')
    ->avg('income');
```

Returns the average income of people born before year 2000.

### Fetch types

By default, records are returned as intances of the Model class, in this example Person. However,
it is possible to change this by specifying a fetch type.

Fetch types are defined as constants in `Phormium\DB`:
- `DB::FETCH_OBJECT` - Return results as objects (default)
- `DB::FETCH_ARRAY` - Return results as array

Both `single()` and `fetch()` methods can be given a fetch type as an optional first argument.

For example:

```php
// Fetch person with id=13 as an object
Person::objects()
    ->filter('id', '=', 13)
    ->single();

// Fetch all people with id between 54 and 57, as array
Person::objects()
    ->filter('id', 'between', array(54, 57))
    ->fetch(DB::FETCH_ARRAY);
```

Writing data
------------

### Creating objects

To create a new record in `person`, just create a new `Person` object and `save()` it.

```php
// Create a new person and save it to the database
$person = new Person();
$person->name = "Frank Zappa";
$person->birthday = "1940-12-20";
$person->save();
```

If the primary key column is auto-incremented, it is not necessary to manually
assign a value to it. The `save()` method will persist the object to the
database and populate the primary key property of the Person object with the
value assigned by the database.

### Updating objects

To change an single existing record, fetch it from the database, make the
required changes and call `save()`.

```php
$person = Person::get(37);
$person->birthday = "1940-12-21";
$person->save();
```

To change multiple records at once, use the `QuerySet::update()` function. This
function performs an update query on all records currently selected by the
`QuerySet`.

```php
$person = Person::objects()
    ->filter('name', 'like', 'X%')
    ->update([
        'name' => 'X-man'
    ]);
```

This will update all Persons whose name starts with a X and set their name to
'X-man'.

### Deleting objects

Similar for deleting records. To delete a single person:

```php
Person::get(37)->delete();
```

To delete multiple records at once, use the `QuerySet::delete()` function. This
will delete all records currently selected by the `QuerySet`.

```php
$person = Person::objects()
    ->filter('salary', '>', 100000)
    ->delete();
```

This will delete all Persons whose salary is greater than 100k.
