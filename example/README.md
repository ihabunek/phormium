Phormium Example using SQLite
=============================
Prerequisites for this example:
* [sqlite](http://www.sqlite.org/) - to create a test database
* [pdo_sqlite](http://php.net/manual/en/ref.pdo-sqlite.php) extension - for Phormium to connect to sqlite

Before running the example script, you need to create an sqlite database with a table and some data:
```
sqlite3 example.sq3 < model.sql
```

This creates a database in `example.sq3`, and within it a table called `person` with some test data.

Here's the code used to create the table:
```
CREATE TABLE person(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100),
    birthday DATE
);
```

DB config file (config.json)
----------------------------
You need a config file which defines your database connections. These are contained in `config.json`.

Only one connection is defined, for the database created above. The connection is named `myconnection`, this is later referenced when mapping objects to the database.

Config file:
```
{
    "myconnection": {
        "dsn": "sqlite:example.sq3",
        "username": "",
        "password": ""
    }
}
```

Entity class (Person.php)
-------------------------
To map the `person` table onto a PHP class, a corresponding class is created:

```
/**
 * @connection myconnection
 * @table person
 */
class Person extends Phormium\Entity
{
    /** @pk */
    public $id;
    
    public $name;
    
    public $birthday;
}
```

Public properties of the Person class should match the column names of the `person` database table.

The required annotatios are:
- @connection - determines which connection from `config.json` to use
- @table - which table the class maps onto
- @pk - set on the primary key column to enable fetching records by primary key


Example file (example.php)
--------------------------
Now run `example.php` and see what happens.
