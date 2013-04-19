==========
Installing
==========

The most flexible installation method is using Composer.

Create a `composer.json` file in the root of your project:

.. code-block:: javascript

    {
        "require": {
            "phormium/phormium": "dev-develop"
        }
    }

And run Composer to install Phormium:

.. code-block:: bash

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

Upgrading
---------

To upgrade Phormium to the latest version, run:

.. code-block:: bash

    php composer.phar update

Once you have installed Phormium, the next step is to :doc:`set it up <setup>`.
