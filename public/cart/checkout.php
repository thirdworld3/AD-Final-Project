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
$success = false;

// Get cart items
try {
    $pdo = get_db_connection();
    
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.id, p.title, p.price, p.stock, 
               u.fullname as seller_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN users u ON p.seller_id = u.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        header('Location: index.php');
        exit;
    }
    
    // Calculate total and check stock
    foreach ($cart_items as $item) {
        if ($item['stock'] < $item['quantity']) {
            $error = "Some items in your cart are no longer available in the requested quantity.";
            break;
        }
        $total_amount += $item['price'] * $item['quantity'];
    }
    
} catch (Exception $e) {
    $error = "Failed to load cart items.";
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $payment_method = $_POST['payment_method'] ?? '';
    $billing_address = trim($_POST['billing_address'] ?? '');
    $billing_city = trim($_POST['billing_city'] ?? '');
    $billing_zip = trim($_POST['billing_zip'] ?? '');
    
    $errors = [];
    
    if (!in_array($payment_method, ['credit_card', 'paypal', 'crypto'])) {
        $errors[] = 'Please select a payment method';
    }
    
    if (!$billing_address) $errors[] = 'Billing address is required';
    if (!$billing_city) $errors[] = 'City is required';
    if (!$billing_zip) $errors[] = 'ZIP code is required';
    
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (buyer_id, total_amount, payment_status) 
                VALUES (?, ?, 'paid')
            ");
            $stmt->execute([$user['id'], $total_amount]);
            $order_id = $pdo->lastInsertId();
            
            // Create order items and update stock
            foreach ($cart_items as $item) {
                // Add order item
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            $pdo->commit();
            $success = true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Checkout failed. Please try again.";
        }
    } else {
        $error = implode(', ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sacred Checkout • The Forbidden Codex</title>
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

    <div class="checkout-container">
        <div class="container">
            <?php if ($success): ?>
                <div class="checkout-success">
                    <div class="success-icon">✓</div>
                    <h1>Order Complete!</h1>
                    <p>Your sacred transaction has been completed. The ancient knowledge is now yours.</p>
                    <div class="success-actions">
                        <a href="../products.php" class="btn btn-primary">Continue Shopping</a>
                        <a href="../account/orders.php" class="btn btn-secondary">View Orders</a>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="checkout-error">
                    <h1>Checkout Failed</h1>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <a href="index.php" class="btn btn-primary">Back to Cart</a>
                </div>
            <?php else: ?>
                <div class="checkout-form-container">
                    <h1 class="checkout-title">Complete Your Sacred Transaction</h1>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p>By: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-price">
                                    <span class="unit-price">$<?php echo number_format($item['price'], 2); ?> each</span>
                                    <span class="total-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="order-total">
                            <strong>Total: $<?php echo number_format($total_amount, 2); ?></strong>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form method="POST" class="checkout-form">
                        <div class="form-section">
                            <h3>Payment Method</h3>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="credit_card" required>
                                    <span class="payment-label">Credit Card</span>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="paypal" required>
                                    <span class="payment-label">PayPal</span>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="crypto" required>
                                    <span class="payment-label">Cryptocurrency</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Billing Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['fullname'] ?? $user['name'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                                <div class="form-group full-width">
                                    <label>Address</label>
                                    <input type="text" name="billing_address" required placeholder="Enter your billing address">
                                </div>
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="billing_city" required placeholder="City">
                                </div>
                                <div class="form-group">
                                    <label>ZIP Code</label>
                                    <input type="text" name="billing_zip" required placeholder="ZIP">
                                </div>
                            </div>
                        </div>

                        <div class="checkout-actions">
                            <button type="submit" class="btn btn-primary btn-large">Complete Order</button>
                            <a href="index.php" class="btn btn-secondary">Back to Cart</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    
    <style>
        .logo-text {
            font-family: 'Cinzel', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #9D9999;
            text-shadow: 0 0 10px rgba(157, 153, 153, 0.3);
            margin: 0;
        }

        .checkout-container {
            min-height: 80vh;
            padding: 3rem 0;
        }
        
        .checkout-title {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .checkout-form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .order-summary {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .order-summary h2 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1.5rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: start;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(157, 153, 153, 0.2);
        }
        
        .item-details h3 {
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .item-details p {
            color: #9D9999;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            text-align: right;
        }
        
        .unit-price {
            display: block;
            color: #9D9999;
            font-size: 0.9rem;
        }
        
        .total-price {
            display: block;
            color: #D8D4D3;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .order-total {
            text-align: right;
            padding: 1rem 0;
            font-size: 1.5rem;
            color: #D8D4D3;
        }
        
        .checkout-form {
            background: rgba(28, 26, 27, 0.8);
            border: 1px solid rgba(157, 153, 153, 0.2);
            border-radius: 10px;
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            font-family: 'Cinzel', serif;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .payment-methods {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .payment-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: rgba(216, 212, 211, 0.1);
            border: 1px solid rgba(157, 153, 153, 0.3);
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .payment-option:hover {
            background: rgba(216, 212, 211, 0.2);
        }
        
        .payment-option input[type="radio"] {
            margin: 0;
        }
        
        .payment-label {
            color: #D8D4D3;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            color: #D8D4D3;
            margin-bottom: 0.5rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(157, 153, 153, 0.3);
            background: rgba(216, 212, 211, 0.1);
            color: #D8D4D3;
            border-radius: 5px;
        }
        
        .form-group input:read-only {
            background: rgba(157, 153, 153, 0.1);
            color: #9D9999;
        }
        
        .checkout-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }
        
        .checkout-success {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 1rem;
        }
        
        .checkout-success h1 {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            margin-bottom: 1rem;
        }
        
        .checkout-success p {
            color: #9D9999;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .checkout-error {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }
        
        .checkout-error h1 {
            font-family: 'Cinzel', serif;
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
        
        .checkout-error p {
            color: #9D9999;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                flex-direction: column;
            }
            
            .order-item {
                flex-direction: column;
                gap: 1rem;
            }
            
            .item-price {
                text-align: left;
            }
            
            .checkout-actions {
                flex-direction: column;
            }
            
            .success-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</body>
</html>
