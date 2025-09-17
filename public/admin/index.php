<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';

Auth::init();

// Require admin access
if (!Auth::check() || Auth::user()['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user = Auth::user();

try {
    require_once UTILS_PATH . '/envSetter.util.php';
    
    // Get database configuration
    $databases = getDatabaseConfig();
    
    $host = $databases['pgHost'];
    $port = $databases['pgPort'];
    $dbname = $databases['pgDB'];
    $username = $databases['pgUser'];
    $password = $databases['pgPassword'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // Get statistics
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total projects
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $stats['total_projects'] = $stmt->fetch()['count'] ?? 0;
    
    // Total tasks
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tasks");
    $stats['total_tasks'] = $stmt->fetch()['count'] ?? 0;
    
    // Active projects
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects WHERE status = 'active'");
    $stats['active_projects'] = $stmt->fetch()['count'] ?? 0;
    
    // Recent users
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();
    
    // Recent projects (if any exist)
    $recent_projects = [];
    try {
        $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
        $recent_projects = $stmt->fetchAll();
    } catch (Exception $e) {
        // Projects table might not exist yet
    }
    
} catch (Exception $e) {
    $stats = ['total_users' => 0, 'total_projects' => 0, 'total_tasks' => 0, 'active_projects' => 0];
    $recent_users = [];
    $recent_projects = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard • The Forbidden Codex</title>
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
                    <a href="../products.php" class="btn btn-secondary">Products</a>
                    <a href="../account/index.php" class="btn btn-secondary">Account</a>
                    <form method="POST" action="../../handlers/auth.handler.php" style="display: inline;">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-login">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="container">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome, Master of the Codex. Command the digital realm from here.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_projects']); ?></h3>
                        <p>Total Projects</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_tasks']); ?></h3>
                        <p>Total Tasks</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🚀</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['active_projects']); ?></h3>
                        <p>Active Projects</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="users.php" class="action-card">
                        <div class="action-icon">👥</div>
                        <h3>Manage Users</h3>
                        <p>View, edit, and manage user accounts</p>
                    </a>
                    <a href="products.php" class="action-card">
                        <div class="action-icon">📦</div>
                        <h3>Manage Products</h3>
                        <p>Oversee all products and inventory</p>
                    </a>
                    <a href="orders.php" class="action-card">
                        <div class="action-icon">🛒</div>
                        <h3>Manage Orders</h3>
                        <p>Track and manage all orders</p>
                    </a>
                    <a href="categories.php" class="action-card">
                        <div class="action-icon">📂</div>
                        <h3>Manage Categories</h3>
                        <p>Add and edit product categories</p>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="admin-grid">
                <!-- Recent Users -->
                <div class="admin-card">
                    <h2>Recent Users</h2>
                    <?php if (empty($recent_users)): ?>
                        <p class="empty-state">No users found.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $recent_user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($recent_user['fullname'] ?? $recent_user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($recent_user['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="role-badge role-<?php echo htmlspecialchars($recent_user['role'] ?? 'user', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo ucfirst($recent_user['role'] ?? 'user'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo isset($recent_user['created_at']) ? date('M j, Y', strtotime($recent_user['created_at'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="users.php" class="btn btn-secondary">View All Users</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Orders -->
                <div class="admin-card">
                    <h2>Recent Orders</h2>
                    <?php if (empty($recent_orders)): ?>
                        <p class="empty-state">No orders found.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
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
                        <div class="card-footer">
                            <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <style>
        .admin-container {
            min-height: 80vh;
            padding: 3rem 0;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .admin-header h1 {
            font-family: 'Cinzel', serif;
            font-size: 3rem;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .admin-header p {
            color: #9D9999;
            font-size: 1.2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        
        .stat-info h3 {
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .stat-info p {
            color: #9D9999;
            margin: 0;
        }
        
        .admin-actions {
            margin-bottom: 3rem;
        }
        
        .admin-actions h2 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .action-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(157, 153, 153, 0.3);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        .action-card h3 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .action-card p {
            color: #9D9999;
            margin: 0;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .admin-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
        }
        
        .admin-card h2 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1.5rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(157, 153, 153, 0.2);
        }
        
        .admin-table th {
            color: #D8D4D3;
            font-family: 'Cinzel', serif;
            font-weight: 600;
        }
        
        .admin-table td {
            color: #9D9999;
        }
        
        .role-badge, .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .role-buyer { background: #4CAF50; color: white; }
        .role-seller { background: #2196F3; color: white; }
        .role-admin { background: #ff9800; color: white; }
        
        .status-pending { background: #ff9800; color: white; }
        .status-paid { background: #4CAF50; color: white; }
        .status-failed { background: #f44336; color: white; }
        
        .empty-state {
            color: #9D9999;
            text-align: center;
            padding: 2rem;
            font-style: italic;
        }
        
        .card-footer {
            text-align: center;
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>
