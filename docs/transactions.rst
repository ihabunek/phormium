============
Transactions
============

Phormium has two ways of using transactions.

The transaction is global, meaning it will be started an all required database
connections without the need to know which model is mapped to which database.

Callback transactions
---------------------

By passing a callable to `DB::transaction()`, the code within the callable will
be executed within a transaction. If an exception is thrown within the callback,
the transaction will be rolled back. Otherwise it will be commited once the 
callback is executed.

For example, if you wanted to increase the salary for several Persons, you might
code it this way:

.. code-block:: php

    $ids = array(10, 20, 30);
    $increment = 100;

    DB::transaction(function() use ($ids, $increment) {
        foreach ($ids as $id) {
            $p = Person::get($id);
            $p->income += $increment;
            $p->save();
        }
    });

If any of the person IDs from `$ids` does not exist, `Person::get($id)` will
raise an exception which will roll back any earlier changes done within the
callback.

Manual transactions
-------------------

It is also possible to control the transaction manaully, however this produces
somewhat less readable code.

Equivalent to the callback example would look like:

.. code-block:: php

    $ids = array(10, 20, 30);
    $increment = 100;

    DB::begin();

    try {
        foreach ($ids as $id) {
            $p = Person::get($id);
            $p->income += $increment;
            $p->save();
        }
    } catch (\Exception $ex) {
        DB::rollback();
        throw new \Exception("Transaction failed. Rolled back.", 0, $ex);
    }

    DB::commit();
