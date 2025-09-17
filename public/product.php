<?php
// Start output buffering to prevent header issues
ob_start();

// Start session first, before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/helpers.php';

$product_id = $_GET['id'] ?? 0;
$product = null;
$error = null;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

try {
    $pdo = get_db_connection();
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, u.fullname as seller_name, u.id as seller_id
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.seller_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: 404.php');
        exit;
    }
    
} catch (Exception $e) {
    $error = "Failed to load product details.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title'] ?? 'Product'); ?> • The Forbidden Codex</title>
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

    <?php if ($error): ?>
        <div class="error-message">
            <div class="container">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="products.php" class="btn btn-primary">Back to Products</a>
            </div>
        </div>
    <?php elseif ($product): ?>
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <div class="container">
                <a href="index.php">Home</a> > 
                <a href="products.php">Products</a> > 
                <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> > 
                <span><?php echo htmlspecialchars($product['title']); ?></span>
            </div>
        </div>

        <!-- Product Details -->
        <section class="product-detail">
            <div class="container">
                <div class="product-layout">
                    <div class="product-image-section">
                        <div class="main-image">
                            <div class="mystical-placeholder-large"></div>
                        </div>
                    </div>
                    
                    <div class="product-info-section">
                        <div class="product-header">
                            <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        </div>
                        
                        <div class="product-price-section">
                            <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <span class="product-stock">
                                <?php if ($product['stock'] > 0): ?>
                                    <?php echo $product['stock']; ?> in stock
                                <?php else: ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="product-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                        
                        <div class="product-seller-info">
                            <h4>Merchant</h4>
                            <p>By: <?php echo htmlspecialchars($product['seller_name']); ?></p>
                            <p class="product-date">Listed: <?php echo date('F j, Y', strtotime($product['created_at'])); ?></p>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <div class="product-actions">
                                <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
                                    <div class="quantity-section">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" id="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                                    </div>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('quantity').value)" 
                                            class="btn btn-primary btn-large">Add to Cart</button>
                                    <button onclick="buyNow(<?php echo $product['id']; ?>)" 
                                            class="btn btn-secondary btn-large">Buy Now</button>
                                <?php else: ?>
                                    <p class="login-prompt">Please <a href="login.php">log in</a> to purchase this item.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section class="related-products">
            <div class="container">
                <h2>Other Sacred Offerings</h2>
                <div class="related-grid">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT p.*, c.name as category_name 
                            FROM products p 
                            JOIN categories c ON p.category_id = c.id 
                            WHERE p.category_id = ? AND p.id != ? AND p.stock > 0 
                            ORDER BY RAND() 
                            LIMIT 4
                        ");
                        $stmt->execute([$product['category_id'], $product['id']]);
                        $related_products = $stmt->fetchAll();
                        
                        foreach ($related_products as $related): ?>
                            <div class="related-card">
                                <div class="related-image">
                                    <div class="mystical-placeholder"></div>
                                </div>
                                <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                                <p class="related-price">$<?php echo number_format($related['price'], 2); ?></p>
                                <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-secondary">View</a>
                            </div>
                        <?php endforeach;
                    } catch (Exception $e) {
                        // Ignore related products error
                    }
                    ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">The Forbidden Codex</h3>
                    <p class="footer-description">Where ancient wisdom meets modern technology.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 The Forbidden Codex. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
        function buyNow(productId) {
            const quantity = document.getElementById('quantity').value;
            window.location.href = `payment.php?product_id=${productId}&quantity=${quantity}`;
        }
    </script>
    
    <style>
        .breadcrumb {
            background: rgba(28, 26, 27, 0.95);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(157, 153, 153, 0.2);
        }
        
        .logo-text {
            font-family: 'Cinzel', serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: #9D9999;
    text-shadow: 0 0 10px rgba(157, 153, 153, 0.3);
    margin: 0;
        }

        .breadcrumb a {
            color: #9D9999;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: #D8D4D3;
        }
        
        .breadcrumb span {
            color: #D8D4D3;
        }
        
        .product-detail {
            padding: 3rem 0;
        }
        
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }
        
        .product-image-section {
            position: sticky;
            top: 2rem;
        }
        
        .main-image {
            width: 100%;
            height: 500px;
            background: linear-gradient(45deg, #333031, #484949);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .mystical-placeholder-large {
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(157, 153, 153, 0.3) 0%, transparent 70%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-info-section {
            space-y: 2rem;
        }
        
        .product-header {
            margin-bottom: 2rem;
        }
        
        .product-title {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .product-category {
            color: #9D9999;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .product-price-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(28, 26, 27, 0.5);
            border-radius: 10px;
        }
        
        .product-price {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            font-weight: 600;
        }
        
        .product-stock {
            color: #9D9999;
            font-size: 1.1rem;
        }
        
        .out-of-stock {
            color: #ff6b6b;
            font-weight: bold;
        }
        
        .product-description {
            margin-bottom: 2rem;
        }
        
        .product-description h3 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .product-description p {
            color: #9D9999;
            line-height: 1.6;
        }
        
        .product-seller-info {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(28, 26, 27, 0.3);
            border-radius: 10px;
        }
        
        .product-seller-info h4 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .product-seller-info p {
            color: #9D9999;
            margin-bottom: 0.5rem;
        }
        
        .product-date {
            font-size: 0.9rem;
            font-style: italic;
        }
        
        .product-actions {
            space-y: 1rem;
        }
        
        .quantity-section {
            margin-bottom: 1rem;
        }
        
        .quantity-section label {
            display: block;
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .quantity-section input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid rgba(157, 153, 153, 0.3);
            background: rgba(216, 212, 211, 0.1);
            color: #D8D4D3;
            border-radius: 5px;
        }
        
        .btn-large {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .login-prompt {
            color: #9D9999;
            text-align: center;
            padding: 2rem;
        }
        
        .login-prompt a {
            color: #D8D4D3;
            text-decoration: none;
        }
        
        .related-products {
            padding: 3rem 0;
            background: rgba(28, 26, 27, 0.3);
        }
        
        .related-products h2 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .related-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        
        .related-image {
            height: 150px;
            background: linear-gradient(45deg, #333031, #484949);
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .related-card h4 {
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .related-price {
            color: #9D9999;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .error-message {
            padding: 3rem 0;
            text-align: center;
            color: #ff6b6b;
        }
        
        @media (max-width: 768px) {
            .product-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-image-section {
                position: static;
            }
            
            .main-image {
                height: 300px;
            }
            
            .product-title {
                font-size: 2rem;
            }
            
            .product-price {
                font-size: 2rem;
            }
            
            .product-price-section {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
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