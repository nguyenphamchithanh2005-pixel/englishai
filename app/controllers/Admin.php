<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/BaiHoc.php';
require_once ROOT.'/app/models/TuVung.php';
require_once ROOT.'/app/models/NguoiDung.php';
class Admin {
    public function tongQuan(): void {
        global $conn; requireAdmin();
        render('admin/tong_quan',['pageTitle'=>'Quản trị – '.SITE_NAME,'thongKeBaiHoc'=>(new BaiHoc($conn))->thongKe(),'thongKeND'=>(new NguoiDung($conn))->thongKe(),'tongTuVung'=>(new TuVung($conn))->tongSo(),'tongChat'=>(int)$conn->query("SELECT COUNT(*) c FROM ai_conversations")->fetch_assoc()['c'],'nguoiMoiNhat'=>(new NguoiDung($conn))->danhSach()]);
    }
    public function quanLyBaiHoc(): void {
        global $conn; requireAdmin(); $model=new BaiHoc($conn);
        $hd=sanitize($_GET['hanh_dong']??'ds'); $id=(int)($_GET['id']??0);
        if($hd==='xoa'&&$id){$model->xoa($id);redirect(BASE_URL.'/quan-tri/bai-hoc');}
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['luu'])){
            $d=['title'=>sanitize($_POST['tieu_de']),'category_id'=>(int)$_POST['danh_muc'],'content'=>$_POST['noi_dung'],'level'=>sanitize($_POST['cap_do']),'lesson_type'=>sanitize($_POST['loai_bai']),'duration'=>(int)$_POST['thoi_gian'],'is_active'=>isset($_POST['hien_thi'])?1:0];
            $id?$model->sua($id,$d):$model->them($d);
            $_SESSION['flash']=['type'=>'success','message'=>'Lưu bài học thành công!'];
            redirect(BASE_URL.'/quan-tri/bai-hoc');
        }
        render('admin/quan_ly_bai_hoc',['pageTitle'=>'Quản lý bài học','model'=>$model,'danhMuc'=>$model->danhMuc(),'danhSach'=>$model->danhSach([]),'hd'=>$hd,'id'=>$id,'editBH'=>$id?$model->timTheoId($id):null]);
    }
    public function quanLyNguoiDung(): void {
        global $conn; requireAdmin(); $model=new NguoiDung($conn);
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['doi_goi'])){
            $uid=(int)$_POST['uid']; $goi=in_array($_POST['goi'],['basic','advanced','premium'])?$_POST['goi']:'basic';
            $model->capNhat($uid,['membership'=>$goi]); redirect(BASE_URL.'/quan-tri/nguoi-dung');
        }
        render('admin/quan_ly_nguoi_dung',['pageTitle'=>'Quản lý người dùng','danhSach'=>$model->danhSach(sanitize($_GET['tim_kiem']??''))]);
    }
    public function quanLyTuVung(): void {
        global $conn; requireAdmin(); $model=new TuVung($conn);
        $hd=sanitize($_GET['hanh_dong']??'ds'); $id=(int)($_GET['id']??0);
        if($hd==='xoa'&&$id){$model->xoa($id);redirect(BASE_URL.'/quan-tri/tu-vung');}
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['luu'])){
            $d=['word'=>sanitize($_POST['tu']),'pronunciation'=>sanitize($_POST['phat_am']??''),'definition'=>sanitize($_POST['dinh_nghia']),'example'=>sanitize($_POST['vi_du']??''),'translation'=>sanitize($_POST['nghia']),'level'=>sanitize($_POST['cap_do']),'category'=>sanitize($_POST['chu_de']??'')];
            $id?$model->sua($id,$d):$model->them($d);
            $_SESSION['flash']=['type'=>'success','message'=>'Lưu từ vựng thành công!'];
            redirect(BASE_URL.'/quan-tri/tu-vung');
        }
        $tv=$model->danhSach([],1,200);
        render('admin/quan_ly_tu_vung',['pageTitle'=>'Quản lý từ vựng','danhSach'=>$tv['danh_sach'],'hd'=>$hd,'id'=>$id,'editTV'=>$id?$model->timTheoId($id):null]);
    }
}
