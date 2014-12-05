=========
Relations
=========

Phormium allows you to define relations between models for tables which are
linked via a foreign key.

Consider a Person and Contact tables like these:

.. image:: ./images/relation.png

The Contact table has a foreign key which references the Person table via
the ``person_id`` field. This makes Person the parent table, and Contact the
child table. Each Person record can have zero or more Contact records.

To keep things simple, relations are not defined in the model meta-data, but
by invoking the following methods on the Model:

* ``hasChildren()`` method can be invoked on the parent model (Person), and
  will return a QuerySet for the child model (Contact) which is filtered to
  include all the child records linked to the parent model on which the method
  is executed. This QuerySet can contain zero or more records.

* ``hasParent()`` method can be invoked on the child model (Contact), and will
  return a QuerySet for the parent model (Person) which is filtered to include
  it's parent Person record.

Example
-------

Models for these tables might look like this:

.. code-block:: php

    class Person extends Phormium\Model
    {
        protected static $_meta = array(
            'database' => 'exampledb',
            'table' => 'person',
            'pk' => 'id'
        );

        public $id;

        public $name;

        public function contacts()
        {
            return $this->hasChildren("Contact");
        }
    }

.. code-block:: php

    class Contact extends Phormium\Model
    {
        protected static $_meta = array(
            'database' => 'exampledb',
            'table' => 'contact',
            'pk' => 'id'
        );

        public $id;

        public $person_id;

        public $value;

        public function person()
        {
            return $this->hasParent("Person");
        }
    }

Note that these functions return a filtered QuerySet, so you need to call
one of the fetching methods to fetch the data.

.. code-block:: php

    // Fetching person's contacts
    $person = Person::get(1);
    $contacts = $person->contacts()->fetch();

    // Fetching contact's person
    $contact = Contact::get(5);
    $person = $contact->person()->single();


Returning a QuerySet allows you to further filter the result. For example, to
return person's contact whose value is not null:

.. code-block:: php

    $person = Person::get(1);

    $contacts = $person->contacts()
        ->filter('value', 'NOT NULL')
        ->fetch();

Overriding defaults
-------------------

Phormium does it's best to guess the names of the foreign key column(s) in both
tables. The guesswork, however depends on:

* Naming classes in CamelCase (e.g. ``FooBar``)
* Naming tables in lowercase using underscores (e.g. ``foo_bar``)
* Naming foreign keys which reference the ``foo_bar`` table  ``foo_bar_$id``,
  where ``$id`` is the name of the primary key column in ``some_table``.

The following code:

.. code-block:: php

    $this->hasChildren("Contact");

is shorthand for:

.. code-block:: php

    $this->hasChildren("Contact", "person_id", "id");

where ``person_id`` is the name of the foreign key column in the child table
(Contact), and ``id`` is the name of the referenced primary key column in the
parent table (Person).

If your keys are named differently, you can override these settings. For
example:

.. code-block:: php

    $this->hasChildren("Contact", "owner_id");

Composite keys
--------------

Relations also work for tables with composite primary/foreign keys.

For example, consider these tables:

.. image:: ./images/relation-composite.png

Models for these tables can be implemented as:

.. code-block:: php

    class Post extends Phormium\Model
    {
        protected static $_meta = array(
            'database' => 'exampledb',
            'table' => 'post',
            'pk' => ['date', 'no']
        );

        public $date;

        public $no;

        public function tags()
        {
            return $this->hasChildren("Tag");
        }
    }

.. code-block:: php

    class Tag extends Phormium\Model
    {
        protected static $_meta = array(
            'database' => 'exampledb',
            'table' => 'tag',
            'pk' => 'id'
        );

        public $id;

        public $post_date;

        public $post_no;

        public function post()
        {
            return $this->hasParent("Post");
        }
    }
