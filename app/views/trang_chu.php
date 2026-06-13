<?php
global $conn;

$totalLessons = $tongBaiHoc ?? $conn->query("SELECT COUNT(*) c FROM lessons WHERE is_active=1")->fetch_assoc()['c'];
$totalVocab   = $tongTuVung  ?? $conn->query("SELECT COUNT(*) c FROM vocabulary")->fetch_assoc()['c'];
$totalUsers   = $tongNguoiDung ?? $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'];

$featuredLessons = $conn->query("
  SELECT l.*, c.name cat_name FROM lessons l
  LEFT JOIN categories c ON l.category_id=c.id
  WHERE l.is_active=1
  ORDER BY l.id DESC LIMIT 6
");
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>

<!-- ── HERO ── -->
<section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-12 mx-auto text-center">
        <span class="badge mb-3 px-3 py-2" style="background:#80d0c7;color:#fff;font-size:.9rem">
          🤖 Tích hợp AI thông minh
        </span>
        <h1 class="text-white">Học tiếng Anh <br>cùng <span style="color:#80d0c7">Trí tuệ nhân tạo</span></h1>
        <h6 class="text-center mb-4">Lộ trình cá nhân · Bài học đa dạng · Luyện tập 24/7 với AI</h6>

        <form method="GET" action="<?= BASE_URL ?>/bai-hoc" class="custom-form mt-4 pt-2 mb-4">
          <div class="input-group input-group-lg">
            <span class="input-group-text bi-search" id="hero-search"></span>
            <input name="tim_kiem" type="search" class="form-control"
                   placeholder="Tìm bài học: Ngữ pháp, Từ vựng, IELTS, Giao tiếp..." aria-label="Search">
            <button type="submit" class="form-control">Tìm kiếm</button>
          </div>
        </form>

        <div class="row justify-content-center g-3 mt-2">
          <div class="col-auto">
            <div class="text-white text-center px-3">
              <div class="fw-bold fs-3" style="color:#80d0c7"><?= number_format($totalLessons) ?>+</div>
              <div style="font-size:.85rem">Bài học</div>
            </div>
          </div>
          <div class="col-auto">
            <div class="text-white text-center px-3" style="border-left:1px solid rgba(255,255,255,.25)">
              <div class="fw-bold fs-3" style="color:#80d0c7"><?= number_format($totalVocab) ?>+</div>
              <div style="font-size:.85rem">Từ vựng</div>
            </div>
          </div>
          <div class="col-auto">
            <div class="text-white text-center px-3" style="border-left:1px solid rgba(255,255,255,.25)">
              <div class="fw-bold fs-3" style="color:#80d0c7"><?= number_format($totalUsers) ?>+</div>
              <div style="font-size:.85rem">Học viên</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURED LESSONS ── -->
<section class="featured-section">
  <div class="container">
    <div class="row justify-content-center">
      <?php $fl = $featuredLessons->fetch_assoc(); if ($fl): ?>
      <div class="col-lg-4 col-12 mb-4 mb-lg-0">
        <div class="custom-block bg-white shadow-lg">
          <a href="<?= (canAccessLevel($fl['level']) && isLoggedIn()) ? BASE_URL.'/bai-hoc/chi-tiet?id='.$fl['id'] : BASE_URL.'/bai-hoc' ?>">
            <div class="d-flex">
              <div>
                <h5 class="mb-2"><?= sanitize($fl['title']) ?></h5>
                <p class="mb-0"><?= sanitize($fl['cat_name']) ?> · <?= $fl['duration'] ?> phút</p>
              </div>
              <?php
              $lvColor=['basic'=>'#00BFA6','advanced'=>'#F9A826','premium'=>'#536DFE'];
              $lvLabel=['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'];
              ?>
              <span class="badge rounded-pill ms-auto align-self-start"
                    style="background:<?= $lvColor[$fl['level']]??'#ccc' ?>">
                <?= $lvLabel[$fl['level']]??$fl['level'] ?>
              </span>
            </div>
            <?php
            $topicImgs = [
              'grammar'  => 'undraw_Educator_re_ju47.png',
              'reading'  => 'undraw_Redesign_feedback_re_jvm0.png',
              'speaking' => 'undraw_Group_video_re_btu7.png',
              'listening'=> 'undraw_Podcast_audience_re_4i5q.png',
              'writing'  => 'undraw_Compose_music_re_wpiw.png',
            ];
            $img = $topicImgs[$fl['lesson_type']] ?? 'undraw_Graduation_re_gthn.png';
            ?>
            <img src="<?= BASE_URL ?>/public/assets/images/topics/<?= $img ?>"
                 class="custom-block-image img-fluid" alt="">
          </a>
        </div>
      </div>
      <?php endif; ?>

      <div class="col-lg-6 col-12">
        <div class="custom-block custom-block-overlay">
          <div class="d-flex flex-column h-100">
            <img src="<?= BASE_URL ?>/public/assets/images/businesswoman-using-tablet-analysis.jpg"
                 class="custom-block-image img-fluid" alt="">
            <div class="custom-block-overlay-text d-flex">
              <div>
                <h5 class="text-white mb-2">🤖 Chat AI 24/7</h5>
                <p class="text-white" style="font-size:.95rem">
                  Luyện tập với AI thông minh: sửa ngữ pháp, luyện hội thoại, ôn thi IELTS/TOEIC ngay lập tức!
                </p>
                <a href="<?= isLoggedIn() ? BASE_URL.'/chat-ai' : BASE_URL.'/dang-ky' ?>"
                   class="btn custom-btn mt-2">Bắt đầu ngay</a>
              </div>
              <span class="badge rounded-pill ms-auto align-self-start" style="background:#536DFE">AI</span>
            </div>
            <div class="social-share d-flex align-items-center">
              <p class="text-white me-3 mb-0" style="font-size:.85rem">Chia sẻ:</p>
              <ul class="social-icon mb-0">
                <li class="social-icon-item"><a href="#" class="social-icon-link bi-facebook"></a></li>
                <li class="social-icon-item"><a href="#" class="social-icon-link bi-twitter"></a></li>
              </ul>
              <a href="<?= isLoggedIn() ? BASE_URL.'/chat-ai' : BASE_URL.'/dang-ky' ?>"
                 class="custom-icon bi-bookmark ms-auto"></a>
            </div>
            <div class="section-overlay"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── BROWSE TOPICS / BÀI HỌC THEO DANH MỤC ── -->
<section class="explore-section section-padding" id="section_2">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <h2 class="mb-2">Khám phá Bài học</h2>
        <p class="mb-4">Học theo chủ đề, phân cấp rõ ràng từ Cơ bản đến Cấp cao</p>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <ul class="nav nav-tabs justify-content-center" id="lessonTabs" role="tablist">
        <?php
        $tabData = [
          ['all','Tất cả','active'],
          ['basic','🟢 Cơ bản',''],
          ['advanced','🟡 Nâng cao',''],
          ['premium','🔴 Cấp cao',''],
        ];
        foreach ($tabData as [$tid,$tlabel,$tactive]):
        ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $tactive ?>" id="tab-<?= $tid ?>"
                  data-bs-toggle="tab" data-bs-target="#pane-<?= $tid ?>"
                  type="button" role="tab"><?= $tlabel ?></button>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="container mt-4">
    <div class="tab-content" id="lessonTabContent">
      <?php
      $topicImgs = ['grammar'=>'undraw_Educator_re_ju47.png','reading'=>'undraw_Redesign_feedback_re_jvm0.png','speaking'=>'undraw_Group_video_re_btu7.png','listening'=>'undraw_Podcast_audience_re_4i5q.png','writing'=>'undraw_Compose_music_re_wpiw.png'];
      $lvColor=['basic'=>'#00BFA6','advanced'=>'#F9A826','premium'=>'#536DFE'];
      $lvLabel=['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'];
      foreach ([['all',''],['basic','basic'],['advanced','advanced'],['premium','premium']] as [$tid,$lv]):
        $w = $lv ? "AND l.level='$lv'" : '';
        $tabLessons = $conn->query("SELECT l.*,c.name cat_name FROM lessons l LEFT JOIN categories c ON l.category_id=c.id WHERE l.is_active=1 $w ORDER BY l.level,l.id DESC LIMIT 6");
        $isActive = $tid==='all' ? 'show active' : '';
      ?>
      <div class="tab-pane fade <?= $isActive ?>" id="pane-<?= $tid ?>" role="tabpanel" tabindex="0">
        <div class="row g-4">
          <?php while ($l = $tabLessons->fetch_assoc()):
            $img = $topicImgs[$l['lesson_type']] ?? 'undraw_Graduation_re_gthn.png';
            $locked = !canAccessLevel($l['level']);
          ?>
          <div class="col-lg-4 col-md-6 col-12">
            <div class="custom-block bg-white shadow-lg <?= $locked?'opacity-75':'' ?>">
              <a href="<?= (!$locked && isLoggedIn()) ? BASE_URL.'/bai-hoc/chi-tiet?id='.$l['id'] : ($locked ? BASE_URL.'/nang-cap' : BASE_URL.'/dang-nhap') ?>">
                <div class="d-flex align-items-start">
                  <div class="flex-grow-1">
                    <h5 class="mb-1" style="font-size:1rem"><?= sanitize($l['title']) ?></h5>
                    <p class="mb-0" style="font-size:.85rem"><?= sanitize($l['cat_name']) ?> · <?= $l['duration'] ?> phút</p>
                  </div>
                  <span class="badge rounded-pill ms-2 flex-shrink-0"
                        style="background:<?= $lvColor[$l['level']]??'#ccc' ?>">
                    <?= $lvLabel[$l['level']]??$l['level'] ?>
                  </span>
                </div>
                <img src="<?= BASE_URL ?>/public/assets/images/topics/<?= $img ?>"
                     class="custom-block-image img-fluid" alt=""
                     style="<?= $locked?'filter:blur(2px)':'' ?>">
                <?php if ($locked): ?>
                <div class="text-center mt-2" style="font-size:.82rem;color:#536DFE">
                  <i class="bi bi-lock me-1"></i>Nâng cấp để mở khóa
                </div>
                <?php endif; ?>
              </a>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
          <a href="<?= BASE_URL ?>/bai-hoc<?= $lv?"?cap_do=$lv":'' ?>" class="btn custom-btn">
            Xem tất cả bài học <?= $lvLabel[$lv]??'' ?> <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="timeline-section section-padding" id="section_3"
         style="background-image:url('<?= BASE_URL ?>/public/assets/images/colleagues-working-cozy-office-medium-shot.jpg')">
  <div class="section-overlay"></div>
  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <h2 class="text-white mb-4">Cách học hiệu quả nhất</h2>
      </div>
      <div class="col-lg-10 col-12 mx-auto">
        <div class="timeline-container">
          <ul class="vertical-scrollable-timeline" id="vertical-scrollable-timeline">
            <div class="list-progress"><div class="inner"></div></div>
            <li>
              <h4 class="text-white mb-3">1. Chọn bài học phù hợp trình độ</h4>
              <p class="text-white">Hệ thống phân 3 cấp độ: Cơ bản, Nâng cao và Cấp cao. Học từ nền tảng ngữ pháp, từ vựng đến kỹ năng IELTS/TOEIC chuyên sâu.</p>
              <div class="icon-holder"><i class="bi-book"></i></div>
            </li>
            <li>
              <h4 class="text-white mb-3">2. Luyện tập với bài tập tương tác</h4>
              <p class="text-white">Sau mỗi bài học là bộ câu hỏi trắc nghiệm thông minh. Hệ thống chấm điểm và giải thích ngay lập tức để bạn hiểu sâu hơn.</p>
              <div class="icon-holder"><i class="bi-pencil-square"></i></div>
            </li>
            <li>
              <h4 class="text-white mb-3">3. Chat với AI để luyện tập thêm</h4>
              <p class="text-white">Hỏi AI về bất kỳ điểm ngữ pháp nào, luyện hội thoại, kiểm tra bài viết 24/7. AI sẽ giải thích bằng tiếng Việt dễ hiểu.</p>
              <div class="icon-holder"><i class="bi-robot"></i></div>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-12 text-center mt-5">
        <p class="text-white">
          Sẵn sàng bắt đầu?
          <a href="<?= isLoggedIn() ? BASE_URL.'/bang-dieu-khien' : BASE_URL.'/dang-ky' ?>"
             class="btn custom-btn custom-border-btn ms-3">
            <?= isLoggedIn() ? 'Vào Dashboard' : 'Đăng ký miễn phí' ?>
          </a>
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ── GÓI HỌC (PRICING) ── -->
<section class="section-padding" id="section_pricing" style="background:#f0f8ff">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center mb-5">
        <h2>Chọn gói học phù hợp</h2>
        <p>Bắt đầu miễn phí, nâng cấp khi bạn sẵn sàng</p>
      </div>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
      $plans = [
        ['basic',   '🟢 Cơ bản',  'Miễn phí',      '#00BFA6', ['Bài học cơ bản','5 tin AI/ngày','Từ vựng 200+ từ','Bài tập trắc nghiệm']],
        ['advanced','🟡 Nâng cao','199.000đ/tháng', '#F9A826', ['Toàn bộ bài Cơ bản','Bài học Nâng cao','30 tin AI/ngày','Từ vựng 350+ từ']],
        ['premium', '🔴 Cấp cao', '399.000đ/tháng', '#536DFE', ['Toàn bộ nội dung','Chat AI không giới hạn','IELTS/TOEIC','Mock test']],
      ];
      foreach ($plans as [$lv,$name,$price,$color,$features]):
        $current = isLoggedIn() && getMembership()===$lv;
      ?>
      <div class="col-lg-4 col-md-6 col-12">
        <div class="custom-block bg-white shadow-lg position-relative text-center"
             style="border-top:5px solid <?= $color ?>">
          <?php if ($current): ?>
            <span class="badge position-absolute top-0 start-50 translate-middle"
                  style="background:<?= $color ?>;font-size:.75rem">✓ Gói hiện tại</span>
          <?php endif; ?>
          <h5 class="mb-1 mt-3"><?= $name ?></h5>
          <div class="display-6 fw-bold my-3" style="color:<?= $color ?>"><?= $price ?></div>
          <ul class="list-unstyled text-start mb-4">
            <?php foreach ($features as $f): ?>
            <li class="mb-2 d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill" style="color:<?= $color ?>"></i><?= $f ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <a href="<?= isLoggedIn() ? BASE_URL.'/nang-cap' : BASE_URL.'/dang-ky' ?>"
             class="btn custom-btn w-100" style="background:<?= $color ?>">
            <?= $lv==='basic' ? 'Bắt đầu miễn phí' : 'Chọn gói này' ?>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FAQs ── -->
<section class="faq-section section-padding" id="section_4">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 col-12 mb-4">
        <h2>Câu hỏi thường gặp</h2>
      </div>
      <div class="clearfix"></div>
      <div class="col-lg-5 col-12">
        <img src="<?= BASE_URL ?>/public/assets/images/faq_graphic.jpg" class="img-fluid rounded" alt="FAQs">
      </div>
      <div class="col-lg-6 col-12 m-auto">
        <div class="accordion" id="faqAccordion">
          <?php
          $faqs = [
            ['Tôi cần trình độ gì để bắt đầu?',
             'Không cần trình độ gì cả! EnglishAI có lộ trình từ cơ bản nhất. Bắt đầu với gói Cơ bản miễn phí, học ngữ pháp nền tảng rồi nâng cấp dần theo tiến độ của bạn.'],
            ['AI trong EnglishAI hoạt động thế nào?',
             'Chúng tôi tích hợp AI tiên tiến để tạo ra trợ lý tiếng Anh 24/7. Bạn có thể hỏi ngữ pháp, luyện hội thoại, kiểm tra bài viết và nhận phản hồi ngay lập tức bằng tiếng Việt.'],
            ['Gói Cơ bản miễn phí có bị giới hạn không?',
             'Gói Cơ bản hoàn toàn miễn phí với các bài học cơ bản, 200+ từ vựng và 5 tin nhắn AI mỗi ngày. Nâng cấp lên Nâng cao hoặc Cấp cao để mở khóa toàn bộ nội dung.'],
            ['Tôi có thể học trên điện thoại không?',
             'Có! EnglishAI được thiết kế responsive, tối ưu cho mọi thiết bị từ máy tính đến điện thoại và máy tính bảng.'],
          ];
          foreach ($faqs as $i => [$q,$a]):
          ?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faqH<?= $i ?>">
              <button class="accordion-button <?= $i>0?'collapsed':'' ?>"
                      type="button" data-bs-toggle="collapse"
                      data-bs-target="#faqC<?= $i ?>">
                <?= $q ?>
              </button>
            </h2>
            <div id="faqC<?= $i ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>"
                 data-bs-parent="#faqAccordion">
              <div class="accordion-body"><?= $a ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CONTACT / CTA ── -->
<section class="contact-section section-padding section-bg" id="section_5">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 col-12 text-center mb-5">
        <h2>Bắt đầu hành trình học tiếng Anh</h2>
        <p>Tham gia cùng <?= number_format($totalUsers) ?>+ học viên đang học mỗi ngày</p>
      </div>
      <?php if (!isLoggedIn()): ?>
      <div class="col-lg-6 col-12 mx-auto text-center">
        <a href="<?= BASE_URL ?>/dang-ky"   class="btn custom-btn btn-lg me-3">Đăng ký miễn phí</a>
        <a href="<?= BASE_URL ?>/dang-nhap" class="btn custom-border-btn btn-lg">Đăng nhập</a>
      </div>
      <?php else: ?>
      <div class="col-lg-6 col-12 mx-auto text-center">
        <a href="<?= BASE_URL ?>/bang-dieu-khien" class="btn custom-btn btn-lg me-3">Vào Dashboard</a>
        <a href="<?= BASE_URL ?>/chat-ai"          class="btn custom-border-btn btn-lg">Chat AI ngay</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include ROOT.'/app/views/layout/footer.php'; ?>
