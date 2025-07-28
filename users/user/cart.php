<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/Cart.php';

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    redirect('../user/login.php');
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    switch ($action) {
        case 'add':
            if ($product_id > 0) {
                $cart->addToCart($product_id);
                $_SESSION['success_message'] = "Product added to cart!";
            }
            break;
            
        case 'remove':
            if ($product_id > 0) {
                $cart->removeFromCart($product_id);
                $_SESSION['success_message'] = "Product removed from cart!";
            }
            break;
            
        case 'update':
            if ($product_id > 0 && isset($_POST['quantity'])) {
                $quantity = (int)$_POST['quantity'];
                $cart->updateQuantity($product_id, $quantity);
                $_SESSION['success_message'] = "Cart updated!";
            }
            break;
            
        case 'clear':
            $cart->clearCart();
            $_SESSION['success_message'] = "Cart cleared!";
            break;
    }
    
    redirect('cart.php');
}

// Get cart items
$cart_items = $cart->getCartItems();
$cart_total = $cart->getCartTotal();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | <?php echo SITE_NAME; ?></title>
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
                <h1>Your Shopping Cart</h1>
            </div>
            <div class="card-body">
                <?php flash(); ?>
                
                <?php if (!empty($cart_items)): ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form action="cart.php?action=update&id=<?php echo $item['id']; ?>" method="POST">
                                            <input type="number" name="quantity" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                            <button type="submit" class="btn btn-secondary">Update</button>
                                        </form>
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-danger">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="mt-4">
                        <a href="checkout.php" class="btn">Proceed to Checkout</a>
                        <a href="cart.php?action=clear" class="btn btn-danger">Clear Cart</a>
                        <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <p>Your cart is empty. <a href="products.php">Browse products</a> to add items to your cart.</p>
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