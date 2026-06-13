<?php
// View: admin/quan_ly_nguoi_dung.php
// Vars từ Admin controller: $pageTitle, $danhSach
include ROOT.'/app/views/layout/admin_start.php';
$timKiem = sanitize($_GET['tim_kiem'] ?? '');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0"><i class="bi bi-people text-success me-2"></i>Quản lý người dùng</h5>
  <form method="GET" class="d-flex gap-2">
    <input type="text" name="tim_kiem" class="form-control form-control-sm" placeholder="Tìm kiếm..." value="<?= $timKiem ?>">
    <button class="btn btn-sm btn-primary">Tìm</button>
  </form>
</div>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr>
        <th>ID</th><th>Họ tên</th><th>Email</th><th>Gói học</th><th>Bài đã học</th><th>AI hôm nay</th><th>Ngày đăng ký</th><th>Thao tác</th>
      </tr></thead>
      <tbody>
        <?php foreach($danhSach as $u): ?>
        <tr>
          <td class="text-muted small">#<?= $u['id'] ?></td>
          <td><div class="d-flex align-items-center gap-2"><div class="avatar-sm"><?= strtoupper(substr($u['fullname'],0,1)) ?></div><span class="fw-semibold small"><?= sanitize($u['fullname']) ?></span></div></td>
          <td class="small text-muted"><?= sanitize($u['email']) ?></td>
          <td><?= getLevelBadge($u['membership']) ?></td>
          <td class="text-center"><?= $u['bai_xong'] ?? 0 ?></td>
          <td class="text-center"><?= $u['ai_messages_today'] ?? 0 ?></td>
          <td class="small text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <form method="POST" class="d-flex gap-1">
              <input type="hidden" name="uid" value="<?= $u['id'] ?>">
              <select name="goi" class="form-select form-select-sm" style="width:100px">
                <option value="basic"    <?= $u['membership']==='basic'?'selected':'' ?>>Cơ bản</option>
                <option value="advanced" <?= $u['membership']==='advanced'?'selected':'' ?>>Nâng cao</option>
                <option value="premium"  <?= $u['membership']==='premium'?'selected':'' ?>>Cấp cao</option>
              </select>
              <button name="doi_goi" class="btn btn-sm btn-warning"><i class="bi bi-save"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT.'/app/views/layout/admin_end.php'; ?>
