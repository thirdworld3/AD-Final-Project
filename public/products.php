<?php
// Prevent any output before session handling
ob_start();

// Start session first, before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/helpers.php';

// Get products from database
try {
    $pdo = get_db_connection();
    
    // Get categories for filter
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    
    // Handle filtering
    $category_filter = $_GET['category'] ?? '';
    $search_query = $_GET['search'] ?? '';
    
    $sql = "SELECT p.*, c.name as category_name, u.fullname as seller_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            JOIN users u ON p.seller_id = u.id 
            WHERE p.stock > 0";
    $params = [];
    
    if ($category_filter) {
        $sql .= " AND c.id = ?";
        $params[] = $category_filter;
    }
    
    if ($search_query) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
} catch (Exception $e) {
    $products = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sacred Offerings • The Forbidden Codex</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
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
                    <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): 
                        $user = $_SESSION['user']; 
                    ?>
                        <a href="cart/index.php" class="btn btn-secondary cart-btn">
                            🛒 Cart <span class="cart-count">0</span>
                        </a>
                        <a href="account/index.php" class="btn btn-secondary">Account</a>
                        <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
                            <a href="admin/index.php" class="btn btn-secondary">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-login">Logout</a>
                    <?php else: ?>
                        <a href="signup.php" class="btn btn-signin">Sign Up</a>
                        <a href="login.php" class="btn btn-login">Log In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Products Header -->
    <section class="products-header">
        <div class="container">
            <h1 class="page-title">Sacred Offerings</h1>
            <p class="page-subtitle">Discover mystical artifacts and forbidden knowledge from our trusted merchants</p>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search the ancient archives..." 
                           value="<?php echo htmlspecialchars($search_query); ?>" class="search-input">
                </div>
                <div class="filter-group">
                    <select name="category" class="category-select">
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
                <?php if ($category_filter || $search_query): ?>
                    <a href="products.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section">
        <div class="container">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <h3>No Sacred Offerings Found</h3>
                    <p>The ancient merchants have not yet blessed us with offerings matching your search.</p>
                    <a href="products.php" class="btn btn-primary">View All Offerings</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <div class="mystical-placeholder"></div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <div class="product-meta">
                                    <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="product-stock"><?php echo $product['stock']; ?> in stock</span>
                                </div>
                                <div class="product-seller">By: <?php echo htmlspecialchars($product['seller_name']); ?></div>
                                <div class="product-actions">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                    <?php if (is_logged_in()): ?>
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-secondary">Add to Cart</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">The Forbidden Codex</h3>
                    <p class="footer-description">Where ancient wisdom meets modern technology.</p>
                </div>
                <div class="footer-section">
                    <h4 class="footer-subtitle">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="index.php#about">About</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 The Forbidden Codex. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    
    <style>
        .products-header {
            background: linear-gradient(135deg, #2C2A2B 0%, #484949 100%);
            padding: 4rem 0 2rem;
            text-align: center;
        }
        
        .page-title {
            font-family: 'Cinzel', serif;
            font-size: 3rem;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: #9D9999;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .logo-text{
            font-family: 'Cinzel', serif;
            font-size: 1.5rem;
    font-weight: 600;
    color: #9D9999;
    text-shadow: 0 0 10px rgba(157, 153, 153, 0.3);
    margin: 0; 
        }

        .filters-section {
            background: rgba(28, 26, 27, 0.95);
            padding: 2rem 0;
            border-bottom: 1px solid rgba(157, 153, 153, 0.2);
        }
        
        .filters-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .search-input, .category-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(157, 153, 153, 0.3);
            background: rgba(216, 212, 211, 0.1);
            color: #D8D4D3;
            border-radius: 5px;
        }
        
        .products-section {
            padding: 3rem 0;
            min-height: 50vh;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(157, 153, 153, 0.3);
        }
        
        .product-image {
            height: 200px;
            background: linear-gradient(45deg, #333031, #484949);
            position: relative;
        }
        
        .mystical-placeholder {
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(157, 153, 153, 0.3) 0%, transparent 70%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-title {
            font-family: 'Cinzel', serif;
            font-size: 1.3rem;
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .product-category {
            color: #9D9999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .product-description {
            color: #9D9999;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-family: 'Cinzel', serif;
            font-size: 1.5rem;
            color: #D8D4D3;
            font-weight: 600;
        }
        
        .product-stock {
            color: #9D9999;
            font-size: 0.9rem;
        }
        
        .product-seller {
            color: #9D9999;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            font-style: italic;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .product-actions .btn {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        
        .no-products {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .no-products h3 {
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .no-products p {
            color: #9D9999;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .filters-form {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .product-actions {
                flex-direction: column;
            }
        }
        
        .cart-btn {
            position: relative;
        }
        
        .cart-count {
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            margin-left: 0.5rem;
            min-width: 1.5rem;
            text-align: center;
            display: inline-block;
        }
    </style>
</body>
</html>