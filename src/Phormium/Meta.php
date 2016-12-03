<?php

namespace Phormium;

/**
 * Meta-data for a {@link Model}.
 */
class Meta
{
    /** Database table onto which the object is mapped. */
    private $table;

    /** The database in which the table is located (as defined in JSON config). */
    private $database;

    /** The name of the class onto which the table is mapped. */
    private $class;

    /** Array of columns, and associated data. */
    private $columns;

    /** Array of columns which form the primary key. If not set, the model will not be writable. */
    private $pk;

    /** Array of all columns except the primary key column. Only populated if pk is set. */
    private $nonPK;

    public function __construct($table, $database, $class, array $columns, $pk, array $nonPK)
    {
        $this->table = $table;
        $this->database = $database;
        $this->class = $class;
        $this->columns = $columns;
        $this->pk = $pk;
        $this->nonPK = $nonPK;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getClass()
    {
        return $this->class;
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

    public function columnExists($name)
    {
        return in_array($name, $this->columns);
    }
}
