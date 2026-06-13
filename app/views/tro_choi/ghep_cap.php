<?php
global $conn;
requireLogin();
$uid   = (int)$_SESSION['user_id'];
$capDo = in_array($_GET['cap_do']??'basic',['basic','advanced','premium']) ? ($_GET['cap_do']??'basic') : 'basic';
$soCap = in_array((int)($_GET['so_cap']??8),[8,12,16]) ? (int)($_GET['so_cap']??8) : 8;

// Lấy từ từ DB
$r = $conn->query("SELECT word, translation FROM vocabulary WHERE level='$capDo' AND word != '' AND translation != '' ORDER BY RAND() LIMIT $soCap");
$rawPairs = [];
while ($row = $r->fetch_assoc()) {
    $rawPairs[] = ['en' => trim($row['word']), 'vi' => trim($row['translation'])];
}

// Fallback nếu không đủ từ
$fallback = [
    ['en'=>'farmer','vi'=>'nông dân'],['en'=>'doctor','vi'=>'bác sĩ'],
    ['en'=>'school','vi'=>'trường học'],['en'=>'market','vi'=>'chợ'],
    ['en'=>'family','vi'=>'gia đình'],['en'=>'friend','vi'=>'bạn bè'],
    ['en'=>'orange','vi'=>'màu cam'],['en'=>'window','vi'=>'cửa sổ'],
    ['en'=>'bridge','vi'=>'cây cầu'],['en'=>'butter','vi'=>'bơ'],
    ['en'=>'garden','vi'=>'khu vườn'],['en'=>'finger','vi'=>'ngón tay'],
    ['en'=>'summer','vi'=>'mùa hè'],['en'=>'winter','vi'=>'mùa đông'],
    ['en'=>'mirror','vi'=>'gương'],['en'=>'bottle','vi'=>'chai'],
];
while (count($rawPairs) < $soCap) {
    $fb = $fallback[count($rawPairs) % count($fallback)];
    $rawPairs[] = $fb;
}
$rawPairs = array_slice($rawPairs, 0, $soCap);

// Tạo cards: mỗi cặp có pairId riêng, shuffle độc lập
$cards = [];
foreach ($rawPairs as $i => $p) {
    $cards[] = ['text' => $p['en'], 'pairId' => $i, 'type' => 'en'];
    $cards[] = ['text' => $p['vi'], 'pairId' => $i, 'type' => 'vi'];
}
shuffle($cards);

$best = $conn->query("SELECT score, JSON_UNQUOTE(JSON_EXTRACT(details,'$.time')) t, JSON_UNQUOTE(JSON_EXTRACT(details,'$.moves')) mv FROM game_scores WHERE user_id=$uid AND game_type='match' ORDER BY score DESC LIMIT 1")->fetch_assoc();
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
body{background:#f0f4f8;}
.mm-wrap{max-width:800px;margin:0 auto;padding:1.5rem 1rem 3rem;}
.mm-head{text-align:center;margin-bottom:1rem;}
.mm-title{font-size:1.9rem;font-weight:900;color:#111;}

/* Controls */
.ctrl-bar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;margin-bottom:.6rem;}
.stat-pill{display:inline-flex;align-items:center;gap:.4rem;background:#fff;border:1.5px solid #e2e8f0;border-radius:99px;padding:.3rem .85rem;font-size:.85rem;font-weight:700;color:#1e293b;}
.ctrl-btn{padding:.3rem .75rem;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;font-size:.78rem;font-weight:600;cursor:pointer;color:#475569;transition:all .15s;text-decoration:none;display:inline-block;}
.ctrl-btn.active,.ctrl-btn:hover{background:#1e293b;color:#fff;border-color:#1e293b;text-decoration:none;}

/* Timer bar */
.timer-bar{height:5px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin-bottom:1.1rem;}
.timer-fill{height:100%;background:linear-gradient(90deg,#22c55e,#3b82f6);border-radius:99px;transition:width 1s linear;}

/* Card grid */
.card-grid{display:grid;gap:10px;margin-bottom:1rem;}
.g8 {grid-template-columns:repeat(4,1fr);}
.g12{grid-template-columns:repeat(4,1fr);}
.g16{grid-template-columns:repeat(4,1fr);}
@media(max-width:500px){.g8,.g12,.g16{grid-template-columns:repeat(4,1fr);gap:7px;}}

/* Card */
.mc{border-radius:14px;cursor:pointer;user-select:none;min-height:80px;position:relative;}
.mc-inner{width:100%;height:100%;min-height:80px;border-radius:14px;transform-style:preserve-3d;transition:transform .4s cubic-bezier(.4,0,.2,1);position:relative;}
.mc.flip .mc-inner{transform:rotateY(180deg);}
.mc.matched .mc-inner{transform:rotateY(180deg);}
.mc-front,.mc-back{
  position:absolute;inset:0;border-radius:14px;
  backface-visibility:hidden;-webkit-backface-visibility:hidden;
  display:flex;align-items:center;justify-content:center;
  padding:.5rem .3rem;text-align:center;
  font-weight:700;min-height:80px;
}
.mc-front{
  background:linear-gradient(135deg,#1e3a8a,#3b82f6);
  color:#fff;font-size:1.6rem;
  border:2px solid rgba(255,255,255,.15);
}
.mc-back{
  transform:rotateY(180deg);
  background:#fff;border:2px solid #e2e8f0;
  font-size:clamp(.7rem,1.4vw,.92rem);
  line-height:1.3;color:#1e3a8a;
}
.mc-back.type-en{color:#1d4ed8;}
.mc-back.type-vi{color:#15803d;}
.mc.matched .mc-back{background:#f0fdf4;border-color:#4ade80;color:#15803d;}
.mc.wrong .mc-inner{animation:shake .4s ease;}
@keyframes shake{
  0%,100%{transform:rotateY(180deg) translateX(0)}
  25%{transform:rotateY(180deg) translateX(-8px)}
  75%{transform:rotateY(180deg) translateX(8px)}
}

/* Result overlay */
.overlay{position:fixed;inset:0;background:rgba(15,23,42,.6);display:flex;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(6px);}
.overlay.hide{display:none;}
.result-card{background:#fff;border-radius:24px;padding:2.5rem 2rem;text-align:center;max-width:360px;width:90%;box-shadow:0 32px 80px rgba(0,0,0,.25);}
.res-score{font-size:4rem;font-weight:900;color:#1e293b;line-height:1;}
.res-row{display:flex;gap:2rem;justify-content:center;margin:1rem 0 1.5rem;}
.res-item .num{font-size:1.6rem;font-weight:800;color:#1e293b;}
.res-item .lbl{font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;}
</style>

<div class="mm-wrap">
  <div class="mm-head">
    <div class="mm-title">🃏 Ghép Cặp</div>
    <p class="text-muted small mt-1">Ghép từ tiếng Anh ↔ tiếng Việt · Lật hết nhanh nhất thắng</p>
    <?php if($best): ?>
    <div style="font-size:.78rem;color:#16a34a">🏆 Kỷ lục: <?=$best['score']?> điểm · <?=$best['t']?>s · <?=$best['mv']?> lượt</div>
    <?php endif; ?>
  </div>

  <!-- Controls -->
  <div class="ctrl-bar">
    <div class="d-flex gap-2 flex-wrap">
      <span class="stat-pill">⏱ <span id="timerDisp">0s</span></span>
      <span class="stat-pill">✅ <span id="matchDisp">0</span>/<?=$soCap?></span>
      <span class="stat-pill">🎯 <span id="movesDisp">0</span> lượt</span>
    </div>
    <div class="d-flex gap-1">
      <?php foreach([8,12,16] as $p): ?>
      <a href="?so_cap=<?=$p?>&cap_do=<?=$capDo?>" class="ctrl-btn<?=$soCap==$p?' active':''?>"><?=$p?> cặp</a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="d-flex gap-1 mb-3 flex-wrap">
    <?php foreach(['basic'=>'🟢 Cơ bản','advanced'=>'🟡 Nâng cao','premium'=>'🔴 Cấp cao'] as $lv=>$lbl): ?>
    <a href="?so_cap=<?=$soCap?>&cap_do=<?=$lv?>" class="ctrl-btn<?=$capDo===$lv?' active':''?>"><?=$lbl?></a>
    <?php endforeach; ?>
  </div>

  <div class="timer-bar"><div class="timer-fill" id="timerFill" style="width:100%"></div></div>

  <!-- Card grid -->
  <div class="card-grid g<?=$soCap <= 8 ? 8 : ($soCap <= 12 ? 12 : 16)?>" id="grid">
    <?php foreach($cards as $idx => $c): ?>
    <div class="mc" data-pair="<?=$c['pairId']?>" data-type="<?=$c['type']?>" id="mc<?=$idx?>">
      <div class="mc-inner">
        <div class="mc-front">🂠</div>
        <div class="mc-back type-<?=$c['type']?>"><?=htmlspecialchars($c['text'], ENT_QUOTES)?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Result overlay -->
<div class="overlay hide" id="overlay">
  <div class="result-card">
    <div style="font-size:2.5rem;margin-bottom:.5rem" id="resStars">⭐</div>
    <div class="res-score" id="resScore">0</div>
    <div style="color:#94a3b8;font-size:.8rem;margin:.3rem 0 0">điểm</div>
    <div class="res-row">
      <div class="res-item"><div class="num" id="resTime">0s</div><div class="lbl">Thời gian</div></div>
      <div class="res-item"><div class="num" id="resMoves">0</div><div class="lbl">Lượt đi</div></div>
      <div class="res-item"><div class="num"><?=$soCap?></div><div class="lbl">Cặp từ</div></div>
    </div>
    <div class="d-flex gap-2 justify-content-center">
      <a href="?so_cap=<?=$soCap?>&cap_do=<?=$capDo?>" class="btn btn-primary px-4">🔄 Chơi lại</a>
      <a href="<?=BASE_URL?>/tro-choi" class="btn btn-outline-secondary">← Games</a>
    </div>
  </div>
</div>

<script>
const TOTAL = <?=$soCap?>;
let flipped = [], matched = 0, moves = 0, timer = 0, tInt = null, started = false, busy = false;

function startTimer() {
  if (started) return;
  started = true;
  tInt = setInterval(() => {
    timer++;
    document.getElementById('timerDisp').textContent = timer + 's';
    const pct = Math.max(0, 100 - timer / 180 * 100);
    const fill = document.getElementById('timerFill');
    fill.style.width = pct + '%';
    if (pct < 25) fill.style.background = 'linear-gradient(90deg,#ef4444,#f97316)';
    else if (pct < 50) fill.style.background = 'linear-gradient(90deg,#f59e0b,#22c55e)';
  }, 1000);
}

document.querySelectorAll('.mc').forEach(card => {
  card.addEventListener('click', () => {
    // Không cho click nếu đang xử lý, đã khớp, đã lật, hoặc đã có 2 thẻ lật
    if (busy) return;
    if (card.classList.contains('matched')) return;
    if (card.classList.contains('flip')) return;
    if (flipped.length >= 2) return;

    startTimer();
    card.classList.add('flip');
    flipped.push(card);

    if (flipped.length === 2) {
      busy = true;
      moves++;
      document.getElementById('movesDisp').textContent = moves;

      const [a, b] = flipped;
      const sameGroup = a.dataset.pair === b.dataset.pair;
      const diffType  = a.dataset.type !== b.dataset.type;

      if (sameGroup && diffType) {
        // ✅ Đúng cặp
        setTimeout(() => {
          a.classList.add('matched');
          b.classList.add('matched');
          flipped = [];
          matched++;
          busy = false;
          document.getElementById('matchDisp').textContent = matched;
          if (matched === TOTAL) finish();
        }, 500);
      } else {
        // ❌ Sai cặp
        [a, b].forEach(c => c.classList.add('wrong'));
        setTimeout(() => {
          [a, b].forEach(c => {
            c.classList.remove('flip', 'wrong');
          });
          flipped = [];
          busy = false;
        }, 1000);
      }
    }
  });
});

function finish() {
  clearInterval(tInt);
  const score = Math.max(100, 2000 - timer * 8 - moves * 5);
  fetch('<?=BASE_URL?>/api/luu-diem', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({type:'match', score, details:{time:timer, moves, pairs:TOTAL}})
  });
  document.getElementById('resScore').textContent = score;
  document.getElementById('resTime').textContent  = timer + 's';
  document.getElementById('resMoves').textContent = moves;
  document.getElementById('resStars').textContent = score > 1500 ? '🏆🏆🏆' : score > 1000 ? '⭐⭐⭐' : score > 500 ? '⭐⭐' : '⭐';
  document.getElementById('overlay').classList.remove('hide');
}
</script>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
