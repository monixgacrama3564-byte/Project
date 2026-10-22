<?php
// ============================================================
// pages/patients.php  — M2: Patient Management System
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();

$db = getDB();

// Search / filter
$search = trim($_GET['search'] ?? '');

$where  = ['p.is_active = 1'];
$params = [];
if ($search) {
    $where[]  = '(p.full_name LIKE ? OR p.patient_code LIKE ? OR p.contact_number LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$whereSQL = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT p.*, u.full_name AS assigned_name
    FROM patients p
    LEFT JOIN users u ON u.id = p.assigned_to
    WHERE {$whereSQL}
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$patients = $stmt->fetchAll();

// Stats
$totalActive   = $db->query("SELECT COUNT(*) FROM patients WHERE is_active=1")->fetchColumn();
$totalInactive = $db->query("SELECT COUNT(*) FROM patients WHERE is_active=0")->fetchColumn();
$totalMale     = $db->query("SELECT COUNT(*) FROM patients WHERE is_active=1 AND gender='Male'")->fetchColumn();
$totalFemale   = $db->query("SELECT COUNT(*) FROM patients WHERE is_active=1 AND gender='Female'")->fetchColumn();

// Providers for assignment (edit modal)
$providers = $db->query("SELECT id, full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();

renderHeader('Patients');
?>

<div class="page-header">
    <div>
        <div class="page-title">Patients</div>
        <div class="page-subtitle">Manage patient records (M2)</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('addPatientModal')">+ Add Patient</button>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-label">Active Patients</div>
        <div class="stat-value"><?= $totalActive ?></div>
        <div class="stat-sub">Currently monitored</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Male</div>
        <div class="stat-value"><?= $totalMale ?></div>
        <div class="stat-sub">Active male patients</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Female</div>
        <div class="stat-value"><?= $totalFemale ?></div>
        <div class="stat-sub">Active female patients</div>
    </div>
    <div class="stat-card <?= $totalInactive > 0 ? 'danger' : 'success' ?>">
        <div class="stat-label">Inactive</div>
        <div class="stat-value"><?= $totalInactive ?></div>
        <div class="stat-sub">Deactivated records</div>
    </div>
</div>

<!-- Search -->
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex;gap:10px;align-items:center;">
            <input type="text" name="search" id="tableSearch" class="form-control"
                   style="max-width:300px;" placeholder="Search by name, code, or contact…"
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline">Search</button>
            <?php if ($search): ?>
                <a href="patients.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Patients Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Patient Records</span>
        <span class="text-muted" style="font-size:12px;"><?= count($patients) ?> records</span>
    </div>
    <div class="table-wrapper">
        <?php if (empty($patients)): ?>
            <div class="empty-state">No patients found<?= $search ? ' matching your search' : '' ?>.</div>
        <?php else: ?>
        <table class="searchable-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Full Name</th>
                    <th>DOB / Age</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Assigned To</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $p):
                    $dob = new DateTime($p['date_of_birth']);
                    $age = (int)(new DateTime())->diff($dob)->y;
                ?>
                <tr>
                    <td><span class="badge badge-info"><?= htmlspecialchars($p['patient_code']) ?></span></td>
                    <td>
                        <strong><?= htmlspecialchars($p['full_name']) ?></strong>
                        <?php if ($p['medical_history']): ?>
                            <br><small class="text-muted" style="font-size:11px;">
                                <?= htmlspecialchars(substr($p['medical_history'], 0, 60)) ?><?= strlen($p['medical_history']) > 60 ? '…' : '' ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($p['date_of_birth']) ?><br>
                        <small class="text-muted"><?= $age ?> yrs</small>
                    </td>
                    <td><?= htmlspecialchars($p['gender']) ?></td>
                    <td><?= $p['contact_number'] ? htmlspecialchars($p['contact_number']) : '<span class="text-muted">—</span>' ?></td>
                    <td><small><?= $p['assigned_name'] ? htmlspecialchars($p['assigned_name']) : '<span class="text-muted">Unassigned</span>' ?></small></td>
                    <td><small><?= date('M d, Y', strtotime($p['created_at'])) ?></small></td>
                    <td style="white-space:nowrap;">
                        <!-- Edit button -->
                        <button class="btn btn-xs btn-outline"
                                onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>

                        <?php if (currentRole() === 'admin'): ?>
                        <!-- Deactivate -->
                        <form method="POST" action="<?= BASE_URL ?>api/patients.php" style="display:inline;">
                            <input type="hidden" name="action"     value="delete">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="patient_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger"
                                    onclick="return confirm('Deactivate patient <?= htmlspecialchars(addslashes($p['full_name'])) ?>?')">
                                Deactivate
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ── Add Patient Modal ─────────────────────────────── -->
<div class="modal-backdrop" id="addPatientModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add New Patient</span>
            <button class="modal-close" onclick="closeModal('addPatientModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>api/patients.php">
                <input type="hidden" name="action"     value="add">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control"
                               placeholder="e.g. Juan dela Cruz" required minlength="3" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select…</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="form-control"
                               max="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control"
                               placeholder="e.g. 09171234567" maxlength="20">
                    </div>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"
                              placeholder="Street, City, Province…"></textarea>
                </div>

                <div class="form-group" style="margin-top:12px;">
                    <label class="form-label">Medical History / Notes</label>
                    <textarea name="medical_history" class="form-control" rows="3"
                              placeholder="Known conditions, allergies, past surgeries…"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Patient</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('addPatientModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Edit Patient Modal ────────────────────────────── -->
<div class="modal-backdrop" id="editPatientModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Patient</span>
            <button class="modal-close" onclick="closeModal('editPatientModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>api/patients.php" id="editPatientForm">
                <input type="hidden" name="action"     value="edit">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="patient_id" id="edit_patient_id">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control"
                               required minlength="3" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender *</label>
                        <select name="gender" id="edit_gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="date_of_birth" id="edit_dob" class="form-control"
                               max="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" id="edit_contact" class="form-control" maxlength="20">
                    </div>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group" style="margin-top:12px;">
                    <label class="form-label">Medical History / Notes</label>
                    <textarea name="medical_history" id="edit_medical_history" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('editPatientModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(p) {
    document.getElementById('edit_patient_id').value    = p.id;
    document.getElementById('edit_full_name').value     = p.full_name;
    document.getElementById('edit_gender').value        = p.gender;
    document.getElementById('edit_dob').value           = p.date_of_birth;
    document.getElementById('edit_contact').value       = p.contact_number  ?? '';
    document.getElementById('edit_address').value       = p.address         ?? '';
    document.getElementById('edit_medical_history').value = p.medical_history ?? '';
    openModal('editPatientModal');
}
</script>

<?php renderFooter(); ?>