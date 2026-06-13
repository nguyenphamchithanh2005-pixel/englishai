<?php
global $conn;
requireLogin();
$pageTitle = 'Hồ sơ – ' . SITE_NAME;
$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fullname = sanitize($_POST['fullname']);
    $newpass  = $_POST['newpass'] ?? '';
    $curpass  = $_POST['curpass'] ?? '';
    if (strlen($fullname)<2) { $msg='<div class="alert alert-danger">Họ tên quá ngắn.</div>'; }
    elseif ($newpass && !password_verify($curpass,$user['password'])) { $msg='<div class="alert alert-danger">Mật khẩu hiện tại không đúng.</div>'; }
    else {
        if ($newpass) {
            $hash=password_hash($newpass,PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET fullname='".addslashes($fullname)."',password='$hash' WHERE id=$uid");
        } else {
            $conn->query("UPDATE users SET fullname='".addslashes($fullname)."' WHERE id=$uid");
        }
        $_SESSION['fullname']=$fullname;
        $msg='<div class="alert alert-success">Cập nhật thành công!</div>';
        $user=$conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    }
}

$stats=[
    'lessons'=>$conn->query("SELECT COUNT(*) c FROM user_progress WHERE user_id=$uid AND completed=1")->fetch_assoc()['c'],
    'vocab'  =>$conn->query("SELECT COUNT(*) c FROM user_vocabulary WHERE user_id=$uid AND status='learned'")->fetch_assoc()['c'],
    'score'  =>$conn->query("SELECT COALESCE(AVG(NULLIF(score,0)),0) c FROM user_progress WHERE user_id=$uid")->fetch_assoc()['c'],
];
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>
<div class="container py-4" style="max-width:800px">
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm text-center p-4">
        <div class="avatar-lg mx-auto mb-3"><?= strtoupper(substr($user['fullname'],0,1)) ?></div>
        <h5 class="fw-bold mb-1"><?= sanitize($user['fullname']) ?></h5>
        <p class="text-muted small"><?= sanitize($user['email']) ?></p>
        <?= getLevelBadge($user['membership']) ?>
        <hr>
        <div class="row g-2 text-center">
          <div class="col-4"><div class="fw-bold"><?= $stats['lessons'] ?></div><div class="text-muted" style="font-size:.75rem">Bài học</div></div>
          <div class="col-4"><div class="fw-bold"><?= $stats['vocab'] ?></div><div class="text-muted" style="font-size:.75rem">Từ thuộc</div></div>
          <div class="col-4"><div class="fw-bold"><?= round($stats['score']) ?>%</div><div class="text-muted" style="font-size:.75rem">TB điểm</div></div>
        </div>
        <hr>
        <a href="<?= BASE_URL ?>/nang-cap" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-gem me-1"></i>Nâng cấp gói học</a>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-bold mb-3">Chỉnh sửa hồ sơ</h5>
        <?= $msg ?>
        <form method="POST">
          <div class="mb-3"><label class="form-label fw-semibold">Họ và tên</label><input type="text" name="fullname" class="form-control" value="<?= sanitize($user['fullname']) ?>" required></div>
          <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled></div>
          <hr><h6 class="fw-bold mb-3">Đổi mật khẩu (để trống nếu không đổi)</h6>
          <div class="mb-3"><label class="form-label fw-semibold">Mật khẩu hiện tại</label><input type="password" name="curpass" class="form-control" placeholder="Nhập để xác nhận đổi mật khẩu"></div>
          <div class="mb-4"><label class="form-label fw-semibold">Mật khẩu mới</label><input type="password" name="newpass" class="form-control" placeholder="Để trống nếu không đổi" minlength="6"></div>
          <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i>Lưu thay đổi</button>
        </form>
      </div>
    </div>
  </div>
</div>
<style>.avatar-lg{width:72px;height:72px;border-radius:50%;background:var(--primary,#4361ee);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;}</style>
<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
