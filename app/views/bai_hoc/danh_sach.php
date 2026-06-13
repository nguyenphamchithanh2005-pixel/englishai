<?php
global $conn;
$pageTitle = 'Bài học – ' . SITE_NAME;

$search   = sanitize($_GET['search'] ?? '');
$level    = sanitize($_GET['level'] ?? '');
$cat      = (int)($_GET['cat'] ?? 0);
$type     = sanitize($_GET['type'] ?? '');

$where = "WHERE l.is_active=1";
if ($search)  $where .= " AND l.title LIKE '%".addslashes($search)."%'";
if ($level)   $where .= " AND l.level='".addslashes($level)."'";
if ($cat)     $where .= " AND l.category_id=$cat";
if ($type)    $where .= " AND l.lesson_type='".addslashes($type)."'";

$lessons    = $conn->query("SELECT l.*,c.name cat_name FROM lessons l LEFT JOIN categories c ON l.category_id=c.id $where ORDER BY l.level,l.order_num,l.id");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Tiến độ người dùng
$progressMap = [];
if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $pr = $conn->query("SELECT lesson_id,completed,score FROM user_progress WHERE user_id=$uid");
    while ($r = $pr->fetch_assoc()) $progressMap[$r['lesson_id']] = $r;
}
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<div class="site-header" style="padding-top:80px;padding-bottom:40px">
  <div class="container">
    <h2 class="fw-bold text-white mb-1"><i class="bi bi-book me-2"></i>Kho bài học</h2>
    <p class="text-light mb-0">Học theo lộ trình từ Cơ bản đến Cấp cao</p>
  </div>
</div>

<div class="container py-4">
  <!-- Bộ lọc -->
  <div class="filter-bar p-3 mb-4 rounded-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="🔍 Tìm kiếm bài học..." value="<?= $search ?>">
      </div>
      <div class="col-md-2">
        <select name="level" class="form-select">
          <option value="">Tất cả cấp độ</option>
          <option value="basic" <?= $level==='basic'?'selected':'' ?>>🟢 Cơ bản</option>
          <option value="advanced" <?= $level==='advanced'?'selected':'' ?>>🟡 Nâng cao</option>
          <option value="premium" <?= $level==='premium'?'selected':'' ?>>🔴 Cấp cao</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="cat" class="form-select">
          <option value="">Tất cả danh mục</option>
          <?php
global $conn; $categories->data_seek(0); while ($c=$categories->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>" <?= $cat==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
          <?php
global $conn; endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select">
          <option value="">Loại bài</option>
          <option value="grammar" <?= $type==='grammar'?'selected':'' ?>>Ngữ pháp</option>
          <option value="reading" <?= $type==='reading'?'selected':'' ?>>Đọc hiểu</option>
          <option value="listening" <?= $type==='listening'?'selected':'' ?>>Luyện nghe</option>
          <option value="speaking" <?= $type==='speaking'?'selected':'' ?>>Giao tiếp</option>
          <option value="writing" <?= $type==='writing'?'selected':'' ?>>Viết</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button>
      </div>
    </form>
  </div>

  <!-- Level badges -->
  <?php
global $conn; if (!isLoggedIn() || !canAccessLevel('advanced')): ?>
  <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-info-circle-fill fs-4"></i>
    <div>Bài học <strong>Nâng cao</strong> và <strong>Cấp cao</strong> yêu cầu nâng cấp tài khoản.
    <a href="<?= BASE_URL ?>/nang-cap" class="alert-link ms-1">Xem các gói học →</a></div>
  </div>
  <?php
global $conn; endif; ?>

  <!-- Danh sách bài học -->
  <?php
global $conn;
  $count = 0;
  $currentLevel = '';
  $allLessons = [];
  while ($l = $lessons->fetch_assoc()) $allLessons[] = $l;
  if (empty($allLessons)):
  ?>
  <div class="text-center py-5">
    <i class="bi bi-search fs-1 text-muted"></i>
    <h5 class="mt-3 text-muted">Không tìm thấy bài học nào</h5>
    <a href="<?= BASE_URL ?>/bai-hoc" class="btn btn-outline-primary mt-2">Xem tất cả</a>
  </div>
  <?php
global $conn; else: foreach ($allLessons as $l):
    if ($l['level'] !== $currentLevel):
      if ($currentLevel) echo '</div></div>'; // close prev section
      $currentLevel = $l['level'];
      $levelInfo = ['basic'=>['🟢','Cơ bản','success'],'advanced'=>['🟡','Nâng cao','warning'],'premium'=>['🔴','Cấp cao','danger']];
      [$icon,$label,$color] = $levelInfo[$l['level']];
      $locked = !canAccessLevel($l['level']);
      echo "<div class='level-section mb-4'>
        <div class='level-section-header d-flex align-items-center gap-3 mb-3 p-3 rounded-3 bg-{$color} bg-opacity-10 border border-{$color} border-opacity-25'>
          <span class='fs-5'>$icon</span>
          <h5 class='fw-bold mb-0 text-{$color}'>Cấp độ: $label</h5>
          ".($locked?"<span class='badge bg-{$color} ms-auto'><i class='bi bi-lock-fill me-1'></i>Yêu cầu nâng cấp</span>":'')."
        </div>
        <div class='row g-3'>";
    endif;
    $prog = $progressMap[$l['id']] ?? null;
    $locked = !canAccessLevel($l['level']);
    $icons = ['grammar'=>'bi-diagram-3','reading'=>'bi-file-text','speaking'=>'bi-mic','listening'=>'bi-headphones','writing'=>'bi-pencil'];
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="lesson-card h-100 <?= $locked?'lesson-locked':'' ?>">
        <div class="lesson-card-header level-<?= $l['level'] ?> position-relative">
          <?php
global $conn; if ($locked): ?><div class="lock-overlay"><i class="bi bi-lock-fill fs-2"></i></div><?php endif; ?>
          <div class="d-flex justify-content-between">
            <?= getLevelBadge($l['level']) ?>
            <span class="badge bg-dark opacity-75"><?= $l['duration'] ?> phút</span>
          </div>
          <?php
global $conn; if ($prog && $prog['completed']): ?>
            <div class="completed-check"><i class="bi bi-check-circle-fill text-success"></i></div>
          <?php
global $conn; endif; ?>
          <div class="mt-2"><i class="bi <?= $icons[$l['lesson_type']]??'bi-book' ?> fs-2 text-white opacity-75"></i></div>
        </div>
        <div class="lesson-card-body p-3 d-flex flex-column">
          <span class="text-muted small mb-1"><?= sanitize($l['cat_name']) ?></span>
          <h6 class="fw-bold mb-2"><?= sanitize($l['title']) ?></h6>
          <?php
global $conn; if ($prog): ?>
            <div class="mb-2">
              <div class="progress" style="height:4px"><div class="progress-bar bg-success" style="width:<?= $prog['completed']?100:30 ?>%"></div></div>
              <?php
global $conn; if ($prog['completed']): ?><small class="text-success">✓ Điểm: <?= $prog['score'] ?>%</small><?php else: ?><small class="text-muted">Đang học...</small><?php endif; ?>
            </div>
          <?php
global $conn; endif; ?>
          <div class="mt-auto">
            <?php
global $conn; if ($locked): ?>
              <a href="<?= BASE_URL ?>/nang-cap" class="btn btn-sm btn-warning w-100 fw-bold"><i class="bi bi-lock me-1"></i>Nâng cấp để học</a>
            <?php
global $conn; elseif (!isLoggedIn()): ?>
              <a href="<?= BASE_URL ?>/dang-nhap" class="btn btn-sm btn-primary w-100">Đăng nhập để học</a>
            <?php
global $conn; else: ?>
              <a href="<?= BASE_URL ?>/bai-hoc/chi-tiet?id=<?= $l['id'] ?>" class="btn btn-sm btn-primary w-100">
                <?= ($prog&&$prog['completed'])?'Học lại':'Học ngay' ?> <i class="bi bi-arrow-right ms-1"></i>
              </a>
            <?php
global $conn; endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php
global $conn; endforeach;
    if ($currentLevel) echo '</div></div>';
  endif; ?>
</div>

<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
