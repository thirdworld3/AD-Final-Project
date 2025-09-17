<?php
// Disable error reporting to prevent output corruption
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Include config without any output
require_once __DIR__ . '/../../config.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!is_logged_in()) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit;
}

$user = current_user();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$product_id = intval($input['product_id']);
$quantity = max(1, intval($input['quantity']));

try {
    $pdo = get_db_connection();
    
    // First, create cart table if it doesn't exist
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
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
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
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Cannot add more items than available stock']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $user['id'], $product_id]);
    } else {
        // Add new cart item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $product_id, $quantity]);
    }
    
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
}
?>
