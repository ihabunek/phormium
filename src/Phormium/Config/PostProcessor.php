<?php

namespace Phormium\Config;

use Phormium\Exception\ConfigurationException;

class PostProcessor
{
    /**
     * Process and validate a configuration array.
     */
    public function processConfig(array $config)
    {
        foreach ($config['databases'] as $name => $dbConfig) {
            $config['databases'][$name] = $this->processDbConfig($name, $dbConfig);
        }

        return $config;
    }

    public function processDbConfig($name, $config)
    {
        // Ensure username and password keys exist
        if (!array_key_exists("username", $config)) {
            $config['username'] = null;
        }

        if (!array_key_exists("password", $config)) {
            $config['password'] = null;
        }

        // Add the driver name to database config, needed to tailor db queries
        $config['driver'] = $this->parseDriver($config['dsn']);

        // Convert string attributes to actual values
        $config['attributes'] = $this->processAttributes($name, $config['attributes']);

        return $config;
    }

    /** Parses the DSN and extracts the driver name. */
    public function parseDriver($dsn)
    {
        $count = preg_match('/^([a-z]+):/', $dsn, $matches);

        if ($count !== 1) {
            throw new ConfigurationException("Invalid DSN: \"$dsn\". The DSN should start with '<driver>:'");
        }

        return $matches[1];
    }

    public function processAttributes($dbName, $attributes)
    {
        $processed = [];

        foreach ($attributes as $name => $value) {
            try {
                $procName = $this->processConstant($name, false);
            } catch (\Exception $ex) {
                throw new ConfigurationException("Invalid attribute \"$name\" specified in configuration for database \"$dbName\".");
            }

            try {
                $procValue = $this->processConstant($value, true);
            } catch (\Exception $ex) {
                throw new ConfigurationException("Invalid value given for attribute \"$name\", in configuration for database \"$dbName\".");
            }

            $processed[$procName] = $procValue;
        }

        return $processed;
    }

    public function processConstant($value, $allowScalar = false)
    {
        // If the value is an integer, assume it's a PDO::* constant value
        // and leave it as-is
        if (is_integer($value)) {
            return $value;
        }

        // If it's a string which starts with "PDO::", try to find the
        // corresponding PDO constant
        if (is_string($value) && substr($value, 0, 5) === 'PDO::' && defined($value)) {
            return constant($value);
        }

        if ($allowScalar && is_scalar($value)) {
            return $value;
        }

        throw new ConfigurationException("Invalid constant value");
    }
}
