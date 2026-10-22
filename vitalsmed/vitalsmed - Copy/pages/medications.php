<?php
// ============================================================
// pages/medications.php  — M4: Medication Compliance Tracker
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();

$db = getDB();

// Active schedules
$schedules = $db->query("
    SELECT ms.*, p.full_name, p.patient_code
    FROM medication_schedules ms
    JOIN patients p ON p.id = ms.patient_id
    WHERE ms.is_active = 1
    ORDER BY ms.scheduled_time, p.full_name
")->fetchAll();

// Recent adherence log
$adherence = $db->query("
    SELECT ma.*, p.full_name, p.patient_code, ms.medication_name, ms.dosage, u.full_name AS confirmed_by_name
    FROM medication_adherence ma
    JOIN patients p ON p.id = ma.patient_id
    JOIN medication_schedules ms ON ms.id = ma.schedule_id
    JOIN users u ON u.id = ma.confirmed_by
    ORDER BY ma.confirmed_at DESC
    LIMIT 50
")->fetchAll();

// Active patients
$patients = $db->query("SELECT id, patient_code, full_name FROM patients WHERE is_active=1 ORDER BY full_name")->fetchAll();

// Adherence stats today
$takenToday  = $db->query("SELECT COUNT(*) FROM medication_adherence WHERE status='taken'  AND DATE(confirmed_at)=CURDATE()")->fetchColumn();
$lateToday   = $db->query("SELECT COUNT(*) FROM medication_adherence WHERE status='late'   AND DATE(confirmed_at)=CURDATE()")->fetchColumn();
$missedToday = $db->query("SELECT COUNT(*) FROM medication_adherence WHERE status='missed' AND DATE(confirmed_at)=CURDATE()")->fetchColumn();

renderHeader('Medications');
?>

<div class="page-header">
    <div>
        <div class="page-title">Medications</div>
        <div class="page-subtitle">Schedules and adherence tracking (M4)</div>
    </div>
    <div class="gap-2">
        <button class="btn btn-primary" onclick="openModal('addScheduleModal')">+ Add Schedule</button>
        <button class="btn btn-success" onclick="openModal('confirmMedModal')">✓ Confirm Dose</button>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-label">Active Schedules</div>
        <div class="stat-value"><?= count($schedules) ?></div>
        <div class="stat-sub">Current prescriptions</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Taken Today</div>
        <div class="stat-value"><?= $takenToday ?></div>
        <div class="stat-sub">On-time doses</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Late Today</div>
        <div class="stat-value"><?= $lateToday ?></div>
        <div class="stat-sub">Outside grace window</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">Missed Today</div>
        <div class="stat-value"><?= $missedToday ?></div>
        <div class="stat-sub">Not confirmed</div>
    </div>
</div>

<!-- Active Schedules -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <span class="card-title">Active Medication Schedules</span>
        <span class="text-muted" style="font-size:12px;">Grace window: ±<?= MED_GRACE_MINUTES ?> min</span>
    </div>
    <div class="table-wrapper">
        <?php if (empty($schedules)): ?>
            <div class="empty-state">No active schedules.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Scheduled Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $s): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($s['full_name']) ?></strong><br>
                        <small class="text-muted"><?= $s['patient_code'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($s['medication_name']) ?></td>
                    <td><?= htmlspecialchars($s['dosage']) ?></td>
                    <td><?= date('g:i A', strtotime($s['scheduled_time'])) ?></td>
                    <td>
                        <form method="POST" action="<?= BASE_URL ?>api/medications.php" style="display:inline;">
                            <input type="hidden" name="action"      value="deactivate">
                            <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
                            <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger"
                                    onclick="return confirm('Deactivate this schedule?')">Deactivate</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Adherence Log -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Adherence Log</span>
        <span class="text-muted" style="font-size:12px;">Last 50 records</span>
    </div>
    <div class="table-wrapper">
        <?php if (empty($adherence)): ?>
            <div class="empty-state">No adherence records yet.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Medication</th>
                    <th>Scheduled</th>
                    <th>Confirmed At</th>
                    <th>Status</th>
                    <th>Confirmed By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adherence as $a):
                    $cls = match($a['status']) {
                        'taken'  => 'badge-success',
                        'late'   => 'badge-warning',
                        'missed' => 'badge-danger',
                        default  => 'badge-muted'
                    };
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($a['full_name']) ?></strong><br>
                        <small class="text-muted"><?= $a['patient_code'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($a['medication_name']) ?><br><small><?= htmlspecialchars($a['dosage']) ?></small></td>
                    <td><small><?= date('M d, g:i A', strtotime($a['scheduled_datetime'])) ?></small></td>
                    <td><small><?= date('M d, g:i A', strtotime($a['confirmed_at'])) ?></small></td>
                    <td><span class="badge <?= $cls ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><small><?= htmlspecialchars($a['confirmed_by_name']) ?></small></td>
                    <td><small><?= $a['notes'] ? htmlspecialchars($a['notes']) : '—' ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal-backdrop" id="addScheduleModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add Medication Schedule</span>
            <button class="modal-close" onclick="closeModal('addScheduleModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>api/medications.php">
                <input type="hidden" name="action"     value="add_schedule">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Patient *</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">Select patient…</option>
                            <?php foreach ($patients as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['patient_code'] . ' — ' . $p['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Scheduled Time *</label>
                        <input type="time" name="scheduled_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Medication Name *</label>
                        <input type="text" name="medication_name" class="form-control"
                               placeholder="e.g. Metformin" required maxlength="150">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dosage *</label>
                        <input type="text" name="dosage" class="form-control"
                               placeholder="e.g. 500mg twice daily" required maxlength="100">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('addScheduleModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Dose Modal -->
<div class="modal-backdrop" id="confirmMedModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Confirm Medication Dose</span>
            <button class="modal-close" onclick="closeModal('confirmMedModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="margin-bottom:16px;">
                The system will automatically calculate if the dose is <strong>On-Time</strong>, <strong>Late</strong>, or <strong>Missed</strong> based on a ±<?= MED_GRACE_MINUTES ?>-minute window.
            </div>
            <form method="POST" action="<?= BASE_URL ?>api/medications.php">
                <input type="hidden" name="action"     value="confirm">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Medication Schedule *</label>
                    <select name="schedule_id" class="form-control" required>
                        <option value="">Select schedule…</option>
                        <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['patient_code'] . ' — ' . $s['full_name'] . ': ' . $s['medication_name'] . ' @ ' . date('g:i A', strtotime($s['scheduled_time']))) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes…"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Confirm Dose</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('confirmMedModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php renderFooter(); ?>