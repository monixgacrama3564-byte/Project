<?php
// ============================================================
// pages/users.php  — M1: User Management (Admin Only)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireAdmin();

$db = getDB();

$users = $db->query("
    SELECT id, full_name, email, role, is_active, created_at
    FROM users
    ORDER BY role ASC, full_name ASC
")->fetchAll();

$totalAdmins    = $db->query("SELECT COUNT(*) FROM users WHERE role='admin'    AND is_active=1")->fetchColumn();
$totalProviders = $db->query("SELECT COUNT(*) FROM users WHERE role='provider' AND is_active=1")->fetchColumn();
$totalInactive  = $db->query("SELECT COUNT(*) FROM users WHERE is_active=0")->fetchColumn();

renderHeader('User Management');
?>

<div class="page-header">
    <div>
        <div class="page-title">User Management</div>
        <div class="page-subtitle">Manage system accounts and roles (M1)</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('addUserModal')">+ Add User</button>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card warning">
        <div class="stat-label">Admins</div>
        <div class="stat-value"><?= $totalAdmins ?></div>
        <div class="stat-sub">Active admin accounts</div>
    </div>
    <div class="stat-card info">
        <div class="stat-label">Providers</div>
        <div class="stat-value"><?= $totalProviders ?></div>
        <div class="stat-sub">Active provider accounts</div>
    </div>
    <div class="stat-card <?= $totalInactive > 0 ? 'danger' : 'success' ?>">
        <div class="stat-label">Inactive</div>
        <div class="stat-value"><?= $totalInactive ?></div>
        <div class="stat-sub">Deactivated accounts</div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">System Users</span>
        <span class="text-muted" style="font-size:12px;"><?= count($users) ?> total</span>
    </div>
    <div class="table-wrapper">
        <?php if (empty($users)): ?>
            <div class="empty-state">No users found.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="user-badge <?= $u['role'] ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-muted">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><small><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
                    <td>
                        <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="POST" action="<?= BASE_URL ?>api/users.php" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
                            <?php if ($u['is_active']): ?>
                                <input type="hidden" name="action" value="deactivate">
                                <button type="submit" class="btn btn-xs btn-danger"
                                        onclick="return confirm('Deactivate this user?')">
                                    Deactivate
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn btn-xs btn-success"
                                        onclick="return confirm('Reactivate this user?')">
                                    Activate
                                </button>
                            <?php endif; ?>
                        </form>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:11px;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal-backdrop" id="addUserModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add New User</span>
            <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="margin-bottom:16px;">
                Password must be at least <strong>8 characters</strong>, include an <strong>uppercase letter</strong> and a <strong>number</strong>.
            </div>
            <form method="POST" action="<?= BASE_URL ?>api/auth.php?action=register">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control"
                               placeholder="e.g. Dr. Maria Santos" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="provider">Provider</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="user@vitalsmed.com" required maxlength="150">
                </div>
                <div class="form-grid" style="margin-top:16px;">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Min 8 chars, 1 uppercase, 1 number" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirm" class="form-control"
                               placeholder="Repeat password" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php renderFooter(); ?>