<?php

namespace Phormium;

use PDO;

/**
 * Creates Phormium connections based on a database configuration.
 */
class ConnectionFactory
{
    private $databases;

    public function __construct(array $databases)
    {
        $this->databases = $databases;
    }

    public function newConnection($name, Database $database)
    {
        // Create a PDO connection
        $pdo = new PDO($dsn, $username, $password);

        // Don't allow ATTR_ERRORMODE to be changed by the configuration,
        // because Phormium depends on errors throwing exceptions.
        if (isset($attributes[PDO::ATTR_ERRMODE])
            && $attributes[PDO::ATTR_ERRMODE] !== PDO::ERRMODE_EXCEPTION) {
            trigger_error(
                "Phormium: Attribute PDO::ATTR_ERRMODE is set to something " .
                "other than PDO::ERRMODE_EXCEPTION for database \"$name\"." .
                " This is not allowed because Phormium depends on this ".
                "setting. Skipping attribute definition.",
                E_USER_WARNING
            );
        }

        $attributes[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        // Apply the attributes
        foreach ($attributes as $key => $value) {
            if (!$pdo->setAttribute($key, $value)) {
                throw new \Exception("Failed setting PDO attribute \"$key\" to \"$value\" on database \"$name\".");
            }
        }

        return new Connection($name, $pdo, $database);
    }
}
