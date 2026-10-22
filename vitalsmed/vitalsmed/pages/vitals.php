<?php
// ============================================================
// pages/vitals.php  — M3: Vital Signs Logic Engine
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();

$db = getDB();

// Filters
$filterPatient = intval($_GET['patient_id'] ?? 0);
$filterAlert   = $_GET['alert'] ?? '';

$where  = ['1=1'];
$params = [];
if ($filterPatient) { $where[] = 'v.patient_id = ?'; $params[] = $filterPatient; }
if ($filterAlert === '1') { $where[] = 'v.alert_flag = 1'; }

$whereSQL = implode(' AND ', $where);

$vitals = $db->prepare("
    SELECT v.*, p.full_name, p.patient_code, u.full_name AS recorded_by_name
    FROM vital_signs v
    JOIN patients p ON p.id = v.patient_id
    JOIN users u ON u.id = v.recorded_by
    WHERE $whereSQL
    ORDER BY v.recorded_at DESC
    LIMIT 100
");
$vitals->execute($params);
$vitals = $vitals->fetchAll();

// Stats
$totalToday  = $db->query("SELECT COUNT(*) FROM vital_signs WHERE DATE(recorded_at)=CURDATE()")->fetchColumn();
$alertsToday = $db->query("SELECT COUNT(*) FROM vital_signs WHERE alert_flag=1 AND DATE(recorded_at)=CURDATE()")->fetchColumn();
$totalAll    = $db->query("SELECT COUNT(*) FROM vital_signs")->fetchColumn();

// Active patients for dropdown
$patients = $db->query("SELECT id, patient_code, full_name FROM patients WHERE is_active=1 ORDER BY full_name")->fetchAll();

renderHeader('Vital Signs');
?>

<div class="page-header">
    <div>
        <div class="page-title">Vital Signs</div>
        <div class="page-subtitle">Monitor and record patient clinical data (M3)</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('addVitalsModal')">+ Log Vitals</button>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-label">Recorded Today</div>
        <div class="stat-value"><?= $totalToday ?></div>
        <div class="stat-sub">Today's entries</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">Alerts Today</div>
        <div class="stat-value"><?= $alertsToday ?></div>
        <div class="stat-sub">Threshold breaches</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Total Records</div>
        <div class="stat-value"><?= $totalAll ?></div>
        <div class="stat-sub">All time entries</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <select name="patient_id" class="form-control" style="max-width:220px;">
                <option value="">All Patients</option>
                <?php foreach ($patients as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filterPatient == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['patient_code'] . ' — ' . $p['full_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="alert" class="form-control" style="max-width:160px;">
                <option value="">All Records</option>
                <option value="1" <?= $filterAlert === '1' ? 'selected' : '' ?>>Alerts Only</option>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
            <a href="vitals.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
</div>

<!-- Vitals Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Vital Sign Records</span>
        <span class="text-muted" style="font-size:12px;"><?= count($vitals) ?> records (last 100)</span>
    </div>
    <div class="table-wrapper">
        <?php if (empty($vitals)): ?>
            <div class="empty-state">No vital sign records found.</div>
        <?php else: ?>
        <table class="searchable-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Temp (°C)</th>
                    <th>BP (mmHg)</th>
                    <th>HR (bpm)</th>
                    <th>O₂ Sat (%)</th>
                    <th>Status</th>
                    <th>Alert Reason</th>
                    <th>Recorded By</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vitals as $v): ?>
                <tr class="<?= $v['alert_flag'] ? 'alert-row' : '' ?>">
                    <td>
                        <strong><?= htmlspecialchars($v['full_name']) ?></strong><br>
                        <small class="text-muted"><?= $v['patient_code'] ?></small>
                    </td>
                    <td class="<?= ($v['temperature'] > TEMP_MAX || $v['temperature'] < TEMP_MIN) ? 'text-danger' : '' ?>">
                        <?= $v['temperature'] ?>
                    </td>
                    <td class="<?= $v['systolic_bp'] > SYSTOLIC_MAX ? 'text-danger' : '' ?>">
                        <?= $v['systolic_bp'] ?>/<?= $v['diastolic_bp'] ?>
                    </td>
                    <td class="<?= $v['heart_rate'] > HEART_RATE_MAX ? 'text-danger' : '' ?>">
                        <?= $v['heart_rate'] ?>
                    </td>
                    <td class="<?= ($v['oxygen_sat'] && $v['oxygen_sat'] < O2_MIN) ? 'text-danger' : '' ?>">
                        <?= $v['oxygen_sat'] ?? '—' ?>
                    </td>
                    <td>
                        <?php if ($v['alert_flag']): ?>
                            <span class="badge badge-danger">⚠ Alert</span>
                        <?php else: ?>
                            <span class="badge badge-success">Normal</span>
                        <?php endif; ?>
                    </td>
                    <td><small><?= $v['alert_reason'] ? htmlspecialchars($v['alert_reason']) : '—' ?></small></td>
                    <td><small><?= htmlspecialchars($v['recorded_by_name']) ?></small></td>
                    <td><small><?= date('M d, Y H:i', strtotime($v['recorded_at'])) ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add Vitals Modal -->
<div class="modal-backdrop" id="addVitalsModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Log Vital Signs</span>
            <button class="modal-close" onclick="closeModal('addVitalsModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="margin-bottom:16px;font-size:12px;">
                <strong>Safe Zone Thresholds:</strong>
                Temp: <?= TEMP_MIN ?>–<?= TEMP_MAX ?>°C &nbsp;|&nbsp;
                Systolic BP: ≤<?= SYSTOLIC_MAX ?> mmHg &nbsp;|&nbsp;
                HR: ≤<?= HEART_RATE_MAX ?> bpm &nbsp;|&nbsp;
                O₂: ≥<?= O2_MIN ?>%
            </div>
            <form method="POST" action="<?= BASE_URL ?>api/vitals.php" id="vitalsForm">
                <input type="hidden" name="action"     value="add">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Patient *</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">Select patient…</option>
                        <?php foreach ($patients as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['patient_code'] . ' — ' . $p['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Temperature (°C) *</label>
                        <input type="number" id="temperature" name="temperature" class="form-control"
                               step="0.1" min="32" max="45" placeholder="e.g. 36.8" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Systolic BP (mmHg) *</label>
                        <input type="number" id="systolic_bp" name="systolic_bp" class="form-control"
                               min="60" max="250" placeholder="e.g. 120" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Diastolic BP (mmHg) *</label>
                        <input type="number" id="diastolic_bp" name="diastolic_bp" class="form-control"
                               min="40" max="150" placeholder="e.g. 80" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heart Rate (bpm) *</label>
                        <input type="number" id="heart_rate" name="heart_rate" class="form-control"
                               min="30" max="250" placeholder="e.g. 75" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">O₂ Saturation (%)</label>
                        <input type="number" id="oxygen_sat" name="oxygen_sat" class="form-control"
                               step="0.1" min="50" max="100" placeholder="e.g. 98">
                    </div>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional clinical notes…"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Record</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('addVitalsModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php renderFooter(); ?>