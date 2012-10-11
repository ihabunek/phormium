<?php

namespace Phormium;

/**
 * Parses {@link Entity} classes and constructs corresponding {@link Model} 
 * objects.
 */
class Parser
{
    public static function parse($class)
    {
        $model = new Model();
        $model->class = $class;
        
        $rc = new \ReflectionClass($class);
        $classDoc = $rc->getDocComment();

        $model->connection = self::getAnnotation($class, $classDoc, 'connection');
        $model->table = self::getAnnotation($class, $classDoc, 'table');
        $model->columns = array();

        $props = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach($props as $prop)
        {
            $propDoc = $prop->getDocComment();
            $name = $prop->name;
            $propID = "$class::\$$name";

            $model->columns[$name] = array(
                'name' => $name
            );
            
            $type = self::getAnnotation($propID, $propDoc, 'type', false);
            if (isset($type)) {
                $model->columns[$name]['type'] = $type;
            }
            
            if (self::hasAnnotation($propDoc, 'pk')) {
                if (isset($model->pk)) {
                    throw new \Exception("Multiple columns marked as @pk. Composite primary keys are not supported.");
                }
                $model->pk = $name;
            }
        }
        
        return $model;
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