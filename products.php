<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/AdminAuth.php';
require_once '../includes/Product.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!$adminAuth->isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_product'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $image_url = '';
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            $filename = uniqid() . '_' . basename($_FILES['image_file']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
                $image_url = '/mini_shopping_cart/assets/images/' . $filename;
            }
        } else if (!empty($_POST['image_url'])) {
            $image_url = trim($_POST['image_url']);
        }
        if (empty($name) || empty($price)) {
            $error = "Name and price are required";
        } else {
            if ($product->createProduct($name, $description, $price, $image_url)) {
                $success = "Product created successfully!";
            } else {
                $error = "Failed to create product";
            }
        }
    } elseif (isset($_POST['update_product'])) {
        error_log('UPDATE POST: ' . print_r($_POST, true));
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $image_url = '';
        // Handle file upload for edit
        if (isset($_FILES['edit_image_file']) && $_FILES['edit_image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            $filename = uniqid() . '_' . basename($_FILES['edit_image_file']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['edit_image_file']['tmp_name'], $target_file)) {
                $image_url = '/mini_shopping_cart/assets/images/' . $filename;
            }
        } else if (!empty($_POST['image_url'])) {
            $image_url = trim($_POST['image_url']);
        } else if (!empty($_POST['current_image_url'])) {
            $image_url = trim($_POST['current_image_url']);
        }
        if (empty($name) || empty($price)) {
            $error = "Name and price are required";
        } else {
            if ($product->updateProduct($id, $name, $description, $price, $image_url)) {
                header('Location: products.php?updated=1');
                exit();
            } else {
                $error = "Failed to update product";
            }
        }
    } elseif (isset($_POST['delete_product'])) {
        $id = (int)$_POST['delete_product'];
        // Optionally, delete the image file from the server
        $prod = $product->getProductById($id);
        if ($product->deleteProduct($id)) {
            // Remove image file if it exists and is local
            if (!empty($prod['image_url']) && strpos($prod['image_url'], '/mini_shopping_cart/assets/images/') === 0) {
                $img_path = '..' . str_replace('/mini_shopping_cart', '', $prod['image_url']);
                if (file_exists($img_path)) { unlink($img_path); }
            }
            header('Location: products.php?deleted=1');
            exit();
        } else {
            header('Location: products.php?deleted=0');
            exit();
        }
    }
}

// Get all products
$products = $product->getAllProducts();

if (isset($_GET['deleted'])) {
    if ($_GET['deleted'] == '1') {
        $success = 'Product deleted successfully!';
    } else {
        $error = 'Failed to delete product';
    }
}
if (isset($_GET['updated'])) {
    if ($_GET['updated'] == '1') {
        $success = 'Product updated successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | <?php echo SITE_NAME; ?></title>
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
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h1>Manage Products</h1>
            </div>
            <div class="card-body">
                <h2>Add New Product</h2>
                <form action="products.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_file">Product Image</label>
                        <input type="file" id="image_file" name="image_file" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="image_url">Or Image URL</label>
                        <input type="text" id="image_url" name="image_url" class="form-control">
                    </div>
                    
                    <button type="submit" name="create_product" class="btn">Add Product</button>
                </form>
                
                <hr>
                
                <h2>Product List</h2>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product_item): ?>
                            <tr>
                                <td><?php echo $product_item['id']; ?></td>
                                <td><?php echo htmlspecialchars($product_item['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($product_item['description'], 0, 50)); ?>...</td>
                                <td>$<?php echo number_format($product_item['price'], 2); ?></td>
                                <td>
                                    <?php if (!empty($product_item['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product_item['image_url']); ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="#" onclick="editProduct(<?php echo $product_item['id']; ?>)" class="btn">Edit</a>
                                    <form action="products.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_product" value="<?php echo $product_item['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Edit Product Modal -->
        <div id="editModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Edit Product</h2>
                <div id="editModalError" style="color: red; display: none;"></div>
                <form id="editForm" action="products.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="update_product" value="1">
                    <input type="hidden" name="current_image_url" id="current_image_url">
                    
                    <div class="form-group">
                        <label for="edit_name">Product Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_price">Price</label>
                        <input type="number" id="edit_price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_image_file">Change Product Image</label>
                        <input type="file" id="edit_image_file" name="edit_image_file" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_image_url">Or Image URL</label>
                        <input type="text" id="edit_image_url" name="image_url" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn">Update Product</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Edit product function
        function editProduct(id) {
            // Show modal and loading message immediately
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('editModalError').style.display = 'none';
            document.getElementById('editModalError').innerText = '';
            document.getElementById('editForm').reset();
            // Fetch product data via AJAX with required header
            fetch(`get_product.php?id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Error: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        document.getElementById('edit_id').value = data.id;
                        document.getElementById('edit_name').value = data.name;
                        document.getElementById('edit_description').value = data.description;
                        document.getElementById('edit_price').value = data.price;
                        document.getElementById('edit_image_url').value = data.image_url || '';
                        document.getElementById('current_image_url').value = data.image_url || '';
                    } else {
                        document.getElementById('editModalError').innerText = 'No product data found.';
                        document.getElementById('editModalError').style.display = 'block';
                    }
                })
                .catch(error => {
                    document.getElementById('editModalError').innerText = 'Failed to load product: ' + error;
                    document.getElementById('editModalError').style.display = 'block';
                });
        }
        
        // Close modal function
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>