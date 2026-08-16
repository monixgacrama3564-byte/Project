<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Account – PC Express</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f5f5;color:#222;}
a{text-decoration:none;color:inherit;}

/* HEADER */
.header{background:#fff;border-bottom:1px solid #ddd;position:sticky;top:0;z-index:1000;box-shadow:0 2px 6px rgba(0,0,0,.1);}
.header-inner{display:flex;align-items:center;gap:16px;padding:10px 24px;max-width:1400px;margin:0 auto;}
.logo-text{font-size:26px;font-weight:900;letter-spacing:-1px;}
.logo-text span.pc{color:#003da5;} .logo-text span.ex{color:#e00;}
.search-bar{flex:1;display:flex;border:2px solid #003da5;border-radius:4px;overflow:hidden;}
.search-bar select{padding:8px 12px;border:none;border-right:1px solid #ccc;background:#f0f0f0;font-size:13px;outline:none;}
.search-bar input{flex:1;padding:8px 14px;border:none;font-size:14px;outline:none;}
.search-bar button{background:#003da5;border:none;padding:0 18px;cursor:pointer;color:#fff;font-size:16px;}
.header-actions{display:flex;align-items:center;gap:22px;}

/* ACCOUNT DROPDOWN */
.acct-wrap{position:relative;}
.acct-icon{display:flex;flex-direction:column;align-items:center;font-size:11px;color:#cc0000;cursor:pointer;}
.acct-dropdown{display:none;position:absolute;top:calc(100% + 10px);right:0;background:#fff;border:1px solid #e0e0e0;border-radius:4px;min-width:160px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:9999;}
.acct-dropdown.open{display:block;}
.acct-dropdown a{display:block;padding:13px 20px;font-size:14px;color:#222;border-bottom:1px solid #f0f0f0;transition:background .15s;}
.acct-dropdown a:last-child{border-bottom:none;}
.acct-dropdown a:hover{background:#f5f7ff;color:#003da5;}

.header-icon-link{display:flex;flex-direction:column;align-items:center;font-size:11px;color:#333;cursor:pointer;position:relative;}
.header-icon-link:hover{color:#003da5;}
.header-icon-link svg{margin-bottom:3px;}
.badge{position:absolute;top:-4px;right:-8px;background:#003da5;color:#fff;border-radius:50%;width:17px;height:17px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:700;}

/* NAVBAR */
.navbar{background:#003da5;}
.navbar-inner{max-width:1400px;margin:0 auto;display:flex;align-items:stretch;}
.all-products-btn{background:#cc0000;color:#fff;padding:10px 18px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;cursor:pointer;border:none;white-space:nowrap;position:relative;}
.dropdown-menu{display:none;position:absolute;top:100%;left:0;background:#fff;min-width:240px;box-shadow:0 6px 20px rgba(0,0,0,.15);z-index:999;border-top:3px solid #cc0000;}
.dropdown-menu a{display:block;padding:11px 18px;font-size:13px;color:#222;border-bottom:1px solid #f0f0f0;}
.dropdown-menu a:hover{background:#f5f7ff;color:#003da5;}
.all-products-btn.open .dropdown-menu{display:block;}
.nav-links{display:flex;flex-wrap:wrap;align-items:center;padding:0 8px;gap:2px;}
.nav-links a{color:#fff;font-size:13px;padding:10px 12px;white-space:nowrap;transition:background .2s;border-radius:3px;}
.nav-links a:hover{background:rgba(255,255,255,.15);}
.nav-links a.laptop-sale{color:#ffd700;font-weight:800;}

/* HERO */
.page-hero{position:relative;height:180px;overflow:hidden;display:flex;align-items:center;justify-content:center;}
.page-hero-bg{position:absolute;inset:0;background:url('https://pcx.com.ph/cdn/shop/files/1800x600-Samsung-AI-Monitor.jpg?v=1765177814&width=1600') center/cover no-repeat;filter:brightness(.35);}
.page-hero-content{position:relative;z-index:2;text-align:center;color:#fff;}
.page-hero-content h1{font-size:34px;font-weight:900;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;}
.breadcrumb-bar{font-size:13px;color:rgba(255,255,255,.75);}
.breadcrumb-bar a{color:rgba(255,255,255,.75);}
.breadcrumb-bar a:hover{color:#fff;text-decoration:underline;}

/* BODY */
.page-body{max-width:1400px;margin:36px auto 80px;padding:0 24px;display:flex;gap:28px;align-items:flex-start;}

/* SIDEBAR */
.sidebar{width:280px;flex-shrink:0;border:1px solid #e0e0e0;border-radius:4px;overflow:hidden;background:#fff;}
.sidebar-item{display:flex;align-items:center;gap:10px;padding:14px 20px;font-size:14px;color:#333;border-bottom:1px solid #f0f0f0;cursor:pointer;transition:background .15s;text-decoration:none;}
.sidebar-item:last-child{border-bottom:none;}
.sidebar-item:hover{background:#f5f7ff;color:#003da5;}
.sidebar-item.active{background:#f5f7ff;color:#003da5;font-weight:600;}
.sidebar-item i{font-size:15px;width:18px;text-align:center;}

/* MAIN */
.main-content{flex:1;}
.hello-line{font-size:15px;color:#333;margin-bottom:22px;line-height:1.7;}
.hello-line strong{color:#003da5;font-weight:700;}
.hello-line a{color:#003da5;}
.hello-line a:hover{text-decoration:underline;}
.section-label{font-size:16px;font-weight:700;color:#111;margin-bottom:14px;}
.order-empty{display:flex;align-items:center;gap:10px;font-size:14px;color:#333;margin-bottom:32px;}
.green-check{width:20px;height:20px;background:#28a745;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;flex-shrink:0;}
.order-empty a{color:#003da5;font-weight:600;}
.order-empty a:hover{text-decoration:underline;}
.account-table{width:100%;border-collapse:collapse;border:1px solid #e0e0e0;margin-top:4px;}
.account-table td{padding:14px 18px;font-size:14px;border-bottom:1px solid #e0e0e0;vertical-align:top;}
.account-table td:first-child{font-weight:700;background:#fafafa;width:160px;}
.account-table tr:last-child td{border-bottom:none;}

/* Loading skeleton */
.skeleton{background:#e0e0e0;border-radius:4px;animation:pulse 1.4s ease-in-out infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}

/* FIXED */
.live-chat{position:fixed;bottom:30px;left:24px;background:#cc0000;color:#fff;padding:12px 20px;border-radius:30px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;cursor:pointer;z-index:999;box-shadow:0 4px 16px rgba(200,0,0,.4);}
.for-you-tab{position:fixed;right:0;top:50%;transform:translateY(-50%);background:#003da5;color:#fff;writing-mode:vertical-rl;padding:16px 8px;font-size:12px;font-weight:700;cursor:pointer;z-index:100;border-radius:4px 0 0 4px;letter-spacing:1px;}
.scroll-top{position:fixed;bottom:30px;right:50px;background:#fff;border:1px solid #ddd;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;color:#333;z-index:99;}
.scroll-top:hover{background:#003da5;color:#fff;}

/* auth guard overlay */
#authGuard{display:none;position:fixed;inset:0;background:#f5f5f5;z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:16px;font-size:15px;color:#555;}
#authGuard.show{display:flex;}

@media(max-width:768px){.page-body{flex-direction:column;}.sidebar{width:100%;}.nav-links{display:none;}}
</style>
</head>
<body>

<!-- Auth guard: shown briefly while Firebase resolves, then hides or redirects -->
<div id="authGuard" class="show">
    <div style="width:40px;height:40px;border:4px solid #003da5;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></div>
    <span>Loading your account…</span>
</div>
<style>@keyframes spin{to{transform:rotate(360deg);}}</style>

<!-- HEADER -->
<header class="header">
<div class="header-inner">
    <a href="home.php"><div class="logo-text"><span class="pc">PC</span><span class="ex">EXPRESS</span></div></a>
    <div class="search-bar">
        <select><option>All Categories</option><option>PC Components</option><option>Laptops</option><option>Desktop PCs</option></select>
        <input type="text" placeholder="Search for products">
        <button><i class="fas fa-search"></i></button>
    </div>
    <div class="header-actions">
        <div class="acct-wrap">
            <div class="acct-icon" onclick="document.getElementById('acctDrop').classList.toggle('open')">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#cc0000" stroke-width="1.8" style="margin-bottom:3px;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Account</span>
            </div>
            <div class="acct-dropdown" id="acctDrop">
                <a href="dashboard.php">Dashboard</a>
                <a href="addresses.php">Addresses</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="#" id="headerLogoutBtn" style="color:#cc0000;">Logout</a>
            </div>
        </div>
        <a href="wishlist.php" class="header-icon-link" title="Wishlist">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>Wishlist</span><span class="badge" id="wishBadge">0</span>
        </a>
        <a href="cart.php" class="header-icon-link" title="Cart">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span>Cart</span><span class="badge" id="cartBadge">0</span>
        </a>
    </div>
</div>
</header>

<!-- NAVBAR -->
<nav class="navbar">
<div class="navbar-inner">
    <div class="all-products-btn" onclick="this.classList.toggle('open')">
        <i class="fas fa-bars"></i> All Products <i class="fas fa-chevron-down" style="font-size:11px;"></i>
        <div class="dropdown-menu" onclick="event.stopPropagation()">
            <a href="#">PC Components</a><a href="#">Laptops</a>
            <a href="#">Desktop PCs</a><a href="#">Smartphones</a><a href="home.php">All Products</a>
        </div>
    </div>
    <div class="nav-links">
        <a href="#">Taiwan Excellence</a><a href="#">AMD</a><a href="#">Powered by ASUS</a>
        <a href="#">ASUS NUC</a><a href="#">NVIDIA</a><a href="#">Intel</a><a href="#">Microsoft</a>
        <a href="#">Lenovo</a><a href="#">Powered by MSI</a><a href="#">Samsung Monitors</a>
        <a href="#">Business Solutions</a><a href="#">↓ Pricelists</a>
        <a href="#" class="laptop-sale">🖥 Laptop Sale!</a>
        <a href="#">Product Spotlight</a><a href="#">News &amp; Promos ▾</a><a href="#">Info ▾</a>
    </div>
</div>
</nav>

<!-- HERO -->
<div class="page-hero">
    <div class="page-hero-bg"></div>
    <div class="page-hero-content">
        <h1>My Account</h1>
        <div class="breadcrumb-bar">
            <a href="home.php">Home</a> &rsaquo; MY ACCOUNT
        </div>
    </div>
</div>

<!-- BODY -->
<div class="page-body">

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-item active">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="addresses.php" class="sidebar-item">
            <i class="fas fa-map-marker-alt"></i> Addresses
            <span id="sidebarAddrCount" style="margin-left:auto;font-size:12px;color:#888;"></span>
        </a>
        <a href="wishlist.php" class="sidebar-item">
            <i class="fas fa-heart"></i> Wishlist
        </a>
        <a href="#" class="sidebar-item" id="sidebarLogoutBtn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main -->
    <div class="main-content">

        <!-- Hello line -->
        <p class="hello-line" id="helloLine">
            <span class="skeleton" style="display:inline-block;width:260px;height:18px;border-radius:4px;">&nbsp;</span>
        </p>

        <!-- Order History -->
        <div class="section-label">Order History:</div>
        <div id="orderHistoryContainer">
            <div style="padding:20px 0;">
                <div class="skeleton" style="height:40px;margin-bottom:8px;border-radius:4px;"></div>
                <div class="skeleton" style="height:40px;margin-bottom:8px;border-radius:4px;"></div>
                <div class="skeleton" style="height:40px;border-radius:4px;"></div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="section-label" style="margin-top:32px;">Account Details:</div>
        <table class="account-table" id="accountDetailsTable">
            <tr>
                <td>Name:</td>
                <td id="detailName"><span class="skeleton" style="display:inline-block;width:160px;height:16px;border-radius:4px;">&nbsp;</span></td>
            </tr>
            <tr>
                <td>E-mail:</td>
                <td id="detailEmail"><span class="skeleton" style="display:inline-block;width:200px;height:16px;border-radius:4px;">&nbsp;</span></td>
            </tr>
        </table>

    </div>
</div>

<div class="live-chat">💬 Live chat</div>
<div class="for-you-tab">For You</div>
<div class="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">▲</div>

<script type="module">
import { auth, db } from "./firebase-config.js";
import { signOut, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
import {
  doc, getDoc, collection, getDocs, query, where, orderBy, limit
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

// ── Logout helper ────────────────────────────────────────────
async function doLogout() {
    try { await signOut(auth); } catch(e) {}
    window.location.href = "home.php";
}
document.getElementById("headerLogoutBtn").addEventListener("click", e => { e.preventDefault(); doLogout(); });
document.getElementById("sidebarLogoutBtn").addEventListener("click", e => { e.preventDefault(); doLogout(); });

// ── Close dropdowns on outside click ────────────────────────
document.addEventListener("click", e => {
    const w = document.querySelector(".acct-wrap");
    const d = document.getElementById("acctDrop");
    if (w && d && !w.contains(e.target)) d.classList.remove("open");
    const btn = document.querySelector(".all-products-btn");
    if (btn && !btn.contains(e.target)) btn.classList.remove("open");
});

// ── Format currency ──────────────────────────────────────────
function fmt(n) {
    return "₱" + parseFloat(n || 0).toLocaleString("en-PH", { minimumFractionDigits: 2 });
}

// ── Render order history ─────────────────────────────────────
async function renderOrders(uid) {
    const container = document.getElementById("orderHistoryContainer");
    try {
        const q = query(
            collection(db, "orders"),
            where("userId", "==", uid),
            orderBy("createdAt", "desc"),
            limit(10)
        );
        const snap = await getDocs(q);

        if (snap.empty) {
            container.innerHTML = `
            <div class="order-empty">
                <div class="green-check">✓</div>
                <span><a href="home.php">Make your first order</a>&nbsp;&nbsp;You haven't placed any orders yet.</span>
            </div>`;
            return;
        }

        let rows = "";
        snap.forEach(d => {
            const o = d.data();
            const date = o.createdAt ? new Date(o.createdAt.toDate()).toLocaleDateString("en-PH", { year:"numeric", month:"short", day:"numeric" }) : "—";
            const statusColor = { completed:"#28a745", processing:"#0d47a1", cancelled:"#cc0000" }[o.status] || "#666";
            rows += `<tr>
                <td>${o.orderNumber || "—"}</td>
                <td>${date}</td>
                <td>${fmt(o.grandTotal)}</td>
                <td><span style="text-transform:capitalize;color:${statusColor};font-weight:600;">${o.status || "pending"}</span></td>
                <td><a href="order_confirmation.php?order=${encodeURIComponent(o.orderNumber || d.id)}" style="color:#003da5;font-weight:600;font-size:13px;">View →</a></td>
            </tr>`;
        });

        container.innerHTML = `
        <table class="account-table" style="margin-bottom:24px;">
            <thead><tr style="background:#f0f4ff;">
                <td style="font-weight:700;">Order #</td>
                <td style="font-weight:700;">Date</td>
                <td style="font-weight:700;">Total</td>
                <td style="font-weight:700;">Status</td>
                <td style="font-weight:700;">Action</td>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table>`;

    } catch (err) {
        console.error("renderOrders error:", err);
        container.innerHTML = `<p style="color:#888;font-size:14px;">Could not load orders. Please refresh.</p>`;
    }
}

// ── Render address count in sidebar ─────────────────────────
async function renderAddrCount(uid) {
    try {
        const snap = await getDocs(collection(db, "userAddresses", uid, "addresses"));
        const count = snap.size;
        if (count > 0) {
            document.getElementById("sidebarAddrCount").textContent = `(${count})`;
        }
    } catch (_) {}
}

// ── Render wishlist badge ─────────────────────────────────────
async function renderWishBadge(uid) {
    try {
        const snap = await getDocs(collection(db, "wishlists", uid, "items"));
        const count = snap.size;
        if (count > 0) {
            document.getElementById("wishBadge").textContent = count;
        }
    } catch (_) {}
}

// ── Main auth listener ────────────────────────────────────────
onAuthStateChanged(auth, async (user) => {
    const guard = document.getElementById("authGuard");

    if (!user) {
        // Not logged in → redirect to home
        window.location.href = "home.php";
        return;
    }

    // Hide guard overlay
    guard.classList.remove("show");

    // Load Firestore profile
    let firstName = "", lastName = "", email = user.email;
    try {
        const snap = await getDoc(doc(db, "users", user.uid));
        if (snap.exists()) {
            const data = snap.data();
            firstName = data.firstName || "";
            lastName  = data.lastName  || "";
            email     = data.email     || user.email;
        }
    } catch (err) {
        console.error("Profile load error:", err);
    }

    const fullName = `${firstName} ${lastName}`.trim() || email;

    // Update hello line
    document.getElementById("helloLine").innerHTML = `
        Hello <strong>${fullName}</strong>! ✨<br>
        Here's a summary of your recent activity. <a href="addresses.php">Manage addresses</a> or <a href="wishlist.php">view wishlist</a>.
    `;

    // Update account details
    document.getElementById("detailName").textContent = fullName;
    document.getElementById("detailEmail").textContent = email;

    // Load orders, address count, and wishlist
    await renderOrders(user.uid);
    await renderAddrCount(user.uid);
    await renderWishBadge(user.uid);
});
</script>
</body>
</html>
