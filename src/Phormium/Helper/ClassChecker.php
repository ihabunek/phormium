<?php

namespace Phormium\Helpers;

class ClassChecker
{
    /**
     * Asserts that the given class exists and
     *
     * @param  string $className
     */
    public static function isModel($className)
    {
        if (!class_exists($class)) {
            return false;
        }

        if (!is_subclass_of($class, "Phormium\\Model")) {
            return false;
        }

        return true;
    }

    public static function hasProperties($className, $properties)
    {
        foreach ($properties as $property) {
            if (!property_exists($class, $property)) {
                return false;
            }
        }

        return true;
    }
}