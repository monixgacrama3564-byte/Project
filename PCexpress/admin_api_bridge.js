// admin_api_bridge.js  v1.0
// ─────────────────────────────────────────────────────────────
// Add to admin_dashboard.php AFTER the admin_firestore.js <script> tag:
//   <script type="module" src="admin_api_bridge.js"></script>
//
// This module waits for admin_firestore.js to register window._firestoreApi,
// then replaces window.api() so all dashboard functions transparently use
// Firestore instead of admin_api_multibranch.php (PHP/MySQL).
//
// The PHP bridge (admin_api_multibranch.php + firebase_auth_bridge.php) is
// still called once on login to establish the PHP session — that flow is
// already handled in admin_dashboard.php's onAuthStateChanged block.
// After that, ALL data operations go through Firestore.
// ─────────────────────────────────────────────────────────────

// Wait for _firestoreApi to be exported by admin_firestore.js
// (ES module execution order is guaranteed, but window._ is set inside the
//  module so it's available synchronously after the module runs)

function patchWindowApi() {
  if (typeof window._firestoreApi === "function") {
    // Replace the classic-script api() with the Firestore version
    window.api = async function(params, _method) {
      // Convert FormData to plain object if needed
      if (params instanceof FormData) {
        const obj = {};
        params.forEach((v, k) => { obj[k] = v; });
        params = obj;
      }
      return window._firestoreApi(params);
    };
    console.log("[admin_api_bridge] window.api() → Firestore ✓");
  } else {
    // _firestoreApi not ready yet; retry after a tick
    setTimeout(patchWindowApi, 50);
  }
}

// Start patching immediately (admin_firestore.js is already evaluated at this point)
patchWindowApi();