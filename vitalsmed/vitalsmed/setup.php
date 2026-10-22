<?php
// ============================================================
// setup.php  — First-Run Setup: Create Initial Admin Account
// DELETE THIS FILE after setup is complete.
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Block if already set up (at least one admin exists)
$db = getDB();
$adminCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

$done    = false;
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email']     ?? '');
    $pass  = $_POST['password']       ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($adminCount > 0)
        $errors[] = 'Setup already complete. An admin account already exists.';
    if (strlen($name) < 3)
        $errors[] = 'Full name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email address.';
    if (strlen($pass) < 8)
        $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $pass))
        $errors[] = 'Password must contain at least one uppercase letter.';
    if (!preg_match('/[0-9]/', $pass))
        $errors[] = 'Password must contain at least one number.';
    if ($pass !== $pass2)
        $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$name, $email, $hash]);
        $success = 'Admin account created! You can now log in. <strong>Delete setup.php now.</strong>';
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitalsMed — Setup</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card" style="max-width:480px;">
        <div class="login-brand">
            <div class="icon">⚙️</div>
            <h1>VitalsMed Setup</h1>
            <p>Create your initial administrator account</p>
        </div>

        <?php if ($adminCount > 0 && !$done): ?>
            <div class="alert alert-warning">
                ⚠ An admin account already exists. Setup is not needed.
                <a href="<?= BASE_URL ?>index.php">Go to Login →</a>
            </div>
        <?php endif; ?>

        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <a href="<?= BASE_URL ?>index.php" class="btn btn-primary" style="width:100%;justify-content:center;">
                Go to Login →
            </a>
        <?php elseif ($adminCount === 0): ?>
        <form method="POST">
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control"
                       placeholder="e.g. Dr. Admin User" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control"
                       placeholder="admin@vitalsmed.com" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Min 8 chars, 1 uppercase, 1 number" required>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="password_confirm" class="form-control"
                       placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                Create Admin Account
            </button>
        </form>
        <?php endif; ?>

        <p style="text-align:center;margin-top:20px;font-size:11px;color:var(--text-muted);">
            ⚠ Delete <code>setup.php</code> after creating your account.
        </p>
    </div>
</div>
</body>
</html>