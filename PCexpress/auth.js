// auth.js v3.0 - Multi-Branch Firebase Authentication
// Handles login, register, logout, session state, role detection

import { auth, db } from "./firebase-config.js";
import {
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  sendPasswordResetEmail,
  signOut,
  onAuthStateChanged,
  updateProfile,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
import {
  doc,
  setDoc,
  getDoc,
  serverTimestamp,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

// ─────────────────────────────────────────────────────────────
// VALIDATION HELPERS
// ─────────────────────────────────────────────────────────────
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
}
function isValidName(name) {
  return /^[\p{L}\s'\-\.]+$/u.test(name);
}

// ─────────────────────────────────────────────────────────────
// REGISTER
// ─────────────────────────────────────────────────────────────
export async function doRegister() {
  const firstName = document.getElementById("regFirst")?.value.trim() || "";
  const lastName  = document.getElementById("regLast")?.value.trim()  || "";
  const email     = document.getElementById("regEmail")?.value.trim() || "";
  const password  = document.getElementById("regPassword")?.value     || "";

  if (!firstName || !lastName || !email || !password) {
    return showMsg("registerMsg", "Please fill in all fields.", "error");
  }
  if (!isValidName(firstName)) return showMsg("registerMsg", "First name contains invalid characters.", "error");
  if (!isValidName(lastName))  return showMsg("registerMsg", "Last name contains invalid characters.", "error");
  if (!isValidEmail(email))    return showMsg("registerMsg", "Please enter a valid email address.", "error");
  if (password.length < 6)     return showMsg("registerMsg", "Password must be at least 6 characters.", "error");
  if (password.length > 128)   return showMsg("registerMsg", "Password is too long (max 128 characters).", "error");
  if (!/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) {
    return showMsg("registerMsg", "Password must contain at least one letter and one number.", "error");
  }

  setBtn("btnRegister", true, "Create Account");

  try {
    const cred = await createUserWithEmailAndPassword(auth, email, password);
    const uid  = cred.user.uid;

    await updateProfile(cred.user, { displayName: `${firstName} ${lastName}` });

    await setDoc(doc(db, "users", uid), {
      firstName,
      lastName,
      email,
      role:      "customer",
      branchId:  null,
      status:    "active",
      permitStockUpdate: false,
      createdAt: serverTimestamp(),
      updatedAt: serverTimestamp(),
    });

    showMsg("registerMsg", "✓ Account created! Switching to login...", "success");
    setTimeout(() => switchView("login"), 1800);

  } catch (err) {
    const msg = {
      "auth/email-already-in-use": "That email address is already registered.",
      "auth/invalid-email":        "Please enter a valid email address.",
      "auth/weak-password":        "Password must be at least 6 characters.",
    }[err.code] || "Registration failed. Please try again.";
    showMsg("registerMsg", msg, "error");
    console.error("Register error:", err);
  } finally {
    setBtn("btnRegister", false, "Create Account");
  }
}

// ─────────────────────────────────────────────────────────────
// LOGIN
// ─────────────────────────────────────────────────────────────
export async function doLogin() {
  const email    = document.getElementById("loginEmail")?.value.trim() || "";
  const password = document.getElementById("loginPassword")?.value     || "";

  if (!email || !password) return showMsg("loginMsg", "Please fill in all fields.", "error");
  if (!isValidEmail(email)) return showMsg("loginMsg", "Please enter a valid email address.", "error");

  setBtn("btnLogin", true, "Sign In");

  try {
    const cred = await signInWithEmailAndPassword(auth, email, password);
    const uid  = cred.user.uid;

    const userSnap = await getDoc(doc(db, "users", uid));
    const userData = userSnap.exists() ? userSnap.data() : {};

    const displayName = `${userData.firstName || ""} ${userData.lastName || ""}`.trim() || email;
    const userRole    = userData.role     || "customer";
    const userBranch  = userData.branchId || null;

    // Block inactive accounts
    if ((userData.status || "active") === "inactive") {
      await signOut(auth);
      return showMsg("loginMsg", "Your account has been deactivated. Please contact support.", "error");
    }

    const adminRoles = ["admin", "main_admin", "sub_admin", "manager", "staff", "rider"];
    
    if (adminRoles.includes(userRole)) {
      // Bridge to PHP session via firebase_auth_bridge.php
      try {
        const idToken = await cred.user.getIdToken();
        const fd = new FormData();
        fd.append("id_token",  idToken);
        fd.append("role",      userRole);
        fd.append("branch_id", userBranch || "");
        fd.append("uid",       uid);
        fd.append("name",      displayName);

        const res  = await fetch("firebase_auth_bridge.php", { method: "POST", body: fd });
        const data = await res.json();
        if (!data.success) {
          console.warn("PHP bridge warning:", data.message);
        }
      } catch (bridgeErr) {
        console.warn("PHP bridge unavailable:", bridgeErr);
      }

      const greeting = userRole === "staff" 
        ? `Welcome, ${displayName}! Redirecting to staff dashboard...` 
        : "Welcome Admin! Redirecting...";
      showMsg("loginMsg", greeting, "success");
      // Store in window for admin_firestore.js role checks
      window.ADMIN_ROLE     = userRole;
      window.SESSION_BRANCH = userBranch;
      sessionStorage.setItem("ADMIN_ROLE", userRole);
      sessionStorage.setItem("SESSION_BRANCH", userBranch || "");
      setTimeout(() => {
        window.location.href = "admin_dashboard.php";
      }, 1500);
      return;
    }

    // Customer login
    showMsg("loginMsg", `Welcome, ${displayName}! Redirecting...`, "success");
    setTimeout(() => {
      window.location.href = "home.php";  // ← Changed from admin_dashboard.php
    }, 1000);

  } catch (err) {
    const msg = {
      "auth/invalid-credential":      "Invalid email or password.",
      "auth/user-not-found":          "No account found with that email.",
      "auth/wrong-password":          "Incorrect password.",
      "auth/too-many-requests":       "Too many attempts. Please try again later.",
      "auth/user-disabled":           "This account has been disabled.",
    }[err.code] || "Invalid email or password.";
    showMsg("loginMsg", msg, "error");
    console.error("Login error:", err);
  } finally {
    setBtn("btnLogin", false, "Sign In");
  }
}

// ─────────────────────────────────────────────────────────────
// FORGOT PASSWORD
// ─────────────────────────────────────────────────────────────
export async function doForgot() {
  const email = document.getElementById("forgotEmail")?.value.trim() || "";
  if (!email)              return showMsg("forgotMsg", "Please enter your email address.", "error");
  if (!isValidEmail(email)) return showMsg("forgotMsg", "Please enter a valid email address.", "error");

  setBtn("btnForgot", true, "Send Reset Link");
  try {
    await sendPasswordResetEmail(auth, email);
    showMsg("forgotMsg", "✓ If that email is registered, a reset link has been sent.", "success");
  } catch (err) {
    // Always show success to prevent email enumeration
    showMsg("forgotMsg", "✓ If that email is registered, a reset link has been sent.", "success");
    console.error("Forgot password error:", err);
  } finally {
    setBtn("btnForgot", false, "Send Reset Link");
  }
}

// ─────────────────────────────────────────────────────────────
// LOGOUT
// ─────────────────────────────────────────────────────────────
export async function doLogout() {
  try {
    // Clear PHP session
    try {
      const fd = new FormData();
      fd.append("action", "logout");
      await fetch("admin_api_multibranch.php", { method: "POST", body: fd });
    } catch (_) {}

    await signOut(auth);
    window.ADMIN_ROLE     = null;
    window.SESSION_BRANCH = null;
    sessionStorage.removeItem("ADMIN_ROLE");
    sessionStorage.removeItem("SESSION_BRANCH");
    window.location.href = "home.php";
  } catch (err) {
    console.error("Logout error:", err);
    alert("Error logging out. Please refresh the page.");
  }
}

// ─────────────────────────────────────────────────────────────
// SESSION OBSERVER
// ─────────────────────────────────────────────────────────────
export function onSession(callback) {
  return onAuthStateChanged(auth, async (user) => {
    if (!user) {
      callback(null, null);
      return;
    }
    const userSnap = await getDoc(doc(db, "users", user.uid));
    const userData = userSnap.exists() ? userSnap.data() : {};
    callback(user, userData);
  });
}

// ─────────────────────────────────────────────────────────────
// UI HELPERS
// ─────────────────────────────────────────────────────────────
export function openPanel(view) {
  switchView(view || "login");
  document.getElementById("accountPanel")?.classList.add("open");
  document.getElementById("panelOverlay")?.classList.add("active");
  document.body.style.overflow = "hidden";
}

export function closePanel() {
  document.getElementById("accountPanel")?.classList.remove("open");
  document.getElementById("panelOverlay")?.classList.remove("active");
  document.body.style.overflow = "";
}

export function switchView(name) {
  document.querySelectorAll(".panel-view").forEach(v => v.classList.remove("active"));
  const key    = name.charAt(0).toUpperCase() + name.slice(1);
  const target = document.getElementById("view" + key);
  if (target) target.classList.add("active");
  clearMsgs();
}

function clearMsgs() {
  document.querySelectorAll(".panel-msg").forEach(m => { m.className = "panel-msg"; m.textContent = ""; });
}

function showMsg(id, text, type) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent  = text;
  el.className    = "panel-msg " + type;
}

function setBtn(id, loading, label) {
  const btn = document.getElementById(id);
  if (!btn) return;
  btn.disabled    = loading;
  btn.textContent = loading ? "Please wait..." : label;
}

export function togglePw(inputId) {
  const inp = document.getElementById(inputId);
  if (inp) inp.type = inp.type === "password" ? "text" : "password";
}

export function updateHeaderLoggedIn(displayName, email) {
  const acctLink = document.querySelector('.header-actions a[title="Account"]');
  if (!acctLink) return;

  acctLink.outerHTML = `
  <div class="acct-wrap" id="acctWrapInline" style="position:relative;">
    <div class="acct-toggle" style="display:flex;flex-direction:column;align-items:center;font-size:11px;color:#cc0000;cursor:pointer;padding:8px;">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#cc0000" stroke-width="1.8" style="margin-bottom:3px;">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span>${displayName.split(" ")[0]}</span>
    </div>
    <div id="acctDropInline" style="display:none;position:absolute;top:calc(100% + 10px);right:0;background:#fff;border:1px solid #e0e0e0;border-radius:4px;min-width:170px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:9999;">
      <div style="padding:10px 16px;border-bottom:1px solid #f0f0f0;font-size:12px;color:#888;word-break:break-all;">${email}</div>
      <a href="dashboard.php" style="display:block;padding:12px 20px;font-size:14px;color:#222;border-bottom:1px solid #f0f0f0;text-decoration:none;">📊 Dashboard</a>
      <a href="addresses.php" style="display:block;padding:12px 20px;font-size:14px;color:#222;border-bottom:1px solid #f0f0f0;text-decoration:none;">📍 Addresses</a>
      <a href="#" id="logoutBtn" style="display:block;padding:12px 20px;font-size:14px;color:#cc0000;text-decoration:none;">🚪 Logout</a>
    </div>
  </div>`;

  document.querySelector(".acct-toggle")?.addEventListener("click", e => {
    e.stopPropagation();
    const drop = document.getElementById("acctDropInline");
    if (drop) drop.style.display = drop.style.display === "block" ? "none" : "block";
  });

  document.getElementById("logoutBtn")?.addEventListener("click", e => {
    e.preventDefault();
    doLogout();
  });

  document.addEventListener("click", e => {
    const wrap = document.getElementById("acctWrapInline");
    const drop = document.getElementById("acctDropInline");
    if (wrap && drop && !wrap.contains(e.target)) drop.style.display = "none";
  });
}

// ─────────────────────────────────────────────────────────────
// AUTO-INIT
// ─────────────────────────────────────────────────────────────
// ─────────────────────────────────────────────────────────────
// MIGRATE LOCALSTORAGE CART → FIRESTORE
// Runs once after login. Moves any items saved by the legacy
// localStorage cart (home.php / product.php inline scripts)
// into Firestore so checkout_firestore_bridge.js can read them.
// ─────────────────────────────────────────────────────────────
async function migrateLocalCart(uid) {
  try {
    const raw = localStorage.getItem("pcx_cart");
    if (!raw) return;
    const items = JSON.parse(raw);
    if (!Array.isArray(items) || items.length === 0) return;

    const { doc, setDoc, getDoc, serverTimestamp } =
      await import("https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js");

    for (const item of items) {
      const id = String(item.id || "");
      if (!id) continue;

      const ref  = doc(db, "carts", uid, "items", id);
      const snap = await getDoc(ref);

      if (snap.exists()) {
        // Already in Firestore — just bump qty
        const existing = snap.data();
        const mergedQty = Math.min(99, (existing.qty || 0) + (item.qty || 1));
        await setDoc(ref, { qty: mergedQty, updatedAt: serverTimestamp() }, { merge: true });
      } else {
        await setDoc(ref, {
          productId:    id,
          productName:  item.name  || "Product",
          productPrice: item.priceRaw || parseFloat((item.price || "0").replace(/[^0-9.]/g, "")) || 0,
          productImage: item.image || "",
          variant:      item.variant || null,
          qty:          Math.max(1, item.qty || 1),
          addedAt:      serverTimestamp(),
          updatedAt:    serverTimestamp(),
        });
      }
    }

    // Clear localStorage cart after successful migration
    localStorage.removeItem("pcx_cart");
    console.log(`[auth] Migrated ${items.length} cart item(s) to Firestore.`);
  } catch (err) {
    console.warn("[auth] migrateLocalCart error:", err);
  }
}

// ─────────────────────────────────────────────────────────────
// GLOBAL addToCartObj — used by home.php / product.php inline JS
// Routes to Firestore when logged in, localStorage when guest.
// ─────────────────────────────────────────────────────────────
window.addToCartObj = async function(product) {
  const user = auth.currentUser;

  if (user) {
    // Logged in → write directly to Firestore
    try {
      const { doc, setDoc, getDoc, updateDoc, serverTimestamp } =
        await import("https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js");

      const id  = String(product.id || "");
      if (!id) return;
      const ref  = doc(db, "carts", user.uid, "items", id);
      const snap = await getDoc(ref);
      const qty  = Math.max(1, parseInt(product.qty) || 1);

      if (snap.exists()) {
        const newQty = Math.min(99, snap.data().qty + qty);
        await updateDoc(ref, { qty: newQty, updatedAt: serverTimestamp() });
      } else {
        const price = product.priceRaw
          || parseFloat((product.price || "0").replace(/[^0-9.]/g, "")) || 0;
        await setDoc(ref, {
          productId:    id,
          productName:  product.name  || "Product",
          productPrice: price,
          productImage: product.image || "",
          variant:      product.variant || null,
          qty,
          addedAt:   serverTimestamp(),
          updatedAt: serverTimestamp(),
        });
      }

      // Update badge
      const { getDocs, collection } =
        await import("https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js");
      const countSnap = await getDocs(collection(db, "carts", user.uid, "items"));
      document.querySelectorAll("#cartBadge").forEach(el => {
        el.textContent = countSnap.size;
      });
    } catch (err) {
      console.error("[addToCartObj] Firestore error:", err);
    }
  } else {
    // Guest → save to localStorage
    try {
      let cart = JSON.parse(localStorage.getItem("pcx_cart") || "[]");
      const existing = cart.find(i => i.id === product.id);
      if (existing) {
        existing.qty = Math.min(99, (existing.qty || 1) + (product.qty || 1));
      } else {
        cart.push({ ...product, qty: product.qty || 1 });
      }
      localStorage.setItem("pcx_cart", JSON.stringify(cart));
      const badge = document.getElementById("cartBadge");
      if (badge) badge.textContent = cart.reduce((s, i) => s + i.qty, 0);
    } catch (err) {
      console.warn("[addToCartObj] localStorage error:", err);
    }
  }
};

// ─────────────────────────────────────────────────────────────
// AUTO-INIT
// ─────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  const ADMIN_ROLES = ["admin", "main_admin", "sub_admin", "manager", "staff"];
  const pathname    = window.location.pathname.toLowerCase();
  const isHomePage  = pathname.includes("home") || pathname === "/" || pathname.endsWith("index.php");
  const isAdminPage = pathname.includes("admin_dashboard");

  onSession(async (user, profile) => {
    if (user && profile) {
      // Redirect admin/staff away from any non-admin page to the dashboard
      if (!isAdminPage && ADMIN_ROLES.includes(profile.role)) {
        window.location.href = "admin_dashboard.php";
        return;
      }
      // Only update the customer-facing header on non-admin pages
      if (!isAdminPage) {
        const displayName = `${profile.firstName || ""} ${profile.lastName || ""}`.trim() || user.email;
        updateHeaderLoggedIn(displayName, user.email);
        // Migrate any localStorage cart items → Firestore on login
        await migrateLocalCart(user.uid);
      }
    }
  });

  // Expose to global scope for inline onclick handlers
  window.openPanel  = openPanel;
  window.closePanel = closePanel;
  window.switchView = switchView;
  window.togglePw   = togglePw;
  window.doLogin    = doLogin;
  window.doRegister = doRegister;
  window.doForgot   = doForgot;
  window.doLogout   = doLogout;

  document.addEventListener("keydown", e => { if (e.key === "Escape") closePanel(); });
});