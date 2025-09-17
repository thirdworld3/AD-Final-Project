<?php
// Start output buffering to prevent header issues
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure session is started before any output
ensure_session_started();

// Require admin access
if (!is_logged_in() || current_user()['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit;
}

$pdo = get_db_connection();

// Get order ID from URL
$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Get order details with buyer info
$query = "
    SELECT o.*, u.fullname, u.username, u.email
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    WHERE o.id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items with product info
$items_query = "
    SELECT oi.*, p.title as product_name, p.price as product_price, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$stmt = $pdo->prepare($items_query);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = $_POST['payment_status'];
    if (in_array($new_status, ['pending', 'paid', 'failed'])) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        set_flash_message('Order status updated successfully.', 'success');
        header("Location: order-details.php?id=$order_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['id']; ?> Details • Admin • The Forbidden Codex</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../../favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="nav-logo">
                    <h2 class="logo-text">The Forbidden Codex</h2>
                </div>
                <div class="nav-buttons">
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                    <a href="index.php" class="btn btn-secondary">Dashboard</a>
                    <a href="../logout.php" class="btn btn-login">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="container">
            <?php display_flash_messages(); ?>
            
            <div class="admin-header">
                <h1>Order #<?php echo $order['id']; ?></h1>
                <p>Detailed view of mystical transaction</p>
            </div>

            <div class="admin-card">
                <div class="card-header">
                    <h2>Order Information</h2>
                </div>
                
                <div class="order-details-grid">
                    <!-- Customer Information -->
                    <div class="detail-section customer-details-section">
                        <h3>Customer Details</h3>
                        
                        <!-- Customer Header with Avatar and Primary Info -->
                        <div class="customer-header">
                            <div class="customer-avatar">
                                <?php 
                                $name = $order['fullname'] ?: $order['username'];
                                $initials = '';
                                $words = explode(' ', trim($name));
                                foreach($words as $word) {
                                    if(!empty($word)) {
                                        $initials .= strtoupper(substr($word, 0, 1));
                                        if(strlen($initials) >= 2) break;
                                    }
                                }
                                echo $initials ?: strtoupper(substr($name, 0, 1));
                                ?>
                            </div>
                            <div class="customer-primary-info">
                                <div class="customer-display-name"><?php echo htmlspecialchars($order['fullname'] ?: $order['username']); ?></div>
                                <div class="customer-username">@<?php echo htmlspecialchars($order['username']); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item customer-info">
                                <div class="info-label">
                                    <span class="info-icon">📧</span>Email Address
                                </div>
                                <div class="info-value customer-email"><?php echo htmlspecialchars($order['email']); ?></div>
                            </div>
                            <div class="info-item customer-info">
                                <div class="info-label">
                                    <span class="info-icon">📱</span>Phone Number
                                </div>
                                <div class="info-value">Not available</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="detail-section">
                        <h3>Order Details</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <span class="info-icon">📅</span>Order Date
                                </div>
                                <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <span class="info-icon">💰</span>Total Amount
                                </div>
                                <div class="info-value">$<?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <span class="info-icon">💳</span>Payment Status
                                </div>
                                <div class="info-value">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <select name="payment_status" onchange="this.form.submit()" class="status-select">
                                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <span class="info-icon">📦</span>Items Count
                                </div>
                                <div class="info-value"><?php echo count($order_items); ?> item(s)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="detail-section">
                        <h3>Actions</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <span class="info-icon">📊</span>Status
                                </div>
                                <div class="info-value">
                                    <span class="status-badge <?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <span class="info-icon">🔄</span>Quick Actions
                                </div>
                                <div class="info-value">
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="orders.php" class="btn btn-sm btn-secondary">← Back to Orders</a>
                                        <button onclick="window.print()" class="btn btn-sm btn-primary">🖨️ Print</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Order Items -->
            <div class="order-items-section">
                <div class="detail-section">
                    <h3>Order Items</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="product-info">
                                                <?php if ($item['image_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                         class="order-item-image">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="3"><strong>Total</strong></td>
                                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
