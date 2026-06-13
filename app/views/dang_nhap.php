<?php
global $conn;
if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
$loi = $loi ?? '';
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
.auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
.auth-card{background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.1);padding:2.5rem 2rem;width:100%;max-width:420px;}
.auth-logo{text-align:center;margin-bottom:1.5rem;}
.auth-logo-icon{font-size:2.5rem;}
.auth-title{font-size:1.5rem;font-weight:900;color:#111;margin:.5rem 0 .3rem;}
.auth-sub{font-size:.85rem;color:#6b7280;}
.input-icon-wrap{position:relative;}
.input-icon-wrap .form-control{padding-left:2.6rem;}
.input-icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:1rem;}
.toggle-pass{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#9ca3af;cursor:pointer;padding:.25rem;}
.divider{display:flex;align-items:center;gap:.75rem;color:#d1d5db;font-size:.8rem;margin:.8rem 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e5e7eb;}
.social-btns{display:flex;gap:.5rem;}
.social-btn{flex:1;padding:.55rem;border-radius:10px;border:1.5px solid #e5e7eb;background:#fff;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.35rem;color:#374151;transition:all .15s;}
.social-btn:hover{background:#f9fafb;border-color:#d1d5db;}
.remember-row{display:flex;align-items:center;justify-content:space-between;margin:.25rem 0 1.2rem;}
.form-check-label{font-size:.85rem;color:#374151;cursor:pointer;}
.forgot-link{font-size:.82rem;color:#13547a;text-decoration:none;font-weight:600;}
.forgot-link:hover{text-decoration:underline;}
</style>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-icon">🎓</div>
      <div class="auth-title"><?= SITE_NAME ?></div>
      <div class="auth-sub">Đăng nhập để tiếp tục học</div>
    </div>

    <?php if ($loi): ?>
    <div class="alert alert-danger py-2 rounded-3 mb-3">
      <i class="bi bi-exclamation-circle me-1"></i><?= $loi ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> py-2 rounded-3 mb-3">
      <?= $_SESSION['flash']['message'] ?>
    </div>
    <?php unset($_SESSION['flash']); endif; ?>

    <form method="POST">
      <!-- Email -->
      <div class="mb-3">
        <label class="form-label fw-semibold small">Email</label>
        <div class="input-icon-wrap">
          <i class="bi bi-envelope input-icon"></i>
          <input type="email" name="email" class="form-control rounded-3"
                 placeholder="you@email.com"
                 value="<?= sanitize($_POST['email']??'') ?>" required autofocus>
        </div>
      </div>

      <!-- Password -->
      <div class="mb-2">
        <label class="form-label fw-semibold small">Mật khẩu</label>
        <div class="input-icon-wrap">
          <i class="bi bi-lock input-icon"></i>
          <input type="password" name="password" id="passInput" class="form-control rounded-3"
                 placeholder="••••••••" required style="padding-right:2.8rem">
          <button type="button" class="toggle-pass" onclick="togglePass()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <!-- Remember me + Quên mật khẩu -->
      <div class="remember-row">
        <div class="form-check">
          <input type="checkbox" name="remember_me" class="form-check-input" id="rememberMe" value="1">
          <label class="form-check-label" for="rememberMe">
            <i class="bi bi-clock-history me-1"></i>Ghi nhớ <?= COOKIE_REMEMBER_DAYS ?> ngày
          </label>
        </div>
        <a href="<?= BASE_URL ?>/quen-mat-khau" class="forgot-link">Quên mật khẩu?</a>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">
        <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
      </button>
    </form>

    <div class="divider">hoặc</div>

    <p class="text-center mb-0 small">
      Chưa có tài khoản?
      <a href="<?= BASE_URL ?>/dang-ky" class="text-primary fw-semibold">Đăng ký miễn phí</a>
    </p>
  </div>
</div>

<script>
function togglePass(){
  const i=document.getElementById('passInput'),e=document.getElementById('eyeIcon');
  if(i.type==='password'){i.type='text';e.className='bi bi-eye-slash';}
  else{i.type='password';e.className='bi bi-eye';}
}
</script>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
