<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user's orders
$orderStmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orderStmt->execute([$_SESSION['user_id']]);
$orders = $orderStmt->fetchAll();

$pageTitle = "My Account";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="account-layout">
        <aside class="account-sidebar">
            <nav class="account-nav">
                <a href="#profile" class="active">Profile</a>
                <a href="#orders">Orders</a>
                <a href="#addresses">Addresses</a>
                <a href="/logout.php">Logout</a>
            </nav>
        </aside>

        <main class="account-main">
            <section id="profile" class="account-section">
                <h2>Profile Information</h2>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <button class="btn btn-secondary" onclick="toggleEditProfile()">Edit Profile</button>
                </div>

                <form id="edit-profile-form" class="hidden" method="POST" action="/api/update-profile.php">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </section>

            <section id="orders" class="account-section">
                <h2>Order History</h2>
                <?php if (empty($orders)): ?>
                    <p>No orders found.</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-number">Order #<?php echo $order['id']; ?></span>
                                    <span class="order-date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-details">
                                    <p>Items: <?php echo $order['item_count']; ?></p>
                                    <p>Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
                                    <p>Status: <?php echo ucfirst($order['status']); ?></p>
                                </div>
                                <a href="/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>

<script>
function toggleEditProfile() {
    const profileInfo = document.querySelector('.profile-info');
    const editForm = document.getElementById('edit-profile-form');
    profileInfo.classList.toggle('hidden');
    editForm.classList.toggle('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?> 