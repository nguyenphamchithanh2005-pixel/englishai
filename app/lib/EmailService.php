<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

class EmailService {
    private PHPMailer $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host       = MAIL_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = MAIL_USER;
        $this->mail->Password   = MAIL_PASS;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = MAIL_PORT;
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->setFrom(MAIL_FROM, SITE_NAME);
        $this->mail->isHTML(true);
    }

    /** Gửi email chào mừng sau đăng ký */
    public function guiChaoMung(string $email, string $hoTen): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $hoTen);
            $this->mail->Subject = '🎉 Chào mừng bạn đến với ' . SITE_NAME . '!';
            $this->mail->Body    = $this->template('chao_mung', [
                'ten'     => $hoTen,
                'link_hoc'=> BASE_URL . '/bai-hoc',
                'link_ai' => BASE_URL . '/chat-ai',
            ]);
            $this->mail->AltBody = "Chào {$hoTen}! Tài khoản của bạn tại " . SITE_NAME . " đã được tạo thành công. Bắt đầu học tại: " . BASE_URL;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email chào mừng lỗi: ' . $e->getMessage());
            return false;
        }
    }

    /** Gửi link đặt lại mật khẩu */
    public function guiResetMatKhau(string $email, string $hoTen, string $token): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $hoTen);
            $this->mail->Subject = '🔐 Đặt lại mật khẩu – ' . SITE_NAME;
            $resetLink = BASE_URL . '/dat-lai-mat-khau?token=' . $token;
            $this->mail->Body = $this->template('reset_mat_khau', [
                'ten'        => $hoTen,
                'reset_link' => $resetLink,
            ]);
            $this->mail->AltBody = "Xin chào {$hoTen},\nNhấn vào link sau để đặt lại mật khẩu (hết hạn sau 1 giờ):\n{$resetLink}";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email reset mật khẩu lỗi: ' . $e->getMessage());
            return false;
        }
    }

    /** Gửi thông báo nâng cấp gói */
    public function guiNangCap(string $email, string $hoTen, string $goi): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $hoTen);
            $goiLabel = ['basic'=>'Cơ bản','advanced'=>'Nâng cao','premium'=>'Cấp cao'][$goi] ?? $goi;
            $this->mail->Subject = '⭐ Nâng cấp thành công – ' . SITE_NAME;
            $this->mail->Body = $this->template('nang_cap', [
                'ten'       => $hoTen,
                'goi'       => $goiLabel,
                'link_hoc'  => BASE_URL . '/bai-hoc',
            ]);
            $this->mail->AltBody = "Chúc mừng {$hoTen}! Bạn đã nâng cấp lên gói {$goiLabel} thành công.";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email nâng cấp lỗi: ' . $e->getMessage());
            return false;
        }
    }

    /** Template HTML email */
    private function template(string $loai, array $vars): string {
        $ten  = htmlspecialchars($vars['ten'] ?? '');
        $site = SITE_NAME;
        $url  = BASE_URL;

        $header = "
        <div style='background:linear-gradient(135deg,#13547a,#80d0c7);padding:32px 24px;text-align:center;border-radius:12px 12px 0 0'>
          <h1 style='color:#fff;margin:0;font-size:26px;font-weight:900;letter-spacing:-.03em'>🎓 {$site}</h1>
          <p style='color:rgba(255,255,255,.8);margin:6px 0 0;font-size:14px'>Học tiếng Anh thông minh cùng AI</p>
        </div>";

        $footer = "
        <div style='background:#f8fafc;padding:20px 24px;border-radius:0 0 12px 12px;border-top:1px solid #e2e8f0;text-align:center'>
          <p style='color:#94a3b8;font-size:12px;margin:0'>© " . date('Y') . " {$site} · <a href='{$url}' style='color:#13547a'>Truy cập website</a></p>
          <p style='color:#cbd5e1;font-size:11px;margin:4px 0 0'>Email này được gửi tự động, vui lòng không trả lời.</p>
        </div>";

        switch ($loai) {
            case 'chao_mung':
                $body = "
                <div style='padding:32px 24px'>
                  <h2 style='color:#111;font-size:22px;margin:0 0 12px'>Chào mừng {$ten}! 🎉</h2>
                  <p style='color:#475569;line-height:1.7;margin:0 0 20px'>Tài khoản của bạn đã được tạo thành công tại <strong>{$site}</strong>. Hãy bắt đầu hành trình học tiếng Anh cùng AI ngay hôm nay!</p>
                  <div style='background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:16px 20px;margin:0 0 24px'>
                    <p style='margin:0;font-weight:700;color:#15803d;font-size:14px'>✅ Gói Cơ bản – Miễn phí bao gồm:</p>
                    <ul style='margin:8px 0 0;padding-left:20px;color:#166534;font-size:13px'>
                      <li>Tất cả bài học cơ bản</li>
                      <li>5 tin nhắn AI mỗi ngày</li>
                      <li>200+ từ vựng</li>
                      <li>Trò chơi học tiếng Anh</li>
                    </ul>
                  </div>
                  <div style='text-align:center;margin:28px 0'>
                    <a href='" . ($vars['link_hoc'] ?? $url) . "' style='background:linear-gradient(135deg,#13547a,#80d0c7);color:#fff;padding:14px 36px;border-radius:99px;text-decoration:none;font-weight:700;font-size:16px;display:inline-block'>
                      📚 Bắt đầu học ngay
                    </a>
                  </div>
                  <p style='color:#94a3b8;font-size:13px;text-align:center'>Hoặc thử ngay <a href='" . ($vars['link_ai'] ?? $url) . "' style='color:#13547a;font-weight:600'>Chat AI</a> để luyện tập tiếng Anh</p>
                </div>";
                break;

            case 'reset_mat_khau':
                $body = "
                <div style='padding:32px 24px'>
                  <h2 style='color:#111;font-size:22px;margin:0 0 12px'>Đặt lại mật khẩu 🔐</h2>
                  <p style='color:#475569;line-height:1.7;margin:0 0 20px'>Xin chào <strong>{$ten}</strong>,<br>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                  <div style='background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:14px 18px;margin:0 0 24px'>
                    <p style='margin:0;color:#9a3412;font-size:13px'>⚠️ Link này sẽ hết hạn sau <strong>1 giờ</strong>. Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>
                  </div>
                  <div style='text-align:center;margin:28px 0'>
                    <a href='" . ($vars['reset_link'] ?? $url) . "' style='background:linear-gradient(135deg,#e11d48,#9f1239);color:#fff;padding:14px 36px;border-radius:99px;text-decoration:none;font-weight:700;font-size:16px;display:inline-block'>
                      🔑 Đặt lại mật khẩu
                    </a>
                  </div>
                  <p style='color:#94a3b8;font-size:12px;text-align:center;word-break:break-all'>Hoặc sao chép link: " . ($vars['reset_link'] ?? '') . "</p>
                </div>";
                break;

            case 'nang_cap':
                $goi = htmlspecialchars($vars['goi'] ?? '');
                $body = "
                <div style='padding:32px 24px'>
                  <h2 style='color:#111;font-size:22px;margin:0 0 12px'>Nâng cấp thành công! ⭐</h2>
                  <p style='color:#475569;line-height:1.7;margin:0 0 20px'>Xin chào <strong>{$ten}</strong>,<br>Bạn đã nâng cấp thành công lên gói <strong style='color:#d97706'>{$goi}</strong>. Khám phá toàn bộ nội dung cao cấp ngay!</p>
                  <div style='background:#fffbeb;border:1.5px solid #fcd34d;border-radius:10px;padding:16px 20px;margin:0 0 24px;text-align:center'>
                    <p style='margin:0;font-size:28px;font-weight:900;color:#d97706'>{$goi}</p>
                    <p style='margin:4px 0 0;color:#92400e;font-size:13px'>Gói học của bạn</p>
                  </div>
                  <div style='text-align:center;margin:28px 0'>
                    <a href='" . ($vars['link_hoc'] ?? $url) . "' style='background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:14px 36px;border-radius:99px;text-decoration:none;font-weight:700;font-size:16px;display:inline-block'>
                      🚀 Khám phá ngay
                    </a>
                  </div>
                </div>";
                break;

            default:
                $body = "<div style='padding:32px 24px'><p>Xin chào {$ten},</p></div>";
        }

        return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:20px;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,sans-serif'>
          <div style='max-width:560px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08)'>
            {$header}{$body}{$footer}
          </div>
        </body></html>";
    }
}
