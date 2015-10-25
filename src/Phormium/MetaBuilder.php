<?php

namespace Phormium;

use Phormium\Exception\InvalidModelException;

use ReflectionClass;
use ReflectionProperty;

/**
 * Constructs Meta objects for Model classes.
 */
class MetaBuilder
{
    /**
     * Primary key to use if a class has no primary key defined.
     */
    private static $defaultPK = ['id'];

    /**
     * Creates a Meta object for the given model class.
     *
     * @param  string $class Name of the model class.
     *
     * @return Meta
     */
    public function build($class)
    {
        // Verify input param is a Model instance
        $this->checkModel($class);

        // Fetch user-defined model meta-data
        $_meta = call_user_func([$class, 'getRawMeta']);
        if (!is_array($_meta)) {
            throw new InvalidModelException("Invalid $class::\$_meta. Not an array.");
        }

        // Construct the Meta
        $database = $this->getDatabase($class, $_meta);
        $table = $this->getTable($class, $_meta);
        $columns = $this->getColumns($class);
        $pk = $this->getPK($class, $_meta, $columns);
        $nonPK = $this->getNonPK($columns, $pk);

        return new Meta(
            $table,
            $database,
            $class,
            $columns,
            $pk,
            $nonPK
        );
    }

    /**
     * Returns class' public properties which correspond to column names.
     *
     * @return array
     */
    private function getColumns($class)
    {
        $columns = [];

        $rc = new ReflectionClass($class);
        $props = $rc->getProperties(ReflectionProperty::IS_PUBLIC);
        $columns = array_map(function (ReflectionProperty $prop) {
            return $prop->name;
        }, $props);

        if (empty($columns)) {
            throw new InvalidModelException("Model $class has no defined columns (public properties).");
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
    private function checkModel($class)
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

    /** Extracts the primary key column(s). */
    private function getPK($class, $meta, $columns)
    {
        // If the primary key is not defined
        if (!isset($meta['pk'])) {
            // If the model has an "id" field, use that as the PK
            $diff = array_diff(self::$defaultPK, $columns);
            if (empty($diff)) {
                return self::$defaultPK;
            } else {
                return null;
            }
        }

        if (is_string($meta['pk'])) {
            $pk = [$meta['pk']];
        } elseif (is_array($meta['pk'])) {
            $pk = $meta['pk'];
        } else {
            throw new InvalidModelException("Invalid primary key given in $class::\$_meta. Not a string or array.");
        }

        // Check all PK columns exist
        $missing = array_diff($pk, $columns);
        if (!empty($missing)) {
            $missing = implode(",", $missing);
            throw new InvalidModelException("Invalid $class::\$_meta. Specified primary key column(s) do not exist: $missing");
        }

        return $pk;
    }

    /** Extracts the non-primary key columns. */
    private function getNonPK($columns, $pk)
    {
        if ($pk === null) {
            return $columns;
        }

        return array_values(
            array_filter($columns, function ($column) use ($pk) {
                return !in_array($column, $pk);
            })
        );
    }

    /** Extracts the database name from user given model metadata. */
    private function getDatabase($class, $meta)
    {
        if (empty($meta['database'])) {
            throw new InvalidModelException("Invalid $class::\$_meta. Missing \"database\".");
        }

        return $meta['database'];
    }

    /** Extracts the table name from user given model metadata. */
    private function getTable($class, $meta)
    {
        if (empty($meta['table'])) {
            throw new InvalidModelException("Invalid $class::\$_meta. Missing \"table\".");
        }

        return $meta['table'];
    }
}
