<?php
// classes/UserDAO.php
// Implements the Data Access Object (DAO) Design Pattern

require_once 'Database.php';
require_once 'UserFactory.php';

class UserDAO {
    private $db;

    public function __construct() {
        // Use the Singleton Database connection
        $this->db = Database::getInstance()->getConnection();
    }

    // Find a user by email
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if ($row) {
            return UserFactory::createUser(
                $row['role'],
                $row['user_id'],
                $row['name'],
                $row['email'],
                $row['password_hash'],
                $row['phone']
            );
        }
        return null;
    }

    // Check if an email exists
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    // Create a new student user
    public function createStudent($name, $email, $password, $phone) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password_hash, phone, role)
             VALUES (:name, :email, :hash, :phone, 'student')"
        );
        
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':hash' => $hash,
            ':phone' => $phone
        ]);
    }
}
?>
