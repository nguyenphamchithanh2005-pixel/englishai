<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/TuVung.php';
class TuVungCtrl {
    public function danhSach(): void {
        global $conn;
        $loc=['cap_do'=>sanitize($_GET['cap_do']??''),'chu_de'=>sanitize($_GET['chu_de']??''),'tim_kiem'=>sanitize($_GET['tim_kiem']??'')];
        $che_do=sanitize($_GET['che_do']??'danh-sach');
        $trang=max(1,(int)($_GET['trang']??1));
        $model=new TuVung($conn);
        if($_SERVER['REQUEST_METHOD']==='POST'&&isLoggedIn()&&isset($_POST['danh_dau'])){
            $tt=in_array($_POST['trang_thai'],['new','learning','learned'])?$_POST['trang_thai']:'learning';
            $model->danhDau((int)$_SESSION['user_id'],(int)$_POST['tu_vung_id'],$tt);
            redirect(BASE_URL.'/tu-vung?'.http_build_query(array_merge($loc,['che_do'=>$che_do,'trang'=>$trang])));
        }
        $data=$model->danhSach($loc,$trang);
        render('tu_vung/danh_sach',['pageTitle'=>'Từ vựng – '.SITE_NAME,'tuVung'=>$data['danh_sach'],'tong'=>$data['tong'],'tongTrang'=>$data['tong_trang'],'trangHien'=>$data['trang_hien'],'demCapDo'=>$model->demCapDo($loc),'danhSachChuDe'=>$model->danhSachChuDe(),'loc'=>$loc,'cheDo'=>$che_do]);
    }
}
