<?php
// ============================================================
// api/medications.php  — M4: Medication Compliance Tracker API
// ============================================================
require_once __DIR__ . '/../includes/config.php';   // 1. config first
require_once __DIR__ . '/../includes/auth.php';      // 2. auth second
require_once __DIR__ . '/../includes/notifications.php'; // 3. notifications last

requireLogin();

$action = $_POST['action'] ?? '';
$db     = getDB();

// ── ADD Schedule ──────────────────────────────────────────
if ($action === 'add_schedule') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
    }

    $errors = [];
    $patientId      = intval($_POST['patient_id']    ?? 0);
    $medicationName = trim($_POST['medication_name'] ?? '');
    $dosage         = trim($_POST['dosage']          ?? '');
    $scheduledTime  = trim($_POST['scheduled_time']  ?? '');

    if ($patientId < 1)                $errors[] = 'Please select a valid patient.';
    if (strlen($medicationName) < 2)   $errors[] = 'Medication name must be at least 2 characters.';
    if (strlen($medicationName) > 150) $errors[] = 'Medication name is too long (max 150 characters).';
    if (strlen($dosage) < 2)           $errors[] = 'Dosage is required (e.g. 500mg twice daily).';
    if (strlen($dosage) > 100)         $errors[] = 'Dosage is too long (max 100 characters).';
    if (empty($scheduledTime) || !preg_match('/^\d{2}:\d{2}$/', $scheduledTime))
        $errors[] = 'A valid scheduled time is required.';

    if ($patientId > 0) {
        $check = $db->prepare("SELECT id FROM patients WHERE id = ? AND is_active = 1");
        $check->execute([$patientId]);
        if (!$check->fetch()) $errors[] = 'Selected patient not found or is inactive.';
    }

    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
    }

    $db->prepare("INSERT INTO medication_schedules (patient_id, medication_name, dosage, scheduled_time) VALUES (?, ?, ?, ?)")
       ->execute([$patientId, $medicationName, $dosage, $scheduledTime]);

    setFlash('success', "Medication schedule for {$medicationName} added successfully.");
    header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
}

// ── DEACTIVATE Schedule ───────────────────────────────────
if ($action === 'deactivate') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
    }

    $scheduleId = intval($_POST['schedule_id'] ?? 0);
    if ($scheduleId < 1) {
        setFlash('danger', 'Invalid schedule.');
        header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
    }

    $db->prepare("UPDATE medication_schedules SET is_active = 0 WHERE id = ?")
       ->execute([$scheduleId]);

    setFlash('success', 'Medication schedule deactivated.');
    header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
}

// ── CONFIRM Dose (Temporal Adherence Engine) ──────────────
if ($action === 'confirm') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
    }

    $errors     = [];
    $scheduleId = intval($_POST['schedule_id'] ?? 0);
    $notes      = trim($_POST['notes'] ?? '');

    if ($scheduleId < 1) $errors[] = 'Please select a valid medication schedule.';

    $schedule = null;
    if ($scheduleId > 0) {
        $stmt = $db->prepare("
            SELECT ms.*, p.id AS p_id, p.full_name AS p_name, p.patient_code
            FROM medication_schedules ms
            JOIN patients p ON p.id = ms.patient_id
            WHERE ms.id = ? AND ms.is_active = 1
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch();
        if (!$schedule) $errors[] = 'Selected schedule not found or is inactive.';
    }

    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
    }

    // ── Temporal Adherence Engine ─────────────────────────
    $now         = new DateTime();
    $todayDate   = $now->format('Y-m-d');
    $scheduledDT = new DateTime($todayDate . ' ' . $schedule['scheduled_time']);
    $diffMinutes = ($now->getTimestamp() - $scheduledDT->getTimestamp()) / 60;

    if (abs($diffMinutes) <= MED_GRACE_MINUTES) {
        $status = 'taken';
    } elseif ($diffMinutes > MED_GRACE_MINUTES) {
        $status = 'late';
    } else {
        $status = 'taken'; // confirmed early
    }

    $stmt = $db->prepare("
        INSERT INTO medication_adherence
            (schedule_id, patient_id, confirmed_by, scheduled_datetime, confirmed_at, status, notes)
        VALUES (?, ?, ?, ?, NOW(), ?, ?)
    ");
    $stmt->execute([
        $scheduleId,
        $schedule['p_id'],
        $_SESSION['user_id'],
        $scheduledDT->format('Y-m-d H:i:s'),
        $status,
        $notes ?: null,
    ]);
    $newAdherenceId = (int) $db->lastInsertId();

    // ── Notify if missed or late ──────────────────────────
    if ($status === 'missed' || $status === 'late') {
        $label     = strtoupper($status);
        $scheduled = date('g:i A', strtotime($schedule['scheduled_time']));
        createNotification(
            type:      'missed_medication',
            title:     "💊 {$label} Medication: " . $schedule['p_name'],
            message:   $schedule['medication_name'] . ' (' . $schedule['dosage'] . ') scheduled at ' . $scheduled . ' was ' . $status . '.',
            patientId: $schedule['p_id'],
            refId:     $newAdherenceId
        );
    }

    setFlash('success', 'Dose confirmed — Status: ' . ucfirst($status) . '.');
    header('Location: ' . BASE_URL . 'pages/medications.php'); exit;
}

// ── Fallback ──────────────────────────────────────────────
setFlash('danger', 'Unknown action.');
header('Location: ' . BASE_URL . 'pages/medications.php');