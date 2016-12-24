<?php

namespace Phormium\Config;

use Phormium\Exception\OrmException;
use Phormium\Exception\ConfigurationException;
use Phormium\Helper\Json;

/**
 * Loads and decodes JSON configuration files.
 */
class JsonLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $json = $this->loadFile($resource);

        try {
            return Json::parse($json, true);
        } catch (OrmException $ex) {
            throw new ConfigurationException("Failed parsing JSON configuration file.", 0, $ex);
        }
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
            pathinfo($resource, PATHINFO_EXTENSION) === 'json';
    }
}
