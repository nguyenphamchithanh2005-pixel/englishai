<?php
global $conn;
$pageTitle = 'Đăng ký – ' . SITE_NAME;
if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname  = sanitize($_POST['fullname'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (strlen($fullname) < 2)   $errors[] = 'Họ tên phải có ít nhất 2 ký tự.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6)   $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    if ($password !== $password2) $errors[] = 'Xác nhận mật khẩu không khớp.';

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Email này đã được đăng ký.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname,email,password) VALUES (?,?,?)");
            $stmt->bind_param('sss', $fullname, $email, $hash);
            if ($stmt->execute()) {
                $uid = $conn->insert_id;
                $msg = 'Chào mừng bạn đến với ' . SITE_NAME . '! 🎉';
                $conn->query("INSERT INTO notifications (user_id,title,message,type) VALUES ($uid,'Chào mừng!','$msg','success')");
                $_SESSION['flash'] = ['type'=>'success','message'=>'Đăng ký thành công! Hãy đăng nhập để bắt đầu học.'];
                redirect(BASE_URL.'/dang-nhap');
            } else {
                $errors[] = 'Có lỗi xảy ra. Vui lòng thử lại.';
            }
        }
    }
}
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<div class="auth-page">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="auth-card">
          <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>" class="text-decoration-none">
              <i class="bi bi-mortarboard-fill fs-1 text-warning"></i>
              <h4 class="fw-bold mt-2 mb-0"><?= SITE_NAME ?></h4>
            </a>
            <p class="text-muted mt-1">Tạo tài khoản học tiếng Anh miễn phí</p>
          </div>

          <?php
global $conn; if ($errors): ?>
          <div class="alert alert-danger py-2">
            <?php
global $conn; foreach($errors as $e): ?><div><i class="bi bi-x-circle me-1"></i><?= $e ?></div><?php endforeach; ?>
          </div>
          <?php
global $conn; endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Họ và tên</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" value="<?= sanitize($_POST['fullname']??'') ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control" placeholder="you@email.com" value="<?= sanitize($_POST['email']??'') ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Mật khẩu</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                <input type="password" name="password2" class="form-control" placeholder="Nhập lại mật khẩu" required>
              </div>
            </div>
            <div class="mb-3">
              <div class="free-plan-note p-3 rounded">
                <i class="bi bi-gift-fill text-success me-2"></i>
                <strong>Gói Cơ bản – Hoàn toàn miễn phí!</strong>
                <div class="small text-muted mt-1">Bài học cơ bản · 5 tin AI/ngày · Từ vựng cơ bản</div>
              </div>
            </div>
            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
              <i class="bi bi-person-plus me-1"></i>Tạo tài khoản miễn phí
            </button>
          </form>

          <div class="divider my-3"><span>hoặc</span></div>
          <p class="text-center mb-0">Đã có tài khoản? <a href="<?= BASE_URL ?>/dang-nhap" class="text-primary fw-semibold">Đăng nhập</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
