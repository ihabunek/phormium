=============
Configuration
=============

Phormium uses a JSON configuration file to configure databases to which to
connect and other options.

.. code-block:: javascript

    {
        "databases": {
            "mydb1": {
                "dsn": "mysql:host=localhost;dbname=db1",
                "username": "myuser",
                "password": "mypass"
            },
            "mydb2": {
                "dsn": "sqlite:/path/to/db2.sqlite"
            }
        },
        "logging": true
    }

The configuration comprises of the following options:

`databases` (object)
    One or more databases to which you wish to connect. Indexed by a database
    name (here `mydb1` and `mydb2`) which will be used later in the model to
    determine in which database the table is located. Each database should
    contain the DSN (see PDO_ for details). Username and password are optional.

`logging` (boolean)
    If set to `true`, Phormium will write out SQL queries which it prepares and
    executes. This requires
    `Apache log4php <http://logging.apache.org/log4php/>`_. Defaults to false.

.. _PDO: http://www.php.net/manual/en/pdo.construct.php

To configure Phormium, pass the path to the configuration file to the configure
method.

.. code-block:: php

    Phormium\DB::configure('/path/to/config.json');

Alternatively, you can configure Phormium using an array instead of a
configuration file:

.. code-block:: php

    Phormium\DB::configure([
        "databases" => [
            "db1" => [
                "dsn" => "mysql:host=localhost;dbname=db1",
                "username" => "myuser",
                "password" => "mypass"
            ],
            "db2" => [
                "dsn" => "sqlite:/path/to/db2.sqlite"
            ]
        ],
        "logging" => true
    ]);

.. note:: Short array syntax `[ ... ]` requires PHP 5.4+.
