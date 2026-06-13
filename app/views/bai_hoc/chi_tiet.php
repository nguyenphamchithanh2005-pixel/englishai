<?php
global $conn;
requireLogin();
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL.'/bai-hoc');

$lesson = $conn->query("SELECT l.*,c.name cat_name FROM lessons l LEFT JOIN categories c ON l.category_id=c.id WHERE l.id=$id AND l.is_active=1")->fetch_assoc();
if (!$lesson) { $_SESSION['flash']=['type'=>'danger','message'=>'Bài học không tồn tại.']; redirect(BASE_URL.'/bai-hoc'); }
if (!canAccessLevel($lesson['level'])) redirect(BASE_URL.'/nang-cap');

$pageTitle  = $lesson['title'] . ' – ' . SITE_NAME;
$exercises  = $conn->query("SELECT * FROM exercises WHERE lesson_id=$id ORDER BY order_num,id");
$exArr      = [];
while ($e = $exercises->fetch_assoc()) $exArr[] = $e;
$progress   = $conn->query("SELECT * FROM user_progress WHERE user_id=$uid AND lesson_id=$id")->fetch_assoc();
if (!$progress) $conn->query("INSERT IGNORE INTO user_progress (user_id,lesson_id) VALUES ($uid,$id)");

// Chỉ hiện TTS cho bài NGHE và ĐỌC
$showTTS    = in_array($lesson['lesson_type'], ['listening', 'reading']);
$isListening = ($lesson['lesson_type'] === 'listening');

$resultData = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_exercise'])) {
    $answers = $_POST['answers'] ?? [];
    $correct = 0; $results = [];
    foreach ($exArr as $ex) {
        $ua = $answers[$ex['id']] ?? '';
        $ok = strtoupper($ua) === strtoupper($ex['correct_answer']);
        if ($ok) $correct++;
        $results[$ex['id']] = ['user'=>$ua,'correct'=>$ex['correct_answer'],'ok'=>$ok,'explain'=>$ex['explanation']];
    }
    $total = count($exArr);
    $score = $total > 0 ? round($correct/$total*100) : 0;
    $done  = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO user_progress (user_id,lesson_id,completed,score,completed_at) VALUES ($uid,$id,1,$score,'$done') ON DUPLICATE KEY UPDATE completed=1,score=$score,completed_at='$done'");
    $resultData = ['correct'=>$correct,'total'=>$total,'score'=>$score,'results'=>$results];
}
$nextLesson = $conn->query("SELECT id,title FROM lessons WHERE is_active=1 AND id>$id ORDER BY id LIMIT 1")->fetch_assoc();
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<style>
/* ── TTS Controls (chỉ dùng cho Nghe & Đọc) ── */
.tts-bar {
  background:linear-gradient(135deg,#13547a,#1a6e96);
  border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.25rem;
}
.tts-bar .tts-label { color:#80d0c7;font-weight:700;font-size:.85rem;letter-spacing:.04em; }
.speed-btn {
  border:1px solid rgba(255,255,255,.3); background:rgba(255,255,255,.1);
  color:#fff; border-radius:6px; padding:3px 10px; font-size:.78rem; cursor:pointer;
}
.speed-btn:hover,.speed-btn.active { background:#80d0c7;border-color:#80d0c7;color:#13547a;font-weight:700; }
.tts-progress-bar  { height:4px;background:rgba(255,255,255,.2);border-radius:2px;margin-top:.5rem;overflow:hidden; }
.tts-progress-fill { height:100%;background:#80d0c7;width:0%;transition:width .3s;border-radius:2px; }
.listening-hint {
  background:linear-gradient(135deg,#f0f4ff,#e8f0ff);
  border-left:4px solid #536DFE; border-radius:0 12px 12px 0; padding:1rem 1.25rem; margin-bottom:1rem;
}
/* ── Nút phát âm trong bài tập ── */
.pron-btn {
  background:none; border:1px solid #dee2e6; border-radius:6px;
  padding:2px 6px; cursor:pointer; color:#6c757d; font-size:.75rem;
  transition:all .15s;
}
.pron-btn:hover  { border-color:#80d0c7;color:#13547a; }
.pron-btn.active { background:#e1f5ee;border-color:#00BFA6;color:#085041; }
/* ── Lesson content ── */
.lesson-detail-header { background:linear-gradient(135deg,#13547a,#1a6e96);padding:1.5rem 1.75rem; }
.lesson-content h4,.lesson-content h5 { color:#13547a; }
.lesson-content strong { color:#13547a; }
.ai-lesson-cta { background:linear-gradient(135deg,#0f2535,#13547a);border-radius:12px; }
.option-label { cursor:pointer;transition:background .15s;border-radius:8px; }
.option-label:hover { background:#f0f8ff; }
.option-label:has(input:checked) { background:#e0f4f2;border-color:#80d0c7 !important; }
.result-pass { background:linear-gradient(135deg,#f0fdf9,#d1f5ee);border-radius:12px; }
.result-fail { background:linear-gradient(135deg,#fff5f5,#ffe0e0);border-radius:12px; }
.result-score { font-size:3rem;font-weight:800;color:#13547a; }
</style>

<?php
global $conn; if ($showTTS): ?>
<!-- TTS chỉ load khi là bài Nghe hoặc Đọc -->
<script>
var _ttsVoices = [];
function _initVoices() {
    _ttsVoices = window.speechSynthesis ? window.speechSynthesis.getVoices() : [];
}
if (window.speechSynthesis) {
    if (window.speechSynthesis.onvoiceschanged !== undefined) {
        window.speechSynthesis.onvoiceschanged = _initVoices;
    }
    _initVoices();
    setTimeout(_initVoices, 500);
}
var _ttsSpeed = 0.80, _ttsPlaying = false, _ttsProgressTimer = null;

function sayText(text, btn, rate) {
    if (!window.speechSynthesis) {
        alert('Trình duyệt không hỗ trợ TTS. Dùng Chrome hoặc Edge.');
        return;
    }
    window.speechSynthesis.cancel();
    if (!text || !text.trim()) return;
    var u = new SpeechSynthesisUtterance(text.replace(/<[^>]*>/g,' ').trim());
    u.lang = 'en-US'; u.rate = rate || _ttsSpeed; u.pitch = 1.0; u.volume = 1.0;
    if (_ttsVoices.length === 0) _initVoices();
    for (var i=0;i<_ttsVoices.length;i++) {
        if (_ttsVoices[i].lang === 'en-US') { u.voice = _ttsVoices[i]; break; }
    }
    if (!u.voice) {
        for (var i=0;i<_ttsVoices.length;i++) {
            if (_ttsVoices[i].lang.indexOf('en')===0) { u.voice=_ttsVoices[i]; break; }
        }
    }
    if (btn) {
        btn.classList.add('active');
        u.onend   = function(){ btn.classList.remove('active'); };
        u.onerror = function(){ btn.classList.remove('active'); };
    }
    window.speechSynthesis.speak(u);
}

function ttsToggle() {
    if (!window.speechSynthesis) return;
    if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
        window.speechSynthesis.pause();
        _setPlayBtn(false, '⏸ Tiếp tục');
        document.getElementById('ttsStatus').textContent = 'Đã tạm dừng';
        return;
    }
    if (window.speechSynthesis.paused) {
        window.speechSynthesis.resume();
        _setPlayBtn(true);
        document.getElementById('ttsStatus').textContent = 'Đang phát...';
        return;
    }
    var el   = document.getElementById('lessonContent');
    var text = el ? (el.innerText || el.textContent) : '';
    text = text.replace(/\s+/g,' ').trim();
    if (!text) return;
    _ttsPlaying = true;
    _setPlayBtn(true);
    document.getElementById('ttsStatus').textContent = 'Đang phát...';
    _startProgress();
    var u = new SpeechSynthesisUtterance(text);
    u.lang = 'en-US'; u.rate = _ttsSpeed; u.pitch = 1.0; u.volume = 1.0;
    if (_ttsVoices.length === 0) _initVoices();
    for (var i=0;i<_ttsVoices.length;i++) {
        if (_ttsVoices[i].lang === 'en-US') { u.voice = _ttsVoices[i]; break; }
    }
    u.onend   = function(){ _ttsPlaying=false; _setPlayBtn(false); document.getElementById('ttsStatus').textContent='✓ Đã phát xong'; _stopProgress(); };
    u.onerror = function(){ _ttsPlaying=false; _setPlayBtn(false); document.getElementById('ttsStatus').textContent='⚠ Lỗi – thử lại'; _stopProgress(); };
    window.speechSynthesis.speak(u);
}

function ttsStop() {
    window.speechSynthesis.cancel();
    _ttsPlaying = false;
    _setPlayBtn(false);
    document.getElementById('ttsStatus').textContent = 'Đã dừng';
    _stopProgress();
    document.getElementById('ttsProgressFill').style.width = '0%';
}

function setSpeed(s, el) {
    _ttsSpeed = s;
    document.querySelectorAll('.speed-btn').forEach(function(b){ b.classList.remove('active'); });
    el.classList.add('active');
    if (_ttsPlaying) { ttsStop(); ttsToggle(); }
}

function _setPlayBtn(playing, label) {
    var b1 = document.getElementById('btnPlay');
    var b2 = document.getElementById('sidebarPlay');
    var txt = playing ? '<i class="bi bi-pause-fill me-1"></i>Tạm dừng' : '<i class="bi bi-play-fill me-1"></i>Play';
    if (label) txt = '<i class="bi bi-play-fill me-1"></i>' + label;
    if (b1) b1.innerHTML = txt;
    if (b2) b2.innerHTML = playing ? '<i class="bi bi-pause-fill me-1"></i>Tạm dừng' : '<i class="bi bi-play-fill me-1"></i>Phát toàn bài';
}

function _startProgress() {
    clearInterval(_ttsProgressTimer);
    var pct = 0;
    _ttsProgressTimer = setInterval(function(){
        if (!window.speechSynthesis.speaking) { clearInterval(_ttsProgressTimer); return; }
        if (window.speechSynthesis.paused) return;
        pct = Math.min(pct + 0.25, 98);
        document.getElementById('ttsProgressFill').style.width = pct + '%';
    }, 200);
}
function _stopProgress() { clearInterval(_ttsProgressTimer); }
</script>
<?php
global $conn; endif; ?>

<div class="container py-4">
  <!-- Breadcrumb -->
  <nav class="mb-3"><ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/bai-hoc">Bài học</a></li>
    <li class="breadcrumb-item text-muted"><?= sanitize($lesson['cat_name']) ?></li>
    <li class="breadcrumb-item active"><?= sanitize($lesson['title']) ?></li>
  </ol></nav>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4">
        <!-- Header -->
        <div class="lesson-detail-header p-4">
          <div class="d-flex flex-wrap gap-2 mb-2">
            <?= getLevelBadge($lesson['level']) ?>
            <span class="badge bg-secondary"><?= ucfirst($lesson['lesson_type']) ?></span>
            <span class="badge bg-dark opacity-75"><i class="bi bi-clock me-1"></i><?= $lesson['duration'] ?> phút</span>
          </div>
          <h3 class="fw-bold text-white mb-0"><?= sanitize($lesson['title']) ?></h3>
          <?php
global $conn; if ($progress && $progress['completed']): ?>
            <span class="badge bg-success mt-2"><i class="bi bi-check-circle me-1"></i>Đã hoàn thành · <?= $progress['score'] ?>%</span>
          <?php
global $conn; endif; ?>
        </div>

        <div class="card-body p-4">

          <?php
global $conn; if ($showTTS): ?>
          <!-- ══ TTS BAR – chỉ hiện cho bài Nghe & Đọc ══ -->
          <div class="tts-bar">
            <div class="tts-label mb-2">
              <i class="bi bi-<?= $isListening?'headphones':'book';'headphones' ?> me-1"></i>
              <?= $isListening ? 'LUYỆN NGHE – Nhấn Play để bắt đầu nghe' : 'ĐỌC TO BÀI HỌC' ?>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <button id="btnPlay" onclick="ttsToggle()"
                      class="btn btn-sm btn-success fw-bold px-3">
                <i class="bi bi-play-fill me-1"></i>Play
              </button>
              <button onclick="window.speechSynthesis.pause?window.speechSynthesis.pause():null"
                      class="btn btn-sm btn-outline-light">
                <i class="bi bi-pause-fill"></i>
              </button>
              <button onclick="ttsStop()" class="btn btn-sm btn-outline-light">
                <i class="bi bi-stop-fill"></i>
              </button>
              <!-- Tốc độ -->
              <div class="d-flex gap-1 align-items-center ms-1">
                <span class="text-white opacity-75" style="font-size:.75rem">Tốc độ:</span>
                <button class="speed-btn" onclick="setSpeed(0.6,this)">0.6×</button>
                <button class="speed-btn active" onclick="setSpeed(0.8,this)">0.8×</button>
                <button class="speed-btn" onclick="setSpeed(1.0,this)">1×</button>
                <button class="speed-btn" onclick="setSpeed(1.25,this)">1.25×</button>
              </div>
            </div>
            <div class="tts-progress-bar">
              <div class="tts-progress-fill" id="ttsProgressFill"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <span id="ttsStatus" class="text-white opacity-75" style="font-size:.72rem">Sẵn sàng</span>
              <span class="text-white opacity-50" style="font-size:.72rem">Web Speech API – Chrome/Edge</span>
            </div>
          </div>

          <?php
global $conn; if ($isListening): ?>
          <!-- Hướng dẫn nghe -->
          <div class="listening-hint">
            <div class="d-flex gap-2">
              <i class="bi bi-info-circle-fill text-primary mt-1"></i>
              <div>
                <strong>Hướng dẫn luyện nghe:</strong>
                <ol class="mb-0 mt-1 small">
                  <li>Nhấn <strong>Play</strong> → nghe toàn bài, <strong>không nhìn</strong> vào nội dung</li>
                  <li>Nghe lần 2 ở tốc độ <strong>0.6×</strong> để nghe rõ từng từ</li>
                  <li>Đọc nội dung bên dưới và nghe lại để đối chiếu</li>
                </ol>
              </div>
            </div>
          </div>
          <?php
global $conn; endif; ?>
          <?php
global $conn; endif; /* end showTTS */ ?>

          <!-- Nội dung bài học -->
          <div class="lesson-content" id="lessonContent">
            <?= $lesson['content'] ?>
          </div>

          <!-- Nút đọc từng đoạn – CHỈ hiện cho bài Nghe & Đọc -->
          <?php
global $conn; if ($showTTS): ?>
          <div class="mt-3 p-3 rounded-3" style="background:#f8f9fa;border:1px dashed #dee2e6">
            <div class="small text-muted fw-semibold mb-2">
              <i class="bi bi-play-circle me-1"></i>Nghe từng đoạn:
            </div>
            <div id="paragraphBtns" class="d-flex flex-wrap gap-2"></div>
          </div>
          <?php
global $conn; endif; ?>

          <!-- AI CTA -->
          <div class="ai-lesson-cta mt-4 p-3">
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-robot fs-2 text-warning"></i>
              <div class="flex-grow-1">
                <div class="fw-bold text-white">Luyện tập với AI</div>
                <div class="small text-light">Hỏi AI về bài "<?= sanitize($lesson['title']) ?>"</div>
              </div>
              <a href="<?= BASE_URL ?>/chat-ai?context=<?= urlencode('Tôi vừa học bài: '.$lesson['title'].'. Hãy giúp tôi luyện tập.') ?>"
                 class="btn btn-warning btn-sm fw-bold">
                <i class="bi bi-chat-dots me-1"></i>Hỏi AI
              </a>
            </div>
          </div>
        </div><!-- /card-body -->
      </div><!-- /card -->

      <!-- ── Bài tập ── -->
      <?php
global $conn; if (!empty($exArr)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 p-4 pb-2">
          <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Bài tập</h5>
          <p class="text-muted small mb-0"><?= count($exArr) ?> câu · Chọn đáp án đúng</p>
        </div>
        <div class="card-body p-4">
          <?php
global $conn; if ($resultData): ?>
          <div class="text-center p-4 mb-4 <?= $resultData['score']>=70?'result-pass':'result-fail' ?>">
            <div class="result-score"><?= $resultData['score'] ?>%</div>
            <div class="fw-bold fs-5"><?= $resultData['score']>=70?'🎉 Xuất sắc!':'💪 Cần cố gắng thêm!' ?></div>
            <div class="text-muted"><?= $resultData['correct'] ?>/<?= $resultData['total'] ?> câu đúng</div>
          </div>
          <?php
global $conn; foreach ($exArr as $i=>$ex): $r=$resultData['results'][$ex['id']]; ?>
          <div class="p-3 mb-3 rounded-3 border <?= $r['ok']?'border-success bg-success bg-opacity-10':'border-danger bg-danger bg-opacity-10' ?>">
            <div class="fw-semibold mb-2"><?= ($i+1) ?>. <?= sanitize($ex['question']) ?></div>
            <?php
global $conn; foreach(['A'=>$ex['option_a'],'B'=>$ex['option_b'],'C'=>$ex['option_c'],'D'=>$ex['option_d']] as $k=>$v): if(!$v) continue; ?>
            <div class="<?= $k===$r['correct']?'text-success fw-bold':($k===$r['user']&&!$r['ok']?'text-danger text-decoration-line-through':'text-muted') ?>">
              <?= $k===$r['correct']?'✓':($k===$r['user']&&!$r['ok']?'✗':'') ?> <?= $k ?>. <?= sanitize($v) ?>
            </div>
            <?php
global $conn; endforeach; ?>
            <?php
global $conn; if ($ex['explanation']): ?>
            <div class="mt-2 small text-muted"><i class="bi bi-lightbulb me-1 text-warning"></i><?= sanitize($ex['explanation']) ?></div>
            <?php
global $conn; endif; ?>
          </div>
          <?php
global $conn; endforeach; ?>
          <div class="d-flex gap-3 justify-content-center mt-4">
            <a href="<?= BASE_URL ?>/bai-hoc/chi-tiet?id=<?= $id ?>" class="btn btn-outline-primary">Làm lại</a>
            <?php
global $conn; if ($nextLesson): ?><a href="<?= BASE_URL ?>/bai-hoc/chi-tiet?id=<?= $nextLesson['id'] ?>" class="btn btn-primary">Bài tiếp <i class="bi bi-arrow-right ms-1"></i></a><?php endif; ?>
          </div>
          <?php
global $conn; else: ?>
          <form method="POST" id="exerciseForm">
            <?php
global $conn; foreach ($exArr as $i=>$ex): ?>
            <div class="exercise-item p-3 mb-3 rounded-3 border">
              <div class="fw-semibold mb-3"><?= ($i+1) ?>. <?= sanitize($ex['question']) ?></div>
              <div class="row g-2">
                <?php
global $conn; foreach(['A'=>$ex['option_a'],'B'=>$ex['option_b'],'C'=>$ex['option_c'],'D'=>$ex['option_d']] as $k=>$v): if(!$v) continue; ?>
                <div class="col-md-6">
                  <label class="option-label d-flex align-items-center p-2 rounded border gap-2">
                    <input type="radio" name="answers[<?= $ex['id'] ?>]" value="<?= $k ?>" required>
                    <strong><?= $k ?>.</strong> <span class="flex-grow-1"><?= sanitize($v) ?></span>
                  </label>
                </div>
                <?php
global $conn; endforeach; ?>
              </div>
            </div>
            <?php
global $conn; endforeach; ?>
            <button type="submit" name="submit_exercise" class="btn btn-success w-100 py-2 fw-bold">
              <i class="bi bi-send me-1"></i>Nộp bài
            </button>
          </form>
          <?php
global $conn; endif; ?>
        </div>
      </div>
      <?php
global $conn; endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm sticky-top" style="top:80px">
        <div class="card-body p-3">
          <h6 class="fw-bold mb-3">Thông tin bài học</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><i class="bi bi-layers me-2 text-primary"></i>Cấp độ: <?= getLevelLabel($lesson['level']) ?></li>
            <li class="mb-2"><i class="bi bi-tag me-2 text-success"></i>Danh mục: <?= sanitize($lesson['cat_name']) ?></li>
            <li class="mb-2"><i class="bi bi-clock me-2 text-warning"></i>Thời gian: <?= $lesson['duration'] ?> phút</li>
            <li class="mb-2"><i class="bi bi-headphones me-2 text-info"></i>
              Loại bài: <?= $isListening?'🎧 Luyện nghe':($lesson['lesson_type']==='reading'?'📖 Đọc hiểu':ucfirst($lesson['lesson_type'])) ?>
            </li>
            <?php
global $conn; if ($progress && $progress['completed']): ?>
            <li class="mb-2"><i class="bi bi-star-fill me-2 text-warning"></i>Điểm tốt nhất: <?= $progress['score'] ?>%</li>
            <?php
global $conn; endif; ?>
          </ul>

          <?php
global $conn; if ($showTTS): ?>
          <!-- Quick play trong sidebar -->
          <div class="p-2 rounded-3 mb-3" style="background:#f0f8ff">
            <div class="small fw-semibold mb-2" style="color:#13547a">
              <i class="bi bi-headphones me-1"></i>Nghe nhanh
            </div>
            <div class="d-grid gap-1">
              <button id="sidebarPlay" onclick="ttsToggle()" class="btn btn-sm btn-primary">
                <i class="bi bi-play-fill me-1"></i>Phát toàn bài
              </button>
              <button onclick="ttsStop()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-stop-fill me-1"></i>Dừng
              </button>
            </div>
          </div>
          <?php
global $conn; endif; ?>

          <hr>
          <a href="<?= BASE_URL ?>/chat-ai?context=<?= urlencode('Giải thích thêm về: '.$lesson['title']) ?>"
             class="btn btn-outline-primary btn-sm w-100 mb-2">
            <i class="bi bi-robot me-1"></i>Hỏi AI về bài này
          </a>
          <a href="<?= BASE_URL ?>/bai-hoc" class="btn btn-outline-secondary btn-sm w-100">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
          </a>
          <?php
global $conn; if ($nextLesson): ?>
          <hr>
          <p class="small text-muted mb-1">Bài tiếp theo:</p>
          <a href="<?= BASE_URL ?>/bai-hoc/chi-tiet?id=<?= $nextLesson['id'] ?>"
             class="text-primary text-decoration-none small fw-semibold">
            <?= sanitize($nextLesson['title']) ?> <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <?php
global $conn; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
global $conn; if ($showTTS): ?>
<script>
// Tạo nút nghe từng đoạn
(function buildParagraphBtns() {
  var container = document.getElementById('paragraphBtns');
  if (!container) return;
  var content = document.getElementById('lessonContent');
  if (!content) return;
  var paras = content.querySelectorAll('p, li');
  var idx = 0;
  paras.forEach(function(p) {
    var text = (p.innerText || p.textContent || '').trim();
    if (text.length < 20) return;
    idx++;
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-sm btn-outline-secondary';
    btn.style.fontSize = '.78rem';
    btn.innerHTML = '<i class="bi bi-play-fill me-1"></i>Đoạn ' + idx;
    btn.title = text.substring(0, 60) + '...';
    (function(t, i, b) {
      b.onclick = function() {
        window.speechSynthesis.cancel();
        b.innerHTML = '<i class="bi bi-stop-fill me-1"></i>Đoạn ' + i;
        b.className = 'btn btn-sm btn-danger';
        sayText(t, null, _ttsSpeed);
        var u = window.speechSynthesis;
        setTimeout(function() {
          b.innerHTML = '<i class="bi bi-play-fill me-1"></i>Đoạn ' + i;
          b.className = 'btn btn-sm btn-outline-secondary';
        }, (t.split(' ').length / _ttsSpeed) * 700);
      };
    })(text, idx, btn);
    container.appendChild(btn);
  });
  if (idx === 0 && container.closest('.mt-3')) {
    container.closest('.mt-3').style.display = 'none';
  }
})();
</script>
<?php
global $conn; endif; ?>

<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
