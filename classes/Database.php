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
        $host = getenv('MYSQLHOST') ?: 'railway';
        $port = getenv('MYSQLPORT') ?: '3306';
        $db_name = getenv('MYSQLDATABASE') ?: 'railway';
        $username = getenv('MYSQLUSER') ?: 'SeyramApreku';
        $password = getenv('MYSQLPASSWORD') ?: 'sGFx5dHTpVs0iPEg';

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