<?php
session_start();

require_once 'db.php';
require_once 'functions.php';

class Auth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Register new user
    public function register($username, $email, $password) {
        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            return "All fields are required";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format";
        }
        
        if (strlen($password) < 6) {
            return "Password must be at least 6 characters";
        }
        
        // Check if user exists
        $user = $this->getUserByUsernameOrEmail($username, $email);
        if ($user) {
            return "Username or email already exists";
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert user
        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email, $password_hash]);
        
        return true;
    }
    
    // Login user
    public function login($username, $password) {
        $user = $this->getUserByUsernameOrEmail($username, $username);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return "Invalid username or password";
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        
        return true;
    }
    
    // Logout user
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
    }
    
    // Get user by username or email
    private function getUserByUsernameOrEmail($username, $email) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }
    
    // Get current user
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) return null;
        
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
}

// Initialize auth
$auth = new Auth($db);
?>