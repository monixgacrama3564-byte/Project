<?php
// ============================================================
// api/users.php  — M1: User Management API (Admin Only)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$action = $_POST['action'] ?? '';
$db     = getDB();

// ── DEACTIVATE user ───────────────────────────────────────
if ($action === 'deactivate') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/users.php'); exit;
    }

    $userId = intval($_POST['user_id'] ?? 0);

    // Prevent self-deactivation
    if ($userId === (int)$_SESSION['user_id']) {
        setFlash('danger', 'You cannot deactivate your own account.');
        header('Location: ' . BASE_URL . 'pages/users.php'); exit;
    }

    if ($userId < 1) {
        setFlash('danger', 'Invalid user ID.');
        header('Location: ' . BASE_URL . 'pages/users.php'); exit;
    }

    $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")
       ->execute([$userId]);

    setFlash('success', 'User account deactivated.');
    header('Location: ' . BASE_URL . 'pages/users.php'); exit;
}

// ── ACTIVATE user ─────────────────────────────────────────
if ($action === 'activate') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/users.php'); exit;
    }

    $userId = intval($_POST['user_id'] ?? 0);

    if ($userId < 1) {
        setFlash('danger', 'Invalid user ID.');
        header('Location: ' . BASE_URL . 'pages/users.php'); exit;
    }

    $db->prepare("UPDATE users SET is_active = 1 WHERE id = ?")
       ->execute([$userId]);

    setFlash('success', 'User account reactivated.');
    header('Location: ' . BASE_URL . 'pages/users.php'); exit;
}

// ── Fallback ──────────────────────────────────────────────
setFlash('danger', 'Unknown action.');
header('Location: ' . BASE_URL . 'pages/users.php');