<?php

namespace Phormium;

use PDO;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class Config
{
    public static function load($config)
    {
        throw new \Exception("Config::load() is deprecated, please use Phormium::configure().");
    }

    public static function reset()
    {
        throw new \Exception("Config::reset() is deprecated, please use Phormium::reset().");
    }
}
