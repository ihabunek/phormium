============
Installation
============

Prerequisites
-------------

Phormium requires PHP 5.4 or greater with the PDO_ extension loaded, as well as
any PDO drivers for databases to wich you wish to connect.

.. _PDO: http://php.net/manual/en/book.pdo.php

Via Composer
------------

The most flexible installation method is using Composer.

Create a `composer.json` file in the root of your project:

.. code-block:: javascript

    {
        "require": {
            "phormium/phormium": "0.*"
        }
    }

Install composer:

.. code-block:: bash

    curl -s http://getcomposer.org/installer | php

Run Composer to install Phormium:

.. code-block:: bash

    php composer.phar install

To upgrade Phormium to the latest version, run:

.. code-block:: bash

    php composer.phar update

Once installed, include `vendor/autoload.php` in your script to autoload
Phormium.

.. code-block:: bash

    require 'vendor/autoload.php';

From GitHub
-----------

The alternative is to checkout the code directly from GitHub:

.. code-block:: bash

    git clone https://github.com/ihabunek/phormium.git

In your code, include and register the Phormium autoloader:

.. code-block:: bash

    require 'phormium/Phormium/Autoloader.php';
    \Phormium\Autoloader::register();

Once you have installed Phormium, the next step is to :doc:`set it up <setup>`.
