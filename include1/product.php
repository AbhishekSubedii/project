<?php
require_once 'db.php';

class Product {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all products
    public function getAllProducts() {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get product by ID
    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Create product
    public function createProduct($name, $description, $price, $image_url) {
        $sql = "INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description, $price, $image_url]);
    }
    
    // Update product
    public function updateProduct($id, $name, $description, $price, $image_url) {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description, $price, $image_url, $id]);
    }
    
    // Delete product
    public function deleteProduct($id) {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Search products
    public function searchProducts($query) {
        $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}

// Initialize product
$product = new Product($db);
?>