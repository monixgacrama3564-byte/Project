<?php
// ============================================================
// pages/incidents.php  — M5: Incident Reporting & Analytics
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();

$db = getDB();

$filterSeverity = $_GET['severity'] ?? '';
$filterResolved = $_GET['resolved'] ?? '';

$where  = ['1=1'];
$params = [];
if ($filterSeverity) { $where[] = 'i.severity = ?'; $params[] = $filterSeverity; }
if ($filterResolved !== '') { $where[] = 'i.resolved = ?'; $params[] = intval($filterResolved); }
$whereSQL = implode(' AND ', $where);

$incidents = $db->prepare("
    SELECT i.*, p.full_name, p.patient_code, u.full_name AS reporter_name
    FROM incident_reports i
    JOIN patients p ON p.id = i.patient_id
    JOIN users u ON u.id = i.reported_by
    WHERE $whereSQL
    ORDER BY i.reported_at DESC
");
$incidents->execute($params);
$incidents = $incidents->fetchAll();

// Stats for open incidents by severity
$stats = $db->query("
    SELECT severity, COUNT(*) as cnt FROM incident_reports WHERE resolved=0 GROUP BY severity
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Total resolved
$totalResolved = $db->query("SELECT COUNT(*) FROM incident_reports WHERE resolved=1")->fetchColumn();

$patients = $db->query("SELECT id, patient_code, full_name FROM patients WHERE is_active=1 ORDER BY full_name")->fetchAll();

renderHeader('Incidents');
?>

<div class="page-header">
    <div>
        <div class="page-title">Incident Reports</div>
        <div class="page-subtitle">Track and resolve clinical incidents (M5)</div>
    </div>
    <button class="btn btn-danger" onclick="openModal('addIncidentModal')">+ Report Incident</button>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card danger">
        <div class="stat-label">Critical (Open)</div>
        <div class="stat-value"><?= $stats['critical'] ?? 0 ?></div>
        <div class="stat-sub">Immediate action needed</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">High (Open)</div>
        <div class="stat-value"><?= $stats['high'] ?? 0 ?></div>
        <div class="stat-sub">Urgent attention</div>
    </div>
    <div class="stat-card info">
        <div class="stat-label">Moderate (Open)</div>
        <div class="stat-value"><?= $stats['moderate'] ?? 0 ?></div>
        <div class="stat-sub">Monitor closely</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Resolved</div>
        <div class="stat-value"><?= $totalResolved ?></div>
        <div class="stat-sub">All time resolved</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <select name="severity" class="form-control" style="max-width:160px;">
                <option value="">All Severities</option>
                <?php foreach (['low','moderate','high','critical'] as $sev): ?>
                <option value="<?= $sev ?>" <?= $filterSeverity === $sev ? 'selected' : '' ?>><?= ucfirst($sev) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="resolved" class="form-control" style="max-width:160px;">
                <option value="">All Status</option>
                <option value="0" <?= $filterResolved === '0' ? 'selected' : '' ?>>Open</option>
                <option value="1" <?= $filterResolved === '1' ? 'selected' : '' ?>>Resolved</option>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
            <a href="incidents.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
</div>

<!-- Incidents Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Incident Log</span>
        <span class="text-muted" style="font-size:12px;"><?= count($incidents) ?> records</span>
    </div>
    <div class="table-wrapper">
        <?php if (empty($incidents)): ?>
            <div class="empty-state">No incidents found.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Severity</th>
                    <th>Description</th>
                    <th>Action Taken</th>
                    <th>Status</th>
                    <th>Reported By</th>
                    <th>Reported At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $inc):
                    $sevClass = match($inc['severity']) {
                        'critical' => 'badge-critical',
                        'high'     => 'badge-danger',
                        'moderate' => 'badge-warning',
                        default    => 'badge-info'
                    };
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($inc['full_name']) ?></strong><br>
                        <small class="text-muted"><?= $inc['patient_code'] ?></small>
                    </td>
                    <td><span class="badge <?= $sevClass ?>"><?= ucfirst($inc['severity']) ?></span></td>
                    <td style="max-width:200px;"><small><?= nl2br(htmlspecialchars($inc['description'])) ?></small></td>
                    <td style="max-width:180px;"><small><?= $inc['action_taken'] ? nl2br(htmlspecialchars($inc['action_taken'])) : '—' ?></small></td>
                    <td>
                        <?php if ($inc['resolved']): ?>
                            <span class="badge badge-success">Resolved</span><br>
                            <small class="text-muted"><?= date('M d, Y', strtotime($inc['resolved_at'])) ?></small>
                        <?php else: ?>
                            <span class="badge badge-danger">Open</span>
                        <?php endif; ?>
                    </td>
                    <td><small><?= htmlspecialchars($inc['reporter_name']) ?></small></td>
                    <td><small><?= date('M d, H:i', strtotime($inc['reported_at'])) ?></small></td>
                    <td>
                        <?php if (!$inc['resolved']): ?>
                        <form method="POST" action="<?= BASE_URL ?>api/incidents.php" style="display:inline;">
                            <input type="hidden" name="action"      value="resolve">
                            <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
                            <input type="hidden" name="incident_id" value="<?= $inc['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-success"
                                    onclick="return confirm('Mark as resolved?')">Resolve</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:11px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add Incident Modal -->
<div class="modal-backdrop" id="addIncidentModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Report Incident</span>
            <button class="modal-close" onclick="closeModal('addIncidentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>api/incidents.php">
                <input type="hidden" name="action"     value="add">
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
                        <label class="form-label">Severity *</label>
                        <select name="severity" class="form-control" required>
                            <option value="">Select…</option>
                            <option value="low">Low</option>
                            <option value="moderate">Moderate</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Description * <small class="text-muted">(min. 10 characters)</small></label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Describe what happened…" required minlength="10"></textarea>
                </div>
                <div class="form-group" style="margin-top:12px;">
                    <label class="form-label">Action Taken</label>
                    <textarea name="action_taken" class="form-control" rows="3"
                              placeholder="What steps were taken?"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('addIncidentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php renderFooter(); ?>