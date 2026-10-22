<?php
// ============================================================
// api/auth.php  — M1: Secure Access & RBAC
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Logout ────────────────────────────────────────────────
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php?msg=logged_out');
    exit;
}

// ── Register (admin only) ─────────────────────────────────
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/users.php');
        exit;
    }

    $errors = [];
    $name   = trim($_POST['full_name']   ?? '');
    $email  = trim($_POST['email']       ?? '');
    $pass   = $_POST['password']         ?? '';
    $pass2  = $_POST['password_confirm'] ?? '';
    $role   = $_POST['role']             ?? 'provider';

    if (strlen($name)  < 3)                         $errors[] = 'Full name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($pass)  < 8)                         $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $pass))              $errors[] = 'Password must contain an uppercase letter.';
    if (!preg_match('/[0-9]/', $pass))              $errors[] = 'Password must contain a number.';
    if ($pass !== $pass2)                           $errors[] = 'Passwords do not match.';
    if (!in_array($role, ['admin','provider']))     $errors[] = 'Invalid role selected.';

    if (empty($errors)) {
        $db     = getDB();
        $exists = $db->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$email]);
        if ($exists->fetch()) $errors[] = 'Email address is already registered.';
    }

    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/users.php');
        exit;
    }

    $db   = getDB();
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?,?,?,?)');
    $stmt->execute([$name, $email, $hash, $role]);

    setFlash('success', "User '{$name}' created successfully.");
    header('Location: ' . BASE_URL . 'pages/users.php');
    exit;
}

// ── Login ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$action) {
    $email  = trim($_POST['email']    ?? '');
    $pass   = $_POST['password']      ?? '';
    $csrf   = $_POST['csrf_token']    ?? '';
    $errors = [];

    if (!verifyCsrf($csrf))                         $errors[] = 'Security token mismatch. Please reload.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (empty($pass))                               $errors[] = 'Password is required.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['email']         = $user['email'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['last_activity'] = time();

            header('Location: ' . BASE_URL . 'pages/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }

    $_SESSION['login_errors'] = $errors;
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

header('Location: ' . BASE_URL . 'index.php');
exit;