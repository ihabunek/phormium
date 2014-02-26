<?php

namespace Phormium\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads and decodes YAML configuration files.
 */
class YamlConfigLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $data = $this->loadFile($resource);
        return Yaml::parse($data);
    }

    public function supports($resource, $type = null)
    {
        if (is_string($resource)) {
            $ext = pathinfo($resource, PATHINFO_EXTENSION);
            return $ext === 'yaml' || $ext === 'yml';
        }
    }
}
