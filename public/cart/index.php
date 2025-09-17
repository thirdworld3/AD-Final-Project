<?php
// Start output buffering to prevent header issues
ob_start();

// Start session first, before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Require login
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$user = current_user();
$cart_items = [];
$total_amount = 0;
$error = null;

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cart_id = intval($_POST['cart_id'] ?? 0);
    
    try {
        $pdo = get_db_connection();
        
        if ($action === 'update' && $cart_id) {
            $quantity = max(1, intval($_POST['quantity'] ?? 1));
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cart_id, $user['id']]);
        } elseif ($action === 'remove' && $cart_id) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user['id']]);
        }
        
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $error = "Failed to update cart.";
    }
}

// Get cart items
try {
    $pdo = get_db_connection();
    
    // Create cart table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product (user_id, product_id)
        )
    ");
    
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.id, p.title, p.price, p.stock, 
               u.fullname as seller_name, cat.name as category_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN users u ON p.seller_id = u.id
        JOIN categories cat ON p.category_id = cat.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $cart_items = $stmt->fetchAll();
    
    // Calculate total
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
} catch (Exception $e) {
    $error = "Failed to load cart items.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sacred Cart • The Forbidden Codex</title>
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
                    <a href="../account/index.php" class="btn btn-secondary">Account</a>
                    <?php if ($user['role'] === 'seller' || $user['role'] === 'admin'): ?>
                        <a href="../admin/index.php" class="btn btn-secondary">Dashboard</a>
                    <?php endif; ?>
                    <a href="../logout.php" class="btn btn-login">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../index.php">Home</a> > 
            <a href="../products.php">Products</a> > 
            <span>Sacred Cart</span>
        </div>
    </div>

    <div class="cart-container">
        <div class="container">
            <h1 class="cart-title">Your Sacred Cart</h1>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p>Discover mystical artifacts and ancient knowledge in our collection.</p>
                    <a href="../products.php" class="btn btn-primary">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <div class="mystical-placeholder"></div>
                                </div>
                                <div class="item-details">
                                    <h3><a href="../product.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></h3>
                                    <p class="item-seller">By: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                                    <p class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                    <p class="item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                                    <?php if ($item['stock'] < $item['quantity']): ?>
                                        <p class="stock-warning">⚠️ Only <?php echo $item['stock']; ?> in stock</p>
                                    <?php endif; ?>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <label>Quantity:</label>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>" onchange="this.form.submit()">
                                    </form>
                                    <form method="POST" class="remove-form">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small" 
                                                onclick="return confirm('Remove this item from cart?')">Remove</button>
                                    </form>
                                </div>
                                <div class="item-total">
                                    <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            <div class="summary-line">
                                <span>Items (<?php echo count($cart_items); ?>)</span>
                                <span>$<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                            <div class="summary-total">
                                <strong>Total: $<?php echo number_format($total_amount, 2); ?></strong>
                            </div>
                            <div class="checkout-actions">
                                <a href="checkout.php" class="btn btn-primary btn-large">Proceed to Checkout</a>
                                <a href="../products.php" class="btn btn-secondary">Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

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

    <script src="../assets/js/script.js"></script>
    
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
        
        .cart-container {
            min-height: 80vh;
            padding: 3rem 0;
        }
        
        .cart-title {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .empty-cart {
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .empty-cart h2 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .empty-cart p {
            color: #9D9999;
            margin-bottom: 2rem;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            align-items: start;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: 1rem;
            align-items: center;
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #333031, #484949);
            border-radius: 5px;
        }
        
        .mystical-placeholder {
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(157, 153, 153, 0.3) 0%, transparent 70%);
            border-radius: 5px;
        }
        
        .item-details h3 {
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .item-details h3 a {
            color: #D8D4D3;
            text-decoration: none;
        }
        
        .item-details h3 a:hover {
            color: #9D9999;
        }
        
        .item-details p {
            color: #9D9999;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: bold;
            color: #D8D4D3 !important;
        }
        
        .stock-warning {
            color: #ff6b6b !important;
            font-weight: bold;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .quantity-form label {
            color: #D8D4D3;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .quantity-form input {
            width: 60px;
            padding: 0.25rem;
            border: 1px solid rgba(157, 153, 153, 0.3);
            background: rgba(216, 212, 211, 0.1);
            color: #D8D4D3;
            border-radius: 3px;
        }
        
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .btn-danger {
            background: #ff6b6b;
            border-color: #ff6b6b;
        }
        
        .btn-danger:hover {
            background: #ff5252;
        }
        
        .item-total {
            text-align: right;
            color: #D8D4D3;
            font-size: 1.2rem;
        }
        
        .summary-card {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
            position: sticky;
            top: 2rem;
        }
        
        .summary-card h3 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1.5rem;
        }
        
        .summary-line {
            display: flex;
            justify-content: space-between;
            color: #9D9999;
            margin-bottom: 1rem;
        }
        
        .summary-total {
            border-top: 1px solid rgba(157, 153, 153, 0.2);
            padding-top: 1rem;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            color: #D8D4D3;
        }
        
        .checkout-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-large {
            padding: 1rem;
            font-size: 1.1rem;
        }
        
        .error-message {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .item-actions {
                flex-direction: row;
                justify-content: center;
            }
            
            .summary-card {
                position: static;
            }
        }
    </style>
</body>
</html>
