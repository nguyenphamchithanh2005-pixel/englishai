<?php
/**
 * EnglishAI – Front Controller (MVC)
 * Mọi request đều đi qua file này
 */
define('ROOT', __DIR__);
require_once ROOT . '/config/config.php';

// ── Parse URL ────────────────────────────────────────────────
$uri  = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$base = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');
if ($base && strpos($uri, $base) === 0) {
    $uri = trim(substr($uri, strlen($base)), '/');
}
$uri = $uri ?: '';

// ── Bảng định tuyến (Route Table) ────────────────────────────
// [URI] => [Controller, method]
$routes = [
    ''                        => ['TrangChu',      'index'],
    'dang-nhap'               => ['XacThuc',       'dangNhap'],
    'dang-ky'                 => ['XacThuc',       'dangKy'],
    'dang-xuat'               => ['XacThuc',       'dangXuat'],
    'quen-mat-khau'           => ['XacThuc',       'quenMatKhau'],
    'dat-lai-mat-khau'        => ['XacThuc',       'datLaiMatKhau'],
    'bang-dieu-khien'         => ['NguoiDungCtrl', 'bangDieuKhien'],
    'ho-so'                   => ['NguoiDungCtrl', 'hoSo'],
    'thong-bao'               => ['NguoiDungCtrl', 'thongBao'],
    'nang-cap'                => ['NguoiDungCtrl', 'nangCap'],
    'thanh-toan'              => ['ThanhToan', 'index'],
    'thanh-toan/xac-nhan'     => ['ThanhToan', 'xacNhan'],
    'bai-hoc'                 => ['BaiHocCtrl',    'danhSach'],
    'bai-hoc/chi-tiet'        => ['BaiHocCtrl',    'chiTiet'],
    'tu-vung'                 => ['TuVungCtrl',    'danhSach'],
    'chat-ai'                 => ['Chat',          'index'],
    'tro-choi'                => ['TroChoiCtrl',   'index'],
    'tro-choi/do-tu'          => ['TroChoiCtrl',   'doTu'],
    'tro-choi/ghep-cap'       => ['TroChoiCtrl',   'ghepCap'],
    'tro-choi/go-nhanh'       => ['TroChoiCtrl',   'goNhanh'],
    'doi-khang'               => ['TroChoiCtrl',   'doiKhang'],
    'quan-tri'                => ['Admin',         'tongQuan'],
    'quan-tri/bai-hoc'        => ['Admin',         'quanLyBaiHoc'],
    'quan-tri/nguoi-dung'     => ['Admin',         'quanLyNguoiDung'],
    'quan-tri/tu-vung'        => ['Admin',         'quanLyTuVung'],
    // API
    'api/chat'                => ['Chat',          'apiGroq'],
    'api/phien-moi'           => ['Chat',          'phienMoi'],
    'api/luu-diem'            => ['TroChoiCtrl',   'luuDiem'],
    'api/duel-diem'           => ['TroChoiCtrl',   'apiDuelDiem'],
    'api/duel-trang-thai'     => ['TroChoiCtrl',   'apiDuelTrangThai'],
];

// ── Dispatch ─────────────────────────────────────────────────
if (isset($routes[$uri])) {
    [$class, $method] = $routes[$uri];
    $file = ROOT . '/app/controllers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        (new $class())->$method();
    }
} else {
    http_response_code(404);
    render('404', ['pageTitle' => '404 – '.SITE_NAME]);
}
