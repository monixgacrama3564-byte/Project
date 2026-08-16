<?php
// checkout.php
session_start();

require_once __DIR__ . '/config.php';
$pdo = getDB();

$isLoggedIn = isset($_SESSION['user_id']);
$uid = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$sessionEmail = $_SESSION['user_email'] ?? '';
$sessionName  = $_SESSION['user_name']  ?? '';

// Fetch default address if logged in
$defaultAddr = null;
if($isLoggedIn && $pdo){
    try{
        $s=$pdo->prepare("SELECT * FROM user_addresses WHERE user_id=? AND is_default=1 LIMIT 1");
        $s->execute([$uid]);
        $defaultAddr=$s->fetch();
        if(!$defaultAddr){
            $s=$pdo->prepare("SELECT * FROM user_addresses WHERE user_id=? ORDER BY id ASC LIMIT 1");
            $s->execute([$uid]);
            $defaultAddr=$s->fetch();
        }
    }catch(Exception $e){}
}

// Philippines provinces
$provinces=['Abra','Agusan del Norte','Agusan del Sur','Aklan','Albay','Antique','Apayao','Aurora','Basilan','Bataan','Batanes','Batangas','Benguet','Biliran','Bohol','Bukidnon','Bulacan','Cagayan','Camarines Norte','Camarines Sur','Camiguin','Capiz','Catanduanes','Cavite','Cebu','Compostela Valley','Cotabato','Davao del Norte','Davao del Sur','Davao Occidental','Davao Oriental','Dinagat Islands','Eastern Samar','Guimaras','Ifugao','Ilocos Norte','Ilocos Sur','Iloilo','Isabela','Kalinga','La Union','Laguna','Lanao del Norte','Lanao del Sur','Leyte','Maguindanao','Marinduque','Masbate','Metro Manila','Misamis Occidental','Misamis Oriental','Mountain Province','Negros Occidental','Negros Oriental','Northern Samar','Nueva Ecija','Nueva Vizcaya','Occidental Mindoro','Oriental Mindoro','Palawan','Pampanga','Pangasinan','Quezon','Quirino','Rizal','Romblon','Samar','Sarangani','Siquijor','Sorsogon','South Cotabato','Southern Leyte','Sultan Kudarat','Sulu','Surigao del Norte','Surigao del Sur','Tarlac','Tawi-Tawi','Zambales','Zamboanga del Norte','Zamboanga del Sur','Zamboanga Sibugay'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Checkout – PC Express</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f5f5;color:#222;min-height:100vh;}
a{text-decoration:none;color:inherit;}

/* ── HEADER ── */
.checkout-header{background:#fff;border-bottom:1px solid #ddd;padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 6px rgba(0,0,0,.07);}
.logo-text{font-size:26px;font-weight:900;letter-spacing:-1px;}
.logo-text span.pc{color:#003da5;} .logo-text span.ex{color:#e00;}
.header-lock{display:flex;align-items:center;gap:7px;font-size:13px;color:#888;}
.header-lock i{font-size:16px;color:#003da5;}

/* ── BREADCRUMB ── */
.checkout-steps{background:#fff;border-bottom:1px solid #eee;padding:10px 0;}
.steps-inner{max-width:1100px;margin:0 auto;padding:0 32px;display:flex;align-items:center;gap:6px;font-size:13px;color:#aaa;}
.steps-inner a{color:#003da5;}
.steps-inner a:hover{text-decoration:underline;}
.steps-inner .sep{color:#ccc;}
.steps-inner .active-step{color:#222;font-weight:600;}

/* ── LAYOUT ── */
.checkout-body{max-width:1100px;margin:0 auto;padding:32px 32px 80px;display:grid;grid-template-columns:1fr 380px;gap:40px;align-items:flex-start;}

/* ── LEFT PANEL ── */
.left-panel{}

/* Contact */
.section-title{font-size:20px;font-weight:700;color:#111;margin-bottom:16px;}
.contact-row{background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
.contact-email{display:flex;align-items:center;gap:12px;font-size:14px;color:#333;}
.user-avatar{width:34px;height:34px;border-radius:50%;background:#003da5;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0;}
.contact-change{font-size:13px;color:#003da5;cursor:pointer;display:flex;align-items:center;gap:10px;}
.contact-change:hover{text-decoration:underline;}
.contact-dots{color:#aaa;font-size:18px;cursor:pointer;line-height:1;}

/* Ship/Pickup toggle */
.ship-toggle{display:flex;border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;margin-bottom:24px;background:#fff;}
.toggle-btn{flex:1;padding:13px;display:flex;align-items:center;justify-content:center;gap:8px;font-size:14px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#555;transition:all .2s;}
.toggle-btn.active{background:#003da5;color:#fff;}
.toggle-btn i{font-size:16px;}

/* Form fields */
.form-section{background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:24px;margin-bottom:20px;}
.form-section-title{font-size:14px;font-weight:700;color:#111;margin-bottom:16px;letter-spacing:.3px;text-transform:uppercase;}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
.field-row.three{grid-template-columns:1fr 1fr 1fr;}
.field-group{display:flex;flex-direction:column;position:relative;}
.field-group.full{grid-column:1/-1;}
.field-label{font-size:12px;font-weight:600;color:#888;margin-bottom:5px;letter-spacing:.3px;}
.field-input{width:100%;padding:13px 14px;border:1.5px solid #ddd;border-radius:6px;font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;background:#fff;color:#222;}
.field-input:focus{border-color:#003da5;box-shadow:0 0 0 3px rgba(0,61,165,.1);}
.field-input::placeholder{color:#bbb;}
.field-input.invalid{border-color:#c62828!important;box-shadow:0 0 0 3px rgba(198,40,40,.1)!important;}
.field-error-msg{font-size:11px;color:#c62828;margin-top:4px;display:none;}
.field-error-msg.show{display:block;}
.field-select{appearance:none;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23888' d='M6 8L0 0h12z'/%3E%3C/svg%3E") no-repeat right 14px center;padding-right:36px;cursor:pointer;}

/* Shipping method */
.shipping-placeholder{border:1.5px dashed #ddd;border-radius:6px;padding:18px 20px;font-size:13px;color:#999;text-align:center;}
.shipping-option{border:1.5px solid #ddd;border-radius:6px;padding:14px 18px;display:flex;align-items:center;gap:14px;cursor:pointer;transition:border-color .2s;margin-bottom:10px;}
.shipping-option:last-child{margin-bottom:0;}
.shipping-option.selected{border-color:#003da5;background:#f5f8ff;}
.shipping-option input[type=radio]{accent-color:#003da5;width:16px;height:16px;}
.ship-label{flex:1;font-size:14px;font-weight:600;color:#222;}
.ship-eta{font-size:12px;color:#888;font-weight:400;margin-top:2px;}
.ship-price{font-size:14px;font-weight:700;color:#003da5;}
.ship-price.free{color:#28a745;}

/* Payment */
.payment-options{display:flex;flex-direction:column;gap:10px;}
.pay-option{border:1.5px solid #ddd;border-radius:6px;padding:14px 18px;display:flex;align-items:center;gap:14px;cursor:pointer;transition:border-color .2s;}
.pay-option.selected{border-color:#003da5;background:#f5f8ff;}
.pay-option input[type=radio]{accent-color:#003da5;width:16px;height:16px;}
.pay-label{flex:1;}
.pay-name{font-size:14px;font-weight:600;color:#222;}
.pay-sub{font-size:12px;color:#888;margin-top:2px;}
.pay-icon{width:36px;height:24px;border:1px solid #e0e0e0;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#003da5;background:#fff;}

/* COD card fields */
.cod-note{background:#fff8e1;border:1px solid #ffe082;border-radius:5px;padding:12px 16px;font-size:13px;color:#795548;margin-top:10px;display:none;}
.cod-note.show{display:block;}

/* Submit */
.submit-btn{width:100%;padding:16px;background:#003da5;color:#fff;border:none;border-radius:6px;font-size:16px;font-weight:700;cursor:pointer;transition:background .2s;letter-spacing:.3px;display:flex;align-items:center;justify-content:center;gap:10px;margin-top:8px;}
.submit-btn:hover{background:#0030a0;}
.submit-btn:disabled{background:#9ab0da;cursor:not-allowed;}
.submit-btn i{font-size:15px;}

.back-link{display:flex;align-items:center;gap:6px;font-size:13px;color:#003da5;margin-top:16px;cursor:pointer;}
.back-link:hover{text-decoration:underline;}

/* ── RIGHT PANEL: ORDER SUMMARY ── */
.order-summary{background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;position:sticky;top:80px;}
.summary-header{background:#f8f8f8;border-bottom:1px solid #eee;padding:16px 20px;font-size:14px;font-weight:700;color:#111;text-transform:uppercase;letter-spacing:.5px;}
.summary-items{padding:16px 20px;max-height:320px;overflow-y:auto;}
.summary-item{display:flex;gap:14px;margin-bottom:14px;align-items:flex-start;}
.summary-item:last-child{margin-bottom:0;}
.item-img-wrap{position:relative;flex-shrink:0;}
.item-img{width:64px;height:64px;object-fit:contain;border:1px solid #eee;border-radius:6px;background:#fafafa;}
.item-qty-badge{position:absolute;top:-6px;right:-6px;background:#555;color:#fff;border-radius:50%;width:20px;height:20px;font-size:11px;display:flex;align-items:center;justify-content:center;font-weight:700;}
.item-info{flex:1;}
.item-name{font-size:13px;font-weight:600;color:#222;line-height:1.4;margin-bottom:4px;}
.item-variant{font-size:12px;color:#888;}
.item-price{font-size:14px;font-weight:700;color:#111;white-space:nowrap;}
.item-price-wrap{display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;}
.item-remove-btn{background:none;border:none;cursor:pointer;color:#bbb;font-size:12px;padding:2px 4px;border-radius:4px;transition:color .2s,background .2s;line-height:1;}
.item-remove-btn:hover{color:#c62828;background:#fdecea;}

.summary-divider{height:1px;background:#f0f0f0;margin:0 20px;}

.summary-totals{padding:16px 20px;}
.total-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:14px;}
.total-row.grand{font-size:18px;font-weight:800;color:#111;margin-top:6px;padding-top:10px;border-top:1px solid #e0e0e0;}
.total-label{color:#555;}
.total-val{color:#222;font-weight:600;}
.total-val.free{color:#28a745;}
.total-val.muted{color:#aaa;font-style:italic;font-weight:400;font-size:13px;}
.total-val.currency{font-size:12px;font-weight:400;color:#888;margin-right:2px;}

/* Coupon */
.coupon-row{display:flex;gap:8px;padding:0 20px 16px;}
.coupon-input{flex:1;padding:10px 14px;border:1.5px solid #ddd;border-radius:6px;font-size:13px;outline:none;font-family:inherit;}
.coupon-input:focus{border-color:#003da5;}
.coupon-btn{background:#222;color:#fff;border:none;padding:10px 18px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;transition:background .2s;white-space:nowrap;}
.coupon-btn:hover{background:#003da5;}

/* Pickup tab content */
.pickup-content{display:none;}
.pickup-content.show{display:block;}
.ship-content{display:block;}
.ship-content.hide{display:none;}

/* Alert */
.checkout-alert{padding:12px 16px;border-radius:6px;font-size:13px;font-weight:600;margin-bottom:16px;display:none;}
.checkout-alert.show{display:block;}
.checkout-alert.success{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;}
.checkout-alert.error{background:#fdecea;color:#c62828;border:1px solid #ef9a9a;}

/* Empty cart */
.empty-cart-msg{text-align:center;padding:40px 20px;color:#888;font-size:14px;}
.empty-cart-msg i{font-size:40px;color:#ddd;display:block;margin-bottom:12px;}
.empty-cart-msg a{color:#003da5;font-weight:700;}

/* Pickup stores */
.store-option{border:1.5px solid #ddd;border-radius:6px;padding:14px 18px;cursor:pointer;margin-bottom:10px;transition:border-color .2s;}
.store-option.selected{border-color:#003da5;background:#f5f8ff;}
.store-option:last-child{margin-bottom:0;}
.store-name{font-size:14px;font-weight:700;color:#222;}
.store-addr{font-size:12px;color:#777;margin-top:4px;}
.store-hours{font-size:12px;color:#28a745;margin-top:2px;}

@media(max-width:900px){
    .checkout-body{grid-template-columns:1fr;}
    .order-summary{position:static;}
    .field-row{grid-template-columns:1fr;}
    .field-row.three{grid-template-columns:1fr 1fr;}
}
@media(max-width:600px){
    .checkout-body{padding:20px 16px 60px;}
    .field-row.three{grid-template-columns:1fr;}
    .checkout-header{padding:12px 16px;}
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="checkout-header">
    <a href="home.php">
        <div class="logo-text"><span class="pc">PC</span><span class="ex">EXPRESS</span></div>
    </a>
    <div class="header-lock">
        <i class="fas fa-lock"></i> Secure Checkout
    </div>
</header>

<!-- BREADCRUMB STEPS -->
<div class="checkout-steps">
    <div class="steps-inner">
        <a href="home.php">Cart</a>
        <span class="sep">›</span>
        <span class="active-step">Information</span>
        <span class="sep">›</span>
        <span>Shipping</span>
        <span class="sep">›</span>
        <span>Payment</span>
    </div>
</div>

<!-- CHECKOUT BODY -->
<div class="checkout-body">

    <!-- ── LEFT PANEL ── -->
    <div class="left-panel">

        <div id="checkoutAlert" class="checkout-alert"></div>

        <!-- Contact -->
<div class="section-title">Contact</div>

<!-- Shown when Firebase user is logged in (hidden until auth resolves) -->
<div class="contact-row" id="contactRow" style="display:none;">
    <div class="contact-email">
        <div class="user-avatar" id="userAvatar">?</div>
        <span id="contactEmail"></span>
    </div>
    <div class="contact-change">
        <a href="#" onclick="doLogout();return false;" style="color:#003da5;font-size:13px;">Change</a>
        <span class="contact-dots" title="Options">···</span>
    </div>
</div>

<!-- Shown when guest (hidden once Firebase auth resolves to a logged-in user) -->
<div id="guestEmailSection" class="form-section" style="margin-bottom:24px;">
    <div class="field-group">
        <label class="field-label">Email or mobile phone number</label>
        <input type="email" id="guestEmail" class="field-input" placeholder="Email address">
    </div>
    <div style="margin-top:10px;font-size:13px;color:#555;">
        Already have an account? <a href="#" onclick="openPanel('login');return false;" style="color:#003da5;font-weight:600;">Log in</a>
    </div>
</div>

        <!-- Ship / Pickup Toggle -->
        <div class="ship-toggle">
            <button class="toggle-btn active" id="btnShip" onclick="setMode('ship')">
                <i class="fas fa-truck"></i> Ship
            </button>
            <button class="toggle-btn" id="btnPickup" onclick="setMode('pickup')">
                <i class="fas fa-store"></i> Pickup
            </button>
        </div>

        <!-- ── SHIP MODE ── -->
        <div id="shipContent" class="ship-content">
            <div class="form-section">
                <div class="form-section-title">Shipping Address</div>

                <!-- Country -->
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Country / Region</label>
                        <select id="fCountry" class="field-input field-select">
                            <option value="Philippines" selected>Philippines</option>
                        </select>
                    </div>
                </div>

                <!-- Name -->
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">First name</label>
                        <input type="text" id="fFirst" class="field-input" placeholder="First name"
                               value="<?= htmlspecialchars($defaultAddr['first_name'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Last name</label>
                        <input type="text" id="fLast" class="field-input" placeholder="Last name"
                               value="<?= htmlspecialchars($defaultAddr['last_name'] ?? '') ?>">
                    </div>
                </div>

                <!-- Company -->
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Company (optional)</label>
                        <input type="text" id="fCompany" class="field-input" placeholder="Company"
                               value="<?= htmlspecialchars($defaultAddr['company'] ?? '') ?>">
                    </div>
                </div>

                <!-- Address -->
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Address</label>
                        <input type="text" id="fAddress" class="field-input" placeholder="Street address"
                               value="<?= htmlspecialchars($defaultAddr['address'] ?? '') ?>">
                    </div>
                </div>

                <!-- Apartment -->
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Apartment, suite, etc. (optional)</label>
                        <input type="text" id="fApartment" class="field-input" placeholder="Apartment, unit, floor…"
                               value="<?= htmlspecialchars($defaultAddr['apartment'] ?? '') ?>">
                    </div>
                </div>

                <!-- Postal / City -->
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Postal code</label>
                        <input type="text" id="fPostal" class="field-input" placeholder="Postal code"
                               value="<?= htmlspecialchars($defaultAddr['postal_code'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label class="field-label">City</label>
                        <input type="text" id="fCity" class="field-input" placeholder="City"
                               value="<?= htmlspecialchars($defaultAddr['city'] ?? '') ?>">
                    </div>
                </div>

                <!-- Province -->
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Province</label>
                        <select id="fProvince" class="field-input field-select">
                            <?php foreach($provinces as $prov): ?>
                            <option value="<?= htmlspecialchars($prov) ?>"
                                <?= (($defaultAddr['province'] ?? '') === $prov) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prov) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Phone -->
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Phone</label>
                        <input type="tel" id="fPhone" class="field-input" placeholder="+63 9XX XXX XXXX"
                               value="<?= htmlspecialchars($defaultAddr['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Shipping Method -->
            <div class="form-section">
                <div class="form-section-title">Shipping Method</div>
                <div id="shippingMethods">
                    <div class="shipping-placeholder" id="shippingPlaceholder">
                        <i class="fas fa-map-marker-alt" style="display:block;font-size:22px;margin-bottom:8px;color:#ddd;"></i>
                        Enter your shipping address to view available shipping methods.
                    </div>
                    <div id="shippingOptions" style="display:none;">
                        <label class="shipping-option selected" onclick="selectShipping(this,'standard',150)">
                            <input type="radio" name="shipping_method" value="standard" checked>
                            <div class="ship-label">
                                Standard Delivery
                                <div class="ship-eta">3–5 business days</div>
                            </div>
                            <span class="ship-price">₱150.00</span>
                        </label>
                        <label class="shipping-option" onclick="selectShipping(this,'express',350)">
                            <input type="radio" name="shipping_method" value="express">
                            <div class="ship-label">
                                Express Delivery
                                <div class="ship-eta">1–2 business days</div>
                            </div>
                            <span class="ship-price">₱350.00</span>
                        </label>
                        <label class="shipping-option" onclick="selectShipping(this,'overnight',600)">
                            <input type="radio" name="shipping_method" value="overnight">
                            <div class="ship-label">
                                Overnight Delivery
                                <div class="ship-eta">Next business day</div>
                            </div>
                            <span class="ship-price">₱600.00</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── PICKUP MODE ── -->
        <div id="pickupContent" class="pickup-content">
            <div class="form-section">
                <div class="form-section-title">Select Pickup Store</div>
                <div class="store-option selected" onclick="selectStore(this,'cebu-main')">
                    <div class="store-name">PC Express – Cebu Main (Ayala Center Cebu)</div>
                    <div class="store-addr">Ayala Center Cebu, Cebu Business Park, Cebu City</div>
                    <div class="store-hours">Open today: 10:00 AM – 9:00 PM</div>
                </div>
                <div class="store-option" onclick="selectStore(this,'sm-cebu')">
                    <div class="store-name">PC Express – SM City Cebu</div>
                    <div class="store-addr">SM City Cebu, North Reclamation Area, Cebu City</div>
                    <div class="store-hours">Open today: 10:00 AM – 9:00 PM</div>
                </div>
                <div class="store-option" onclick="selectStore(this,'it-park')">
                    <div class="store-name">PC Express – IT Park Lahug</div>
                    <div class="store-addr">Cybergate Alpha, Lahug, Cebu City</div>
                    <div class="store-hours">Open today: 9:00 AM – 8:00 PM</div>
                </div>
            </div>
            <div class="form-section">
                <div class="form-section-title">Pickup Contact</div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">First name</label>
                        <input type="text" id="pFirst" class="field-input" placeholder="First name"
                               value="<?= htmlspecialchars($defaultAddr['first_name'] ?? '') ?>">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Last name</label>
                        <input type="text" id="pLast" class="field-input" placeholder="Last name"
                               value="<?= htmlspecialchars($defaultAddr['last_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group full">
                        <label class="field-label">Phone</label>
                        <input type="tel" id="pPhone" class="field-input" placeholder="+63 9XX XXX XXXX"
                               value="<?= htmlspecialchars($defaultAddr['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="form-section">
            <div class="form-section-title">Payment Method</div>
            <div class="payment-options">
                <label class="pay-option selected" onclick="selectPay(this,'cod')">
                    <input type="radio" name="payment" value="cod" checked>
                    <div class="pay-label">
                        <div class="pay-name">Cash on Delivery</div>
                        <div class="pay-sub">Pay when your order arrives</div>
                    </div>
                    <div class="pay-icon">COD</div>
                </label>
                <label class="pay-option" onclick="selectPay(this,'gcash')">
                    <input type="radio" name="payment" value="gcash">
                    <div class="pay-label">
                        <div class="pay-name">GCash</div>
                        <div class="pay-sub">Digital wallet payment</div>
                    </div>
                    <div class="pay-icon" style="color:#007bff;">G</div>
                </label>
                <label class="pay-option" onclick="selectPay(this,'bank')">
                    <input type="radio" name="payment" value="bank">
                    <div class="pay-label">
                        <div class="pay-name">Bank Transfer</div>
                        <div class="pay-sub">BDO, BPI, UnionBank, Metrobank</div>
                    </div>
                    <div class="pay-icon" style="color:#28a745;">BNK</div>
                </label>
                <label class="pay-option" onclick="selectPay(this,'card')">
                    <input type="radio" name="payment" value="card">
                    <div class="pay-label">
                        <div class="pay-name">Credit / Debit Card</div>
                        <div class="pay-sub">Visa, Mastercard, JCB</div>
                    </div>
                    <div class="pay-icon" style="color:#e00;">💳</div>
                </label>
            </div>
            <div class="cod-note" id="codNote">
                ⚠️ Please prepare the exact amount upon delivery. Our courier cannot provide change.
            </div>
        </div>

        <!-- Order Note -->
        <div class="form-section">
            <div class="form-section-title">Order Notes (optional)</div>
            <textarea id="orderNote" class="field-input" rows="3" style="resize:vertical;"
                      placeholder="Special instructions, gate codes, landmark info…"></textarea>
        </div>

        <!-- Submit -->
        <button class="submit-btn" id="btnPlaceOrder">
            <i class="fas fa-lock"></i> Place Order
        </button>
        <a href="home.php" class="back-link">
            <i class="fas fa-chevron-left"></i> Return to shopping
        </a>
    </div>

    <!-- ── RIGHT PANEL: ORDER SUMMARY ── -->
    <div class="order-summary">
        <div class="summary-header">Order Summary</div>

        <!-- Items from cart (rendered by JS) -->
        <div class="summary-items" id="summaryItems">
            <div class="empty-cart-msg">
                <i class="fas fa-shopping-cart"></i>
                Your cart is empty.<br>
                <a href="home.php">Continue shopping</a>
            </div>
        </div>

        <div class="summary-divider"></div>

        <!-- Coupon -->
        <div class="coupon-row" style="padding-top:14px;">
            <input type="text" class="coupon-input" id="couponInput" placeholder="Discount code">
            <button class="coupon-btn" onclick="applyCoupon()">Apply</button>
        </div>

        <div class="summary-divider"></div>

        <!-- Totals -->
        <div class="summary-totals">
            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-val" id="summarySubtotal">₱0.00</span>
            </div>
            <div class="total-row">
                <span class="total-label">Shipping</span>
                <span class="total-val muted" id="summaryShipping">Enter shipping address</span>
            </div>
            <div class="total-row" id="discountRow" style="display:none;">
                <span class="total-label">Discount</span>
                <span class="total-val" id="summaryDiscount" style="color:#28a745;">-₱0.00</span>
            </div>
            <div class="total-row grand">
                <span>Total</span>
                <span>
                    <span class="currency">PHP</span>
                    <span id="summaryTotal">₱0.00</span>
                </span>
            </div>
        </div>
    </div>

</div>

<?php if(!$isLoggedIn): ?>
<?php include 'account-panel.php'; ?>
<?php endif; ?>

<script>
/* ── CART ── */
let cartItems = [];
let shippingFee = null;
let selectedPayment = 'cod';
let currentMode = 'ship';
let selectedStore = 'cebu-main';
let discount = 0;

function fmt(n){ return '₱'+Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

// REPLACE WITH:
async function loadCart(){
    try {
        const { auth, db } = await import('./firebase-config.js');
        const { collection, getDocs } = await import('https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js');
        const { onAuthStateChanged } = await import('https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js');
        
        onAuthStateChanged(auth, async (user) => {
            if (user) {
                const snap = await getDocs(collection(db, "carts", user.uid, "items"));
                cartItems = snap.docs.map(d => ({
                    id: d.id,
                    name: d.data().productName || "Product",
                    priceRaw: d.data().productPrice || 0,
                    image: d.data().productImage || "",
                    qty: d.data().qty || 1
                }));
            }
            
            if (!cartItems.length) {
                try {
                    const stored = localStorage.getItem('pcx_cart');
                    cartItems = stored ? JSON.parse(stored) : [];
                } catch(e) {}
            }
            renderSummary();
        });
    } catch(e) {
        console.error("loadCart error:", e);
        try {
            const stored = localStorage.getItem('pcx_cart');
            cartItems = stored ? JSON.parse(stored) : [];
        } catch(e) {}
        renderSummary();
    }
}

function renderSummary(){
    // Expose to bridge so interceptPlaceOrder() can read the cart
    window._checkoutCartItems = cartItems;
    window.cartItems = cartItems;

    const wrap = document.getElementById('summaryItems');
    if(!cartItems.length){
        wrap.innerHTML = `<div class="empty-cart-msg"><i class="fas fa-shopping-cart"></i>Your cart is empty.<br><a href="home.php">Continue shopping</a></div>`;
        document.getElementById('summarySubtotal').textContent = '₱0.00';
        document.getElementById('summaryTotal').textContent = '₱0.00';
        return;
    }
    let subtotal = 0;
    wrap.innerHTML = cartItems.map(item=>{
        const price = parseFloat(item.priceRaw) || parseFloat((item.price||'0').replace(/[^0-9.]/g,'')) || 0;
        subtotal += price * (item.qty||1);
        return `<div class="summary-item">
            <div class="item-img-wrap">
                <img src="${item.image||'https://via.placeholder.com/64'}" alt="${item.name||''}" class="item-img">
                <span class="item-qty-badge">${item.qty||1}</span>
            </div>
            <div class="item-info">
                <div class="item-name">${item.name||'Product'}</div>
                ${item.variant?`<div class="item-variant">${item.variant}</div>`:''}
            </div>
            <div class="item-price">${fmt(price*(item.qty||1))}</div>
        </div>`;
    }).join('');
    updateTotals(subtotal);
}

function updateTotals(subtotal){
    if(subtotal===undefined){
        // Prefer bridge's cart if local cartItems is empty (bridge loads async)
        const items = (cartItems && cartItems.length > 0)
            ? cartItems
            : (window._checkoutCartItems || []);
        let s=0;
        items.forEach(i=>s+=(parseFloat(i.priceRaw)||parseFloat((i.price||'0').replace(/[^0-9.]/g,''))||0)*(parseInt(i.qty)||1));
        subtotal=s;
    }
    document.getElementById('summarySubtotal').textContent = fmt(subtotal - discount);
    const shipEl = document.getElementById('summaryShipping');
    let shipAmt = 0;
    if(currentMode==='pickup'){
        shipEl.textContent='Free';
        shipEl.className='total-val free';
        shipAmt = 0;
    } else if(shippingFee===null){
        shipEl.textContent='Enter shipping address';
        shipEl.className='total-val muted';
        shipAmt = 0;
    } else {
        shipEl.textContent = shippingFee===0?'Free':fmt(shippingFee);
        shipEl.className = shippingFee===0?'total-val free':'total-val';
        shipAmt = shippingFee;
    }
    document.getElementById('summaryTotal').textContent = fmt(Math.max(0, subtotal - discount + shipAmt));
}

/* ── SHIP/PICKUP MODE ── */
function setMode(mode){
    currentMode = mode;
    document.getElementById('btnShip').classList.toggle('active', mode==='ship');
    document.getElementById('btnPickup').classList.toggle('active', mode==='pickup');
    document.getElementById('shipContent').style.display = mode==='ship'?'':'none';
    document.getElementById('pickupContent').style.display = mode==='pickup'?'':'none';
    if(mode==='pickup') shippingFee=0;
    else shippingFee=null;
    updateTotals();
}

/* ── SHIPPING SELECTION ── */
function selectShipping(el, method, fee){
    document.querySelectorAll('.shipping-option').forEach(o=>o.classList.remove('selected'));
    el.classList.add('selected');
    shippingFee = fee;
    updateTotals();
}

/* ── PAYMENT SELECTION ── */
function selectPay(el, method){
    document.querySelectorAll('.pay-option').forEach(o=>o.classList.remove('selected'));
    el.classList.add('selected');
    selectedPayment = method;
    document.getElementById('codNote').classList.toggle('show', method==='cod');
}

/* ── STORE SELECTION ── */
function selectStore(el, store){
    document.querySelectorAll('.store-option').forEach(o=>o.classList.remove('selected'));
    el.classList.add('selected');
    selectedStore = store;
}

/* ── ADDRESS DETECTION → show shipping options ── */
function checkAddressReady(){
    const addr = document.getElementById('fAddress').value.trim();
    const city = document.getElementById('fCity').value.trim();
    if(addr && city){
        document.getElementById('shippingPlaceholder').style.display='none';
        document.getElementById('shippingOptions').style.display='block';
        if(shippingFee===null){
            // Always default to Standard (150) and make sure the radio + selected class match
            shippingFee=150;
            const stdOption = document.querySelector('.shipping-option');
            const stdRadio  = document.querySelector('input[name="shipping_method"][value="standard"]');
            document.querySelectorAll('.shipping-option').forEach(o=>o.classList.remove('selected'));
            if(stdOption) stdOption.classList.add('selected');
            if(stdRadio)  stdRadio.checked = true;
            updateTotals();
        }
    } else {
        document.getElementById('shippingPlaceholder').style.display='block';
        document.getElementById('shippingOptions').style.display='none';
        shippingFee=null; updateTotals();
    }
}
['fAddress','fCity','fPostal'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('input',checkAddressReady);
});

// Auto-detect if default address is pre-filled
window.addEventListener('DOMContentLoaded',function(){
    // NOTE: Cart loading is handled by checkout_firestore_bridge.js
    // Do NOT call loadCart() here — it causes duplicate/phantom items.
    // loadCart() is kept below only as a fallback if the bridge is absent.
    if(typeof window._bridgeActive === 'undefined'){
        loadCart();
    }
    checkAddressReady();
    <?php if($isLoggedIn): ?>
    // Show COD note by default
    document.getElementById('codNote').classList.add('show');
    <?php endif; ?>
});

/* ── COUPON ── */
function applyCoupon(){
    const code = document.getElementById('couponInput').value.trim().toUpperCase();
    const VALID = { 'PCX10': 10, 'SAVE50': 50, 'WELCOME': 100 };
    if(VALID[code]){
        const subtotal = cartItems.reduce((s,i)=>s+(parseFloat(i.priceRaw)||parseFloat((i.price||'0').replace(/[^0-9.]/g,''))||0)*(i.qty||1),0);
        discount = Math.min(VALID[code], subtotal);
        document.getElementById('discountRow').style.display='flex';
        document.getElementById('summaryDiscount').textContent='-'+fmt(discount);
        updateTotals();
        showAlert('Coupon applied: -'+fmt(discount),'success');
    } else {
        showAlert('Invalid or expired coupon code.','error');
    }
}

/* ── FIELD-LEVEL ERROR HELPERS ── */
function setFieldError(id, msg){
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.add('invalid');
    // insert or reuse error msg span
    let err = el.parentElement.querySelector('.field-error-msg');
    if(!err){
        err = document.createElement('span');
        err.className = 'field-error-msg';
        el.parentElement.appendChild(err);
    }
    err.textContent = msg;
    err.classList.add('show');
}
function clearFieldError(id){
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.remove('invalid');
    const err = el.parentElement.querySelector('.field-error-msg');
    if(err){ err.classList.remove('show'); err.textContent=''; }
}
function clearAllErrors(){
    document.querySelectorAll('.field-input.invalid').forEach(el=>{
        el.classList.remove('invalid');
        const err = el.parentElement.querySelector('.field-error-msg');
        if(err){ err.classList.remove('show'); err.textContent=''; }
    });
}
// Clear error on user input
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.field-input').forEach(inp=>{
        inp.addEventListener('input',()=>clearFieldError(inp.id));
        inp.addEventListener('change',()=>clearFieldError(inp.id));
    });
});

/* ── VALIDATION ── */
function isValidEmail(email){
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
}
function isValidPhone(phone){
    if(!phone) return true; // optional
    return /^(\+63|0)[0-9\s\-]{9,14}$/.test(phone);
}
function isValidPostal(postal){
    if(!postal) return true; // optional
    return /^[0-9]{4,10}$/.test(postal);
}
function isValidName(name){
    return /^[\p{L}\s'\-\.]+$/u.test(name);
}

function validate(){
    clearAllErrors();
    let valid = true;
    const mode = currentMode;

    // Only require guest email if Firebase has no current user AND PHP session has no user
    const firebaseUser = (typeof window._firebaseCurrentUser !== 'undefined') ? window._firebaseCurrentUser : null;
    const phpLoggedIn  = <?= $isLoggedIn ? 'true' : 'false' ?>;
    if(!phpLoggedIn && !firebaseUser){
        const email = (document.getElementById('guestEmail')?.value||'').trim();
        if(!email){
            setFieldError('guestEmail','Email address is required.');
            valid = false;
        } else if(!isValidEmail(email)){
            setFieldError('guestEmail','Please enter a valid email address.');
            valid = false;
        }
    }

    if(mode==='ship'){
        const fFirst   = document.getElementById('fFirst')?.value.trim()||'';
        const fLast    = document.getElementById('fLast')?.value.trim()||'';
        const fAddress = document.getElementById('fAddress')?.value.trim()||'';
        const fCity    = document.getElementById('fCity')?.value.trim()||'';
        const fPhone   = document.getElementById('fPhone')?.value.trim()||'';
        const fPostal  = document.getElementById('fPostal')?.value.trim()||'';

        if(!fFirst){
            setFieldError('fFirst','First name is required.'); valid=false;
        } else if(!isValidName(fFirst)){
            setFieldError('fFirst','First name contains invalid characters.'); valid=false;
        }
        if(!fLast){
            setFieldError('fLast','Last name is required.'); valid=false;
        } else if(!isValidName(fLast)){
            setFieldError('fLast','Last name contains invalid characters.'); valid=false;
        }
        if(!fAddress){
            setFieldError('fAddress','Street address is required.'); valid=false;
        } else if(fAddress.length < 5){
            setFieldError('fAddress','Please enter a complete street address.'); valid=false;
        }
        if(!fCity){
            setFieldError('fCity','City is required.'); valid=false;
        }
        if(fPhone && !isValidPhone(fPhone)){
            setFieldError('fPhone','Enter a valid PH phone number (e.g. 09XX XXX XXXX).'); valid=false;
        }
        if(fPostal && !isValidPostal(fPostal)){
            setFieldError('fPostal','Postal code must be 4–10 digits.'); valid=false;
        }
        // Auto-resolve shippingFee if address is filled but options weren't manually shown
        if(shippingFee===null && fAddress && fCity){
            shippingFee = 150;
            checkAddressReady();
        }
        if(shippingFee===null){
            showAlert('Please enter a complete address to see shipping options.','error');
            valid=false;
        }
    } else {
        // Pickup
        const pFirst = document.getElementById('pFirst')?.value.trim()||'';
        const pLast  = document.getElementById('pLast')?.value.trim()||'';
        const pPhone = document.getElementById('pPhone')?.value.trim()||'';
        if(!pFirst){
            setFieldError('pFirst','First name is required for pickup.'); valid=false;
        } else if(!isValidName(pFirst)){
            setFieldError('pFirst','First name contains invalid characters.'); valid=false;
        }
        if(!pLast){
            setFieldError('pLast','Last name is required for pickup.'); valid=false;
        } else if(!isValidName(pLast)){
            setFieldError('pLast','Last name contains invalid characters.'); valid=false;
        }
        if(pPhone && !isValidPhone(pPhone)){
            setFieldError('pPhone','Enter a valid PH phone number (e.g. 09XX XXX XXXX).'); valid=false;
        }
    }

    // Use bridge's cart if local cartItems hasn't been synced yet
    const _cartToCheck = (cartItems && cartItems.length > 0) ? cartItems : (window._checkoutCartItems || []);
    if(!_cartToCheck.length){
        showAlert('Your cart is empty.','error'); valid=false;
    }

    if(!valid){
        // Scroll to first invalid field or the alert
        const firstInvalid = document.querySelector('.field-input.invalid');
        if(firstInvalid) firstInvalid.scrollIntoView({behavior:'smooth',block:'center'});
        else document.getElementById('checkoutAlert').scrollIntoView({behavior:'smooth',block:'center'});
    }
    return valid;
}

/* ── PLACE ORDER ── */
async function placeOrder(){
    const _cart = (cartItems && cartItems.length > 0) ? cartItems : (window._checkoutCartItems || []);
    if(!_cart.length){
        showAlert('Your cart is empty.', 'error');
        return;
    }

    const btn = document.getElementById('btnPlaceOrder');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';

    try {
        const { auth, db } = await import('./firebase-config.js');
        const { placeOrder: place } = await import('./order.js');

        const user = auth.currentUser;
        if(!user && !document.getElementById('guestEmail').value){
            showAlert('Please enter your email address.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
            return;
        }

        const orderData = {
            mode: currentMode,
            payment: selectedPayment,
            cartItems: cartItems,
            shippingFee: shippingFee,
            discount: discount,
            coupon: document.getElementById('couponInput').value,
            orderNote: document.getElementById('orderNote').value,
            shippingMethod: document.querySelector('input[name="shipping_method"]:checked')?.value || 'standard',
            guestEmail: user ? '' : document.getElementById('guestEmail').value,
            selectedStore: selectedStore,
            firstName: document.getElementById(currentMode === 'ship' ? 'fFirst' : 'pFirst')?.value || '',
            lastName: document.getElementById(currentMode === 'ship' ? 'fLast' : 'pLast')?.value || '',
            address: document.getElementById('fAddress')?.value || '',
            city: document.getElementById('fCity')?.value || '',
            province: document.getElementById('fProvince')?.value || '',
            postalCode: document.getElementById('fPostal')?.value || '',
            phone: document.getElementById(currentMode === 'ship' ? 'fPhone' : 'pPhone')?.value || '',
            country: 'Philippines'
        };

        const result = await place(orderData);
        
        if(result.success){
            localStorage.removeItem('pcx_cart');
            window.location.href = 'order_confirmation.php?order=' + encodeURIComponent(result.orderNumber);
        } else {
            showAlert(result.message || 'Failed to place order.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
        }
    } catch(err){
        console.error('placeOrder error:', err);
        showAlert('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
    }
}

/* ── HELPERS ── */
function showAlert(msg,type){
    const el=document.getElementById('checkoutAlert');
    el.textContent=msg; el.className='checkout-alert show '+type;
    el.scrollIntoView({behavior:'smooth',block:'center'});
    setTimeout(()=>el.classList.remove('show'),5000);
}

function doLogout(){
    import('./auth.js').then(m => {
        if(typeof m.doLogout === 'function') m.doLogout();
        else window.location.href = 'home.php';
    });
}</script>
<script type="module" src="auth.js"></script>
  <script type="module" src="checkout_firestore_bridge.js"></script>
</body>
</html>