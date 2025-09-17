<?php
// Basic configuration for database and app settings

// NOTE: Docker MySQL configuration - uses environment variables from compose.yml
$DB_HOST = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'mysql';
$DB_NAME = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'forbidden_codex';
$DB_USER = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root';
$DB_PASS = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : 'rootpass';
$DB_CHARSET = 'utf8mb4';

// Dynamically determine base URL (directory of the executing script)
// Ensures redirects like base_url('account/index.php') work regardless of folder name
$APP_BASE_URL = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

// Create PDO connection with sane defaults
function get_db_connection() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;

	$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	try {
		return new PDO($dsn, $DB_USER, $DB_PASS, $options);
	} catch (PDOException $e) {
		// Handle different types of database connection errors
		if (strpos($e->getMessage(), 'Unknown database') !== false) {
			die('<h2>Database Setup Required</h2>
				<p>The database "' . $DB_NAME . '" does not exist.</p>
				<p><a href="' . dirname($_SERVER['PHP_SELF']) . '/../../setup_database.php" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Click here to set up the database</a></p>');
		} elseif (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'SQLSTATE[HY000] [2002]') !== false) {
			die('<h2>Docker MySQL Not Running</h2>
				<p>Cannot connect to MySQL container: ' . $DB_HOST . '</p>
				<p><strong>To fix this, start Docker services:</strong></p>
				<ul style="text-align: left; margin: 20px;">
					<li>Run: <code>docker compose up -d</code></li>
					<li>Wait for MySQL container to fully start (may take 30-60 seconds)</li>
					<li>Check status: <code>docker compose ps</code></li>
				</ul>
				<p>Once Docker MySQL is running, <a href="javascript:location.reload()" style="color: #007cba;">refresh this page</a></p>');
		}
		// Re-throw other database errors
		throw $e;
	}
}

// Initialize session (safe to call multiple times)
function ensure_session_started() {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		// Only set cookie params if session hasn't been started yet
		if (session_status() === PHP_SESSION_NONE) {
			// Secure cookie flags; adjust for local dev over HTTP
			$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
			session_set_cookie_params(
				0,        // lifetime
				'/',      // path
				'',       // domain
				$secure,  // secure
				true      // httponly
			);
		}
		session_start();
	}
}

function base_url($path = '') {
	global $APP_BASE_URL;
	$prefix = rtrim($APP_BASE_URL, '/');
	$suffix = ltrim($path, '/');
	return $suffix ? ($prefix . '/' . $suffix) : $prefix;
}

// Session helper functions are defined in public/includes/helpers.php

?>


