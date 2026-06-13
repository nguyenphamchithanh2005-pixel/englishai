<?php
/**
 * MailHelper – Gửi email qua Gmail SMTP (PHPMailer)
 * Cấu hình trong config.php: MAIL_HOST, MAIL_USER, MAIL_PASS, MAIL_FROM, MAIL_FROM_NAME
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT.'/lib/phpmailer/Exception.php';
require_once ROOT.'/lib/phpmailer/PHPMailer.php';
require_once ROOT.'/lib/phpmailer/SMTP.php';

class MailHelper {

    /**
     * Gửi email HTML
     * @return bool|string  true nếu thành công, chuỗi lỗi nếu thất bại
     */
    public static function gui(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('MAIL_USER') ? MAIL_USER : '';
            $mail->Password   = defined('MAIL_PASS') ? MAIL_PASS : '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Fix lỗi SSL certificate trên localhost/XAMPP
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            $fromEmail = defined('MAIL_FROM') ? MAIL_FROM : $mail->Username;
            $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : SITE_NAME;
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($fromEmail, $fromName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::wrapTemplate($htmlBody, $subject);
            $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','</p>'], "\n", $htmlBody));

            $mail->send();
            return true;
        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }

    /** Template email chung */
    private static function wrapTemplate(string $body, string $subject): string {
        $siteName = SITE_NAME;
        $baseUrl  = BASE_URL;
        $year     = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$subject}</title></head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:40px 0;">
<tr><td align="center">
  <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:600px;width:90%;">
    <!-- Header -->
    <tr><td style="background:linear-gradient(135deg,#13547a,#80d0c7);padding:32px 40px;text-align:center;">
      <h1 style="color:#fff;margin:0;font-size:1.8rem;font-weight:800;letter-spacing:-.02em;">
        🎓 {$siteName}
      </h1>
      <p style="color:rgba(255,255,255,.8);margin:6px 0 0;font-size:.9rem;">Học tiếng Anh thông minh với AI</p>
    </td></tr>
    <!-- Body -->
    <tr><td style="padding:36px 40px;color:#374151;font-size:.97rem;line-height:1.7;">
      {$body}
    </td></tr>
    <!-- Footer -->
    <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
      <p style="color:#9ca3af;font-size:.8rem;margin:0;">
        © {$year} {$siteName} · <a href="{$baseUrl}" style="color:#13547a;text-decoration:none;">{$baseUrl}</a>
      </p>
      <p style="color:#9ca3af;font-size:.75rem;margin:4px 0 0;">
        Email này được gửi tự động, vui lòng không trả lời.
      </p>
    </td></tr>
  </table>
</td></tr>
</table>
</body></html>
HTML;
    }

    // ── Template emails ──────────────────────────────────────────

    public static function chaoMung(string $toEmail, string $toName): bool|string {
        $body = "
<h2 style='color:#13547a;margin:0 0 16px;'>Chào mừng, <strong>{$toName}</strong>! 🎉</h2>
<p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>".SITE_NAME."</strong>. Hành trình chinh phục tiếng Anh của bạn bắt đầu từ đây!</p>
<div style='background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:20px 24px;margin:24px 0;'>
  <p style='margin:0 0 12px;font-weight:700;color:#15803d;'>🚀 Bắt đầu ngay với gói Cơ bản miễn phí:</p>
  <ul style='margin:0;padding-left:20px;color:#374151;'>
    <li style='margin-bottom:6px;'>📚 Truy cập các bài học cơ bản</li>
    <li style='margin-bottom:6px;'>🤖 5 tin nhắn AI mỗi ngày</li>
    <li style='margin-bottom:6px;'>📖 Học 200+ từ vựng</li>
    <li>🎮 Chơi tất cả mini games</li>
  </ul>
</div>
<div style='text-align:center;margin:28px 0;'>
  <a href='".BASE_URL."/bang-dieu-khien' style='background:linear-gradient(135deg,#13547a,#80d0c7);color:#fff;text-decoration:none;padding:14px 36px;border-radius:99px;font-weight:700;font-size:1rem;display:inline-block;'>
    🎓 Vào học ngay
  </a>
</div>
<p style='color:#6b7280;font-size:.875rem;'>Nếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email.</p>
";
        return self::gui($toEmail, $toName, '🎉 Chào mừng đến với '.SITE_NAME.'!', $body);
    }

    public static function quenMatKhau(string $toEmail, string $toName, string $token): bool|string {
        $link = BASE_URL.'/dat-lai-mat-khau?token='.$token;
        $body = "
<h2 style='color:#13547a;margin:0 0 16px;'>Đặt lại mật khẩu 🔑</h2>
<p>Xin chào <strong>{$toName}</strong>,</p>
<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản <strong>{$toEmail}</strong>.</p>
<div style='text-align:center;margin:32px 0;'>
  <a href='{$link}' style='background:linear-gradient(135deg,#e11d48,#9f1239);color:#fff;text-decoration:none;padding:14px 36px;border-radius:99px;font-weight:700;font-size:1rem;display:inline-block;'>
    🔑 Đặt lại mật khẩu
  </a>
</div>
<p style='color:#6b7280;font-size:.875rem;'>Liên kết này có hiệu lực trong <strong>30 phút</strong>.</p>
<p style='color:#6b7280;font-size:.875rem;'>Nếu không phải bạn yêu cầu, hãy bỏ qua email này. Tài khoản của bạn vẫn an toàn.</p>
<div style='background:#fef3c7;border:1.5px solid #fcd34d;border-radius:10px;padding:12px 16px;margin-top:20px;'>
  <p style='margin:0;font-size:.82rem;color:#92400e;'>🔒 Hoặc copy link: <code style='word-break:break-all;'>{$link}</code></p>
</div>
";
        return self::gui($toEmail, $toName, '🔑 Đặt lại mật khẩu '.SITE_NAME, $body);
    }

    public static function nangCapGoi(string $toEmail, string $toName, string $goi): bool|string {
        $goiLabel = ['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'][$goi] ?? $goi;
        $goiEmoji = ['basic'=>'🟢','advanced'=>'🟡','premium'=>'🔴'][$goi] ?? '⭐';
        $body = "
<h2 style='color:#13547a;margin:0 0 16px;'>Nâng cấp thành công! {$goiEmoji}</h2>
<p>Xin chào <strong>{$toName}</strong>,</p>
<p>Tài khoản của bạn đã được nâng cấp lên gói <strong>{$goiEmoji} {$goiLabel}</strong>. Chúc mừng!</p>
<div style='background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:20px 24px;margin:24px 0;text-align:center;'>
  <div style='font-size:2.5rem;margin-bottom:8px;'>{$goiEmoji}</div>
  <div style='font-size:1.3rem;font-weight:800;color:#15803d;'>Gói {$goiLabel}</div>
  <div style='color:#6b7280;font-size:.85rem;margin-top:4px;'>Đã kích hoạt trên tài khoản của bạn</div>
</div>
<div style='text-align:center;margin:28px 0;'>
  <a href='".BASE_URL."/bang-dieu-khien' style='background:linear-gradient(135deg,#13547a,#80d0c7);color:#fff;text-decoration:none;padding:14px 36px;border-radius:99px;font-weight:700;font-size:1rem;display:inline-block;'>
    🚀 Bắt đầu học ngay
  </a>
</div>
";
        return self::gui($toEmail, $toName, "{$goiEmoji} Nâng cấp thành công – Gói {$goiLabel}", $body);
    }
}
