<?php
// ============================================================
// index.php  — Login Page
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

$errors  = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);
$timeout = isset($_GET['timeout']);
$msg     = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Login</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand">
            <div class="icon">❤️</div>
            <h1><?= APP_NAME ?></h1>
            <p>Patient Monitoring System</p>
        </div>

        <?php if ($timeout): ?>
            <div class="alert alert-warning">Your session expired. Please log in again.</div>
        <?php endif; ?>

        <?php if ($msg === 'logged_out'): ?>
            <div class="alert alert-info">You have been logged out successfully.</div>
        <?php endif; ?>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="<?= BASE_URL ?>api/auth.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="you@example.com" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom:24px;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                Sign In
            </button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:12px;color:var(--text-muted);">
            Contact your administrator to create an account.
        </p>
    </div>
</div>
</body>
</html>