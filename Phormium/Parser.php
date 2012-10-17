<?php

namespace Phormium;

/**
 * Parses {@link Model} classes and constructs corresponding {@link Meta}
 * objects.
 */
class Parser
{
    public static function parse($class)
    {
        $meta = new Meta();
        $meta->class = $class;

        $rc = new \ReflectionClass($class);
        $classDoc = $rc->getDocComment();

        $meta->connection = self::getAnnotation($class, $classDoc, 'connection');
        $meta->table = self::getAnnotation($class, $classDoc, 'table');
        $meta->columns = array();

        $props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propDoc = $prop->getDocComment();
            $name = $prop->name;
            $propID = "$class::\$$name";

            $meta->columns[$name] = array(
                'name' => $name
            );

            $type = self::getAnnotation($propID, $propDoc, 'type', false);
            if (isset($type)) {
                $meta->columns[$name]['type'] = $type;
            }

            if (self::hasAnnotation($propDoc, 'pk')) {
                if (isset($meta->pk)) {
                    throw new \Exception("Multiple columns marked as @pk. Composite primary keys are not supported.");
                }
                $meta->pk = $name;
            }
        }

        return $meta;
    }

    private static function getAnnotation($id, $doc, $name, $required = true)
    {
        $result = preg_match("/@$name ([a-z0-9_-]+)/", $doc, $matches);
        if ($result == 1) {
            return $matches[1];
        }

        if ($required) {
            throw new \Exception("Required annotation @$name not defined in [$id].");
        }
        return null;
    }

    private static function hasAnnotation($doc, $name)
    {
        return preg_match('/@' . $name . '[^\\w]/', $doc, $matches) > 0;
    }
}
