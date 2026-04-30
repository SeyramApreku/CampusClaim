<?php
// classes/UserFactory.php
// Implements the Factory Method Design Pattern

require_once 'User.php';

class UserFactory {
    public static function createUser($role, $id, $name, $email, $password_hash, $phone) {
        if ($role === 'admin') {
            return new AdminUser($id, $name, $email, $password_hash, $phone);
        }
        
        // Default to student
        return new StudentUser($id, $name, $email, $password_hash, $phone);
    }
}
?>
