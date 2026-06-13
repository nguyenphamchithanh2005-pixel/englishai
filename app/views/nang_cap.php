<?php
global $conn;
$pageTitle = 'Nâng cấp tài khoản – ' . SITE_NAME;

// Chuyển hướng sang trang thanh toán khi chọn gói trả phí
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<div class="site-header" style="padding-top:80px;padding-bottom:50px">
  <div class="container text-center">
    <h2 class="fw-bold text-white mb-2"><i class="bi bi-gem me-2 text-warning"></i>Chọn gói học phù hợp</h2>
    <p class="text-light">Học tiếng Anh hiệu quả hơn với các tính năng nâng cao</p>
    <?php
global $conn; if (isLoggedIn()): ?>
    <p class="text-warning">Gói hiện tại của bạn: <strong><?= getLevelLabel(getMembership()) ?></strong></p>
    <?php
global $conn; endif; ?>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4 justify-content-center">
    <?php
global $conn;
    $plans = [
      [
        'level' => 'basic',
        'name'  => 'Cơ bản',
        'price' => 'Miễn phí',
        'color' => 'success',
        'features' => [
          ['✅','Bài học cơ bản (Ngữ pháp, Giao tiếp)'],
          ['✅','Bài tập trắc nghiệm'],
          ['✅','Từ vựng cơ bản (200+ từ)'],
          ['✅','Chat AI: 5 tin nhắn/ngày'],
          ['✅','Theo dõi tiến độ cơ bản'],
          ['❌','Bài học nâng cao'],
          ['❌','Từ vựng IELTS/TOEIC'],
          ['❌','Chat AI không giới hạn'],
        ]
      ],
      [
        'level' => 'advanced',
        'name'  => 'Nâng cao',
        'price' => '199.000đ/tháng',
        'color' => 'warning',
        'popular' => true,
        'features' => [
          ['✅','Toàn bộ bài học Cơ bản'],
          ['✅','Bài học Nâng cao (50+ bài)'],
          ['✅','Từ vựng cơ bản + nâng cao'],
          ['✅','Chat AI: 30 tin nhắn/ngày'],
          ['✅','Báo cáo tiến độ chi tiết'],
          ['✅','Luyện viết với AI'],
          ['❌','Bài học Cấp cao (IELTS/TOEIC)'],
          ['❌','Chat AI không giới hạn'],
        ]
      ],
      [
        'level' => 'premium',
        'name'  => 'Cấp cao',
        'price' => '399.000đ/tháng',
        'color' => 'danger',
        'features' => [
          ['✅','Toàn bộ nội dung học (100+ bài)'],
          ['✅','Bài học IELTS, TOEIC, Business English'],
          ['✅','Từ vựng học thuật (500+ từ)'],
          ['✅','Chat AI KHÔNG GIỚI HẠN'],
          ['✅','Luyện viết, chấm điểm AI'],
          ['✅','Mock test IELTS/TOEIC'],
          ['✅','Báo cáo và phân tích nâng cao'],
          ['✅','Ưu tiên hỗ trợ'],
        ]
      ],
    ];
    foreach ($plans as $p):
    $current = isLoggedIn() && getMembership() === $p['level'];
    ?>
    <div class="col-md-4">
      <div class="pricing-card <?= !empty($p['popular'])?'pricing-popular':'' ?> h-100 position-relative">
        <?php
global $conn; if (!empty($p['popular'])): ?><div class="popular-badge">⭐ Phổ biến nhất</div><?php endif; ?>
        <?php
global $conn; if ($current): ?><div class="current-plan-badge">✓ Gói hiện tại</div><?php endif; ?>
        
        <div class="pricing-header bg-<?= $p['color'] ?> <?= $p['color']==='warning'?'text-dark':'text-white' ?> p-4 text-center">
          <h4 class="fw-bold mb-1"><?= $p['name'] ?></h4>
          <div class="display-5 fw-bold"><?= $p['price'] ?></div>
          <?php
global $conn; if ($p['level']!=='basic'): ?><div class="small opacity-75">Hủy bất kỳ lúc nào</div><?php endif; ?>
        </div>
        
        <div class="p-4">
          <ul class="list-unstyled mb-4">
            <?php
global $conn; foreach($p['features'] as [$icon,$feat]): ?>
            <li class="mb-2 d-flex gap-2">
              <span><?= $icon ?></span>
              <span class="<?= $icon==='❌'?'text-muted':'' ?>"><?= $feat ?></span>
            </li>
            <?php
global $conn; endforeach; ?>
          </ul>
          
          <?php
global $conn; if ($current): ?>
            <button class="btn btn-<?= $p['color'] ?> w-100 fw-bold" disabled>✓ Đang dùng</button>
          <?php
global $conn; elseif (!isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/dang-ky" class="btn btn-<?= $p['color'] ?> w-100 fw-bold">Đăng ký để bắt đầu</a>
          <?php
global $conn; else: ?>
            <?php if ($p['level']==='basic'): ?>
            <form method="POST" action="<?= BASE_URL ?>/nang-cap">
              <input type="hidden" name="goi" value="basic">
              <button name="ha_cap" class="btn btn-<?= $p['color'] ?> w-100 fw-bold py-2">
                Chuyển về Cơ bản
              </button>
            </form>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/thanh-toan?goi=<?= $p['level'] ?>"
               class="btn btn-<?= $p['color'] ?> w-100 fw-bold py-2 text-decoration-none d-block text-center">
              <i class="bi bi-credit-card me-1"></i> Nâng cấp ngay
            </a>
            <?php endif; ?>
          <?php
global $conn; endif; ?>
        </div>
      </div>
    </div>
    <?php
global $conn; endforeach; ?>
  </div>

  <!-- So sánh tính năng -->
  <div class="mt-5">
    <h4 class="text-center fw-bold mb-4">So sánh chi tiết các gói</h4>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>Tính năng</th>
            <th class="text-center">🟢 Cơ bản</th>
            <th class="text-center">🟡 Nâng cao</th>
            <th class="text-center">🔴 Cấp cao</th>
          </tr>
        </thead>
        <tbody>
          <?php
global $conn;
          $compare = [
            ['Số lượng bài học','30 bài','80 bài','100+ bài'],
            ['Chat AI/ngày','5 tin','30 tin','Không giới hạn'],
            ['Từ vựng','200 từ','350 từ','500+ từ'],
            ['Bài tập trắc nghiệm','✅','✅','✅'],
            ['Luyện Speaking với AI','❌','✅','✅'],
            ['Bài học IELTS/TOEIC','❌','❌','✅'],
            ['Mock Test','❌','❌','✅'],
            ['Báo cáo tiến độ','Cơ bản','Chi tiết','Nâng cao'],
          ];
          foreach ($compare as $row):
          ?>
          <tr>
            <td class="fw-semibold"><?= $row[0] ?></td>
            <td class="text-center"><?= $row[1] ?></td>
            <td class="text-center bg-warning bg-opacity-10"><?= $row[2] ?></td>
            <td class="text-center bg-danger bg-opacity-10"><?= $row[3] ?></td>
          </tr>
          <?php
global $conn; endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
