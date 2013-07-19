<?php

namespace Phormium;

/**
 * Metadata parser for Model classes.
 */
class Parser
{
    private static $cache = array();

    public static function getMeta($class, $meta)
    {
        if (!isset(self::$cache[$class])) {
            self::$cache[$class] = self::parseMeta($class, $meta);
        }
        return self::$cache[$class];
    }

    protected static function parseMeta($class, $_meta)
    {
        if (!is_array($_meta)) {
            throw new \Exception("Invalid $class::\$_meta. Not an array.");
        }

        if (empty($_meta['database'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing 'database'.");
        }

        if (empty($_meta['table'])) {
            throw new \Exception("Invalid $class::\$_meta. Missing 'table'.");
        }

        $meta = new Meta();
        $meta->class = $class;
        $meta->database = $_meta['database'];
        $meta->table = $_meta['table'];
        $meta->pk = self::parsePK($class, $_meta);
        $meta->columns = array();

        // Fetch class' public properties which correspond to column names
        $rc = new \ReflectionClass($class);
        $props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $meta->columns[] = $prop->name;
        }

        // Primary key is optional
        if (isset($meta->pk)) {

            // Verify primary key columns exist
            foreach ($meta->pk as $column) {
                if (!in_array($column, $meta->columns)) {
                    throw new \Exception(
                        "Invalid $class::\$_meta. Given primary key column [{$column}] does not exist."
                    );
                }
            }

            // Compile non-pk columns
            $meta->nonPK = array();
            foreach ($meta->columns as $column) {
                if (!in_array($column, $meta->pk)) {
                    $meta->nonPK[] = $column;
                }
            }
        } else {
            $meta->nonPK = $meta->columns;
        }

        return $meta;
    }

    private static function parsePK($class, $meta)
    {
        if (!isset($meta['pk'])) {
            return null;
        }

        if (is_string($meta['pk'])) {
            return array($meta['pk']);
        }

        if (is_array($meta['pk'])) {
            return $meta['pk'];
        }

        throw new \Exception("Invalid $class::\$_meta['pk']. Not a string or array.");
    }
}
