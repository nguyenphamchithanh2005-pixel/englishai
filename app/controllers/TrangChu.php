<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/BaiHoc.php';
require_once ROOT.'/app/models/TuVung.php';
require_once ROOT.'/app/models/NguoiDung.php';
class TrangChu {
    public function index(): void {
        global $conn;
        render('trang_chu',['pageTitle'=>SITE_NAME.' – Học tiếng Anh thông minh với AI',
            'baiMoiNhat'=>(new BaiHoc($conn))->baiMoiNhat(6),
            'tongBaiHoc'=>(new BaiHoc($conn))->thongKe()['tong'],
            'tongTuVung'=>(new TuVung($conn))->tongSo(),
            'tongNguoiDung'=>(new NguoiDung($conn))->thongKe()['tong'],
            'danhMuc'=>(new BaiHoc($conn))->danhMuc()]);
    }
}
