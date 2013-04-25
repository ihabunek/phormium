Phormium Example using SQLite
=============================
Prerequisites for this example:
* [sqlite](http://www.sqlite.org/) - to create a test database
* [pdo_sqlite](http://php.net/manual/en/ref.pdo-sqlite.php) extension - for
  Phormium to connect to the test database

Before running the example script, you will need to create a sqlite database
with a table and some data:

    sqlite3 example.sq3 < person.sql

This creates a database in `example.sq3`, and within it a table called `person`
with some test data.

Database config file (config.json)
----------------------------------
You need a config file which defines your databases. These are contained in
`config.json`.

You can use anything as the database name, you will use this name to reference
the database in the model. In this example it's called `exampledb`.

Entity class (Person.php)
-------------------------
To map the `person` table onto a PHP class, a corresponding Model class is
needed, which must extend Phormium\Model. In this example, the class is called
Person (can be named anything, but this is logical).

Public properties of the Person class match the column names of the `person`
database table.

Additionaly, a protected static $_meta property is required which holds an array
with the following values:
- connection - name of the database, as defined in `config.json`
- table - name of the database table
- pk - name of the primary key column (required, composite keys not supported)

Examples
--------
Now run the provided example files:
* example1.php
* example2.php
* example3.php
