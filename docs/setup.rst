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
        "databases": {
            "testdb": {
                "dsn": "mysql:host=localhost;dbname=testdb",
                "username": "myuser",
                "password": "mypass"
            }
        }
    }


For details on database specific DSNs consult the `PHP documentation
<http://www.php.net/manual/en/pdo.construct.php>`_.

A more detailed config file reference can be found in the :doc:`configuration
chapter <configure>`.

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

Public properties of the `Person` class match the column names of the `person`
database table.

Additionaly, a protected static `$_meta` property is required which holds an
array with the following values:

`database`
    Name of the database, as defined in the configuration.
`table`
    Name of the database table to which the model maps.
`pk`
    Name of the primary key column (or an array of names for composite primary
    keys). If not defined, will default to "id", if that column exists.

Try it out
----------

Create a few test rows in the database table and run the following code to fetch
them:

.. code-block:: php

    require 'vendor/autoload.php';
    require 'Person.php';

    Phormium\Orm::configure('config.json');

    $persons = Person::objects()->fetch();

Learn more about usage in the :doc:`next chapter <usage>`.
