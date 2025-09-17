<?php
// Simple cart add without JSON - returns plain text
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to prevent any unwanted output
ob_start();

try {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../includes/helpers.php';
} catch (Exception $e) {
    ob_clean();
    echo "CONFIG_ERROR: " . $e->getMessage();
    exit;
}

// Use the safe session function from config
ensure_session_started();

// Clean any previous output
ob_clean();

// Check if user is logged in
if (!is_logged_in()) {
    echo "LOGIN_REQUIRED";
    exit;
}

$user = current_user();
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));

if (!$product_id) {
    echo "INVALID_PRODUCT";
    exit;
}

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
    
    // Check if product exists and has sufficient stock
    $stmt = $pdo->prepare("SELECT id, title, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo "PRODUCT_NOT_FOUND";
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        echo "INSUFFICIENT_STOCK";
        exit;
    }
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user['id'], $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing cart item
        $new_quantity = $existing['quantity'] + $quantity;
        
        if ($new_quantity > $product['stock']) {
            echo "EXCEEDS_STOCK";
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $user['id'], $product_id]);
    } else {
        // Add new cart item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $product_id, $quantity]);
    }
    
    echo "SUCCESS";
    
} catch (Exception $e) {
    // Log the actual error for debugging
    error_log("Cart add error: " . $e->getMessage());
    echo "ERROR: " . $e->getMessage();
}
?>
