<?php
global $conn;
requireLogin();
$uid  = (int)$_SESSION['user_id'];
$name = sanitize($_SESSION['fullname']);

$phongId = (int)($_GET['phong'] ?? 0);
$phong   = null;
if ($phongId) {
    $r = $conn->query("SELECT d.*,u1.fullname c_name,u2.fullname o_name FROM duels d LEFT JOIN users u1 ON d.challenger=u1.id LEFT JOIN users u2 ON d.opponent=u2.id WHERE d.id=$phongId");
    $phong = $r ? $r->fetch_assoc() : null;
}

// Xác định vai trò
$isChallenger = $phong && $phong['challenger'] == $uid;
$isOpponent   = $phong && $phong['opponent']   == $uid;

// Câu hỏi - dùng cùng seed theo phong ID để cả 2 người có câu hỏi giống nhau
$cauHoi = [];
if ($phong && $phong['status'] === 'active') {
    srand($phongId * 137); // seed cố định theo phòng
    $allQ = [];
    $qr = $conn->query("SELECT e.*,l.title ten_bai FROM exercises e JOIN lessons l ON e.lesson_id=l.id WHERE e.option_a IS NOT NULL AND e.option_b IS NOT NULL");
    while ($q = $qr->fetch_assoc()) $allQ[] = $q;
    shuffle($allQ); // shuffle theo seed
    $cauHoi = array_slice($allQ, 0, 10);
    srand(); // reset random
}

// Top duel
$topDuel = [];
$td = $conn->query("SELECT u.fullname, COUNT(*) wins FROM duels d JOIN users u ON d.winner_id=u.id WHERE d.status='finished' GROUP BY d.winner_id ORDER BY wins DESC LIMIT 5");
while ($r2 = $td->fetch_assoc()) $topDuel[] = $r2;
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
body{background:#0f0f1a;}
.dw{max-width:820px;margin:0 auto;padding:1.5rem 1rem 3rem;}
.dt{text-align:center;font-size:2rem;font-weight:900;color:#f1f5f9;margin-bottom:.3rem;}
.ds{text-align:center;color:#64748b;font-size:.85rem;margin-bottom:1.5rem;}

/* Lobby */
.lobby-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.5rem;}
@media(max-width:540px){.lobby-grid{grid-template-columns:1fr;}}
.lc{background:#1a1a2e;border:1.5px solid #2d2d44;border-radius:20px;padding:2rem 1.5rem;text-align:center;}
.lc-icon{font-size:2.8rem;margin-bottom:.75rem;}
.lc-h{font-size:1.1rem;font-weight:800;color:#f1f5f9;margin-bottom:.4rem;}
.lc-p{font-size:.82rem;color:#64748b;margin-bottom:1.2rem;}
.inp{background:#111827;border:1.5px solid #374151;border-radius:10px;color:#f1f5f9;padding:.55rem .9rem;font-size:.9rem;width:100%;margin-bottom:.5rem;}
.inp::placeholder{color:#4b5563;}
.btn-c{background:linear-gradient(135deg,#e11d48,#9f1239);border:none;border-radius:10px;color:#fff;padding:.7rem 2rem;font-weight:800;font-size:1rem;cursor:pointer;width:100%;}
.btn-j{background:linear-gradient(135deg,#6366f1,#4338ca);border:none;border-radius:10px;color:#fff;padding:.7rem 2rem;font-weight:800;font-size:1rem;cursor:pointer;width:100%;}

/* Arena header */
.arena{background:linear-gradient(135deg,#1a1a2e,#16213e);border:1.5px solid #2d2d44;border-radius:20px;padding:1.5rem;margin-bottom:1.2rem;}
.ap{display:grid;grid-template-columns:1fr auto 1fr;gap:.75rem;align-items:center;}
.pbox{background:rgba(255,255,255,.04);border-radius:14px;padding:1rem;text-align:center;}
.pbox.me{border:1.5px solid #6366f1;}
.pbox.opp{border:1.5px solid #2d2d44;}
.pbox.winning{border-color:#22c55e;}
.pbox.losing{border-color:#ef4444;}
.av{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#818cf8);display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:900;color:#fff;margin-bottom:.5rem;}
.av.opp{background:linear-gradient(135deg,#f59e0b,#d97706);}
.pname{font-size:.82rem;font-weight:700;color:#94a3b8;}
.pscore{font-size:2.5rem;font-weight:900;color:#f1f5f9;line-height:1.1;transition:all .3s;}
.pscore.up{color:#4ade80;transform:scale(1.15);}
.vs{font-size:1.5rem;font-weight:900;color:#f59e0b;text-align:center;}
.cd{font-size:2rem;font-weight:900;color:#f59e0b;min-width:44px;text-align:center;}

/* Room code */
.rc{background:rgba(99,102,241,.1);border:1.5px solid #6366f1;border-radius:12px;padding:.6rem 1.2rem;display:inline-flex;align-items:center;gap:.6rem;cursor:pointer;margin-top:.75rem;}
.rc-code{font-size:1.4rem;font-weight:900;letter-spacing:.15em;color:#818cf8;}

/* Question */
.qw{background:#1a1a2e;border:1.5px solid #2d2d44;border-radius:16px;overflow:hidden;}
.qh{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.2rem;border-bottom:1px solid #2d2d44;background:rgba(255,255,255,.02);}
.qpb{height:4px;background:#111827;}
.qpf{height:100%;background:linear-gradient(90deg,#6366f1,#f59e0b);transition:width .4s;}
.qb{padding:1.4rem 1.2rem;}
.qt{font-size:1.05rem;font-weight:700;color:#f1f5f9;margin-bottom:1.2rem;line-height:1.5;}
.qo{display:block;width:100%;margin-bottom:.6rem;padding:.75rem 1rem;border-radius:10px;border:1.5px solid #2d2d44;background:#111827;color:#cbd5e1;text-align:left;font-size:.9rem;font-weight:600;cursor:pointer;transition:all .15s;}
.qo:hover:not(:disabled){border-color:#6366f1;background:rgba(99,102,241,.12);color:#818cf8;}
.qo.correct{border-color:#22c55e!important;background:rgba(34,197,94,.12)!important;color:#4ade80!important;}
.qo.wrong{border-color:#ef4444!important;background:rgba(239,68,68,.1)!important;color:#f87171!important;}
.qbadge{display:inline-flex;align-items:center;gap:.35rem;background:rgba(99,102,241,.15);border-radius:8px;padding:.2rem .6rem;font-size:.75rem;color:#818cf8;font-weight:700;}

/* Waiting */
.wait{text-align:center;padding:2.5rem 1rem;}
.spin{width:48px;height:48px;border:4px solid #2d2d44;border-top-color:#6366f1;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 1rem;}
@keyframes spin{to{transform:rotate(360deg)}}

/* Result */
.result{text-align:center;padding:2rem 1rem;}

/* LB */
.lb{background:#1a1a2e;border:1.5px solid #2d2d44;border-radius:14px;padding:1rem 1.25rem;margin-top:1rem;}
.lbr{display:flex;align-items:center;gap:.75rem;padding:.4rem 0;border-bottom:1px solid #111827;}
.lbr:last-child{border:none;}
</style>

<div class="dw">
  <div class="dt">⚔️ Đối Kháng 1v1</div>
  <div class="ds">Thách đấu bạn bè · Câu hỏi như nhau · Ai đúng nhiều hơn thắng</div>

<?php if (!$phong): ?>
<!-- ── LOBBY ── -->
<div class="lobby-grid">
  <div class="lc">
    <div class="lc-icon">⚔️</div>
    <div class="lc-h">Tạo phòng mới</div>
    <div class="lc-p">Tạo phòng và chia sẻ ID cho bạn bè vào thách đấu</div>
    <form method="POST"><button name="tao_phong" class="btn-c">Tạo phòng Duel</button></form>
  </div>
  <div class="lc">
    <div class="lc-icon">🚪</div>
    <div class="lc-h">Tham gia phòng</div>
    <div class="lc-p">Nhập ID phòng bạn bè chia sẻ</div>
    <form method="POST">
      <input type="number" name="phong_id" class="inp" placeholder="Nhập ID phòng..." required min="1">
      <button name="tham_gia" class="btn-j">Vào phòng</button>
    </form>
  </div>
</div>
<?php if($topDuel): ?>
<div class="lb">
  <div style="font-size:.9rem;font-weight:800;color:#f1f5f9;margin-bottom:.75rem">🏆 Bảng xếp hạng</div>
  <?php $med=['🥇','🥈','🥉','4️⃣','5️⃣'];
  foreach($topDuel as $i=>$t): ?>
  <div class="lbr">
    <span style="font-size:1rem;width:24px"><?=$med[$i]??($i+1)?></span>
    <span style="flex:1;font-size:.82rem;color:#cbd5e1;font-weight:600"><?=sanitize($t['fullname'])?></span>
    <span style="font-size:.8rem;color:#818cf8;font-weight:700"><?=$t['wins']?> thắng</span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif ($phong['status'] === 'waiting'): ?>
<!-- ── WAITING ── -->
<div class="arena">
  <div class="ap">
    <div class="pbox me">
      <div class="av"><?=strtoupper(substr($phong['c_name'],0,1))?></div>
      <div class="pname"><?=sanitize($phong['c_name'])?></div>
      <div style="font-size:.7rem;color:#4b5563;margin-top:.15rem">Người tạo phòng</div>
    </div>
    <div class="vs">VS<br><span style="font-size:.8rem;color:#4b5563">#<?=$phongId?></span></div>
    <div class="pbox opp">
      <div class="av opp" style="background:rgba(255,255,255,.08);font-size:1.5rem">?</div>
      <div class="pname" style="color:#4b5563">Chờ đối thủ…</div>
    </div>
  </div>
  <div class="text-center mt-3">
    <div class="rc" onclick="copyCode()">
      <span style="color:#64748b;font-size:.8rem">ID phòng:</span>
      <span class="rc-code"><?=$phongId?></span>
      <span style="color:#64748b;font-size:.8rem">📋 Sao chép</span>
    </div>
  </div>
</div>
<div class="wait">
  <div class="spin"></div>
  <div style="color:#f1f5f9;font-weight:700;font-size:1.05rem">Đang chờ đối thủ tham gia…</div>
  <div style="color:#64748b;font-size:.8rem;margin:.5rem 0 1.2rem">Tự động kiểm tra mỗi 3 giây</div>
  <button onclick="location.reload()" class="btn btn-outline-light btn-sm">🔄 Kiểm tra ngay</button>
</div>
<script>setInterval(()=>location.reload(),3000);</script>

<?php elseif ($phong['status'] === 'active' && ($isChallenger || $isOpponent) && !empty($cauHoi)): ?>
<!-- ── ARENA ── -->
<?php
$myScore   = $isChallenger ? $phong['challenger_score'] : $phong['opponent_score'];
$oppScore  = $isChallenger ? $phong['opponent_score']   : $phong['challenger_score'];
$myName    = $isChallenger ? $phong['c_name'] : $phong['o_name'];
$oppName   = $isChallenger ? $phong['o_name'] : $phong['c_name'];
?>
<div class="arena" id="arenaBox">
  <div class="ap">
    <div class="pbox me" id="myBox">
      <div class="av"><?=strtoupper(substr($myName,0,1))?></div>
      <div class="pname">Bạn (<?=sanitize($myName)?>)</div>
      <div class="pscore" id="myScore">0</div>
    </div>
    <div style="text-align:center">
      <div class="vs">VS</div>
      <div class="cd" id="cdDisp">15</div>
      <div style="font-size:.72rem;color:#4b5563;margin-top:.2rem">giây</div>
    </div>
    <div class="pbox opp" id="oppBox">
      <div class="av opp"><?=strtoupper(substr($oppName??'?',0,1))?></div>
      <div class="pname"><?=sanitize($oppName??'Đối thủ')?></div>
      <div class="pscore" id="oppScore">0</div>
    </div>
  </div>
</div>

<div class="qw" id="qWrap">
  <div class="qh">
    <span class="qbadge" id="qNum">Câu 1/<?=count($cauHoi)?></span>
    <span style="color:#64748b;font-size:.78rem">✅ Đúng: <span id="correctCnt">0</span></span>
  </div>
  <div class="qpb"><div class="qpf" id="qProg" style="width:0%"></div></div>
  <div class="qb">
    <div class="qt" id="qText">Đang tải câu hỏi…</div>
    <div id="qOpts"></div>
    <div style="font-size:.73rem;color:#4b5563;margin-top:.6rem" id="qSrc"></div>
  </div>
</div>

<script>
const PHONG_ID = <?=$phongId?>;
const IS_CHALLENGER = <?=$isChallenger?'true':'false'?>;
const QS = <?=json_encode(array_values($cauHoi))?>;
const BASE = '<?=BASE_URL?>';
let qi=0, correct=0, myScoreVal=0, cdTimer=null, pollTimer=null, finished=false;

// ── Score updater ──────────────────────────
function updateScoreDisplay(my, opp) {
  const mEl = document.getElementById('myScore');
  const oEl = document.getElementById('oppScore');
  if (mEl.textContent != my) { mEl.textContent = my; mEl.classList.add('up'); setTimeout(()=>mEl.classList.remove('up'),400); }
  oEl.textContent = opp;
  // Border color
  document.getElementById('myBox').className  = 'pbox me'  + (my>opp?' winning':my<opp?' losing':'');
  document.getElementById('oppBox').className = 'pbox opp' + (opp>my?' winning':opp<my?' losing':'');
}

function pushScore(done=false) {
  fetch(BASE+'/api/duel-diem', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({phong_id:PHONG_ID, diem:myScoreVal, xong:done})
  });
}

// Poll điểm đối thủ mỗi 2s
function pollOpp() {
  fetch(BASE+'/api/duel-trang-thai?phong='+PHONG_ID)
    .then(r=>r.json())
    .then(d=>{
      if(!d || d.ok===false) return;
      const myS  = IS_CHALLENGER ? (d.challenger_score||0) : (d.opponent_score||0);
      const oppS = IS_CHALLENGER ? (d.opponent_score||0)   : (d.challenger_score||0);
      updateScoreDisplay(myS, oppS);
    }).catch(()=>{});
}

// ── Question logic ──────────────────────────
function showQ() {
  if (qi >= QS.length) { endDuel(); return; }
  const q = QS[qi];
  document.getElementById('qNum').textContent  = 'Câu '+(qi+1)+'/'+QS.length;
  document.getElementById('qProg').style.width = (qi/QS.length*100)+'%';
  document.getElementById('qText').textContent = q.question || q.question_text || '';
  document.getElementById('qSrc').textContent  = q.ten_bai ? '📚 '+q.ten_bai : '';
  const opts = document.getElementById('qOpts'); opts.innerHTML='';
  ['a','b','c','d'].forEach(k => {
    const v = q['option_'+k]; if(!v) return;
    const btn = document.createElement('button');
    btn.className='qo'; btn.textContent=k.toUpperCase()+'. '+v;
    btn.onclick = () => answer(k.toUpperCase(), (q.correct_answer||'').toUpperCase(), btn, opts);
    opts.appendChild(btn);
  });
  // Countdown 15s
  let cd = 15;
  document.getElementById('cdDisp').textContent = cd;
  document.getElementById('cdDisp').style.color = '#f59e0b';
  clearInterval(cdTimer);
  cdTimer = setInterval(() => {
    cd--;
    document.getElementById('cdDisp').textContent = cd;
    if (cd <= 5) document.getElementById('cdDisp').style.color = '#ef4444';
    if (cd <= 0) { clearInterval(cdTimer); nextQ(); }
  }, 1000);
}

function answer(chosen, correct_ans, btn, optsEl) {
  clearInterval(cdTimer);
  optsEl.querySelectorAll('.qo').forEach(b => b.disabled=true);
  if (chosen === correct_ans) {
    btn.classList.add('correct');
    correct++;
    myScoreVal += 100;
    document.getElementById('correctCnt').textContent = correct;
    updateScoreDisplay(myScoreVal, parseInt(document.getElementById('oppScore').textContent)||0);
    pushScore(false);
  } else {
    btn.classList.add('wrong');
    optsEl.querySelectorAll('.qo').forEach(b => {
      if (b.textContent.startsWith(correct_ans+'.')) b.classList.add('correct');
    });
  }
  setTimeout(nextQ, 1200);
}

function nextQ() { qi++; showQ(); }

function endDuel() {
  if (finished) return;
  finished = true;
  clearInterval(cdTimer);
  clearInterval(pollTimer);
  pushScore(true);

  // Wait for API to settle then show result
  setTimeout(() => {
    fetch(BASE+'/api/duel-trang-thai?phong='+PHONG_ID)
      .then(r=>r.json())
      .then(d=>{
        const myFinal  = IS_CHALLENGER ? (d.challenger_score||0) : (d.opponent_score||0);
        const oppFinal = IS_CHALLENGER ? (d.opponent_score||0)   : (d.challenger_score||0);
        const won = myFinal > oppFinal;
        const draw = myFinal === oppFinal;
        updateScoreDisplay(myFinal, oppFinal);
        document.getElementById('qWrap').innerHTML = `
          <div class="result">
            <div style="font-size:3.5rem;margin-bottom:.6rem">${won?'🏆':draw?'🤝':'😢'}</div>
            <div style="font-size:1.6rem;font-weight:900;color:#f1f5f9">${won?'Bạn thắng!':draw?'Hòa!':'Bạn thua!'}</div>
            <div style="color:#64748b;font-size:.85rem;margin:.4rem 0 .5rem">${correct}/${QS.length} câu đúng</div>
            <div style="font-size:2rem;font-weight:900;color:#818cf8;margin-bottom:1.5rem">${myFinal} điểm</div>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
              <a href="${BASE}/doi-khang" class="btn btn-primary px-4">⚔️ Duel mới</a>
              <a href="${BASE}/tro-choi" class="btn btn-outline-secondary">← Games</a>
            </div>
          </div>`;
      });
  }, 1500);
}

// Start
showQ();
pollTimer = setInterval(pollOpp, 2000);
</script>

<?php elseif ($phong['status'] === 'finished'): ?>
<!-- ── FINISHED ── -->
<?php
$myFinalScore  = $isChallenger ? $phong['challenger_score'] : $phong['opponent_score'];
$oppFinalScore = $isChallenger ? $phong['opponent_score']   : $phong['challenger_score'];
$myName2  = $isChallenger ? $phong['c_name'] : $phong['o_name'];
$oppName2 = $isChallenger ? $phong['o_name'] : $phong['c_name'];
$won = $phong['winner_id'] == $uid;
$draw = $phong['challenger_score'] == $phong['opponent_score'];
?>
<div class="arena">
  <div class="ap">
    <div class="pbox me <?=$won&&!$draw?'winning':(!$won&&!$draw?'losing':'')?>">
      <div class="av"><?=strtoupper(substr($myName2,0,1))?></div>
      <div class="pname">Bạn (<?=sanitize($myName2)?>)</div>
      <div class="pscore"><?=$myFinalScore?></div>
    </div>
    <div class="vs">VS</div>
    <div class="pbox opp <?=!$won&&!$draw?'winning':($won&&!$draw?'losing':'')?>">
      <div class="av opp"><?=strtoupper(substr($oppName2??'?',0,1))?></div>
      <div class="pname"><?=sanitize($oppName2??'Đối thủ')?></div>
      <div class="pscore"><?=$oppFinalScore?></div>
    </div>
  </div>
</div>
<div class="result">
  <div style="font-size:3.5rem;margin-bottom:.6rem"><?=$won?'🏆':($draw?'🤝':'😢')?></div>
  <div style="font-size:1.6rem;font-weight:900;color:#f1f5f9"><?=$won?'Bạn thắng!':($draw?'Hòa!':'Bạn thua!')?></div>
  <div style="color:#64748b;font-size:.85rem;margin:.5rem 0 1.5rem">Trận đấu kết thúc</div>
  <div class="d-flex gap-2 justify-content-center flex-wrap">
    <a href="<?=BASE_URL?>/doi-khang" class="btn btn-primary px-4">⚔️ Duel mới</a>
    <a href="<?=BASE_URL?>/tro-choi" class="btn btn-outline-secondary">← Games</a>
  </div>
</div>

<?php else: ?>
<div class="wait">
  <div style="font-size:3rem">🚫</div>
  <div style="color:#f1f5f9;font-weight:700;margin:.75rem 0">Không tìm thấy phòng hoặc bạn không phải thành viên</div>
  <a href="<?=BASE_URL?>/doi-khang" class="btn btn-primary mt-2">← Về Lobby</a>
</div>
<?php endif; ?>
</div>

<script>
function copyCode(){
  navigator.clipboard?.writeText('<?=$phongId?>').then(()=>{
    const rc=document.querySelector('.rc');
    if(rc){const o=rc.innerHTML;rc.innerHTML='<span style="color:#4ade80;font-weight:700">✅ Đã sao chép ID: <?=$phongId?></span>';setTimeout(()=>rc.innerHTML=o,2000);}
  });
}
</script>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
