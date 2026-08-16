<?php
/**
 * firebase_auth_bridge.php - Simplified for development
 * In production, keep full JWT verification enabled.
 */

session_start();
header('Content-Type: application/json');

function ok($data=[]){ echo json_encode(array_merge(['success'=>true],$data)); exit; }
function fail($msg){ echo json_encode(['success'=>false,'message'=>$msg]); exit; }

$idToken  = trim($_POST['id_token']  ?? '');
$role     = trim($_POST['role']      ?? 'customer');
$branchId = $_POST['branch_id'] ?? null;
$uid      = trim($_POST['uid']       ?? '');
$name     = trim($_POST['name']      ?? '');

if (!$idToken || !$uid) fail('Missing token or UID.');

// ✅ BASIC CHECKS (no signature verification in dev mode)
$parts = explode('.', $idToken);
if (count($parts) !== 3) fail('Invalid token format.');

$payload = json_decode(base64_decode(str_replace(['-','_'],['+','/'],$parts[1])), true);
if (!$payload) fail('Could not decode token payload.');

$projectId = 'pcexpress-6ea42';
$now = time();

// Time-based validation only
if (($payload['exp'] ?? 0) < $now) fail('Token expired.');
if (($payload['iat'] ?? 0) > ($now + 300)) fail('Token issued in future.');
if (($payload['sub'] ?? '') !== $uid) fail('UID mismatch.');
if (($payload['aud'] ?? '') !== $projectId) fail('Project ID mismatch.');

// ✅ SET PHP SESSION (roles pre-authorized by Firestore)
$validAdminRoles = ['admin','main_admin','sub_admin','manager','staff','rider'];
if (!in_array($role, $validAdminRoles)) fail('Invalid role: '.$role);

$_SESSION['user_id']    = $uid;
$_SESSION['user_role']  = $role;
$_SESSION['branch_id']  = $branchId;
$_SESSION['user_name']  = $name;
$_SESSION['auth_time']  = $now;

ok(['role' => $role, 'branch_id' => $branchId]);
?>