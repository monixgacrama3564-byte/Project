<?php
// ============================================================
// api/patients.php  — M2: Patient Management CRUD
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

// ── Helper: validate patient fields ──────────────────────
function validatePatient(array $d): array {
    $errors = [];
    if (strlen(trim($d['full_name'] ?? '')) < 3)
        $errors[] = 'Patient name must be at least 3 characters.';
    if (empty($d['date_of_birth']) || !strtotime($d['date_of_birth']))
        $errors[] = 'A valid date of birth is required.';
    if (!empty($d['date_of_birth']) && strtotime($d['date_of_birth']) > time())
        $errors[] = 'Date of birth cannot be in the future.';
    if (!in_array($d['gender'] ?? '', ['Male','Female','Other']))
        $errors[] = 'Please select a valid gender.';
    if (!empty($d['contact_number']) && !preg_match('/^[0-9\+\-\s]{7,20}$/', $d['contact_number']))
        $errors[] = 'Contact number format is invalid.';
    return $errors;
}

// ── ADD ───────────────────────────────────────────────────
if ($action === 'add') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
    }
    $errors = validatePatient($_POST);
    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
    }

    // Generate patient code
    $last = $db->query("SELECT patient_code FROM patients ORDER BY id DESC LIMIT 1")->fetch();
    $num  = $last ? (intval(substr($last['patient_code'], 3)) + 1) : 1;
    $code = 'PT-' . str_pad($num, 4, '0', STR_PAD_LEFT);

    $stmt = $db->prepare("INSERT INTO patients
        (patient_code, full_name, date_of_birth, gender, contact_number, address, medical_history, assigned_to)
        VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $code,
        trim($_POST['full_name']),
        $_POST['date_of_birth'],
        $_POST['gender'],
        trim($_POST['contact_number'] ?? ''),
        trim($_POST['address']        ?? ''),
        trim($_POST['medical_history']?? ''),
        $_SESSION['user_id'],
    ]);
    setFlash('success', "Patient {$code} added successfully.");
    header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
}

// ── EDIT ──────────────────────────────────────────────────
if ($action === 'edit') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
    }
    $id = intval($_POST['patient_id'] ?? 0);
    if ($id < 1) {
        setFlash('danger', 'Invalid patient.');
        header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
    }

    $errors = validatePatient($_POST);
    if (!empty($errors)) {
        setFlash('danger', implode(' ', $errors));
        header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
    }
    $stmt = $db->prepare("UPDATE patients SET full_name=?, date_of_birth=?, gender=?, contact_number=?, address=?, medical_history=? WHERE id=?");
    $stmt->execute([
        trim($_POST['full_name']),
        $_POST['date_of_birth'],
        $_POST['gender'],
        trim($_POST['contact_number'] ?? ''),
        trim($_POST['address']        ?? ''),
        trim($_POST['medical_history']?? ''),
        $id,
    ]);
    setFlash('success', 'Patient record updated.');
    header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
}

// ── DEACTIVATE ────────────────────────────────────────────
if ($action === 'delete') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
    }
    $id = intval($_POST['patient_id'] ?? 0);
    $db->prepare("UPDATE patients SET is_active = 0 WHERE id = ?")->execute([$id]);
    setFlash('success', 'Patient deactivated.');
    header('Location: ' . BASE_URL . 'pages/patients.php'); exit;
}

header('Location: ' . BASE_URL . 'pages/patients.php');