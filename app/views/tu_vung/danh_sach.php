<?php
global $conn;
$pageTitle = 'Từ vựng – ' . SITE_NAME;

$level  = sanitize($_GET['level']  ?? '');
$cat    = sanitize($_GET['cat']    ?? '');
$search = sanitize($_GET['search'] ?? '');
$mode   = sanitize($_GET['mode']   ?? 'list');

$where = "WHERE 1";
if ($level) $where .= " AND v.level='" . addslashes($level) . "'";
if ($cat)   $where .= " AND v.category='" . addslashes($cat) . "'";
if ($search) {
    $s = addslashes($search);
    $where .= " AND (v.word LIKE '%$s%' OR v.translation LIKE '%$s%' OR v.definition LIKE '%$s%')";
}

$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 60;
$totalCount = (int)$conn->query("SELECT COUNT(*) c FROM vocabulary v $where")->fetch_assoc()['c'];
$totalPages = max(1, (int)ceil($totalCount / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;
$levelOrder = "FIELD(v.level,'basic','advanced','premium')";

if (isLoggedIn()) {
    $uid   = (int)$_SESSION['user_id'];
    $query = "SELECT v.*, uv.status, uv.review_count
              FROM vocabulary v
              LEFT JOIN user_vocabulary uv ON v.id=uv.vocab_id AND uv.user_id=$uid
              $where ORDER BY $levelOrder, v.word LIMIT $perPage OFFSET $offset";
} else {
    $query = "SELECT v.*, NULL AS status, 0 AS review_count
              FROM vocabulary v
              $where ORDER BY $levelOrder, v.word LIMIT $perPage OFFSET $offset";
}
$result   = $conn->query($query);
$vocabArr = [];
while ($r = $result->fetch_assoc()) $vocabArr[] = $r;
$categories = $conn->query("SELECT DISTINCT category FROM vocabulary WHERE category IS NOT NULL ORDER BY category");

if ($_SERVER['REQUEST_METHOD']==='POST' && isLoggedIn() && isset($_POST['mark_vocab'])) {
    $vid    = (int)$_POST['vocab_id'];
    $status = in_array($_POST['status'],['new','learning','learned']) ? $_POST['status'] : 'learning';
    $now    = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO user_vocabulary (user_id,vocab_id,status,review_count,last_reviewed)
                  VALUES ($uid,$vid,'$status',1,'$now')
                  ON DUPLICATE KEY UPDATE status='$status',review_count=review_count+1,last_reviewed='$now'");
    redirect("vocabulary.php?" . http_build_query(['level'=>$level,'mode'=>$mode,'cat'=>$cat,'page'=>$page]));
}

$counts = [];
foreach (['basic','advanced','premium'] as $lv) {
    $wh = "WHERE level='$lv'";
    if ($cat)    $wh .= " AND category='" . addslashes($cat) . "'";
    if ($search) $wh .= " AND (word LIKE '%" . addslashes($search) . "%' OR translation LIKE '%" . addslashes($search) . "%')";
    $counts[$lv] = (int)$conn->query("SELECT COUNT(*) c FROM vocabulary $wh")->fetch_assoc()['c'];
}
$totalAll = array_sum($counts);
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<style>
.pronounce-btn {
  background: linear-gradient(135deg,#13547a,#80d0c7);
  color:#fff; border:none; border-radius:50%;
  width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;
  cursor:pointer; transition:transform .15s,box-shadow .15s; font-size:.85rem; flex-shrink:0;
}
.pronounce-btn:hover  { transform:scale(1.15); box-shadow:0 4px 12px rgba(19,84,122,.4); }
.pronounce-btn.active { background:linear-gradient(135deg,#e74c3c,#c0392b); }
.vocab-word-big { font-size:1.1rem;font-weight:700;color:#13547a; }
.vocab-card { background:#fff;border-radius:14px;border:1px solid #e0eeec;overflow:hidden;transition:transform .2s,box-shadow .2s; }
.vocab-card:hover { transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1); }
.vocab-card-header { padding:.8rem 1rem;background:#f5fbf9;border-bottom:1px solid #e0eeec; }
.vocab-card-body   { padding:.8rem 1rem; }
.blur-text { filter:blur(4px); }
.flashcard-wrap  { width:360px;max-width:92vw;height:230px;perspective:1000px;cursor:pointer; }
.flashcard-inner { width:100%;height:100%;position:relative;transform-style:preserve-3d;transition:transform .5s; }
.flashcard-front,.flashcard-back {
  position:absolute;inset:0;backface-visibility:hidden;border-radius:20px;
  box-shadow:0 8px 28px rgba(0,0,0,.12);
  display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem;
}
.flashcard-front { background:linear-gradient(135deg,#13547a,#80d0c7); }
.flashcard-back  { background:linear-gradient(135deg,#536DFE,#764ba2);transform:rotateY(180deg); }
.fc-word  { color:#fff;font-size:2.2rem;font-weight:800; }
.fc-pron  { color:rgba(255,255,255,.75);font-size:.95rem; }
.fc-trans { color:#fff;font-weight:700;font-size:1.25rem; }
.fc-def,.fc-ex { color:rgba(255,255,255,.85);font-size:.88rem;text-align:center; }
</style>

<!-- ══ TTS Engine: dùng Web Speech API trực tiếp, không qua wrapper ══ -->
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
    setTimeout(_initVoices, 500);   // Chrome cần gọi lại
}

function sayWord(word, btn, rate) {
    if (!window.speechSynthesis) {
        alert('Trình duyệt chưa hỗ trợ TTS. Vui lòng dùng Chrome hoặc Edge.');
        return;
    }
    window.speechSynthesis.cancel();
    if (!word || word.trim() === '') return;

    var u     = new SpeechSynthesisUtterance(word.trim());
    u.lang    = 'en-US';
    u.rate    = rate || 0.75;
    u.pitch   = 1.0;
    u.volume  = 1.0;

    // Chọn giọng tiếng Anh
    if (_ttsVoices.length === 0) _initVoices();
    var voice = null;
    for (var i = 0; i < _ttsVoices.length; i++) {
        if (_ttsVoices[i].lang === 'en-US') { voice = _ttsVoices[i]; break; }
    }
    if (!voice) {
        for (var i = 0; i < _ttsVoices.length; i++) {
            if (_ttsVoices[i].lang.indexOf('en') === 0) { voice = _ttsVoices[i]; break; }
        }
    }
    if (voice) u.voice = voice;

    if (btn) {
        btn.classList.add('active');
        u.onend   = function() { btn.classList.remove('active'); };
        u.onerror = function() { btn.classList.remove('active'); };
    }
    window.speechSynthesis.speak(u);
}

</script>

<!-- PAGE HEADER -->
<div class="site-header" style="padding-top:80px;padding-bottom:40px">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="fw-bold text-white mb-1"><i class="bi bi-translate me-2"></i>Từ vựng</h2>
        <p class="text-light mb-0">
          Hiển thị <strong><?= count($vocabArr) ?></strong> / <strong><?= $totalCount ?></strong> từ
          &nbsp;·&nbsp; Trang <?= $page ?>/<?= $totalPages ?>
        </p>
      </div>
      <div class="d-flex gap-2">
        <a href="?<?= http_build_query(array_merge($_GET,['mode'=>'list','page'=>1])) ?>"
           class="btn btn-sm <?= $mode==='list'?'btn-white':'btn-outline-light' ?>">
          <i class="bi bi-list-ul me-1"></i>Danh sách
        </a>
        <a href="?<?= http_build_query(array_merge($_GET,['mode'=>'flashcard','page'=>1])) ?>"
           class="btn btn-sm <?= $mode==='flashcard'?'btn-white':'btn-outline-light' ?>">
          <i class="bi bi-card-text me-1"></i>Flashcard
        </a>
      </div>
    </div>
  </div>
</div>

<div class="container py-4">

  <!-- Bộ lọc -->
  <div class="filter-bar p-3 mb-3 rounded-3">
    <form method="GET" class="row g-2 align-items-end">
      <input type="hidden" name="mode" value="<?= $mode ?>">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control"
               placeholder="🔍 Tìm từ vựng..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-3">
        <select name="level" class="form-select">
          <option value="">Tất cả cấp độ</option>
          <option value="basic"    <?= $level==='basic'?'selected':'' ?>>🟢 Cơ bản</option>
          <option value="advanced" <?= $level==='advanced'?'selected':'' ?>>🟡 Nâng cao</option>
          <option value="premium"  <?= $level==='premium'?'selected':'' ?>>🔴 Cấp cao</option>
        </select>
      </div>
      <div class="col-md-3">
        <select name="cat" class="form-select">
          <option value="">Tất cả chủ đề</option>
          <?php
$catVI = [
  'Academic'=>'Học thuật','Animals'=>'Động vật','Art'=>'Nghệ thuật',
  'Body'=>'Cơ thể','Business'=>'Kinh doanh','Character'=>'Tính cách',
  'Colors'=>'Màu sắc','Description'=>'Mô tả','Education'=>'Giáo dục',
  'Environment'=>'Môi trường','Family'=>'Gia đình','Food'=>'Đồ ăn',
  'Greetings'=>'Chào hỏi','Health'=>'Sức khỏe','Home'=>'Nhà cửa',
  'IELTS'=>'IELTS','Law & Politics'=>'Luật & Chính trị',
  'Literature'=>'Văn học','Medical'=>'Y tế','Nature'=>'Thiên nhiên',
  'Occupations'=>'Nghề nghiệp','Science'=>'Khoa học','Shopping'=>'Mua sắm',
  'Society'=>'Xã hội','Sports'=>'Thể thao','Technology'=>'Công nghệ',
  'Time'=>'Thời gian','TOEIC'=>'TOEIC','Travel'=>'Du lịch',
  'Weather'=>'Thời tiết','Work'=>'Công việc','General'=>'Chung',
];
global $conn; while ($c = $categories->fetch_assoc()):
  $label = $catVI[$c['category']] ?? $c['category'];
?>
          <option value="<?= htmlspecialchars($c['category']) ?>"
                  <?= $cat===$c['category']?'selected':'' ?>>
            <?= htmlspecialchars($label) ?>
          </option>
          <?php
global $conn; endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Lọc</button>
      </div>
    </form>
  </div>

  <!-- Tabs cấp độ -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a href="?<?= http_build_query(array_merge($_GET,['level'=>'','page'=>1])) ?>"
       class="btn btn-sm <?= $level===''?'btn-dark':'btn-outline-secondary' ?>">
      🔤 Tất cả <span class="badge bg-secondary ms-1"><?= $totalAll ?></span>
    </a>
    <a href="?<?= http_build_query(array_merge($_GET,['level'=>'basic','page'=>1])) ?>"
       class="btn btn-sm <?= $level==='basic'?'btn-success':'btn-outline-success' ?>">
      🟢 Cơ bản <span class="badge bg-success ms-1"><?= $counts['basic'] ?></span>
    </a>
    <a href="?<?= http_build_query(array_merge($_GET,['level'=>'advanced','page'=>1])) ?>"
       class="btn btn-sm <?= $level==='advanced'?'btn-warning':'btn-outline-warning' ?>">
      🟡 Nâng cao <span class="badge bg-warning text-dark ms-1"><?= $counts['advanced'] ?></span>
    </a>
    <a href="?<?= http_build_query(array_merge($_GET,['level'=>'premium','page'=>1])) ?>"
       class="btn btn-sm <?= $level==='premium'?'btn-danger':'btn-outline-danger' ?>">
      🔴 Cấp cao <span class="badge bg-danger ms-1"><?= $counts['premium'] ?></span>
    </a>
  </div>

<?php
global $conn; if ($mode === 'flashcard'): ?>
<!-- ══ FLASHCARD ══ -->
<?php
global $conn; if (empty($vocabArr)): ?>
  <div class="text-center py-5">
    <i class="bi bi-search fs-1 text-muted"></i>
    <h5 class="mt-3 text-muted">Không tìm thấy từ vựng</h5>
  </div>
<?php
global $conn; else: ?>
  <div class="text-center mb-3">
    <p class="text-muted small">Nhấn vào thẻ để lật · <?= count($vocabArr) ?> từ · <kbd>←</kbd><kbd>→</kbd> chuyển · <kbd>Space</kbd> lật · <kbd>P</kbd> phát âm</p>
  </div>
  <div class="d-flex flex-column align-items-center">
    <div class="flashcard-wrap" onclick="flipCard()">
      <div class="flashcard-inner" id="fcInner">
        <div class="flashcard-front">
          <div id="fcLevel" class="mb-2"></div>
          <h2 class="fc-word" id="fcWord"></h2>
          <div class="fc-pron mt-1" id="fcPron"></div>
          <button type="button" id="fcPronBtn"
                  onclick="event.stopPropagation(); sayWord(document.getElementById('fcWord').textContent, this, 0.75)"
                  class="pronounce-btn mt-3" title="Nghe phát âm (P)">
            <i class="bi bi-volume-up-fill"></i>
          </button>
          <div class="small mt-2 text-white opacity-60">Nhấn để xem nghĩa</div>
        </div>
        <div class="flashcard-back">
          <div class="fc-trans mb-2" id="fcTrans"></div>
          <div class="fc-def mb-1"   id="fcDef"></div>
          <div class="fc-ex"         id="fcEx"></div>
          <button type="button" id="fcExBtn"
                  onclick="event.stopPropagation(); var t=document.getElementById('fcEx').textContent.replace(/[\"\']/g,'').trim(); if(t) sayWord(t, this, 0.8);"
                  class="pronounce-btn mt-2" title="Nghe câu ví dụ">
            <i class="bi bi-chat-quote-fill"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="d-flex gap-3 mt-4 align-items-center">
      <button class="btn btn-outline-secondary" onclick="prevCard()"><i class="bi bi-arrow-left"></i> Trước</button>
      <span class="text-muted" id="fcCounter">1 / <?= count($vocabArr) ?></span>
      <button class="btn btn-outline-primary" onclick="nextCard()">Tiếp <i class="bi bi-arrow-right"></i></button>
    </div>
    <?php
global $conn; if (isLoggedIn()): ?>
    <div class="d-flex gap-2 mt-3">
      <form method="POST" class="d-inline"><input type="hidden" name="vocab_id" id="markId1"><input type="hidden" name="status" value="learning"><button name="mark_vocab" class="btn btn-warning btn-sm"><i class="bi bi-bookmark me-1"></i>Đang học</button></form>
      <form method="POST" class="d-inline"><input type="hidden" name="vocab_id" id="markId2"><input type="hidden" name="status" value="learned"><button name="mark_vocab" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Đã thuộc</button></form>
    </div>
    <?php
global $conn; endif; ?>
  </div>
  <script>
  var vocab = <?= json_encode($vocabArr) ?>;
  var idx = 0, flipped = false;
  var lvColor = {basic:'#00BFA6',advanced:'#F9A826',premium:'#536DFE'};
  var lvLabel = {basic:'Cơ bản',advanced:'Nâng cao',premium:'Cấp cao'};
  function showCard() {
    var v = vocab[idx];
    document.getElementById('fcLevel').innerHTML = '<span class="badge rounded-pill" style="background:'+lvColor[v.level]+'">'+(lvLabel[v.level]||v.level)+'</span>';
    document.getElementById('fcWord').textContent  = v.word;
    document.getElementById('fcPron').textContent  = v.pronunciation || '';
    document.getElementById('fcTrans').textContent = v.translation   || '';
    document.getElementById('fcDef').textContent   = v.definition    || '';
    document.getElementById('fcEx').textContent    = v.example ? '"'+v.example+'"' : '';
    document.getElementById('fcCounter').textContent = (idx+1)+' / '+vocab.length;
    if (document.getElementById('markId1')) document.getElementById('markId1').value = v.id;
    if (document.getElementById('markId2')) document.getElementById('markId2').value = v.id;
    flipped = false;
    document.getElementById('fcInner').style.transform = '';
  }
  function flipCard()  { flipped=!flipped; document.getElementById('fcInner').style.transform=flipped?'rotateY(180deg)':''; }
  function nextCard()  { idx=(idx+1)%vocab.length; showCard(); }
  function prevCard()  { idx=(idx-1+vocab.length)%vocab.length; showCard(); }
  document.addEventListener('keydown', function(e) {
    if (e.key==='ArrowRight') nextCard();
    if (e.key==='ArrowLeft')  prevCard();
    if (e.key===' ')          { e.preventDefault(); flipCard(); }
    if (e.key==='p'||e.key==='P') sayWord(vocab[idx].word, document.getElementById('fcPronBtn'), 0.75);
  });
  showCard();
  </script>
<?php
global $conn; endif; ?>

<?php
global $conn; else: ?>
<!-- ══ LIST MODE ══ -->
<?php
global $conn; if (empty($vocabArr)): ?>
  <div class="text-center py-5">
    <i class="bi bi-search fs-1 text-muted"></i>
    <h5 class="mt-3 text-muted">Không tìm thấy từ vựng nào</h5>
    <a href="<?= BASE_URL ?>/tu-vung" class="btn btn-outline-primary mt-2">Xem tất cả</a>
  </div>
<?php
global $conn; else: ?>


  <div class="row g-3">
    <?php
global $conn; foreach ($vocabArr as $v):
      $locked  = !canAccessLevel($v['level']);
      $lvColor = ['basic'=>'#00BFA6','advanced'=>'#F9A826','premium'=>'#536DFE'];
      $lvLabel = ['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'];
      // Dùng json_encode để tránh lỗi quote
      $wordJs = json_encode((string)$v['word']);
      $exJs   = json_encode((string)($v['example'] ?? ''));
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="vocab-card h-100">
        <div class="vocab-card-header d-flex justify-content-between align-items-start gap-2">
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2">
              <?php
global $conn; if (!$locked): ?>
              <button class="pronounce-btn"
                      onclick="sayWord('<?= htmlspecialchars(addslashes((string)$v['word'])) ?>', this, 0.75)"
                      title="Nghe phát âm">
                <i class="bi bi-volume-up-fill"></i>
              </button>
              <?php
global $conn; endif; ?>
              <h6 class="vocab-word-big mb-0 <?= $locked?'blur-text':'' ?>">
                <?= $locked ? '●●●●●' : sanitize($v['word']) ?>
              </h6>
            </div>
            <?php
global $conn; if (!$locked && $v['pronunciation']): ?>
            <div class="text-muted small mt-1"><?= sanitize($v['pronunciation']) ?></div>
            <?php
global $conn; endif; ?>
          </div>
          <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
            <span class="badge rounded-pill"
                  style="background:<?= $lvColor[$v['level']]??'#ccc' ?>;font-size:.7rem">
              <?= $lvLabel[$v['level']]??$v['level'] ?>
            </span>
            <?php
global $conn; if (!empty($v['status'])): ?>
            <span class="badge <?= $v['status']==='learned'?'bg-success':($v['status']==='learning'?'bg-warning text-dark':'bg-secondary') ?>"
                  style="font-size:.65rem">
              <?= $v['status']==='learned'?'✓ Thuộc':($v['status']==='learning'?'Đang học':'Mới') ?>
            </span>
            <?php
global $conn; endif; ?>
          </div>
        </div>
        <div class="vocab-card-body">
          <?php
global $conn; if ($locked): ?>
            <p class="text-muted small mb-0">
              <a href="<?= BASE_URL ?>/nang-cap" class="fw-semibold" style="color:#536DFE">
                <i class="bi bi-lock me-1"></i>Nâng cấp để xem từ vựng cấp này
              </a>
            </p>
          <?php
global $conn; else: ?>
            <?php
global $conn; if ($v['category']): ?>
            <p class="mb-1">
              <span class="badge" style="background:#e8f4f0;color:#13547a;font-size:.72rem">
                <?php
$catVI2=['Academic'=>'Học thuật','Animals'=>'Động vật','Art'=>'Nghệ thuật','Body'=>'Cơ thể','Business'=>'Kinh doanh','Character'=>'Tính cách','Colors'=>'Màu sắc','Description'=>'Mô tả','Education'=>'Giáo dục','Environment'=>'Môi trường','Family'=>'Gia đình','Food'=>'Đồ ăn','Greetings'=>'Chào hỏi','Health'=>'Sức khỏe','Home'=>'Nhà cửa','IELTS'=>'IELTS','Law & Politics'=>'Luật & Chính trị','Literature'=>'Văn học','Medical'=>'Y tế','Nature'=>'Thiên nhiên','Occupations'=>'Nghề nghiệp','Science'=>'Khoa học','Shopping'=>'Mua sắm','Society'=>'Xã hội','Sports'=>'Thể thao','Technology'=>'Công nghệ','Time'=>'Thời gian','TOEIC'=>'TOEIC','Travel'=>'Du lịch','Weather'=>'Thời tiết','Work'=>'Công việc','General'=>'Chung'];
echo sanitize($catVI2[$v['category']] ?? $v['category']); ?>
              </span>
            </p>
            <?php
global $conn; endif; ?>
            <p class="small mb-1"><strong style="color:#13547a">Nghĩa:</strong> <?= sanitize($v['translation']) ?></p>
            <p class="small text-muted mb-1"><?= sanitize($v['definition']) ?></p>
            <?php
global $conn; if ($v['example']): ?>
            <div class="d-flex align-items-start gap-2 mb-2">
              <button class="pronounce-btn flex-shrink-0"
                      style="width:26px;height:26px;font-size:.7rem"
                      onclick="sayWord('<?= htmlspecialchars(addslashes((string)($v['example'] ?? ''))) ?>', this, 0.8)"
                      title="Nghe câu ví dụ">
                <i class="bi bi-chat-quote-fill"></i>
              </button>
              <p class="small fst-italic mb-0" style="color:#536DFE">
                "<?= sanitize($v['example']) ?>"
              </p>
            </div>
            <?php
global $conn; endif; ?>
            <?php
global $conn; if (isLoggedIn()): ?>
            <div class="d-flex gap-1 mt-2">
              <form method="POST" class="d-inline">
                <input type="hidden" name="vocab_id" value="<?= $v['id'] ?>">
                <input type="hidden" name="status" value="learning">
                <button name="mark_vocab" class="btn btn-outline-warning btn-sm" style="font-size:.7rem" title="Đang học"><i class="bi bi-bookmark"></i></button>
              </form>
              <form method="POST" class="d-inline">
                <input type="hidden" name="vocab_id" value="<?= $v['id'] ?>">
                <input type="hidden" name="status" value="learned">
                <button name="mark_vocab" class="btn btn-outline-success btn-sm" style="font-size:.7rem" title="Đã thuộc"><i class="bi bi-check2"></i></button>
              </form>
              <a href="<?= BASE_URL ?>/chat-ai?q=<?= urlencode('Cho tôi thêm ví dụ về từ: '.$v['word']) ?>"
                 class="btn btn-sm" style="font-size:.7rem;background:#e8f4f0;color:#13547a" title="Hỏi AI">
                <i class="bi bi-robot"></i>
              </a>
            </div>
            <?php
global $conn; endif; ?>
          <?php
global $conn; endif; ?>
        </div>
      </div>
    </div>
    <?php
global $conn; endforeach; ?>
  </div>

  <!-- Phân trang -->
  <?php
global $conn; if ($totalPages > 1): ?>
  <nav class="mt-4">
    <ul class="pagination justify-content-center flex-wrap gap-1">
      <?php
global $conn; if ($page > 1): ?>
        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">‹</a></li>
      <?php
global $conn; endif; ?>
      <?php
global $conn;
      $from=max(1,$page-2); $to=min($totalPages,$page+2);
      if ($from>1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php
global $conn; endif;
      for ($p=$from;$p<=$to;$p++): ?>
      <li class="page-item <?= $p===$page?'active':'' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"><?= $p ?></a>
      </li>
      <?php
global $conn; endfor;
      if ($to<$totalPages): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php
global $conn; endif; ?>
      <?php
global $conn; if ($page < $totalPages): ?>
        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">›</a></li>
      <?php
global $conn; endif; ?>
    </ul>
    <p class="text-center text-muted small mt-1">Trang <?= $page ?> / <?= $totalPages ?> · Tổng <?= $totalCount ?> từ</p>
  </nav>
  <?php
global $conn; endif; ?>

<?php
global $conn; endif; ?><?php /* end empty */ ?>
<?php
global $conn; endif; ?><?php /* end mode */ ?>

</div><!-- /container -->
<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
