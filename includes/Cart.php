<?php
require_once 'db.php';
require_once 'Product.php';

class Cart {
    private $db;
    private $product;
    
    public function __construct($db) {
        $this->db = $db;
        $this->product = new Product($db);
        
        // Initialize cart in session if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    // Add product to cart
    public function addToCart($product_id, $quantity = 1) {
        $product = $this->product->getProductById($product_id);
        
        if (!$product) {
            return false;
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'quantity' => $quantity
            ];
        }
        
        return true;
    }
    
    // Remove product from cart
    public function removeFromCart($product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            return true;
        }
        return false;
    }
    
    // Update product quantity in cart
    public function updateQuantity($product_id, $quantity) {
        if (isset($_SESSION['cart'][$product_id]) && $quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            return true;
        }
        return false;
    }
    
    // Get all items in cart
    public function getCartItems() {
        return $_SESSION['cart'];
    }
    
    // Get cart total
    public function getCartTotal() {
        $total = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    // Get cart count
    public function getCartCount() {
        $count = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    // Clear cart
    public function clearCart() {
        $_SESSION['cart'] = [];
    }
}

// Initialize cart
$cart = new Cart($db);
?>