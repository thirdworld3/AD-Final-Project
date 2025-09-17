<!-- header.php -->
<?php
require_once __DIR__ . '/helpers.php';
$config = require __DIR__ . '/../../config.php';
$base = $config['app']['base_url'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mini Shop</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="icon" type="image/x-icon" href="../favicon.ico">
</head>
<body>
<header class="site-header">
<div class="container">
<h1><a href="index.php">Mini Shop</a></h1>
<nav>
<a href="index.php">Home</a>
<a href="products.php">Products</a>
<?php if (is_logged_in()): ?>
<a href="account.php">Account</a>
<a href="logout.php">Logout</a>
<?php else: ?>
<a href="login.php">Login</a>
<a href="signup.php">Signup</a>
<?php endif; ?>
</nav>
</div>
</header>
<main class="container"></main>