<?php
global $conn;
if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
$loi   = $loi ?? '';
$valid = $valid ?? false;
$token = $token ?? '';
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
.auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
.auth-card{background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.1);padding:2.5rem 2rem;width:100%;max-width:420px;}
.input-icon-wrap{position:relative;}
.input-icon-wrap .form-control{padding-left:2.6rem;padding-right:2.8rem;}
.input-icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9ca3af;}
.toggle-pass{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#9ca3af;cursor:pointer;}
</style>
<div class="auth-wrap">
  <div class="auth-card">
    <div style="text-align:center;margin-bottom:1.5rem">
      <div style="font-size:2.5rem"><?= $valid ? '🔒' : '⚠️' ?></div>
      <div style="font-size:1.4rem;font-weight:900;color:#111;margin:.5rem 0 .3rem">Đặt lại mật khẩu</div>
    </div>
    <?php if (!$valid): ?>
    <div class="alert alert-danger rounded-3">
      <i class="bi bi-exclamation-circle me-1"></i><?= $loi ?: 'Liên kết không hợp lệ hoặc đã hết hạn.' ?>
    </div>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/quen-mat-khau" class="btn btn-primary rounded-3 px-4">Yêu cầu link mới</a>
    </div>
    <?php else: ?>
    <?php if ($loi): ?>
    <div class="alert alert-danger py-2 rounded-3 mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= $loi ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold small">Mật khẩu mới</label>
        <div class="input-icon-wrap">
          <i class="bi bi-lock input-icon"></i>
          <input type="password" name="password" id="p1" class="form-control rounded-3" placeholder="Tối thiểu 6 ký tự" required minlength="6" autofocus>
          <button type="button" class="toggle-pass" onclick="t('p1','e1')"><i class="bi bi-eye" id="e1"></i></button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold small">Xác nhận mật khẩu</label>
        <div class="input-icon-wrap">
          <i class="bi bi-lock-fill input-icon"></i>
          <input type="password" name="password2" id="p2" class="form-control rounded-3" placeholder="Nhập lại mật khẩu" required minlength="6">
          <button type="button" class="toggle-pass" onclick="t('p2','e2')"><i class="bi bi-eye" id="e2"></i></button>
        </div>
      </div>
      <button type="submit" class="btn btn-success w-100 py-2 fw-bold rounded-3">
        <i class="bi bi-check-circle me-1"></i>Đặt lại mật khẩu
      </button>
    </form>
    <?php endif; ?>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/dang-nhap" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập</a>
    </div>
  </div>
</div>
<script>
function t(inp,ico){const i=document.getElementById(inp),e=document.getElementById(ico);if(i.type==='password'){i.type='text';e.className='bi bi-eye-slash';}else{i.type='password';e.className='bi bi-eye';}}
</script>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
