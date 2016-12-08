<?php

namespace Phormium;

use Evenement\EventEmitter;

use Phormium\Config\ArrayLoader;
use Phormium\Config\Configuration;
use Phormium\Config\JsonLoader;
use Phormium\Config\PostProcessor;
use Phormium\Config\YamlLoader;
use Phormium\Database\Database;
use Phormium\Database\Factory;
use Phormium\QueryBuilder\QueryBuilderFactory;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class Container extends \Pimple\Container
{
    public function __construct()
    {
        // One or more configurations can be given as constructor arguments
        $this['config.input'] = func_get_args();

        $this['config.loader'] = function () {
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

        $this['config'] = function () {
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
            return new EventEmitter();
        };

        // Parser for model metadata
        $this['meta.builder'] = function () {
            return new MetaBuilder();
        };

        // Model metadata cache
        $this['meta.cache'] = function () {
            return new \ArrayObject();
        };

        // Database connection factory
        $this['database.factory'] = function () {
            return new Factory(
                $this['config']['databases'],
                $this['emitter']
            );
        };

        // Database connection manager
        $this['database'] = function () {
            return new Database(
                $this['database.factory'],
                $this['emitter']
            );
        };

        // Cache for query builders
        $this['query_builder.cache'] = function () {
            return new \ArrayObject();
        };

        // Query builder factory
        $this['query_builder.factory'] = function () {
            return new QueryBuilderFactory($this['query_builder.cache']);
        };

        // Cache for Query objects
        $this['query.cache'] = function () {
            return new \ArrayObject();
        };
    }
}
