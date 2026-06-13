<?php
global $conn;
requireLogin();
$pageTitle = 'Thông báo – ' . SITE_NAME;
$uid = $_SESSION['user_id'];

// Đánh dấu đã đọc
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");
$notifs = $conn->query("SELECT * FROM notifications WHERE user_id=$uid ORDER BY created_at DESC LIMIT 30");
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>
<div class="container py-4" style="max-width:700px">
  <h4 class="fw-bold mb-4"><i class="bi bi-bell me-2"></i>Thông báo</h4>
  <?php
global $conn; if ($notifs->num_rows === 0): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-bell-slash fs-1"></i><p class="mt-3">Chưa có thông báo nào.</p></div>
  <?php
global $conn; else: while($n=$notifs->fetch_assoc()): $icons=['success'=>'bi-check-circle-fill text-success','info'=>'bi-info-circle-fill text-info','warning'=>'bi-exclamation-triangle-fill text-warning']; ?>
  <div class="notif-item p-3 mb-2 rounded-3 border <?= $n['is_read']?'':'unread' ?>">
    <div class="d-flex gap-3">
      <i class="bi <?= $icons[$n['type']]??'bi-bell text-secondary' ?> fs-5 mt-1"></i>
      <div>
        <div class="fw-semibold"><?= sanitize($n['title']) ?></div>
        <div class="text-muted small"><?= sanitize($n['message']) ?></div>
        <div class="text-muted" style="font-size:.72rem;margin-top:.25rem"><?= timeAgo($n['created_at']) ?></div>
      </div>
    </div>
  </div>
  <?php
global $conn; endwhile; endif; ?>
</div>
<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
