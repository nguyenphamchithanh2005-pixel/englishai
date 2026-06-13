<?php
global $conn;
requireLogin();
$pageTitle = 'Dashboard – ' . SITE_NAME;
$uid = $_SESSION['user_id'];

// Thống kê học tập
$stats = $conn->query("
  SELECT 
    COUNT(*) total,
    SUM(completed) completed,
    COALESCE(AVG(NULLIF(score,0)),0) avg_score
  FROM user_progress WHERE user_id=$uid
")->fetch_assoc();

$totalLessons  = $conn->query("SELECT COUNT(*) c FROM lessons WHERE is_active=1 AND level <= '".getMembership()."'")->fetch_assoc()['c'];
$vocabLearned  = $conn->query("SELECT COUNT(*) c FROM user_vocabulary WHERE user_id=$uid AND status='learned'")->fetch_assoc()['c'];

// AI messages today
$user = $conn->query("SELECT ai_messages_today,ai_last_reset FROM users WHERE id=$uid")->fetch_assoc();
$today = date('Y-m-d');
if ($user['ai_last_reset'] !== $today) {
    $conn->query("UPDATE users SET ai_messages_today=0, ai_last_reset='$today' WHERE id=$uid");
    $aiUsed = 0;
} else {
    $aiUsed = (int)$user['ai_messages_today'];
}
$aiLimit = getAILimit();

// Bài học gần đây chưa hoàn thành
$recentLessons = $conn->query("
  SELECT l.id,l.title,l.level,l.duration,l.lesson_type,
         COALESCE(up.completed,0) completed, COALESCE(up.score,0) score
  FROM lessons l
  LEFT JOIN user_progress up ON l.id=up.lesson_id AND up.user_id=$uid
  WHERE l.is_active=1
  ORDER BY up.completed ASC, l.id DESC LIMIT 4
");

// Từ vựng cần ôn tập
$reviewVocab = $conn->query("
  SELECT v.word,v.translation,v.level,uv.status,uv.review_count
  FROM user_vocabulary uv
  JOIN vocabulary v ON uv.vocab_id=v.id
  WHERE uv.user_id=$uid AND uv.status!='learned'
  LIMIT 5
");

$completedPct = $totalLessons > 0 ? round($stats['completed']/$totalLessons*100) : 0;
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<div class="container py-4">
  <!-- Chào mừng -->
  <div class="welcome-banner p-4 mb-4 rounded-3">
    <div class="row align-items-center">
      <div class="col">
        <h4 class="fw-bold text-white mb-1">Xin chào, <?= sanitize($_SESSION['fullname']) ?>! 👋</h4>
        <p class="text-light mb-0">Gói học: <?= getLevelBadge(getMembership()) ?> &nbsp;·&nbsp; Hãy tiếp tục luyện tập hôm nay!</p>
      </div>
      <div class="col-auto">
        <a href="<?= BASE_URL ?>/chat-ai" class="btn btn-warning fw-bold"><i class="bi bi-robot me-1"></i>Chat AI</a>
      </div>
    </div>
  </div>

  <!-- Thống kê -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-icon text-primary mb-2"><i class="bi bi-book-fill fs-2"></i></div>
        <div class="fw-bold fs-4"><?= $stats['completed'] ?></div>
        <div class="text-muted small">Bài đã học</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-icon text-success mb-2"><i class="bi bi-translate fs-2"></i></div>
        <div class="fw-bold fs-4"><?= $vocabLearned ?></div>
        <div class="text-muted small">Từ đã thuộc</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-icon text-warning mb-2"><i class="bi bi-star-fill fs-2"></i></div>
        <div class="fw-bold fs-4"><?= round($stats['avg_score']) ?>%</div>
        <div class="text-muted small">Điểm trung bình</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-icon text-danger mb-2"><i class="bi bi-robot fs-2"></i></div>
        <div class="fw-bold fs-4"><?= $aiUsed ?>/<?= $aiLimit===9999?'∞':$aiLimit ?></div>
        <div class="text-muted small">AI hôm nay</div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Tiến độ học -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 pb-0">
          <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Tiến độ học tập</h6>
        </div>
        <div class="card-body pt-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="small">Tổng tiến độ</span>
            <span class="small fw-bold"><?= $completedPct ?>%</span>
          </div>
          <div class="progress mb-3" style="height:10px">
            <div class="progress-bar bg-primary" style="width:<?= $completedPct ?>%"></div>
          </div>
          <h6 class="mb-3">Bài học gợi ý</h6>
          <?php
global $conn; while ($l = $recentLessons->fetch_assoc()): ?>
          <div class="lesson-item d-flex align-items-center p-2 mb-2 rounded">
            <div class="lesson-item-icon me-3">
              <?php
global $conn;
              $icons=['grammar'=>'bi-diagram-3','reading'=>'bi-file-text','speaking'=>'bi-mic','listening'=>'bi-headphones','writing'=>'bi-pencil'];
              echo '<i class="bi '.($icons[$l['lesson_type']]??'bi-book').' fs-5"></i>';
              ?>
            </div>
            <div class="flex-grow-1 overflow-hidden">
              <div class="fw-semibold text-truncate small"><?= sanitize($l['title']) ?></div>
              <div class="d-flex gap-2"><?= getLevelBadge($l['level']) ?> <span class="text-muted small"><?= $l['duration'] ?> phút</span></div>
            </div>
            <div class="ms-2">
              <?php
global $conn; if ($l['completed']): ?>
                <span class="badge bg-success"><i class="bi bi-check2"></i> <?= $l['score'] ?>%</span>
              <?php
global $conn; elseif (canAccessLevel($l['level'])): ?>
                <a href="<?= BASE_URL ?>/bai-hoc/chi-tiet?id=<?= $l['id'] ?>" class="btn btn-sm btn-primary">Học</a>
              <?php
global $conn; else: ?>
                <a href="<?= BASE_URL ?>/nang-cap" class="btn btn-sm btn-outline-warning"><i class="bi bi-lock"></i></a>
              <?php
global $conn; endif; ?>
            </div>
          </div>
          <?php
global $conn; endwhile; ?>
          <a href="<?= BASE_URL ?>/bai-hoc" class="btn btn-outline-primary w-100 mt-2">Xem tất cả bài học</a>
        </div>
      </div>
    </div>

    <!-- Cột phụ -->
    <div class="col-lg-4">
      <!-- Ôn từ vựng -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between">
          <h6 class="fw-bold mb-0"><i class="bi bi-translate text-success me-2"></i>Ôn từ vựng</h6>
          <a href="<?= BASE_URL ?>/tu-vung" class="small text-primary">Xem thêm</a>
        </div>
        <div class="card-body pt-2">
          <?php
global $conn; if ($reviewVocab->num_rows > 0): while ($v = $reviewVocab->fetch_assoc()): ?>
          <div class="vocab-item d-flex justify-content-between align-items-center py-1 border-bottom">
            <div>
              <span class="fw-semibold small"><?= sanitize($v['word']) ?></span>
              <div class="text-muted" style="font-size:.75rem"><?= sanitize($v['translation']) ?></div>
            </div>
            <?= getLevelBadge($v['level']) ?>
          </div>
          <?php
global $conn; endwhile; else: ?>
          <p class="text-muted small mb-0 text-center py-2">Bắt đầu học từ vựng nào! <a href="<?= BASE_URL ?>/tu-vung">Bắt đầu</a></p>
          <?php
global $conn; endif; ?>
        </div>
      </div>

      <!-- AI Chat Quick -->
      <div class="ai-widget p-3 rounded-3">
        <h6 class="text-white fw-bold mb-2"><i class="bi bi-robot me-1 text-warning"></i>Luyện tập với AI</h6>
        <p class="text-light small mb-3">Đặt câu hỏi, luyện hội thoại, kiểm tra ngữ pháp ngay!</p>
        <div class="d-flex gap-2 mb-2">
          <span class="ai-suggestion-chip" onclick="goChat(this)">Kiểm tra ngữ pháp</span>
          <span class="ai-suggestion-chip" onclick="goChat(this)">Luyện speaking</span>
        </div>
        <div class="d-flex gap-2 mb-3">
          <span class="ai-suggestion-chip" onclick="goChat(this)">Dịch câu</span>
          <span class="ai-suggestion-chip" onclick="goChat(this)">Học phrasal verbs</span>
        </div>
        <div class="progress mb-1" style="height:6px;background:rgba(255,255,255,.2)">
          <div class="progress-bar bg-warning" style="width:<?= $aiLimit===9999?100:min(100,($aiUsed/$aiLimit)*100) ?>%"></div>
        </div>
        <div class="text-light small mb-3"><?= $aiUsed ?>/<?= $aiLimit===9999?'Không giới hạn':$aiLimit ?> tin hôm nay</div>
        <a href="<?= BASE_URL ?>/chat-ai" class="btn btn-warning w-100 fw-bold btn-sm"><i class="bi bi-chat-dots me-1"></i>Mở Chat AI</a>
      </div>
    </div>
  </div>
</div>

<script>
function goChat(el){ window.location='/englishAI/ai-chat.php?q='+encodeURIComponent(el.textContent); }
</script>
<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
