<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/NguoiDung.php';

class ThanhToan {

    private array $goiHopLe = ['advanced', 'premium'];

    private array $thongTinGoi = [
        'advanced' => ['name' => 'Nâng cao',  'price' => '199.000đ', 'amount' => 199000],
        'premium'  => ['name' => 'Cấp cao',   'price' => '399.000đ', 'amount' => 399000],
    ];

    // GET /thanh-toan?goi=advanced
    // Hiển thị trang thanh toán với QR chuyển khoản
    public function index(): void {
        requireLogin();
        $uid  = (int) $_SESSION['user_id'];
        $goi  = $_GET['goi'] ?? 'advanced';

        if (!in_array($goi, $this->goiHopLe)) {
            redirect(BASE_URL . '/nang-cap');
        }

        // Lưu gói vào session để dùng ở bước xác nhận
        $_SESSION['selected_plan'] = $goi;

        $info           = $this->thongTinGoi[$goi];
        $transferContent = 'ENGLISHAI_' . $uid;

        $vietqrUrl = 'https://img.vietqr.io/image/MB-123456789-compact2.png'
                   . '?amount='      . $info['amount']
                   . '&addInfo='     . urlencode($transferContent)
                   . '&accountName=' . urlencode('ENGLISH AI');

        render('thanh_toan', [
            'pageTitle'       => 'Thanh toán – ' . SITE_NAME,
            'plan'            => $goi,
            'info'            => $info,
            'vietqrUrl'       => $vietqrUrl,
            'transferContent' => $transferContent,
        ]);
    }

    // POST /thanh-toan/xac-nhan
    // Người dùng xác nhận đã chuyển khoản → nâng cấp membership
    public function xacNhan(): void {
        global $conn;
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/nang-cap');
        }

        $uid = (int) $_SESSION['user_id'];
        $goi = $_POST['goi'] ?? ($_SESSION['selected_plan'] ?? '');

        if (!in_array($goi, $this->goiHopLe)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gói không hợp lệ.'];
            redirect(BASE_URL . '/nang-cap');
        }

        // Cập nhật membership
        $stmt = $conn->prepare("UPDATE users SET membership = ? WHERE id = ?");
        $stmt->bind_param('si', $goi, $uid);
        $stmt->execute();
        $stmt->close();

        $_SESSION['membership'] = $goi;
        unset($_SESSION['selected_plan']);

        // Gửi email thông báo nếu bật
        if (defined('MAIL_ENABLED') && MAIL_ENABLED) {
            require_once ROOT . '/lib/MailHelper.php';
            $user = (new NguoiDung($conn))->timTheoId($uid);
            if ($user) {
                MailHelper::nangCapGoi($user['email'], $user['fullname'], $goi);
            }
        }

        // Lưu cookie
        setcookie('user_membership', $goi, time() + 30 * 86400, '/');

        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => '🎉 Thanh toán thành công! Gói <strong>' . getLevelLabel($goi) . '</strong> đã được kích hoạt.',
        ];
        redirect(BASE_URL . '/bang-dieu-khien');
    }
}
