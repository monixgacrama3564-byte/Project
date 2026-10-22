<?php
// ============================================================
// includes/notifications.php — Notification Helpers
// IMPORTANT: Always require config.php and auth.php BEFORE
// requiring this file.
// ============================================================

if (!function_exists('getDB')) {
    die('notifications.php requires config.php to be loaded first.');
}

/**
 * Create a notification broadcast to ALL active users,
 * or to a specific user if $userId is provided.
 */
function createNotification(
    string $type,
    string $title,
    string $message,
    ?int   $patientId = null,
    ?int   $refId     = null,
    ?int   $userId    = null
): void {
    try {
        $db = getDB();

        if ($userId !== null) {
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, title, message, patient_id, ref_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $type, $title, $message, $patientId, $refId]);
        } else {
            // Broadcast to every active user
            $users = $db->query("SELECT id FROM users WHERE is_active = 1")->fetchAll();
            $stmt  = $db->prepare("
                INSERT INTO notifications (user_id, type, title, message, patient_id, ref_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($users as $u) {
                $stmt->execute([$u['id'], $type, $title, $message, $patientId, $refId]);
            }
        }
    } catch (Exception $e) {
        error_log('createNotification error: ' . $e->getMessage());
    }
}

/**
 * Get unread notification count for the logged-in user.
 */
function getUnreadCount(): int {
    if (empty($_SESSION['user_id'])) return 0;
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get recent notifications for the logged-in user (last 20).
 */
function getRecentNotifications(): array {
    if (empty($_SESSION['user_id'])) return [];
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT n.*, p.full_name AS patient_name, p.patient_code
            FROM notifications n
            LEFT JOIN patients p ON p.id = n.patient_id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Mark all unread notifications as read for the logged-in user.
 */
function markAllRead(): void {
    if (empty($_SESSION['user_id'])) return;
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (Exception $e) {
        error_log('markAllRead error: ' . $e->getMessage());
    }
}