<?php

/**
 * Database Class
 * 
 * Handles all database connections and queries
 * Uses MySQLi for secure database operations
 */

class Database
{
    private $conn;
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $port;
    private $charset;

    public function __construct($host, $user, $pass, $dbname, $port = 3306, $charset = 'utf8mb4')
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbname = $dbname;
        $this->port = $port;
        $this->charset = $charset;
        $this->connect();
    }

    /**
     * Connect to the database
     */
    private function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        // Set charset
        if (!$this->conn->set_charset($this->charset)) {
            throw new Exception("Error loading character set: " . $this->conn->error);
        }
    }

    /**
     * Get database connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Execute a prepared statement
     */
    public function executeQuery($query, $params = [], $types = '')
    {
        try {
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            if (!empty($params)) {
                $bindParams = [$types];
                foreach ($params as &$param) {
                    $bindParams[] = &$param;
                }
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
            }

            if ($stmt->execute()) {
                return $stmt;
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Query Error: " . $e->getMessage());
        }
    }

    /**
     * Fetch all results
     */
    public function fetchAll($query, $params = [], $types = '')
    {
        $stmt = $this->executeQuery($query, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch single row
     */
    public function fetchOne($query, $params = [], $types = '')
    {
        $stmt = $this->executeQuery($query, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Insert/Update/Delete
     */
    public function execute($query, $params = [], $types = '')
    {
        $stmt = $this->executeQuery($query, $params, $types);
        return $stmt->affected_rows;
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId()
    {
        return $this->conn->insert_id;
    }

    /**
     * Close connection
     */
    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Check whether the database already has tables
     */
    public function hasTables()
    {
        $result = $this->conn->query("SHOW TABLES");
        return $result && $result->num_rows > 0;
    }

    /**
     * Import the SQL dump into the current database connection
     */
    public function importSqlFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found: " . $filePath);
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new Exception("Unable to read SQL file: " . $filePath);
        }

        if (!$this->conn->multi_query($sql)) {
            throw new Exception("SQL import failed: " . $this->conn->error);
        }

        do {
            if ($result = $this->conn->store_result()) {
                $result->free();
            }
        } while ($this->conn->more_results() && $this->conn->next_result());
    }

    /**
     * Initialize schema from SQL dump if no tables exist
     */
    public function initializeSchema($filePath)
    {
        if (!$this->hasTables()) {
            $this->importSqlFile($filePath);
        }
    }

    /**
     * Escape string for security
     */
    public function escape($string)
    {
        return $this->conn->real_escape_string($string);
    }

    /**
     * Destructor - close connection
     */
    public function __destruct()
    {
        $this->close();
    }
}
