<?php
// Start output buffering to prevent header issues
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../public/includes/helpers.php';

ensure_session_started();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                if (isset($_POST['username']) && isset($_POST['password'])) {
                    $username = trim($_POST['username']);
                    $password = $_POST['password'];
                    
                    if (empty($username) || empty($password)) {
                        header('Location: ../public/login.php?error=' . urlencode('Username and password are required'));
                        exit;
                    }
                    
                    try {
                        $pdo = get_db_connection();
                        
                        // Try to find user by username or email
                        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
                        $stmt->execute([$username, $username]);
                        $user = $stmt->fetch();
                        
                        // Debug: Log the authentication attempt
                        error_log("Login attempt - Username: $username, User found: " . ($user ? 'YES' : 'NO'));
                        if ($user) {
                            error_log("Stored password: " . $user['password'] . ", Entered: $password");
                        }
                        
                        // Plain-text comparison only
                        $isValid = false;
                        if ($user) {
                            $stored = (string)($user['password'] ?? '');
                            $isValid = hash_equals($stored, (string)$password);
                        }

                        if ($user && $isValid) {
                            // Login successful - store user in session
                            login_user($user);
                            
                            // Clear output buffer and redirect based on role
                            ob_clean();
                            if ($user['role'] === 'admin') {
                                header('Location: ../public/admin/index.php');
                            } else {
                                header('Location: ../public/account/index.php');
                            }
                            exit;
                        } else {
                            ob_clean();
                            header('Location: ../public/login.php?error=' . urlencode('Invalid username or password'));
                            exit;
                        }
                    } catch (Exception $e) {
                        ob_clean();
                        header('Location: ../public/login.php?error=' . urlencode('Database error: ' . $e->getMessage()));
                        exit;
                    }
                } else {
                    ob_clean();
                    header('Location: ../public/login.php?error=' . urlencode('Missing login credentials'));
                    exit;
                }
                
            case 'logout':
                logout_user();
                ob_clean();
                header('Location: ../public/login.php?message=' . urlencode('Logged out successfully'));
                exit;
                
            default:
                ob_clean();
                header('Location: ../public/login.php?error=' . urlencode('Invalid action'));
                exit;
        }
    } else {
        ob_clean();
        header('Location: ../public/login.php?error=' . urlencode('No action specified'));
        exit;
    }
} else {
    // GET request - redirect to login
    ob_clean();
    header('Location: ../public/login.php');
    exit;
}
