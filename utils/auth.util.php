<?php

require_once BASE_PATH . '/bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

class Auth
{
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($username, $password) {
        self::init();
        
        try {
            // Connect to database using the private method
            $pdo = self::connectDatabase();

            // Check if username exists
            $stmt = $pdo->prepare("SELECT id, username, password, first_name, last_name, role FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && hash_equals((string)$user['password'], (string)$password)) {
                // Store user data in session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role']
                ];
                return ['success' => true, 'message' => 'Login successful'];
            } else {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    public static function user() {
        self::init();
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public static function check() {
        self::init();
        return isset($_SESSION['user']);
    }

    public static function logout() {
        self::init();
        
        // Clear session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    private static function connectDatabase() {
        // Load database configuration
        require_once UTILS_PATH . '/envSetter.util.php';
        
        $dbConfig = getDatabaseConfig();
        $host = $dbConfig['pgHost'];
        $port = $dbConfig['pgPort'];
        $dbname = $dbConfig['pgDB'];
        $username = $dbConfig['pgUser'];
        $password = $dbConfig['pgPassword'];

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
}
