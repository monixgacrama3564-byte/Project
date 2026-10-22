<?php
// ============================================================
// api/notifications.php — Notification Actions & Polling
// ============================================================
require_once __DIR__ . '/../includes/config.php';       // 1. config first
require_once __DIR__ . '/../includes/auth.php';          // 2. auth second
require_once __DIR__ . '/../includes/notifications.php'; // 3. notifications last

requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── AJAX: unread count (polled every 30s) ─────────────────
if ($action === 'count') {
    header('Content-Type: application/json');
    echo json_encode(['count' => getUnreadCount()]);
    exit;
}

// ── AJAX: list recent notifications ──────────────────────
if ($action === 'list') {
    markAllRead();
    $items = getRecentNotifications();
    header('Content-Type: application/json');

    $html = '';
    if (empty($items)) {
        $html = '<div class="notif-empty">No notifications yet 🎉</div>';
    } else {
        foreach ($items as $n) {
            $icon    = match($n['type']) {
                'vital_alert'       => '⚠️',
                'missed_medication' => '💊',
                default             => '🔔',
            };
            $readCls = $n['is_read'] ? '' : ' notif-unread';
            $patient = $n['patient_name']
                ? '<span class="notif-patient">' . htmlspecialchars($n['patient_code'] . ' — ' . $n['patient_name']) . '</span>'
                : '';
            $time    = timeAgo($n['created_at']);
            $html   .= '
            <div class="notif-item' . $readCls . '">
                <div class="notif-icon">' . $icon . '</div>
                <div class="notif-body">
                    <div class="notif-title">' . htmlspecialchars($n['title'])   . '</div>
                    <div class="notif-msg">'   . htmlspecialchars($n['message']) . '</div>
                    ' . $patient . '
                    <div class="notif-time">'  . $time . '</div>
                </div>
            </div>';
        }
    }

    echo json_encode(['html' => $html]);
    exit;
}

// ── POST: mark all read ───────────────────────────────────
if ($action === 'mark_all_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    markAllRead();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);

// ── Helper ────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff / 60)   . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600)  . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day ago';
    return date('M d', strtotime($datetime));
}