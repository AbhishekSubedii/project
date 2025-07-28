<?php
require_once 'db.php';

class AdminAuth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Login admin
    public function login($username, $password) {
        $sql = "SELECT * FROM admins WHERE username = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return "Invalid username or password";
        }
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_logged_in'] = true;
        
        return true;
    }
    
    // Logout admin
    public function logout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_logged_in']);
        session_destroy();
    }
    
    // Check if admin is logged in
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
    }
    
    // Get current admin
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) return null;
        
        $sql = "SELECT * FROM admins WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    }
}

// Initialize admin auth
$adminAuth = new AdminAuth($db);
?>