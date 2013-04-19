==========
Setting up
==========

Unlike some ORMs, Phormium does not automatically generate the database model or
the PHP classes onto which the model is mapped. This has to be done manually.

Configure database connections
------------------------------

Create a JSON configuration file which contains database definitions you wish to
use. Each database must have a DSN string, and optional username and password if
required.

.. code-block:: javascript

    {
        "testdb": {
            "dsn": "mysql:host=localhost;dbname=testdb",
            "username": "myuser",
            "password": "mypass"
        }
    }


For details on database specific DSNs consult the `PHP documentation 
<http://www.php.net/manual/en/pdo.construct.php>`_.

Create a database model
-----------------------

You need a database table which will be mapped. For example, the following SQL
will create a MySQL table called `person`:

.. code-block:: sql

    CREATE TABLE person (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100),
        birthday DATE,
        salary DECIMAL
    );

The table does not have to have a primary key, but if it doesn't Phormium will
not perform update or delete queries.

Create a Model class
--------------------

To map the `person` table onto a PHP class, a corresponding Model class is
defined. Although this class can be called anything, it's sensible to name it
the same as the table being mapped.

.. code-block:: php

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

Public properties of the Person class match the column names of the `person`
database table.

Additionaly, a protected static $_meta property is required which holds an array
with the following values:

- database - name of the database, as defined in the `configuration 
  <#configure-database-connections>`_
- table - name of the database table
- pk - name of the primary key column (or an array of names for composite
  primary keys)

Now that a database table and the corresponding PHP model are created, you can
begin :doc:`using Phormium <usage>`.
