<?php
// View: admin/quan_ly_bai_hoc.php
// Vars từ Admin controller: $pageTitle, $model, $danhMuc, $danhSach, $hd, $id, $editBH
include ROOT.'/app/views/layout/admin_start.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0"><i class="bi bi-book text-primary me-2"></i>Quản lý bài học</h5>
  <a href="?hanh_dong=them" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Thêm bài học</a>
</div>

<?php if ($hd === 'them' || $hd === 'sua'): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold">
    <?= $editBH ? 'Sửa bài học: '.sanitize($editBH['title']) : 'Thêm bài học mới' ?>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="luu" value="1">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Tiêu đề bài học *</label>
          <input type="text" name="tieu_de" class="form-control" value="<?= sanitize($editBH['title']??'') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Danh mục</label>
          <select name="danh_muc" class="form-select">
            <?php foreach($danhMuc as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($editBH['category_id']??0)==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Cấp độ</label>
          <select name="cap_do" class="form-select">
            <option value="basic"    <?= ($editBH['level']??'basic')==='basic'?'selected':''   ?>>🟢 Cơ bản</option>
            <option value="advanced" <?= ($editBH['level']??'')==='advanced'?'selected':''    ?>>🟡 Nâng cao</option>
            <option value="premium"  <?= ($editBH['level']??'')==='premium'?'selected':''     ?>>🔴 Cấp cao</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Loại bài</label>
          <select name="loai_bai" class="form-select">
            <?php foreach(['grammar'=>'Ngữ pháp','reading'=>'Đọc hiểu','listening'=>'Luyện nghe','speaking'=>'Giao tiếp','writing'=>'Viết'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($editBH['lesson_type']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Thời gian (phút)</label>
          <input type="number" name="thoi_gian" class="form-control" value="<?= $editBH['duration']??15 ?>" min="5" max="120">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check mb-2">
            <input type="checkbox" name="hien_thi" class="form-check-input" id="activeCheck" <?= ($editBH['is_active']??1)?'checked':'' ?>>
            <label class="form-check-label" for="activeCheck">Hiển thị</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Nội dung bài học (HTML)</label>
          <textarea name="noi_dung" class="form-control" rows="10" style="font-family:monospace"><?= htmlspecialchars($editBH['content']??'') ?></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Lưu bài học</button>
          <a href="<?= BASE_URL ?>/quan-tri/bai-hoc" class="btn btn-outline-secondary">Hủy</a>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr>
        <th>Tiêu đề</th><th>Danh mục</th><th>Cấp độ</th><th>Loại</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th>
      </tr></thead>
      <tbody>
        <?php foreach($danhSach as $l): ?>
        <tr>
          <td class="fw-semibold"><?= sanitize($l['title']) ?></td>
          <td class="small text-muted"><?= sanitize($l['ten_danh_muc']??'') ?></td>
          <td><?= getLevelBadge($l['level']) ?></td>
          <td><span class="badge bg-secondary"><?= $l['lesson_type'] ?></span></td>
          <td><?= $l['duration'] ?> phút</td>
          <td><?= $l['is_active']?'<span class="badge bg-success">Hiện</span>':'<span class="badge bg-secondary">Ẩn</span>' ?></td>
          <td>
            <a href="?hanh_dong=sua&id=<?= $l['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
            <a href="?hanh_dong=xoa&id=<?= $l['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa bài học này?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include ROOT.'/app/views/layout/admin_end.php'; ?>
