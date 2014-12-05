<?php

namespace Phormium;

/**
 * Meta-data for a {@link Model}.
 */
class Meta
{
    /** Database table onto which the object is mapped. */
    public $table;

    /** The database in which the table is located (as defined in JSON config). */
    public $database;

    /** Array of columns, and associated data. */
    public $columns;

    /** The name of the class onto which the table is mapped. */
    public $class;

    /** Array of columns which form the primary key. If not set, the model will not be writable. */
    public $pk;

    /** Array of all columns except the primary key column. Only populated if pk is set. */
    public $nonPK;

    // -- Accessors ------------------------------------------------------------

    public function getTable()
    {
        return $this->table;
    }

    public function getDatabase()
    {
        return $this->databse;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getPkColumns()
    {
        return $this->pk;
    }

    public function getNonPkColumns()
    {
        return $this->nonPK;
    }

    public function getClass()
    {
        return $this->class;
    }
}
