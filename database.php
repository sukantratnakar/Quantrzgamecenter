<?php

class Database {
    private $servername;
    private $username;
    private $password;
    private $database;
    private $port;
    private $conn;

    /**
     * Constructor for the default database
     */
    public function __construct() {
        // Use environment variables with fallback to old values for backward compatibility
        $this->servername = getenv('DB_HOST') ?: "localhost";
        $this->username = getenv('DB_USER') ?: "u755186149_root";
        $this->password = getenv('DB_PASSWORD') ?: "AM/g=CS7+(}Py~Q]_nmNx[";
        $this->database = getenv('DB_NAME') ?: "u755186149_test";
        $this->port = getenv('DB_PORT') ?: "3306";
    }

    /**
     * Constructor for connecting to a different database
     */
    public function __construct_custom($servername, $username, $password, $database, $port) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    /**
     * Connect to the database and return the connection object
     */
    public function connect() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database, $this->port);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        return $this->conn;
    }
}

?>