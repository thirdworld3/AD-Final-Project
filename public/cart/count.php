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
    echo json_encode(['count' => 0]);
    exit;
}

$user = current_user();

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
    
    // Get total items in cart for this user
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch();
    
    $count = $result['total'] ? intval($result['total']) : 0;
    
    ob_clean();
    echo json_encode(['count' => $count]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['count' => 0]);
}
?>
