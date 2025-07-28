<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/AdminAuth.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!$adminAuth->isLoggedIn()) {
    redirect('login.php');
}

// Fetch all orders with user info
$sql = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll();

function getOrderItems($db, $order_id) {
    $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | <?php echo SITE_NAME; ?></title>
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
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <div class="card">
            <div class="card-header">
                <h1>All Orders</h1>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p>No orders found.</p>
                <?php else: ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Items</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td>$<?php echo number_format($order['total'], 2); ?></td>
                                <td><?php echo $order['created_at']; ?></td>
                                <td>
                                    <table style="width:100%; background: #f8fafc; border-radius: 6px;">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (getOrderItems($db, $order['id']) as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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