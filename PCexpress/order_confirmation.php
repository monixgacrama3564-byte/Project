<?php
// order_confirmation.php — Firestore version (no MySQL needed)
$orderNumber = htmlspecialchars($_GET['order'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Order Confirmed – PC Express</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f5f5;color:#222;min-height:100vh;}
a{text-decoration:none;color:inherit;}
.header{background:#fff;border-bottom:1px solid #ddd;padding:14px 32px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 6px rgba(0,0,0,.07);}
.logo-text{font-size:26px;font-weight:900;letter-spacing:-1px;}
.logo-text span.pc{color:#003da5;} .logo-text span.ex{color:#e00;}
.page-wrap{max-width:680px;margin:48px auto 80px;padding:0 24px;}
.confirm-hero{text-align:center;padding:40px 24px 32px;background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:24px;}
.check-circle{width:72px;height:72px;border-radius:50%;background:#e8f5e9;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;animation:pop .5s cubic-bezier(.4,0,.2,1);}
.check-circle i{font-size:34px;color:#28a745;}
@keyframes pop{0%{transform:scale(.5);opacity:0;}70%{transform:scale(1.1);}100%{transform:scale(1);opacity:1;}}
.confirm-title{font-size:26px;font-weight:800;color:#111;margin-bottom:8px;}
.confirm-sub{font-size:14px;color:#555;line-height:1.7;}
.order-num-badge{display:inline-block;background:#f0f4ff;border:1.5px solid #b0c2f7;border-radius:50px;padding:6px 20px;font-size:14px;font-weight:700;color:#003da5;margin-top:14px;letter-spacing:.5px;}
.card{background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;margin-bottom:20px;}
.card-header{background:#f8f8f8;border-bottom:1px solid #eee;padding:14px 20px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#555;}
.card-body{padding:20px;}
.order-item{display:flex;gap:14px;align-items:center;padding-bottom:14px;margin-bottom:14px;border-bottom:1px solid #f0f0f0;}
.order-item:last-child{border-bottom:none;padding-bottom:0;margin-bottom:0;}
.oi-img{width:60px;height:60px;object-fit:contain;border:1px solid #eee;border-radius:6px;background:#fafafa;flex-shrink:0;}
.oi-name{font-size:14px;font-weight:600;color:#222;line-height:1.4;margin-bottom:3px;}
.oi-variant{font-size:12px;color:#888;}
.oi-qty{font-size:12px;color:#888;margin-top:2px;}
.oi-price{font-size:14px;font-weight:700;color:#111;white-space:nowrap;margin-left:auto;}
.total-row{display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;}
.total-row:last-child{margin-bottom:0;}
.total-row.grand{font-size:17px;font-weight:800;color:#111;padding-top:10px;border-top:1px solid #e0e0e0;margin-top:10px;}
.total-label{color:#555;}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.detail-item label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa;display:block;margin-bottom:4px;}
.detail-item p{font-size:14px;color:#222;line-height:1.5;}
.action-row{display:flex;gap:12px;justify-content:center;margin-top:28px;flex-wrap:wrap;}
.btn-primary{padding:13px 28px;background:#003da5;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;transition:background .2s;}
.btn-primary:hover{background:#0030a0;}
.btn-outline{padding:13px 28px;background:#fff;color:#003da5;border:2px solid #003da5;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;}
.btn-outline:hover{background:#f0f4ff;}
.status-badge{display:inline-block;padding:3px 12px;border-radius:50px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.status-badge.pending{background:#fff8e1;color:#e65100;border:1px solid #ffe082;}
.status-badge.processing{background:#e3f2fd;color:#0d47a1;border:1px solid #90caf9;}
.status-badge.completed{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;}
.loading{text-align:center;padding:40px;color:#888;font-size:14px;}
@media(max-width:600px){.detail-grid{grid-template-columns:1fr;}.action-row{flex-direction:column;}.btn-primary,.btn-outline{width:100%;text-align:center;}}
</style>
</head>
<body>

<header class="header">
  <a href="Home.php">
    <div class="logo-text"><span class="pc">PC</span><span class="ex">EXPRESS</span></div>
  </a>
  <a href="dashboard.php" id="myAccountLink" style="display:none;font-size:13px;color:#003da5;font-weight:600;">My Account</a>
</header>

<div class="page-wrap">

  <!-- Hero -->
  <div class="confirm-hero">
    <div class="check-circle"><i class="fas fa-check"></i></div>
    <div class="confirm-title">Thank you for your order!</div>
    <div class="confirm-sub">
      Your order has been received and is being processed.<br>
      <span id="confirmEmail"></span>
    </div>
    <?php if($orderNumber): ?>
    <div class="order-num-badge">Order #<?= $orderNumber ?></div>
    <?php endif; ?>
  </div>

  <!-- Dynamic content loaded from Firestore -->
  <div id="orderContent">
    <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading your order details...</div>
  </div>

  <!-- Actions -->
  <div class="action-row">
    <a href="Home.php"><button class="btn-primary"><i class="fas fa-shopping-bag"></i> Continue Shopping</button></a>
    <a href="dashboard.php" id="myAccountBtn" style="display:none;"><button class="btn-outline"><i class="fas fa-th-large"></i> My Account</button></a>
  </div>

</div>

<script type="module">
import { auth, db } from "./firebase-config.js";
import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
import { collection, getDocs, query, where, limit } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

const orderNumber = "<?= $orderNumber ?>";
const fmt = n => '₱' + Number(n).toLocaleString('en-PH', {minimumFractionDigits:2});

async function loadOrder(user) {
  const content = document.getElementById('orderContent');

  if (!orderNumber) {
    content.innerHTML = fallbackHtml();
    return;
  }

  try {
    // Find order by orderNumber field
    const q = query(
      collection(db, "orders"),
      where("orderNumber", "==", orderNumber),
      limit(1)
    );
    const snap = await getDocs(q);

    if (snap.empty) {
      content.innerHTML = fallbackHtml();
      return;
    }

    const orderDoc = snap.docs[0];
    const o = orderDoc.data();

    // Show my account link for logged-in users
    if (user) {
      document.getElementById('myAccountLink').style.display = 'block';
      document.getElementById('myAccountBtn').style.display  = 'block';
    }

    // Show email confirmation line
    const email = o.guestEmail || user?.email || '';
    if (email) {
      document.getElementById('confirmEmail').innerHTML =
        `A confirmation will be sent to <strong>${email}</strong>.`;
    }

    // Load order items subcollection
    const itemsSnap = await getDocs(collection(db, "orders", orderDoc.id, "items"));
    const items = itemsSnap.docs.map(d => d.data());

    // Build HTML
    let html = '';

    // Items
    if (items.length > 0) {
      html += `<div class="card">
        <div class="card-header">Items Ordered</div>
        <div class="card-body">
          ${items.map(item => `
            <div class="order-item">
              <img src="${item.productImage || 'https://via.placeholder.com/60'}"
                   class="oi-img" alt="${item.productName}"
                   onerror="this.src='https://via.placeholder.com/60'">
              <div style="flex:1;">
                <div class="oi-name">${item.productName}</div>
                ${item.variant ? `<div class="oi-variant">${item.variant}</div>` : ''}
                <div class="oi-qty">Qty: ${item.qty}</div>
              </div>
              <div class="oi-price">${fmt(item.lineTotal)}</div>
            </div>`).join('')}
        </div>
      </div>`;
    }

    // Totals
    html += `<div class="card">
      <div class="card-header">Order Total</div>
      <div class="card-body">
        <div class="total-row"><span class="total-label">Subtotal</span><span>${fmt(o.subtotal)}</span></div>
        ${o.discount > 0 ? `<div class="total-row"><span class="total-label">Discount${o.couponCode ? ' ('+o.couponCode+')' : ''}</span><span style="color:#28a745;">-${fmt(o.discount)}</span></div>` : ''}
        <div class="total-row"><span class="total-label">Shipping</span><span>${o.shippingFee == 0 ? '<span style="color:#28a745;">Free</span>' : fmt(o.shippingFee)}</span></div>
        <div class="total-row grand"><span>Total</span><span>${fmt(o.grandTotal)}</span></div>
      </div>
    </div>`;

    // Order details
    const date = o.createdAt?.toDate ? o.createdAt.toDate().toLocaleDateString('en-PH', {year:'numeric',month:'long',day:'numeric'}) : 'Just now';
    html += `<div class="card">
      <div class="card-header">Order Details</div>
      <div class="card-body">
        <div class="detail-grid">
          <div class="detail-item"><label>Order Number</label><p>${o.orderNumber}</p></div>
          <div class="detail-item"><label>Status</label><p><span class="status-badge ${o.status}">${o.status}</span></p></div>
          <div class="detail-item"><label>Payment Method</label><p>${(o.paymentMethod||'').toUpperCase()}</p></div>
          <div class="detail-item"><label>Date</label><p>${date}</p></div>
          ${o.mode === 'ship' ? `
          <div class="detail-item" style="grid-column:1/-1;">
            <label>Shipping Address</label>
            <p>${o.shipFirstName} ${o.shipLastName}<br>
               ${o.shipAddress}${o.shipApartment ? ', '+o.shipApartment : ''}<br>
               ${o.shipCity}${o.shipProvince ? ', '+o.shipProvince : ''}${o.shipPostalCode ? ' '+o.shipPostalCode : ''}<br>
               ${o.shipCountry}</p>
          </div>` : `
          <div class="detail-item" style="grid-column:1/-1;">
            <label>Pickup Location</label>
            <p>${o.pickupStore || 'Store pickup'}</p>
          </div>`}
          ${o.orderNote ? `<div class="detail-item" style="grid-column:1/-1;"><label>Order Note</label><p>${o.orderNote}</p></div>` : ''}
        </div>
      </div>
    </div>`;

    content.innerHTML = html;

  } catch (err) {
    console.error('loadOrder:', err);
    content.innerHTML = fallbackHtml();
  }
}

function fallbackHtml() {
  return `<div class="card">
    <div class="card-body" style="text-align:center;padding:32px;color:#555;font-size:14px;line-height:1.8;">
      <i class="fas fa-envelope" style="font-size:32px;color:#003da5;display:block;margin-bottom:12px;"></i>
      We've received your order and will send a confirmation to your email shortly.<br>
      If you have any questions, please contact our support team.
    </div>
  </div>`;
}

onAuthStateChanged(auth, user => {
  loadOrder(user);
});
</script>

<script type="module" src="auth.js"></script>
</body>
</html>