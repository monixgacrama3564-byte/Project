<?php
// addresses.php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: home.php'); exit; }

$pdo = getDB();

$uid=(int)$_SESSION['user_id'];
$addresses=[];
$addrCount=0;
$dbError='';

if($pdo){
    try{
        $s=$pdo->prepare("SELECT * FROM user_addresses WHERE user_id=? ORDER BY is_default DESC,id ASC");
        $s->execute([$uid]); $addresses=$s->fetchAll(); $addrCount=count($addresses);
    }catch(Exception $e){ $dbError='user_addresses table not found. Please run the SQL update.'; }
}

// Handle form POST (save address)
$formMsg=''; $formType='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_address'])){
    csrf_verify();
    $fn=trim($_POST['first_name']??''); $ln=trim($_POST['last_name']??'');
    $co=trim($_POST['company']??'');    $ad=trim($_POST['address']??'');
    $ap=trim($_POST['apartment']??'');  $ci=trim($_POST['city']??'');
    $cn=trim($_POST['country']??'Philippines'); $pr=trim($_POST['province']??'');
    $pc=trim($_POST['postal_code']??''); $ph=trim($_POST['phone']??'');
    $def=isset($_POST['is_default'])?1:0;
    // Server-side validation
    $errors = [];
    if(!$fn) $errors[]='First name is required.';
    elseif(!preg_match("/^[\p{L}\s'\-\.]+\$/u",$fn)) $errors[]='First name contains invalid characters.';
    if(!$ln) $errors[]='Last name is required.';
    elseif(!preg_match("/^[\p{L}\s'\-\.]+\$/u",$ln)) $errors[]='Last name contains invalid characters.';
    if(!$ad) $errors[]='Address is required.';
    elseif(strlen($ad)<5) $errors[]='Please enter a complete street address.';
    if(!$ci) $errors[]='City is required.';
    if($ph && !preg_match('/^(\+63|0)[0-9\s\-]{9,14}$/',$ph)) $errors[]='Enter a valid PH phone number.';
    if($pc && !preg_match('/^[0-9]{4,10}$/',$pc)) $errors[]='Postal code must be 4–10 digits.';
    if(!empty($errors)){
        $formMsg=implode(' ',$errors); $formType='error';
    } elseif($pdo){
        try{
            // ── DUPLICATE CHECK ───────────────────────────────────────────────
            // Compare street address, apartment, city, province, and postal code
            // case-insensitively. Names and phone are intentionally excluded so
            // a user can have the same location for different recipients.
            $dup = $pdo->prepare("
                SELECT id FROM user_addresses
                WHERE user_id = ?
                  AND LOWER(address)     = LOWER(?)
                  AND LOWER(apartment)   = LOWER(?)
                  AND LOWER(city)        = LOWER(?)
                  AND LOWER(province)    = LOWER(?)
                  AND LOWER(postal_code) = LOWER(?)
                LIMIT 1
            ");
            $dup->execute([$uid, $ad, $ap, $ci, $pr, $pc]);
            if($dup->fetch()){
                $formMsg = 'This address already exists in your address book.';
                $formType = 'error';
            } else {
                if($def) $pdo->prepare("UPDATE user_addresses SET is_default=0 WHERE user_id=?")->execute([$uid]);
                $s=$pdo->prepare("INSERT INTO user_addresses(user_id,first_name,last_name,company,address,apartment,city,country,province,postal_code,phone,is_default)VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
                $s->execute([$uid,$fn,$ln,$co,$ad,$ap,$ci,$cn,$pr,$pc,$ph,$def]);
                $formMsg='Address saved successfully!'; $formType='success';
                header('Location: addresses.php?saved=1'); exit;
            }
        }catch(Exception $e){ $formMsg='Could not save: '.$e->getMessage(); $formType='error'; }
    }
}
if(isset($_GET['saved'])){ $formMsg='Address saved successfully!'; $formType='success'; }

// Handle delete (POST only, with CSRF)
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_address'])){
    csrf_verify();
    $aid=(int)($_POST['delete_id'] ?? 0);
    if($aid && $pdo){
        try{ $pdo->prepare("DELETE FROM user_addresses WHERE id=? AND user_id=?")->execute([$aid,$uid]); }catch(Exception $e){}
    }
    header('Location: addresses.php'); exit;
}

$showForm = isset($_GET['add']) || ($formType==='error');

// Philippines provinces
$provinces=['Abra','Agusan del Norte','Agusan del Sur','Aklan','Albay','Antique','Apayao','Aurora','Basilan','Bataan','Batanes','Batangas','Benguet','Biliran','Bohol','Bukidnon','Bulacan','Cagayan','Camarines Norte','Camarines Sur','Camiguin','Capiz','Catanduanes','Cavite','Cebu','Compostela Valley','Cotabato','Davao del Norte','Davao del Sur','Davao Occidental','Davao Oriental','Dinagat Islands','Eastern Samar','Guimaras','Ifugao','Ilocos Norte','Ilocos Sur','Iloilo','Isabela','Kalinga','La Union','Laguna','Lanao del Norte','Lanao del Sur','Leyte','Maguindanao','Marinduque','Masbate','Metro Manila','Misamis Occidental','Misamis Oriental','Mountain Province','Negros Occidental','Negros Oriental','Northern Samar','Nueva Ecija','Nueva Vizcaya','Occidental Mindoro','Oriental Mindoro','Palawan','Pampanga','Pangasinan','Quezon','Quirino','Rizal','Romblon','Samar','Sarangani','Siquijor','Sorsogon','South Cotabato','Southern Leyte','Sultan Kudarat','Sulu','Surigao del Norte','Surigao del Sur','Tarlac','Tawi-Tawi','Zambales','Zamboanga del Norte','Zamboanga del Sur','Zamboanga Sibugay'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Addresses – PC Express</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f5f5;color:#222;}
a{text-decoration:none;color:inherit;}

.header{background:#fff;border-bottom:1px solid #ddd;position:sticky;top:0;z-index:1000;box-shadow:0 2px 6px rgba(0,0,0,.1);}
.header-inner{display:flex;align-items:center;gap:16px;padding:10px 24px;max-width:1400px;margin:0 auto;}
.logo-text{font-size:26px;font-weight:900;letter-spacing:-1px;}
.logo-text span.pc{color:#003da5;} .logo-text span.ex{color:#e00;}
.search-bar{flex:1;display:flex;border:2px solid #003da5;border-radius:4px;overflow:hidden;}
.search-bar select{padding:8px 12px;border:none;border-right:1px solid #ccc;background:#f0f0f0;font-size:13px;outline:none;}
.search-bar input{flex:1;padding:8px 14px;border:none;font-size:14px;outline:none;}
.search-bar button{background:#003da5;border:none;padding:0 18px;cursor:pointer;color:#fff;font-size:16px;}
.header-actions{display:flex;align-items:center;gap:22px;}
.acct-wrap{position:relative;}
.acct-icon{display:flex;flex-direction:column;align-items:center;font-size:11px;color:#cc0000;cursor:pointer;}
.acct-dropdown{display:none;position:absolute;top:calc(100% + 10px);right:0;background:#fff;border:1px solid #e0e0e0;border-radius:4px;min-width:160px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:9999;}
.acct-dropdown.open{display:block;}
.acct-dropdown a{display:block;padding:13px 20px;font-size:14px;color:#222;border-bottom:1px solid #f0f0f0;}
.acct-dropdown a:last-child{border-bottom:none;}
.acct-dropdown a:hover{background:#f5f7ff;color:#003da5;}
.header-icon-link{display:flex;flex-direction:column;align-items:center;font-size:11px;color:#333;cursor:pointer;position:relative;}
.header-icon-link svg{margin-bottom:3px;}
.badge{position:absolute;top:-4px;right:-8px;background:#003da5;color:#fff;border-radius:50%;width:17px;height:17px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:700;}

.navbar{background:#003da5;}
.navbar-inner{max-width:1400px;margin:0 auto;display:flex;align-items:stretch;}
.all-products-btn{background:#cc0000;color:#fff;padding:10px 18px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;cursor:pointer;border:none;white-space:nowrap;position:relative;}
.dropdown-menu{display:none;position:absolute;top:100%;left:0;background:#fff;min-width:220px;box-shadow:0 6px 20px rgba(0,0,0,.15);z-index:999;border-top:3px solid #cc0000;}
.dropdown-menu a{display:block;padding:11px 18px;font-size:13px;color:#222;border-bottom:1px solid #f0f0f0;}
.dropdown-menu a:hover{background:#f5f7ff;color:#003da5;}
.all-products-btn.open .dropdown-menu{display:block;}
.nav-links{display:flex;flex-wrap:wrap;align-items:center;padding:0 8px;gap:2px;}
.nav-links a{color:#fff;font-size:13px;padding:10px 12px;white-space:nowrap;transition:background .2s;border-radius:3px;}
.nav-links a:hover{background:rgba(255,255,255,.15);}
.nav-links a.laptop-sale{color:#ffd700;font-weight:800;}

.page-hero{position:relative;height:180px;overflow:hidden;display:flex;align-items:center;justify-content:center;}
.page-hero-bg{position:absolute;inset:0;background:url('https://pcx.com.ph/cdn/shop/files/1800x600-Samsung-AI-Monitor.jpg?v=1765177814&width=1600') center/cover no-repeat;filter:brightness(.3);}
.page-hero-content{position:relative;z-index:2;text-align:center;color:#fff;}
.page-hero-content h1{font-size:34px;font-weight:900;letter-spacing:1px;margin-bottom:8px;}
.breadcrumb-bar{font-size:13px;color:rgba(255,255,255,.75);}
.breadcrumb-bar a{color:rgba(255,255,255,.75);}
.breadcrumb-bar a:hover{color:#fff;text-decoration:underline;}

.page-body{max-width:1400px;margin:36px auto 80px;padding:0 24px;display:flex;gap:28px;align-items:flex-start;}

.sidebar{width:280px;flex-shrink:0;border:1px solid #e0e0e0;border-radius:4px;overflow:hidden;background:#fff;}
.sidebar-item{display:flex;align-items:center;gap:10px;padding:14px 20px;font-size:14px;color:#333;border-bottom:1px solid #f0f0f0;cursor:pointer;transition:background .15s;text-decoration:none;}
.sidebar-item:last-child{border-bottom:none;}
.sidebar-item:hover{background:#f5f7ff;color:#003da5;}
.sidebar-item.active{background:#f5f7ff;color:#003da5;font-weight:600;}
.sidebar-item i{font-size:15px;width:18px;text-align:center;}

.main-content{flex:1;}

/* Add button */
.add-btn{display:inline-block;background:#222;color:#fff;padding:13px 28px;font-size:14px;font-weight:700;border:none;cursor:pointer;transition:background .2s;margin-bottom:24px;letter-spacing:.3px;}
.add-btn:hover{background:#003da5;}

/* Alert */
.alert{padding:13px 18px;border-radius:6px;font-size:14px;font-weight:600;margin-bottom:20px;}
.alert-success{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;}
.alert-error{background:#fdecea;color:#c62828;border:1px solid #ef9a9a;}
.alert-info{background:#fff3e0;color:#e65100;border:1px solid #ffcc80;}

/* Address cards */
.addr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-bottom:28px;}
.addr-card{background:#fff;border:1px solid #e0e0e0;border-radius:6px;padding:20px;position:relative;}
.addr-card.is-default{border-color:#003da5;}
.default-badge{display:inline-block;background:#003da5;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:3px;margin-bottom:10px;}
.addr-name{font-weight:700;font-size:15px;margin-bottom:6px;}
.addr-text{font-size:13px;color:#555;line-height:1.6;}
.addr-actions{margin-top:14px;display:flex;gap:10px;}
.addr-del-btn{font-size:12px;color:#cc0000;background:none;border:none;cursor:pointer;padding:0;}
.addr-del-btn:hover{text-decoration:underline;}

/* ADD FORM */
.addr-form-wrap{background:#fff;border:1px solid #e0e0e0;border-radius:6px;padding:32px 36px;margin-top:4px;}
.addr-form-title{font-size:18px;font-weight:700;text-align:center;margin-bottom:28px;color:#111;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}
.form-group{margin-bottom:16px;display:flex;flex-direction:column;}
.form-group label{font-size:13px;font-weight:600;color:#555;margin-bottom:6px;}
.form-input{width:100%;padding:13px 16px;border:1.5px solid #ccc;border-radius:4px;font-size:14px;outline:none;font-family:inherit;transition:border-color .2s;}
.form-input:focus{border-color:#003da5;box-shadow:0 0 0 3px rgba(0,61,165,.08);}
.form-input::placeholder{color:#aaa;}
.form-select{appearance:none;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23555' d='M6 8L0 0h12z'/%3E%3C/svg%3E") no-repeat right 14px center;padding-right:36px;}

.label-above{font-size:13px;font-weight:600;color:#555;margin-bottom:8px;display:block;}
.checkbox-row{display:flex;align-items:center;gap:10px;margin-bottom:24px;}
.checkbox-row input[type=checkbox]{width:16px;height:16px;cursor:pointer;accent-color:#003da5;}
.checkbox-row label{font-size:14px;color:#333;cursor:pointer;}

.form-actions{display:flex;gap:14px;align-items:center;}
.btn-submit{background:#111;color:#fff;border:none;padding:13px 28px;font-size:14px;font-weight:700;cursor:pointer;letter-spacing:.3px;transition:background .2s;}
.btn-submit:hover{background:#003da5;}
.btn-cancel{background:#fff;color:#333;border:1.5px solid #ccc;padding:12px 24px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;border-radius:2px;}
.btn-cancel:hover{border-color:#333;}

/* FIXED */
.live-chat{position:fixed;bottom:30px;left:24px;background:#cc0000;color:#fff;padding:12px 20px;border-radius:30px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;cursor:pointer;z-index:999;box-shadow:0 4px 16px rgba(200,0,0,.4);}
.for-you-tab{position:fixed;right:0;top:50%;transform:translateY(-50%);background:#003da5;color:#fff;writing-mode:vertical-rl;padding:16px 8px;font-size:12px;font-weight:700;cursor:pointer;z-index:100;border-radius:4px 0 0 4px;letter-spacing:1px;}
.scroll-top{position:fixed;bottom:30px;right:50px;background:#fff;border:1px solid #ddd;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;color:#333;z-index:99;}
.scroll-top:hover{background:#003da5;color:#fff;}

@media(max-width:768px){.page-body{flex-direction:column;}.sidebar{width:100%;}.form-row{grid-template-columns:1fr;}.nav-links{display:none;}}
</style>
</head>
<body>

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
                <a href="dashboard.php" style="display:block;padding:12px 20px;font-size:14px;color:#222;border-bottom:1px solid #f0f0f0;text-decoration:none;">Dashboard</a>
                <a href="addresses.php" style="display:block;padding:12px 20px;font-size:14px;color:#222;border-bottom:1px solid #f0f0f0;text-decoration:none;">Addresses</a>
                <a href="#" onclick="doLogoutInline();return false;" style="display:block;padding:12px 20px;font-size:14px;color:#cc0000;text-decoration:none;">Logout</a>
            </div>
        </div>
        <a href="#" class="header-icon-link" title="Wishlist">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>Wishlist</span><span class="badge">0</span>
        </a>
        <a href="#" class="header-icon-link" title="Cart">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span>Cart</span><span class="badge">0</span>
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
            <a href="pc_components.php">PC Components</a><a href="#">Laptops</a>
            <a href="#">Desktop PCs</a><a href="home.php">All Products</a>
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
        <h1>Addresses</h1>
        <div class="breadcrumb-bar">
            <a href="home.php">Home</a> &rsaquo; Addresses
        </div>
    </div>
</div>

<!-- BODY -->
<div class="page-body">

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-item">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="addresses.php" class="sidebar-item active">
            <i class="fas fa-map-marker-alt"></i> Addresses (<?= $addrCount ?>)
        </a>
        <a href="#" class="sidebar-item">
            <i class="fas fa-heart"></i> Wishlist (0)
        </a>
        <a href="#" class="sidebar-item" onclick="doLogout();return false;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main -->
    <div class="main-content">

        <!-- Alert messages -->
        <?php if($formMsg): ?>
        <div class="alert alert-<?= $formType ?>"><?= htmlspecialchars($formMsg) ?></div>
        <?php endif; ?>
        <?php if($dbError): ?>
        <div class="alert alert-info">⚠️ <?= htmlspecialchars($dbError) ?> <a href="add_address_table.sql" style="color:#003da5;font-weight:700;">Download SQL</a></div>
        <?php endif; ?>

        <!-- Add New Address button -->
        <a href="addresses.php?add=1" class="add-btn">Add a New Address</a>

        <!-- Existing addresses -->
        <?php if(!empty($addresses)): ?>
        <div class="addr-grid">
            <?php foreach($addresses as $addr): ?>
            <div class="addr-card <?= $addr['is_default']?'is-default':'' ?>">
                <?php if($addr['is_default']): ?><div class="default-badge">Default</div><?php endif; ?>
                <div class="addr-name"><?= htmlspecialchars($addr['first_name'].' '.$addr['last_name']) ?></div>
                <div class="addr-text">
                    <?php if($addr['company']): ?><?= htmlspecialchars($addr['company']) ?><br><?php endif; ?>
                    <?= htmlspecialchars($addr['address']) ?><br>
                    <?php if($addr['apartment']): ?><?= htmlspecialchars($addr['apartment']) ?><br><?php endif; ?>
                    <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['province']) ?> <?= htmlspecialchars($addr['postal_code']) ?><br>
                    <?= htmlspecialchars($addr['country']) ?><br>
                    <?php if($addr['phone']): ?><?= htmlspecialchars($addr['phone']) ?><?php endif; ?>
                </div>
                <div class="addr-actions">
                    <form method="POST" action="addresses.php" onsubmit="return confirm('Delete this address?')" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="delete_id" value="<?= (int)$addr['id'] ?>">
                        <button type="submit" name="delete_address" class="addr-del-btn">Remove</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Add Address Form -->
        <?php if($showForm): ?>
        <div class="addr-form-wrap" id="addrForm">
            <div class="addr-form-title">Add a New Address</div>
            <form method="POST" action="addresses.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="first_name" class="form-input" placeholder="First Name"
                               value="<?= htmlspecialchars($_POST['first_name']??'') ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="last_name" class="form-input" placeholder="Last Name"
                               value="<?= htmlspecialchars($_POST['last_name']??'') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" name="company" class="form-input" placeholder="Company"
                           value="<?= htmlspecialchars($_POST['company']??'') ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="address" class="form-input" placeholder="Address"
                           value="<?= htmlspecialchars($_POST['address']??'') ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="apartment" class="form-input" placeholder="Apartment, suite, etc."
                           value="<?= htmlspecialchars($_POST['apartment']??'') ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="city" class="form-input" placeholder="City"
                           value="<?= htmlspecialchars($_POST['city']??'') ?>" required>
                </div>
                <div class="form-group">
                    <label class="label-above">Country/region</label>
                    <select name="country" class="form-input form-select">
                        <option value="Philippines" selected>Philippines</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="label-above">Province</label>
                    <select name="province" class="form-input form-select">
                        <?php foreach($provinces as $prov): ?>
                        <option value="<?= htmlspecialchars($prov) ?>"
                            <?= (($_POST['province']??'')===$prov)?'selected':'' ?>>
                            <?= htmlspecialchars($prov) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="postal_code" class="form-input" placeholder="Postal/Zip Code"
                           value="<?= htmlspecialchars($_POST['postal_code']??'') ?>">
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" class="form-input" placeholder="Phone"
                           value="<?= htmlspecialchars($_POST['phone']??'') ?>">
                </div>
                <div class="checkbox-row">
                    <input type="checkbox" name="is_default" id="chkDefault" value="1"
                           <?= isset($_POST['is_default'])?'checked':'' ?>>
                    <label for="chkDefault">Set as default address</label>
                </div>
                <div class="form-actions">
                    <button type="submit" name="save_address" class="btn-submit">Add Address</button>
                    <a href="addresses.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<div class="live-chat">💬 Live chat</div>
<div class="for-you-tab">For You</div>
<div class="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">▲</div>

<script>

/* ── ADDRESS FORM CLIENT-SIDE VALIDATION ── */
(function(){
    const form = document.querySelector('form[action="addresses.php"]');
    if(!form) return;

    function isValidName(n){ return /^[\p{L}\s'\-\.]+$/u.test(n); }
    function isValidPhone(p){ return !p || /^(\+63|0)[0-9\s\-]{9,14}$/.test(p); }
    function isValidPostal(p){ return !p || /^[0-9]{4,10}$/.test(p); }

    function setErr(input, msg){
        input.style.borderColor='#c62828';
        input.style.boxShadow='0 0 0 3px rgba(198,40,40,.1)';
        let span = input.nextElementSibling;
        if(!span || !span.classList.contains('js-err')){
            span = document.createElement('span');
            span.className='js-err';
            span.style.cssText='font-size:11px;color:#c62828;display:block;margin-top:3px;';
            input.parentNode.insertBefore(span, input.nextSibling);
        }
        span.textContent = msg;
    }
    function clearErr(input){
        input.style.borderColor='';
        input.style.boxShadow='';
        const span = input.nextElementSibling;
        if(span && span.classList.contains('js-err')) span.textContent='';
    }

    // Live clear on input
    form.querySelectorAll('.form-input').forEach(inp=>{
        inp.addEventListener('input',()=>clearErr(inp));
    });

    form.addEventListener('submit', function(e){
        let ok = true;
        const fn = form.querySelector('[name="first_name"]');
        const ln = form.querySelector('[name="last_name"]');
        const ad = form.querySelector('[name="address"]');
        const ci = form.querySelector('[name="city"]');
        const ph = form.querySelector('[name="phone"]');
        const pc = form.querySelector('[name="postal_code"]');

        if(!fn.value.trim()){ setErr(fn,'First name is required.'); ok=false; }
        else if(!isValidName(fn.value.trim())){ setErr(fn,'First name contains invalid characters.'); ok=false; }
        else clearErr(fn);

        if(!ln.value.trim()){ setErr(ln,'Last name is required.'); ok=false; }
        else if(!isValidName(ln.value.trim())){ setErr(ln,'Last name contains invalid characters.'); ok=false; }
        else clearErr(ln);

        if(!ad.value.trim()){ setErr(ad,'Address is required.'); ok=false; }
        else if(ad.value.trim().length<5){ setErr(ad,'Please enter a complete street address.'); ok=false; }
        else clearErr(ad);

        if(!ci.value.trim()){ setErr(ci,'City is required.'); ok=false; }
        else clearErr(ci);

        if(ph && !isValidPhone(ph.value.trim())){ setErr(ph,'Enter a valid PH phone number (e.g. 09XX XXX XXXX).'); ok=false; }
        else if(ph) clearErr(ph);

        if(pc && !isValidPostal(pc.value.trim())){ setErr(pc,'Postal code must be 4–10 digits.'); ok=false; }
        else if(pc) clearErr(pc);

        if(!ok){
            e.preventDefault();
            const firstErr = form.querySelector('[style*="c62828"]');
            if(firstErr) firstErr.scrollIntoView({behavior:'smooth',block:'center'});
        }
    });
})();

function doLogoutInline(){
    const fd=new FormData(); fd.append('action','logout');
    fetch('account_handler.php',{method:'POST',body:fd})
    .then(()=>{ window.location.href='home.php'; });
}
function doLogout(){
    const fd=new FormData(); fd.append('action','logout');
    fetch('account_handler.php',{method:'POST',body:fd})
    .then(()=>{ window.location.href='home.php'; });
}
document.addEventListener('click',function(e){
    const w=document.querySelector('.acct-wrap');
    const drop=document.getElementById('acctDrop');
    if(w&&drop&&!w.contains(e.target)) drop.classList.remove('open');
    const btn=document.querySelector('.all-products-btn');
    if(btn&&!btn.contains(e.target)) btn.classList.remove('open');
});
// Auto scroll to form when it's shown
<?php if($showForm): ?>
window.addEventListener('load',function(){
    const f=document.getElementById('addrForm');
    if(f) f.scrollIntoView({behavior:'smooth',block:'start'});
});
<?php endif; ?>
</script>
<script type="module" src="auth.js"></script>
<script type="module" src="addresses.js"></script>
<script type="module">
  import { renderAddressesPage, initAddressForm } from './addresses.js';
  import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
  import { auth } from './firebase-config.js';

  onAuthStateChanged(auth, (user) => {
    if (!user) { window.location.href = 'Home.php'; return; }
    renderAddressesPage();
    initAddressForm();
  });
</script>
<script type="module" src="auth.js"></script>
  <script type="module" src="order.js"></script>
  <script type="module" src="checkout_firestore_bridge.js"></script>
</body>
</html>