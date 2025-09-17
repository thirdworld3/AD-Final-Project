<?php
// Enable error reporting for debugging during signup
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering to avoid header issues
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/helpers.php';
ensure_session_started();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$confirm = $_POST['confirm_password'] ?? '';

	if ($name === '') $errors[] = 'Name is required';
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
	if (strlen($password) < 3) $errors[] = 'Password must be at least 3 characters';
	if ($password !== $confirm) $errors[] = 'Passwords do not match';

	if (!$errors) {
		try {
			$pdo = get_db_connection();
			if (find_user_by_email($pdo, $email)) {
				$errors[] = 'An account with this email already exists';
			} else {
				$userId = create_user($pdo, $name, $email, $password, 'buyer');
				// Redirect to login with a success message and pre-fill username/email
				$redirect = base_url('login.php') . '?message=' . urlencode('Account created. Please log in.') . '&username=' . urlencode($email);
				ob_clean();
				header('Location: ' . $redirect);
				exit;
			}
		} catch (Throwable $e) {
			$errors[] = 'Registration failed. Please try again later.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up • The Forbidden Codex</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Join the Codex</h1>
            <?php if ($errors): ?>
            <div class="auth-alert">
                <?php foreach ($errors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Sign Up</button>
                <p class="auth-switch">Already sworn the oath? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
 </body>
 </html>


