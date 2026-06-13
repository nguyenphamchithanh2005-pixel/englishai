<?php
// Admin layout - admin_start.php (MVC version)
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'Admin – '.SITE_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="<?= BASE_URL ?>/public/assets/css/templatemo-topic-listing.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/public/assets/css/custom.css" rel="stylesheet">
<style>
.admin-sidebar{width:220px;min-height:100vh;background:#1a1a2e;}
.admin-sidebar .nav-link{color:rgba(255,255,255,.7);padding:.55rem 1rem;border-radius:.4rem;margin:.1rem .5rem;font-size:.9rem;}
.admin-sidebar .nav-link:hover,.admin-sidebar .nav-link.active{color:#fff;background:rgba(255,255,255,.12);}
</style>
</head>
<body>
<div class="d-flex">
<div class="admin-sidebar d-flex flex-column py-3">
  <div class="px-3 mb-4">
    <a href="<?= BASE_URL ?>" class="text-warning text-decoration-none fw-bold">
      <i class="bi bi-mortarboard-fill me-2"></i><?= SITE_NAME ?>
    </a>
    <div class="text-muted small mt-1 px-0">Admin Panel</div>
  </div>
  <nav class="flex-grow-1">
    <?php
    $reqUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $baseP  = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');
    if ($baseP) $reqUri = trim(substr($reqUri, strlen($baseP)), '/');
    $menu = [
      ['quan-tri',           'bi-speedometer2', 'Dashboard'],
      ['quan-tri/bai-hoc',   'bi-book',         'Bài học'],
      ['quan-tri/nguoi-dung','bi-people',        'Người dùng'],
      ['quan-tri/tu-vung',   'bi-translate',     'Từ vựng'],
    ];
    foreach($menu as [$url, $icon, $label]):
    ?>
    <a href="<?= BASE_URL ?>/<?= $url ?>" class="nav-link <?= $reqUri===$url?'active':'' ?>">
      <i class="bi <?= $icon ?> me-2"></i><?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="px-3 py-2 border-top border-secondary">
    <a href="<?= BASE_URL ?>/dang-xuat" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a>
  </div>
</div>
<div class="flex-grow-1 bg-light" style="min-height:100vh">
  <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
    <h5 class="fw-bold mb-0"><?= $pageTitle ?? '' ?></h5>
    <span class="text-muted small"><?= sanitize($_SESSION['fullname']??'') ?></span>
  </div>
  <?php if(isset($_SESSION['flash'])): ?>
  <div class="mx-4 mt-3">
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
      <?= $_SESSION['flash']['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash']); endif; ?>
  <div class="p-4">
