<?php
// ============================================================
// pages/dashboard.php  — M1/M3/M4/M5: Overview Dashboard
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();

$db = getDB();

// Stats
$totalPatients = $db->query("SELECT COUNT(*) FROM patients WHERE is_active=1")->fetchColumn();
$alertVitals   = $db->query("SELECT COUNT(*) FROM vital_signs WHERE alert_flag=1 AND DATE(recorded_at)=CURDATE()")->fetchColumn();
$missedMeds    = $db->query("SELECT COUNT(*) FROM medication_adherence WHERE status='missed' AND DATE(confirmed_at)=CURDATE()")->fetchColumn();
$openIncidents = $db->query("SELECT COUNT(*) FROM incident_reports WHERE resolved=0")->fetchColumn();

// Recent vitals with alerts
$recentAlerts = $db->query("
    SELECT v.*, p.full_name, p.patient_code
    FROM vital_signs v
    JOIN patients p ON p.id = v.patient_id
    WHERE v.alert_flag = 1
    ORDER BY v.recorded_at DESC
    LIMIT 10
")->fetchAll();

// Today's medication status summary
$medSummary = $db->query("
    SELECT status, COUNT(*) as cnt
    FROM medication_adherence
    WHERE DATE(confirmed_at) = CURDATE()
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Recent incidents
$recentIncidents = $db->query("
    SELECT i.*, p.full_name, p.patient_code
    FROM incident_reports i
    JOIN patients p ON p.id = i.patient_id
    WHERE i.resolved = 0
    ORDER BY i.reported_at DESC
    LIMIT 5
")->fetchAll();

renderHeader('Dashboard');
?>

<div class="page-header">
    <div>
        <div class="page-title">Dashboard</div>
        <div class="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></div>
    </div>
    <span class="text-muted" style="font-size:13px;"><?= date('l, F j, Y') ?></span>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-label">Active Patients</div>
        <div class="stat-value"><?= $totalPatients ?></div>
        <div class="stat-sub">Registered in system</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">Vital Alerts Today</div>
        <div class="stat-value"><?= $alertVitals ?></div>
        <div class="stat-sub">Threshold breaches</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Missed Meds Today</div>
        <div class="stat-value"><?= $missedMeds ?></div>
        <div class="stat-sub">Doses not confirmed</div>
    </div>
    <div class="stat-card <?= $openIncidents > 0 ? 'danger' : 'success' ?>">
        <div class="stat-label">Open Incidents</div>
        <div class="stat-value"><?= $openIncidents ?></div>
        <div class="stat-sub">Unresolved reports</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <!-- Recent Vital Alerts -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Recent Vital Alerts</span>
            <a href="<?= BASE_URL ?>pages/vitals.php?alert=1" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-wrapper">
            <?php if (empty($recentAlerts)): ?>
                <div class="empty-state">No alerts today 🎉</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Reason</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAlerts as $a): ?>
                    <tr class="alert-pulse">
                        <td>
                            <strong><?= htmlspecialchars($a['full_name']) ?></strong><br>
                            <small class="text-muted"><?= $a['patient_code'] ?></small>
                        </td>
                        <td><small><?= htmlspecialchars($a['alert_reason']) ?></small></td>
                        <td><small><?= date('M d, H:i', strtotime($a['recorded_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Open Incidents -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🚨 Open Incidents</span>
            <a href="<?= BASE_URL ?>pages/incidents.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-wrapper">
            <?php if (empty($recentIncidents)): ?>
                <div class="empty-state">No open incidents</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Severity</th>
                        <th>Reported</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentIncidents as $inc): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($inc['full_name']) ?></strong><br>
                            <small class="text-muted"><?= $inc['patient_code'] ?></small>
                        </td>
                        <td>
                            <?php
                            $sev = $inc['severity'];
                            $cls = match($sev) {
                                'critical' => 'badge-critical',
                                'high'     => 'badge-danger',
                                'moderate' => 'badge-warning',
                                default    => 'badge-info'
                            };
                            ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst($sev) ?></span>
                        </td>
                        <td><small><?= date('M d, H:i', strtotime($inc['reported_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Today's Medication Summary -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-title">💊 Today's Medication Summary</span>
        <a href="<?= BASE_URL ?>pages/medications.php" class="btn btn-sm btn-outline">Manage</a>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-label">Taken</div>
                <div class="stat-value"><?= $medSummary['taken'] ?? 0 ?></div>
                <div class="stat-sub">On time doses</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">Late</div>
                <div class="stat-value"><?= $medSummary['late'] ?? 0 ?></div>
                <div class="stat-sub">Outside grace window</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">Missed</div>
                <div class="stat-value"><?= $medSummary['missed'] ?? 0 ?></div>
                <div class="stat-sub">Not confirmed</div>
            </div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>