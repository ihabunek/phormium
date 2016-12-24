<?php

namespace Phormium\Config;

use Phormium\Exception\ConfigurationException;
use Symfony\Component\Config\Loader\Loader;

/**
 * Common functions for config file loaders.
 */
abstract class FileLoader extends Loader
{
    protected function loadFile($path)
    {
        if (!file_exists($path)) {
            throw new ConfigurationException("Config file not found at \"$path\".");
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new ConfigurationException("Error loading config file from \"$path\".");
        }

        return $data;
    }
}
