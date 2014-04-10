=============
Configuration
=============

Phormium uses a configuration file to configure databases to which to connect.
JSON and YAML notations are natively supported.

The configuration comprises of the following options:

`databases`
    One or more databases to which you wish to connect. Indexed by a database
    name (here `mydb1` and `mydb2`) which will be used later in the model to
    determine in which database the table is located. Each database should
    contain the DSN (see PDO_ for details). Username and password are optional.

.. _PDO: http://www.php.net/manual/en/pdo.construct.php


JSON example:

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
        }
    }

YAML example:

.. code-block:: yaml

    databases:
        mydb1:
            dsn: mysql:host=localhost;dbname=db1
            username: myuser
            password: mypass
        mydb2:
            dsn: sqlite:/path/to/db2.sqlite

To configure Phormium, pass the path to the configuration file to the configure
method.

.. code-block:: php

    Phormium\DB::configure('/path/to/config.json');
    Phormium\DB::configure('/path/to/config.yaml');

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
        ]
    ]);

.. note:: Short array syntax `[ ... ]` requires PHP 5.4+.
