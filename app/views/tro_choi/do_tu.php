<?php
global $conn;
requireLogin();
$uid   = (int)$_SESSION['user_id'];

// Đảm bảo có từ 5 chữ cái
// Danh sách từ tiếng Anh 5 chữ cái dự phòng
$fallbackWords = [
    ['word'=>'table','translation'=>'cái bàn'],['word'=>'chair','translation'=>'cái ghế'],
    ['word'=>'heart','translation'=>'trái tim'],['word'=>'water','translation'=>'nước'],
    ['word'=>'light','translation'=>'ánh sáng'],['word'=>'house','translation'=>'ngôi nhà'],
    ['word'=>'music','translation'=>'âm nhạc'],['word'=>'beach','translation'=>'bãi biển'],
    ['word'=>'plant','translation'=>'cây cối'],['word'=>'clock','translation'=>'đồng hồ'],
    ['word'=>'bread','translation'=>'bánh mì'],['word'=>'night','translation'=>'ban đêm'],
    ['word'=>'phone','translation'=>'điện thoại'],['word'=>'brain','translation'=>'não bộ'],
    ['word'=>'river','translation'=>'con sông'],['word'=>'cloud','translation'=>'đám mây'],
    ['word'=>'storm','translation'=>'cơn bão'],['word'=>'sweet','translation'=>'ngọt ngào'],
    ['word'=>'smile','translation'=>'nụ cười'],['word'=>'dream','translation'=>'giấc mơ'],
    ['word'=>'stone','translation'=>'viên đá'],['word'=>'horse','translation'=>'con ngựa'],
    ['word'=>'shirt','translation'=>'áo sơ mi'],['word'=>'blood','translation'=>'máu'],
    ['word'=>'floor','translation'=>'sàn nhà'],['word'=>'space','translation'=>'không gian'],
    ['word'=>'sugar','translation'=>'đường mía'],['word'=>'tiger','translation'=>'con hổ'],
    ['word'=>'flame','translation'=>'ngọn lửa'],['word'=>'grass','translation'=>'cỏ xanh'],
];

// Random từ mỗi lần chơi (không cố định theo ngày)
$wordRow = $conn->query("SELECT word,translation FROM vocabulary WHERE LENGTH(word)=5 AND word REGEXP '^[a-zA-Z]+$' ORDER BY RAND() LIMIT 1")->fetch_assoc();
if (!$wordRow) {
    $wordRow = $fallbackWords[array_rand($fallbackWords)];
}
$secret  = strtoupper($wordRow['word']);
$meaning = $wordRow['translation'];
$played  = false; // Cho phép chơi nhiều lần

$best = $conn->query("SELECT score, JSON_UNQUOTE(JSON_EXTRACT(details,'$.attempts')) att FROM game_scores WHERE user_id=$uid AND game_type='wordle' AND score>0 ORDER BY score DESC LIMIT 1")->fetch_assoc();
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
:root{--c-correct:#22c55e;--c-present:#f59e0b;--c-absent:#4b5563;--cell:clamp(48px,12vw,64px);}
body{background:#fafafa;}
.wp{max-width:500px;margin:0 auto;padding:1.5rem 1rem 2rem;}
.wp-head{text-align:center;margin-bottom:1.2rem;}
.wp-title{font-size:2rem;font-weight:900;letter-spacing:-.04em;color:#111;display:flex;align-items:center;justify-content:center;gap:.4rem;}
.wp-date{font-size:.78rem;color:#9ca3af;margin-top:.25rem;}
.best-chip{display:inline-flex;align-items:center;gap:.3rem;background:#f0fdf4;border:1.5px solid #86efac;border-radius:99px;padding:.25rem .75rem;font-size:.75rem;font-weight:700;color:#15803d;margin-top:.5rem;}
.grid-wrap{display:flex;flex-direction:column;gap:5px;align-items:center;margin:1rem 0;}
.g-row{display:grid;grid-template-columns:repeat(5,var(--cell));gap:5px;}
.g-cell{width:var(--cell);height:var(--cell);border:2.5px solid #d1d5db;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:900;text-transform:uppercase;color:#111;background:#fff;transition:border-color .1s;user-select:none;}
.g-cell.filled{border-color:#6b7280;animation:pop .1s;}
.g-cell.correct{background:var(--c-correct);border-color:var(--c-correct);color:#fff;}
.g-cell.present{background:var(--c-present);border-color:var(--c-present);color:#fff;}
.g-cell.absent{background:var(--c-absent);border-color:var(--c-absent);color:#fff;}
.g-cell.shake{animation:shake .4s;}
@keyframes pop{0%,100%{transform:scale(1)}50%{transform:scale(1.14)}}
@keyframes shake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}
@keyframes flip{0%{transform:rotateX(0)}50%{transform:rotateX(-90deg)}100%{transform:rotateX(0)}}
.g-cell.flip{animation:flip .45s ease;}
.msg{min-height:34px;text-align:center;font-weight:700;font-size:.95rem;margin:.4rem 0;}
.hint-btn{display:inline-flex;align-items:center;gap:.35rem;background:#fffbeb;border:1.5px solid #fcd34d;border-radius:99px;padding:.28rem .8rem;font-size:.8rem;font-weight:600;color:#92400e;cursor:pointer;transition:all .15s;margin-bottom:.5rem;}
.hint-btn:hover{background:#fef3c7;}
.kb{display:flex;flex-direction:column;gap:5px;align-items:center;}
.kb-row{display:flex;gap:4px;}
.k{height:54px;min-width:34px;max-width:42px;flex:1;border-radius:7px;border:none;background:#e5e7eb;color:#111;font-size:.85rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .18s,color .18s,transform .08s;-webkit-tap-highlight-color:transparent;}
.k.wide{min-width:52px;max-width:60px;font-size:.7rem;}
.k:active{transform:scale(.9);}
.k.correct{background:var(--c-correct);color:#fff;}
.k.present{background:var(--c-present);color:#fff;}
.k.absent{background:var(--c-absent);color:#fff;}
.result-box{background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;padding:1.4rem;margin-top:1.2rem;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,.06);}
.share-emoji{font-size:1.4rem;line-height:1.5;letter-spacing:2px;margin:.8rem 0;}
.played-alert{background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:.75rem 1rem;text-align:center;font-size:.9rem;color:#166534;margin-bottom:1rem;}
</style>
<div class="wp">
  <div class="wp-head">
    <div class="wp-title">🟩 Đoán Từ</div>
    <div class="wp-date">Đoán từ 5 chữ cái · 6 lần thử · Mỗi ván từ khác nhau</div>
    <?php if ($best): ?><div class="best-chip">🏆 Kỷ lục: <?= $best['score'] ?> điểm · <?= $best['att'] ?>/6 lần</div><?php endif; ?>
  </div>

  <?php // Cho phép chơi nhiều lần - không hiển thị thông báo đã chơi ?>

  <div class="text-center">
    <span class="hint-btn" id="hintBtn">💡 <span id="hintTxt">Nhấn xem gợi ý nghĩa</span></span>
  </div>

  <div class="grid-wrap" id="grid">
    <?php for($r=0;$r<6;$r++): ?>
    <div class="g-row" id="row<?=$r?>">
      <?php for($c=0;$c<5;$c++): ?><div class="g-cell" id="c<?=$r?><?=$c?>"></div><?php endfor; ?>
    </div>
    <?php endfor; ?>
  </div>

  <div class="msg" id="msg"></div>

  <div class="kb" id="kb">
    <div class="kb-row"><?php foreach(str_split('QWERTYUIOP')as $k): ?><button class="k" data-k="<?=$k?>"><?=$k?></button><?php endforeach; ?></div>
    <div class="kb-row"><?php foreach(str_split('ASDFGHJKL')as $k): ?><button class="k" data-k="<?=$k?>"><?=$k?></button><?php endforeach; ?></div>
    <div class="kb-row">
      <button class="k wide" data-k="ENTER">ENTER</button>
      <?php foreach(str_split('ZXCVBNM')as $k): ?><button class="k" data-k="<?=$k?>"><?=$k?></button><?php endforeach; ?>
      <button class="k wide" data-k="BACK">⌫</button>
    </div>
  </div>

  <div class="result-box d-none" id="res">
    <div id="resEmoji" class="fs-1"></div>
    <h5 class="fw-bold mt-1" id="resTitle"></h5>
    <p class="text-muted small" id="resSub"></p>
    <div class="share-emoji" id="shareEmoji"></div>
    <div class="d-flex gap-2 justify-content-center flex-wrap mt-2">
      <button onclick="copyShare()" class="btn btn-success btn-sm px-3">📋 Sao chép kết quả</button>
      <button onclick="location.reload()" class="btn btn-primary btn-sm px-3">🔄 Từ mới</button>
      <a href="<?=BASE_URL?>/tro-choi" class="btn btn-outline-primary btn-sm px-3">← Về Games</a>
    </div>
  </div>
</div>

<script>
const SECRET=<?=json_encode($secret)?>,MEANING=<?=json_encode($meaning)?>,DONE=false;
let row=0,col=0,guess='',over=false;
const kst={},hist=[];

document.getElementById('hintBtn').onclick=()=>{ document.getElementById('hintTxt').textContent='Nghĩa: '+MEANING; };
function cel(r,c){return document.getElementById('c'+r+c);}
function msg(t,col){const m=document.getElementById('msg');m.textContent=t;m.style.color=col||'#111';}

function add(l){if(over||col>=5)return;guess+=l;const c=cel(row,col);c.textContent=l;c.classList.add('filled');col++;}
function del(){if(over||col<=0)return;col--;guess=guess.slice(0,-1);const c=cel(row,col);c.textContent='';c.classList.remove('filled');}
function shakeRow(){for(let c=0;c<5;c++){const e=cel(row,c);e.classList.remove('shake');void e.offsetWidth;e.classList.add('shake');}}

function submit(){
  if(over)return;
  if(col<5){msg('Từ phải có 5 chữ cái!','#ef4444');shakeRow();return;}
  const g=guess.toUpperCase();
  const res=Array(5).fill('absent'),used=Array(5).fill(false);
  for(let i=0;i<5;i++)if(g[i]===SECRET[i]){res[i]='correct';used[i]=true;}
  for(let i=0;i<5;i++){if(res[i]==='correct')continue;for(let j=0;j<5;j++){if(!used[j]&&g[i]===SECRET[j]){res[i]='present';used[j]=true;break;}}}
  for(let i=0;i<5;i++){const el=cel(row,i);const delay=i*110;setTimeout(()=>{el.classList.add('flip');setTimeout(()=>{el.classList.add(res[i]);el.classList.remove('flip');const k=g[i];if(!kst[k]||res[i]==='correct'||(res[i]==='present'&&kst[k]==='absent'))kst[k]=res[i];if(i===4)updateKb();},230);},delay);}
  hist.push({g,res});
  const won=g===SECRET;
  setTimeout(()=>{if(won){over=true;end(true);}else{row++;col=0;guess='';if(row>=6){over=true;end(false);}else msg('');}},5*110+350);
}

function updateKb(){document.querySelectorAll('.k[data-k]').forEach(b=>{const k=b.dataset.k;b.className='k'+(b.classList.contains('wide')?' wide':'');if(kst[k])b.classList.add(kst[k]);});}

function end(won){
  const em={'correct':'🟩','present':'🟨','absent':'⬛'};
  const share=hist.map(h=>h.res.map(r=>em[r]).join('')).join('\n');
  const score=won?(7-row)*100:0;
  document.getElementById('resEmoji').textContent=won?'🎉':'😢';
  document.getElementById('resTitle').textContent=won?'Tuyệt vời! Đoán đúng rồi!':'Hết lượt thử rồi!';
  document.getElementById('resSub').textContent='Từ bí mật: '+SECRET+' = '+MEANING;
  document.getElementById('shareEmoji').textContent=share;
  document.getElementById('res').classList.remove('d-none');
  msg(won?'🎉 Xuất sắc!':'Từ đúng là: '+SECRET,won?'#16a34a':'#ef4444');
  fetch('<?=BASE_URL?>/api/luu-diem',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'wordle',score,details:{won,attempts:won?row+1:6,word:SECRET}})});
}

window._shareHist=()=>{const em={'correct':'🟩','present':'🟨','absent':'⬛'};return hist.map(h=>h.res.map(r=>em[r]).join('')).join('\n');};
function copyShare(){navigator.clipboard?.writeText('🟩 Đoán Từ '+new Date().toLocaleDateString('vi-VN')+'\n'+hist.length+'/6\n\n'+window._shareHist()).then(()=>msg('✅ Đã sao chép!','#16a34a'));}

if(!DONE){
  document.addEventListener('keydown',e=>{if(e.ctrlKey||e.altKey||e.metaKey)return;if(e.key==='Enter')submit();else if(e.key==='Backspace')del();else if(/^[a-zA-Z]$/.test(e.key))add(e.key.toUpperCase());});
}
document.querySelectorAll('.k').forEach(b=>b.addEventListener('click',()=>{const k=b.dataset.k;if(k==='ENTER')submit();else if(k==='BACK')del();else add(k);}));
</script>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
