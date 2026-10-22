<?php
// ============================================================
// api/vitals.php  — M3: Vital Signs Logic Engine API
// ============================================================
require_once __DIR__ . '/../includes/config.php';       // 1. config first
require_once __DIR__ . '/../includes/auth.php';          // 2. auth second
require_once __DIR__ . '/../includes/notifications.php'; // 3. notifications last

requireLogin();

$action = $_POST['action'] ?? '';
$db     = getDB();

// ── ADD Vital Signs ───────────────────────────────────────
if ($action === 'add') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/vitals.php'); exit;
    }

    $errors = [];

    $patientId = intval($_POST['patient_id']   ?? 0);
    $temp      = floatval($_POST['temperature'] ?? 0);
    $systolic  = intval($_POST['systolic_bp']   ?? 0);
    $diastolic = intval($_POST['diastolic_bp']  ?? 0);
    $heartRate = intval($_POST['heart_rate']    ?? 0);
    $oxygenSat = isset($_POST['oxygen_sat']) && $_POST['oxygen_sat'] !== ''
                   ? floatval($_POST['oxygen_sat']) : null;
    $notes     = trim($_POST['notes'] ?? '');

    if ($patientId < 1)                              $errors[] = 'Please select a valid patient.';
    if ($temp < 32 || $temp > 45)                    $errors[] = 'Temperature must be between 32–45 °C.';
    if ($systolic < 60 || $systolic > 250)           $errors[] = 'Systolic BP must be between 60–250 mmHg.';
    if ($diastolic < 40 || $diastolic > 150)         $errors[] = 'Diastolic BP must be between 40–150 mmHg.';
    if ($systolic <= $diastolic)                     $errors[] = 'Systolic BP must be greater than Diastolic BP.';
    if ($heartRate < 30 || $heartRate > 250)         $errors[] = 'Heart Rate must be between 30–250 bpm.';
    if ($oxygenSat !== null && ($oxygenSat < 50 || $oxygenSat > 100))
        $errors[] = 'O₂ Saturation must be between 50–100 %.';

    $patient = null;
    if ($patientId > 0) {
        $check = $db->prepare("SELECT id, full_name, patient_code FROM patients WHERE id = ? AND is_active = 1");
        $check->execute([$patientId]);
        $patient = $check->fetch();
        if (!$patient) $errors[] = 'Selected patient not found or is inactive.';
    }

    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/vitals.php'); exit;
    }

    // ── Safe-Zone Engine ──────────────────────────────────
    $alertFlag  = 0;
    $alertParts = [];

    if ($systolic > SYSTOLIC_MAX)
        $alertParts[] = "High Systolic BP ({$systolic} mmHg)";
    if ($heartRate > HEART_RATE_MAX)
        $alertParts[] = "High Heart Rate ({$heartRate} bpm)";
    if ($temp > TEMP_MAX)
        $alertParts[] = "High Temperature ({$temp} °C)";
    if ($temp < TEMP_MIN)
        $alertParts[] = "Low Temperature ({$temp} °C)";
    if ($oxygenSat !== null && $oxygenSat < O2_MIN)
        $alertParts[] = "Low O₂ Saturation ({$oxygenSat} %)";

    if (!empty($alertParts)) {
        $alertFlag   = 1;
        $alertReason = implode('; ', $alertParts);
    } else {
        $alertReason = null;
    }

    // ── Insert record ─────────────────────────────────────
    $stmt = $db->prepare("
        INSERT INTO vital_signs
            (patient_id, recorded_by, temperature, systolic_bp, diastolic_bp,
             heart_rate, oxygen_sat, alert_flag, alert_reason, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $patientId, $_SESSION['user_id'],
        $temp, $systolic, $diastolic,
        $heartRate, $oxygenSat,
        $alertFlag, $alertReason,
        $notes ?: null,
    ]);
    $newVitalId = (int) $db->lastInsertId();

    // ── Notify all staff if alert ─────────────────────────
    if ($alertFlag && $patient) {
        createNotification(
            type:      'vital_alert',
            title:     '⚠️ Vital Alert: ' . $patient['full_name'],
            message:   $alertReason,
            patientId: $patientId,
            refId:     $newVitalId
        );
    }

    if ($alertFlag) {
        setFlash('warning', '⚠ Vital signs recorded with ALERT: ' . $alertReason);
    } else {
        setFlash('success', 'Vital signs recorded successfully — all values within normal range.');
    }

    header('Location: ' . BASE_URL . 'pages/vitals.php'); exit;
}

// ── Fallback ──────────────────────────────────────────────
setFlash('danger', 'Unknown action.');
header('Location: ' . BASE_URL . 'pages/vitals.php');