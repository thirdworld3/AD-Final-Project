<?php
// Start output buffering to prevent header issues
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/helpers.php';

// Ensure session is started before logout
ensure_session_started();

// Logout the user
logout_user();

// Redirect to home page
header('Location: index.php');
exit;
?>
