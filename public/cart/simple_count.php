<?php
// Simple cart count without JSON - returns plain number
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
    echo "0";
    exit;
}

// Use the safe session function from config
ensure_session_started();

// Clean any previous output
ob_clean();

// Check if user is logged in
if (!is_logged_in()) {
    echo "0";
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
    echo $count;
    
} catch (Exception $e) {
    echo "0";
}
?>
