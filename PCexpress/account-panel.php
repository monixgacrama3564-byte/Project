<?php
/**
 * account-panel.php
 * Include in home.php:  <?php include 'account-panel.php'; ?>
 * Trigger from header:  onclick="openPanel('login')"
 */
?>

<div id="panelOverlay" onclick="closePanel()"></div>

<div id="accountPanel">

    <!-- LOGIN -->
    <div id="viewLogin" class="panel-view active">
        <div class="panel-header">
            <span>LOGIN</span>
            <button class="panel-close" onclick="closePanel()">&#10005;</button>
        </div>
        <div class="panel-body">
            <div id="loginMsg" class="panel-msg"></div>
            <div class="field-group">
                <input type="email" id="loginEmail" placeholder="Email *" class="panel-input" autocomplete="email">
            </div>
            <div class="field-group" style="position:relative;">
                <input type="password" id="loginPassword" placeholder="Password *" class="panel-input" autocomplete="current-password">
                <span onclick="togglePw('loginPassword','eyeLogin')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#aaa;" id="eyeLogin">👁</span>
            </div>
            <a href="#" class="panel-link" onclick="switchView('forgot');return false;">Forgot your password?</a>
            <button class="panel-btn" id="btnLogin" onclick="doLogin()">Sign In</button>
            <div class="panel-divider">or</div>
            <a href="#" class="panel-link" style="text-align:center;" onclick="switchView('register');return false;">New customer? Create your account</a>
        </div>
    </div>

    <!-- REGISTER -->
    <div id="viewRegister" class="panel-view">
        <div class="panel-header">
            <span>CREATE ACCOUNT</span>
            <button class="panel-close" onclick="closePanel()">&#10005;</button>
        </div>
        <div class="panel-body">
            <div id="registerMsg" class="panel-msg"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div class="field-group">
                    <input type="text" id="regFirst" placeholder="First Name *" class="panel-input">
                </div>
                <div class="field-group">
                    <input type="text" id="regLast" placeholder="Last Name *" class="panel-input">
                </div>
            </div>
            <div class="field-group">
                <input type="email" id="regEmail" placeholder="Email *" class="panel-input">
            </div>
            <div class="field-group" style="position:relative;">
                <input type="password" id="regPassword" placeholder="Password * (min. 6 chars)" class="panel-input">
                <span onclick="togglePw('regPassword','eyeReg')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#aaa;" id="eyeReg">👁</span>
            </div>
            <button class="panel-btn" id="btnRegister" onclick="doRegister()">Create Account</button>
            <a href="#" class="panel-link" style="text-align:center;margin-top:8px;" onclick="switchView('login');return false;">Already have an account? Sign in</a>
        </div>
    </div>

    <!-- FORGOT PASSWORD -->
    <div id="viewForgot" class="panel-view">
        <div class="panel-header">
            <span>RESET PASSWORD</span>
            <button class="panel-close" onclick="closePanel()">&#10005;</button>
        </div>
        <div class="panel-body">
            <div id="forgotMsg" class="panel-msg"></div>
            <p style="font-size:13px;color:#555;margin-bottom:16px;line-height:1.6;">Enter your email and we'll send you a link to reset your password.</p>
            <div class="field-group">
                <input type="email" id="forgotEmail" placeholder="Email address *" class="panel-input">
            </div>
            <button class="panel-btn" id="btnForgot" onclick="doForgot()">Send Reset Link</button>
            <a href="#" class="panel-link" style="text-align:center;margin-top:8px;" onclick="switchView('login');return false;">&#8592; Back to Login</a>
        </div>
    </div>

</div>

<style>
#panelOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;}
#panelOverlay.active{display:block;}
#accountPanel{position:fixed;top:0;right:-420px;width:380px;max-width:100vw;height:100%;background:#fff;z-index:1001;box-shadow:-4px 0 30px rgba(0,0,0,.18);transition:right .35s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;font-family:'Segoe UI',Arial,sans-serif;}
#accountPanel.open{right:0;}
.panel-view{display:none;flex-direction:column;height:100%;}
.panel-view.active{display:flex;}
.panel-header{display:flex;align-items:center;justify-content:space-between;padding:22px 24px 18px;border-bottom:1px solid #eee;font-size:16px;font-weight:700;letter-spacing:.08em;color:#111;}
.panel-close{background:none;border:none;font-size:18px;cursor:pointer;color:#555;padding:4px 8px;border-radius:4px;}
.panel-close:hover{background:#f0f0f0;}
.panel-body{padding:24px;display:flex;flex-direction:column;gap:14px;flex:1;overflow-y:auto;}
.field-group{display:flex;flex-direction:column;}
.panel-input{width:100%;padding:13px 14px;border:1.5px solid #ddd;border-radius:6px;font-size:14px;outline:none;transition:border-color .2s;box-sizing:border-box;}
.panel-input:focus{border-color:#1a44c2;box-shadow:0 0 0 3px rgba(26,68,194,.1);}
.panel-btn{width:100%;padding:14px;background:#1a44c2;color:#fff;border:none;border-radius:50px;font-size:15px;font-weight:700;cursor:pointer;transition:background .2s;}
.panel-btn:hover{background:#1535a0;}
.panel-btn:disabled{background:#9ab0da;cursor:not-allowed;}
.panel-link{font-size:13px;color:#1a44c2;text-decoration:none;display:block;}
.panel-link:hover{text-decoration:underline;}
.panel-msg{display:none;padding:11px 14px;border-radius:6px;font-size:13px;font-weight:600;}
.panel-msg.success{display:block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;}
.panel-msg.error{display:block;background:#fdecea;color:#c62828;border:1px solid #ef9a9a;}
.panel-divider{text-align:center;font-size:12px;color:#bbb;position:relative;}
.panel-divider::before,.panel-divider::after{content:'';position:absolute;top:50%;width:42%;height:1px;background:#eee;}
.panel-divider::before{left:0;} .panel-divider::after{right:0;}
</style>

<script>
/**
 * account-panel.php inline script — UI helpers ONLY.
 * All auth logic (doLogin, doRegister, doForgot, doLogout)
 * is handled by auth.js (Firebase). Do NOT duplicate them here.
 */
function openPanel(view){
    switchView(view||'login');
    document.getElementById('accountPanel').classList.add('open');
    document.getElementById('panelOverlay').classList.add('active');
    document.body.style.overflow='hidden';
}
function closePanel(){
    document.getElementById('accountPanel').classList.remove('open');
    document.getElementById('panelOverlay').classList.remove('active');
    document.body.style.overflow='';
}
function switchView(name){
    document.querySelectorAll('.panel-view').forEach(v=>v.classList.remove('active'));
    const target=document.getElementById('view'+name.charAt(0).toUpperCase()+name.slice(1));
    if(target) target.classList.add('active');
    document.querySelectorAll('.panel-msg').forEach(m=>{m.className='panel-msg';m.textContent='';});
}
function togglePw(inputId){
    const inp=document.getElementById(inputId);
    if(inp) inp.type=inp.type==='password'?'text':'password';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape') closePanel();});
</script>