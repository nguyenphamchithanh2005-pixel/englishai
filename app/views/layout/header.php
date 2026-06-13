<?php
// includes/header.php – Dùng template TemplateMo 590
$notifCount  = isLoggedIn() ? getNotificationCount() : 0;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$membership  = getMembership();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?? SITE_NAME ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- TemplateMo CSS -->
  <link href="<?= BASE_URL ?>/public/assets/css/templatemo-topic-listing.css" rel="stylesheet">
  <!-- Custom EnglishAI CSS -->
  <link href="<?= BASE_URL ?>/public/assets/css/custom.css" rel="stylesheet">
</head>

<body id="top">
<main>

<!-- ── NAVBAR ── -->
<div class="sticky-wrapper">
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand text-white fw-bold" href="<?= BASE_URL ?>">
        <i class="bi bi-mortarboard-fill me-1" style="color:#80d0c7"></i>
        <span style="font-family:'Montserrat',sans-serif"><?= SITE_NAME ?></span>
      </a>

      <!-- Mobile: icon chuông + toggler -->
      <div class="d-lg-none ms-auto me-3 d-flex align-items-center gap-2">
        <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/thong-bao" class="text-white position-relative" style="font-size:1.2rem">
          <i class="bi bi-bell"></i>
          <?php if ($notifCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem"><?= $notifCount ?></span>
          <?php endif; ?>
        </a>
        <?php endif; ?>
      </div>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav ms-lg-4 me-lg-auto">
          <li class="nav-item">
            <a class="nav-link <?= $currentPage==='index'?'active':'' ?>" href="<?= BASE_URL ?>">Trang chủ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage==='lessons'?'active':'' ?>" href="<?= BASE_URL ?>/bai-hoc">Bài học</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage==='vocabulary'?'active':'' ?>" href="<?= BASE_URL ?>/tu-vung">Từ vựng</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= in_array($currentPage,['index','match','typing','wordle','duel'])?'active':'' ?>"
               href="#" data-bs-toggle="dropdown">
              🎮 Games
            </a>
            <ul class="dropdown-menu shadow" style="min-width:200px">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/tro-choi">
                <span class="me-2">🎯</span>Tất cả trò chơi
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/tro-choi/do-tu">
                🟩 Đoán Từ
              </a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/tro-choi/ghep-cap">
                🃏 Ghép Cặp
              </a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/tro-choi/go-nhanh">
                ⌨️ Gõ Nhanh
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/doi-khang">
                ⚔️ Đối Kháng 1v1
              </a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage==='ai-chat'?'active':'' ?>" href="<?= BASE_URL ?>/chat-ai">
              Chat AI <span class="badge ms-1" style="background:#80d0c7;color:#fff;font-size:.65rem">AI</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage==='pricing'?'active':'' ?>" href="<?= BASE_URL ?>/nang-cap">Nâng cấp</a>
          </li>
        </ul>

        <!-- Right side -->
        <div class="d-none d-lg-flex align-items-center gap-3">
          <?php if (isLoggedIn()): ?>
          <!-- Chuông thông báo -->
          <a href="<?= BASE_URL ?>/thong-bao" class="position-relative text-white" style="font-size:1.1rem;line-height:1">
            <i class="bi bi-bell"></i>
            <?php if ($notifCount > 0): ?>
              <span class="position-absolute badge bg-danger rounded-pill" style="font-size:.6rem;top:-6px;left:10px"><?= $notifCount ?></span>
            <?php endif; ?>
          </a>
          <!-- User dropdown -->
          <div class="dropdown">
            <a href="#" class="dropdown-toggle d-flex align-items-center gap-2 text-white text-decoration-none" data-bs-toggle="dropdown">
              <span class="avatar-sm"><?= strtoupper(substr($_SESSION['fullname'],0,1)) ?></span>
              <span style="font-size:.85rem;font-weight:600"><?= sanitize($_SESSION['fullname']) ?></span>
              <?php
              $lvColors = ['basic'=>'#00BFA6','advanced'=>'#F9A826','premium'=>'#536DFE'];
              $lvLabels = ['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'];
              $lv = $membership;
              ?>
              <span class="badge rounded-pill" style="background:<?= $lvColors[$lv]??'#ccc' ?>;font-size:.65rem">
                <?= $lvLabels[$lv]??$lv ?>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:200px">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/bang-dieu-khien"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/ho-so"><i class="bi bi-person me-2"></i>Hồ sơ</a></li>
              <?php if ($_SESSION['role'] === 'admin'): ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-primary" href="<?= BASE_URL ?>/quan-tri"><i class="bi bi-shield-check me-2"></i>Quản trị</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/dang-xuat"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
          </div>
          <?php else: ?>
          <a href="<?= BASE_URL ?>/dang-nhap" class="nav-link text-white" style="font-size:.85rem">Đăng nhập</a>
          <a href="<?= BASE_URL ?>/dang-ky" class="custom-btn" style="font-size:.82rem;padding:8px 18px">Đăng ký miễn phí</a>
          <?php endif; ?>
        </div>

        <!-- Mobile user links -->
        <?php if (isLoggedIn()): ?>
        <div class="d-lg-none border-top border-secondary mt-2 pt-2">
          <a class="nav-link" href="<?= BASE_URL ?>/bang-dieu-khien"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
          <a class="nav-link" href="<?= BASE_URL ?>/ho-so"><i class="bi bi-person me-2"></i>Hồ sơ</a>
          <a class="nav-link text-danger" href="<?= BASE_URL ?>/dang-xuat"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a>
        </div>
        <?php else: ?>
        <div class="d-lg-none mt-2">
          <a class="nav-link" href="<?= BASE_URL ?>/dang-nhap">Đăng nhập</a>
          <a class="nav-link" href="<?= BASE_URL ?>/dang-ky">Đăng ký miễn phí</a>
        </div>
        <?php endif; ?>
      </div><!-- /.navbar-collapse -->
    </div>
  </nav>
</div><!-- /.sticky-wrapper -->

<?php if (isset($_SESSION['flash'])): ?>
<div class="container mt-3">
  <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
    <?= $_SESSION['flash']['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php unset($_SESSION['flash']); endif; ?>
