<?php
// auth.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';


function signup($username, $email, $password, $fullname, $role='buyer') {
global $pdo;
$stmt = $pdo->prepare('INSERT INTO users (username,email,password,fullname,role) VALUES (?,?,?,?,?)');
try {
$stmt->execute([$username,$email,$password,$fullname,$role]);
return $pdo->lastInsertId();
} catch (PDOException $e) {
return false;
}
}


function login($identifier, $password) {
global $pdo;
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$identifier,$identifier]);
$user = $stmt->fetch();
    if ($user && hash_equals((string)($user['password'] ?? ''), (string)$password)) {
if (!session_id()) session_start();
unset($user['password']);
$_SESSION['user'] = $user;
return true;
}
return false;
}


function logout() {
if (!session_id()) session_start();
session_unset();
session_destroy();
}


function require_role($role) {
if (!is_logged_in()) { header('Location: login.php'); exit; }
$user = current_user();
if ($user['role'] !== $role && $user['role'] !== 'admin') {
http_response_code(403);
echo "Forbidden: You don't have permission.";
exit;
}
}