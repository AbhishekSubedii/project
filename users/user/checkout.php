<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/Cart.php';

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    redirect('../user/login.php');
}

// Get cart items
$cart_items = $cart->getCartItems();
$cart_total = $cart->getCartTotal();

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save order to database
    $user_id = $_SESSION['user_id'];
    $total = $cart_total;
    $created_at = date('Y-m-d H:i:s');
    // Create orders table if not exists
    $db->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total DECIMAL(10,2),
        created_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    // Create order_items table if not exists
    $db->query("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        product_name VARCHAR(255),
        price DECIMAL(10,2),
        quantity INT,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");
    // Insert order
    $stmt = $db->prepare("INSERT INTO orders (user_id, total, created_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $total, $created_at]);
    $order_id = $db->lastInsertId();
    // Insert order items
    foreach ($cart_items as $item) {
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['name'], $item['price'], $item['quantity']]);
    }
    $cart->clearCart();
    $_SESSION['success_message'] = "Thank you for your purchase! Your order has been placed successfully.";
    redirect('../index.php');
}

// Redirect if cart is empty
if (empty($cart_items)) {
    $_SESSION['error_message'] = "Your cart is empty. Please add some products before checkout.";
    redirect('cart.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo" style="display: flex; align-items: center; gap: 0.7rem;">
                    <img src="../assets/images/logo.png" alt="Logo" style="height: 38px; width: 38px; object-fit: contain; border-radius: 8px; box-shadow: 0 2px 8px rgba(44,62,80,0.10); background: #fff;">
                    <?php echo SITE_NAME; ?>
                </div>
                <ul class="nav-links">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="cart.php">Cart (<?php echo $cart->getCartCount(); ?>)</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <div class="card-header">
                <h1>Checkout</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h2>Order Summary</h2>
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                    <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h2>Payment Information</h2>
                        <form action="checkout.php" method="POST">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/YY" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="name_on_card">Name on Card</label>
                                <input type="text" id="name_on_card" name="name_on_card" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn">Place Order</button>
                            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>