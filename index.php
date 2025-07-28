<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/AdminAuth.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!$adminAuth->isLoggedIn()) {
    redirect('login.php');
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['delete_user'];
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: index.php?user_deleted=1');
        exit();
    } elseif (isset($_POST['block_user'])) {
        $user_id = (int)$_POST['block_user'];
        $stmt = $db->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: index.php?user_blocked=1');
        exit();
    } elseif (isset($_POST['unblock_user'])) {
        $user_id = (int)$_POST['unblock_user'];
        $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: index.php?user_unblocked=1');
        exit();
    }
}

// Fetch all users
$stmt = $db->prepare("SELECT id, username, email, created_at, status FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .users-table th, .users-table td { text-align: center; }
        .users-table .btn { margin: 0 2px; }
        .users-table .status-active { color: var(--success-color); font-weight: 600; }
        .users-table .status-blocked { color: var(--danger-color); font-weight: 600; }
    </style>
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
                <h1>Admin Dashboard</h1>
            </div>
            <div class="card-body">
                <h2>Welcome, <?php echo $_SESSION['admin_username']; ?>!</h2>
                <div class="mt-4">
                    <h3>Quick Stats</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h4>Total Products</h4>
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM products";
                            $stmt = $db->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->fetch();
                            ?>
                            <p><?php echo $result['total']; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Total Users</h4>
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM users";
                            $stmt = $db->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->fetch();
                            ?>
                            <p><?php echo $result['total']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h3>All Users</h3>
                    <table class="cart-table users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['created_at']; ?></td>
                                <td>
                                    <span class="status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="index.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                                    </form>
                                    <?php if ($user['status'] === 'active'): ?>
                                        <form action="index.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="block_user" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-secondary" onclick="return confirm('Block this user?')">Block</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="index.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="unblock_user" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn" onclick="return confirm('Unblock this user?')">Unblock</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
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