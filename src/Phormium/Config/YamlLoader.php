<?php

namespace Phormium\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads and decodes YAML configuration files.
 */
class YamlLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $data = $this->loadFile($resource);

        return Yaml::parse($data);
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
            in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yaml', 'yml']);
    }
}
