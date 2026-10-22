<?php
// ============================================================
// includes/layout.php  — Shared Header & Footer
// ============================================================

function renderHeader(string $pageTitle = ''): void {
    $title = $pageTitle ? $pageTitle . ' — ' . APP_NAME : APP_NAME;
    $user  = $_SESSION['full_name'] ?? 'User';
    $role  = ucfirst($_SESSION['role'] ?? '');

    // Unread count for initial render (avoids flicker on load)
    $unread = 0;
    if (!empty($_SESSION['user_id'])) {
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$_SESSION['user_id']]);
            $unread = (int) $stmt->fetchColumn();
        } catch (Exception $e) { /* notifications table may not exist yet */ }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
    .notif-wrapper { position: relative; }
    .notif-bell {
        background: none; border: none; cursor: pointer;
        font-size: 20px; padding: 4px 8px; border-radius: 8px;
        position: relative; line-height: 1; transition: background .15s;
    }
    .notif-bell:hover { background: #f1f5f9; }
    .notif-badge {
        position: absolute; top: -2px; right: 0;
        background: #dc2626; color: #fff;
        font-size: 10px; font-weight: 700;
        min-width: 18px; height: 18px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        padding: 0 4px; pointer-events: none; transition: transform .2s;
    }
    .notif-badge.hidden { display: none; }
    .notif-badge.bump   { transform: scale(1.4); }
    .notif-dropdown {
        display: none; position: absolute; top: calc(100% + 10px); right: 0;
        width: 340px; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
        z-index: 500; overflow: hidden;
    }
    .notif-dropdown.open { display: block; }
    .notif-header {
        padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .notif-header span { font-weight: 600; font-size: 14px; }
    .notif-mark-all {
        font-size: 11px; color: #2563eb; background: none;
        border: none; cursor: pointer; padding: 0;
    }
    .notif-mark-all:hover { text-decoration: underline; }
    .notif-list { max-height: 360px; overflow-y: auto; }
    .notif-item {
        display: flex; gap: 10px; padding: 12px 16px;
        border-bottom: 1px solid #f8fafc; transition: background .1s;
    }
    .notif-item:hover { background: #f8fafc; }
    .notif-item:last-child { border-bottom: none; }
    .notif-unread { background: #eff6ff; }
    .notif-unread:hover { background: #dbeafe; }
    .notif-icon { font-size: 20px; flex-shrink: 0; margin-top: 2px; }
    .notif-body { flex: 1; min-width: 0; }
    .notif-title  { font-weight: 600; font-size: 13px; color: #1e293b; }
    .notif-msg    { font-size: 12px; color: #475569; margin-top: 2px; line-height: 1.4; }
    .notif-patient{ font-size: 11px; color: #2563eb; margin-top: 3px; display: block; }
    .notif-time   { font-size: 11px; color: #94a3b8; margin-top: 4px; }
    .notif-empty  { padding: 32px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
    .notif-footer { padding: 10px 16px; border-top: 1px solid #f1f5f9; text-align: center; }
    .notif-footer a { font-size: 12px; color: #2563eb; text-decoration: none; }
    .notif-footer a:hover { text-decoration: underline; }
    </style>

    <!-- Modal JS inlined in <head> so buttons always work -->
    <script>
    function openModal(id) {
        var m = document.getElementById(id);
        if (m) m.classList.add('open');
        else console.error('Modal not found: ' + id);
    }
    function closeModal(id) {
        var m = document.getElementById(id);
        if (m) m.classList.remove('open');
    }
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop'))
            e.target.classList.remove('open');
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape')
            document.querySelectorAll('.modal-backdrop.open')
                    .forEach(function(m){ m.classList.remove('open'); });
    });
    </script>
</head>
<body>
<nav class="navbar">
    <a href="<?= BASE_URL ?>pages/dashboard.php" class="nav-brand">
        <span class="brand-icon">❤</span>
        <span class="brand-name"><?= APP_NAME ?></span>
    </a>
    <div class="nav-links">
        <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>pages/dashboard.php"   class="nav-link">Dashboard</a>
            <a href="<?= BASE_URL ?>pages/patients.php"    class="nav-link">Patients</a>
            <a href="<?= BASE_URL ?>pages/vitals.php"      class="nav-link">Vitals</a>
            <a href="<?= BASE_URL ?>pages/medications.php" class="nav-link">Medications</a>
            <a href="<?= BASE_URL ?>pages/incidents.php"   class="nav-link">Incidents</a>
            <?php if (currentRole() === 'admin'): ?>
            <a href="<?= BASE_URL ?>pages/users.php"       class="nav-link">Users</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (isLoggedIn()): ?>
    <div class="nav-user">
        <!-- Notification Bell -->
        <div class="notif-wrapper" id="notifWrapper">
            <button class="notif-bell" id="notifBell" title="Notifications"
                    onclick="toggleNotifDropdown()">
                🔔
                <span class="notif-badge <?= $unread === 0 ? 'hidden' : '' ?>" id="notifBadge">
                    <?= $unread > 99 ? '99+' : $unread ?>
                </span>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <button class="notif-mark-all" onclick="markAllRead()">Mark all read</button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Loading…</div>
                </div>
                <div class="notif-footer">
                    <a href="<?= BASE_URL ?>pages/notifications.php">View all notifications →</a>
                </div>
            </div>
        </div>

        <span class="user-badge <?= currentRole() ?>"><?= $role ?></span>
        <span class="user-name"><?= htmlspecialchars($user) ?></span>
        <a href="<?= BASE_URL ?>api/auth.php?action=logout" class="btn btn-sm btn-outline">Logout</a>
    </div>
    <?php endif; ?>
</nav>

<main class="main-content">
<?php
    $flash = getFlash();
    if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> flash-msg">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif;
}

function renderFooter(): void { ?>
</main>
<footer class="footer">
    <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; Patient Monitoring System</p>
</footer>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
// ── Notification Bell Logic ───────────────────────────────
var notifOpen = false;
var csrfToken = '<?= csrfToken() ?>';
var baseUrl   = '<?= BASE_URL ?>';

function toggleNotifDropdown() {
    notifOpen = !notifOpen;
    var dd = document.getElementById('notifDropdown');
    if (notifOpen) {
        dd.classList.add('open');
        loadNotifications();
    } else {
        dd.classList.remove('open');
    }
}

function loadNotifications() {
    fetch(baseUrl + 'api/notifications.php?action=list')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('notifList').innerHTML = data.html || '';
            updateBadge(0);
        })
        .catch(function() {});
}

function markAllRead() {
    fetch(baseUrl + 'api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=mark_all_read&csrf_token=' + encodeURIComponent(csrfToken)
    }).then(function() {
        updateBadge(0);
        loadNotifications();
    });
}

function updateBadge(count) {
    var badge = document.getElementById('notifBadge');
    if (!badge) return;
    if (count <= 0) {
        badge.classList.add('hidden');
    } else {
        badge.classList.remove('hidden');
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.add('bump');
        setTimeout(function() { badge.classList.remove('bump'); }, 300);
    }
}

// Poll every 30 seconds
function pollNotifications() {
    fetch(baseUrl + 'api/notifications.php?action=count')
        .then(function(r) { return r.json(); })
        .then(function(data) { updateBadge(data.count || 0); })
        .catch(function() {});
}

// Close when clicking outside
document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('notifWrapper');
    if (wrapper && !wrapper.contains(e.target) && notifOpen) {
        notifOpen = false;
        document.getElementById('notifDropdown').classList.remove('open');
    }
});

window.addEventListener('load', function() {
    pollNotifications();
    setInterval(pollNotifications, 30000);
});
</script>
</body>
</html>
<?php }