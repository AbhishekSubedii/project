<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mini_shopping_cart');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change to your database password

// Site configuration
define('SITE_NAME', 'Mini Shopping Cart');
define('SITE_URL', 'http://localhost/mini-shopping-cart'); // Change to your project URL
?>