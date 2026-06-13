<?php
// View: admin/tong_quan.php
// Vars từ Admin controller: $pageTitle, $thongKeBaiHoc, $thongKeND, $tongTuVung, $tongChat, $nguoiMoiNhat
global $conn;
$topLessons = $conn->query("SELECT l.title,l.level,COUNT(up.id) views FROM lessons l LEFT JOIN user_progress up ON l.id=up.lesson_id GROUP BY l.id ORDER BY views DESC LIMIT 5");

include ROOT.'/app/views/layout/admin_start.php';

$stats = [
    'users'    => $thongKeND['tong'] ?? 0,
    'lessons'  => $thongKeBaiHoc['tong'] ?? 0,
    'vocab'    => $tongTuVung ?? 0,
    'chats'    => $tongChat ?? 0,
    'basic'    => $thongKeND['co_ban'] ?? 0,
    'advanced' => $thongKeND['nang_cao'] ?? 0,
    'premium'  => $thongKeND['cap_cao'] ?? 0,
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard</h5>
  <span class="text-muted small">Xin chào, <?= sanitize($_SESSION['fullname']??'') ?></span>
</div>

<!-- Thống kê -->
<div class="row g-3 mb-4">
  <?php
  $boxes = [
    ['bi-people-fill','primary','Người dùng',$stats['users']],
    ['bi-book-fill','success','Bài học',$stats['lessons']],
    ['bi-translate','warning','Từ vựng',$stats['vocab']],
    ['bi-chat-dots-fill','info','Chat AI',$stats['chats']],
  ];
  foreach ($boxes as [$icon,$color,$label,$val]):
  ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded p-2 bg-<?= $color ?> bg-opacity-10">
          <i class="bi <?= $icon ?> fs-4 text-<?= $color ?>"></i>
        </div>
        <div>
          <div class="fw-bold fs-4"><?= number_format($val) ?></div>
          <div class="text-muted small"><?= $label ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Phân bố gói học + người dùng mới + bài học phổ biến -->
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>Phân bố gói học</div>
      <div class="card-body">
        <?php
        $total = max(1,$stats['users']);
        $dist = [['Cơ bản',$stats['basic'],'success'],['Nâng cao',$stats['advanced'],'warning'],['Cấp cao',$stats['premium'],'danger']];
        foreach ($dist as [$name,$cnt,$color]):
          $pct = round($cnt/$total*100);
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="small fw-semibold"><?= $name ?></span>
            <span class="small"><?= $cnt ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress" style="height:8px">
            <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-people me-2 text-success"></i>Người dùng mới</span>
        <a href="<?= BASE_URL ?>/quan-tri/nguoi-dung" class="small text-primary">Xem tất cả</a>
      </div>
      <div class="card-body p-0">
        <?php foreach($nguoiMoiNhat as $u): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <div class="avatar-sm"><?= strtoupper(substr($u['fullname'],0,1)) ?></div>
          <div class="flex-grow-1 overflow-hidden">
            <div class="small fw-semibold text-truncate"><?= sanitize($u['fullname']) ?></div>
            <div class="text-muted" style="font-size:.72rem"><?= sanitize($u['email']) ?></div>
          </div>
          <?= getLevelBadge($u['membership']) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-fire me-2 text-danger"></i>Bài học phổ biến</span>
        <a href="<?= BASE_URL ?>/quan-tri/bai-hoc" class="small text-primary">Quản lý</a>
      </div>
      <div class="card-body p-0">
        <?php while ($l=$topLessons->fetch_assoc()): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <div class="flex-grow-1 overflow-hidden">
            <div class="small fw-semibold text-truncate"><?= sanitize($l['title']) ?></div>
            <?= getLevelBadge($l['level']) ?>
          </div>
          <span class="badge bg-light text-dark"><?= $l['views'] ?> lượt</span>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</div>

<!-- Actions nhanh -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-warning"></i>Thao tác nhanh</div>
  <div class="card-body d-flex flex-wrap gap-2">
    <a href="<?= BASE_URL ?>/quan-tri/bai-hoc?hanh_dong=them" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Thêm bài học</a>
    <a href="<?= BASE_URL ?>/quan-tri/tu-vung?hanh_dong=them" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Thêm từ vựng</a>
    <a href="<?= BASE_URL ?>/quan-tri/nguoi-dung" class="btn btn-info"><i class="bi bi-people me-1"></i>Quản lý người dùng</a>
    <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary" target="_blank"><i class="bi bi-eye me-1"></i>Xem trang học viên</a>
  </div>
</div>

<?php include ROOT.'/app/views/layout/admin_end.php'; ?>
