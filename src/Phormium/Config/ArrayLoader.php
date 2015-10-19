<?php

namespace Phormium\Config;

/**
 * Loads array configurations (passthrough).
 */
class ArrayLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        return $resource;
    }

    public function supports($resource, $type = null)
    {
        return is_array($resource);
    }
}
