<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/NguoiDung.php';
require_once ROOT.'/lib/MailHelper.php';

class XacThuc {

    // ── Đăng nhập ─────────────────────────────────────────────
    public function dangNhap(): void {
        global $conn;
        if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
        $loi = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new NguoiDung($conn);
            $email = sanitize($_POST['email'] ?? '');
            $user  = $model->timTheoEmail($email);
            if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
                // Ghi session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['fullname']   = $user['fullname'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['membership'] = $user['membership'];

                // Remember me cookie
                if (!empty($_POST['remember_me'])) {
                    $token = bin2hex(random_bytes(32));
                    $hash  = hash('sha256', $token.COOKIE_SECRET);
                    $uid   = $user['id'];
                    $conn->query("DELETE FROM user_tokens WHERE user_id=$uid");
                    $conn->query("INSERT INTO user_tokens (user_id,token_hash,expires_at) VALUES ($uid,'$hash',DATE_ADD(NOW(), INTERVAL ".COOKIE_REMEMBER_DAYS." DAY))");
                    setCookieSecure('remember_token', $token, COOKIE_REMEMBER_DAYS);
                }

                $_SESSION['flash'] = ['type'=>'success','message'=>'Chào mừng, <strong>'.sanitize($user['fullname']).'</strong>!'];
                redirect($user['role']==='admin' ? BASE_URL.'/quan-tri' : BASE_URL.'/bang-dieu-khien');
            }
            $loi = 'Email hoặc mật khẩu không đúng.';
        }
        render('dang_nhap', ['pageTitle'=>'Đăng nhập – '.SITE_NAME, 'loi'=>$loi]);
    }

    // ── Đăng ký ───────────────────────────────────────────────
    public function dangKy(): void {
        global $conn;
        if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
        $loi = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hoTen = sanitize($_POST['fullname'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $mk    = $_POST['password'] ?? '';
            $mk2   = $_POST['password2'] ?? '';
            if (strlen($hoTen) < 2)                        $loi[] = 'Họ tên tối thiểu 2 ký tự.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $loi[] = 'Email không hợp lệ.';
            if (strlen($mk) < 6)                           $loi[] = 'Mật khẩu tối thiểu 6 ký tự.';
            if ($mk !== $mk2)                              $loi[] = 'Xác nhận mật khẩu không khớp.';
            if (empty($loi)) {
                $model = new NguoiDung($conn);
                if ($model->timTheoEmail($email)) {
                    $loi[] = 'Email đã được đăng ký.';
                } else {
                    $uid = $model->dangKy($hoTen, $email, $mk);
                    $conn->query("INSERT INTO notifications(user_id,title,message,type) VALUES($uid,'Chào mừng!','Tài khoản đã tạo thành công!','success')");
                    // Gửi email chào mừng
                    if (MAIL_ENABLED) MailHelper::chaoMung($email, $hoTen);
                    $_SESSION['flash'] = ['type'=>'success','message'=>'🎉 Đăng ký thành công! Kiểm tra email để xác nhận.'];
                    redirect(BASE_URL.'/dang-nhap');
                }
            }
        }
        render('dang_ky', ['pageTitle'=>'Đăng ký – '.SITE_NAME, 'loi'=>$loi]);
    }

    // ── Đăng xuất ─────────────────────────────────────────────
    public function dangXuat(): void {
        global $conn;
        // Xóa remember-me token
        if (!empty($_COOKIE['remember_token'])) {
            $token = preg_replace('/[^a-f0-9]/', '', $_COOKIE['remember_token']);
            if ($token && $conn) {
                $hash = hash('sha256', $token.COOKIE_SECRET);
                $conn->query("DELETE FROM user_tokens WHERE token_hash='$hash'");
            }
            deleteCookie('remember_token');
        }
        session_destroy();
        redirect(BASE_URL.'/dang-nhap');
    }

    // ── Quên mật khẩu ─────────────────────────────────────────
    public function quenMatKhau(): void {
        global $conn;
        if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
        $thongBao = '';
        $loai = 'info';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $model = new NguoiDung($conn);
            $user  = $model->timTheoEmail($email);
            // Luôn báo thành công để tránh user enumeration
            $thongBao = '✅ Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu. Kiểm tra hộp thư (kể cả Spam).';
            $loai = 'success';
            if ($user) {
                // Xóa token cũ, tạo mới
                $safeEmail = addslashes($email);
                $conn->query("DELETE FROM password_resets WHERE email='$safeEmail'");
                $token = bin2hex(random_bytes(32));
                $hash  = hash('sha256', $token.COOKIE_SECRET);
                $conn->query("INSERT INTO password_resets (email,token_hash,expires_at) VALUES ('$safeEmail','$hash',DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
                if (MAIL_ENABLED) {
                    MailHelper::quenMatKhau($email, $user['fullname'], $token);
                } else {
                    // Dev mode: hiện link trực tiếp
                    $thongBao .= ' <br><small class="text-muted">[Dev] <a href="'.BASE_URL.'/dat-lai-mat-khau?token='.$token.'">Click để đặt lại</a></small>';
                }
            }
        }
        render('quen_mat_khau', ['pageTitle'=>'Quên mật khẩu – '.SITE_NAME, 'thongBao'=>$thongBao, 'loai'=>$loai]);
    }

    // ── Đặt lại mật khẩu ──────────────────────────────────────
    public function datLaiMatKhau(): void {
        global $conn;
        if (isLoggedIn()) redirect(BASE_URL.'/bang-dieu-khien');
        $token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
        $loi   = '';
        $ok    = false;
        if (!$token) redirect(BASE_URL.'/dang-nhap');
        $hash = hash('sha256', $token.COOKIE_SECRET);
        $reset = $conn->query("SELECT * FROM password_resets WHERE token_hash='$hash' AND expires_at>NOW() AND used=0 LIMIT 1")->fetch_assoc();
        if (!$reset) {
            $loi = 'Liên kết không hợp lệ hoặc đã hết hạn.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mk  = $_POST['password'] ?? '';
            $mk2 = $_POST['password2'] ?? '';
            if (strlen($mk) < 6)  $loi = 'Mật khẩu tối thiểu 6 ký tự.';
            elseif ($mk !== $mk2) $loi = 'Xác nhận mật khẩu không khớp.';
            else {
                $model = new NguoiDung($conn);
                $user  = $model->timTheoEmail($reset['email']);
                if ($user) {
                    $model->capNhat($user['id'], ['password'=>$mk]);
                    $conn->query("UPDATE password_resets SET used=1 WHERE token_hash='$hash'");
                    $conn->query("DELETE FROM user_tokens WHERE user_id=".$user['id']); // logout all devices
                    $_SESSION['flash'] = ['type'=>'success','message'=>'✅ Mật khẩu đã đặt lại thành công!'];
                    redirect(BASE_URL.'/dang-nhap');
                }
            }
        }
        render('dat_lai_mat_khau', ['pageTitle'=>'Đặt lại mật khẩu – '.SITE_NAME, 'token'=>$token, 'loi'=>$loi, 'ok'=>$ok, 'valid'=>(bool)$reset]);
    }
}
