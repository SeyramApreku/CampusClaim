<?php
// classes/User.php

abstract class User {
    protected $id;
    protected $name;
    protected $email;
    protected $password_hash;
    protected $phone;
    protected $role;

    public function __construct($id, $name, $email, $password_hash, $phone) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->phone = $phone;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPasswordHash() { return $this->password_hash; }
    public function getPhone() { return $this->phone; }
    public function getRole() { return $this->role; }

    // Each user type might have a different dashboard URL
    abstract public function getDashboardUrl();
}

class StudentUser extends User {
    public function __construct($id, $name, $email, $password_hash, $phone) {
        parent::__construct($id, $name, $email, $password_hash, $phone);
        $this->role = 'student';
    }

    public function getDashboardUrl() {
        return '../items/browse.php';
    }
}

class AdminUser extends User {
    public function __construct($id, $name, $email, $password_hash, $phone) {
        parent::__construct($id, $name, $email, $password_hash, $phone);
        $this->role = 'admin';
    }

    public function getDashboardUrl() {
        return '../admin/dashboard.php';
    }
}
?>
