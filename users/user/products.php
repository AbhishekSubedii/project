<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/Product.php';

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    redirect('../user/login.php');
}

// Search functionality
$search_query = '';
$products = [];

if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $products = $product->searchProducts($search_query);
} else {
    $products = $product->getAllProducts();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | <?php echo SITE_NAME; ?></title>
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
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <div class="card-header">
                <h1>Our Products</h1>
            </div>
            <div class="card-body">
                <!-- Search form -->
                <form action="products.php" method="GET" class="mb-4">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <button type="submit" class="btn">Search</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="products.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
                
                <!-- Products grid -->
                <div class="products-grid">
                    <?php foreach ($products as $product_item): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product_item['image_url']); ?>" alt="<?php echo htmlspecialchars($product_item['name']); ?>" class="product-img">
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product_item['name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($product_item['description'], 0, 100)); ?>...</p>
                                <p class="product-price">$<?php echo number_format($product_item['price'], 2); ?></p>
                                <a href="cart.php?action=add&id=<?php echo $product_item['id']; ?>" class="btn">Add to Cart</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($products)): ?>
                    <p>No products found.</p>
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