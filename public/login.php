<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/helpers.php';

ensure_session_started();

$errors = [];
$message = '';

// Check for error or success messages from URL parameters
if (isset($_GET['error'])) {
    $errors[] = $_GET['error'];
}
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In • The Forbidden Codex</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Enter the Codex</h1>
            <?php if ($errors): ?>
            <div class="auth-alert">
                <?php foreach ($errors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($message): ?>
            <div class="auth-success">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
            <?php endif; ?>
            <form method="POST" class="auth-form" action="../handlers/auth.handler.php">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_GET['username'] ?? ''); ?>" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-full">Log In</button>
                <p class="auth-switch">New to the order? <a href="signup.php">Sign up</a></p>
            </form>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
 </body>
 </html>


