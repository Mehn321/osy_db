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
    private $socket;
    private $sslMode;
    private $sslCa;
    private $sslCert;
    private $sslKey;
    private $sslVerifyServerCert;

    public function __construct($host = null, $user = null, $pass = null, $dbname = null, $port = null, $charset = 'utf8mb4', $socket = null, $sslMode = null, $sslCa = null, $sslCert = null, $sslKey = null, $sslVerifyServerCert = null)
    {
        $this->host = $host ?? DB_HOST;
        $this->user = $user ?? DB_USER;
        $this->pass = $pass ?? DB_PASS;
        $this->dbname = $dbname ?? DB_NAME;
        $this->port = $port ?? DB_PORT;
        $this->charset = $charset;
        $this->socket = $socket ?? DB_SOCKET;
        $this->sslMode = $sslMode ?? DB_SSL_MODE;
        $this->sslCa = $sslCa ?? DB_SSL_CA;
        $this->sslCert = $sslCert ?? DB_SSL_CERT;
        $this->sslKey = $sslKey ?? DB_SSL_KEY;
        $this->sslVerifyServerCert = $sslVerifyServerCert ?? DB_SSL_VERIFY_SERVER_CERT;
        $this->connect();
    }

    /**
     * Connect to the database
     */
    private function connect()
    {
        $mysqli = mysqli_init();

        if (!$mysqli) {
            throw new Exception('Failed to initialize MySQLi.');
        }

        $attempts = [];
        $useSsl = !empty($this->sslMode) && $this->sslMode !== 'disable';
        $sslError = null;

        if ($useSsl) {
            $caPath = $this->sslCa;
            if ($caPath && !preg_match('#^(/|[a-zA-Z]:)#', $caPath)) {
                $caPath = dirname(__DIR__) . '/' . $caPath;
            }
            $mysqli->ssl_set($this->sslKey ?: null, $this->sslCert ?: null, $caPath ?: null, null, null);

            if ($this->sslVerifyServerCert === false && defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
                $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
            }
        }

        $attempts[] = ['ssl' => $useSsl, 'flags' => $useSsl ? MYSQLI_CLIENT_SSL : 0];

        if ($useSsl) {
            $attempts[] = ['ssl' => false, 'flags' => 0];
        }

        $lastError = null;
        foreach ($attempts as $attempt) {
            $flags = $attempt['flags'];
            $connected = $this->socket !== ''
                ? $mysqli->real_connect($this->host, $this->user, $this->pass, $this->dbname, $this->port, $this->socket, $flags)
                : $mysqli->real_connect($this->host, $this->user, $this->pass, $this->dbname, $this->port, null, $flags);

            if ($connected) {
                $this->conn = $mysqli;
                break;
            }

            $lastError = $mysqli->connect_error ?: $mysqli->error;
            $sslError = $lastError;
            $mysqli = mysqli_init();
            if ($attempt['ssl']) {
                $mysqli->ssl_set($this->sslKey ?: null, $this->sslCert ?: null, null, null, null);
            }
        }

        if (!isset($this->conn)) {
            $sslSummary = $useSsl ? 'enabled' : 'disabled';
            $detail = $lastError ?: 'No connection error returned';
            if ($useSsl && stripos($detail, 'ssl') !== false) {
                $detail = 'SSL/TLS negotiation failed. ' . $detail;
            }
            throw new Exception(
                "Connection failed to MySQL host '{$this->host}' on port {$this->port} for database '{$this->dbname}'. " .
                    "SSL mode: {$sslSummary}. Last error: " . $detail
            );
        }

        if (!$this->conn->set_charset($this->charset)) {
            throw new Exception("Error loading character set: " . $this->conn->error);
        }

        // Aiven for MySQL can enforce sql_require_primary_key for new tables.
        // Disable it for this session so the bundled schema import can succeed.
        $this->conn->query("SET SESSION sql_require_primary_key = OFF");
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
                if (empty($types)) {
                    $types = '';
                    foreach ($params as $param) {
                        if (is_int($param)) {
                            $types .= 'i';
                        } elseif (is_float($param) || is_double($param)) {
                            $types .= 'd';
                        } else {
                            $types .= 's';
                        }
                    }
                }
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
