<?php
// View: admin/quan_ly_tu_vung.php
// Vars từ Admin controller: $pageTitle, $danhSach, $hd, $id, $editTV
include ROOT.'/app/views/layout/admin_start.php';
?>
<div class="d-flex justify-content-between mb-4">
  <h5 class="fw-bold mb-0"><i class="bi bi-translate text-warning me-2"></i>Quản lý từ vựng</h5>
  <a href="?hanh_dong=them" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Thêm từ mới</a>
</div>

<?php if ($hd==='them' || $hd==='sua'): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><?= $editTV ? 'Sửa từ: '.sanitize($editTV['word']) : 'Thêm từ vựng mới' ?></div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="luu" value="1">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label fw-semibold">Từ vựng *</label><input type="text" name="tu" class="form-control" value="<?= sanitize($editTV['word']??'') ?>" required></div>
        <div class="col-md-4"><label class="form-label fw-semibold">Phiên âm</label><input type="text" name="phat_am" class="form-control" value="<?= sanitize($editTV['pronunciation']??'') ?>" placeholder="/ˈwɜːd/"></div>
        <div class="col-md-4"><label class="form-label fw-semibold">Cấp độ</label>
          <select name="cap_do" class="form-select">
            <?php foreach(['basic'=>'🟢 Cơ bản','advanced'=>'🟡 Nâng cao','premium'=>'🔴 Cấp cao'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($editTV['level']??'basic')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6"><label class="form-label fw-semibold">Nghĩa tiếng Việt *</label><input type="text" name="nghia" class="form-control" value="<?= sanitize($editTV['translation']??'') ?>" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Chủ đề</label><input type="text" name="chu_de" class="form-control" value="<?= sanitize($editTV['category']??'') ?>" placeholder="Greetings, Academic..."></div>
        <div class="col-12"><label class="form-label fw-semibold">Định nghĩa tiếng Anh</label><input type="text" name="dinh_nghia" class="form-control" value="<?= sanitize($editTV['definition']??'') ?>"></div>
        <div class="col-12"><label class="form-label fw-semibold">Câu ví dụ</label><input type="text" name="vi_du" class="form-control" value="<?= sanitize($editTV['example']??'') ?>"></div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Lưu</button>
          <a href="<?= BASE_URL ?>/quan-tri/tu-vung" class="btn btn-outline-secondary">Hủy</a>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Từ vựng</th><th>Phiên âm</th><th>Nghĩa</th><th>Chủ đề</th><th>Cấp độ</th><th>Thao tác</th></tr></thead>
      <tbody>
        <?php foreach($danhSach as $v): ?>
        <tr>
          <td class="fw-bold"><?= sanitize($v['word']) ?></td>
          <td class="text-muted small"><?= sanitize($v['pronunciation']) ?></td>
          <td class="small"><?= sanitize($v['translation']) ?></td>
          <td><span class="badge bg-light text-dark"><?= sanitize($v['category']) ?></span></td>
          <td><?= getLevelBadge($v['level']) ?></td>
          <td>
            <a href="?hanh_dong=sua&id=<?= $v['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
            <a href="?hanh_dong=xoa&id=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa từ này?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT.'/app/views/layout/admin_end.php'; ?>
