Phormium Example using SQLite
=============================
The prerequisite for these examples is the
[pdo_sqlite](http://php.net/manual/en/ref.pdo-sqlite.php) extension so
Phormium can to connect to the test database.

You may also want to have [sqlite](http://www.sqlite.org/) in order to
(re)create the test database.

The test database `example.sq3` is provided, but if you wish to recreate it,
you can run the following in the example dir:

    sqlite3 example.sq3 < setup.sql

Database config file (config.json)
----------------------------------
The Phormium configuration file defines where the database is located.

Model classes
-------------

This example uses several Model classes, which are located in the `models`
directory. Model classes extend `Phormium\Model` and map onto a database table.

Public properties of each Model class match the column names of the
corresponding database table.

Additionaly, they contain a protected static `$_meta` array which contains
the following values:
- database - name of the database, as defined in `config.json`
- table - name of the database table
- pk - the primary key column(s)
