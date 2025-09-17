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

try {
    $pdo = get_db_connection();
    
    // Get user's recent orders
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.buyer_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recent_orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $recent_orders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account • The Forbidden Codex</title>
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
                    <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
                        <a href="../admin/index.php" class="btn btn-secondary">Dashboard</a>
                    <?php endif; ?>
                    <a href="../logout.php" class="btn btn-login">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="account-container">
        <div class="container">
            <div class="account-header">
                <h1>Welcome, <?php echo htmlspecialchars($user['fullname'] ?? $user['username'] ?? 'User'); ?></h1>
                <p>Manage your sacred account and view your mystical transactions</p>
            </div>

            <div class="account-grid">
                <!-- Account Info -->
                <div class="account-card">
                    <h2>Account Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($user['fullname'] ?? $user['username'] ?? 'User'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Role:</label>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Member Since:</label>
                            <span><?php echo isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'Unknown'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="account-card">
                    <h2>Quick Actions</h2>
                    <div class="action-buttons">
                        <a href="../products.php" class="btn btn-primary">Browse Products</a>
                        <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                        <?php if ($user['role'] === 'buyer'): ?>
                            <a href="../404.php" class="btn btn-accent">Become a Seller</a>
                        <?php endif; ?>
                        <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
                            <a href="../admin/index.php" class="btn btn-accent">Seller Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="account-card full-width">
                    <h2>Recent Orders</h2>
                    <?php if (empty($recent_orders)): ?>
                        <div class="empty-state">
                            <p>No orders yet. Start your mystical journey!</p>
                            <a href="../products.php" class="btn btn-primary">Browse Products</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['item_count']; ?> items</td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <style>
        .account-container {
            min-height: 80vh;
            padding: 3rem 0;
        }
        
        .account-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .account-header h1 {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .account-header p {
            color: #9D9999;
            font-size: 1.2rem;
        }
        
        .account-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .account-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
        }
        
        .account-card.full-width {
            grid-column: 1 / -1;
        }
        
        .account-card h2 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .info-grid {
            display: grid;
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(157, 153, 153, 0.1);
        }
        
        .info-item label {
            color: #9D9999;
            font-weight: 600;
        }
        
        .info-item span {
            color: #D8D4D3;
        }
        
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .role-buyer { background: #4CAF50; color: white; }
        .role-seller { background: #2196F3; color: white; }
        .role-admin { background: #ff9800; color: white; }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-accent {
            background: linear-gradient(45deg, #9D9999, #D8D4D3);
            color: #1C1A1B;
            border: none;
            font-weight: bold;
        }
        
        .btn-accent:hover {
            background: linear-gradient(45deg, #D8D4D3, #9D9999);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .empty-state p {
            color: #9D9999;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .orders-table {
            overflow-x: auto;
        }
        
        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(157, 153, 153, 0.2);
        }
        
        .orders-table th {
            color: #D8D4D3;
            font-family: 'Cinzel', serif;
            font-weight: 600;
        }
        
        .orders-table td {
            color: #9D9999;
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
        
        .table-footer {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .account-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .orders-table {
                font-size: 0.9rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 0.5rem;
            }
        }
    </style>
</body>
</html>
