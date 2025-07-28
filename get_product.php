<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/AdminAuth.php';
require_once '../includes/Product.php';

// Only allow AJAX requests
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit("Forbidden");
}

// Check if admin is logged in
if (!$adminAuth->isLoggedIn()) {
    http_response_code(401);
    exit("Unauthorized");
}

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    http_response_code(400);
    exit("Invalid product ID");
}

// Get product
$product = $product->getProductById($product_id);

if (!$product) {
    http_response_code(404);
    exit("Product not found");
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($product);
?>