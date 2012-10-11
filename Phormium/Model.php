<?php

namespace Phormium;

/**
 * Maps a database table to a class (subclass of {@link Entity}).
 */
class Model
{
    /** Database table onto which the object is mapped. */
    public $table;

    /** Connection to the database in which the table is located. */
    public $connection;

    /** Array of columns, and associated data. */
    public $columns;

    /** The name of the class onto which the table is mapped. */
    public $class;

    /** Name of the primary key database column. */
    public $pk;
}
