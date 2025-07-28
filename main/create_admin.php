<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once 'includes/db.php';

// Only run this once to create an admin user
$username = 'admin1'; // Updated username
$password = 'admin1234'; // Updated password

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert admin
$sql = "INSERT INTO admins (username, password_hash) VALUES (?, ?)";
$stmt = $db->prepare($sql);
$stmt->execute([$username, $password_hash]);

echo "Admin user created successfully!";
?>