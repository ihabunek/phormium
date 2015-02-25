<?php

namespace Phormium;

use Phormium\Config\ArrayLoader;
use Phormium\Config\JsonLoader;
use Phormium\Config\YamlLoader;
use Phormium\Config\PostProcessor;
use Phormium\Config\Configuration;

use Pimple\Container;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class Orm extends Container
{
    public function __construct()
    {
        // One or more configurations can be given as constructor arguments
        $this['config.input'] = func_get_args();

        $this['config.loader'] = function() {
            return new DelegatingLoader(new LoaderResolver([
                new ArrayLoader(),
                new JsonLoader(),
                new YamlLoader(),
            ]));
        };

        $this['config.processor'] = function () {
            return new Processor();
        };

        $this['config.tree'] = function () {
            $configuration = new Configuration();
            $builder = $configuration->getConfigTreeBuilder();
            return $builder->buildTree();
        };

        $this['config.postprocessor'] = function () {
            return new PostProcessor();
        };

        $this['config'] = function() {
            // Load all given configurations
            $configs = [];
            foreach ($this['config.input'] as $raw) {
                $configs[] = $this['config.loader']->load($raw);
            }

            // Combine them and validate
            $config = $this['config.processor']->process(
                $this['config.tree'],
                $configs
            );

            // Additional postprocessing to handle
            return $this['config.postprocessor']->processConfig($config);
        };

        // Event emitter
        $this['emitter'] = function () {
            return new Evenement\EventEmitter();
        };
    }
}
