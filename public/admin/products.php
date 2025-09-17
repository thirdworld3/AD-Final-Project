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

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_product':
                $product_id = (int)$_POST['product_id'];
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                set_flash_message('Product deleted successfully.', 'success');
                break;
                
            case 'update_stock':
                $product_id = (int)$_POST['product_id'];
                $new_stock = max(0, (int)$_POST['stock']);
                $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $stmt->execute([$new_stock, $product_id]);
                set_flash_message('Stock updated successfully.', 'success');
                break;
        }
        header('Location: products.php');
        exit;
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetch()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products with seller and category info
$query = "
    SELECT p.*, u.username as seller_name, c.name as category_name
    FROM products p
    LEFT JOIN users u ON p.seller_id = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    $where_clause
    ORDER BY p.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products • Admin • The Forbidden Codex</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../../favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="nav-logo">
                    <h2 class="logo-text"><a href="../../index.php">The Forbidden Codex</a></h2>
                </div>
                <div class="nav-buttons">
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
                <h1><i class="fas fa-magic"></i> Manage Products</h1>
                <p>Oversee the mystical artifacts in your realm</p>
            </div>

            <!-- Filters and Search -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="products.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <!-- Products Table -->
            <div class="admin-card">
                <div class="card-header">
                    <h2>Products (<?php echo number_format($total_products); ?> total)</h2>
                </div>
                
                <?php if (empty($products)): ?>
                    <p class="empty-state">No products found matching your criteria.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-image"></i> Product</th>
                                    <th><i class="fas fa-user"></i> Seller</th>
                                    <th><i class="fas fa-tags"></i> Category</th>
                                    <th><i class="fas fa-dollar-sign"></i> Price</th>
                                    <th><i class="fas fa-boxes"></i> Stock</th>
                                    <th><i class="fas fa-calendar"></i> Created</th>
                                    <th><i class="fas fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <div class="product-info-with-image">
                                                <div class="product-thumbnail">
                                                    <?php 
                                                    $image_path = "../assets/images/products/" . strtolower(str_replace(' ', '_', $product['title'])) . ".jpg";
                                                    if (file_exists($image_path)): 
                                                    ?>
                                                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-thumb">
                                                    <?php else: ?>
                                                        <div class="product-placeholder">
                                                            <?php 
                                                            $category_icons = [
                                                                'spellbooks' => 'fas fa-book',
                                                                'potions' => 'fas fa-flask',
                                                                'artifacts' => 'fas fa-gem',
                                                                'scrolls' => 'fas fa-scroll',
                                                                'crystals' => 'fas fa-diamond'
                                                            ];
                                                            $icon = $category_icons[strtolower($product['category_name'])] ?? 'fas fa-magic';
                                                            ?>
                                                            <i class="<?php echo $icon; ?>"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product-details">
                                                    <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                                    <div class="product-description">
                                                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['seller_name'] ?? 'Unknown'); ?></td>
                                        <td>
                                            <span class="category-badge">
                                                <?php 
                                                $category_icons = [
                                                    'spellbooks' => 'fas fa-book',
                                                    'potions' => 'fas fa-flask',
                                                    'artifacts' => 'fas fa-gem',
                                                    'scrolls' => 'fas fa-scroll',
                                                    'crystals' => 'fas fa-diamond'
                                                ];
                                                $icon = $category_icons[strtolower($product['category_name'])] ?? 'fas fa-magic';
                                                ?>
                                                <i class="<?php echo $icon; ?>"></i>
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="price-display">
                                                <i class="fas fa-coins"></i>
                                                $<?php echo number_format($product['price'], 2); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="stock-container">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_stock">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <div class="stock-input-group">
                                                        <i class="fas fa-cube"></i>
                                                        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" 
                                                               min="0" class="stock-input" onchange="this.form.submit()">
                                                    </div>
                                                </form>
                                                <span class="stock-status <?php echo $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock'); ?>">
                                                    <?php 
                                                    if ($product['stock'] > 10) echo '<i class="fas fa-check-circle"></i> In Stock';
                                                    elseif ($product['stock'] > 0) echo '<i class="fas fa-exclamation-triangle"></i> Low Stock';
                                                    else echo '<i class="fas fa-times-circle"></i> Out of Stock';
                                                    ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="../product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-secondary btn-sm" target="_blank">
                                                   <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="action" value="delete_product">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
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
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>" 
                                   class="btn btn-secondary">Previous</a>
                            <?php endif; ?>
                            
                            <span class="pagination-info">
                                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>" 
                                   class="btn btn-secondary">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
