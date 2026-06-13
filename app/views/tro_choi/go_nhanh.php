<?php
global $conn;
requireLogin();
$uid = (int)$_SESSION['user_id'];

// Danh sách đoạn văn tiếng Anh thuần để luyện gõ
$passages = [
    "The quick brown fox jumps over the lazy dog near the river bank on a bright sunny morning.",
    "Practice makes perfect when you dedicate time to learning new skills every single day without giving up.",
    "Reading books regularly helps you expand your vocabulary and improve your understanding of the English language.",
    "Technology has changed the way people communicate, work, and interact with each other around the world.",
    "Success comes to those who work hard, stay focused, and never stop believing in their own abilities.",
    "Learning a new language opens many doors and allows you to connect with people from different cultures.",
    "The best way to improve your typing speed is to practice consistently and focus on accuracy first.",
    "Every morning is a new opportunity to grow, learn something new, and become a better version of yourself.",
    "Good communication skills are essential in both personal and professional life in today's competitive world.",
    "Music has the power to bring people together and express emotions that words alone cannot describe.",
    "Traveling to new places broadens your perspective and helps you appreciate the diversity of human culture.",
    "Regular exercise not only keeps your body healthy but also improves your mental focus and well being.",
    "The internet has made information accessible to everyone regardless of where they live or who they are.",
    "Kindness costs nothing but means everything to the person who receives it at the right moment.",
    "Hard work and determination are the two most important ingredients for achieving any goal in life.",
    "A positive attitude can transform any difficult situation into a valuable learning experience that builds character.",
    "Friendship is one of the greatest gifts in life and should be nurtured with care and honesty.",
    "Critical thinking allows us to analyze problems carefully and find creative solutions that others might overlook.",
    "The ocean covers more than seventy percent of the Earth's surface and is home to countless species.",
    "Confidence grows when you step outside your comfort zone and challenge yourself to try new things daily.",
];
$doan = $passages[array_rand($passages)];

// Top scores
$tops = $conn->query("SELECT u.fullname, MAX(gs.score) best, JSON_UNQUOTE(JSON_EXTRACT(gs.details,'$.wpm')) wpm FROM game_scores gs JOIN users u ON gs.user_id=u.id WHERE gs.game_type='typing' GROUP BY gs.user_id ORDER BY best DESC LIMIT 5");
$topRows = [];
while ($t = $tops->fetch_assoc()) $topRows[] = $t;

$myBest = $diemCao ?? $conn->query("SELECT score, JSON_UNQUOTE(JSON_EXTRACT(details,'$.wpm')) wpm FROM game_scores WHERE user_id=$uid AND game_type='typing' ORDER BY score DESC LIMIT 1")->fetch_assoc();
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
body{background:#0f172a;}
.tr-wrap{max-width:860px;margin:0 auto;padding:1.5rem 1rem 3rem;}
.tr-head{text-align:center;margin-bottom:1.5rem;}
.tr-title{font-size:2rem;font-weight:900;letter-spacing:-.04em;color:#f1f5f9;}
.tr-sub{color:#64748b;font-size:.85rem;margin-top:.3rem;}
/* Stats row */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.5rem;}
.stat-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:1rem;text-align:center;}
.stat-big{font-size:2rem;font-weight:900;color:#38bdf8;line-height:1;}
.stat-lbl{font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-top:.25rem;}
/* Typing area */
.ty-box{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:1.5rem;margin-bottom:1rem;cursor:text;position:relative;}
.ty-text{font-size:1.2rem;line-height:1.9;font-family:'Courier New',monospace;color:#64748b;user-select:none;word-break:break-word;}
.ty-char{transition:color .05s;}
.ty-char.correct{color:#4ade80;}
.ty-char.wrong{color:#f87171;background:rgba(248,113,113,.12);border-radius:2px;}
.ty-char.cursor{border-left:2.5px solid #38bdf8;animation:blink 1s infinite;}
@keyframes blink{0%,100%{border-color:#38bdf8}50%{border-color:transparent}}
.ty-input{position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;}
.ty-hint{color:#475569;font-size:.8rem;margin-top:.75rem;text-align:center;}
/* Progress bar */
.prog-bar{height:5px;background:#1e293b;border-radius:99px;overflow:hidden;margin-bottom:1rem;}
.prog-fill{height:100%;background:linear-gradient(90deg,#38bdf8,#6366f1);border-radius:99px;transition:width .15s;}
/* Leaderboard */
.lb-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:1rem 1.25rem;}
.lb-title{font-size:.9rem;font-weight:700;color:#f1f5f9;margin-bottom:.75rem;}
.lb-row{display:flex;align-items:center;gap:.75rem;padding:.4rem 0;border-bottom:1px solid #1e293b;}
.lb-row:last-child{border:none;}
.lb-rank{font-size:1.1rem;width:28px;text-align:center;}
.lb-name{flex:1;font-size:.85rem;color:#cbd5e1;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.lb-wpm{font-size:.8rem;color:#38bdf8;font-weight:700;}
/* Mode selector */
.mode-btns{display:flex;gap:.5rem;justify-content:center;margin-bottom:1rem;flex-wrap:wrap;}
.mode-btn{padding:.4rem .9rem;border-radius:8px;border:1.5px solid #334155;background:transparent;color:#94a3b8;font-size:.82rem;font-weight:600;cursor:pointer;transition:all .15s;}
.mode-btn.active{background:#38bdf8;border-color:#38bdf8;color:#0f172a;}
/* Result */
.result-panel{background:#1e293b;border:1.5px solid #334155;border-radius:16px;padding:1.5rem;text-align:center;margin-top:1rem;display:none;}
.wpm-display{font-size:4rem;font-weight:900;color:#38bdf8;line-height:1;}
.acc-display{font-size:1.4rem;font-weight:700;color:#4ade80;}
</style>

<div class="tr-wrap">
  <div class="tr-head">
    <div class="tr-title">⌨️ Gõ Nhanh</div>
    <div class="tr-sub">Gõ nhanh · Chính xác · Đo WPM</div>
    <?php if($myBest): ?><div style="color:#38bdf8;font-size:.8rem;margin-top:.4rem">🏆 Kỷ lục của bạn: <?=$myBest['wpm']??0?> WPM · <?=$myBest['score']?> điểm</div><?php endif; ?>
  </div>

  <div class="stat-grid">
    <div class="stat-card"><div class="stat-big" id="wpmDisp">0</div><div class="stat-lbl">WPM</div></div>
    <div class="stat-card"><div class="stat-big" id="accDisp">100%</div><div class="stat-lbl">Chính xác</div></div>
    <div class="stat-card"><div class="stat-big" id="timeDisp">0s</div><div class="stat-lbl">Thời gian</div></div>
    <div class="stat-card"><div class="stat-big" id="charDisp">0</div><div class="stat-lbl">Ký tự</div></div>
  </div>

  <div class="prog-bar"><div class="prog-fill" id="progFill" style="width:0%"></div></div>

  <div class="mode-btns">
    <button class="mode-btn active" onclick="setMode(this,'normal')">⏱ Thường</button>
    <button class="mode-btn" onclick="setMode(this,'timed60')">⚡ 60 giây</button>
    <button class="mode-btn" onclick="setMode(this,'timed30')">🔥 30 giây</button>
  </div>

  <div class="ty-box" id="tyBox" onclick="document.getElementById('tyInput').focus()">
    <div class="ty-text" id="tyText"></div>
    <input type="text" id="tyInput" class="ty-input" autocomplete="off" spellcheck="false">
    <div class="ty-hint" id="tyHint">Nhấn vào đây hoặc bắt đầu gõ để bắt đầu…</div>
  </div>

  <div class="result-panel" id="resultPanel">
    <div style="color:#64748b;font-size:.8rem;margin-bottom:.5rem">Kết quả</div>
    <div class="wpm-display" id="finalWpm">0</div>
    <div style="color:#64748b;font-size:.9rem;margin:.3rem 0">từ / phút</div>
    <div class="acc-display" id="finalAcc">100%</div>
    <div style="color:#64748b;font-size:.8rem;margin-bottom:1rem">độ chính xác</div>
    <div style="font-size:1.5rem;font-weight:900;color:#f1f5f9" id="finalScore">0 điểm</div>
    <div class="d-flex gap-2 justify-content-center mt-3">
      <button onclick="restart()" class="btn btn-primary px-4">🔄 Thử lại</button>
      <a href="<?=BASE_URL?>/tro-choi" class="btn btn-outline-secondary">← Games</a>
    </div>
  </div>

  <?php if($topRows): ?>
  <div class="lb-card mt-3">
    <div class="lb-title">🏆 Bảng xếp hạng Gõ Nhanh</div>
    <?php $medals=['🥇','🥈','🥉','4️⃣','5️⃣'];
    foreach($topRows as $i=>$t): ?>
    <div class="lb-row">
      <span class="lb-rank"><?=$medals[$i]??($i+1)?></span>
      <span class="lb-name"><?=sanitize($t['fullname'])?></span>
      <span class="lb-wpm"><?=$t['wpm']?> WPM</span>
      <span style="font-size:.75rem;color:#64748b;margin-left:.5rem"><?=$t['best']?> điểm</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
let TEXT=<?=json_encode($doan)?>;
let chars=[...TEXT],pos=0,errors=0,startTime=null,tInt=null,done=false,mode='normal',timeLeft=60;

function setMode(btn,m){
  document.querySelectorAll('.mode-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  mode=m;restart();
}

function buildDisplay(){
  document.getElementById('tyText').innerHTML=chars.map((c,i)=>`<span class="ty-char" id="ch${i}">${c===' '?'&nbsp;':c}</span>`).join('');
  moveCursor(0);
  document.getElementById('tyHint').style.display='';
}

function moveCursor(idx){
  document.querySelectorAll('.ty-char.cursor').forEach(e=>e.classList.remove('cursor'));
  const el=document.getElementById('ch'+idx);
  if(el)el.classList.add('cursor');
}

const inp=document.getElementById('tyInput');
inp.addEventListener('input',e=>{
  if(done)return;
  const val=inp.value;if(!val.length)return;
  inp.value='';
  if(!startTime){
    startTime=Date.now();
    document.getElementById('tyHint').style.display='none';
    if(mode==='timed60'||mode==='timed30'){
      timeLeft=mode==='timed60'?60:30;
      tInt=setInterval(()=>{timeLeft--;document.getElementById('timeDisp').textContent=timeLeft+'s';updateStats();if(timeLeft<=0)finish();},1000);
    } else {
      tInt=setInterval(updateStats,250);
    }
  }
  const typed=val[val.length-1];
  if(pos>=chars.length)return;
  const el=document.getElementById('ch'+pos);
  if(typed===chars[pos]){el.classList.add('correct');}
  else{el.classList.add('wrong');errors++;}
  pos++;
  moveCursor(pos);
  document.getElementById('charDisp').textContent=pos;
  document.getElementById('progFill').style.width=(pos/chars.length*100)+'%';
  updateStats();
  if(pos>=chars.length&&mode==='normal')finish();
});

document.getElementById('tyBox').addEventListener('click',()=>inp.focus());

function updateStats(){
  if(!startTime)return;
  const elapsed=mode==='normal'?(Date.now()-startTime)/1000:
                mode==='timed60'?60-timeLeft:30-timeLeft;
  const wpm=elapsed>0?Math.round((pos/5)/(elapsed/60)):0;
  const acc=pos>0?Math.round((1-errors/pos)*100):100;
  document.getElementById('wpmDisp').textContent=wpm;
  document.getElementById('accDisp').textContent=acc+'%';
  if(mode==='normal')document.getElementById('timeDisp').textContent=Math.round(elapsed)+'s';
}

function finish(){
  clearInterval(tInt);done=true;
  const elapsed=mode==='normal'?(Date.now()-startTime)/1000:
                mode==='timed60'?60:30;
  const wpm=elapsed>0?Math.round((pos/5)/(elapsed/60)):0;
  const acc=pos>0?Math.round((1-errors/pos)*100):100;
  const score=Math.round(wpm*(acc/100)*10);
  document.getElementById('finalWpm').textContent=wpm;
  document.getElementById('finalAcc').textContent=acc+'%';
  document.getElementById('finalScore').textContent=score+' điểm';
  document.getElementById('resultPanel').style.display='block';
  document.getElementById('wpmDisp').textContent=wpm;
  document.getElementById('accDisp').textContent=acc+'%';
  fetch('<?=BASE_URL?>/api/luu-diem',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'typing',score,details:{wpm,acc,time:Math.round(elapsed),mode}})});
}

function restart(){
  clearInterval(tInt);pos=0;errors=0;startTime=null;done=false;timeLeft=mode==='timed30'?30:60;
  document.getElementById('progFill').style.width='0%';
  document.getElementById('wpmDisp').textContent='0';
  document.getElementById('accDisp').textContent='100%';
  document.getElementById('timeDisp').textContent='0s';
  document.getElementById('charDisp').textContent='0';
  document.getElementById('resultPanel').style.display='none';
  // Fetch new text
  fetch(location.href,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(()=>{
    chars=[...TEXT];buildDisplay();inp.focus();
  }).catch(()=>{chars=[...TEXT];buildDisplay();inp.focus();});
}

buildDisplay();
setTimeout(()=>inp.focus(),300);
</script>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
