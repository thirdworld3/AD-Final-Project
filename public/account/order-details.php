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

// Require login
if (!is_logged_in()) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$user = current_user();
$pdo = get_db_connection();

// Get order ID from URL
$order_id = (int)($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Get order details - only allow users to view their own orders
$query = "
    SELECT o.*, u.fullname, u.username, u.email
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    WHERE o.id = ? AND o.buyer_id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$order_id, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash_message('Order not found or you do not have permission to view it.', 'error');
    header('Location: orders.php');
    exit;
}

// Get order items with product info
$items_query = "
    SELECT oi.*, p.title as product_name, p.price as product_price, p.description
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$stmt = $pdo->prepare($items_query);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['id']; ?> Details • The Forbidden Codex</title>
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
                    <a href="orders.php" class="btn btn-secondary">Back to My Orders</a>
                    <a href="index.php" class="btn btn-secondary">Account</a>
                    <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
                        <a href="../admin/index.php" class="btn btn-secondary">Dashboard</a>
                    <?php endif; ?>
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
                <p>Details of your mystical transaction</p>
            </div>

            <div class="admin-card">
                <div class="card-header">
                    <h2>Order Information</h2>
                </div>
                
                <div class="order-details-grid">
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
                                    <span class="status-badge <?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
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

                    <!-- Customer Information -->
                    <div class="detail-section customer-details-section">
                        <h3>My Details</h3>
                        
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
                                    <a href="orders.php" class="btn btn-sm btn-secondary">← My Orders</a>
                                    <button onclick="window.print()" class="btn btn-sm btn-primary">🖨️ Print</button>
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
                                                    <?php if ($item['description']): ?>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                            <div class="product-description"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?><?php echo strlen($item['description']) > 100 ? '...' : ''; ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                    <?php endif; ?>
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

                <!-- Order Status Information -->
                <div class="detail-section">
                    <h3>Order Status</h3>
                    <div class="status-info">
                        <?php if ($order['payment_status'] === 'pending'): ?>
                            <div class="status-message status-pending">
                                <strong>Payment Pending</strong>
                                <p>Your order is awaiting payment confirmation. Please ensure your payment method is valid.</p>
                            </div>
                        <?php elseif ($order['payment_status'] === 'paid'): ?>
                            <div class="status-message status-paid">
                                <strong>Payment Confirmed</strong>
                                <p>Your payment has been successfully processed. Your mystical items will be delivered soon!</p>
                            </div>
                        <?php elseif ($order['payment_status'] === 'failed'): ?>
                            <div class="status-message status-failed">
                                <strong>Payment Failed</strong>
                                <p>There was an issue processing your payment. Please contact support or try placing a new order.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <style>
        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .detail-section {
            margin-bottom: 2rem;
        }
        
        .detail-section h3 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .info-grid {
            display: grid;
            gap: 0.75rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(157, 153, 153, 0.1);
        }
        
        .info-label {
            color: #9D9999;
            font-weight: 600;
        }
        
        .info-value {
            color: #D8D4D3;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .order-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .product-description {
            max-width: 200px;
            color: #9D9999;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #ff9800; color: white; }
        .status-paid { background: #4CAF50; color: white; }
        .status-failed { background: #f44336; color: white; }
        
        .status-info {
            margin-top: 1rem;
        }
        
        .status-message {
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .status-message.status-pending {
            background: rgba(255, 152, 0, 0.1);
            border-left-color: #ff9800;
        }
        
        .status-message.status-paid {
            background: rgba(76, 175, 80, 0.1);
            border-left-color: #4CAF50;
        }
        
        .status-message.status-failed {
            background: rgba(244, 67, 54, 0.1);
            border-left-color: #f44336;
        }
        
        .status-message strong {
            color: #D8D4D3;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .status-message p {
            color: #9D9999;
            margin: 0;
        }
        
        .total-row {
            background: rgba(157, 153, 153, 0.1);
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .product-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-description {
                max-width: 100%;
            }
        }
    </style>
</body>
</html>
