<?php

namespace Phormium;

use ReflectionClass;
use ReflectionProperty;

/**
 * Constructs Meta objects for Model classes.
 */
class MetaBuilder
{
    private static $defaultPK = ['id'];

    /**
     * Creates a Meta object for the given model class.
     *
     * @param  [type] $class [description]
     * @return [type]        [description]
     */
    public function build($class)
    {
        // Verify input param is a Model instance
        $this->checkModel($class);

        // Fetch user-defined model meta-data
        $_meta = $class::getMeta();
        if (!is_array($_meta)) {
            throw new \Exception("Invalid $class::\$_meta. Not an array.");
        }

        // Construct the Meta
        $meta = new Meta();
        $meta->class = $class;
        $meta->database = $this->getDatabase($class, $_meta);
        $meta->table = $this->getTable($class, $_meta);
        $meta->columns = $this->getColumns($class);
        $meta->pk = $this->getPK($class, $_meta, $meta->columns);
        $meta->nonPK = $this->getNonPK($class, $meta->columns, $meta->pk);

        return $meta;
    }

    /**
     * Returns class' public properties which correspond to column names.
     *
     * @return array
     */
    protected function getColumns($class)
    {
        $columns = [];

        $rc = new ReflectionClass($class);
        $props = $rc->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $columns[] = $prop->name;
        }

        if (empty($columns)) {
            throw new \Exception("Model $class has no defined columns (public properties).");
        }

        return $columns;
    }

    /**
     * Verifies that the given classname is a valid class which extends Model.
     *
     * @param  string $class The name of the class to check.
     *
     * @throws InvalidArgumentException If this is not the case.
     */
    protected function checkModel($class)
    {
        if (!is_string($class)) {
            throw new \InvalidArgumentException("Invalid model given");
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Class \"$class\" does not exist.");
        }

        if (!is_subclass_of($class, "Phormium\\Model")) {
            throw new \InvalidArgumentException("Class \"$class\" is not a subclass of Phormium\\Model.");
        }
    }

    /** Extracts the primary key columns. */
    protected function getPK($class, $meta, $columns)
    {
        if (empty($meta['pk'])) {
            $pk = self::$defaultPK;
        } elseif (is_string($meta['pk'])) {
            $pk = [$meta['pk']];
        } elseif (is_array($meta['pk'])) {
            $pk = $meta['pk'];
        } else {
            throw new \Exception("Invalid primary key given in $class::\$_meta. Not a string or array.");
        }

        // Verify primary key column(s) exist
        foreach ($pk as $column) {
            if (!in_array($column, $columns)) {
                throw new \Exception("Invalid $class::\$_meta. Specified primary key column \"$column\" does not exist.");
            }
        }

        return $pk;
    }

    /** Extracts the non-primary key columns. */
    protected function getNonPK($class, $columns, $pk)
    {
        return array_values(
            array_filter($columns, function($column) use ($pk) {
                return !in_array($column, $pk);
            })
        );
    }

    /** Extracts the database name from user given model metadata. */
    protected function getDatabase($class, $meta)
    {
        if (empty($meta['database'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing \"database\".");
        }

        return $meta['database'];
    }

    /** Extracts the table name from user given model metadata. */
    protected function getTable($class, $meta)
    {
        if (empty($meta['table'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing \"table\".");
        }

        return $meta['table'];
    }
}
