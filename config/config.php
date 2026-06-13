<?php
/**
 * EnglishAI – Cấu hình hệ thống
 * MVC Pattern | Tên biến tiếng Việt trong Model
 */
session_start();

if (!defined('ROOT')) {
    define('ROOT', __DIR__);
}

// ── Cơ sở dữ liệu ────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'english_learning');

// ── Email (Gmail SMTP) ────────────────────────────────────────
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_USER',      'nvquang.ggit@gmail.com');   // << Điền Gmail của bạn
define('MAIL_PASS',      'rbassvwwamnnfqpp');       // << Điền App Password Gmail (16 ký tự)
define('MAIL_FROM',      'nvquang.ggit@gmail.com');   // << Giống MAIL_USER
define('MAIL_FROM_NAME', 'EnglishAI');
define('MAIL_ENABLED',   true);                      // Đổi thành false nếu muốn tắt email

// ── Cookie settings ───────────────────────────────────────────
define('COOKIE_REMEMBER_DAYS', 30);         // Ghi nhớ đăng nhập X ngày
define('COOKIE_THEME_DAYS',    365);        // Lưu giao diện 1 năm
define('COOKIE_LANG_DAYS',     365);        // Lưu ngôn ngữ 1 năm
define('COOKIE_SECRET',        'nvquang2024@EnglishAI#xyz!789$abc'); // << Đổi chuỗi này

// ── AI (Groq) ─────────────────────────────────────────────────
define('GROQ_API_KEY', '');  // << Điền key vào đây
define('GROQ_MODEL',   'llama-3.3-70b-versatile');
define('AI_LIMIT_BASIC',    5);
define('AI_LIMIT_ADVANCED', 30);
define('AI_LIMIT_PREMIUM',  9999);

// ── Tên trang ─────────────────────────────────────────────────
define('SITE_NAME', 'EnglishAI');

// ── Base URL tự động (chạy subdirectory hay root đều OK) ──────
$_protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_dir       = dirname($_SERVER['SCRIPT_NAME']);
$_dir       = ($_dir === '/' || $_dir === '\\') ? '' : rtrim($_dir, '/\\');
define('BASE_URL', $_protocol . '://' . $_host . $_dir);
define('SITE_URL', BASE_URL);

// ── Kết nối DB ────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="padding:2rem;color:#c0392b;font-family:sans-serif"><h2>Lỗi kết nối Database</h2><p>'.$conn->connect_error.'</p></div>');
}
$conn->set_charset('utf8mb4');

// ── Tự động tạo bảng nếu chưa có ─────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS user_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash VARCHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  token_hash VARCHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ── Hàm dùng chung ───────────────────────────────────────────
function redirect(string $url): void { header("Location: $url"); exit(); }
function isLoggedIn(): bool          { return isset($_SESSION['user_id']); }
function requireLogin(): void        { if (!isLoggedIn()) redirect(BASE_URL.'/dang-nhap'); }
function requireAdmin(): void        { if (!isLoggedIn() || ($_SESSION['role']??'') !== 'admin') redirect(BASE_URL); }
function getMembership(): string     { return $_SESSION['membership'] ?? 'basic'; }
function canAccessLevel(string $lv): bool {
    $m = ['basic'=>1,'advanced'=>2,'premium'=>3];
    return ($m[getMembership()]??1) >= ($m[$lv]??1);
}
function getLevelBadge(string $lv): string {
    return ['basic'=>'<span class="badge bg-success">Cơ bản</span>','advanced'=>'<span class="badge bg-warning text-dark">Nâng cao</span>','premium'=>'<span class="badge bg-danger">Cấp cao</span>'][$lv] ?? '';
}
function getLevelLabel(string $lv): string {
    return ['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'][$lv] ?? $lv;
}
function sanitize($d): string        { return htmlspecialchars(strip_tags(trim((string)$d))); }
function getAILimit(): int           { $m=getMembership(); return $m==='premium'?AI_LIMIT_PREMIUM:($m==='advanced'?AI_LIMIT_ADVANCED:AI_LIMIT_BASIC); }
function getNotificationCount(): int {
    global $conn;
    if (!isLoggedIn()) return 0;
    $uid = (int)$_SESSION['user_id'];
    return (int)$conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_assoc()['c'];
}
function timeAgo(string $dt): string {
    $d = (new DateTime)->diff(new DateTime($dt));
    if ($d->d > 7) return date('d/m/Y', strtotime($dt));
    if ($d->d > 0) return $d->d.' ngày trước';
    if ($d->h > 0) return $d->h.' giờ trước';
    if ($d->i > 0) return $d->i.' phút trước';
    return 'Vừa xong';
}
function render(string $view, array $data = []): void {
    extract($data);
    $f = ROOT.'/app/views/'.$view.'.php';
    file_exists($f) ? require $f : die("View không tồn tại: $view");
}

// ── Cookie helper functions ───────────────────────────────────
function setCookieSecure(string $name, string $value, int $days, string $path='/'): void {
    $exp = $days > 0 ? time() + $days * 86400 : 0;
    setcookie($name, $value, [
        'expires'  => $exp,
        'path'     => $path,
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
function deleteCookie(string $name, string $path='/'): void {
    setcookie($name, '', ['expires'=>time()-3600, 'path'=>$path, 'httponly'=>true, 'samesite'=>'Lax']);
}
function getTheme(): string    { return $_COOKIE['theme'] ?? 'light'; }
function getLang(): string     { return $_COOKIE['lang']  ?? 'vi'; }
function isDarkMode(): bool    { return getTheme() === 'dark'; }

// ── Auto-login từ remember-me cookie ─────────────────────────
if (!isLoggedIn() && !empty($_COOKIE['remember_token'])) {
    global $conn;
    $token = preg_replace('/[^a-f0-9]/', '', $_COOKIE['remember_token']);
    if ($token && $conn) {
        $hash = hash('sha256', $token.COOKIE_SECRET);
        $r = $conn->query("SELECT u.* FROM users u JOIN user_tokens t ON u.id=t.user_id WHERE t.token_hash='$hash' AND t.expires_at>NOW() LIMIT 1");
        if ($r && $user = $r->fetch_assoc()) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['fullname']   = $user['fullname'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['membership'] = $user['membership'];
            // Gia hạn cookie & token
            setCookieSecure('remember_token', $token, COOKIE_REMEMBER_DAYS);
            $conn->query("UPDATE user_tokens SET expires_at=DATE_ADD(NOW(), INTERVAL ".COOKIE_REMEMBER_DAYS." DAY) WHERE token_hash='$hash'");
        }
    }
}
