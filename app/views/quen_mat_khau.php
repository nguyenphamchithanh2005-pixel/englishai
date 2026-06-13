<?php
global $conn;
if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
$thongBao = $thongBao ?? '';
$loai = $loai ?? 'info';
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
.auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
.auth-card{background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.1);padding:2.5rem 2rem;width:100%;max-width:420px;}
.auth-logo{text-align:center;margin-bottom:1.5rem;}
.input-icon-wrap{position:relative;}
.input-icon-wrap .form-control{padding-left:2.6rem;}
.input-icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9ca3af;}
</style>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">
      <div style="font-size:2.5rem">🔑</div>
      <div style="font-size:1.4rem;font-weight:900;color:#111;margin:.5rem 0 .3rem">Quên mật khẩu</div>
      <div style="font-size:.85rem;color:#6b7280">Nhập email để nhận link đặt lại mật khẩu</div>
    </div>
    <?php if ($thongBao): ?>
    <div class="alert alert-<?= $loai ?> py-2 rounded-3 mb-3"><?= $thongBao ?></div>
    <?php endif; ?>
    <?php if ($loai !== 'success'): ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold small">Email đăng ký</label>
        <div class="input-icon-wrap">
          <i class="bi bi-envelope input-icon"></i>
          <input type="email" name="email" class="form-control rounded-3" placeholder="you@email.com" required autofocus>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">
        <i class="bi bi-send me-1"></i>Gửi link đặt lại
      </button>
    </form>
    <?php endif; ?>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/dang-nhap" class="text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
      </a>
    </div>
  </div>
</div>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
