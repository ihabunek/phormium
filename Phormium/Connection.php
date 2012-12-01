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

    /** Holds inserted ID of the last executed query. */
    private $lastInsertID;

    /** Holds row count of the last executed query. */
    private $lastRowCount;

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

    /**
     * Prepares and executes a query and fetches all returned data.
     * @return array An array of either objects, arrays or strings, depending
     *      on the fetch type.
     */
    public function execute($query, $args = array(), $fetchType = DB::FETCH_OBJECT, $class = null)
    {
        $pdo = $this->getPDO();

        // echo "\nPreparing query: $query\n";
        $stmt = $pdo->prepare($query);

        // echo "Executing query with args: " . implode(', ', $args) . "\n";
        $stmt->execute($args);

        // Fetch into objects
        if ($fetchType === DB::FETCH_OBJECT) {
            $data = $stmt->fetchAll(PDO::FETCH_CLASS, $class);
        }

        // Fetch into associative arrays
        elseif ($fetchType === DB::FETCH_ARRAY) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch into objects and perform json_encode on each one
        elseif ($fetchType === DB::FETCH_JSON) {
            $data = array();
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
            while ($object = $stmt->fetch()) {
                $data[] = json_encode($object);
            }
        } else {
            throw new \Excepion("Unknown fetch type [$fetchType].");
        }

        $this->lastInsertID = $pdo->lastInsertId();
        $this->lastRowCount = $stmt->rowCount();

        $stmt->closeCursor();
        return $data;
    }

    /**
     * Prepares and executes the query, but does not fetch.
     */
    public function executeNoFetch($query, $args = array())
    {
        $pdo = $this->getPDO();

        // echo "\nPreparing query: $query\n";
        $stmt = $pdo->prepare($query);

        // echo "Executing query with args: " . implode(', ', $args) . "\n";
        $stmt->execute($args);

        $this->lastInsertID = $pdo->lastInsertId();
        $this->lastRowCount = $stmt->rowCount();
        $stmt->closeCursor();

        // echo "Finished execution, rowCount: {$this->lastRowCount}, lastInsertID: {$this->lastInsertID}\n";
    }

    public function getLastInsertID()
    {
        return $this->lastInsertID;
    }

    public function getLastRowCount()
    {
        return $this->lastRowCount;
    }
}
