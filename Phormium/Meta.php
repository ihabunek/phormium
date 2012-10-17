<?php

namespace Phormium;

/**
 * Meta-data for a {@link Model}.
 */
class Meta
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

    /** Array of all columns except the primary key column. */
    public $nonPK;
}
