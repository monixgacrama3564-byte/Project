<?php
/**
 * admin_api_multibranch.php  v4.0
 * ─────────────────────────────────────────────────────────────
 * This file is a THIN PHP SESSION BRIDGE for the admin dashboard.
 * All data operations run through Firestore via admin_firestore.js.
 * This file only handles:
 *   1. Session validation (set by firebase_auth_bridge.php)
 *   2. Role / branch checks via PHP session
 *   3. Proxying role/branch info back to the dashboard
 *
 * For full CRUD, use admin_firestore.js directly on the client.
 * ─────────────────────────────────────────────────────────────
 */

session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// ── Response helpers ─────────────────────────────────────────
function ok(array $data = [], string $msg = 'Success'): void {
    echo json_encode(array_merge(['success' => true, 'message' => $msg], $data));
    exit;
}
function fail(string $msg = 'Operation failed.', int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// ── Session / role helpers ───────────────────────────────────
function sessionUid(): string       { return $_SESSION['user_id']   ?? ''; }
function sessionRole(): string      { return $_SESSION['user_role'] ?? ''; }
function sessionBranch(): ?string   { return $_SESSION['branch_id'] ?? null; }
function sessionName(): string      { return $_SESSION['user_name'] ?? ''; }

function isMainAdmin(): bool {
    return in_array(sessionRole(), ['admin', 'main_admin'], true);
}
function isSubAdmin(): bool   { return sessionRole() === 'sub_admin'; }
function isStaff(): bool      { return sessionRole() === 'staff'; }
function isAnyAdmin(): bool {
    return in_array(sessionRole(), ['admin','main_admin','sub_admin','manager'], true);
}
function isStaffOrAbove(): bool {
    return in_array(sessionRole(), ['admin','main_admin','sub_admin','manager','staff'], true);
}

// ── Auth guard ───────────────────────────────────────────────
function requireAuth(): void {
    if (!sessionUid() || !isStaffOrAbove()) {
        fail('Session expired or insufficient permissions. Please log in again.', 403);
    }
}

// ─────────────────────────────────────────────────────────────
// ROUTE
// ─────────────────────────────────────────────────────────────
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Public actions: session info ─────────────────────────────
if ($action === 'get_session_info') {
    if (!sessionUid()) {
        ok([
            'logged_in' => false,
            'role'      => null,
            'branch_id' => null,
            'name'      => null,
        ]);
    }
    ok([
        'logged_in'  => true,
        'uid'        => sessionUid(),
        'role'       => sessionRole(),
        'branch_id'  => sessionBranch(),
        'name'       => sessionName(),
        'is_main_admin' => isMainAdmin(),
        'is_sub_admin'  => isSubAdmin(),
        'is_staff'      => isStaff(),
    ]);
}

if ($action === 'logout') {
    session_destroy();
    ok([], 'Logged out.');
}

// ── All remaining actions require auth ────────────────────────
requireAuth();

// ─────────────────────────────────────────────────────────────
// ROLE-BASED ACCESS VALIDATION
// Used by admin_dashboard.php to verify actions before calling
// Firestore. Returns permission info; actual data ops run JS-side.
// ─────────────────────────────────────────────────────────────

if ($action === 'check_permission') {
    $op        = $_POST['op'] ?? '';
    $targetBid = $_POST['branch_id'] ?? null;

    $allowed = false;
    $reason  = '';

    switch ($op) {
        // Products
        case 'create_product':
        case 'edit_product':
        case 'delete_product':
        case 'approve_product':
            $allowed = isMainAdmin();
            $reason  = 'Only main admin can manage the global product catalog.';
            break;

        // Branches
        case 'create_branch':
        case 'delete_branch':
        case 'set_main_branch':
            $allowed = isMainAdmin();
            $reason  = 'Only main admin can manage branches.';
            break;

        // Sub-admin assignment
        case 'assign_sub_admin':
            $allowed = isMainAdmin();
            $reason  = 'Only main admin can assign sub-admins.';
            break;

        // Branch inventory — sub_admin own branch | main_admin all
        case 'add_branch_inventory':
        case 'delete_branch_inventory':
            if (isMainAdmin()) { $allowed = true; break; }
            if (isSubAdmin())  {
                $allowed = (!$targetBid || $targetBid === sessionBranch());
                $reason  = 'You can only manage inventory for your own branch.';
            }
            break;

        case 'update_inventory_stock':
            if (isMainAdmin() || isSubAdmin()) {
                $allowed = (!$targetBid || $targetBid === sessionBranch() || isMainAdmin());
            } elseif (isStaff()) {
                // Staff must have permitStockUpdate flag (verified client-side via Firestore)
                $allowed = true; // Firestore rules enforce the permit check
                $reason  = 'Stock updates are subject to your admin-granted permissions.';
            }
            break;

        // Transfers
        case 'transfer_inventory':
            if (isMainAdmin()) { $allowed = true; break; }
            if (isSubAdmin())  {
                $from    = $_POST['from_branch'] ?? null;
                $allowed = (!$from || $from === sessionBranch());
                $reason  = 'You can only transfer from your own branch.';
            } else {
                $reason = 'Staff cannot initiate inventory transfers.';
            }
            break;

        // Orders
        case 'accept_order':
        case 'process_order':
        case 'update_order_status':
            $allowed = isStaff();
            $reason  = 'Only staff can process orders.';
            break;

        case 'delete_order':
            $allowed = isMainAdmin();
            $reason  = 'Only main admin can delete orders.';
            break;

        // Staff management
        case 'add_staff':
            if (isMainAdmin())  { $allowed = true; break; }
            if (isSubAdmin())   {
                $allowed = true; // Sub-admin can add staff to own branch (enforced in Firestore)
            } else {
                $reason = 'Staff cannot add other users.';
            }
            break;

        case 'delete_user':
            $allowed = isMainAdmin();
            $reason  = 'Only main admin can delete users.';
            break;

        // Tasks
        case 'assign_task':
            $allowed = isMainAdmin() || isSubAdmin();
            $reason  = 'Only admins can assign tasks.';
            break;

        case 'update_task':
            $allowed = isStaffOrAbove();
            break;

        // Analytics / read access
        case 'view_all_branches':
        case 'view_all_analytics':
            $allowed = isMainAdmin();
            $reason  = 'Only main admin can view all branches and analytics.';
            break;

        case 'view_branch_analytics':
            if (isMainAdmin())  { $allowed = true; break; }
            if (isSubAdmin())   { $allowed = (!$targetBid || $targetBid === sessionBranch()); $reason = 'Access restricted to your branch.'; break; }
            if (isStaff())      { $allowed = (!$targetBid || $targetBid === sessionBranch()); $reason = 'Access restricted to your branch.'; break; }
            break;

        default:
            $allowed = isStaffOrAbove();
            $reason  = 'Permission denied.';
    }

    ok([
        'allowed'    => $allowed,
        'reason'     => $allowed ? null : $reason,
        'role'       => sessionRole(),
        'branch_id'  => sessionBranch(),
    ]);
}

// ─────────────────────────────────────────────────────────────
// DASHBOARD NAV CONFIG
// Returns which sidebar items are visible for this role.
// ─────────────────────────────────────────────────────────────
if ($action === 'get_nav_config') {
    $role     = sessionRole();
    $branchId = sessionBranch();

    $allSections = [
        'overview'          => ['label' => 'Overview',         'icon' => '📊', 'roles' => ['admin','main_admin','sub_admin','manager','staff']],
        'orders'            => ['label' => 'Orders',           'icon' => '🛒', 'roles' => ['admin','main_admin','sub_admin','manager','staff']],
        'products'          => ['label' => 'Products',         'icon' => '📦', 'roles' => ['admin','main_admin']],
        'branch_inventory'  => ['label' => 'Branch Inventory', 'icon' => '🏪', 'roles' => ['admin','main_admin','sub_admin','manager','staff']],
        'transfer'          => ['label' => 'Inventory Transfer','icon'=> '🔄', 'roles' => ['admin','main_admin','sub_admin','manager']],
        'branches'          => ['label' => 'Branches',         'icon' => '🏢', 'roles' => ['admin','main_admin']],
        'employees'         => ['label' => 'Employees',        'icon' => '👥', 'roles' => ['admin','main_admin','sub_admin','manager']],
        'tasks'             => ['label' => 'Tasks',            'icon' => '✅', 'roles' => ['admin','main_admin','sub_admin','manager','staff']],
        'analytics'         => ['label' => 'Analytics',        'icon' => '📈', 'roles' => ['admin','main_admin','sub_admin','manager']],
        'users'             => ['label' => 'Customers',        'icon' => '👤', 'roles' => ['admin','main_admin']],
        'settings'          => ['label' => 'Settings',         'icon' => '⚙️', 'roles' => ['admin','main_admin']],
    ];

    $nav = [];
    foreach ($allSections as $key => $cfg) {
        if (in_array($role, $cfg['roles'], true)) {
            $nav[$key] = $cfg;
        }
    }

    ok([
        'nav'            => $nav,
        'role'           => $role,
        'branch_id'      => $branchId,
        'name'           => sessionName(),
        'is_main_admin'  => isMainAdmin(),
        'is_sub_admin'   => isSubAdmin(),
        'is_staff'       => isStaff(),
    ]);
}

// ─────────────────────────────────────────────────────────────
// FALLBACK
// ─────────────────────────────────────────────────────────────
fail('Unknown action: ' . htmlspecialchars($action));