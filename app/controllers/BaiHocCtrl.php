<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/BaiHoc.php';
require_once ROOT.'/app/models/BaiTap.php';
class BaiHocCtrl {
    public function danhSach(): void {
        global $conn; requireLogin();
        $loc=['tim_kiem'=>sanitize($_GET['tim_kiem']??''),'cap_do'=>sanitize($_GET['cap_do']??''),'danh_muc'=>(int)($_GET['danh_muc']??0),'loai_bai'=>sanitize($_GET['loai_bai']??'')];
        $model=new BaiHoc($conn);
        render('bai_hoc/danh_sach',['pageTitle'=>'Bài học – '.SITE_NAME,'danhSach'=>$model->danhSach($loc),'danhMuc'=>$model->danhMuc(),'tienDo'=>$model->tienDo((int)$_SESSION['user_id']),'loc'=>$loc]);
    }
    public function chiTiet(): void {
        global $conn; requireLogin();
        $id=(int)($_GET['id']??0); if(!$id)redirect(BASE_URL.'/bai-hoc');
        $model=new BaiHoc($conn); $btModel=new BaiTap($conn);
        $baiHoc=$model->timTheoId($id);
        if(!$baiHoc){$_SESSION['flash']=['type'=>'danger','message'=>'Bài học không tồn tại.'];redirect(BASE_URL.'/bai-hoc');}
        if(!canAccessLevel($baiHoc['level']))redirect(BASE_URL.'/nang-cap');
        $uid=(int)$_SESSION['user_id'];
        $conn->query("INSERT IGNORE INTO user_progress (user_id,lesson_id) VALUES ($uid,$id)");
        $baiTap=$btModel->layTheoBaiHoc($id); $ketQua=null;
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['nop_bai'])){
            $ketQua=$btModel->cham($baiTap,$_POST['dap_an']??[]);
            $model->luuTienDo($uid,$id,$ketQua['diem']);
        }
        render('bai_hoc/chi_tiet',['pageTitle'=>$baiHoc['title'].' – '.SITE_NAME,'baiHoc'=>$baiHoc,'baiTap'=>$baiTap,'ketQua'=>$ketQua,
            'tienDo'=>$conn->query("SELECT * FROM user_progress WHERE user_id=$uid AND lesson_id=$id")->fetch_assoc(),
            'baiTiepTheo'=>$model->baiTiepTheo($id),'showTTS'=>in_array($baiHoc['lesson_type'],['listening','reading'])]);
    }
}
