<?php
// classes/Database.php
// Implements the Singleton Design Pattern

class Database
{
    private static $instance = null;
    private $pdo;

    // Private constructor prevents direct instantiation
    private function __construct()
    {
        $host = getenv('MYSQLHOST');
        $port = getenv('MYSQLPORT');
        $db_name = getenv('MYSQLDATABASE');
        $username = getenv('MYSQLUSER');
        $password = getenv('MYSQLPASSWORD');

        // Validate that all required environment variables are set
        if (!$host || !$port || !$db_name || !$username || !$password) {
            die("Database configuration error: Missing required environment variables");
        }

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8",
                $username,
                $password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Method to get the single instance of the Database
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get the PDO object
    public function getConnection()
    {
        return $this->pdo;
    }
}
?>