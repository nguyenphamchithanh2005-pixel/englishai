<?php
global $conn;
requireLogin();
$pageTitle = 'Chat AI – ' . SITE_NAME;
$uid = $_SESSION['user_id'];

// Reset đếm nếu sang ngày mới
$userRow = $conn->query("SELECT ai_messages_today,ai_last_reset FROM users WHERE id=$uid")->fetch_assoc();
$today   = date('Y-m-d');
if ($userRow['ai_last_reset'] !== $today) {
    $conn->query("UPDATE users SET ai_messages_today=0, ai_last_reset='$today' WHERE id=$uid");
    $aiUsed = 0;
} else {
    $aiUsed = (int)$userRow['ai_messages_today'];
}
$aiLimit  = getAILimit();
$canChat  = ($aiLimit === AI_LIMIT_PREMIUM) || ($aiUsed < $aiLimit);

// Session chat
if (empty($_SESSION['ai_session'])) $_SESSION['ai_session'] = bin2hex(random_bytes(16));
$sessionId = $_SESSION['ai_session'];

// Lịch sử chat (tải lên để JS khởi tạo conversation)
$historyRows = $conn->query("
    SELECT role, message, created_at
    FROM ai_conversations
    WHERE user_id=$uid AND session_id='$sessionId'
    ORDER BY id ASC LIMIT 40
");
$chatHistory = [];
while ($r = $historyRows->fetch_assoc()) $chatHistory[] = $r;

$context = sanitize($_GET['context'] ?? '');
$initQ   = sanitize($_GET['q'] ?? '');
?>
<?php
global $conn; include ROOT.'/app/views/layout/header.php'; ?>

<div class="container-fluid py-0 ai-chat-page" style="max-width:1200px">
<div class="row g-0 h-100">
    <!-- ── Sidebar gợi ý ── -->
    <div class="col-md-3 d-none d-md-flex flex-column ai-sidebar p-3">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="ai-avatar"><i class="bi bi-robot text-warning"></i></div>
        <div>
          <div class="fw-bold text-white" style="font-size:.9rem">Giáo viên AI</div>
          <div style="font-size:.72rem;color:#94a3b8">
            <i class="bi bi-google me-1 text-warning"></i>Groq <?= GROQ_MODEL ?>
          </div>
        </div>
      </div>

      <!-- Quota bar -->
      <div class="mb-3 p-2 rounded" style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.25)">
        <div class="d-flex justify-content-between mb-1">
          <span class="text-warning fw-semibold" style="font-size:.78rem">Tin hôm nay</span>
          <span class="text-light" style="font-size:.78rem" id="quotaText">
            <?= $aiUsed ?>/<?= $aiLimit===AI_LIMIT_PREMIUM?'∞':$aiLimit ?>
          </span>
        </div>
        <div class="progress" style="height:5px;background:rgba(255,255,255,.1)">
          <div class="progress-bar bg-warning" id="quotaBar"
               style="width:<?= $aiLimit===AI_LIMIT_PREMIUM?8:min(100,round($aiUsed/$aiLimit*100)) ?>%">
          </div>
        </div>
        <?php
global $conn; if (!canAccessLevel('advanced')): ?>
        <a href="<?= BASE_URL ?>/nang-cap" class="btn btn-warning btn-sm w-100 mt-2 fw-bold" style="font-size:.72rem">
          <i class="bi bi-gem me-1"></i>Nâng cấp – chat nhiều hơn
        </a>
        <?php
global $conn; endif; ?>
      </div>

      <!-- Gợi ý -->
      <div class="text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.04em">GỢI Ý CÂU HỎI</div>
      <div class="flex-grow-1 overflow-auto pe-1">
        <?php
global $conn;
        $suggestions = [
          ['bi-diagram-3','Ngữ pháp',[
            'Giải thích thì hiện tại hoàn thành',
            'Phân biệt "since" và "for"',
            'Cách dùng câu điều kiện loại 2 & 3',
            'Passive voice – khi nào nên dùng?',
          ]],
          ['bi-chat-dots','Hội thoại',[
            'Hãy nói chuyện với tôi bằng tiếng Anh',
            'Luyện phỏng vấn xin việc bằng tiếng Anh',
            'Luyện đặt đồ tại nhà hàng',
            'Giao tiếp khi đi du lịch nước ngoài',
          ]],
          ['bi-translate','Từ vựng',[
            'Phrasal verbs thông dụng với "get"',
            'Idioms hay dùng trong giao tiếp',
            'Từ vựng học thuật cho IELTS',
          ]],
          ['bi-pencil-square','Viết',[
            'Kiểm tra và sửa câu này: ...',
            'Cách viết email chuyên nghiệp',
            'Cách viết essay IELTS Task 2',
          ]],
        ];
        foreach ($suggestions as [$icon, $cat, $qs]):
        ?>
        <div class="mb-3">
          <div class="mb-1" style="font-size:.72rem;color:#64748b;font-weight:600">
            <i class="bi <?= $icon ?> me-1"></i><?= $cat ?>
          </div>
          <?php
global $conn; foreach ($qs as $q): ?>
          <button class="suggestion-btn mb-1" onclick="sendSuggestion(this)"><?= $q ?></button>
          <?php
global $conn; endforeach; ?>
        </div>
        <?php
global $conn; endforeach; ?>
      </div>

      <button class="btn btn-outline-secondary btn-sm mt-2" onclick="newConversation()">
        <i class="bi bi-plus-circle me-1"></i>Cuộc trò chuyện mới
      </button>
    </div>

    <!-- ── Khu vực chat ── -->
    <div class="col-md-9 d-flex flex-column" style="border-left:1px solid #e2e8f0">

      <!-- Header -->
      <div class="ai-chat-header px-4 py-2 d-flex align-items-center gap-3">
        <div class="ai-avatar"><i class="bi bi-robot text-warning fs-6"></i></div>
        <div class="flex-grow-1">
          <div class="fw-bold" style="font-size:.92rem">Trợ lý tiếng Anh AI</div>
          <div class="text-muted" style="font-size:.75rem">
            <span class="text-success">●</span> Sẵn sàng ·
            <i class="bi bi-google me-1"></i>Groq <?= GROQ_MODEL ?>
          </div>
        </div>
        <!-- Badge quota trên mobile -->
        <span class="badge bg-dark d-md-none" id="quotaBadge">
          <?= $aiUsed ?>/<?= $aiLimit===AI_LIMIT_PREMIUM?'∞':$aiLimit ?>
        </span>
        <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="newConversation()">
          <i class="bi bi-plus"></i>
        </button>
      </div>

      <!-- Messages -->
<div class="chat-messages flex-grow-1 p-3 p-md-4" id="chatMessages">
  
        <?php
global $conn; if (empty($chatHistory)): ?>
        <!-- Welcome -->
        <div class="message assistant-message">
          <div class="message-bubble">
            <p class="mb-2">👋 Xin chào <strong><?= sanitize($_SESSION['fullname']) ?></strong>!</p>
            <p class="mb-1">Tôi là trợ lý AI học tiếng Anh, có thể giúp bạn:</p>
            <ul class="mb-0 ps-3">
              <li>🔤 Giải thích ngữ pháp &amp; từ vựng</li>
              <li>💬 Luyện hội thoại thực tế</li>
              <li>✍️ Kiểm tra &amp; sửa bài viết</li>
              <li>📝 Luyện thi IELTS / TOEIC</li>
            </ul>
            <?php
global $conn; if ($context): ?>
            <div class="mt-2 p-2 rounded" style="background:rgba(99,102,241,.08);font-size:.82rem">
              📚 <strong>Bài học:</strong> <?= $context ?>
            </div>
            <?php
global $conn; endif; ?>
          </div>
          <div class="message-time">Vừa xong</div>
        </div>

        <?php
global $conn; else: foreach ($chatHistory as $m): ?>
        <div class="message <?= $m['role']==='user'?'user-message':'assistant-message' ?>">
          <div class="message-bubble"><?= formatAIHtml(sanitize($m['message'])) ?></div>
          <div class="message-time"><?= timeAgo($m['created_at']) ?></div>
        </div>
        <?php
global $conn; endforeach; endif; ?>

        <!-- Typing indicator -->
        <div id="typingIndicator" class="message assistant-message d-none">
          <div class="message-bubble" style="padding:.6rem .9rem">
            <div class="typing-dots"><span></span><span></span><span></span></div>
          </div>
        </div>
      </div>

      <!-- Input -->
      <?php
global $conn; if (!$canChat): ?>
      <div class="chat-input-area p-3 text-center">
        <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-clock-history fs-5"></i>
          <div>Hết <?= $aiLimit ?> tin hôm nay.
            <a href="<?= BASE_URL ?>/nang-cap" class="alert-link fw-bold">Nâng cấp để chat nhiều hơn →</a>
          </div>
        </div>
      </div>
      <?php
global $conn; else: ?>
      <div class="chat-input-area p-3">
        <div class="input-group shadow-sm">
          <textarea id="userInput" class="form-control"
            placeholder="Nhập câu hỏi tiếng Anh... (Enter gửi · Shift+Enter xuống dòng)"
            rows="2" style="resize:none;font-size:.9rem"></textarea>
          <button class="btn btn-primary px-3" id="sendBtn" onclick="sendMessage()" title="Gửi (Enter)">
            <i class="bi bi-send-fill"></i>
          </button>
        </div>
        <div class="d-flex justify-content-between mt-1 px-1">
          <span class="text-muted" style="font-size:.72rem">
            <i class="bi bi-google me-1"></i>Groq <?= GROQ_MODEL ?>
          </span>
          <span class="text-muted" style="font-size:.72rem" id="remainingText">
            Còn lại: <strong><?= $aiLimit===AI_LIMIT_PREMIUM?'không giới hạn':($aiLimit-$aiUsed).' tin' ?></strong>
          </span>
        </div>
      </div>
      <?php
global $conn; endif; ?>

    </div><!-- /chat col -->
  </div>
</div>

<script>
// ── Khởi tạo ──────────────────────────────────────────────
const SESSION_ID  = '<?= $sessionId ?>';
const AI_LIMIT    = <?= $aiLimit ?>;
const AI_PREMIUM  = <?= AI_LIMIT_PREMIUM ?>;
const CONTEXT     = <?= json_encode($context) ?>;

// Lịch sử hội thoại (định dạng {role:'user'|'assistant', content:'...'})
let history = [];
<?php
global $conn; foreach ($chatHistory as $m): ?>
history.push({ role: '<?= $m['role'] ?>', content: <?= json_encode($m['message']) ?> });
<?php
global $conn; endforeach; ?>

let aiUsed = <?= $aiUsed ?>;

// DOM refs
const messagesDiv  = document.getElementById('chatMessages');
const inputEl      = document.getElementById('userInput');
const sendBtn      = document.getElementById('sendBtn');
const typingEl     = document.getElementById('typingIndicator');
const quotaText    = document.getElementById('quotaText');
const quotaBar     = document.getElementById('quotaBar');
const quotaBadge   = document.getElementById('quotaBadge');
const remainTxt    = document.getElementById('remainingText');

// Auto-scroll
function scrollBottom() { messagesDiv.scrollTop = messagesDiv.scrollHeight; }
scrollBottom();

// Enter gửi (Shift+Enter xuống dòng)
inputEl?.addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

// ── Gửi tin nhắn ──────────────────────────────────────────
async function sendMessage() {
  const msg = inputEl.value.trim();
  if (!msg || sendBtn?.disabled) return;

  inputEl.value = '';
  if (sendBtn) sendBtn.disabled = true;

  // Hiện tin user
  appendMessage('user', escHtml(msg));
  history.push({ role: 'user', content: msg });

  // Hiện typing
  typingEl.classList.remove('d-none');
  scrollBottom();

  try {
    // Gọi PHP proxy – key an toàn phía server
    const res = await fetch('<?= BASE_URL ?>/api/chat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        messages:   history.slice(-12),   // giữ 12 lượt gần nhất
        context:    CONTEXT,
        session_id: SESSION_ID,
      })
    });

    const data = await res.json();
    typingEl.classList.add('d-none');

    if (!res.ok || data.error) {
      const errMsg = data.error || 'Lỗi không xác định.';
      appendMessage('assistant',
        `<div class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${escHtml(errMsg)}</div>`
        + (data.upgrade ? '<div class="mt-2"><a href="<?= BASE_URL ?>/nang-cap" class="btn btn-warning btn-sm">Nâng cấp gói học</a></div>' : '')
      );
    } else {
      const aiText = data.message;
      history.push({ role: 'assistant', content: aiText });
      appendMessage('assistant', renderMarkdown(aiText));
      // Cập nhật quota UI
      aiUsed = data.used;
      updateQuotaUI(data.used, data.limit);
    }
  } catch (err) {
    typingEl.classList.add('d-none');
    appendMessage('assistant',
      `<div class="text-danger"><i class="bi bi-wifi-off me-1"></i>Mất kết nối tới server. Thử lại sau.</div>`
    );
    console.error(err);
  }

  if (sendBtn) sendBtn.disabled = false;
  inputEl?.focus();
}

// ── Thêm bong bóng chat vào DOM ────────────────────────────
function appendMessage(role, htmlContent) {
  const div = document.createElement('div');
  div.className = 'message ' + (role === 'user' ? 'user-message' : 'assistant-message');
  div.innerHTML = `<div class="message-bubble">${htmlContent}</div>
                   <div class="message-time">Vừa xong</div>`;
  typingEl.before(div);
  scrollBottom();
}

// ── Cập nhật UI quota ─────────────────────────────────────
function updateQuotaUI(used, limit) {
  const unlimited = limit >= AI_PREMIUM;
  const pct = unlimited ? 5 : Math.min(100, Math.round(used / limit * 100));
  const rem = unlimited ? '∞' : (limit - used);
  if (quotaBar)   quotaBar.style.width = pct + '%';
  if (quotaText)  quotaText.textContent = used + '/' + (unlimited ? '∞' : limit);
  if (quotaBadge) quotaBadge.textContent = used + '/' + (unlimited ? '∞' : limit);
  if (remainTxt)  remainTxt.innerHTML =
    'Còn lại: <strong>' + (unlimited ? 'không giới hạn' : rem + ' tin') + '</strong>';
  // Cảnh báo khi gần hết
  if (!unlimited && used >= limit) {
    appendMessage('assistant',
      `<div class="text-warning"><i class="bi bi-clock me-1"></i>
       Bạn đã dùng hết tin nhắn hôm nay.
       <a href="<?= BASE_URL ?>/nang-cap" class="ms-2 btn btn-warning btn-sm">Nâng cấp →</a></div>`
    );
    if (sendBtn) sendBtn.disabled = true;
  }
}

// ── Render Markdown đơn giản ──────────────────────────────
function renderMarkdown(text) {
  return escHtml(text)
    .replace(/\*\*(.*?)\*\*/g,  '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g,      '<em>$1</em>')
    .replace(/`([^`]+)`/g,      '<code>$1</code>')
    .replace(/^#{1,3}\s(.+)$/gm,'<div class="fw-bold mt-2">$1</div>')
    .replace(/^[-*]\s(.+)$/gm,  '<div class="d-flex gap-2 mt-1"><span>•</span><span>$1</span></div>')
    .replace(/\n\n/g,           '<br><br>')
    .replace(/\n/g,             '<br>');
}

function escHtml(t) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(t));
  return d.innerHTML;
}

// ── Nút gợi ý ─────────────────────────────────────────────
function sendSuggestion(el) {
  if (inputEl) { inputEl.value = el.textContent.trim(); sendMessage(); }
}

// ── Cuộc trò chuyện mới ───────────────────────────────────
function newConversation() {
  if (confirm('Bắt đầu cuộc trò chuyện mới? Lịch sử chat hiện tại sẽ được lưu.')) {
    fetch('<?= BASE_URL ?>/api/phien-moi').then(() => location.reload());
  }
}

// ── Auto-start nếu có query string ?q= ────────────────────
<?php
global $conn; if ($initQ): ?>
window.addEventListener('load', () => {
  if (inputEl) { inputEl.value = <?= json_encode($initQ) ?>; sendMessage(); }
});
<?php
global $conn; endif; ?>
</script>

<?php
global $conn;
// Helper render markdown phía PHP (dùng cho history load)
function formatAIHtml($text) {
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/',     '<em>$1</em>',         $text);
    $text = preg_replace('/`([^`]+)`/',     '<code>$1</code>',     $text);
    $text = nl2br($text);
    return $text;
}
?>
<?php
global $conn; include ROOT.'/app/views/layout/footer.php'; ?>
