<?php

namespace Phormium\Database;

use Evenement\EventEmitter;
use PDO;
use Phormium\Exception\DatabaseException;

/**
 * Database Connection factory.
 */
class Factory
{
    /**
     * Event emitter.
     *
     * @var Evenement\EventEmitter
     */
    private $emitter;

    /**
     * Database configuration array.
     *
     * @var array
     */
    protected $databases;

    public function __construct($databases, EventEmitter $emitter)
    {
        $this->databases = $databases;
        $this->emitter = $emitter;
    }

    /** Creates a new connection. */
    public function newConnection($name)
    {
        if (!isset($this->databases[$name])) {
            throw new DatabaseException("Database \"$name\" is not configured.");
        }

        // Extract settings
        $dsn = $this->databases[$name]['dsn'];
        $username = $this->databases[$name]['username'];
        $password = $this->databases[$name]['password'];
        $attributes = $this->databases[$name]['attributes'];

        // Create a PDO connection
        $pdo = new PDO($dsn, $username, $password);

        // Don't allow ATTR_ERRORMODE to be changed by the configuration,
        // because Phormium depends on errors throwing exceptions.
        if (isset($attributes[PDO::ATTR_ERRMODE])
            && $attributes[PDO::ATTR_ERRMODE] !== PDO::ERRMODE_EXCEPTION) {
            // Warn the user
            $msg = "Phormium: Attribute PDO::ATTR_ERRMODE is set to something ".
                "other than PDO::ERRMODE_EXCEPTION for database \"$name\".".
                " This is not allowed because Phormium depends on this ".
                "setting. Skipping attribute definition.";

            trigger_error($msg, E_USER_WARNING);
        }

        $attributes[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        // Apply the attributes
        foreach ($attributes as $key => $value) {
            if (!$pdo->setAttribute($key, $value)) {
                throw new DatabaseException("Failed setting PDO attribute \"$key\" to \"$value\" on database \"$name\".");
            }
        }

        return new Connection($pdo, $this->emitter);
    }
}
