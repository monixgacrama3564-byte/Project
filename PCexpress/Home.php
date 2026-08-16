<?php
// home.php - PC Express Homepage

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Express - Philippines' No. 1 PC Company</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        /* ── TOP HEADER ── */
        .header {
            background: #fff;
            border-bottom: 1px solid #ddd;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 6px rgba(0,0,0,.1);
        }
        .header-inner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 24px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .logo img {
            height: 46px;
            width: auto;
        }
        .logo-text {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -1px;
        }
        .logo-text span.pc { color: #003da5; }
        .logo-text span.ex { color: #e00; }

        .search-bar {
            flex: 1;
            display: flex;
            border: 2px solid #003da5;
            border-radius: 4px;
            overflow: hidden;
        }
        .search-bar select {
            padding: 8px 12px;
            border: none;
            border-right: 1px solid #ccc;
            background: #f0f0f0;
            font-size: 13px;
            cursor: pointer;
            outline: none;
        }
        .search-bar input {
            flex: 1;
            padding: 8px 14px;
            border: none;
            font-size: 14px;
            outline: none;
        }
        .search-bar button {
            background: #003da5;
            border: none;
            padding: 0 18px;
            cursor: pointer;
            color: #fff;
            font-size: 16px;
            transition: background .2s;
        }
        .search-bar button:hover { background: #002880; }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 22px;
        }
        .header-actions a {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 11px;
            color: #333;
            cursor: pointer;
            position: relative;
            transition: color .2s;
        }
        .header-actions a:hover { color: #003da5; }
        .header-actions a img {
            width: 26px;
            height: 26px;
            margin-bottom: 3px;
        }
        .badge {
            position: absolute;
            top: -4px;
            right: -8px;
            background: #003da5;
            color: #fff;
            border-radius: 50%;
            width: 17px;
            height: 17px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* ── NAV BAR ── */
        .navbar {
            background: #003da5;
        }
        .navbar-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: stretch;
        }
        .all-products-btn {
            background: #cc0000;
            color: #fff;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            white-space: nowrap;
            border: none;
            position: relative;
            user-select: none;
        }
        .all-products-btn i { font-size: 16px; }

        /* Dropdown */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            min-width: 240px;
            box-shadow: 0 6px 20px rgba(0,0,0,.15);
            z-index: 999;
            border-top: 3px solid #cc0000;
        }
        .dropdown-menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 18px;
            font-size: 13px;
            color: #222;
            border-bottom: 1px solid #f0f0f0;
            transition: background .15s;
        }
        .dropdown-menu a:hover { background: #f5f7ff; color: #003da5; }
        .all-products-btn:hover .dropdown-menu,
        .all-products-btn.open .dropdown-menu { display: block; }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 0 8px;
            gap: 2px;
        }
        .nav-links a {
            color: #fff;
            font-size: 13px;
            padding: 10px 12px;
            white-space: nowrap;
            transition: background .2s;
            border-radius: 3px;
        }
        .nav-links a:hover { background: rgba(255,255,255,.15); }
        .nav-links a.highlight { color: #ffd700; font-weight: 700; }
        .nav-links a.laptop-sale {
            color: #ffd700;
            font-weight: 800;
            background: rgba(255,255,255,.08);
        }

        /* ── HERO SLIDER ── */
        .hero-slider {
            position: relative;
            overflow: hidden;
            background: #001a5c;
            max-height: 440px;
        }
        .slides-wrapper {
            display: flex;
            transition: transform .5s ease;
        }
        .slide {
            min-width: 100%;
            position: relative;
            overflow: hidden;
        }
        .slide img {
            width: 100%;
            height: 440px;
            object-fit: cover;
            display: block;
        }

        /* Fallback slide (CSS only) */
        .slide-fallback {
            width: 100%;
            height: 440px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 40px 80px;
            color: #fff;
        }
        .slide-fallback h1 {
            font-size: 60px;
            font-weight: 900;
            line-height: 1.1;
        }

        .slide-1 { background: linear-gradient(135deg, #001a5c 0%, #003da5 60%, #0060d0 100%); }
        .slide-2 { background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 60%, #16213e 100%); }
        .slide-3 { background: linear-gradient(135deg, #1a0a00 0%, #3d1500 60%, #6b2500 100%); }

        .slide-hero-img {
            max-height: 360px;
            object-fit: contain;
            filter: drop-shadow(0 10px 30px rgba(0,0,0,.5));
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,.25);
            border: 2px solid rgba(255,255,255,.5);
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
            z-index: 10;
        }
        .slider-btn:hover { background: rgba(255,255,255,.5); }
        .slider-btn.prev { left: 16px; }
        .slider-btn.next { right: 16px; }

        .slider-dots {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
        }
        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,.45);
            cursor: pointer;
            transition: background .2s, transform .2s;
        }
        .dot.active { background: #fff; transform: scale(1.3); }

        /* ── LIVE CHAT ── */
        .live-chat {
            position: fixed;
            bottom: 30px;
            left: 24px;
            background: #cc0000;
            color: #fff;
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(200,0,0,.4);
            transition: transform .2s;
        }
        .live-chat:hover { transform: scale(1.05); }

        /* ── FOR YOU TAB ── */
        .for-you-tab {
            position: fixed;
            right: 0;
            top: 50%;
            transform: translateY(-50%) rotate(90deg) translateX(50%);
            background: #003da5;
            color: #fff;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            transform-origin: right center;
            writing-mode: horizontal-tb;
            transform: rotate(90deg);
            right: -20px;
            top: 50%;
            z-index: 100;
        }

        /* ── SECTION WRAPPER ── */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ── SHOP BY CATEGORY ── */
        .section-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            padding: 28px 0 18px;
            color: #111;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 28px;
        }
        .category-card {
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            aspect-ratio: 1.1;
            background: #1a0a20;
            transition: transform .2s, box-shadow .2s;
        }
        .category-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,.2); }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .85;
        }
        .category-card .cat-label {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            text-shadow: 0 1px 4px rgba(0,0,0,.7);
        }

        /* Category placeholders */
        .cat-pc { background: linear-gradient(135deg, #1a0a20, #3d1060); }
        .cat-desktop { background: linear-gradient(135deg, #0a1a30, #1060a0); }
        .cat-laptop { background: linear-gradient(135deg, #100a20, #602090); }
        .cat-monitor { background: linear-gradient(135deg, #1a0a10, #a02050); }
        .cat-gaming { background: linear-gradient(135deg, #0a100a, #206020); }
        .cat-peripherals { background: linear-gradient(135deg, #1a1a0a, #806020); }

        /* Payment banner */
        .payment-banner {
            background: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 20px 0 32px;
            font-size: 18px;
            font-weight: 700;
            color: #111;
        }
        .payment-banner .visa { color: #1a1f71; font-size: 28px; font-weight: 900; font-style: italic; }
        .payment-banner .mc {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            font-weight: 700;
            color: #333;
        }
        .mc-circles {
            display: flex;
        }
        .mc-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
        }
        .mc-red { background: #eb001b; margin-right: -10px; }
        .mc-orange { background: #f79e1b; }

        /* ── PRODUCT SECTION ── */
        .product-section {
            display: flex;
            gap: 24px;
            margin-bottom: 40px;
            align-items: flex-start;
        }
        .section-banner {
            min-width: 260px;
            max-width: 260px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        .section-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            min-height: 300px;
        }

        /* Fallback banners */
        .banner-taiwan { background: linear-gradient(135deg, #fff 60%, #f5f5f5); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; min-height: 300px; }
        .banner-taiwan .tw-logo { width: 80px; margin-bottom: 10px; }
        .banner-taiwan h2 { font-size: 22px; font-weight: 900; color: #cc0000; text-align: center; line-height: 1.2; }
        .banner-taiwan p { font-size: 13px; color: #666; font-style: italic; }

        .banner-nvidia { background: linear-gradient(135deg, #000 30%, #1a2a1a); color: #76b900; min-height: 300px; display: flex; flex-direction: column; justify-content: flex-end; padding: 24px; }
        .banner-nvidia h2 { font-size: 28px; font-weight: 900; line-height: 1.1; color: #fff; }
        .banner-nvidia span { color: #76b900; }

        .banner-intel { background: linear-gradient(135deg, #0068b5 0%, #003f7a); min-height: 300px; display: flex; align-items: center; justify-content: center; }
        .banner-intel h2 { font-size: 48px; font-weight: 900; color: #fff; }

        .banner-asus { background: linear-gradient(135deg, #000 30%, #111); color: #fff; min-height: 300px; display: flex; flex-direction: column; justify-content: flex-end; padding: 24px; }
        .banner-asus h2 { font-size: 28px; font-weight: 900; }

        .banner-lenovo { background: linear-gradient(135deg, #e2231a 0%, #8b0000); min-height: 300px; display: flex; flex-direction: column; justify-content: center; padding: 24px; }
        .banner-lenovo h2 { font-size: 28px; font-weight: 700; color: #fff; }
        .banner-lenovo p { color: rgba(255,255,255,.8); font-size: 13px; margin-top: 8px; }

        .banner-laptops { background: linear-gradient(135deg, #003da5 0%, #0060d0); min-height: 300px; display: flex; align-items: center; justify-content: center; }
        .banner-laptops h2 { font-size: 42px; font-weight: 900; color: #fff; }

        .banner-pcx-desktop { background: linear-gradient(135deg, #000 30%, #1a1a3e); min-height: 300px; display: flex; flex-direction: column; justify-content: flex-end; padding: 24px; }
        .banner-pcx-desktop h2 { font-size: 28px; font-weight: 900; color: #fff; }

        .banner-pccomponents { background: linear-gradient(135deg, #001a5c 0%, #003da5); min-height: 300px; display: flex; align-items: center; justify-content: center; }
        .banner-pccomponents h2 { font-size: 32px; font-weight: 900; color: #fff; text-align: center; }

        .banner-consoles { background: linear-gradient(135deg, #001a5c 0%, #0040a0 50%, #002060); min-height: 300px; display: flex; flex-direction: column; justify-content: center; padding: 24px; }
        .banner-consoles h2 { font-size: 26px; font-weight: 900; color: #fff; }

        .section-right {
            flex: 1;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .section-header h2 { font-size: 20px; font-weight: 700; color: #111; }
        .view-all {
            border: 2px solid #003da5;
            color: #003da5;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
        }
        .view-all:hover { background: #003da5; color: #fff; }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .product-card {
            background: #fff;
            border-radius: 6px;
            border: 2px solid transparent;
            overflow: hidden;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s, transform .2s;
            position: relative;
        }
        .product-card:hover {
            border-color: #003da5;
            box-shadow: 0 4px 16px rgba(0,61,165,.15);
            transform: translateY(-2px);
        }
        .product-card.taiwan-badge { border-color: #cc0000; }

        .product-img-wrap {
            position: relative;
            background: #f8f8f8;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .product-img-wrap img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            transition: transform .3s;
        }
        .product-card:hover .product-img-wrap img { transform: scale(1.05); }

        .product-actions {
            position: absolute;
            right: 8px;
            top: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            opacity: 0;
            transition: opacity .2s;
        }
        .product-card:hover .product-actions { opacity: 1; }
        .action-btn {
            width: 32px;
            height: 32px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            color: #555;
            transition: background .2s, color .2s;
        }
        .action-btn:hover { background: #003da5; color: #fff; border-color: #003da5; }

        .taiwan-badge-img {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 50px;
        }

        .discount-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #cc0000;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 3px;
        }

        .pcx-badge {
            position: absolute;
            bottom: 6px;
            right: 6px;
            background: #003da5;
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            padding: 2px 6px;
            border-radius: 2px;
        }

        .add-to-cart-btn {
            display: none;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #003da5;
            color: #fff;
            border: none;
            padding: 10px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            letter-spacing: .5px;
        }
        .product-card:hover .add-to-cart-btn { display: block; }

        .product-info { padding: 10px 12px 14px; }
        .product-brand { font-size: 11px; color: #003da5; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .product-name { font-size: 12px; color: #333; line-height: 1.4; margin-bottom: 8px; min-height: 34px; }
        .product-price { font-size: 14px; font-weight: 700; color: #cc0000; }
        .product-old-price { font-size: 11px; color: #999; text-decoration: line-through; margin-right: 6px; }
        .price-row { display: flex; align-items: baseline; gap: 4px; }

        /* ── PROMOTIONS SLIDER ── */
        .promos-section { padding: 28px 0 0; }
        .promos-title { font-size: 20px; font-weight: 700; margin-bottom: 16px; }
        .promos-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 40px;
        }
        .promo-card {
            border-radius: 8px;
            overflow: hidden;
            height: 160px;
            background: linear-gradient(135deg, #003da5, #0060d0);
            cursor: pointer;
            transition: transform .2s;
        }
        .promo-card:hover { transform: translateY(-3px); }
        .promo-card img { width: 100%; height: 100%; object-fit: cover; }

        /* ── FOOTER ── */
        footer {
            background: #1a1a2e;
            color: #ccc;
            padding: 40px 0 20px;
            margin-top: 40px;
        }
        .footer-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
        }
        .footer-logo { font-size: 24px; font-weight: 900; margin-bottom: 12px; }
        .footer-logo span.pc { color: #4a80ff; }
        .footer-logo span.ex { color: #ff4444; }
        .footer p { font-size: 13px; line-height: 1.7; color: #aaa; }
        .footer h4 { color: #fff; font-size: 14px; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .footer ul { list-style: none; }
        .footer ul li { margin-bottom: 8px; }
        .footer ul li a { color: #aaa; font-size: 13px; transition: color .2s; }
        .footer ul li a:hover { color: #fff; }
        .footer-bottom {
            text-align: center;
            padding: 20px 24px 0;
            border-top: 1px solid #333;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }

        /* ── CART DRAWER ── */
        .cart-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;opacity:0;pointer-events:none;transition:opacity .3s ease;}
        .cart-overlay.open{opacity:1;pointer-events:all;}
        .cart-drawer{position:fixed;top:0;right:0;width:420px;max-width:100vw;height:100vh;background:#fff;z-index:2001;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .35s cubic-bezier(.4,0,.2,1);box-shadow:-4px 0 28px rgba(0,0,0,.18);}
        .cart-drawer.open{transform:translateX(0);}
        .cart-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #e8e8e8;}
        .cart-header h2{font-size:16px;font-weight:800;color:#111;text-transform:uppercase;letter-spacing:1.5px;}
        .cart-close-btn{width:32px;height:32px;background:none;border:none;font-size:22px;cursor:pointer;color:#555;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background .2s,color .2s;line-height:1;}
        .cart-close-btn:hover{background:#f0f0f0;color:#cc0000;}
        .cart-body{flex:1;overflow-y:auto;}
        .cart-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 24px;text-align:center;}
        .cart-empty-icon{font-size:60px;color:#ddd;margin-bottom:16px;}
        .cart-empty h3{font-size:17px;font-weight:700;color:#333;margin-bottom:8px;}
        .cart-empty p{font-size:13px;color:#888;margin-bottom:20px;}
        .btn-return{background:#003da5;color:#fff;border:none;padding:11px 28px;font-weight:700;border-radius:4px;cursor:pointer;font-size:14px;}
        .cart-item{display:grid;grid-template-columns:90px 1fr auto;border-bottom:1px solid #f0f0f0;position:relative;animation:slideIn .22s ease;}
        @keyframes slideIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
        .cart-item-thumb{width:90px;height:90px;padding:10px;display:flex;align-items:center;justify-content:center;background:#fff;border-right:1px solid #f0f0f0;flex-shrink:0;}
        .cart-item-thumb img{max-width:72px;max-height:72px;object-fit:contain;border:1px solid #e8e8e8;border-radius:4px;background:#f8f8f8;}
        .cart-item-details{padding:12px 12px 10px;display:flex;flex-direction:column;justify-content:space-between;min-width:0;}
        .cart-item-name{font-size:12.5px;font-weight:600;color:#111;line-height:1.4;margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
        .cart-item-name a{color:#003da5;text-decoration:none;}
        .cart-item-prices{display:flex;align-items:baseline;gap:8px;margin-bottom:10px;}
        .cart-item-old{font-size:11px;color:#999;text-decoration:line-through;}
        .cart-item-price{font-size:15px;font-weight:800;color:#cc0000;}
        .cart-item-qty{display:flex;align-items:center;}
        .qty-btn{width:28px;height:28px;border:1px solid #ddd;background:#fff;font-size:16px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#333;transition:background .15s,color .15s;line-height:1;}
        .qty-btn:first-child{border-radius:4px 0 0 4px;}
        .qty-btn:last-child{border-radius:0 4px 4px 0;}
        .qty-btn:hover{background:#003da5;color:#fff;border-color:#003da5;}
        .qty-num{width:32px;height:28px;border-top:1px solid #ddd;border-bottom:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#222;}
        .cart-item-remove{width:24px;display:flex;align-items:flex-start;justify-content:center;padding-top:12px;padding-right:10px;flex-shrink:0;}
        .cart-item-remove button{background:none;border:none;color:#bbb;font-size:18px;cursor:pointer;line-height:1;padding:0;transition:color .2s;}
        .cart-item-remove button:hover{color:#cc0000;}
        .cart-also-like{background:#f9f9f9;border-top:1px solid #eee;border-bottom:1px solid #eee;padding:14px 16px;}
        .cart-also-like h4{font-size:12px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;}
        .also-like-item{display:flex;align-items:center;gap:10px;cursor:pointer;padding:8px;border-radius:6px;transition:background .15s;}
        .also-like-item:hover{background:#fff;}
        .also-like-item img{width:50px;height:50px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f8f8f8;flex-shrink:0;}
        .also-like-info{flex:1;min-width:0;}
        .also-like-name{font-size:11.5px;font-weight:600;color:#003da5;line-height:1.3;margin-bottom:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
        .also-like-prices{display:flex;align-items:baseline;gap:6px;}
        .also-like-old{font-size:11px;color:#999;text-decoration:line-through;}
        .also-like-price{font-size:13px;font-weight:700;color:#cc0000;}
        .cart-action-icons{display:flex;align-items:center;justify-content:center;gap:28px;padding:14px 16px;border-bottom:1px solid #f0f0f0;}
        .cart-action-icon{display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;font-size:11px;color:#666;transition:color .2s;position:relative;}
        .cart-action-icon:hover{color:#003da5;}
        .cart-action-icon i{font-size:20px;}
        .cart-action-label{font-size:11px;color:inherit;font-weight:500;}
        .cart-footer{padding:16px 20px;border-top:2px solid #f0f0f0;background:#fff;}
        .cart-subtotal-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;}
        .cart-subtotal-label{font-size:13px;color:#555;font-weight:600;}
        .cart-subtotal-amount{font-size:18px;font-weight:900;color:#111;}
        .cart-terms-row{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
        .cart-terms-row input[type=checkbox]{width:15px;height:15px;accent-color:#003da5;cursor:pointer;}
        .cart-terms-row label{font-size:12px;color:#555;cursor:pointer;line-height:1.4;}
        .cart-terms-row a{color:#003da5;}
        .cart-terms-error{display:none;background:#fff8d0;border:1px solid #e5c200;color:#7a6000;font-size:12px;padding:8px 12px;border-radius:4px;margin-bottom:10px;align-items:center;gap:8px;}
        .cart-terms-error.show{display:flex;}
        .view-cart-link{display:block;text-align:center;font-size:13px;font-weight:700;color:#003da5;text-decoration:underline;letter-spacing:.5px;margin-bottom:12px;cursor:pointer;}
        .btn-checkout{width:100%;padding:14px;background:#003da5;color:#fff;border:none;font-size:15px;font-weight:800;cursor:pointer;border-radius:4px;letter-spacing:.8px;transition:background .2s;text-transform:uppercase;}
        .btn-checkout:hover{background:#002880;}
        .cart-toast{position:fixed;bottom:28px;right:28px;background:#222;color:#fff;padding:13px 18px;border-radius:8px;font-size:13.5px;font-weight:600;z-index:4000;display:flex;align-items:center;gap:10px;transform:translateY(20px);opacity:0;transition:transform .3s ease,opacity .3s ease;box-shadow:0 4px 20px rgba(0,0,0,.3);max-width:320px;}
        .cart-toast.show{transform:translateY(0);opacity:1;}
        .toast-check{color:#28a745;font-size:17px;}

        /* ── RESPONSIVE ── */
        @media (max-width: 1100px) {
            .category-grid { grid-template-columns: repeat(3, 1fr); }
            .products-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .header-inner { flex-wrap: wrap; }
            .category-grid { grid-template-columns: repeat(2, 1fr); }
            .products-grid { grid-template-columns: repeat(2, 1fr); }
            .product-section { flex-direction: column; }
            .section-banner { min-width: 100%; max-width: 100%; }
            .footer-inner { grid-template-columns: 1fr 1fr; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════ HEADER ══════════════════════════ -->
<header class="header">
    <div class="header-inner">
        <!-- Logo -->
        <a href="home.php" class="logo">
            <div class="logo-text"><span class="pc">PC</span><span class="ex">EXPRESS</span></div>
        </a>

        <!-- Search -->
        <div class="search-bar">
            <select>
                <option>All Categories</option>
                <option>PC Components</option>
                <option>Laptops</option>
                <option>Desktop PCs</option>
                <option>Monitors</option>
                <option>Gaming Devices</option>
                <option>Peripherals</option>
            </select>
            <input type="text" placeholder="Search for products">
            <button><i class="fas fa-search"></i></button>
        </div>

        <!-- Actions -->
        <div class="header-actions">
            <a href="#" onclick="openPanel('login'); return false;" title="Account">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Account</span>
            </a>
            <a href="wishlist.php" title="Wishlist">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span>Wishlist</span>
                <span class="badge" id="wishBadge" style="display:none;">0</span>
            </a>
            <a href="#" title="Cart" onclick="openCart(); return false;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.8">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span>Cart</span>
                <span class="badge" id="cartBadge">0</span>
            </a>
        </div>
    </div>
</header>

<!-- ══════════════════════════ NAVBAR ══════════════════════════ -->
<nav class="navbar">
    <div class="navbar-inner">
        <div class="all-products-btn" onclick="this.classList.toggle('open')">
            <i class="fas fa-bars"></i> All Products
            <i class="fas fa-chevron-down" style="font-size:11px;margin-left:4px;"></i>
            <div class="dropdown-menu" onclick="event.stopPropagation()">
                <a href="#">PC Components <i class="fas fa-chevron-right" style="font-size:10px;"></i></a>
                <a href="#">PC Peripherals &amp; Accessories <i class="fas fa-chevron-right" style="font-size:10px;"></i></a>
                <a href="#">Gaming Devices &amp; Accessories <i class="fas fa-chevron-right" style="font-size:10px;"></i></a>
                <a href="#">Desktop PCs</a>
                <a href="#">Laptops</a>
                <a href="#">Smartphones</a>
                <a href="#">Business Solutions</a>
                <a href="#">Software</a>
                <a href="#">All Products</a>
            </div>
        </div>
        <div class="nav-links">
            <a href="#">Taiwan Excellence</a>
            <a href="#">AMD</a>
            <a href="#">Powered by ASUS</a>
            <a href="#">ASUS NUC</a>
            <a href="#">NVIDIA</a>
            <a href="#">Intel</a>
            <a href="#">Microsoft</a>
            <a href="#">Lenovo</a>
            <a href="#">Powered by MSI</a>
            <a href="#">Samsung Monitors</a>
            <a href="#" style="border-top:1px solid rgba(255,255,255,.2);margin-top:2px;">Business Solutions</a>
            <a href="#">↓ Pricelists</a>
            <a href="#" class="laptop-sale">🖥 Laptop Sale!</a>
            <a href="#">Product Spotlight</a>
            <a href="#">News &amp; Promos ▾</a>
            <a href="#">Info ▾</a>
        </div>
    </div>
</nav>

<!-- ══════════════════════════ HERO SLIDER ══════════════════════════ -->
<div class="hero-slider">
    <div class="slides-wrapper" id="slidesWrapper">

        <!-- Slide 1 -->
        <div class="slide">
            <div class="slide-image">
                <img src="https://cdn.shopify.com/s/files/1/0749/0525/6234/files/Taiwan-Products_4005d16c-e9a5-433a-b183-754f963b7a6c.png?v=1715821496" alt="Taiwan Products">
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="slide">
            <div class="slide-image">
                <img src="https://pcx.com.ph/cdn/shop/files/AMD_Aorus_Family_-_1800x600_f451b045-c395-43b5-8f79-9a58d00b5f69.jpg?v=1777255990&width=1500" alt="AMD Aorus Family">
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="slide">
            <div class="slide-image">
                <img src="https://pcx.com.ph/cdn/shop/files/1800x600-Samsung-AI-Monitor.jpg?v=1765177814&width=1600" alt="Samsung AI Monitor">
            </div>
        </div>

        <!-- Slide 4 -->
        <div class="slide">
            <div class="slide-image">
                <img src="https://pcx.com.ph/cdn/shop/files/1800p_x_600p_Business_solutions.jpg?v=1739784992&width=1500" alt="Business Solutions">
            </div>
        </div>

        <!-- Slide 5 -->
        <div class="slide">
            <div class="slide-image">
                <img src="https://pcx.com.ph/cdn/shop/files/PCX_1800x600_b2923a14-4d61-449c-ab4b-c3a509c0a10f.jpg?v=1753682972&width=1600" alt="PCX Desktop">
            </div>
        </div>

        <!-- Slide 6 -->
        <div class="slide">
            <div class="slide-image">
                <img src="https://pcx.com.ph/cdn/shop/files/1800x600-Samsung-Odyssey-OLED-G8.jpg?v=1765174192&width=1500" alt="PCX Desktop">
            </div>
        </div>

    </div>

    <!-- Prev/Next buttons -->
    <button class="slider-btn prev" onclick="changeSlide(-1)">&#8249;</button>
    <button class="slider-btn next" onclick="changeSlide(1)">&#8250;</button>

    <!-- Dots -->
    <div class="slider-dots" id="sliderDots"></div>
</div>

<!-- ══════════════════════════ MAIN CONTENT ══════════════════════════ -->
<div class="main-content">

    <!-- Shop by Category -->
    <h2 class="section-title">Shop by Category</h2>
    <div class="category-grid">
        <?php
        $categories = [
            ['label'=>'PC COMPONENTS',           'class'=>'cat-pc',
            'img'=>'https://pcx.com.ph/cdn/shop/collections/Components-400x400.jpg?v=1766214282&width=400'],
            ['label'=>'Desktop PCs',             'class'=>'cat-desktop',
            'img'=>'https://pcx.com.ph/cdn/shop/collections/WebDesktopPC-400x400.jpg?v=1766214387&width=400'],
            ['label'=>'LAPTOPS',                 'class'=>'cat-laptop',
            'img'=>'https://pcx.com.ph/cdn/shop/collections/WebGamingLaptops-400x400_773d9373-dc09-47d4-a9df-07251f4e0d31.jpg?v=1766214741&width=400'],
            ['label'=>'MONITORS',                'class'=>'cat-monitor',
            'img'=>'https://pcx.com.ph/cdn/shop/collections/Monitors-400x400.jpg?v=1774425098&width=400'],
            ['label'=>'GAMING DEVICES & ACCESSORIES','class'=>'cat-gaming',
            'img'=>'https://pcx.com.ph/cdn/shop/collections/WebGamingDevices-400x400.jpg?v=1766214813&width=400'],
            ['label'=>'PERIPHERALS & ACCESSORIES','class'=>'cat-peripherals',
            'img'=>'https://pcx.com.ph/cdn/shop/collections/WebPeripherals-400x400_2a0357ff-8bac-4c33-ad77-87279974fb6c.jpg?v=1766215104&width=400'],
        ];
        foreach ($categories as $cat): ?>
        <div class="category-card <?= $cat['class'] ?>" onclick="window.location='#'">
           <img src="<?= $cat['img'] ?>" 
                alt="<?= $cat['label'] ?>" 
                class="category-image">
            <div class="cat-label"><?= $cat['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Payment Banner -->
    <div class="payment-banner">
        <span>Online payments are now available with Visa and MasterCard</span>
        <span class="visa">VISA</span>
        <div class="mc">
            <div class="mc-circles">
                <div class="mc-circle mc-red"></div>
                <div class="mc-circle mc-orange"></div>
            </div>
            <span style="margin-left:8px;">mastercard.</span>
        </div>
    </div>

    <!-- ── NEW IN STORE (Live from Database) ── -->
    <!-- ── NEW IN STORE (Live from Firestore) ── -->
<div class="product-section" id="new-in-store" style="display:none;">
    <div class="section-banner">
        <div style="background:linear-gradient(135deg,#003da5,#cc0000);color:#fff;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;border-radius:8px;">
            <div style="font-size:40px;margin-bottom:10px;">🛒</div>
            <h2 style="font-size:20px;font-weight:900;letter-spacing:-0.5px;margin-bottom:6px;">NEW IN STORE</h2>
            <p style="font-size:12px;opacity:.85;">Fresh from our catalog</p>
        </div>
    </div>
    <div class="section-right">
        <div class="section-header">
            <h2>New In Store</h2>
            <a href="#" class="view-all">View All →</a>
        </div>
        <div class="products-grid" id="newInStoreGrid"></div>
    </div>
</div>

    <!-- ── TAIWAN EXCELLENCE ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-taiwan">
                <div style="font-size:50px;margin-bottom:10px;">🏅</div>
                <h2>TAIWAN EXCELLENCE</h2>
                <p>Everyday Excellence</p>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>Taiwan Excellence</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $tw_products = [
                    ['id'=>'asus-vg27aq3a','brand'=>'Asus',
                    'name'=>'ASUS TUF Gaming VG27AQ3A 27-inch 180Hz Gaming Monitor',
                    'price'=>'₱13,450.00','old'=>'','disc'=>'',
                    'img'=>'https://dlcdnwebimgs.asus.com/gain/17123866-eeb2-4e14-83c1-421d13bdcde5/w692'],
                    ['id'=>'asus-sdrw-08d2s','brand'=>'Asus','name'=>'ASUS SDRW-08D2S-U LITE 8X External Optical Drive (Black)','price'=>'₱1,750.00','old'=>'','disc'=>''
                    ,'img'=>'https://www.asus.com/media/global/products/m2WrHtUaWZvxYLfb/P_setting_xxx_0_90_end_500.png'],
                    ['id'=>'asus-ai-mic','brand'=>'Asus','name'=>'ASUS AI NOISE CANCELLING (TYPE C TO 3.5MM) Mic Adapter','price'=>'₱1,750.00','old'=>'','disc'=>''
                    ,'img'=>'https://www.asus.com/media/global/products/wcdq6rux9c9gmdvg/P_setting_xxx_0_90_end_500.png'],
                    ['id'=>'asus-vg279q3a','brand'=>'Asus','name'=>'ASUS 27" VG279Q3A IPS TUF Gaming Monitor','price'=>'₱10,000.00','old'=>'₱12,100.00','disc'=>'-17%',
                    'img'=>'https://dlcdnwebimgs.asus.com/gain/63f94625-d0c2-43be-8b17-7054220cdb85/w692'],
                ];
                foreach ($tw_products as $p): ?>
                <div class="product-card taiwan-badge" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist"
                                 data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>"
                                 data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                 data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>"
                                 data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>"
                                 data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>"
                                 data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn" title="Quick View">👁</div>
                            <div class="action-btn" title="Compare">⇄</div>
                        </div>
                        <?php if ($p['disc']): ?><div class="discount-badge"><?= $p['disc'] ?></div><?php endif; ?>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <?php if ($p['old']): ?><span class="product-old-price"><?= $p['old'] ?></span><?php endif; ?>
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── NVIDIA ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-nvidia">
                <div style="font-size:40px;margin-bottom:12px;">🎮</div>
                <h2>Game Changer<br><span>GeForce RTX 50 Series,</span><br>Powered by Blackwell and AI.</h2>
                <button style="margin-top:16px;background:#76b900;color:#000;border:none;padding:8px 18px;font-weight:700;border-radius:4px;cursor:pointer;font-size:13px;">Learn More</button>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>NVIDIA</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $nv_products = [
                    ['id'=>'pcx-project-s-5090','brand'=>'PCX','name'=>'PCX PROJECT S (PRO-R95090) GeForce RTX™ 5090 AMD Ryzen™ 9 Gaming Desktop - Powered By ASUS','price'=>'From ₱390,995.00','old'=>'','disc'=>''
                    ,'img'=>'https://pcx.com.ph/cdn/shop/files/R9-5090-MIDFEB.jpg?v=1771481266'],
                    ['id'=>'msi-rtx-5080','brand'=>'MSI','name'=>'MSI GeForce RTX™ 5080 VENTUS 3X OC 16GB (G5080-16V3C) GDDR7 256-bit Graphics Card','price'=>'₱88,550.00','old'=>'','disc'=>''
                    ,'img'=>'https://storage-asset.msi.com/global/picture/image/feature/vga/NVIDIA/new-gen/GB203-300-VENTUS-3X-OC/kv/msi-70ventus-ti-3x-oc.webp'],
                    ['id'=>'lenovo-loq-rtx5060','brand'=>'Lenovo','name'=>'LENOVO LOQ 15AHP10 (83JG00C4PH) GeForce RTX™ 5060 AMD Ryzen™ 7 Laptop (Luna Grey)','price'=>'₱79,995.00','old'=>'₱82,995.00','disc'=>'-4%',
                    'img'=>'https://villman.com/product_photos/gif_v7pzo_bby4u_h6fmg_vh4kw.gif'],
                    ['id'=>'hp-victus-rtx3050','brand'=>'HP','name'=>'HP VICTUS 15-FB3223AX (D07C7PA) GeForce RTX™ 3050 AMD Ryzen™ 5 Laptop (Mica Silver)','price'=>'₱52,990.00','old'=>'₱53,990.00','disc'=>'-2%',
                    'img'=>'https://hp.widen.net/content/8ngitvensp/original/8ngitvensp.png'],
                ];
                foreach ($nv_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <?php if ($p['disc']): ?><div class="discount-badge"><?= $p['disc'] ?></div><?php endif; ?>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <?php if ($p['old']): ?><span class="product-old-price"><?= $p['old'] ?></span><?php endif; ?>
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── INTEL ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-intel">
                <div style="text-align:center;">
                    <div style="font-size:50px;margin-bottom:10px;">💡</div>
                    <h2>intel</h2>
                    <div style="color:#0068b5;background:#fff;padding:4px 12px;border-radius:4px;font-size:13px;font-weight:700;margin-top:8px;">CORE</div>
                </div>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>Intel</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $intel_products = [
                    ['id'=>'acer-aspire3-i7','brand'=>'Acer','name'=>'ACER Aspire 3 A315-59-726B Intel® Core™ i7 Laptop (Pure Silver)','price'=>'₱44,999.00','old'=>'₱45,999.00','disc'=>'-2%',
                    'img'=>'https://villman.com/product_photos/gif_5c86e.gif'],
                    ['id'=>'acer-aspire5-rtx2050','brand'=>'Acer','name'=>'ACER Aspire 5 A515-58GM-58ZY GeForce RTX™ 2050 Intel® Core™ i5 Laptop (Steel Gray)','price'=>'₱36,899.00','old'=>'₱40,999.00','disc'=>'-10%',
                    'img'=>'https://villman.com/product_photos/gif_rem3a.gif'],
                    ['id'=>'acer-aspire7-rtx3050-a','brand'=>'Acer','name'=>'ACER Aspire 7 A715-59G-5618 GeForce RTX™ 3050 Intel® Core™ i5 Laptop (Titanium Black)','price'=>'₱51,999.00','old'=>'₱52,999.00','disc'=>'-2%',
                    'img'=>'https://villman.com/product_photos/ezgif-1-f2ca7bc0f6_dcyho_0bbfg_n5mfr.gif'],
                    ['id'=>'acer-aspire7-rtx3050-b','brand'=>'Acer','name'=>'ACER Aspire 7 A715-59G-56W7 GeForce RTX™ 3050 Intel® Core™ i5 Laptop (Titanium Black)','price'=>'₱46,999.00','old'=>'₱47,999.00','disc'=>'-2%',
                    'img'=>'https://villman.com/product_photos/ezgif-1-f2ca7bc0f6_dcyho_0bbfg_n5mfr.gif'],
                ];
                foreach ($intel_products as $p): ?>
                <div class="product-card taiwan-badge" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <div class="discount-badge"><?= $p['disc'] ?></div>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <span class="product-old-price"><?= $p['old'] ?></span>
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── PCX Desktops Powered by ASUS ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-asus">
                <div style="font-size:40px;margin-bottom:10px;">🖥️</div>
                <h2>DOMINATE<br>WITH THE<br>BEST</h2>
                <button style="margin-top:14px;background:#cc0000;color:#fff;border:none;padding:8px 18px;font-weight:700;border-radius:4px;cursor:pointer;font-size:13px;">SHOP NOW</button>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>PCX Desktops Powered by ASUS</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $asus_products = [
                    ['id'=>'pcx-hydra-i5-v2','brand'=>'PCX','name'=>'PCX GFH HYDRA i5 V2 - TEAMGROUP GeForce RTX™ 4060 Dual OC Gaming Desktop - Powered By ASUS','price'=>'From ₱59,995.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/HYDRAi5V21.jpg?v=1776326661&width=800'],
                    ['id'=>'pcx-genius-g','brand'=>'PCX','name'=>'PCX LFH GENIUS G Intel® Pentium G6400 Desktop Package - Powered By ASUS','price'=>'From ₱14,995.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/GeniusG_71009a1b-d433-47c7-8281-c1e5bb50c1cd.jpg?v=1769765109&width=600'],
                    ['id'=>'pcx-whiz-i3','brand'=>'PCX','name'=>'PCX LFH WHIZ (GI31200) Intel® Core™ i3 Desktop Package - Powered By ASUS','price'=>'From ₱20,995.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/Whiz12thGeni3DDR4.jpg?v=1776325147&width=600'],
                    ['id'=>'pcx-drake-r7-5060','brand'=>'PCX','name'=>'PCX GFH DRAKE (R7) GeForce RTX™ 5060 AMD Ryzen™ 7 Desktop - Powered By ASUS','price'=>'From ₱89,500.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/DESKTOP-PCX_GFH_DRAKE_R7_5060_-_Desktop_Only.jpg?v=1776489661&width=600'],
                ];
                foreach ($asus_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── LENOVO ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-lenovo">
                <div style="background:#e2231a;color:#fff;font-size:28px;font-weight:900;padding:6px 14px;border-radius:3px;display:inline-block;margin-bottom:16px;">Lenovo</div>
                <h2>Smarter technology for all</h2>
                <p>Discover the latest technology with Lenovo laptops, all-in-one PCs, tower PCs, monitors and more.</p>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>Lenovo</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $lenovo_products = [
                    ['id'=>'lenovo-yoga7-ultra7','brand'=>'Lenovo','name'=>'LENOVO YOGA 7 2-IN-1 14ILL10 (83JQ00DKPH) Intel® Core™ Ultra 7 Laptop (Luna Grey)','price'=>'₱88,995.00','old'=>'₱90,995.00','disc'=>'-2%',
                    'img'=>'https://villman.com/product_photos/yoga-7i-2-in-1-14ill10-gif-300px-062425_dhil6_4m2c7.gif'],
                    ['id'=>'lenovo-loq-rtx5060','brand'=>'Lenovo','name'=>'LENOVO LOQ 15AHP10 (83JG00C4PH) GeForce RTX™ 5060 AMD Ryzen™ 7 Laptop (Luna Grey)','price'=>'₱79,995.00','old'=>'₱82,995.00','disc'=>'-4%',
                    'img'=>'https://villman.com/product_photos/gif_v7pzo_bby4u_h6fmg_vh4kw.gif'],
                    ['id'=>'lenovo-thinkpad-t14-ultra7','brand'=>'Lenovo','name'=>'LENOVO Thinkpad T14 Gen 6 (21QC0075PH) Intel® Core™ Ultra 7 Laptop (Black)','price'=>'₱106,995.00','old'=>'₱110,995.00','disc'=>'-4%',
                    'img'=>'https://villman.com/product_photos/thinkpad-t14-gif-300px-062425_iifh4.gif'],
                    ['id'=>'lenovo-thinkpad-t14-ultra5','brand'=>'Lenovo','name'=>'LENOVO Thinkpad T14 Gen 6 (21QC0072PH) Intel® Core™ Ultra 5 Laptop (Black)','price'=>'₱99,995.00','old'=>'₱104,995.00','disc'=>'-5%',
                    'img'=>'https://p1-ofp.static.pub//fes/cms/2025/04/08/mi5qm8tr8bx7flftut4lop021yuv1z747949.png?width=584&height=584'],
                ];
                foreach ($lenovo_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <div class="discount-badge"><?= $p['disc'] ?></div>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <span class="product-old-price"><?= $p['old'] ?></span>
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── LAPTOPS ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-laptops">
                <h2>Laptops</h2>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>Laptops</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $laptop_products = [
                    ['id'=>'acer-aspire3-i7','brand'=>'Acer','name'=>'ACER Aspire 3 A315-59-726B Intel® Core™ i7 Laptop (Pure Silver)','price'=>'₱44,999.00','old'=>'₱45,999.00','disc'=>'-2%',
                    'img'=>'https://villman.com/product_photos/gif_5c86e.gif'],
                    ['id'=>'acer-aspire5-rtx2050','brand'=>'Acer','name'=>'ACER Aspire 5 A515-58GM-58ZY GeForce RTX™ 2050 Intel® Core™ i5 Laptop (Steel Gray)','price'=>'₱36,899.00','old'=>'₱40,999.00','disc'=>'-10%',
                    'img'=>'https://villman.com/product_photos/gif_rem3a.gif'],
                    ['id'=>'acer-aspire-a15-core5','brand'=>'Acer','name'=>'ACER Aspire A15-51M-56E2 Intel® Core™ 5 Laptop (Steel Gray)','price'=>'₱38,999.00','old'=>'₱39,999.00','disc'=>'-3%',
                    'img'=>'https://villman.com/product_photos/gif_djlzb.gif'],
                    ['id'=>'acer-aspire-a315-ryzen7','brand'=>'Acer','name'=>'ACER Aspire A315-44P-R9LQ AMD Ryzen™ 7 Laptop (Pure Silver)','price'=>'₱36,999.00','old'=>'₱37,999.00','disc'=>'-3%',
                    'img'=>'https://villman.com/product_photos/gif_tk3p7.gif'],
                ];
                foreach ($laptop_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <?php if ($p['disc']): ?><div class="discount-badge"><?= $p['disc'] ?></div><?php endif; ?>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <?php if ($p['old']): ?><span class="product-old-price"><?= $p['old'] ?></span><?php endif; ?>
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── PCX DESKTOP ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-pcx-desktop">
                <div style="font-size:40px;margin-bottom:12px;">🖥️</div>
                <h2>PCX Desktop</h2>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>PCX Desktop</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $pcxd_products = [
                    ['id'=>'pcx-project-s-u9-5090','brand'=>'PCX','name'=>'PCX PROJECT S GeForce RTX™ 5090 OC Intel® Core™ Ultra 9 Gaming & Studio Desktop - Powered By ASUS','price'=>'₱425,995.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/PROJECT_S_PRO-U95090.jpg?v=1773364959&width=600'],
                    ['id'=>'pcx-dragon-forge-r9','brand'=>'PCX','name'=>'PCX GFH DRAGON FORGE (R9-5070) AMD Ryzen™ 9 Gaming Desktop','price'=>'From ₱130,995.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/DRAGONFORGE.jpg?v=1769847196&width=600'],
                    ['id'=>'pcx-hydra-i5-v2','brand'=>'PCX','name'=>'PCX GFH HYDRA i5 V2 - TEAMGROUP GeForce RTX™ 4060 Dual OC Gaming Desktop - Powered By ASUS','price'=>'From ₱59,995.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/HYDRAi5V21.jpg?v=1776326661&width=600'],
                    ['id'=>'pcx-morph-delta-r5','brand'=>'PCX','name'=>'PCX GFH MORPH (DELTA) AMD Ryzen™ 5 Desktop - Powered By ASUS','price'=>'₱36,500.00','old'=>'','disc'=>'',
                    'img'=>'https://pcx.com.ph/cdn/shop/files/MORPHDELTA_8e3a0b34-fdb6-4fcf-bf46-92a4728523f9.jpg?v=1776331919&width=600'],
                ];
                foreach ($pcxd_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── PC COMPONENTS ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-pccomponents">
                <h2>PC<br>Components</h2>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>PC Components</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $comp_products = [
                    ['id'=>'adata-ddr4-16gb','brand'=>'ADATA','name'=>'ADATA 16GB (2x 8GB) DDR4 3200MHz (AX4U32008G16A-DTBKD35) XPG GAMMIX D35 Desktop Memory','price'=>'₱8,000.00','old'=>'','disc'=>'',
                    'img'=>'https://www.dateks.lv/images/pic/2400/2400/029/1464.jpg'],
                    ['id'=>'adata-legend710-1tb-m2','brand'=>'ADATA','name'=>'ADATA 1TB LEGEND 710 M.2 PCIe NVMe (ALEG-710-1TCS) Solid State Drive','price'=>'₱9,500.00','old'=>'','disc'=>'',
                    'img'=>'https://ecommerce.datablitz.com.ph/cdn/shop/files/download_18_593ff19b-d0f9-4a9a-ba0f-a26656bd0b4e_800x.jpg?v=1713941603'],
                    ['id'=>'adata-su650-1tb','brand'=>'ADATA','name'=>'ADATA 1TB SU650 2.5" Solid State Drive','price'=>'₱9,000.00','old'=>'','disc'=>'',
                    'img'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSN6GvyEi73hPaXjqesWWBCNlprWTNptaHqbw&s'],
                    ['id'=>'adata-legend710-256gb','brand'=>'ADATA','name'=>'ADATA 256GB LEGEND 710 M.2 PCIe NVMe Solid State Drive','price'=>'₱3,750.00','old'=>'','disc'=>'',
                    'img'=>'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRw_qvAPiymk3EbjdcolBEyo87xF89qpoIKhA&s'],
                ];
                foreach ($comp_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── VIDEO GAME CONSOLES ── -->
    <div class="product-section">
        <div class="section-banner">
            <div class="banner-consoles">
                <div style="font-size:50px;margin-bottom:12px;">🎮</div>
                <h2>Video Game Consoles &amp; Accessories</h2>
            </div>
        </div>
        <div class="section-right">
            <div class="section-header">
                <h2>Video Game Console &amp; Accessories</h2>
                <a href="#" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php
                $console_products = [
                    ['id'=>'nintendo-switch2','brand'=>'Nintendo','name'=>'NINTENDO Switch 2 Console','price'=>'₱31,990.00','old'=>'','disc'=>'',
                    'img'=>'https://media1.tenor.com/m/kJJEBHrHgeAAAAAd/nintendo-switch-2-video-game-console.gif'],
                    ['id'=>'nintendo-switch2-mariokart-bundle','brand'=>'Nintendo','name'=>'NINTENDO Switch™ 2 + Mario Kart™ World Bundle','price'=>'₱35,500.00','old'=>'','disc'=>'',
                    'img'=>'https://ecommerce.datablitz.com.ph/cdn/shop/files/xzdgncghmtg_500x.jpg?v=1750869133'],
                    ['id'=>'nintendo-switch-oled-mk8','brand'=>'Nintendo','name'=>'NINTENDO Switch™ - OLED Model: Mario Kart™ 8 Deluxe Bundle','price'=>'₱18,000.00','old'=>'','disc'=>'',
                    'img'=>'https://www.nintendo.com/ph/news/assets/article/1cviLHuw6rzbDTbBLIHIfi/1.jpg'],
                    ['id'=>'nintendo-switch-v2-pikmin4','brand'=>'Nintendo','name'=>'NINTENDO Switch™ V2 Gray+Pikmin 4','price'=>'₱13,099.00','old'=>'','disc'=>'',
                    'img'=>'https://assets.nintendo.com/image/upload/f_auto/q_auto/dpr_1.5/c_scale/ccb3e8ca3c296e21a8c933e8369031511589d0ef6b079cf5bb3667b09893482c/Global%20Nav/Explore/Nintendo-Switch-2'],
                ];
                foreach ($console_products as $p): ?>
                <div class="product-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="product-img-wrap">
                        <img src="<?= $p['img'] ?>" alt="<?= $p['name'] ?>" class="product-image">
                        <div class="product-actions">
                            <div class="action-btn wish-btn" title="Wishlist" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-brand="<?= htmlspecialchars($p['brand'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>" data-image="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" data-url="product.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES) ?>">♡</div>
                            <div class="action-btn">👁</div>
                            <div class="action-btn">⇄</div>
                        </div>
                        <span class="pcx-badge">PCEXPRESS</span>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= $p['brand'] ?></div>
                        <div class="product-name"><?= $p['name'] ?></div>
                        <div class="price-row">
                            <span class="product-price"><?= $p['price'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div><!-- /main-content -->

<!-- ══════════════════════════ CART DRAWER ══════════════════════════ -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>

<div class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
        <h2>Shopping Cart</h2>
        <button class="cart-close-btn" onclick="closeCart()" title="Close">&times;</button>
    </div>
    <div class="cart-body" id="cartBody">
        <div class="cart-empty" id="cartEmpty">
            <div class="cart-empty-icon"><i class="fas fa-shopping-cart"></i></div>
            <h3>Your cart is empty.</h3>
            <p>Looks like you haven't added anything yet!</p>
            <button class="btn-return" onclick="closeCart()">RETURN TO SHOP</button>
        </div>
        <div id="cartItemsList" style="display:none;">
            <div class="cart-items-list" id="cartItemsInner"></div>

            <!-- Action Icon Bar -->
            <div class="cart-action-icons">
                <div class="cart-action-icon" onclick="openNotePanel()">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="cart-action-label">Note</span>
                </div>
                <div class="cart-action-icon" onclick="openShippingPanel()">
                    <i class="fas fa-truck"></i>
                    <span class="cart-action-label">Shipping</span>
                </div>
                <div class="cart-action-icon" onclick="openCouponPanel()">
                    <i class="fas fa-tag"></i>
                    <span class="cart-action-label">Discount</span>
                </div>
            </div>

            <!-- Sub-panel container (injected by cart.js) -->
            <div id="cartSubPanelContainer" style="padding:0 16px;"></div>

            <!-- You may also like -->
            <div class="cart-also-like" id="cartAlsoLike" style="display:none;">
                <h4>You may also like</h4>
                <div class="also-like-list" id="alsoLikeList"></div>
            </div>
        </div><!-- /#cartItemsList -->
    </div><!-- /#cartBody -->

    <div class="cart-footer" id="cartFooter" style="display:none;">
        <div class="cart-subtotal-row">
            <span class="cart-subtotal-label">Subtotal:</span>
            <span class="cart-subtotal-amount" id="cartSubtotalAmt">₱0.00</span>
        </div>
        <div class="cart-subtotal-currency" id="cartSubtotalCurrency">PHP</div>
        <div class="cart-terms-row">
            <input type="checkbox" id="agreeTerms" onchange="clearTermsError()">
            <label for="agreeTerms">I agree with the <a href="#" onclick="return false;">terms and conditions.</a></label>
        </div>
        <div class="cart-terms-error" id="termsError">
            <i class="fas fa-exclamation-triangle"></i>
            You must agree with the terms and conditions of sales to check out.
        </div>
        <a class="view-cart-link" onclick="openCart()">VIEW CART</a>
        <button class="btn-checkout" onclick="proceedToCheckout()">CHECKOUT</button>
    </div>
</div>

<!-- Toast -->
<div class="cart-toast" id="cartToast">
    <span class="toast-check">✔</span>
    <span id="cartToastMsg"></span>
</div>

<!-- ══════════════════════════ FOOTER ══════════════════════════ -->
<footer>
    <div class="footer-inner">
        <div>
            <div class="footer-logo"><span class="pc">PC</span><span class="ex">EXPRESS</span></div>
            <p>Philippines' No. 1 PC Company. We offer the widest selection of computers, laptops, and peripherals at the most competitive prices.</p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Store Locator</a></li>
                <li><a href="#">Careers</a></li>
            </ul>
        </div>
        <div>
            <h4>Customer Service</h4>
            <ul>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Return Policy</a></li>
                <li><a href="#">Warranty</a></li>
                <li><a href="#">Track Order</a></li>
            </ul>
        </div>
        <div>
            <h4>Follow Us</h4>
            <ul>
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Instagram</a></li>
                <li><a href="#">YouTube</a></li>
                <li><a href="#">Twitter / X</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> PC Express Philippines. All Rights Reserved.
    </div>
</footer>

<!-- ══════════════════════════ LIVE CHAT ══════════════════════════ -->
<div class="live-chat">
    <span>💬</span> Live chat
</div>

<!-- For You Tab -->
<div class="for-you-tab">For You</div>

<!-- ══════════════════════════ SCRIPTS ══════════════════════════ -->
<script>
    // ── SLIDER ──
    let currentSlide = 0;
    const totalSlides = 6;
    const wrapper = document.getElementById('slidesWrapper');
    const dotsContainer = document.getElementById('sliderDots');
    let autoSlideTimer;

    // Build dots
    for (let i = 0; i < totalSlides; i++) {
        const d = document.createElement('div');
        d.className = 'dot' + (i === 0 ? ' active' : '');
        d.onclick = () => goToSlide(i);
        dotsContainer.appendChild(d);
    }

    function goToSlide(n) {
        currentSlide = (n + totalSlides) % totalSlides;
        wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        document.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', i === currentSlide));
        resetTimer();
    }

    function changeSlide(dir) { goToSlide(currentSlide + dir); }

    function resetTimer() {
        clearInterval(autoSlideTimer);
        autoSlideTimer = setInterval(() => changeSlide(1), 4500);
    }

    resetTimer();

    // ── DROPDOWN CLOSE ON OUTSIDE CLICK ──
    document.addEventListener('click', function(e) {
        const btn = document.querySelector('.all-products-btn');
        if (!btn.contains(e.target)) btn.classList.remove('open');
    });

    // ── ADD TO CART BUTTONS ──
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const product = {
                id:       this.dataset.id,
                name:     this.dataset.name,
                price:    this.dataset.price,
                oldPrice: this.dataset.oldprice || '',
                priceRaw: parseFloat(this.dataset.priceraw) || 0,
                image:    this.dataset.image,
                brand:    this.dataset.brand || '',
                sku:      this.dataset.sku || ''
            };
            addToCartObj(product);
            this.textContent = '✓ Added!';
            this.style.background = '#28a745';
            setTimeout(() => {
                this.textContent = 'ADD TO CART';
                this.style.background = '#003da5';
            }, 1500);
        });
    });

    // ── WISHLIST BUTTONS ──────────────────────────────────────
    document.querySelectorAll('.wish-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const pid   = this.dataset.id    || '';
            const name  = this.dataset.name  || '';
            const brand = this.dataset.brand || '';
            const price = this.dataset.price || '';
            const image = this.dataset.image || '';
            const url   = this.dataset.url   || '';
            if (!pid) return;
            const fd = new FormData();
            fd.append('action',        'add');
            fd.append('product_id',    pid);
            fd.append('product_name',  name);
            fd.append('product_brand', brand);
            fd.append('product_price', price);
            fd.append('product_image', image);
            fd.append('product_url',   url);
            this.style.opacity = '0.5';
            const self = this;
            fetch('wishlist_handler.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                self.style.opacity = '1';
                if (data.success) {
                    self.textContent = data.wishlisted ? '♥' : '♡';
                    self.style.color = data.wishlisted ? '#cc0000' : '';
                    updateWishBadge(data.count);
                    showWishToast(data.message || (data.wishlisted ? 'Added to wishlist' : 'Removed from wishlist'));
                } else if (data.message && data.message.toLowerCase().includes('log in')) {
                    if (typeof openPanel === 'function') openPanel('login');
                    else showWishToast('Please log in to save items.');
                } else {
                    showWishToast(data.message || 'Something went wrong.');
                }
            })
            .catch(() => { self.style.opacity = '1'; showWishToast('Network error. Try again.'); });
        });
    });

    fetch('wishlist_handler.php?action=count')
    .then(r => r.json())
    .then(data => {
        if (data.success) updateWishBadge(data.count);
    }).catch(() => {});

    function updateWishBadge(count) {
        const badge = document.getElementById('wishBadge');
        if (!badge) return;
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    function showWishToast(msg) {
        let t = document.getElementById('wishToast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'wishToast';
            t.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);background:#222;color:#fff;padding:12px 24px;border-radius:50px;font-size:14px;font-weight:600;z-index:99999;opacity:0;transition:all .35s;white-space:nowrap;pointer-events:none;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        requestAnimationFrame(() => { t.style.opacity='1'; t.style.transform='translateX(-50%) translateY(0)'; });
        clearTimeout(window._wishTimer);
        window._wishTimer = setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(-50%) translateY(80px)'; }, 2500);
    }

    // ── SEARCH ──
    document.querySelector('.search-bar button').addEventListener('click', function() {
        const q = document.querySelector('.search-bar input').value.trim();
        if (q) alert('Searching for: ' + q);
    });
    document.querySelector('.search-bar input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') document.querySelector('.search-bar button').click();
    });
</script>

<!-- Shared Cart Logic -->
<script type="module" src="cart.js"></script>

<!-- Account Panel -->
<?php include 'account-panel.php'; ?>
<script type="module" src="auth.js"></script>
<script type="module" src="products.js"></script>
<script type="module" src="order.js"></script>
<script type="module" src="checkout_firestore_bridge.js"></script>

<script type="module">
import { db } from "./firebase-config.js";
import { collection, getDocs, query, where, orderBy, limit }
  from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

(async () => {
  try {
    // Load approved products from Firestore; fall back to all products if none are approved yet
    const q = query(
      collection(db, "products"),
      where("inventoryStatus", "==", "approved"),
      orderBy("createdAt", "desc"),
      limit(8)
    );
    let snap = await getDocs(q);
    if (snap.empty) {
      console.log('No approved products found. Showing all products instead.');
      // Fallback: get all products if none are approved yet
      const q2 = query(
        collection(db, "products"),
        orderBy("createdAt", "desc"),
        limit(8)
      );
      snap = await getDocs(q2);
    }

    const grid = document.getElementById("newInStoreGrid");
    if (!grid || snap.empty) return;

    grid.innerHTML = snap.docs.map(d => {
      const p    = d.data();
      const pid  = d.id;
      const img  = p.image || 'https://via.placeholder.com/300x300?text=No+Image';
      const name = p.name || '';
      const brand= p.category || '';
      const price= '₱' + Number(p.price||0).toLocaleString('en-PH',{minimumFractionDigits:2});
      return `
      <div class="product-card" onclick="window.location='product.php?id=${pid}'">
        <div class="product-img-wrap">
          <img src="${img}" alt="${name}" class="product-image"
               onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
          <div class="product-actions">
            <div class="action-btn wish-btn" title="Add to Wishlist"
                 data-id="${pid}" data-name="${name}" data-brand="${brand}"
                 data-price="${price}" data-image="${img}"
                 data-url="product.php?id=${pid}">♡</div>
            <div class="action-btn" title="Quick View">👁</div>
          </div>
          <span class="pcx-badge">PCEXPRESS</span>
        </div>
        <div class="product-info">
          <div class="product-brand">${brand}</div>
          <div class="product-name">${name}</div>
          <div class="price-row"><span class="product-price">${price}</span></div>
          <button class="add-to-cart-btn"
            data-id="${pid}" data-name="${name}" data-price="${price}"
            data-priceraw="${p.price||0}" data-image="${img}" data-brand="${brand}"
            onclick="event.stopPropagation()">ADD TO CART</button>
        </div>
      </div>`;
    }).join('');

    document.getElementById("new-in-store").style.display = "";

    // Re-attach listeners...
    grid.querySelectorAll('.add-to-cart-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (typeof addToCartObj === 'function') {
          addToCartObj({
            id: this.dataset.id, name: this.dataset.name,
            price: this.dataset.price, priceRaw: parseFloat(this.dataset.priceraw),
            image: this.dataset.image, brand: this.dataset.brand
          });
        }
        this.textContent = '✓ Added!';
        this.style.background = '#28a745';
        setTimeout(() => { this.textContent = 'ADD TO CART'; this.style.background = '#003da5'; }, 1500);
      });
    });
  } catch(e) { 
    console.warn('Firestore products error:', e); 
  }
})();
</script>
</body>
</html>