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

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query - only show current user's orders
$where_conditions = ["o.buyer_id = ?"];
$params = [$user['id']];

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR o.id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter) {
    $where_conditions[] = "o.payment_status = ?";
    $params[] = $status_filter;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total count
$count_query = "
    SELECT COUNT(DISTINCT o.id) as total 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    $where_clause
";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_orders = $stmt->fetch()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders with item info
$query = "
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           GROUP_CONCAT(DISTINCT p.title SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders • The Forbidden Codex</title>
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
                    <a href="index.php" class="btn btn-secondary">Account</a>
                    <a href="../products.php" class="btn btn-secondary">Browse Products</a>
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
                <h1>My Orders</h1>
                <p>Track your mystical purchases and transactions</p>
            </div>

            <!-- Filters and Search -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search by product name or order ID..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <select name="status" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="orders.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="admin-card">
                <div class="card-header">
                    <h2>Your Orders (<?php echo number_format($total_orders); ?> total)</h2>
                </div>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>No orders found matching your criteria.</p>
                        <a href="../products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Products</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>
                                            <div class="product-names">
                                                <?php 
                                                $names = $order['product_names'] ? explode(', ', $order['product_names']) : ['Unknown Product'];
                                                $display_names = array_slice($names, 0, 2);
                                                echo htmlspecialchars(implode(', ', $display_names));
                                                if (count($names) > 2) {
                                                    echo ' <span class="more-items">+' . (count($names) - 2) . ' more</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td><?php echo $order['item_count']; ?> item(s)</td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-secondary btn-sm">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                                   class="btn btn-secondary">Previous</a>
                            <?php endif; ?>
                            
                            <span class="pagination-info">
                                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                                   class="btn btn-secondary">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <style>
        .product-names {
            max-width: 200px;
            word-wrap: break-word;
        }
        
        .more-items {
            color: #9D9999;
            font-style: italic;
            font-size: 0.9em;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { 
            background: #ff9800; 
            color: white; 
        }
        
        .status-paid { 
            background: #4CAF50; 
            color: white; 
        }
        
        .status-failed { 
            background: #f44336; 
            color: white; 
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
        
        @media (max-width: 768px) {
            .admin-table {
                font-size: 0.9rem;
            }
            
            .product-names {
                max-width: 150px;
            }
        }
    </style>
</body>
</html>
