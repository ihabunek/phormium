<?php

namespace Phormium;

use \PDO;

/**
 * A database connection object.
 *
 * Encapsulates a PDO connection and provides methods for executing queries
 * and fetching data.
 */
class Connection
{
    /**
     * The Data Source Name.
     * @see http://www.php.net/manual/en/pdo.construct.php
     */
    private $dsn;

    /** Username used to connect. */
    private $username;

    /** Password used to connect. */
    private $password;

    /**
     * The underlying PDO connection.
     * @var PDO
     */
    private $pdo;

    public function __construct($config)
    {
        if (empty($config['dsn'])) {
            throw new \Exception("Invalid configuration for database [$name]: DSN not specified.");
        }

        $this->dsn = $config['dsn'];
        $this->username = isset($config['username']) ? $config['username'] : null;
        $this->password = isset($config['password']) ? $config['password'] : null;
    }

    /**
     * Returns the underlying PDO connection. Creates it if it doesn't yet exist.
     * @return PDO
     */
    public function getPDO()
    {
        if (!isset($this->pdo)) {
            // Establish a connection
            $this->pdo = new PDO($this->dsn, $this->username, $this->password);

            // Force lower case column names
            $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

            // Force an exception to be thrown on error
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this->pdo;
    }
}
