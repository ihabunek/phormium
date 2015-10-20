=============
Configuration
=============

Phormium uses a configuration array to configure the databases to which to
connect. JSON and YAML files are also supported. To configure Phormium, pass the
configuration array, or a path to the configuration file to
``Phormium\Orm::configure()``.

The configuration array comprises of the following options:

`databases`
    Configuration for one or more databases to which you wish to connect,
    indexed by a database name which is used in the model to determine in which
    database the table is located.

Databases
---------

Each entry in ``databases`` has the following configuration options:

`dsn`
    The Data Source Name, or DSN, contains the information required to connect
    to the database. See `PDO documentation`_ for more information.

`username`
    The username used to connect to the database.

`password`
    The username used to connect to the database.

`attributes`
    Associative array of PDO attributes with corresponding values to be set on
    the PDO connection after it has been created.

    When using a configuration array PDO constants can be used directly
    (e.g. ``PDO::ATTR_CASE``), whereas when using a config file, the constant
    can be given as a string (e.g. ``"PDO::ATTR_CASE"``) instead.

    For available attributes see the `PDO attributes`_ documentation.

.. _PDO documentation: http://www.php.net/manual/en/pdo.construct.php
.. _PDO attributes: http://php.net/manual/en/pdo.setattribute.php

Examples
--------

PHP example
~~~~~~~~~~~

.. code-block:: php

    Phormium\Orm::configure([
        "databases" => [
            "db1" => [
                "dsn" => "mysql:host=localhost;dbname=db1",
                "username" => "myuser",
                "password" => "mypass",
                "attributes" => [
                    PDO::ATTR_CASE => PDO::CASE_LOWER,
                    PDO::ATTR_STRINGIFY_FETCHES => true
                ]
            ],
            "db2" => [
                "dsn" => "sqlite:/path/to/db2.sqlite"
            ]
        ]
    ]);

.. note:: Short array syntax `[ ... ]` requires PHP 5.4+.

JSON example
~~~~~~~~~~~~

This is the equivalent configuration in JSON.

.. code-block:: javascript

    {
        "databases": {
            "db1": {
                "dsn": "mysql:host=localhost;dbname=db1",
                "username": "myuser",
                "password": "mypass",
                "attributes": {
                    "PDO::ATTR_CASE": "PDO::CASE_LOWER",
                    "PDO::ATTR_STRINGIFY_FETCHES": true
                }
            },
            "db2": {
                "dsn": "sqlite:\/path\/to\/db2.sqlite"
            }
        }
    }

.. code-block:: php

    Phormium\Orm::configure('/path/to/config.json');

YAML example
~~~~~~~~~~~~

This is the equivalent configuration in YAML.

.. code-block:: yaml

    databases:
        db1:
            dsn: 'mysql:host=localhost;dbname=db1'
            username: myuser
            password: mypass
            attributes:
                'PDO::ATTR_CASE': 'PDO::CASE_LOWER'
                'PDO::ATTR_STRINGIFY_FETCHES': true
        db2:
            dsn: 'sqlite:/path/to/db2.sqlite'

.. code-block:: php

    Phormium\Orm::configure('/path/to/config.yaml');

