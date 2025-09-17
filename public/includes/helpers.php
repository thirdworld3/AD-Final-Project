<?php
// helpers.php
function e($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }


function flash_set($key, $msg) {
ensure_session_started();
$_SESSION['flash'][$key] = $msg;
}
function flash_get($key) {
ensure_session_started();
$val = $_SESSION['flash'][$key] ?? null;
if (isset($_SESSION['flash'][$key])) unset($_SESSION['flash'][$key]);
return $val;
}


function is_logged_in() {
ensure_session_started();
return !empty($_SESSION['user']);
}


function current_user() {
ensure_session_started();
return $_SESSION['user'] ?? null;
}


function require_login() {
if (!is_logged_in()) {
header('Location: login.php');
exit;
}
}


function csrf_token() {
if (!session_id()) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
return $_SESSION['csrf'];
}


function validate_csrf($token) {
if (!session_id()) session_start();
return hash_equals($_SESSION['csrf'] ?? '', $token ?? '');
}


// Database helper functions
require_once __DIR__ . '/../../config.php';

// ensure_session_started() is already defined in config.php

function set_flash_message($message, $type = 'info') {
    ensure_session_started();
    $_SESSION['flash_messages'][] = ['message' => $message, 'type' => $type];
}

function get_flash_messages() {
    ensure_session_started();
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function display_flash_messages() {
    $messages = get_flash_messages();
    foreach ($messages as $flash) {
        $class = $flash['type'] === 'error' ? 'auth-alert' : 'auth-success';
        echo '<div class="' . $class . '"><p>' . htmlspecialchars($flash['message']) . '</p></div>';
    }
}

function find_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function create_user($pdo, $name, $email, $password, $role = 'buyer') {
    // Use email local part as username; ensure uniqueness fallback
    $baseUsername = strtolower(str_replace('@', '_', explode('@', $email)[0]));
    $username = $baseUsername;

    // Generate a unique username if needed
    $suffix = 1;
    while (true) {
        $check = $pdo->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
        $check->execute([$username]);
        if (!$check->fetchColumn()) break;
        $username = $baseUsername . $suffix++;
    }

    $stmt = $pdo->prepare('INSERT INTO users (fullname, email, password, username, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$name, $email, $password, $username, $role]);
    return $pdo->lastInsertId();
}

function login_user($user) {
    ensure_session_started();
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
}

function verify_login($pdo, $email, $password) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $stored = (string)($user['password'] ?? '');
        if (hash_equals($stored, (string)$password)) {
            unset($user['password']);
            return $user;
        }
    }
    return false;
}

function logout_user() {
    ensure_session_started();
    session_unset();
    session_destroy();
}