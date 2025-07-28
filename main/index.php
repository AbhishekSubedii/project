<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo" style="display: flex; align-items: center; gap: 0.7rem;">
                    <img src="assets/images/logo.png" alt="Logo" style="height: 38px; width: 38px; object-fit: contain; border-radius: 8px; box-shadow: 0 2px 8px rgba(44,62,80,0.10); background: #fff;">
                    <?php echo SITE_NAME; ?>
                </div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <li><a href="user/products.php">Products</a></li>
                        <li><a href="user/cart.php">Cart</a></li>
                        <li><a href="user/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="user/login.php">Login</a></li>
                        <li><a href="user/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 2rem;">
            <div style="background: rgba(241, 227, 211, 0.25); color: #6B705C; border-radius: 16px; padding: 2.2rem 2.5rem; box-shadow: 0 4px 24px rgba(107,112,92,0.10); font-size: 1.5rem; font-weight: 600; text-align: center; max-width: 600px;">
                Welcome<?php if ($auth->isLoggedIn()) echo ', ' . htmlspecialchars($_SESSION['username']); ?>!<br>
                <span style="font-size: 1.1rem; font-weight: 400;">We’re glad to have you here. Enjoy shopping with us!</span>
            </div>
        </div>
        <?php if ($auth->isLoggedIn()): ?>
            <?php
            // Fetch user's previous orders
            $user_id = $_SESSION['user_id'];
            $orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $orders->execute([$user_id]);
            $orders = $orders->fetchAll();
            function getOrderItems($db, $order_id) {
                $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmt->execute([$order_id]);
                return $stmt->fetchAll();
            }
            ?>
            <section class="card" style="max-width: 1000px; margin: 0 auto 2rem auto;">
                <div class="card-header">
                    <h2>Your Previous Orders</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p>You have not placed any orders yet.</p>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                            <?php foreach ($orders as $order): ?>
                                <div style="background: #F1E3D3; border-radius: 10px; box-shadow: 0 2px 12px rgba(107,112,92,0.08); padding: 1.2rem 1.2rem 1rem 1.2rem; margin-bottom: 0; border: 1px solid #DDBEA9;">
                                    <strong>Order Date:</strong> <?php echo $order['created_at']; ?><br>
                                    <strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?><br>
                                    <strong>Items:</strong>
                                    <ul style="margin: 0.5rem 0 0 1.2rem;">
                                        <?php foreach (getOrderItems($db, $order['id']) as $item): ?>
                                            <li><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['quantity']; ?>) - $<?php echo number_format($item['price'], 2); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
        <!-- Removed the hero card with 'Welcome to Mini Shopping Cart' -->
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>