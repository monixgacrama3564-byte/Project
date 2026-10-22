<?php
// ============================================================
// includes/auth.php  — Session Management & Auth Helpers
// ============================================================

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,          // set true on HTTPS
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ── Session timeout check ─────────────────────────────────
function checkSession(): void {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            header('Location: ' . BASE_URL . 'index.php?timeout=1');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

// ── Require login ─────────────────────────────────────────
function requireLogin(): void {
    checkSession();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
        exit;
    }
}

// ── Require admin role ────────────────────────────────────
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . BASE_URL . 'pages/dashboard.php?error=forbidden');
        exit;
    }
}

// ── Is logged in? ─────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

// ── Current user role ─────────────────────────────────────
function currentRole(): string {
    return $_SESSION['role'] ?? '';
}

// ── CSRF token helpers ────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Flash messages ────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}