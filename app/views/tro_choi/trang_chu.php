<?php
global $conn;
requireLogin();
$uid = (int)$_SESSION['user_id'];

function getBest($conn,$uid,$type){
  $r=$conn->query("SELECT score,details,played_at FROM game_scores WHERE user_id=$uid AND game_type='$type' ORDER BY score DESC LIMIT 1");
  return $r?$r->fetch_assoc():null;
}
function getTop($conn,$type,$n=5){
  $r=$conn->query("SELECT u.fullname,MAX(gs.score) best,gs.details FROM game_scores gs JOIN users u ON gs.user_id=u.id WHERE gs.game_type='$type' GROUP BY gs.user_id ORDER BY best DESC LIMIT $n");
  $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;return $rows;
}
$bW=getBest($conn,$uid,'wordle');$bM=getBest($conn,$uid,'match');$bT=getBest($conn,$uid,'typing');
$tW=getTop($conn,'wordle');$tM=getTop($conn,'match');$tT=getTop($conn,'typing');
$duelWins=(int)$conn->query("SELECT COUNT(*) c FROM duels WHERE winner_id=$uid AND status='finished'")->fetch_assoc()['c'];
$topDuel=[];
$td=$conn->query("SELECT u.fullname,COUNT(*) wins FROM duels d JOIN users u ON d.winner_id=u.id WHERE d.status='finished' GROUP BY d.winner_id ORDER BY wins DESC LIMIT 5");
while($r=$td->fetch_assoc())$topDuel[]=$r;
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>
<style>
body{background:#0f172a;}
.gh{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);padding:3rem 0 2rem;text-align:center;border-bottom:1px solid #1e293b;}
.gh-title{font-size:2.4rem;font-weight:900;letter-spacing:-.05em;color:#f8fafc;}
.gh-sub{color:#64748b;font-size:.95rem;margin-top:.4rem;}
.gh-stats{display:flex;gap:2rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap;}
.gh-stat{text-align:center;}
.gh-num{font-size:1.5rem;font-weight:900;color:#818cf8;}
.gh-lbl{font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;}
.games-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.2rem;max-width:900px;margin:2.5rem auto;padding:0 1rem;}
@media(max-width:600px){.games-grid{grid-template-columns:1fr;}}
.gc{background:#1e293b;border:1.5px solid #334155;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;transition:transform .2s,border-color .2s,box-shadow .2s;}
.gc:hover{transform:translateY(-4px);border-color:#6366f1;box-shadow:0 12px 40px rgba(99,102,241,.15);}
.gc-top{padding:1.8rem 1.5rem 1.2rem;display:flex;flex-direction:column;align-items:center;text-align:center;}
.gc-icon{font-size:3rem;margin-bottom:.75rem;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));}
.gc-name{font-size:1.25rem;font-weight:900;color:#f1f5f9;letter-spacing:-.03em;}
.gc-desc{font-size:.8rem;color:#64748b;margin-top:.3rem;}
.gc-my{background:rgba(99,102,241,.1);border-top:1px solid #334155;padding:.6rem 1rem;font-size:.78rem;color:#818cf8;font-weight:600;display:flex;align-items:center;gap:.4rem;}
.gc-lb{padding:.6rem 1rem 0;flex:1;}
.lb-mini-row{display:flex;align-items:center;gap:.5rem;padding:.3rem 0;border-bottom:1px solid #1e293b;}
.lb-mini-row:last-child{border:none;}
.lb-mini-rank{font-size:.85rem;width:22px;text-align:center;}
.lb-mini-name{flex:1;font-size:.77rem;color:#94a3b8;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.lb-mini-score{font-size:.75rem;color:#6366f1;font-weight:700;}
.gc-btn{display:block;margin:1rem;padding:.75rem;border-radius:12px;font-weight:800;font-size:.95rem;text-align:center;text-decoration:none;transition:opacity .15s;}
.gc-btn:hover{opacity:.88;text-decoration:none;}
.btn-wordle{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;}
.btn-match{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;}
.btn-typing{background:linear-gradient(135deg,#38bdf8,#0284c7);color:#fff;}
.btn-duel{background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;}
.my-badge{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:8px;padding:.25rem .6rem;font-size:.73rem;color:#4ade80;font-weight:700;}
</style>

<div class="gh">
  <div class="gh-title">🎮 Mini Games</div>
  <div class="gh-sub">Học tiếng Anh qua trò chơi · Vui vẻ · Cạnh tranh</div>
  <div class="gh-stats">
    <div class="gh-stat">
      <div class="gh-num"><?=$bW?$bW['score']:0?></div>
      <div class="gh-lbl">Điểm Đoán Từ</div>
    </div>
    <div class="gh-stat">
      <div class="gh-num"><?=$bM?$bM['score']:0?></div>
      <div class="gh-lbl">Điểm Memory</div>
    </div>
    <div class="gh-stat">
      <div class="gh-num"><?=$bT?json_decode($bT['details'],true)['wpm']??0:0?> WPM</div>
      <div class="gh-lbl">Typing tốt nhất</div>
    </div>
    <div class="gh-stat">
      <div class="gh-num"><?=$duelWins?></div>
      <div class="gh-lbl">Duel thắng</div>
    </div>
  </div>
</div>

<div class="games-grid">
  <!-- Đoán Từ -->
  <div class="gc">
    <div class="gc-top">
      <div class="gc-icon">🟩</div>
      <div class="gc-name">Đoán Từ</div>
      <div class="gc-desc">Đoán từ 5 chữ cái · 6 lần thử · Mỗi ngày 1 từ mới</div>
    </div>
    <?php if($bW): ?><div class="gc-my">🏅 Kỷ lục: <?=$bW['score']?> điểm · <?=date('d/m',strtotime($bW['played_at']))?></div><?php endif; ?>
    <div class="gc-lb">
      <?php $medals=['🥇','🥈','🥉','4️⃣','5️⃣'];
      foreach($tW as $i=>$t): ?>
      <div class="lb-mini-row">
        <span class="lb-mini-rank"><?=$medals[$i]??($i+1)?></span>
        <span class="lb-mini-name"><?=sanitize($t['fullname'])?></span>
        <span class="lb-mini-score"><?=$t['best']?> đ</span>
      </div>
      <?php endforeach; if(!$tW): ?><div style="font-size:.78rem;color:#4b5563;padding:.5rem 0">Chưa có ai chơi. Hãy là người đầu tiên!</div><?php endif; ?>
    </div>
    <a href="<?=BASE_URL?>/tro-choi/do-tu" class="gc-btn btn-wordle">🟩 Chơi ngay</a>
  </div>

  <!-- Ghép Cặp -->
  <div class="gc">
    <div class="gc-top">
      <div class="gc-icon">🃏</div>
      <div class="gc-name">Ghép Cặp</div>
      <div class="gc-desc">Ghép từ với nghĩa · Lật thẻ · Chọn 8/12/16 cặp</div>
    </div>
    <?php if($bM): ?><div class="gc-my">🏅 Kỷ lục: <?=$bM['score']?> điểm</div><?php endif; ?>
    <div class="gc-lb">
      <?php foreach($tM as $i=>$t): ?>
      <div class="lb-mini-row">
        <span class="lb-mini-rank"><?=$medals[$i]??($i+1)?></span>
        <span class="lb-mini-name"><?=sanitize($t['fullname'])?></span>
        <span class="lb-mini-score"><?=$t['best']?> đ</span>
      </div>
      <?php endforeach; if(!$tM): ?><div style="font-size:.78rem;color:#4b5563;padding:.5rem 0">Chưa có ai chơi. Hãy là người đầu tiên!</div><?php endif; ?>
    </div>
    <a href="<?=BASE_URL?>/tro-choi/ghep-cap" class="gc-btn btn-match">🃏 Chơi ngay</a>
  </div>

  <!-- Gõ Nhanh -->
  <div class="gc">
    <div class="gc-top">
      <div class="gc-icon">⌨️</div>
      <div class="gc-name">Gõ Nhanh</div>
      <div class="gc-desc">Gõ nhanh · Đo WPM · 3 chế độ chơi</div>
    </div>
    <?php if($bT): $td=json_decode($bT['details'],true); ?><div class="gc-my">🏅 Kỷ lục: <?=$td['wpm']??0?> WPM · <?=$bT['score']?> điểm</div><?php endif; ?>
    <div class="gc-lb">
      <?php foreach($tT as $i=>$t): $td2=json_decode($t['details']??'{}',true); ?>
      <div class="lb-mini-row">
        <span class="lb-mini-rank"><?=$medals[$i]??($i+1)?></span>
        <span class="lb-mini-name"><?=sanitize($t['fullname'])?></span>
        <span class="lb-mini-score"><?=$td2['wpm']??$t['best']?> WPM</span>
      </div>
      <?php endforeach; if(!$tT): ?><div style="font-size:.78rem;color:#4b5563;padding:.5rem 0">Chưa có ai chơi. Hãy là người đầu tiên!</div><?php endif; ?>
    </div>
    <a href="<?=BASE_URL?>/tro-choi/go-nhanh" class="gc-btn btn-typing">⌨️ Chơi ngay</a>
  </div>

  <!-- Đối Kháng 1v1 -->
  <div class="gc">
    <div class="gc-top">
      <div class="gc-icon">⚔️</div>
      <div class="gc-name">Đối Kháng 1v1</div>
      <div class="gc-desc">Thách đấu bạn bè · Câu hỏi ngữ pháp · Realtime</div>
    </div>
    <div class="gc-my">⚔️ Tổng thắng: <strong><?=$duelWins?> trận</strong></div>
    <div class="gc-lb">
      <?php foreach($topDuel as $i=>$t): ?>
      <div class="lb-mini-row">
        <span class="lb-mini-rank"><?=$medals[$i]??($i+1)?></span>
        <span class="lb-mini-name"><?=sanitize($t['fullname'])?></span>
        <span class="lb-mini-score"><?=$t['wins']?> thắng</span>
      </div>
      <?php endforeach; if(!$topDuel): ?><div style="font-size:.78rem;color:#4b5563;padding:.5rem 0">Chưa có trận nào. Thách đấu ngay!</div><?php endif; ?>
    </div>
    <a href="<?=BASE_URL?>/doi-khang" class="gc-btn btn-duel">⚔️ Vào Duel</a>
  </div>
</div>

<!-- Hướng dẫn -->
<div style="max-width:900px;margin:0 auto 3rem;padding:0 1rem;">
  <div style="background:#1e293b;border:1.5px solid #334155;border-radius:20px;padding:1.5rem 2rem;">
    <div style="font-size:.9rem;font-weight:800;color:#f1f5f9;margin-bottom:1rem;">💡 Cách chơi nhanh</div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;">
      <?php
      $guides=[
        ['🟩','Đoán Từ','Gõ từ 5 chữ cái → Enter để đoán. 🟩=đúng vị trí, 🟨=có nhưng sai chỗ, ⬛=không có.'],
        ['🃏','Ghép Cặp','Click 2 thẻ cùng cặp (tiếng Anh – tiếng Việt). Ghép hết tất cả nhanh nhất thắng.'],
        ['⌨️','Gõ Nhanh','Click vào khung → bắt đầu gõ theo văn bản. Đo WPM và độ chính xác. 3 chế độ: Thường / 60s / 30s.'],
        ['⚔️','Đối Kháng 1v1','Tạo phòng → chia sẻ mã → bạn bè tham gia. Trả lời 10 câu hỏi. Ai đúng nhiều hơn thắng.'],
      ];
      foreach($guides as [$icon,$name,$desc]):
      ?>
      <div style="display:flex;gap:.75rem;align-items:flex-start;">
        <div style="font-size:1.5rem;flex-shrink:0"><?=$icon?></div>
        <div>
          <div style="font-size:.85rem;font-weight:700;color:#f1f5f9"><?=$name?></div>
          <div style="font-size:.78rem;color:#64748b;margin-top:.2rem;line-height:1.5"><?=$desc?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include ROOT.'/app/views/layout/footer.php'; ?>
