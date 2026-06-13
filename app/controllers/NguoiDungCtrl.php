<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/NguoiDung.php';
require_once ROOT.'/app/models/BaiHoc.php';
require_once ROOT.'/app/models/TuVung.php';
class NguoiDungCtrl {
    public function bangDieuKhien(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id'];
        $thongKe=$conn->query("SELECT COUNT(*) tong,SUM(completed) hoan_thanh,COALESCE(AVG(NULLIF(score,0)),0) diem_tb FROM user_progress WHERE user_id=$uid")->fetch_assoc();
        $tongBai=(new BaiHoc($conn))->thongKe()['tong'];
        $aiUsed=(new NguoiDung($conn))->kiemTraAI($uid);
        render('bang_dieu_khien',['pageTitle'=>'Dashboard – '.SITE_NAME,'thongKe'=>$thongKe,'tongBai'=>$tongBai,'soTuDaHoc'=>(new TuVung($conn))->soTuDaHoc($uid),'aiUsed'=>$aiUsed,'aiLimit'=>getAILimit(),'baiGanDay'=>(new BaiHoc($conn))->danhSach([]),'tienDo'=>(new BaiHoc($conn))->tienDo($uid),'tiLePhanTram'=>$tongBai>0?round(($thongKe['hoan_thanh']??0)/$tongBai*100):0]);
    }
    public function hoSo(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id'];
        $model=new NguoiDung($conn); $user=$model->timTheoId($uid); $thongBao='';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $hoTen=sanitize($_POST['fullname']??''); $mkCu=$_POST['password_cu']??''; $mkMoi=$_POST['password_moi']??'';
            if(strlen($hoTen)<2){$thongBao='error:Họ tên quá ngắn.';}
            elseif($mkMoi&&!password_verify($mkCu,$user['password'])){$thongBao='error:Mật khẩu hiện tại không đúng.';}
            else{$d=['fullname'=>$hoTen];if($mkMoi)$d['password']=$mkMoi;$model->capNhat($uid,$d);$_SESSION['fullname']=$hoTen;$thongBao='success:Cập nhật thành công!';$user=$model->timTheoId($uid);}
        }
        $tk=$conn->query("SELECT COUNT(*) c,COALESCE(AVG(NULLIF(score,0)),0) avg FROM user_progress WHERE user_id=$uid AND completed=1")->fetch_assoc();
        render('ho_so',['pageTitle'=>'Hồ sơ – '.SITE_NAME,'user'=>$user,'thongBao'=>$thongBao,'soTuDaHoc'=>(new TuVung($conn))->soTuDaHoc($uid),'soHoanThanh'=>$tk['c'],'diemTB'=>round($tk['avg'])]);
    }
    public function thongBao(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id'];
        $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");
        $r=$conn->query("SELECT * FROM notifications WHERE user_id=$uid ORDER BY created_at DESC LIMIT 30");
        $ds=[]; while($row=$r->fetch_assoc())$ds[]=$row;
        render('thong_bao',['pageTitle'=>'Thông báo – '.SITE_NAME,'danhSach'=>$ds]);
    }
    public function nangCap(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id'];
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['goi'])){
            $goi=in_array($_POST['goi'],['basic','advanced','premium'])?$_POST['goi']:'basic';
            $conn->query("UPDATE users SET membership='$goi' WHERE id=$uid"); $_SESSION['membership']=$goi;
            // Gửi email thông báo nâng cấp
            if (MAIL_ENABLED) {
                require_once ROOT.'/lib/MailHelper.php';
                $user = (new NguoiDung($conn))->timTheoId($uid);
                if ($user) MailHelper::nangCapGoi($user['email'], $user['fullname'], $goi);
            }
            // Cookie lưu gói hiện tại (30 ngày, không httponly để JS đọc được)
            setcookie('user_membership', $goi, time()+30*86400, '/');
            $_SESSION['flash']=['type'=>'success','message'=>'🎉 Nâng cấp thành công lên <strong>'.getLevelLabel($goi).'</strong>!'];
            redirect(BASE_URL.'/bang-dieu-khien');
        }
        render('nang_cap',['pageTitle'=>'Nâng cấp – '.SITE_NAME]);
    }
}
