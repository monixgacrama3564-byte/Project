<?php
// ============================================================
// api/incidents.php  — M5: Incident Reporting & Analytics API
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$action = $_POST['action'] ?? '';
$db     = getDB();

// ── ADD Incident ──────────────────────────────────────────
if ($action === 'add') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
    }

    $errors = [];

    $patientId   = intval($_POST['patient_id']   ?? 0);
    $severity    = trim($_POST['severity']        ?? '');
    $description = trim($_POST['description']     ?? '');
    $actionTaken = trim($_POST['action_taken']    ?? '');

    if ($patientId < 1)
        $errors[] = 'Please select a valid patient.';

    if (!in_array($severity, ['low', 'moderate', 'high', 'critical']))
        $errors[] = 'Please select a valid severity level.';

    if (strlen($description) < 10)
        $errors[] = 'Description must be at least 10 characters.';

    if (strlen($description) > 5000)
        $errors[] = 'Description is too long (max 5000 characters).';

    if (strlen($actionTaken) > 5000)
        $errors[] = 'Action taken text is too long (max 5000 characters).';

    // Confirm patient exists and is active
    if ($patientId > 0) {
        $check = $db->prepare("SELECT id FROM patients WHERE id = ? AND is_active = 1");
        $check->execute([$patientId]);
        if (!$check->fetch()) $errors[] = 'Selected patient not found or is inactive.';
    }

    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
    }

    $stmt = $db->prepare("
        INSERT INTO incident_reports (patient_id, reported_by, severity, description, action_taken)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $patientId,
        $_SESSION['user_id'],
        $severity,
        $description,
        $actionTaken ?: null,
    ]);

    $sevLabel = ucfirst($severity);
    setFlash('success', "Incident ({$sevLabel}) reported successfully.");
    header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
}

// ── RESOLVE Incident ──────────────────────────────────────
if ($action === 'resolve') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
    }

    $incidentId = intval($_POST['incident_id'] ?? 0);

    if ($incidentId < 1) {
        setFlash('danger', 'Invalid incident ID.');
        header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
    }

    // Confirm incident exists and is still open
    $check = $db->prepare("SELECT id, resolved FROM incident_reports WHERE id = ?");
    $check->execute([$incidentId]);
    $inc = $check->fetch();

    if (!$inc) {
        setFlash('danger', 'Incident not found.');
        header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
    }

    if ($inc['resolved']) {
        setFlash('warning', 'This incident is already resolved.');
        header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
    }

    $stmt = $db->prepare("
        UPDATE incident_reports SET resolved = 1, resolved_at = NOW() WHERE id = ?
    ");
    $stmt->execute([$incidentId]);

    setFlash('success', 'Incident marked as resolved.');
    header('Location: ' . BASE_URL . 'pages/incidents.php'); exit;
}

// ── Fallback ──────────────────────────────────────────────
setFlash('danger', 'Unknown action.');
header('Location: ' . BASE_URL . 'pages/incidents.php');