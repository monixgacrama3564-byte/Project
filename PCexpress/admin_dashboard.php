<?php
// admin_dashboard.php  — PC Express Multi-Branch Admin v3.0
// Auth is handled by Firebase JS (auth.js) — no PHP session check needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PC Express — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --blue:#003da5;--blue-light:#1a56c4;--blue-faint:#eff4ff;--blue-mid:#dce8ff;
  --red:#cc0000;--red-light:#e81717;--red-faint:#fff0f0;
  --gray-50:#f8f9fc;--gray-100:#f0f2f7;--gray-200:#e4e7ef;--gray-300:#cdd1de;
  --gray-400:#9ba3b8;--gray-500:#6b7490;--gray-700:#3a4060;--gray-900:#111827;
  --green:#16a34a;--green-faint:#f0fdf4;--yellow:#ca8a04;--yellow-faint:#fefce8;
  --orange:#ea580c;--orange-faint:#fff7ed;--purple:#7c3aed;--purple-faint:#f5f3ff;
  --cyan:#0891b2;--cyan-faint:#ecfeff;
  --sidebar-w:260px;--topbar-h:64px;
  --radius-sm:8px;--radius:12px;--radius-lg:16px;
  --shadow-sm:0 1px 3px rgba(0,0,0,.08);--shadow:0 4px 16px rgba(0,0,0,.08);--shadow-lg:0 12px 40px rgba(0,0,0,.12);
  --font:'Plus Jakarta Sans',sans-serif;--mono:'JetBrains Mono',monospace;
}
html,body{height:100%;overflow:hidden;}
body{font-family:var(--font);background:var(--gray-50);color:var(--gray-900);font-size:14px;line-height:1.5;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}
button{font-family:var(--font);cursor:pointer;border:none;background:none;}
input,select,textarea{font-family:var(--font);}
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-thumb{background:var(--gray-200);border-radius:99px;}

/* ── LAYOUT ── */
#shell{display:flex;height:100vh;}
.sidebar{width:var(--sidebar-w);flex-shrink:0;background:#fff;border-right:1px solid var(--gray-200);display:flex;flex-direction:column;overflow:hidden;position:relative;z-index:10;}
.sb-brand{padding:20px 20px 16px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;gap:10px;}
.sb-brand-logo{width:36px;height:36px;background:var(--blue);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sb-brand-logo svg{width:20px;height:20px;}
.sb-brand-name{font-size:15px;font-weight:800;letter-spacing:-.3px;color:var(--gray-900);}
.sb-brand-name span{color:var(--red);}
.sb-brand-tag{font-size:10px;color:var(--gray-400);font-weight:500;text-transform:uppercase;letter-spacing:.06em;}
.sb-nav{flex:1;overflow-y:auto;padding:12px 10px;display:flex;flex-direction:column;gap:1px;}
.sb-group-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-400);padding:14px 10px 4px;}
.sb-group-label:first-child{padding-top:4px;}
.sb-link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:var(--radius-sm);font-size:13.5px;font-weight:500;color:var(--gray-500);cursor:pointer;transition:background .15s,color .15s;}
.sb-link:hover{background:var(--gray-50);color:var(--gray-900);}
.sb-link.active{background:var(--blue-faint);color:var(--blue);font-weight:600;}
.sb-link .ico{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:15px;background:transparent;flex-shrink:0;transition:background .15s;}
.sb-link.active .ico{background:var(--blue-mid);}
.sb-link .badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;min-width:18px;text-align:center;}
.sb-footer{padding:12px 10px;border-top:1px solid var(--gray-100);}
.sb-user{display:flex;align-items:center;gap:10px;padding:10px;border-radius:var(--radius-sm);background:var(--gray-50);margin-bottom:6px;}
.sb-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--red));color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sb-user-name{font-size:13px;font-weight:600;color:var(--gray-900);line-height:1.3;}
.sb-user-role{font-size:11px;color:var(--gray-400);}
.sb-logout-btn{width:100%;display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;color:var(--red);transition:background .15s;}
.sb-logout-btn:hover{background:var(--red-faint);}

/* ── MAIN ── */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;}
.topbar{height:var(--topbar-h);background:#fff;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:12px;padding:0 28px;flex-shrink:0;}
.topbar-title{font-size:17px;font-weight:700;color:var(--gray-900);flex:1;display:flex;align-items:center;gap:8px;}
.topbar-title .breadcrumb{font-size:12px;color:var(--gray-400);font-weight:400;}
.topbar-title .breadcrumb::before{content:'/';margin-right:8px;}
.search-box{display:flex;align-items:center;gap:8px;background:var(--gray-50);border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);padding:8px 14px;width:260px;transition:border-color .2s;}
.search-box:focus-within{border-color:var(--blue);}
.search-box input{background:none;border:none;outline:none;font-size:13px;color:var(--gray-900);flex:1;}
.search-box input::placeholder{color:var(--gray-400);}
.topbar-btn{width:36px;height:36px;border-radius:var(--radius-sm);border:1.5px solid var(--gray-200);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--gray-500);transition:background .15s;position:relative;}
.topbar-btn:hover{background:var(--gray-50);}
.notif-dot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--red);border-radius:50%;border:2px solid #fff;}
.content{flex:1;overflow-y:auto;padding:24px 28px;}
.view{display:none;animation:fadeUp .22s ease;}
.view.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

/* ── COMPONENTS ── */
.page-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:16px;}
.page-hd-left h1{font-size:22px;font-weight:800;letter-spacing:-.4px;color:var(--gray-900);margin-bottom:2px;}
.page-hd-left p{font-size:13px;color:var(--gray-400);}
.page-hd-right{display:flex;gap:10px;align-items:center;flex-shrink:0;}

.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;transition:all .15s;border:1.5px solid transparent;cursor:pointer;}
.btn-primary{background:var(--blue);color:#fff;border-color:var(--blue);}
.btn-primary:hover{background:var(--blue-light);}
.btn-outline{background:#fff;color:var(--gray-700);border-color:var(--gray-200);}
.btn-outline:hover{background:var(--gray-50);border-color:var(--gray-300);}
.btn-danger-soft{background:var(--red-faint);color:var(--red);border-color:transparent;}
.btn-danger-soft:hover{background:#ffe0e0;}
.btn-success-soft{background:var(--green-faint);color:var(--green);border-color:transparent;}
.btn-warning-soft{background:var(--yellow-faint);color:var(--yellow);border-color:transparent;}
.btn-sm{padding:7px 14px;font-size:12px;}
.btn-icon{padding:8px;border-radius:var(--radius-sm);}

.card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--gray-100);}
.card-header-title{font-size:14px;font-weight:700;color:var(--gray-900);}
.card-header-sub{font-size:12px;color:var(--gray-400);margin-top:1px;}
.card-body{padding:20px;}

.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.kpi-grid-5{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;}
.kpi-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
.kpi-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:20px;display:flex;flex-direction:column;gap:12px;transition:box-shadow .2s,transform .2s;position:relative;overflow:hidden;}
.kpi-card:hover{box-shadow:var(--shadow);transform:translateY(-1px);}
.kpi-card::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--radius) var(--radius) 0 0;}
.kpi-card.k-blue::after{background:linear-gradient(90deg,var(--blue),#5b9bff);}
.kpi-card.k-red::after{background:linear-gradient(90deg,var(--red),#ff6b6b);}
.kpi-card.k-green::after{background:linear-gradient(90deg,var(--green),#4ade80);}
.kpi-card.k-yellow::after{background:linear-gradient(90deg,var(--yellow),#facc15);}
.kpi-card.k-purple::after{background:linear-gradient(90deg,var(--purple),#a78bfa);}
.kpi-card.k-cyan::after{background:linear-gradient(90deg,var(--cyan),#22d3ee);}
.kpi-card.k-orange::after{background:linear-gradient(90deg,var(--orange),#fb923c);}
.kpi-top{display:flex;align-items:center;justify-content:space-between;}
.kpi-icon-wrap{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.k-blue .kpi-icon-wrap{background:var(--blue-faint);}
.k-red .kpi-icon-wrap{background:var(--red-faint);}
.k-green .kpi-icon-wrap{background:var(--green-faint);}
.k-yellow .kpi-icon-wrap{background:var(--yellow-faint);}
.k-purple .kpi-icon-wrap{background:var(--purple-faint);}
.k-cyan .kpi-icon-wrap{background:var(--cyan-faint);}
.k-orange .kpi-icon-wrap{background:var(--orange-faint);}
.kpi-badge{font-size:11px;font-weight:700;padding:3px 8px;border-radius:99px;}
.kpi-badge.up{background:var(--green-faint);color:var(--green);}
.kpi-badge.dn{background:var(--red-faint);color:var(--red);}
.kpi-value{font-size:28px;font-weight:800;letter-spacing:-.8px;color:var(--gray-900);line-height:1;}
.kpi-label{font-size:12px;color:var(--gray-400);font-weight:500;}

/* Tables */
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-400);padding:12px 16px;border-bottom:1px solid var(--gray-100);background:var(--gray-50);white-space:nowrap;}
td{padding:13px 16px;border-bottom:1px solid var(--gray-100);font-size:13px;color:var(--gray-700);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--gray-50);}
.tbl-id{font-family:var(--mono);font-size:12px;color:var(--blue);font-weight:600;}
.tbl-name{font-weight:600;color:var(--gray-900);}
.tbl-sub{font-size:11px;color:var(--gray-400);margin-top:2px;}
.tbl-amount{font-weight:700;color:var(--gray-900);}

/* Pills */
.pill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;white-space:nowrap;}
.pill-green{background:var(--green-faint);color:var(--green);}
.pill-red{background:var(--red-faint);color:var(--red);}
.pill-yellow{background:var(--yellow-faint);color:var(--yellow);}
.pill-blue{background:var(--blue-faint);color:var(--blue);}
.pill-orange{background:var(--orange-faint);color:var(--orange);}
.pill-purple{background:var(--purple-faint);color:var(--purple);}
.pill-gray{background:var(--gray-100);color:var(--gray-500);}
.pill-cyan{background:var(--cyan-faint);color:var(--cyan);}

/* Filters */
.filters{display:flex;align-items:center;gap:10px;padding:14px 20px;border-bottom:1px solid var(--gray-100);flex-wrap:wrap;}
.filter-input,.filter-select{padding:8px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:#fff;font-size:13px;color:var(--gray-900);outline:none;transition:border-color .2s;font-family:var(--font);}
.filter-input:focus,.filter-select:focus{border-color:var(--blue);}
.filter-input{width:220px;}

/* Act buttons */
.act-btns{display:flex;gap:6px;}
.act-btn{width:28px;height:28px;border-radius:6px;border:1.5px solid var(--gray-200);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--gray-400);cursor:pointer;transition:all .15s;}
.act-btn:hover{border-color:var(--blue);color:var(--blue);background:var(--blue-faint);}
.act-btn.del:hover{border-color:var(--red);color:var(--red);background:var(--red-faint);}

/* Grids */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
.three-col{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;}

/* Charts */
.bar-chart-wrap{display:flex;align-items:flex-end;gap:10px;height:160px;padding-top:10px;}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
.bar-fill{width:100%;border-radius:6px 6px 0 0;transition:height .6s cubic-bezier(.4,0,.2,1);cursor:pointer;min-height:4px;}
.bar-fill:hover{filter:brightness(1.1);}
.bar-lbl{font-size:10px;color:var(--gray-400);font-weight:500;}
.bar-val{font-size:10px;color:var(--gray-700);font-weight:700;}

/* Donut */
.donut-container{display:flex;align-items:center;gap:24px;}
.donut-svg{width:130px;height:130px;flex-shrink:0;}
.donut-legend{flex:1;display:flex;flex-direction:column;gap:10px;}
.donut-row{display:flex;align-items:center;justify-content:space-between;font-size:12px;}
.donut-dot-lbl{display:flex;align-items:center;gap:8px;color:var(--gray-500);}
.donut-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.donut-val{font-weight:700;color:var(--gray-900);font-size:12px;}

/* Progress */
.prog-list{display:flex;flex-direction:column;gap:14px;}
.prog-top{display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;}
.prog-lbl{color:var(--gray-600);font-weight:500;}
.prog-pct{color:var(--gray-900);font-weight:700;}
.prog-track{height:6px;background:var(--gray-100);border-radius:99px;overflow:hidden;}
.prog-fill{height:100%;border-radius:99px;transition:width 1.2s cubic-bezier(.4,0,.2,1);}

/* Branch cards */
.branch-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
.branch-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:20px;transition:box-shadow .2s,transform .2s;position:relative;}
.branch-card:hover{box-shadow:var(--shadow);transform:translateY(-1px);}
.branch-card.main-branch{border-color:var(--blue);background:linear-gradient(135deg,var(--blue-faint),#fff);}
.branch-card-head{display:flex;align-items:center;gap:12px;margin-bottom:14px;}
.branch-av{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--blue),var(--blue-light));color:#fff;font-weight:700;font-size:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.branch-av.sub{background:linear-gradient(135deg,var(--purple),#a78bfa);}
.branch-name{font-weight:700;font-size:14px;color:var(--gray-900);}
.branch-sub{font-size:11px;color:var(--gray-400);}
.branch-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;}
.branch-stat{background:var(--gray-50);border-radius:6px;padding:8px 10px;}
.branch-stat-val{font-size:15px;font-weight:800;color:var(--gray-900);}
.branch-stat-lbl{font-size:10px;color:var(--gray-400);text-transform:uppercase;letter-spacing:.04em;}
.branch-badge{position:absolute;top:12px;right:12px;}

/* Employee / task cards */
.emp-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:16px;display:flex;align-items:center;gap:14px;transition:box-shadow .2s;}
.emp-card:hover{box-shadow:var(--shadow);}
.emp-av{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--purple),#a78bfa);color:#fff;font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.emp-name{font-weight:700;font-size:14px;color:var(--gray-900);}
.emp-sub{font-size:11px;color:var(--gray-400);margin-top:1px;}
.emp-meta{margin-left:auto;display:flex;align-items:center;gap:8px;}

/* Detail panel */
.order-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}
.detail-block{background:var(--gray-50);border-radius:var(--radius-sm);padding:16px;}
.detail-block-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-400);margin-bottom:12px;}
.detail-row{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid var(--gray-200);}
.detail-row:last-child{border-bottom:none;}
.detail-row .lbl{color:var(--gray-500);}
.detail-row .val{font-weight:600;color:var(--gray-900);text-align:right;}

/* Flow */
.flow-row{display:flex;align-items:center;gap:0;}
.flow-step{flex:1;display:flex;flex-direction:column;align-items:center;gap:10px;}
.flow-icon{width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;border:2px solid var(--gray-200);}
.flow-lbl{font-size:12px;font-weight:600;color:var(--gray-700);text-align:center;}
.flow-cnt{font-size:18px;font-weight:800;color:var(--gray-900);}
.flow-arrow{font-size:18px;color:var(--gray-300);padding-bottom:30px;flex-shrink:0;}

/* Rider cards */
.rider-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;}
.rider-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:20px;transition:box-shadow .2s;}
.rider-card:hover{box-shadow:var(--shadow);}
.rider-head{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
.rider-av{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--red));color:#fff;font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rider-name{font-weight:700;font-size:14px;color:var(--gray-900);}
.rider-sub{font-size:12px;color:var(--gray-400);}
.rider-stats{display:flex;gap:16px;margin-bottom:14px;}
.rider-stat-val{font-size:20px;font-weight:800;color:var(--gray-900);}
.rider-stat-lbl{font-size:10px;color:var(--gray-400);text-transform:uppercase;letter-spacing:.05em;}

/* Modal */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);z-index:8000;display:none;align-items:center;justify-content:center;}
.modal-bg.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius-lg);width:520px;max-width:94vw;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-lg);animation:modalIn .2s ease;}
.modal-wide{width:680px;}
@keyframes modalIn{from{opacity:0;transform:scale(.96) translateY(8px)}to{opacity:1;transform:none}}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid var(--gray-100);}
.modal-head h3{font-size:16px;font-weight:700;color:var(--gray-900);}
.modal-close{width:28px;height:28px;border-radius:6px;border:1.5px solid var(--gray-200);background:#fff;color:var(--gray-400);font-size:15px;display:flex;align-items:center;justify-content:center;}
.modal-close:hover{background:var(--gray-50);color:var(--gray-700);}
.modal-body{padding:20px 24px;display:flex;flex-direction:column;gap:14px;}
.modal-foot{padding:14px 24px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--gray-100);}
.form-row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);}
.form-group input,.form-group select,.form-group textarea{padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:#fff;color:var(--gray-900);font-size:13.5px;outline:none;transition:border-color .2s;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--blue);}
.form-group textarea{min-height:80px;resize:vertical;}

/* Toast */
.toast{position:fixed;bottom:28px;right:28px;background:var(--gray-900);color:#fff;border-radius:var(--radius-sm);padding:13px 18px;font-size:13px;font-weight:500;box-shadow:var(--shadow-lg);z-index:9999;display:flex;align-items:center;gap:10px;transform:translateY(20px);opacity:0;transition:all .3s cubic-bezier(.4,0,.2,1);pointer-events:none;max-width:320px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast-icon{font-size:16px;flex-shrink:0;}
.toast.t-success .toast-icon::before{content:'✓';color:#4ade80;}
.toast.t-error   .toast-icon::before{content:'✕';color:#f87171;}
.toast.t-info    .toast-icon::before{content:'ℹ';color:#60a5fa;}

/* Alert banner */
.alert-bar{display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;margin-bottom:16px;}
.alert-bar.warn{background:var(--yellow-faint);color:var(--yellow);border:1px solid #fde68a;}
.alert-bar.info{background:var(--blue-faint);color:var(--blue);border:1px solid var(--blue-mid);}

/* Responsive */
@media(max-width:1200px){.kpi-grid-5{grid-template-columns:repeat(3,1fr);}.branch-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:1100px){.kpi-grid{grid-template-columns:repeat(2,1fr);}.two-col,.order-detail-grid{grid-template-columns:1fr;}}
@media(max-width:800px){:root{--sidebar-w:200px;}.content{padding:16px;}.rider-grid,.branch-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div id="shell">

<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-brand-logo">
      <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round">
        <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
      </svg>
    </div>
    <div>
      <div class="sb-brand-name">PC<span>EXPRESS</span></div>
      <div class="sb-brand-tag">
        <span class="js-admin-role">Admin</span>
      </div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="sb-group-label sb-role-hide" data-roles="admin,main_admin,sub_admin,manager">Main</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('overview',this)"><span class="ico">📊</span> Overview</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('transactions',this)"><span class="ico">💳</span> Transactions<span class="badge">0</span></div>

    <div class="sb-group-label sb-role-hide" data-roles="admin,main_admin">Catalog</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('products',this)"><span class="ico">🖥️</span> Products</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('inventory',this)"><span class="ico">📦</span> Inventory</div>

    <div class="sb-group-label sb-role-hide" data-roles="admin,main_admin,sub_admin,manager,staff">Branch Operations</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('branches',this)"><span class="ico">🏢</span> Branch Management</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('sub-admins',this)"><span class="ico">🔑</span> Sub Admin Mgmt</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('inv-transfer',this)"><span class="ico">🔄</span> Inventory Transfer</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager,staff" onclick="showView('branch-inventory',this)"><span class="ico">🗄️</span> Branch Inventory</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('employees',this)"><span class="ico">👤</span> Employee Management</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('branch-staff',this)"><span class="ico">👔</span> Branch Staff</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager,staff" onclick="showView('tasks',this)"><span class="ico">✅</span> Tasks</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('approvals',this)"><span class="ico">📋</span> Approvals<span class="badge" id="approvalBadge">0</span></div>

    <div class="sb-group-label sb-role-hide" data-roles="admin,main_admin,sub_admin,manager,staff">Operations</div>
    <div class="sb-link sb-role-hide" data-roles="staff" onclick="showView('pending-orders',this)"><span class="ico">⚡</span> Pending Orders<span class="badge" id="pendingOrderBadge">0</span></div>
    <div class="sb-link sb-role-hide" data-roles="staff" onclick="showView('orders',this)"><span class="ico">🛒</span> All Orders</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('orders',this)"><span class="ico">🛒</span> Orders <small style="font-size:10px;color:var(--gray-400);">(Monitor)</small></div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('users',this)"><span class="ico">👥</span> Users</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,rider" onclick="showView('delivery',this)"><span class="ico">🚚</span> Delivery</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('riders',this)"><span class="ico">🏍️</span> Riders</div>

    <div class="sb-group-label sb-role-hide" data-roles="admin,main_admin,sub_admin,manager">Insights</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin" onclick="showView('analytics',this)"><span class="ico">📈</span> Analytics</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('branch-analytics',this)"><span class="ico">🏪</span> Branch Analytics</div>
    <div class="sb-link sb-role-hide" data-roles="admin,main_admin,sub_admin,manager" onclick="showView('order-history',this)"><span class="ico">🧾</span> Order History</div>

    <div class="sb-group-label sb-role-hide" data-roles="staff,sub_admin,manager">My Work</div>
    <div class="sb-link sb-role-hide" data-roles="staff,sub_admin,manager" onclick="showView('emp-dashboard',this)"><span class="ico">🙋</span> My Dashboard</div>

    <div class="sb-group-label sb-role-hide" data-roles="rider">My Deliveries</div>
    <div class="sb-link sb-role-hide" data-roles="rider" onclick="showView('delivery',this)"><span class="ico">🚚</span> My Deliveries</div>
  </nav>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><span class="js-admin-initial">A</span></div>
      <div>
        <div class="sb-user-name"><span class="js-admin-name">Admin</span></div>
        <div class="sb-user-role"><span class="js-admin-role">admin</span></div>
      </div>
    </div>
    <button class="sb-logout-btn js-logout-btn">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
  </svg>
  Sign Out
</button>
    </form>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<div class="main">
  <div class="topbar">
    <div class="topbar-title" id="topbarTitle">Overview<span class="breadcrumb" id="topbarSub">Dashboard</span></div>
    <div class="search-box">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" placeholder="Search anything…" id="globalSearch" oninput="handleSearch(this.value)">
    </div>
    <button class="topbar-btn" title="Notifications">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span class="notif-dot"></span>
    </button>
  </div>

  <div class="content">

<!-- ═══ OVERVIEW ═══ -->
<div class="view active" id="view-overview">
  <div class="page-hd">
    <div class="page-hd-left"><h1>Good day, <span class="js-admin-name">Admin</span>! 👋</h1><p>Here's what's happening across all branches today.</p></div>
    <div class="page-hd-right">
      <button class="btn btn-outline btn-sm">📅 Today</button>
      <button class="btn btn-primary btn-sm" onclick="exportCSV('orders')">⬇ Export</button>
    </div>
  </div>
  <div class="kpi-grid" id="overviewKpis">
    <div class="kpi-card k-blue"><div class="kpi-top"><div class="kpi-icon-wrap">💰</div><span class="kpi-badge up">↑</span></div><div class="kpi-value" id="kpiRevenue"><span id="statRevenue">—</span></div><div class="kpi-label">Total Revenue</div></div>
    <div class="kpi-card k-green"><div class="kpi-top"><div class="kpi-icon-wrap">🛒</div><span class="kpi-badge up">↑</span></div><div class="kpi-value" id="kpiOrders"><span id="statOrders">—</span></div><div class="kpi-label">Total Orders</div></div>
    <div class="kpi-card k-yellow"><div class="kpi-top"><div class="kpi-icon-wrap">⏳</div></div><div class="kpi-value" id="kpiPending"><span id="statPending">—</span></div><div class="kpi-label">Pending Orders</div></div>
    <div class="kpi-card k-purple"><div class="kpi-top"><div class="kpi-icon-wrap">👥</div></div><div class="kpi-value" id="kpiUsers"><span id="statUsers">—</span></div><div class="kpi-label">Registered Users</div></div>
  </div>
  <div id="overviewKpis2" class="kpi-grid" style="grid-template-columns:repeat(5,1fr);margin-top:-8px;"></div>
  <div class="two-col" style="margin-bottom:20px;">
    <div class="card"><div class="card-header"><div><div class="card-header-title">Monthly Revenue</div><div class="card-header-sub">Last 6 months</div></div></div><div class="card-body"><div class="bar-chart-wrap" id="barChart"></div></div></div>
    <div class="card"><div class="card-header"><div class="card-header-title">Order Breakdown</div></div><div class="card-body">
      <div class="donut-container">
        <svg class="donut-svg" viewBox="0 0 130 130">
          <circle cx="65" cy="65" r="48" fill="none" stroke="var(--gray-100)" stroke-width="18"/>
          <circle cx="65" cy="65" r="48" fill="none" stroke="var(--green)" stroke-width="18" stroke-dasharray="188 114" stroke-dashoffset="0" stroke-linecap="round" transform="rotate(-90 65 65)"/>
          <circle cx="65" cy="65" r="48" fill="none" stroke="var(--yellow)" stroke-width="18" stroke-dasharray="76 226" stroke-dashoffset="-188" stroke-linecap="round" transform="rotate(-90 65 65)"/>
          <circle cx="65" cy="65" r="48" fill="none" stroke="var(--red)" stroke-width="18" stroke-dasharray="34 268" stroke-dashoffset="-264" stroke-linecap="round" transform="rotate(-90 65 65)"/>
          <circle cx="65" cy="65" r="48" fill="none" stroke="var(--cyan)" stroke-width="18" stroke-dasharray="28 274" stroke-dashoffset="-298" stroke-linecap="round" transform="rotate(-90 65 65)"/>
        </svg>
        <div class="donut-legend">
          <div class="donut-row"><div class="donut-dot-lbl"><div class="donut-dot" style="background:var(--green)"></div>Completed</div><div class="donut-val" id="dCompleted">—</div></div>
          <div class="donut-row"><div class="donut-dot-lbl"><div class="donut-dot" style="background:var(--yellow)"></div>Pending</div><div class="donut-val" id="dPending">—</div></div>
          <div class="donut-row"><div class="donut-dot-lbl"><div class="donut-dot" style="background:var(--red)"></div>Cancelled</div><div class="donut-val" id="dCancelled">—</div></div>
          <div class="donut-row"><div class="donut-dot-lbl"><div class="donut-dot" style="background:var(--cyan)"></div>Processing</div><div class="donut-val" id="dProcessing">—</div></div>
        </div>
      </div>
    </div></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Recent Orders</div></div>
    <div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Date</th><th>Status</th></tr></thead><tbody id="recentOrdersBody"><tr><td colspan="5" style="text-align:center;padding:32px;color:var(--gray-400);">Loading…</td></tr></tbody></table></div>
  </div>
</div>

<!-- ═══ TRANSACTIONS ═══ -->
<div class="view" id="view-transactions">
  <div class="page-hd"><div class="page-hd-left"><h1>Transactions</h1><p>All financial transactions</p></div><div class="page-hd-right"><button class="btn btn-outline btn-sm" onclick="exportCSV('transactions')">⬇ Export</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search transactions…" oninput="loadTransactions(this.value)">
      <select class="filter-select" onchange="loadTransactions('',this.value)"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="cancelled">Cancelled</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Payment</th><th>Total</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody id="transactionsBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ PRODUCTS ═══ -->
<div class="view" id="view-products">
  <div class="page-hd"><div class="page-hd-left"><h1>Products</h1><p>Catalog management</p></div><div class="page-hd-right"><button class="btn btn-outline btn-sm" onclick="exportCSV('products')">⬇ Export</button><button class="btn btn-primary btn-sm" onclick="openProductModal()">+ Add Product</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search products…" oninput="loadProducts(this.value)">
      <select class="filter-select" id="prodBranchFilter" onchange="loadProducts()"><option value="">All Branches</option></select>
      <select class="filter-select" onchange="loadProducts()"><option value="">All Categories</option><option>PC Components</option><option>Laptops</option><option>Peripherals</option><option>Storage</option><option>Networking</option></select>
      <select class="filter-select" id="prodApprovalFilter" onchange="loadProducts()"><option value="">All Listings</option><option value="pending">Pending Approval</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>SKU</th><th>Product Name</th><th>Branch</th><th>Category</th><th>Price</th><th>Stock</th><th>Listing</th><th>Status</th><th>Actions</th></tr></thead><tbody id="productsBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ INVENTORY ═══ -->
<div class="view" id="view-inventory">
  <div class="page-hd"><div class="page-hd-left"><h1>Inventory</h1><p>Stock levels and alerts</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openRestockModal()">+ Restock</button></div></div>
  <div class="kpi-grid-3" id="invKpis">
    <div class="kpi-card k-green"><div class="kpi-top"><div class="kpi-icon-wrap">✅</div></div><div class="kpi-value" id="kpiInStock">—</div><div class="kpi-label">In Stock</div></div>
    <div class="kpi-card k-yellow"><div class="kpi-top"><div class="kpi-icon-wrap">⚠️</div></div><div class="kpi-value" id="kpiLowStock">—</div><div class="kpi-label">Low Stock (≤5)</div></div>
    <div class="kpi-card k-red"><div class="kpi-top"><div class="kpi-icon-wrap">❌</div></div><div class="kpi-value" id="kpiOutStock">—</div><div class="kpi-label">Out of Stock</div></div>
  </div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search inventory…" oninput="loadInventory(this.value)">
      <select class="filter-select" onchange="loadInventory('',this.value)"><option value="">All Status</option><option>In Stock</option><option>Low Stock</option><option>Out of Stock</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>SKU</th><th>Product</th><th>Category</th><th>In Stock</th><th>Reorder Level</th><th>Last Updated</th><th>Status</th><th>Actions</th></tr></thead><tbody id="invBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ BRANCHES ═══ (main admin only) -->
<div class="view" id="view-branches">
  <div class="page-hd"><div class="page-hd-left"><h1>Branch Management</h1><p>Create and manage all store branches</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openBranchModal()">+ Add Branch</button></div></div>
  <div class="kpi-grid-3" style="margin-bottom:20px;" id="branchOverviewKpis">
    <div class="kpi-card k-blue"><div class="kpi-top"><div class="kpi-icon-wrap">🏢</div></div><div class="kpi-value" id="kpiBranches">—</div><div class="kpi-label">Total Branches</div></div>
    <div class="kpi-card k-purple"><div class="kpi-top"><div class="kpi-icon-wrap">👤</div></div><div class="kpi-value" id="kpiSubAdmins">—</div><div class="kpi-label">Sub Admins</div></div>
    <div class="kpi-card k-green"><div class="kpi-top"><div class="kpi-icon-wrap">👥</div></div><div class="kpi-value" id="kpiTotalEmployees">—</div><div class="kpi-label">Total Employees</div></div>
  </div>
  <div class="branch-grid" id="branchGrid"></div>
  <div class="card">
    <div class="card-header"><div class="card-header-title">All Branches</div></div>
    <div class="tbl-wrap"><table><thead><tr><th>Branch Name</th><th>City</th><th>Sub Admin</th><th>Staff</th><th>Orders</th><th>Revenue</th><th>Stock Alerts</th><th>Status</th><th>Actions</th></tr></thead><tbody id="branchesBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ SUB ADMIN MANAGEMENT ═══ -->
<div class="view" id="view-sub-admins">
  <div class="page-hd"><div class="page-hd-left"><h1>Sub Admin Management</h1><p>Assign and manage sub-admins per branch</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openAddUserModal('sub_admin')">+ Add Sub Admin</button></div></div>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Sub Admins</div></div>
    <div class="tbl-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Branch</th><th>Status</th><th>Actions</th></tr></thead><tbody id="subAdminsBody"></tbody></table></div>
  </div>
  <div style="margin-top:20px;" class="card">
    <div class="card-header"><div class="card-header-title">Assign Sub Admin to Branch</div></div>
    <div class="card-body">
      <div class="form-row2">
        <div class="form-group"><label>Select User</label><select id="assignSubUser" style="width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;outline:none;"></select></div>
        <div class="form-group"><label>Select Branch</label><select id="assignSubBranch" style="width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;outline:none;"></select></div>
      </div>
      <button class="btn btn-primary btn-sm" style="margin-top:10px;" onclick="assignSubAdmin()">Assign Sub Admin</button>
    </div>
  </div>
</div>

<!-- ═══ EMPLOYEES ═══ -->
<div class="view" id="view-employees">
  <div class="page-hd"><div class="page-hd-left"><h1>Employee Management</h1><p>Manage staff, managers, and employees across branches</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openAddUserModal('staff')">+ Add Staff</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search employees…" oninput="loadEmployees(this.value)">
      <select class="filter-select" id="empBranchFilter" onchange="loadEmployees()"><option value="">All Branches</option></select>
      <select class="filter-select" id="empRoleFilter" onchange="loadEmployees()"><option value="">All Roles</option><option value="manager">Manager</option><option value="staff">Staff</option><option value="sub_admin">Sub Admin</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Name</th><th>Role</th><th>Branch</th><th>Position</th><th>Open Tasks</th><th>Status</th><th>Actions</th></tr></thead><tbody id="employeesBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ BRANCH INVENTORY ═══ -->
<div class="view" id="view-branch-inventory">
  <div class="page-hd"><div class="page-hd-left"><h1>Branch Inventory</h1><p>Per-branch stock management</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openBranchInvModal()">+ Add Item</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search inventory…" oninput="loadBranchInventory(this.value)">
      <select class="filter-select" id="branchInvFilter" onchange="loadBranchInventory()"><option value="">All Branches</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Product</th><th>SKU</th><th>Branch</th><th>Qty</th><th>Reorder Level</th><th>Unit Cost</th><th>Status</th><th>Last Updated</th><th>Actions</th></tr></thead><tbody id="branchInvBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ INVENTORY TRANSFER ═══ -->
<div class="view" id="view-inv-transfer">
  <div class="page-hd"><div class="page-hd-left"><h1>Inventory Transfer</h1><p>Move stock between branches</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openModal('transfer');populateBranchSelects()">+ New Transfer</button></div></div>
  <div class="two-col" style="margin-bottom:20px;">
    <div class="card">
      <div class="card-header"><div class="card-header-title">Transfer Stock</div></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:14px;">
        <div class="form-group"><label>From Branch</label><select id="tfFrom" style="width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;outline:none;" onchange="loadBranchInvProducts()"></select></div>
        <div class="form-group"><label>To Branch</label><select id="tfTo" style="width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;outline:none;"></select></div>
        <div class="form-group"><label>Product</label><select id="tfProduct" style="width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;outline:none;"></select></div>
        <div class="form-row2">
          <div class="form-group"><label>Quantity</label><input type="number" id="tfQty" placeholder="0" min="1"></div>
          <div class="form-group"><label>Available</label><input type="text" id="tfAvail" readonly style="background:var(--gray-50);padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;color:var(--gray-500);"></div>
        </div>
        <div class="form-group"><label>Reason</label><textarea id="tfReason" placeholder="Transfer reason…" style="min-height:60px;resize:vertical;width:100%;padding:10px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:13px;outline:none;"></textarea></div>
        <button class="btn btn-primary" onclick="doTransfer()">🔄 Execute Transfer</button>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-header-title">Transfer History</div></div>
      <div class="tbl-wrap"><table><thead><tr><th>From</th><th>To</th><th>Product</th><th>Qty</th><th>By</th><th>Date</th></tr></thead><tbody id="transferLogsBody"></tbody></table></div>
    </div>
  </div>
</div>

<!-- ═══ TASKS ═══ -->
<div class="view" id="view-tasks">
  <div class="page-hd"><div class="page-hd-left"><h1>Employee Tasks</h1><p>Assign and track employee tasks across branches</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openModal('task')">+ Assign Task</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search tasks…" id="taskSearch">
      <select class="filter-select" id="taskBranchFilter" onchange="loadTasks()"><option value="">All Branches</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Title</th><th>Assigned To</th><th>Branch</th><th>Priority</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead><tbody id="tasksBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ ORDERS ═══ -->
<!-- ═══ PENDING ORDERS (Staff-only processing view) ═══ -->
<div class="view" id="view-pending-orders">
  <div class="page-hd">
    <div class="page-hd-left">
      <h1>⚡ Pending Orders</h1>
      <p>Accept and process incoming customer orders</p>
    </div>
    <div class="page-hd-right">
      <button class="btn btn-outline btn-sm" onclick="loadPendingOrders()">🔄 Refresh</button>
    </div>
  </div>
  
  <div style="background:var(--green-faint);border:1px solid #bbf7d0;border-radius:var(--radius);padding:12px 18px;margin-bottom:18px;font-size:13px;color:var(--green);font-weight:600;">
    ✅ Staff Access — You can accept, process, and update order statuses.
  </div>
  <div style="background:var(--yellow-faint);border:1px solid #fde68a;border-radius:var(--radius);padding:12px 18px;margin-bottom:18px;font-size:13px;color:var(--yellow);font-weight:600;">
    👁 Monitor Mode — Order processing is restricted to Staff members only.
  </div>
  
  <div class="card">
    <div class="tbl-wrap">
      <table>
        <thead><tr>
          <th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th>
          <th>Date</th><th>Status</th><th>Accepted By</th>
          <th>Actions</th>
        </tr></thead>
        <tbody id="pendingOrdersBody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ═══ ORDERS (All roles — admin sees monitoring view, staff sees full actions) ═══ -->
<div class="view" id="view-orders">
  <div class="page-hd">
    <div class="page-hd-left">
      <h1>Order Management</h1>
      <p>Track and manage all orders</p>
    </div>
    <div class="page-hd-right"><button class="btn btn-outline btn-sm" onclick="exportCSV('orders')">⬇ Export</button></div>
  </div>
  
  <div style="background:var(--blue-faint);border:1px solid var(--blue-mid);border-radius:var(--radius);padding:12px 18px;margin-bottom:18px;font-size:13px;color:var(--blue);font-weight:600;">
    📊 Supervisor View — You can monitor all orders. To process orders, contact branch staff.
  </div>
  
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search orders…" oninput="loadOrders(this.value)">
      <select class="filter-select" onchange="loadOrders('',this.value)"><option value="">All Status</option><option value="completed">Completed</option><option value="processing">Processing</option><option value="pending">Pending</option><option value="cancelled">Cancelled</option><option value="shipped">Shipped</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Date</th><th>Status</th><th>Accepted By</th><th>Actions</th></tr></thead><tbody id="ordersBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ DELIVERY ═══ -->
<div class="view" id="view-delivery">
  <div class="page-hd"><div class="page-hd-left"><h1>Delivery Management</h1><p>Track all shipments</p></div><div class="page-hd-right"><button class="btn btn-outline btn-sm" onclick="showView('riders',null)">👤 Riders</button><button class="btn btn-primary btn-sm" onclick="openModal('assign')">+ Assign Rider</button></div></div>
  <div class="card" style="margin-bottom:20px;"><div class="card-header"><div class="card-header-title">Delivery Pipeline</div></div><div class="card-body"><div class="flow-row">
    <div class="flow-step"><div class="flow-icon" style="background:var(--cyan-faint);border-color:var(--cyan)">🔄</div><div class="flow-cnt" style="color:var(--cyan)" id="flowProcessing">0</div><div class="flow-lbl">Processing</div></div>
    <div class="flow-arrow">→</div>
    <div class="flow-step"><div class="flow-icon" style="background:var(--yellow-faint);border-color:var(--yellow)">📦</div><div class="flow-cnt" style="color:var(--yellow)">—</div><div class="flow-lbl">Packed</div></div>
    <div class="flow-arrow">→</div>
    <div class="flow-step"><div class="flow-icon" style="background:var(--orange-faint);border-color:var(--orange)">🏍️</div><div class="flow-cnt" style="color:var(--orange)">—</div><div class="flow-lbl">Out for Delivery</div></div>
    <div class="flow-arrow">→</div>
    <div class="flow-step"><div class="flow-icon" style="background:var(--green-faint);border-color:var(--green)">✅</div><div class="flow-cnt" style="color:var(--green)" id="flowCompleted">0</div><div class="flow-lbl">Delivered</div></div>
  </div></div></div>
  <div class="card"><div class="filters"><input class="filter-input" type="text" placeholder="🔍  Search deliveries…" oninput="filterTable('deliveryBody',this.value)"><select class="filter-select" onchange="filterByStatus('deliveryBody',this.value)"><option value="">All Status</option><option>Processing</option><option>Packed</option><option>Out for Delivery</option><option>Delivered</option></select></div><div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Address</th><th>Mode</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody id="deliveryBody"></tbody></table></div></div>
</div>

<!-- ═══ RIDERS ═══ -->
<div class="view" id="view-riders">
  <div class="page-hd"><div class="page-hd-left"><h1>Delivery Personnel</h1><p>Manage your rider team</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openModal('rider')">+ Add Rider</button></div></div>
  <div class="rider-grid" id="riderGrid"></div>
  <div class="card"><div class="card-header"><div class="card-header-title">Active Assignments</div></div><div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Address</th><th>Rider</th><th>Assigned</th><th>Status</th><th>Actions</th></tr></thead><tbody id="assignBody"></tbody></table></div></div>
</div>

<!-- ═══ USERS ═══ -->
<div class="view" id="view-users">
  <div class="page-hd"><div class="page-hd-left"><h1>User Management</h1><p>All registered accounts</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openModal('user')">+ Add User</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search users…" oninput="loadUsers(this.value)">
      <select class="filter-select" onchange="loadUsers('',this.value)"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
      <select class="filter-select" onchange="loadUsers('','',this.value)"><option value="">All Roles</option><option value="customer">Customer</option><option value="employee">Employee</option><option value="staff">Staff</option><option value="manager">Manager</option><option value="sub_admin">Sub Admin</option><option value="main_admin">Main Admin</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Registered</th><th>Status</th><th>Actions</th></tr></thead><tbody id="usersBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ ANALYTICS ═══ -->
<div class="view" id="view-analytics">
  <div class="page-hd"><div class="page-hd-left"><h1>Analytics & Reports</h1><p>Performance overview</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm">⬇ Download Report</button></div></div>
  <div class="kpi-grid">
    <div class="kpi-card k-blue"><div class="kpi-top"><div class="kpi-icon-wrap">💰</div></div><div class="kpi-value">₱—</div><div class="kpi-label">This Month Revenue</div></div>
    <div class="kpi-card k-green"><div class="kpi-top"><div class="kpi-icon-wrap">📦</div></div><div class="kpi-value">—</div><div class="kpi-label">This Quarter</div></div>
    <div class="kpi-card k-orange"><div class="kpi-top"><div class="kpi-icon-wrap">📊</div></div><div class="kpi-value">—</div><div class="kpi-label">Avg Order Value</div></div>
    <div class="kpi-card k-cyan"><div class="kpi-top"><div class="kpi-icon-wrap">🔁</div></div><div class="kpi-value">—</div><div class="kpi-label">Cancellation Rate</div></div>
  </div>
  <div class="two-col" style="margin-bottom:20px;">
    <div class="card"><div class="card-header"><div class="card-header-title">Revenue (7 Days)</div></div><div class="card-body"><div class="bar-chart-wrap" id="analyticsBar"></div></div></div>
    <div class="card"><div class="card-header"><div class="card-header-title">Sales by Category</div></div><div class="card-body"><div class="prog-list" id="catProg"><div style="color:var(--gray-400);font-size:13px;text-align:center;padding:20px;">Loading…</div></div></div></div>
  </div>
  <div class="card"><div class="card-header"><div class="card-header-title">Top Products by Estimated Revenue</div></div><div class="tbl-wrap"><table><thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Price</th><th>Est. Revenue</th></tr></thead><tbody id="topProductsBody"></tbody></table></div></div>
</div>

<!-- ═══ BRANCH ANALYTICS ═══ (main admin only) -->
<div class="view" id="view-branch-analytics">
  <div class="page-hd"><div class="page-hd-left"><h1>Branch Analytics</h1><p>Compare performance across all branches</p></div><div class="page-hd-right"><select class="filter-select" id="baFilter" onchange="loadBranchAnalytics()"><option value="">All Branches</option></select></div></div>
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-header-title">Branch Revenue Share</div></div>
    <div class="card-body"><div class="prog-list" id="branchRevShare"><div style="color:var(--gray-400);font-size:13px;text-align:center;padding:20px;">Loading…</div></div></div>
  </div>
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-header-title">Branch Performance Table</div></div>
    <div class="tbl-wrap"><table><thead><tr><th>Branch</th><th>City</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Staff</th><th>Stock</th><th>Low Stock</th><th>Status</th></tr></thead><tbody id="branchPerfBody"></tbody></table></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Monthly Revenue by Branch (Last 6 Months)</div></div>
    <div class="card-body"><div class="bar-chart-wrap" id="branchMonthlyBar" style="height:180px;"></div></div>
  </div>
</div>

<!-- ═══ ORDER HISTORY ═══ -->
<div class="view" id="view-order-history">
  <div class="page-hd"><div class="page-hd-left"><h1>Order History</h1><p>Complete order ledger</p></div><div class="page-hd-right"><button class="btn btn-outline btn-sm" onclick="exportCSV('orders')">⬇ Export</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search history…" oninput="loadOrders(this.value)">
      <select class="filter-select" onchange="loadOrders('',this.value)"><option value="">All Status</option><option>Completed</option><option>Cancelled</option><option>Pending</option><option>Processing</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody id="historyBody"></tbody></table></div>
  </div>
  <div id="orderDetailPanel" style="display:none;margin-top:20px;">
    <div class="card"><div class="card-header"><div class="card-header-title" id="detailOrderId">Order Details</div><button class="btn btn-outline btn-sm" onclick="document.getElementById('orderDetailPanel').style.display='none'">✕ Close</button></div>
    <div class="card-body">
      <div class="order-detail-grid">
        <div class="detail-block"><div class="detail-block-title">Order Info</div><div class="detail-row"><span class="lbl">Order #</span><span class="val" id="dOrderNum">—</span></div><div class="detail-row"><span class="lbl">Date</span><span class="val" id="dOrderDate">—</span></div><div class="detail-row"><span class="lbl">Payment</span><span class="val" id="dPayment">—</span></div><div class="detail-row"><span class="lbl">Status</span><span class="val" id="dDelStatus">—</span></div></div>
        <div class="detail-block"><div class="detail-block-title">Customer</div><div class="detail-row"><span class="lbl">Name</span><span class="val" id="dCustName">—</span></div><div class="detail-row"><span class="lbl">Email</span><span class="val" id="dCustEmail">—</span></div><div class="detail-row"><span class="lbl">Address</span><span class="val" id="dCustAddr">—</span></div></div>
      </div>
      <div class="detail-block" style="margin-bottom:14px;"><div class="detail-block-title">Items</div><div id="dOrderItems"></div></div>
      <div class="detail-block" style="max-width:280px;margin-left:auto;">
        <div class="detail-row"><span class="lbl">Subtotal</span><span class="val" id="dSubtotal">—</span></div>
        <div class="detail-row"><span class="lbl">Shipping</span><span class="val" id="dShipping">—</span></div>
        <div class="detail-row" style="font-size:15px;font-weight:800;"><span class="lbl" style="color:var(--gray-900)">Total</span><span class="val" id="dTotal">—</span></div>
      </div>
    </div></div>
  </div>
</div>

<!-- ═══ PENDING APPROVALS ═══ (main admin only) -->
<div class="view" id="view-approvals">
  <div class="page-hd"><div class="page-hd-left"><h1>Pending Approvals</h1><p>Review inventory listings submitted by Sub Admins</p></div></div>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Products Awaiting Approval</div><span class="pill pill-yellow" id="pendingApprovalCount">Loading…</span></div>
    <div class="tbl-wrap"><table><thead><tr><th>Product Name</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Branch</th><th>Submitted By</th><th>Date</th><th>Actions</th></tr></thead><tbody id="approvalsBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ BRANCH STAFF ═══ -->
<div class="view" id="view-branch-staff">
  <div class="page-hd"><div class="page-hd-left"><h1>Branch Staff</h1><p>View and manage staff assignments per branch</p></div><div class="page-hd-right"><button class="btn btn-primary btn-sm" onclick="openAddUserModal('staff')">+ Add Staff</button></div></div>
  <div class="card">
    <div class="filters">
      <input class="filter-input" type="text" placeholder="🔍  Search staff…" oninput="loadBranchStaff(this.value)">
      <select class="filter-select" id="staffBranchFilter" onchange="loadBranchStaff()"><option value="">All Branches</option></select>
    </div>
    <div class="tbl-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Position</th><th>Department</th><th>Assigned</th><th>Actions</th></tr></thead><tbody id="branchStaffBody"></tbody></table></div>
  </div>
</div>

<!-- ═══ EMPLOYEE DASHBOARD ═══ -->
<div class="view" id="view-emp-dashboard">
  <div class="page-hd"><div class="page-hd-left"><h1>My Dashboard</h1><p>Your tasks, orders, and branch info</p></div></div>
  <div class="kpi-grid-3" id="empKpis">
    <div class="kpi-card k-blue"><div class="kpi-top"><div class="kpi-icon-wrap">✅</div></div><div class="kpi-value" id="empOpenTasks">—</div><div class="kpi-label">Open Tasks</div></div>
    <div class="kpi-card k-yellow"><div class="kpi-top"><div class="kpi-icon-wrap">🛒</div></div><div class="kpi-value" id="empPendingOrders">—</div><div class="kpi-label">Pending Orders</div></div>
    <div class="kpi-card k-orange"><div class="kpi-top"><div class="kpi-icon-wrap">⚠️</div></div><div class="kpi-value" id="empLowStock">—</div><div class="kpi-label">Low Stock Alerts</div></div>
  </div>
  <div class="two-col">
    <div class="card"><div class="card-header"><div class="card-header-title">My Tasks</div></div><div class="tbl-wrap"><table><thead><tr><th>Task</th><th>Priority</th><th>Due</th><th>Status</th><th>Action</th></tr></thead><tbody id="empTasksBody"></tbody></table></div></div>
    <div class="card"><div class="card-header"><div class="card-header-title">Pending Orders (My Branch)</div></div><div class="tbl-wrap"><table><thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody id="empOrdersBody"></tbody></table></div></div>
  </div>
  <div class="card" style="margin-top:20px;">
    <div class="card-header"><div class="card-header-title">Inventory Lookup</div></div>
    <div class="card-body">
      <div style="display:flex;gap:10px;margin-bottom:16px;">
        <input class="filter-input" type="text" id="invLookupQ" placeholder="Search product by name or SKU…" style="flex:1;">
        <button class="btn btn-primary btn-sm" onclick="doInvLookup()">Search</button>
      </div>
      <div class="tbl-wrap"><table><thead><tr><th>Name</th><th>SKU</th><th>Category</th><th>Price</th><th>Branch Stock</th><th>Total Stock</th></tr></thead><tbody id="invLookupBody"></tbody></table></div>
    </div>
  </div>
</div>

  </div><!-- end .content -->
</div><!-- end .main -->
</div><!-- end #shell -->

<!-- ═══════════════ MODALS ═══════════════ -->

<!-- Product Modal -->
<div class="modal-bg" id="modal-product" onclick="if(event.target===this)closeModal('product')">
  <div class="modal">
    <div class="modal-head"><h3 id="prodModalTitle">Add Product</h3><button class="modal-close" onclick="closeModal('product')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="pId">
      <div class="form-group"><label>Product Name *</label><input type="text" id="pName" placeholder="e.g. ASUS TUF Gaming Monitor"></div>
      <div class="form-row2">
        <div class="form-group"><label>SKU</label><input type="text" id="pSKU" placeholder="PCX-0001"></div>
        <div class="form-group"><label>Category</label><select id="pCat"><option>PC Components</option><option>Laptops</option><option>Peripherals</option><option>Storage</option><option>Networking</option></select></div>
      </div>
      <div class="form-row2">
        <div class="form-group"><label>Price (₱) *</label><input type="number" id="pPrice" placeholder="0.00" min="0"></div>
        <div class="form-group"><label>Stock Qty</label><input type="number" id="pStock" placeholder="0" min="0"></div>
      </div>
      <div class="form-row2">
        <div class="form-group"><label>Branch</label><select id="pBranch"><option value="">— None —</option></select></div>
        <div class="form-group"><label>Status</label><select id="pStatus"><option value="In Stock">In Stock</option><option value="Low Stock">Low Stock</option><option value="Out of Stock">Out of Stock</option><option value="Discontinued">Discontinued</option></select></div>
      </div>
      <div class="form-group"><label>Image URL</label><input type="url" id="pImage" placeholder="https://…"></div>
      <div class="form-group"><label>Description</label><textarea id="pDesc" placeholder="Product description…"></textarea></div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('product')">Cancel</button><button class="btn btn-primary" onclick="saveProduct()">Save Product</button></div>
  </div>
</div>

<!-- User / Employee Modal -->
<div class="modal-bg" id="modal-user" onclick="if(event.target===this)closeModal('user')">
  <div class="modal modal-wide">
    <div class="modal-head"><h3 id="userModalTitle">Add User</h3><button class="modal-close" onclick="closeModal('user')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="uId">
      <div class="form-row2">
        <div class="form-group"><label>First Name *</label><input type="text" id="uFirst"></div>
        <div class="form-group"><label>Last Name *</label><input type="text" id="uLast"></div>
      </div>
      <div class="form-group"><label>Email *</label><input type="email" id="uEmail"></div>
      <div class="form-row2">
        <div class="form-group"><label>Role</label><select id="uRole" onchange="document.getElementById('uPositionRow').style.display=this.value==='sub_admin'?'none':'grid';"><option value="customer">Customer</option><option value="staff">Staff</option><option value="manager">Manager</option><option value="sub_admin">Sub Admin</option><option value="main_admin">Main Admin</option></select></div>
        <div class="form-group"><label>Status</label><select id="uStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </div>
      <div class="form-row2">
        <div class="form-group"><label>Branch</label><select id="uBranch"><option value="">— None —</option></select></div>
        <div class="form-group"><label>Employee Type</label><select id="uEmpType"><option value="">— N/A —</option><option value="full_time">Full Time</option><option value="part_time">Part Time</option><option value="contractual">Contractual</option></select></div>
      </div>
      <div class="form-row2" id="uPositionRow">
        <div class="form-group"><label>Position</label><input type="text" id="uPosition" placeholder="e.g. Cashier"></div>
        <div class="form-group"><label>Department</label><input type="text" id="uDept" placeholder="e.g. Sales"></div>
      </div>
      <div class="form-group"><label>Password *</label><input type="password" id="uPass" placeholder="min. 6 characters"></div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('user')">Cancel</button><button class="btn btn-primary" onclick="saveUser()">Save</button></div>
  </div>
</div>

<!-- Branch Modal -->
<div class="modal-bg" id="modal-branch" onclick="if(event.target===this)closeModal('branch')">
  <div class="modal modal-wide">
    <div class="modal-head"><h3 id="branchModalTitle">Add Branch</h3><button class="modal-close" onclick="closeModal('branch')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="bId">
      <div class="form-group"><label>Branch Name *</label><input type="text" id="bName" placeholder="e.g. SM Cebu Branch"></div>
      <div class="form-group"><label>Address</label><input type="text" id="bAddress" placeholder="Street address"></div>
      <div class="form-row3">
        <div class="form-group"><label>City</label><input type="text" id="bCity" placeholder="Cebu City"></div>
        <div class="form-group"><label>Province</label><input type="text" id="bProvince" placeholder="Cebu"></div>
        <div class="form-group"><label>Postal Code</label><input type="text" id="bPostal" placeholder="6000"></div>
      </div>
      <div class="form-row2">
        <div class="form-group"><label>Phone</label><input type="text" id="bPhone" placeholder="+63 32 XXX XXXX"></div>
        <div class="form-group"><label>Email</label><input type="email" id="bEmail" placeholder="branch@pcexpress.com"></div>
      </div>
      <div class="form-group" id="bStatusGroup" style="display:none;"><label>Status</label><select id="bStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('branch')">Cancel</button><button class="btn btn-primary" onclick="saveBranch()">Save Branch</button></div>
  </div>
</div>

<!-- Branch Inventory Modal -->
<div class="modal-bg" id="modal-branch-inv" onclick="if(event.target===this)closeModal('branch-inv')">
  <div class="modal">
    <div class="modal-head"><h3 id="biModalTitle">Add Branch Inventory Item</h3><button class="modal-close" onclick="closeModal('branch-inv')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="biId">
      <div class="form-group"><label>Branch *</label><select id="biBranch"><option value="">Select branch…</option></select></div>
      <div class="form-group"><label>Product (from catalog)</label><select id="biProduct" onchange="biProductChange()"><option value="">— Select product —</option></select></div>
      <div class="form-group"><label>Product Name *</label><input type="text" id="biName" placeholder="Or type manually"></div>
      <div class="form-row2">
        <div class="form-group"><label>SKU</label><input type="text" id="biSku"></div>
        <div class="form-group"><label>Quantity *</label><input type="number" id="biQty" placeholder="0" min="0"></div>
      </div>
      <div class="form-row2">
        <div class="form-group"><label>Reorder Level</label><input type="number" id="biReorder" placeholder="10" min="0"></div>
        <div class="form-group"><label>Unit Cost (₱)</label><input type="number" id="biCost" placeholder="0.00" min="0"></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('branch-inv')">Cancel</button><button class="btn btn-primary" onclick="saveBranchInv()">Save</button></div>
  </div>
</div>

<!-- Task Modal -->
<div class="modal-bg" id="modal-task" onclick="if(event.target===this)closeModal('task')">
  <div class="modal">
    <div class="modal-head"><h3>Assign Task</h3><button class="modal-close" onclick="closeModal('task')">✕</button></div>
    <div class="modal-body">
      <div class="form-group"><label>Branch *</label><select id="taskBranch" onchange="loadTaskUsers()"><option value="">Select branch…</option></select></div>
      <div class="form-group"><label>Assign To *</label><select id="taskUser"><option value="">Select employee…</option></select></div>
      <div class="form-group"><label>Task Title *</label><input type="text" id="taskTitle" placeholder="e.g. Restock Monitor Section"></div>
      <div class="form-group"><label>Description</label><textarea id="taskDesc" placeholder="Task details…"></textarea></div>
      <div class="form-row2">
        <div class="form-group"><label>Priority</label><select id="taskPriority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
        <div class="form-group"><label>Due Date</label><input type="date" id="taskDue"></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('task')">Cancel</button><button class="btn btn-primary" onclick="saveTask()">Assign Task</button></div>
  </div>
</div>

<!-- Restock Modal -->
<div class="modal-bg" id="modal-stock" onclick="if(event.target===this)closeModal('stock')">
  <div class="modal">
    <div class="modal-head"><h3>Restock Item</h3><button class="modal-close" onclick="closeModal('stock')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="rstProductId">
      <div class="form-group"><label>Product</label><input type="text" id="rstProduct" readonly style="background:var(--gray-50)"></div>
      <div class="form-row2">
        <div class="form-group"><label>Quantity to Add</label><input type="number" id="rstQty" placeholder="0" min="1"></div>
        <div class="form-group"><label>Reorder Level</label><input type="number" id="rstReorder" placeholder="5" min="0"></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('stock')">Cancel</button><button class="btn btn-primary" onclick="saveRestock()">Confirm Restock</button></div>
  </div>
</div>

<!-- Order Status Modal -->
<div class="modal-bg" id="modal-orderstatus" onclick="if(event.target===this)closeModal('orderstatus')">
  <div class="modal" style="width:380px;">
    <div class="modal-head"><h3>Update Order Status</h3><button class="modal-close" onclick="closeModal('orderstatus')">✕</button></div>
    <div class="modal-body">
      <div class="form-group"><label>Order ID</label><input type="text" id="osOrderId" readonly style="background:var(--gray-50)"></div>
      <div class="form-group"><label>New Status</label><select id="osStatus"><option value="pending">Pending</option><option value="processing">Processing</option><option value="shipped">Shipped</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('orderstatus')">Cancel</button><button class="btn btn-primary" onclick="updateOrderStatus()">Update</button></div>
  </div>
</div>

<!-- Assign Branch Modal -->
<div class="modal-bg" id="modal-assign-branch" onclick="if(event.target===this)closeModal('assign-branch')">
  <div class="modal" style="width:420px;">
    <div class="modal-head"><h3>Assign Employee to Branch</h3><button class="modal-close" onclick="closeModal('assign-branch')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="abUserId">
      <div class="form-group"><label>Employee</label><input type="text" id="abUserName" readonly style="background:var(--gray-50)"></div>
      <div class="form-group"><label>Branch *</label><select id="abBranch"><option value="">Select branch…</option></select></div>
      <div class="form-row2">
        <div class="form-group"><label>Position</label><input type="text" id="abPosition" placeholder="e.g. Sales Staff"></div>
        <div class="form-group"><label>Department</label><input type="text" id="abDept" placeholder="e.g. Sales"></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('assign-branch')">Cancel</button><button class="btn btn-primary" onclick="saveAssignBranch()">Assign</button></div>
  </div>
</div>

<!-- Assign Rider -->
<div class="modal-bg" id="modal-assign" onclick="if(event.target===this)closeModal('assign')">
  <div class="modal"><div class="modal-head"><h3>Assign Rider</h3><button class="modal-close" onclick="closeModal('assign')">✕</button></div>
  <div class="modal-body"><div class="form-group"><label>Order ID</label><input type="text" id="assignOrderId" placeholder="PCX-00001"></div><div class="form-group"><label>Rider</label><select id="assignRider"><option>Miguel Santos</option><option>Jose Reyes</option><option>Carlos Garcia</option></select></div><div class="form-group"><label>Notes</label><textarea id="assignNotes" placeholder="Special instructions…"></textarea></div></div>
  <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('assign')">Cancel</button><button class="btn btn-primary" onclick="saveAssignment()">Assign</button></div></div>
</div>

<!-- Inventory Transfer -->
<div class="modal-bg" id="modal-transfer" onclick="if(event.target===this)closeModal('transfer')">
  <div class="modal modal-wide">
    <div class="modal-head"><h3>New Inventory Transfer</h3><button class="modal-close" onclick="closeModal('transfer')">✕</button></div>
    <div class="modal-body">
      <div class="form-row2">
        <div class="form-group"><label>From Branch *</label><select id="tfFrom" class="field-input" onchange="loadBranchInvProducts()"><option value="">Select source…</option></select></div>
        <div class="form-group"><label>To Branch *</label><select id="tfTo" class="field-input"><option value="">Select destination…</option></select></div>
      </div>
      <div class="form-group"><label>Product *</label><select id="tfProduct" class="field-input"><option value="">Select product…</option></select></div>
      <div class="form-row2">
        <div class="form-group"><label>Available Stock</label><input id="tfAvail" class="field-input" type="number" readonly placeholder="0"></div>
        <div class="form-group"><label>Qty to Transfer *</label><input id="tfQty" class="field-input" type="number" min="1" placeholder="Enter quantity"></div>
      </div>
      <div class="form-group"><label>Reason</label><input id="tfReason" class="field-input" type="text" placeholder="Optional reason"></div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('transfer')">Cancel</button><button class="btn btn-primary" onclick="doTransfer()">Transfer Stock</button></div>
  </div>
</div>

<!-- Add Rider -->
<div class="modal-bg" id="modal-rider" onclick="if(event.target===this)closeModal('rider')">
  <div class="modal"><div class="modal-head"><h3>Add Delivery Personnel</h3><button class="modal-close" onclick="closeModal('rider')">✕</button></div>
  <div class="modal-body"><div class="form-row2"><div class="form-group"><label>First Name</label><input type="text" id="rFirst"></div><div class="form-group"><label>Last Name</label><input type="text" id="rLast"></div></div><div class="form-group"><label>Phone</label><input type="tel" id="rPhone" placeholder="+63 9XX XXX XXXX"></div><div class="form-row2"><div class="form-group"><label>Vehicle</label><select id="rVehicle"><option>Motorcycle</option><option>Bicycle</option><option>Van</option></select></div><div class="form-group"><label>Zone</label><select id="rZone"><option>North</option><option>South</option><option>East</option><option>West</option><option>Central</option></select></div></div></div>
  <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('rider')">Cancel</button><button class="btn btn-primary" onclick="saveRider()">Add Rider</button></div></div>
</div>

<!-- Password Reset Modal -->
<div class="modal-bg" id="modal-pwreset" onclick="if(event.target===this)closeModal('pwreset')">
  <div class="modal" style="width:380px;">
    <div class="modal-head"><h3>Reset Password</h3><button class="modal-close" onclick="closeModal('pwreset')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="pwUserId">
      <div class="form-group"><label>User</label><input type="text" id="pwUserName" readonly style="background:var(--gray-50)"></div>
      <div class="form-group"><label>New Password *</label><input type="password" id="pwNewPass" placeholder="min. 6 characters"></div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline" onclick="closeModal('pwreset')">Cancel</button><button class="btn btn-primary" onclick="doPasswordReset()">Reset Password</button></div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"><span class="toast-icon"></span><span id="toastMsg"></span></div>

<script>
// ═══════════════════════════════════════════
//  CONSTANTS (PHP → JS)
// ═══════════════════════════════════════════
// Set dynamically by Firebase module after auth loads
let SESSION_ROLE     = window.ADMIN_ROLE      || '';
let SESSION_BRANCH   = null;
let IS_MAIN_ADMIN    = window.IS_MAIN_ADMIN   || false;
let IS_SUB_ADMIN     = false;
let IS_EMPLOYEE_ONLY = window.IS_EMPLOYEE_ONLY|| false;
let IS_STAFF         = window.IS_STAFF        || false;
let IS_SUPERVISOR    = false;
let IS_RIDER         = window.IS_RIDER        || false;

// ═══════════════════════════════════════════
//  API HELPER
// ═══════════════════════════════════════════
async function api(params, method='GET'){
  // Always route through Firestore if available (set by admin_firestore.js)
  if (typeof window._firestoreApi === 'function') {
    return window._firestoreApi(params);
  }
  // Firestore module not ready yet — poll up to 5s then retry
  return new Promise((resolve) => {
    let waited = 0;
    const interval = setInterval(() => {
      waited += 100;
      if (typeof window._firestoreApi === 'function') {
        clearInterval(interval);
        resolve(window._firestoreApi(params));
      } else if (waited >= 5000) {
        clearInterval(interval);
        resolve({success:false, message:'Firestore not ready. Please refresh the page.'});
      }
    }, 100);
  });
}

// ═══════════════════════════════════════════
//  NAVIGATION
// ═══════════════════════════════════════════
const viewTitles={
  overview:'Overview',transactions:'Transactions',products:'Products',
  inventory:'Inventory',orders:'Orders',delivery:'Delivery',
  riders:'Delivery Personnel',users:'Users',analytics:'Analytics',
  'branch-analytics':'Branch Analytics','order-history':'Order History',
  branches:'Branches','sub-admins':'Sub Admin Management',employees:'Employees',
  'branch-inventory':'Branch Inventory','inv-transfer':'Inventory Transfer',
  tasks:'Tasks','emp-dashboard':'My Dashboard',
  approvals:'Pending Approvals','branch-staff':'Branch Staff'
};
const viewSubs={
  overview:'Dashboard',transactions:'Finance',products:'Catalog',
  inventory:'Catalog',orders:'Operations',delivery:'Operations',
  riders:'Operations',users:'Operations',analytics:'Insights',
  'branch-analytics':'Insights','order-history':'Insights',
  branches:'Branch Ops','sub-admins':'Branch Ops',employees:'Branch Ops',
  'branch-inventory':'Branch Ops','inv-transfer':'Branch Ops',
  tasks:'Branch Ops','emp-dashboard':'My Work',
  approvals:'Branch Ops','branch-staff':'Branch Ops'
};

function showView(name,el){
  document.querySelectorAll('.view').forEach(v=>v.classList.remove('active'));
  const target=document.getElementById('view-'+name);
  if(target) target.classList.add('active');
  document.querySelectorAll('.sb-link').forEach(l=>l.classList.remove('active'));
  if(el) el.classList.add('active');
  document.getElementById('topbarTitle').innerHTML=
    (viewTitles[name]||name)+`<span class="breadcrumb">${viewSubs[name]||''}</span>`;
  document.getElementById('globalSearch').value='';
  loadView(name);
}
function loadView(name){
  if(name==='overview') loadOverview();
  else if(name==='orders'||name==='order-history') loadOrders();
  else if(name==='pending-orders') loadPendingOrders();
  else if(name==='transactions') loadTransactions();
  else if(name==='products') loadProducts();
  else if(name==='inventory') loadInventory();
  else if(name==='users') loadUsers();
  else if(name==='analytics') loadAnalytics();
  else if(name==='riders') renderRiders();
  else if(name==='delivery') loadOrders();
  else if(name==='branches') loadBranches();
  else if(name==='sub-admins') loadSubAdmins();
  else if(name==='employees') loadEmployees();
  else if(name==='branch-inventory') loadBranchInventory();
  else if(name==='inv-transfer') { loadTransferPage(); loadTransferLogs(); }
  else if(name==='tasks') loadTasks();
  else if(name==='branch-analytics') loadBranchAnalytics();
  else if(name==='emp-dashboard') loadEmpDashboard();
  else if(name==='approvals') loadPendingApprovals();
  else if(name==='branch-staff') loadBranchStaff();
}

// ═══════════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════════
function pillClass(s){
  s=(s||'').toLowerCase();
  const m={completed:'pill-green',delivered:'pill-green',active:'pill-green','in stock':'pill-green',paid:'pill-green',approved:'pill-green',
    pending:'pill-yellow','low stock':'pill-orange','out for delivery':'pill-orange',orange:'pill-orange',
    processing:'pill-blue',packed:'pill-cyan',assigned:'pill-purple','sub_admin':'pill-purple','main_admin':'pill-blue',
    manager:'pill-cyan',staff:'pill-cyan',employee:'pill-cyan','in_progress':'pill-cyan',
    cancelled:'pill-red',inactive:'pill-gray','out of stock':'pill-red',failed:'pill-red',rejected:'pill-red',
    low:'pill-cyan',medium:'pill-yellow',high:'pill-orange',urgent:'pill-red',shipped:'pill-cyan'};
  return m[s]||'pill-gray';
}
function pill(s){ return `<span class="pill ${pillClass(s)}">${cap(s)}</span>`; }
function cap(s){ return s?s.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()):''; }
function fmt(n){ return '₱'+Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtDate(d){ return d?new Date(d).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'}):'—'; }
function actBtns(...btns){ return `<div class="act-btns">${btns.join('')}</div>`; }
function eyeBtn(id) {
  // id should be a raw Firestore doc ID, not a function call string
  const rawId = typeof id === 'string' && id.startsWith('viewOrderDetail(')
    ? id.replace('viewOrderDetail(', '').replace(/\)$/, '')
    : id;
  return `<button class="act-btn act-eye" data-id="${rawId}" title="View">👁️</button>`;
}

function editBtn(id) { 
  return `<button class="act-btn act-edit" data-id="${id}" title="Edit">✏️</button>`; 
}

function delBtn(id) { 
  return `<button class="act-btn del act-delete" data-id="${id}" title="Delete">🗑️</button>`; 
}

function renderTable(tbodyId, rows, rowFn){
  const el=document.getElementById(tbodyId);
  if(!el) return;
  if(!rows||!rows.length){
    el.innerHTML=`<tr><td colspan="99" style="text-align:center;padding:32px;color:var(--gray-400);">No records found.</td></tr>`;
    return;
  }
  el.innerHTML=rows.map(rowFn).join('');
}

function populateBranchSelect(selId, includeAll=false, selectedVal=''){
  api({action:'get_branches_simple'}).then(d=>{
    if(!d.success) return;
    const sel=document.getElementById(selId);
    if(!sel) return;
    let html=includeAll?'<option value="">All Branches</option>':'<option value="">Select branch…</option>';
    d.branches.forEach(b=>{ html+=`<option value="${b.id}" ${b.id==selectedVal?'selected':''}>${b.name}${b.is_main?' ★':''}</option>`; });
    sel.innerHTML=html;
  });
}

function populateBranchSelects(){
  // Populate all branch dropdowns on the page
  ['prodBranchFilter','pBranch','uBranch','biBranch','branchInvFilter','tfFrom','tfTo',
   'taskBranch','taskBranchFilter','abBranch','assignSubBranch','baFilter','empBranchFilter','staffBranchFilter'].forEach(id=>{
    const el=document.getElementById(id);
    if(!el) return;
    const includeAll=id.endsWith('Filter')||id==='baFilter';
    populateBranchSelect(id, includeAll);
  });
}

// ═══════════════════════════════════════════
//  DRAW BAR CHART
// ═══════════════════════════════════════════
function drawBarChart(containerId, data, colorFn){
  const el=document.getElementById(containerId);
  if(!el||!data.length) return;
  const max=Math.max(...data.map(d=>d.v),1);
  el.innerHTML=data.map((d,i)=>`
    <div class="bar-col">
      <div class="bar-val">₱${(d.v/1000).toFixed(0)}K</div>
      <div class="bar-fill" style="height:${Math.round((d.v/max)*140)}px;background:${colorFn(i,data.length)}"></div>
      <div class="bar-lbl">${d.m}</div>
    </div>`).join('');
}

// ═══════════════════════════════════════════
//  OVERVIEW
// ═══════════════════════════════════════════
async function loadOverview(){
  const d=await api({action:'get_overview'});
  if(!d.success) return;
  if(d.total_sales!==undefined) document.getElementById('kpiRevenue').textContent='₱'+Number(d.total_sales).toLocaleString('en-PH',{maximumFractionDigits:0});
  if(d.total_orders!==undefined) document.getElementById('kpiOrders').textContent=d.total_orders;
  if(d.status_breakdown?.pending!==undefined) document.getElementById('kpiPending').textContent=d.status_breakdown.pending;
  if(d.total_users!==undefined) document.getElementById('kpiUsers').textContent=d.total_users;

  // Extended KPIs row 2
  const kpis2=document.getElementById('overviewKpis2');
  if(kpis2&&IS_MAIN_ADMIN){
    kpis2.innerHTML=`
      <div class="kpi-card k-cyan"><div class="kpi-top"><div class="kpi-icon-wrap">🏢</div></div><div class="kpi-value">${d.total_branches??'—'}</div><div class="kpi-label">Branches</div></div>
      <div class="kpi-card k-purple"><div class="kpi-top"><div class="kpi-icon-wrap">👤</div></div><div class="kpi-value">${d.total_sub_admins??'—'}</div><div class="kpi-label">Sub Admins</div></div>
      <div class="kpi-card k-orange"><div class="kpi-top"><div class="kpi-icon-wrap">👥</div></div><div class="kpi-value">${d.total_employees??'—'}</div><div class="kpi-label">Employees</div></div>
      <div class="kpi-card k-yellow"><div class="kpi-top"><div class="kpi-icon-wrap">⚠️</div></div><div class="kpi-value">${d.low_stock??'—'}</div><div class="kpi-label">Low Stock Items</div></div>
      <div class="kpi-card k-red"><div class="kpi-top"><div class="kpi-icon-wrap">📋</div></div><div class="kpi-value">${d.pending_approvals??'—'}</div><div class="kpi-label">Pending Approvals</div></div>`;
  }

  // Sidebar badges
  const pd=d.status_breakdown?.pending||0;
  document.querySelectorAll('.sb-link .badge').forEach(b=>{
    const txt=b.closest('.sb-link').textContent;
    if(txt.includes('Orders')) b.textContent=pd;
    if(txt.includes('Transactions')) b.textContent=pd;
  });

  // Bar chart
  if(d.monthly?.length){
    drawBarChart('barChart',d.monthly.map(r=>({m:r.month,v:parseFloat(r.revenue)||0})),
      (i,len)=>{const colors=['#dce8ff','#c8dbff','#a8c4ff','#7ba8ff','#4d87ff','#003da5'];return colors[i]||'#003da5';});
  }

  // Donut values
  const total=d.total_orders||1;
  const sb=d.status_breakdown||{};
  ['Completed','Pending','Cancelled','Processing'].forEach((k,i)=>{
    const el=document.getElementById('d'+k);
    const v=sb[k.toLowerCase()]||0;
    if(el) el.textContent=`${v} (${Math.round(v/total*100)}%)`;
  });

  // Recent orders
  if(d.recent_orders){
    renderTable('recentOrdersBody',d.recent_orders,o=>`<tr>
      <td><span class="tbl-id">${o.order_number||o.id}</span></td>
      <td><div class="tbl-name">${o.customer}</div></td>
      <td><span class="tbl-amount">${fmt(o.total_amount)}</span></td>
      <td>${fmtDate(o.created_at)}</td>
      <td>${pill(o.status)}</td>
    </tr>`);
  }

  // Flow counters
  if(document.getElementById('flowProcessing')) document.getElementById('flowProcessing').textContent=sb.processing||0;
  if(document.getElementById('flowCompleted')) document.getElementById('flowCompleted').textContent=sb.completed||0;
}

// ═══════════════════════════════════════════
//  ORDERS
// ═══════════════════════════════════════════
let _ordersCache=[];
async function loadOrders(search='',status=''){
  const d=await api({action:'get_orders',search,status});
  if(!d.success) return;
  _ordersCache=d.orders||[];

  const actionCell = o => {
    const eye = eyeBtn(`viewOrderDetail(${o.id})`);
    if(IS_STAFF){
      const updateBtn = `<button class="act-btn" onclick="openOrderStatus(${o.id},'${o.status}')" title="Update Status">🔄</button>`;
      const acceptBtn = o.status==='pending'
        ? `<button class="act-btn" onclick="acceptOrder(${o.id})" title="Accept Order" style="color:var(--green)">✅</button>`
        : '';
      const delB = IS_MAIN_ADMIN ? delBtn(`deleteOrder(${o.id})`) : '';
      return actBtns(eye, acceptBtn, updateBtn, delB);
    }
    if(IS_MAIN_ADMIN) return actBtns(eye, delBtn(`deleteOrder(${o.id})`));
    return actBtns(eye);
  };

  const acceptedCell = o => o.accepted_by_name && o.accepted_by_name.trim() && o.accepted_by_name!=='  '
    ? `<span style="font-size:11px;color:var(--green);">✅ ${o.accepted_by_name}</span>`
    : `<span style="font-size:11px;color:var(--gray-400);">—</span>`;

  const rowFn=o=>`<tr>
    <td><span class="tbl-id">${o.order_number}</span></td>
    <td><div class="tbl-name">${o.customer_name}</div><div class="tbl-sub">${o.customer_email||''}</div></td>
    <td>${o.items_count} item${o.items_count!=1?'s':''}</td>
    <td><span class="tbl-amount">${fmt(o.total_amount)}</span></td>
    <td>${fmtDate(o.created_at)}</td>
    <td>${pill(o.status)}</td>
    <td>${acceptedCell(o)}</td>
    <td>${actionCell(o)}</td>
  </tr>`;

  const histFn=o=>`<tr>
    <td><span class="tbl-id">${o.order_number}</span></td>
    <td><div class="tbl-name">${o.customer_name}</div></td>
    <td>${fmtDate(o.created_at)}</td>
    <td>${cap(o.payment_method)}</td>
    <td><span class="tbl-amount">${fmt(o.total_amount)}</span></td>
    <td>${pill(o.status)}</td>
    <td>${actBtns(eyeBtn(`viewOrderDetail(${o.id})`))}</td>
  </tr>`;

  const delFn=o=>`<tr>
    <td><span class="tbl-id">${o.order_number}</span></td>
    <td><div class="tbl-name">${o.customer_name}</div></td>
    <td>${o.mode==='ship'?o.ship_city||'—':'Pickup: '+(o.pickup_store||'—')}</td>
    <td>${cap(o.mode)}</td>
    <td>${fmtDate(o.created_at)}</td>
    <td>${pill(o.status)}</td>
    <td>${IS_STAFF
      ? actBtns(`<button class="act-btn" onclick="openOrderStatus(${o.id},'${o.status}')" title="Update Status">🔄</button>`)
      : actBtns(eyeBtn(`viewOrderDetail(${o.id})`))
    }</td>
  </tr>`;

  renderTable('ordersBody',_ordersCache,rowFn);
  renderTable('historyBody',_ordersCache,histFn);
  renderTable('deliveryBody',_ordersCache,delFn);
}

// ── PENDING ORDERS (Staff-facing action queue) ───────────────────
let _pendingOrdersCache=[];
async function loadPendingOrders(){
  const d=await api({action:'get_pending_orders'});
  if(!d.success) return;
  _pendingOrdersCache=d.orders||[];

  // Update badge
  const pendingCount=_pendingOrdersCache.filter(o=>o.status==='pending').length;
  const el=document.getElementById('pendingOrderBadge');
  if(el) el.textContent=pendingCount||'';

  const acceptedCell = o => o.accepted_by_name && o.accepted_by_name.trim() && o.accepted_by_name!=='  '
    ? `<span style="font-size:11px;color:var(--green);">✅ ${o.accepted_by_name}</span>`
    : `<span style="font-size:11px;color:var(--gray-400);">Not yet accepted</span>`;

  renderTable('pendingOrdersBody',_pendingOrdersCache,o=>`<tr>
    <td><span class="tbl-id">${o.order_number}</span></td>
    <td><div class="tbl-name">${o.customer_name}</div><div class="tbl-sub">${o.customer_email||''}</div></td>
    <td>${o.items_count} item${o.items_count!=1?'s':''}</td>
    <td><span class="tbl-amount">${fmt(o.total_amount)}</span></td>
    <td>${fmtDate(o.created_at)}</td>
    <td>${pill(o.status)}</td>
    <td>${acceptedCell(o)}</td>
    ${IS_STAFF?`<td>${actBtns(
      eyeBtn(`viewOrderDetail(${o.id})`),
      o.status==='pending'?`<button class="act-btn" onclick="acceptOrder(${o.id})" title="Accept Order" style="color:var(--green);font-weight:700;">✅ Accept</button>`:'',
      o.status==='processing'?`<button class="act-btn" onclick="confirmPrep(${o.id})" title="Confirm Preparation" style="color:var(--blue);">📦 Confirm</button>`:'',
      `<button class="act-btn" onclick="openOrderStatus(${o.id},'${o.status}')" title="Update Status">🔄</button>`
    )}</td>`:''}
  </tr>`);
}

// ── ACCEPT ORDER (Staff only — enforced server-side too) ─────────
async function acceptOrder(id){
  if(!IS_STAFF){showToast('Only staff can accept orders.','error');return;}
  if(!confirm('Accept this order and begin processing?')) return;
  const d=await api({action:'accept_order',id},'POST');
  if(d.success){showToast('Order accepted ✓');loadPendingOrders();loadOrders();loadOverview();}
  else showToast(d.message||'Error','error');
}

// ── CONFIRM PREPARATION (Staff only) ────────────────────────────
async function confirmPrep(id){
  if(!IS_STAFF){showToast('Only staff can confirm order preparation.','error');return;}
  if(!confirm('Confirm that this order is packed and ready for shipment/pickup?')) return;
  const d=await api({action:'confirm_preparation',id},'POST');
  if(d.success){showToast('Preparation confirmed — status set to Shipped ✓');loadPendingOrders();loadOrders();loadOverview();}
  else showToast(d.message||'Error','error');
}

async function deleteOrder(id){
  if(!confirm('Delete this order?')) return;
  const d=await api({action:'delete_order',id},'POST');
  if(d.success){showToast('Order deleted.');loadOrders();loadOverview();}
  else showToast(d.message||'Error','error');
}

async function viewOrderDetail(id){
  const d=await api({action:'get_order_detail',id});
  if(!d.success||!d.order){showToast('Order not found','error');return;}
  const o=d.order;

  // Support both Firestore camelCase and legacy snake_case field names
  const orderNum   = o.orderNumber   || o.order_number  || '—';
  const custName   = o.customer_name || `${o.shipFirstName||''} ${o.shipLastName||''}`.trim() || o.guestEmail || 'Guest';
  const custEmail  = o.customer_email|| o.guestEmail    || o.email || '—';
  const shipAddr   = o.shipAddress   || o.ship_address  || '';
  const shipCity   = o.shipCity      || o.ship_city     || '';
  const custAddr   = shipAddr ? `${shipAddr}${shipCity ? ', '+shipCity : ''}` : 'Pickup';
  const payMethod  = o.paymentMethod || o.payment_method|| '—';
  const subtotal   = o.subtotal      || 0;
  const shipFee    = o.shippingFee   || o.shipping_fee  || 0;
  const grandTotal = o.grandTotal    || o.grand_total   || 0;
  const createdAt  = o.created_at    || (o.createdAt?.seconds ? new Date(o.createdAt.seconds*1000).toISOString() : null);

  document.getElementById('detailOrderId').textContent='Order #'+orderNum;
  document.getElementById('dOrderNum').textContent=orderNum;
  document.getElementById('dOrderDate').textContent=fmtDate(createdAt);
  document.getElementById('dPayment').textContent=cap(payMethod);
  document.getElementById('dDelStatus').innerHTML=pill(o.status);
  document.getElementById('dCustName').textContent=custName;
  document.getElementById('dCustEmail').textContent=custEmail;
  document.getElementById('dCustAddr').textContent=custAddr;
  document.getElementById('dSubtotal').textContent=fmt(subtotal);
  document.getElementById('dShipping').textContent=fmt(shipFee);
  document.getElementById('dTotal').textContent=fmt(grandTotal);
  document.getElementById('dOrderItems').innerHTML=(o.items||[]).map(i=>{
    const name = i.productName || i.product_name || 'Product';
    const qty  = i.qty || 1;
    const line = i.lineTotal || i.line_total || (i.unitPrice||i.unit_price||0)*qty;
    return `<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--gray-100);font-size:13px;"><span>${name} × ${qty}</span><span style="font-weight:600;">${fmt(line)}</span></div>`;
  }).join('');

  // Show detail panel — scroll it into view
  const panel = document.getElementById('orderDetailPanel');
  if(panel){ panel.style.display='block'; panel.scrollIntoView({behavior:'smooth'}); }
}

// ═══════════════════════════════════════════
//  TRANSACTIONS
// ═══════════════════════════════════════════
async function loadTransactions(search='',status=''){
  const d=await api({action:'get_orders',search,status});
  if(!d.success) return;
  renderTable('transactionsBody',d.orders||[],o=>`<tr>
    <td><span class="tbl-id">${o.order_number}</span></td>
    <td><div class="tbl-name">${o.customer_name}</div></td>
    <td>${cap(o.payment_method)}</td>
    <td><span class="tbl-amount">${fmt(o.total_amount)}</span></td>
    <td>${fmtDate(o.created_at)}</td>
    <td>${pill(o.status)}</td>
    <td>${actBtns(eyeBtn(`viewOrderDetail(${o.id})`))}</td>
  </tr>`);
}

// ═══════════════════════════════════════════
//  PRODUCTS
// ═══════════════════════════════════════════
let _productsCache=[];
async function loadProducts(search=''){
  const branchId=document.getElementById('prodBranchFilter')?.value||'';
  const invStatus=document.getElementById('prodApprovalFilter')?.value||'';
  const d=await api({action:'get_products',search,branch_id:branchId,inv_status:invStatus});
  if(!d.success) return;
  _productsCache=d.products||[];
  renderTable('productsBody',_productsCache,p=>`<tr>
    <td><span class="tbl-id">${p.sku||'—'}</span></td>
    <td><div class="tbl-name">${p.name}</div></td>
    <td>${p.branch_name?`<span class="pill pill-blue">${p.branch_name}</span>`:'<span class="pill pill-gray">All</span>'}</td>
    <td>${p.category}</td>
    <td><span class="tbl-amount">${fmt(p.price)}</span></td>
    <td>${p.stock}</td>
    <td>${pill(p.inventory_status||'approved')}</td>
    <td>${pill(p.status)}</td>
    <td>${actBtns(
      editBtn(`editProduct(${p.id})`),
      IS_MAIN_ADMIN&&p.inventory_status==='pending'?`<button class="act-btn" onclick="approveProduct(${p.id})" title="Approve" style="color:var(--green)">✓</button>`:'',
      IS_MAIN_ADMIN&&p.inventory_status==='pending'?`<button class="act-btn del" onclick="rejectProduct(${p.id})" title="Reject">✗</button>`:'',
      IS_MAIN_ADMIN?delBtn(`deleteProduct(${p.id})`):'')}</td>
  </tr>`);
}

function openProductModal(id=0){
  document.getElementById('pId').value='';
  document.getElementById('prodModalTitle').textContent=id?'Edit Product':'Add Product';
  if(!id){['pName','pSKU','pImage','pDesc'].forEach(i=>document.getElementById(i).value='');document.getElementById('pPrice').value='';document.getElementById('pStock').value='';}
  populateBranchSelect('pBranch',false);
  openModal('product');
}

async function editProduct(id){
  const d=await api({action:'get_products'});
  if(!d.success) return;
  const p=d.products.find(x=>x.id==id);
  if(!p){showToast('Product not found','error');return;}
  document.getElementById('pId').value=p.id;
  document.getElementById('prodModalTitle').textContent='Edit Product';
  document.getElementById('pName').value=p.name;
  document.getElementById('pSKU').value=p.sku||'';
  document.getElementById('pPrice').value=p.price;
  document.getElementById('pStock').value=p.stock;
  document.getElementById('pDesc').value=p.description||'';
  document.getElementById('pImage').value=p.image||'';
  populateBranchSelect('pBranch',false,p.branch_id||'');
  openModal('product');
}

async function saveProduct(){
  const params={
    action:'save_product',
    id:document.getElementById('pId').value||null,
    name:document.getElementById('pName').value.trim(),
    sku:document.getElementById('pSKU').value.trim(),
    category:document.getElementById('pCat').value,
    price:document.getElementById('pPrice').value,
    stock:document.getElementById('pStock').value,
    status:document.getElementById('pStatus').value,
    branch_id:document.getElementById('pBranch').value,
    description:document.getElementById('pDesc').value.trim(),
    image:document.getElementById('pImage').value.trim(),
  };
  if(!params.name){showToast('Product name required','error');return;}
  const d=await api(params,'POST');
  if(d.success){closeModal('product');showToast('Product saved ✓');loadProducts();}
  else showToast(d.message||'Error','error');
}

async function deleteProduct(id){
  if(!confirm('Delete this product?')) return;
  const d=await api({action:'delete_product',id},'POST');
  if(d.success){showToast('Product deleted.');loadProducts();}
  else showToast(d.message||'Error','error');
}

async function approveProduct(id){
  const d=await api({action:'approve_product',id},'POST');
  if(d.success){showToast('Product approved ✓');loadProducts();if(document.getElementById('view-approvals').classList.contains('active')) loadPendingApprovals();}
  else showToast(d.message||'Error','error');
}

async function rejectProduct(id){
  const d=await api({action:'reject_product',id},'POST');
  if(d.success){showToast('Product rejected.');loadProducts();if(document.getElementById('view-approvals').classList.contains('active')) loadPendingApprovals();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  INVENTORY
// ═══════════════════════════════════════════
let _invCache=[];
async function loadInventory(search='',status=''){
  const d=await api({action:'get_inventory',search,status});
  if(!d.success) return;
  _invCache=d.inventory||[];
  let inStock=0,low=0,out=0;
  _invCache.forEach(i=>{
    const qty = i.quantity || i.stock || 0;  // ← Handle both field names
    if(qty === 0) out++;
    else if(qty <= 5) low++;
    else inStock++;
  });
  document.getElementById('kpiInStock').textContent=inStock;
  document.getElementById('kpiLowStock').textContent=low;
  document.getElementById('kpiOutStock').textContent=out;
  renderTable('invBody',_invCache,i=>{
    const qty = i.quantity || i.stock || 0;  // ← Use either field
    return `<tr>
    <td><span class="tbl-id">${i.sku||'—'}</span></td>
    <td><div class="tbl-name">${i.name}</div>${i.branch_name?`<div class="tbl-sub">${i.branch_name}</div>`:''}</td>
    <td>${i.category}</td>
    <td style="font-weight:700;${qty<=0?'color:var(--red)':qty<=5?'color:var(--yellow)':''}">${qty}</td>
    <td>—</td>
    <td>${i.last_updated?fmtDate(i.last_updated):'—'}</td>
    <td>${pill(i.inv_status||'active')}</td>
    <td>${actBtns(`<button class="act-btn" onclick="openRestockModal(${i.id},'${i.name.replace(/'/g,"\\'")}',${qty})" title="Restock">📦</button>`)}</td>
  </tr>`;
  });
}

function openRestockModal(id=0,name='',stock=0){
  document.getElementById('rstProductId').value=id;
  document.getElementById('rstProduct').value=name||'Select from inventory';
  document.getElementById('rstQty').value='';
  document.getElementById('rstReorder').value='5';
  openModal('stock');
}

async function saveRestock(){
  const pid=document.getElementById('rstProductId').value;
  const qty=document.getElementById('rstQty').value;
  if(!pid){showToast('No product selected','error');return;}
  if(!qty||qty<1){showToast('Enter a valid quantity','error');return;}
  const d=await api({action:'restock',product_id:pid,qty},'POST');
  if(d.success){closeModal('stock');showToast(d.message||'Restocked ✓');loadInventory();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  BRANCHES
// ═══════════════════════════════════════════
async function loadBranches(){
  const d=await api({action:'get_branches'});
  if(!d.success) return;
  const branches=d.branches||[];
  document.getElementById('kpiBranches').textContent=branches.length;

  const d2=await api({action:'get_overview'});
  if(d2.success){
    document.getElementById('kpiSubAdmins').textContent=d2.total_sub_admins??'—';
    document.getElementById('kpiTotalEmployees').textContent=d2.total_employees??'—';
  }

  // Branch cards
  document.getElementById('branchGrid').innerHTML=branches.slice(0,6).map(b=>`
    <div class="branch-card ${b.is_main?'main-branch':''}">
      ${b.is_main?'<div class="branch-badge"><span class="pill pill-blue">★ Main</span></div>':''}
      <div class="branch-card-head">
        <div class="branch-av ${b.is_main?'':'sub'}">${b.name.charAt(0)}</div>
        <div><div class="branch-name">${b.name}</div><div class="branch-sub">${b.city||'—'}</div></div>
      </div>
      <div class="branch-stats">
        <div class="branch-stat"><div class="branch-stat-val">${b.order_count||0}</div><div class="branch-stat-lbl">Orders</div></div>
        <div class="branch-stat"><div class="branch-stat-val">${fmt(b.total_revenue||0).replace('₱','₱')}</div><div class="branch-stat-lbl">Revenue</div></div>
        <div class="branch-stat"><div class="branch-stat-val">${b.staff_count||0}</div><div class="branch-stat-lbl">Staff</div></div>
        <div class="branch-stat"><div class="branch-stat-val ${b.low_stock_count>0?'style="color:var(--orange)"':''}">${b.low_stock_count||0}</div><div class="branch-stat-lbl">Low Stock</div></div>
      </div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        ${pill(b.status)}
        ${b.sub_admin_name?`<span style="font-size:11px;color:var(--gray-400);">👤 ${b.sub_admin_name}</span>`:'<span style="font-size:11px;color:var(--red);">No Sub Admin</span>'}
        ${!b.is_main?`<button class="btn btn-outline btn-sm" style="margin-left:auto;" onclick="editBranch(${b.id})">Edit</button>`:''}
        ${!b.is_main?`<button class="btn btn-warning-soft btn-sm" onclick="setMainBranch(${b.id})" title="Set as Main Branch">★</button>`:''}
      </div>
    </div>`).join('');

  // Table
  renderTable('branchesBody',branches,b=>`<tr>
    <td><div class="tbl-name">${b.name}${b.is_main?' <span class="pill pill-blue" style="font-size:10px;">MAIN</span>':''}</div></td>
    <td>${b.city||'—'}</td>
    <td>${b.sub_admin_name?`<span style="font-size:12px;">${b.sub_admin_name}</span>`:'<span style="color:var(--red);font-size:12px;">Unassigned</span>'}</td>
    <td>${b.staff_count||0}</td>
    <td>${b.order_count||0}</td>
    <td><span class="tbl-amount">${fmt(b.total_revenue||0)}</span></td>
    <td>${b.low_stock_count>0?`<span class="pill pill-orange">⚠ ${b.low_stock_count} low</span>`:'<span class="pill pill-green">OK</span>'}${b.out_of_stock_count>0?` <span class="pill pill-red">✗ ${b.out_of_stock_count}</span>`:''}</td>
    <td>${pill(b.status)}</td>
    <td>${actBtns(
      !b.is_main?editBtn(`editBranch(${b.id})`):'',
      !b.is_main?delBtn(`deleteBranch(${b.id})`):''
    )}</td>
  </tr>`);
}

function openBranchModal(id=0){
  document.getElementById('bId').value='';
  document.getElementById('branchModalTitle').textContent=id?'Edit Branch':'Add Branch';
  document.getElementById('bStatusGroup').style.display=id?'block':'none';
  if(!id){['bName','bAddress','bCity','bProvince','bPostal','bPhone','bEmail'].forEach(i=>document.getElementById(i).value='');}
  openModal('branch');
}

async function editBranch(id){
  const d=await api({action:'get_branch_detail',id});
  if(!d.success||!d.branch){showToast('Branch not found','error');return;}
  const b=d.branch;
  document.getElementById('bId').value=b.id;
  document.getElementById('branchModalTitle').textContent='Edit Branch';
  document.getElementById('bName').value=b.name;
  document.getElementById('bAddress').value=b.address||'';
  document.getElementById('bCity').value=b.city||'';
  document.getElementById('bProvince').value=b.province||'';
  document.getElementById('bPostal').value=b.postal_code||'';
  document.getElementById('bPhone').value=b.phone||'';
  document.getElementById('bEmail').value=b.email||'';
  document.getElementById('bStatusGroup').style.display='block';
  openModal('branch');
}

async function saveBranch(){
  const id=document.getElementById('bId').value||0;
  const params={
    action:id?'update_branch':'add_branch',
    id,
    name:document.getElementById('bName').value.trim(),
    address:document.getElementById('bAddress').value.trim(),
    city:document.getElementById('bCity').value.trim(),
    province:document.getElementById('bProvince').value.trim(),
    postalCode:document.getElementById('bPostal').value.trim(),
    phone:document.getElementById('bPhone').value.trim(),
    email:document.getElementById('bEmail').value.trim(),
    status:document.getElementById('bStatus')?.value||'active',
  };
  if(!params.name){showToast('Branch name required','error');return;}
  const d=await api(params,'POST');
  if(d.success){closeModal('branch');showToast('Branch saved ✓');loadBranches();populateBranchSelects();}
  else showToast(d.message||'Error','error');
}

async function deleteBranch(id){
  if(!confirm('Delete this branch? All staff assignments and inventory records will be removed.')) return;
  const d=await api({action:'delete_branch',id},'POST');
  if(d.success){showToast('Branch deleted.');loadBranches();populateBranchSelects();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  SUB ADMINS
// ═══════════════════════════════════════════
async function loadSubAdmins(){
  const d=await api({action:'get_sub_admins'});
  if(!d.success) return;
  renderTable('subAdminsBody',d.sub_admins||[],u=>`<tr>
    <td><div class="tbl-name">${u.first_name} ${u.last_name}</div></td>
    <td>${u.email}</td>
    <td>${u.branch_name?pill(u.branch_name):'<span style="color:var(--red);font-size:12px;">Unassigned</span>'}</td>
    <td>${pill(u.status)}</td>
    <td>${actBtns(delBtn(`deleteUser(${u.id})`))}</td>
  </tr>`);

  // Populate assign dropdowns
  populateBranchSelect('assignSubBranch',false);
  const d2=await api({action:'get_users',role:'',status:'active'});
  if(d2.success){
    const eligible=d2.users.filter(u=>['employee','staff','manager','sub_admin'].includes(u.role));
    document.getElementById('assignSubUser').innerHTML=`<option value="">Select user…</option>`+
      eligible.map(u=>`<option value="${u.id}">${u.first_name} ${u.last_name} (${cap(u.role)})</option>`).join('');
  }
}

function openAddUserModal(defaultRole='staff'){
  document.getElementById('uId').value='';
  const titles={staff:'Add Staff Member',sub_admin:'Add Sub Admin',manager:'Add Manager'};
  document.getElementById('userModalTitle').textContent=titles[defaultRole]||'Add User / Employee';
  ['uFirst','uLast','uEmail','uPass','uPosition','uDept'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('uRole').value=IS_SUB_ADMIN?'staff':defaultRole;
  document.getElementById('uStatus').value='active';
  document.getElementById('uPositionRow').style.display=defaultRole==='sub_admin'?'none':'grid';
  const roleSel=document.getElementById('uRole');
  if(roleSel) roleSel.disabled=IS_SUB_ADMIN;
  populateBranchSelect('uBranch',false,IS_SUB_ADMIN?SESSION_BRANCH:'');
  const branchSel=document.getElementById('uBranch');
  if(branchSel) branchSel.disabled=IS_SUB_ADMIN;
  openModal('user');
}

async function assignSubAdmin(){
  const user_id=document.getElementById('assignSubUser').value;
  const branch_id=document.getElementById('assignSubBranch').value;
  if(!user_id||!branch_id){showToast('Select both user and branch','error');return;}
  const d=await api({action:'assign_sub_admin',user_id,branch_id},'POST');
  if(d.success){showToast('Sub admin assigned ✓');loadSubAdmins();loadBranches();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  EMPLOYEES
// ═══════════════════════════════════════════
async function loadEmployees(search=''){
  const branchId=document.getElementById('empBranchFilter')?.value||'';
  const roleFilter=document.getElementById('empRoleFilter')?.value||'';
  const d=await api({action:'get_employees',branch_id:branchId,role:roleFilter});
  if(!d.success) return;
  renderTable('employeesBody',d.employees||[],e=>`<tr>
    <td>
      <div class="tbl-name">${e.firstName || e.first_name || '—'} ${e.lastName || e.last_name || '—'}</div>
      <div class="tbl-sub">${e.email}</div>
    </td>
    <td>${pill(e.role)}</td>
    <td>${e.branch_name?`<span class="pill pill-blue">${e.branch_name}</span>`:'<span class="pill pill-gray">Unassigned</span>'}</td>
    <td>${e.position||'—'}</td>
    <td>${e.open_tasks||0} open / ${e.completed_tasks||0} done</td>
    <td>${pill(e.status||'active')}</td>
    <td>${actBtns(
      IS_MAIN_ADMIN?`<button class="act-btn" onclick="openAssignBranchModal(${e.id},'${((e.firstName || e.first_name || '') + ' ' + (e.lastName || e.last_name || '')).replace(/'/g,"\\'")}')" title="Assign Branch">🏢</button>`:'',
      delBtn(`deleteUser(${e.id})`)
    )}</td>
  </tr>`);
}

function openAssignBranchModal(userId, userName){
  document.getElementById('abUserId').value=userId;
  document.getElementById('abUserName').value=userName;
  document.getElementById('abPosition').value='';
  document.getElementById('abDept').value='';
  populateBranchSelect('abBranch',false);
  openModal('assign-branch');
}

async function saveAssignBranch(){
  const user_id=document.getElementById('abUserId').value;
  const branch_id=document.getElementById('abBranch').value;
  const position=document.getElementById('abPosition').value.trim();
  const department=document.getElementById('abDept').value.trim();
  if(!branch_id){showToast('Select a branch','error');return;}
  const d=await api({action:'assign_employee_branch',user_id,branch_id,position,department},'POST');
  if(d.success){closeModal('assign-branch');showToast('Employee assigned ✓');loadEmployees();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  BRANCH INVENTORY
// ═══════════════════════════════════════════
async function loadBranchInventory(search=''){
  const branchId=document.getElementById('branchInvFilter')?.value||'';
  const d=await api({action:'get_branch_inventory',branch_id:branchId,search});
  if(!d.success) return;
  renderTable('branchInvBody',d.inventory||[],i=>`<tr>
    <td><div class="tbl-name">${i.product_name}</div></td>
    <td><span class="tbl-id">${i.sku||'—'}</span></td>
    <td><span class="pill pill-blue">${i.branch_name}</span></td>
    <td style="font-weight:700;${i.quantity<=0?'color:var(--red)':i.quantity<=i.reorder_level?'color:var(--yellow)':''}">${i.quantity}</td>
    <td>${i.reorder_level}</td>
    <td>${fmt(i.unit_cost||0)}</td>
    <td>${pill(i.inv_status)}</td>
    <td>${fmtDate(i.last_updated)}</td>
    <td>${actBtns(
      editBtn(`editBranchInv(${i.id},${i.quantity},${i.reorder_level})`),
      delBtn(`deleteBranchInv(${i.id})`)
    )}</td>
  </tr>`);
}

function openBranchInvModal(){
  document.getElementById('biId').value='';
  document.getElementById('biModalTitle').textContent='Add Branch Inventory Item';
  ['biName','biSku'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('biQty').value='';
  document.getElementById('biReorder').value='10';
  document.getElementById('biCost').value='';
  populateBranchSelect('biBranch',false);
  // Populate products
  api({action:'get_products_simple'}).then(d=>{
    if(!d.success) return;
    document.getElementById('biProduct').innerHTML=`<option value="">— Select product —</option>`+
      d.products.map(p=>`<option value="${p.id}" data-name="${p.name}" data-sku="${p.sku}">${p.name}</option>`).join('');
  });
  openModal('branch-inv');
}

function biProductChange(){
  const sel=document.getElementById('biProduct');
  const opt=sel.options[sel.selectedIndex];
  if(opt.value){
    document.getElementById('biName').value=opt.dataset.name||'';
    document.getElementById('biSku').value=opt.dataset.sku||'';
  }
}

function editBranchInv(id,qty,reorder){
  document.getElementById('biId').value=id;
  document.getElementById('biModalTitle').textContent='Edit Branch Inventory';
  document.getElementById('biQty').value=qty;
  document.getElementById('biReorder').value=reorder;
  openModal('branch-inv');
}

async function saveBranchInv(){
  const id=document.getElementById('biId').value||0;
  const params={
    action:'add_branch_inventory',
    id,
    branch_id:document.getElementById('biBranch').value,
    product_id:document.getElementById('biProduct').value||0,
    product_name:document.getElementById('biName').value.trim(),
    sku:document.getElementById('biSku').value.trim(),
    quantity:document.getElementById('biQty').value,
    reorder_level:document.getElementById('biReorder').value||10,
    unit_cost:document.getElementById('biCost').value||0,
  };
  if(id) { params.action='update_inventory_stock'; params.id=id; params.quantity=document.getElementById('biQty').value; params.reorder_level=document.getElementById('biReorder').value; }
  if(!params.branch_id&&!id){showToast('Select a branch','error');return;}
  if(!params.product_name&&!id){showToast('Product name required','error');return;}
  const d=await api(params,'POST');
  if(d.success){closeModal('branch-inv');showToast('Saved ✓');loadBranchInventory();}
  else showToast(d.message||'Error','error');
}

async function deleteBranchInv(id){
  if(!confirm('Remove this inventory item?')) return;
  const d=await api({action:'delete_branch_inventory',id},'POST');
  if(d.success){showToast('Removed.');loadBranchInventory();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  INVENTORY TRANSFER
// ═══════════════════════════════════════════
async function loadTransferPage(){
  populateBranchSelect('tfFrom',false);
  populateBranchSelect('tfTo',false);
}

async function loadBranchInvProducts(){
  const branchId=document.getElementById('tfFrom').value;
  if(!branchId) return;
  const d=await api({action:'get_branch_inventory',branch_id:branchId});
  if(!d.success) return;
  document.getElementById('tfProduct').innerHTML=`<option value="">Select product…</option>`+
    d.inventory.map(i=>`<option value="${i.product_id}" data-avail="${i.quantity}" data-name="${i.product_name}">${i.product_name} (Avail: ${i.quantity})</option>`).join('');
  document.getElementById('tfProduct').onchange=function(){
    const opt=this.options[this.selectedIndex];
    document.getElementById('tfAvail').value=opt.dataset.avail||'0';
  };
}

async function doTransfer(){
  const from_branch_id=document.getElementById('tfFrom').value;
  const to_branch_id=document.getElementById('tfTo').value;
  const pSel=document.getElementById('tfProduct');
  const product_id=pSel.value;
  const product_name=pSel.options[pSel.selectedIndex]?.dataset?.name||'';
  const qty=document.getElementById('tfQty').value;
  const reason=document.getElementById('tfReason').value.trim();
  if(!from_branch_id||!to_branch_id||!product_id||!qty){showToast('Fill all required fields','error');return;}
  const d=await api({action:'transfer_inventory',from_branch:from_branch_id,to_branch:to_branch_id,productId:product_id,productName:product_name,quantity:qty,reason},'POST');
  if(d.success){closeModal('transfer');showToast(d.message||'Transfer complete ✓');loadTransferLogs();loadBranchInvProducts();loadBranchInventory();}
  else showToast(d.message||'Error','error');
}

async function loadTransferLogs(){
  const d=await api({action:'get_transfer_logs'});
  if(!d.success) return;
  renderTable('transferLogsBody',d.logs||[],l=>`<tr>
    <td><span class="pill pill-orange">${l.from_branch}</span></td>
    <td><span class="pill pill-blue">${l.to_branch}</span></td>
    <td>${l.product_name}</td>
    <td style="font-weight:700;">${l.quantity}</td>
    <td>${l.requested_by_name}</td>
    <td>${fmtDate(l.created_at)}</td>
  </tr>`);
}

// ═══════════════════════════════════════════
//  TASKS
// ═══════════════════════════════════════════
async function loadTasks(){
  const branchId=document.getElementById('taskBranchFilter')?.value||'';
  const d=await api({action:'get_tasks',branch_id:branchId});
  if(!d.success) return;
  renderTable('tasksBody',d.tasks||[],t=>`<tr>
    <td><div class="tbl-name">${t.title}</div>${t.description?`<div class="tbl-sub">${t.description.substring(0,60)}…</div>`:''}</td>
    <td>${t.assigned_to_name||t.assigned_by_name||'—'}</td>
    <td>${t.branch_name?`<span class="pill pill-blue">${t.branch_name}</span>`:'—'}</td>
    <td>${pill(t.priority)}</td>
    <td>${t.due_date?fmtDate(t.due_date):'—'}</td>
    <td>${pill(t.status)}</td>
    <td>${actBtns(
      `<button class="act-btn" onclick="updateTaskStatus(${t.id},'in_progress')" title="Mark In Progress" style="color:var(--yellow)">▶</button>`,
      `<button class="act-btn" onclick="updateTaskStatus(${t.id},'completed')" title="Mark Done" style="color:var(--green)">✓</button>`,
      delBtn(`deleteTask(${t.id})`)
    )}</td>
  </tr>`);
}

async function loadTaskUsers(){
  const branchId=document.getElementById('taskBranch').value;
  const d=await api({action:'get_users_simple',branch_id:branchId});
  if(!d.success) return;
  document.getElementById('taskUser').innerHTML=`<option value="">Select employee…</option>`+
    d.users.map(u=>`<option value="${u.id}">${u.first_name} ${u.last_name} (${cap(u.role)})</option>`).join('');
}

async function saveTask(){
  const params={
    action:'add_task',
    branch_id:document.getElementById('taskBranch').value,
    assignedTo:document.getElementById('taskUser').value,
    title:document.getElementById('taskTitle').value.trim(),
    description:document.getElementById('taskDesc').value.trim(),
    priority:document.getElementById('taskPriority').value,
    due_date:document.getElementById('taskDue').value,
  };
  if(!params.branch_id||!params.assignedTo||!params.title){showToast('Fill required fields','error');return;}
  const d=await api(params,'POST');
  if(d.success){closeModal('task');showToast('Task assigned ✓');loadTasks();loadEmpDashboard();}
  else showToast(d.message||'Error','error');
}

async function updateTaskStatus(id,status){
  const d=await api({action:'update_task_status',id,status},'POST');
  if(d.success){showToast('Task updated ✓');loadTasks();}
  else showToast(d.message||'Error','error');
}

async function deleteTask(id){
  if(!confirm('Delete this task?')) return;
  const d=await api({action:'delete_task',id},'POST');
  if(d.success){showToast('Task deleted.');loadTasks();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  ANALYTICS
// ═══════════════════════════════════════════
async function loadAnalytics(){
  const d=await api({action:'get_analytics'});
  if(!d.success) return;
  const vals=document.querySelectorAll('#view-analytics .kpi-value');
  if(vals[0]) vals[0].textContent='₱'+Number(d.this_month||0).toLocaleString('en-PH',{maximumFractionDigits:0});
  if(vals[1]) vals[1].textContent='₱'+Number(d.this_quarter||0).toLocaleString('en-PH',{maximumFractionDigits:0});
  if(vals[2]) vals[2].textContent='₱'+Number(d.avg_order||0).toLocaleString('en-PH',{maximumFractionDigits:0});
  if(vals[3]) vals[3].textContent=(d.return_rate||0)+'%';
  if(d.weekly?.length) drawBarChart('analyticsBar',d.weekly.map(r=>({m:r.day,v:parseFloat(r.revenue)||0})),(i,len)=>{const h=Math.round(140+(i/len)*80);return `hsl(${h},70%,45%)`;});
  if(d.top_categories?.length){
    const total=d.top_categories.reduce((s,c)=>s+(parseInt(c.product_count)||0),0)||1;
    const colors=['var(--blue)','var(--green)','var(--orange)','var(--yellow)','var(--purple)'];
    document.getElementById('catProg').innerHTML=d.top_categories.map((c,i)=>`
      <div class="prog-item"><div class="prog-top"><span class="prog-lbl">${c.category}</span><span class="prog-pct">${c.product_count} products</span></div><div class="prog-track"><div class="prog-fill" style="width:${Math.round(c.product_count/total*100)}%;background:${colors[i]||'var(--blue)'}"></div></div></div>`).join('');
  }
  if(d.top_products?.length){
    renderTable('topProductsBody',d.top_products,p=>`<tr>
      <td><div class="tbl-name">${p.name}</div></td>
      <td>${p.category}</td>
      <td>${p.stock}</td>
      <td>${fmt(p.price)}</td>
      <td><span class="tbl-amount">${fmt(p.est_revenue)}</span></td>
    </tr>`);
  }
}

// ═══════════════════════════════════════════
//  BRANCH ANALYTICS
// ═══════════════════════════════════════════
async function loadBranchAnalytics(){
  const branchId=document.getElementById('baFilter')?.value||'';
  const d=await api({action:'get_branch_analytics',branch_id:branchId});
  if(!d.success) return;
  const perf=d.branch_performance||[];
  const colors=['var(--blue)','var(--green)','var(--orange)','var(--purple)','var(--cyan)'];
  document.getElementById('branchRevShare').innerHTML=perf.map((b,i)=>`
    <div class="prog-item">
      <div class="prog-top"><span class="prog-lbl">${b.name}${b.is_main?' ★':''}</span><span class="prog-pct">${fmt(b.revenue)} (${b.revenue_pct}%)</span></div>
      <div class="prog-track"><div class="prog-fill" style="width:${b.revenue_pct}%;background:${colors[i%colors.length]}"></div></div>
    </div>`).join('')||'<div style="color:var(--gray-400);padding:20px;text-align:center;font-size:13px;">No data</div>';
  renderTable('branchPerfBody',perf,b=>`<tr>
    <td><div class="tbl-name">${b.name}${b.is_main?' ★':''}</div><div class="tbl-sub">${b.city||''}</div></td>
    <td>${b.city||'—'}</td>
    <td>${b.orders||0}</td>
    <td><span class="tbl-amount">${fmt(b.revenue||0)}</span></td>
    <td>${fmt(b.avg_order||0)}</td>
    <td>${b.staff_count||0}</td>
    <td>${b.total_stock||0}</td>
    <td>${b.low_stock>0?`<span class="pill pill-orange">⚠ ${b.low_stock}</span>`:'<span class="pill pill-green">OK</span>'}</td>
    <td>${pill(b.status||'active')}</td>
  </tr>`);
  // Monthly bar chart — aggregate
  if(d.monthly_by_branch?.length){
    const months=[...new Set(d.monthly_by_branch.map(r=>r.month_label))];
    const totals=months.map(m=>{
      const rows=d.monthly_by_branch.filter(r=>r.month_label===m);
      return {m:m.replace(' ','\n'),v:rows.reduce((s,r)=>s+parseFloat(r.revenue||0),0)};
    });
    drawBarChart('branchMonthlyBar',totals,(i,len)=>{const h=Math.round(220+(i/len)*40);return `hsl(${h},65%,50%)`;});
  }
}

// ═══════════════════════════════════════════
//  USERS
// ═══════════════════════════════════════════
async function loadUsers(search='',status='',role=''){
  const d=await api({action:'get_users',search,status,role});
  if(!d.success) return;
  renderTable('usersBody',d.users||[],u=>`<tr>
  <td><span class="tbl-id">#${u.id}</span></td>
  <td><div class="tbl-name">${u.firstName || '—'} ${u.lastName || '—'}</div></td>
  <td>${u.email}</td>
  <td>${pill(u.role)}</td>
  <td>${u.branchName?`<span class="pill pill-blue">${u.branchName}</span>`:'—'}</td>
  <td>${fmtDate(u.created_at)}</td>
  <td>${pill(u.status||'active')}</td>
  <td>${actBtns(
    editBtn(`editUserModal(${u.id})`),
    `<button class="act-btn" onclick="toggleUserStatus(${u.id},'${u.status==='active'?'inactive':'active'}')" title="Toggle Status">🔄</button>`,
    IS_MAIN_ADMIN?`<button class="act-btn" onclick="openPasswordReset(${u.id},'${((u.firstName||'') + ' ' + (u.lastName||'')).replace(/'/g,"\\'")}')" title="Reset Password">🔑</button>`:'',
    IS_MAIN_ADMIN?delBtn(`deleteUser(${u.id})`):''
  )}</td>
</tr>`);
}

async function editUserModal(id){
  const d=await api({action:'get_users'});
  if(!d.success) return;
  const u=d.users.find(x=>x.id==id);
  if(!u){showToast('User not found','error');return;}
  document.getElementById('uId').value=u.id;
  document.getElementById('userModalTitle').textContent='Edit User';
  document.getElementById('uFirst').value=u.first_name;
  document.getElementById('uLast').value=u.last_name;
  document.getElementById('uEmail').value=u.email;
  document.getElementById('uRole').value=u.role;
  document.getElementById('uStatus').value=u.status||'active';
  document.getElementById('uPass').value='';
  populateBranchSelect('uBranch',false,u.branch_id||'');
  openModal('user');
}

async function saveUser(){
  const id = document.getElementById('uId').value.trim();
  const params = {
    action:       id ? 'update_user' : 'add_user',
    id: id || undefined,
    firstName:    document.getElementById('uFirst').value.trim(),   // ← was first_name
    lastName:     document.getElementById('uLast').value.trim(),    // ← was last_name
    email:        document.getElementById('uEmail').value.trim(),
    role:         document.getElementById('uRole').value,
    status:       document.getElementById('uStatus').value,
    branchId:     document.getElementById('uBranch').value || null,
    branch_id:    document.getElementById('uBranch').value || null,
    employeeType: document.getElementById('uEmpType').value,        // ← was employee_type
    position:     document.getElementById('uPosition').value.trim(),
    department:   document.getElementById('uDept').value.trim(),
  };
  if (!id) {
    params.password = document.getElementById('uPass').value;
    if (!params.password) { showToast('Password required for new users', 'error'); return; }
  }
  if (!params.firstName || !params.lastName || !params.email) {
    showToast('Name and email required', 'error'); return;
  }
  const d = await api(params, 'POST');
  if (d.success) {
    closeModal('user'); showToast('User saved ✓');
    loadUsers();
    loadEmployees();
    loadSubAdmins();
    loadBranchStaff();
    if (params.role === 'sub_admin') loadBranches();
  } else {
    showToast(d.message || 'Error', 'error');
  }
}

async function deleteUser(id){
  if(!confirm('Delete this user?')) return;
  const d=await api({action:'delete_user',id},'POST');
  if(d.success){showToast('User deleted.');loadUsers();}
  else showToast(d.message||'Error','error');
}

async function toggleUserStatus(id,newStatus){
  const d=await api({action:'toggle_user_status',id,status:newStatus},'POST');
  if(d.success){showToast('Status updated ✓');loadUsers();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  EMPLOYEE DASHBOARD
// ═══════════════════════════════════════════
async function loadEmpDashboard(){
  const d = await api({action:'get_employee_dashboard'});
  
  if (!d.success) {
    showToast('Dashboard load failed: ' + (d.message || 'Unknown error'), 'error');
    console.error('Employee dashboard error:', d);
    document.getElementById('empOpenTasks').textContent = '—';
    document.getElementById('empPendingOrders').textContent = '—';
    document.getElementById('empLowStock').textContent = '—';
    return;
  }

  // ✅ Update KPIs
  document.getElementById('empOpenTasks').textContent = (d.tasks || []).filter(t => t.status !== 'completed').length || '0';
  document.getElementById('empPendingOrders').textContent = (d.pending_orders || []).length || '0';
  document.getElementById('empLowStock').textContent = (d.low_stock_alerts || []).length || '0';

  // ✅ Render tasks table
  renderTable('empTasksBody', d.tasks || [], t => `<tr>
    <td><div class="tbl-name">${t.title || 'Untitled Task'}</div></td>
    <td>${pill(t.priority || 'medium')}</td>
    <td>${t.dueDate ? fmtDate(t.dueDate) : '—'}</td>
    <td>${pill(t.status || 'pending')}</td>
    <td>
      <button class="btn btn-success-soft btn-sm" onclick="updateTaskStatus('${t.id}', 'completed')">Mark Done</button>
    </td>
  </tr>`);

  // ✅ Render orders table (STAFF ONLY gets action buttons)
  renderTable('empOrdersBody', d.pending_orders || [], o => `<tr>
    <td><span class="tbl-id">${o.order_number}</span></td>
    <td>${o.customer}</td>
    <td>${fmt(o.grand_total)}</td>
    <td>${pill(o.status)}</td>
    ${IS_STAFF ? `<td>${actBtns(
      o.status === 'pending' ? `<button class="act-btn" onclick="acceptOrder('${o.id}')" title="Accept Order" style="color:var(--green);font-weight:700;">✅ Accept</button>` : '',
      `<button class="act-btn" onclick="openOrderStatus('${o.id}','${o.status}')" title="Update Status">🔄</button>`
    )}</td>` : ''}
  </tr>`);

  // ✅ Render inventory lookup table (default: branch low stock)
  if (d.low_stock_alerts && d.low_stock_alerts.length > 0) {
    renderTable('invLookupBody', d.low_stock_alerts, i => `<tr>
      <td><div class="tbl-name">${i.productName || '—'}</div></td>
      <td><span class="tbl-id">${i.sku || '—'}</span></td>
      <td>${i.category || '—'}</td>
      <td style="font-weight:700;color:var(--orange)">⚠ ${i.quantity || 0}</td>
      <td>${i.reorderLevel || 10}</td>
    </tr>`);
  }

  // ✅ Show branch info if available
  if (d.branch) {
    const branchInfo = document.createElement('div');
    branchInfo.className = 'card';
    branchInfo.style.marginBottom = '20px';
    branchInfo.innerHTML = `
      <div class="card-header">
        <div class="card-header-title">Your Branch</div>
      </div>
      <div class="card-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
        <div><strong>Name:</strong> ${d.branch.name}</div>
        <div><strong>City:</strong> ${d.branch.city || '—'}</div>
        <div><strong>Phone:</strong> ${d.branch.phone || '—'}</div>
        <div><strong>Email:</strong> ${d.branch.email || '—'}</div>
      </div>
    `;
    const empKpis = document.getElementById('empKpis');
    if (empKpis && empKpis.parentNode) {
      empKpis.parentNode.insertBefore(branchInfo, empKpis.nextSibling);
    }
  }
}

async function doInvLookup(){
  const q=document.getElementById('invLookupQ').value.trim();
  if(!q){showToast('Enter search term','error');return;}
  const d=await api({action:'inventory_lookup',q});
  if(!d.success) return;
  renderTable('invLookupBody',d.products||[],p=>`<tr>
    <td><div class="tbl-name">${p.name}</div></td>
    <td><span class="tbl-id">${p.sku||'—'}</span></td>
    <td>${p.category}</td>
    <td>${fmt(p.price)}</td>
    <td style="font-weight:700;${p.branch_stock<=0?'color:var(--red)':p.branch_stock<=5?'color:var(--yellow)':''}">${p.branch_stock}</td>
    <td>${p.total_stock}</td>
  </tr>`);
}

// ═══════════════════════════════════════════
//  PENDING APPROVALS
// ═══════════════════════════════════════════
async function loadPendingApprovals(){
  const d=await api({action:'get_pending_approvals'});
  if(!d.success) return;
  const products=d.products||[];
  const count=document.getElementById('pendingApprovalCount');
  if(count) count.textContent=products.length+' pending';
  const badge=document.getElementById('approvalBadge');
  if(badge) badge.textContent=products.length;
  renderTable('approvalsBody',products,p=>`<tr>
    <td><div class="tbl-name">${p.name}</div></td>
    <td><span class="tbl-id">${p.sku||'—'}</span></td>
    <td>${p.category}</td>
    <td><span class="tbl-amount">${fmt(p.price)}</span></td>
    <td>${p.stock}</td>
    <td>${p.branch_name?`<span class="pill pill-blue">${p.branch_name}</span>`:'—'}</td>
    <td>${p.created_by_name||'—'}</td>
    <td>${fmtDate(p.created_at)}</td>
    <td>${actBtns(
      `<button class="btn btn-success-soft btn-sm" onclick="approveProduct(${p.id})">✓ Approve</button>`,
      `<button class="btn btn-danger-soft btn-sm" onclick="rejectProduct(${p.id})">✗ Reject</button>`
    )}</td>
  </tr>`);
}

// ═══════════════════════════════════════════
//  BRANCH STAFF
// ═══════════════════════════════════════════
async function loadBranchStaff(search=''){
  const branchId=document.getElementById('staffBranchFilter')?.value||'';
  const d=await api({action:'get_branch_staff',branch_id:branchId});
  if(!d.success) return;
  const staff=d.staff||[];
  renderTable('branchStaffBody',staff,s=>`<tr>
    <td><div class="tbl-name">${s.name || '—'}</div></td>
    <td>${s.email || '—'}</td>
    <td>${pill(s.role)}</td>
    <td>${s.branch_name?`<span class="pill pill-blue">${s.branch_name}</span>`:'—'}</td>
    <td>${s.position||'—'}</td>
    <td>${s.department||'—'}</td>
    <td>${fmtDate(s.assigned_at)}</td>
    <td>${actBtns(
      IS_MAIN_ADMIN?`<button class="act-btn del" onclick="removeBranchStaff(${s.user_id},${s.branch_id})" title="Remove from Branch">🏢✗</button>`:'',
      delBtn(`deleteUser(${s.user_id})`)
    )}</td>
  </tr>`);
}

async function removeBranchStaff(userId,branchId){
  if(!confirm('Remove this staff member from the branch?')) return;
  const d=await api({action:'remove_branch_staff',user_id:userId,branch_id:branchId},'POST');
  if(d.success){showToast('Staff removed from branch ✓');loadBranchStaff();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  PASSWORD RESET
// ═══════════════════════════════════════════
function openPasswordReset(userId, userName){
  document.getElementById('pwUserId').value=userId;
  document.getElementById('pwUserName').value=userName;
  document.getElementById('pwNewPass').value='';
  openModal('pwreset');
}

async function doPasswordReset(){
  const id=document.getElementById('pwUserId').value;
  const pw=document.getElementById('pwNewPass').value;
  if(!pw||pw.length<6){showToast('Password must be at least 6 characters','error');return;}
  const d=await api({action:'reset_user_password',id,password:pw},'POST');
  if(d.success){closeModal('pwreset');showToast('Password reset successfully ✓');}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  SET MAIN BRANCH
// ═══════════════════════════════════════════
async function setMainBranch(id){
  if(!confirm('Set this as the Main Branch? The current main branch will be changed.')) return;
  const d=await api({action:'set_main_branch',id},'POST');
  if(d.success){showToast('Main branch updated ✓');loadBranches();populateBranchSelects();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  ORDER STATUS MODAL
// ═══════════════════════════════════════════
let _currentOrderId=null;
function openOrderStatus(id,currentStatus){
  if(!IS_STAFF){showToast('Only staff members can update order status.','error');return;}
  _currentOrderId=id;
  document.getElementById('osOrderId').value='#'+id;
  const sel=document.getElementById('osStatus');
  for(let i=0;i<sel.options.length;i++){if(sel.options[i].value===(currentStatus||'').toLowerCase()){sel.selectedIndex=i;break;}}
  openModal('orderstatus');
}
async function updateOrderStatus(){
  if(!IS_STAFF){showToast('Only staff members can update order status.','error');closeModal('orderstatus');return;}
  if(!_currentOrderId){closeModal('orderstatus');return;}
  const status=document.getElementById('osStatus').value.toLowerCase();
  const d=await api({action:'update_order_status',id:_currentOrderId,status},'POST');
  if(d.success){closeModal('orderstatus');showToast(`Order updated to "${status}" ✓`);loadOrders();loadPendingOrders();}
  else showToast(d.message||'Error','error');
}

// ═══════════════════════════════════════════
//  RIDERS (static)
// ═══════════════════════════════════════════
const ridersData=[
  {name:'Miguel Santos',zone:'North Luzon',vehicle:'🏍️',deliveries:142,rating:4.9,active:3,status:'Active'},
  {name:'Jose Reyes',zone:'Metro Manila',vehicle:'🏍️',deliveries:98,rating:4.7,active:2,status:'Active'},
  {name:'Carlos Garcia',zone:'South Luzon',vehicle:'🚐',deliveries:76,rating:4.8,active:1,status:'Active'},
  {name:'Ramon Cruz',zone:'Visayas',vehicle:'🏍️',deliveries:54,rating:4.6,active:0,status:'Inactive'},
  {name:'Eduardo Ramos',zone:'Mindanao',vehicle:'🏍️',deliveries:61,rating:4.8,active:1,status:'Active'},
];
function renderRiders(){
  document.getElementById('riderGrid').innerHTML=ridersData.map(r=>`
    <div class="rider-card">
      <div class="rider-head"><div class="rider-av">${r.name.charAt(0)}</div><div><div class="rider-name">${r.name}</div><div class="rider-sub">${r.vehicle} ${r.zone}</div></div><div style="margin-left:auto">${pill(r.status)}</div></div>
      <div class="rider-stats">
        <div><div class="rider-stat-val">${r.deliveries}</div><div class="rider-stat-lbl">Deliveries</div></div>
        <div><div class="rider-stat-val">${r.rating}⭐</div><div class="rider-stat-lbl">Rating</div></div>
        <div><div class="rider-stat-val">${r.active}</div><div class="rider-stat-lbl">Active</div></div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="openModal('assign')">Assign Order</button>
    </div>`).join('');
}
function saveAssignment(){closeModal('assign');showToast('Rider assigned ✓');}
function saveRider(){closeModal('rider');showToast('Rider added ✓');}

// ═══════════════════════════════════════════
//  BAR CHARTS
// ═══════════════════════════════════════════
function drawBarChart(containerId,data,colorFn){
  const el=document.getElementById(containerId);
  if(!el||!data.length) return;
  const max=Math.max(...data.map(d=>d.v),1);
  el.innerHTML=data.map((d,i)=>`
    <div class="bar-col">
      <div class="bar-val">₱${(d.v/1000).toFixed(0)}K</div>
      <div class="bar-fill" style="height:${Math.round((d.v/max)*140)}px;background:${colorFn(i,data.length)}"></div>
      <div class="bar-lbl">${d.m}</div>
    </div>`).join('');
}

// ═══════════════════════════════════════════
//  MODALS
// ═══════════════════════════════════════════
function openModal(n){document.getElementById('modal-'+n).classList.add('open');}
function closeModal(n){document.getElementById('modal-'+n).classList.remove('open');}

// ═══════════════════════════════════════════
//  FILTER & SEARCH
// ═══════════════════════════════════════════
function filterTable(id,q){
  q=q.toLowerCase();
  document.getElementById(id)?.querySelectorAll('tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});
}
function filterByStatus(id,val){
  document.getElementById(id)?.querySelectorAll('tr').forEach(r=>{r.style.display=(!val||r.textContent.toLowerCase().includes(val.toLowerCase()))?'':'none';});
}
function handleSearch(v){
  const active=document.querySelector('.view.active');
  if(!active) return;
  const name=active.id.replace('view-','');
  if(name==='orders'||name==='order-history') loadOrders(v);
  else if(name==='transactions') loadTransactions(v);
  else if(name==='products') loadProducts(v);
  else if(name==='inventory') loadInventory(v);
  else if(name==='users') loadUsers(v);
  else if(name==='employees') loadEmployees(v);
  else if(name==='branch-inventory') loadBranchInventory(v);
  else if(name==='delivery') filterTable('deliveryBody',v);
}

// ═══════════════════════════════════════════
//  EXPORT CSV
// ═══════════════════════════════════════════
function exportCSV(type){
  const maps={orders:{data:_ordersCache,fields:['order_number','customer_name','total_amount','created_at','status']},products:{data:_productsCache,fields:['sku','name','category','price','stock','status']}};
  const m=maps[type];if(!m||!m.data.length){showToast('No data to export','error');return;}
  const csv=[m.fields.join(','),...m.data.map(r=>m.fields.map(f=>`"${r[f]??''}"`).join(','))].join('\n');
  const a=document.createElement('a');
  a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
  a.download=type+'_export.csv';a.click();
  showToast('CSV exported ✓');
}

// ═══════════════════════════════════════════
//  TOAST
// ═══════════════════════════════════════════
function showToast(msg,type='success'){
  const t=document.getElementById('toast');
  document.getElementById('toastMsg').textContent=msg;
  t.className='toast t-'+type+' show';
  setTimeout(()=>t.className='toast',3200);
}

// ═══════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════
window.addEventListener('DOMContentLoaded',()=>{
  // Escape closes modals
  document.addEventListener('keydown',e=>{
    if(e.key==='Escape') document.querySelectorAll('.modal-bg.open').forEach(m=>m.classList.remove('open'));
  });

  // ── Event delegation for action buttons (rendered dynamically into tables) ──
  document.addEventListener('click', e => {
    const eyeBtn   = e.target.closest('.act-eye');
    const editBtn  = e.target.closest('.act-edit');
    const delBtn   = e.target.closest('.act-delete');

    if (eyeBtn) {
      const id = eyeBtn.dataset.id;
      if (id) viewOrderDetail(id);
    }
    if (editBtn) {
      const id = editBtn.dataset.id;
      if (id && typeof window[id] === 'function') window[id]();
      else if (id && id.includes('(')) { try { eval(id); } catch(_){} }
    }
    if (delBtn) {
      const id = delBtn.dataset.id;
      if (id && id.includes('(')) { try { eval(id); } catch(_){} }
    }
  });
});
</script>
<script type="module" src="admin_firestore.js"></script>
<script type="module">
import { auth, db } from "./firebase-config.js";
import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
import { doc, getDoc, collection, getDocs } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";
import { firestoreApi, watchPendingOrders, watchOverviewStats } from "./admin_firestore.js";

let _unsubOrders = null;
let _unsubOverview = null;

// Make firestoreApi available to classic scripts immediately
window._firestoreApi = firestoreApi;

const ADMIN_ROLES = ['admin','main_admin','sub_admin','manager','staff','rider'];

// \u2500\u2500 Bridge: set PHP session so admin_api.php auth gate passes \u2500\u2500
async function establishPhpSession(user, role, branchId, name) {
  try {
    const idToken = await user.getIdToken();
    const fd = new FormData();
    fd.append('id_token',  idToken);
    fd.append('role',      role);
    fd.append('branch_id', branchId || '');
    fd.append('uid',       user.uid);
    fd.append('name',      name);
    const res  = await fetch('firebase_auth_bridge.php', { method:'POST', body:fd, credentials:'include' });
    const data = await res.json();
    if (!data.success) console.warn('Auth bridge failed:', data.message);
    return data.success;
  } catch(e) {
    console.warn('Auth bridge error:', e);
    return false;
  }
}

onAuthStateChanged(auth, async (user) => {
  if (!user) { 
    window.location.href = "Home.php"; 
    return; 
  }
  
  // ✅ Load profile from Firestore
  const snap    = await getDoc(doc(db, "users", user.uid));
  const profile = snap.exists() ? snap.data() : {};
  const role    = profile.role || "customer";

  if (!ADMIN_ROLES.includes(role)) {
    window.location.href = "Home.php";
    return;
  }

  const name     = `${profile.firstName || ""} ${profile.lastName || ""}`.trim() || user.email;
  const initial  = name.charAt(0).toUpperCase();
  const branchId = profile.branchId || profile.branch_id || null;

  // ─── Populate admin name / initial ───
  document.querySelectorAll(".js-admin-name").forEach(el => el.textContent = name);
  document.querySelectorAll(".js-admin-initial").forEach(el => el.textContent = initial);
  document.querySelectorAll(".js-admin-role").forEach(el => el.textContent = role);

  // ─── Set JS role flags BEFORE any API / UI calls ───
  window.ADMIN_ROLE       = role;
  window.IS_MAIN_ADMIN    = ['admin','main_admin'].includes(role);
  window.IS_SUB_ADMIN     = role === 'sub_admin';
  window.IS_STAFF         = role === 'staff';
  window.IS_RIDER         = role === 'rider';
  window.IS_EMPLOYEE_ONLY = role === 'staff' || role === 'rider';
  
  IS_MAIN_ADMIN    = window.IS_MAIN_ADMIN;
  IS_SUB_ADMIN     = window.IS_SUB_ADMIN;
  IS_STAFF         = window.IS_STAFF;
  IS_RIDER         = window.IS_RIDER;
  IS_EMPLOYEE_ONLY = window.IS_EMPLOYEE_ONLY;
  SESSION_ROLE     = role;
  SESSION_BRANCH   = branchId || null;
  window.SESSION_BRANCH = branchId || null;

  // ─── Establish PHP session ───
  await establishPhpSession(user, role, branchId, name);

  // ─── Populate branch selects NOW ───
  populateBranchSelects();
  populateBranchSelect('taskBranchFilter', true);

  // ─── Load approval badge ───
  if (window.IS_MAIN_ADMIN) {
    api({action:'get_pending_approvals'}).then(d => {
      if (d.success) {
        const badge = document.getElementById('approvalBadge');
        if (badge) badge.textContent = (d.products||[]).length;
      }
    });
  }

  // ─── Show initial view ───
  if (window.IS_STAFF) {
  showView('pending-orders', null);  // Staff sees pending orders to process
} else if (window.IS_RIDER) {
  showView('emp-dashboard', null);   // Riders see their deliveries dashboard
} else {
  loadOverview();                     // Admins see overview
}

  // ─── Firestore KPI totals ───
  try {
    const [usersSnap, ordersSnap] = await Promise.all([
      getDocs(collection(db, "users")),
      getDocs(collection(db, "orders")),
    ]);
    const orders  = ordersSnap.docs.map(d => d.data());
    const revenue = orders
      .filter(o => o.status !== 'cancelled')
      .reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0);
    const fmt2 = n => '₱' + Number(n).toLocaleString('en-PH', {minimumFractionDigits:2});
    const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
    setEl('statUsers',   usersSnap.size);
    setEl('statOrders',  orders.length);
    setEl('statRevenue', fmt2(revenue));
    setEl('statPending', orders.filter(o => o.status === 'pending').length);
  } catch(e) { 
    console.warn('KPI totals error:', e); 
  }

  // ─── Logout handler ───
  document.querySelectorAll(".js-logout-btn").forEach(btn => {
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      await signOut(auth);
      window.location.href = "Home.php";
    });
  });

  if (_unsubOrders) _unsubOrders();
  if (_unsubOverview) _unsubOverview();
  if (window.IS_STAFF) {
    _unsubOrders = watchPendingOrders(() => {
      loadPendingOrders();
      loadOrders();
    });
  } else {
    _unsubOverview = watchOverviewStats((stats) => {
      if (stats?.success) loadOverview();
    });
  }

  document.body.style.visibility = "visible";
});

document.body.style.visibility = "hidden";
</script>
<script type="module" src="admin_api_bridge.js"></script>
</body>
</html>