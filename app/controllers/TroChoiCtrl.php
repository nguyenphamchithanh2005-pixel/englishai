<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/TroChoi.php';
require_once ROOT.'/app/models/BaiTap.php';
require_once ROOT.'/app/models/TuVung.php';
class TroChoiCtrl {
    public function index(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id']; $m=new TroChoi($conn);
        render('tro_choi/trang_chu',['pageTitle'=>'Mini Games – '.SITE_NAME,'bestWordle'=>$m->diemCao($uid,'wordle'),'bestMatch'=>$m->diemCao($uid,'match'),'bestTyping'=>$m->diemCao($uid,'typing'),'topWordle'=>$m->bangXepHang('wordle'),'topMatch'=>$m->bangXepHang('match'),'topTyping'=>$m->bangXepHang('typing'),'thangDuel'=>$m->thangCua($uid)]);
    }
    public function doTu(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id']; $today=date('Y-m-d');
        $where = "LENGTH(word)=5 AND word REGEXP '^[a-zA-Z]+$'";
        $total=(int)$conn->query("SELECT COUNT(*) c FROM vocabulary WHERE $where")->fetch_assoc()['c'];
        if(!$total)redirect(BASE_URL.'/tro-choi');
        $offset=abs(crc32($today))%$total;
        $tu=$conn->query("SELECT word,translation FROM vocabulary WHERE $where ORDER BY id LIMIT 1 OFFSET $offset")->fetch_assoc();
        if(!$tu)redirect(BASE_URL.'/tro-choi');
        $daDaHom=$conn->query("SELECT id FROM game_scores WHERE user_id=$uid AND game_type='wordle' AND DATE(played_at)='$today'")->num_rows>0;
        $r=$conn->query("SELECT UPPER(word) w FROM vocabulary WHERE $where");
        $ds=[];
        while($row=$r->fetch_assoc())$ds[]=$row['w'];
        render('tro_choi/do_tu',['pageTitle'=>'Word Puzzle – '.SITE_NAME,'tuBiMat'=>strtoupper($tu['word']),'nghia'=>$tu['translation'],'daDaHom'=>$daDaHom,'danhSachTu'=>$ds]);
    }
    public function ghepCap(): void {
        global $conn; requireLogin();
        $capDo=sanitize($_GET['cap_do']??'basic'); $soCapDo=in_array((int)($_GET['so_cap']??8),[8,12,16])?(int)($_GET['so_cap']??8):8;
        $r=$conn->query("SELECT word,translation FROM vocabulary WHERE level='$capDo' ORDER BY RAND() LIMIT $soCapDo");
        $the=[]; while($row=$r->fetch_assoc()){$the[]=['id'=>count($the),'noi_dung'=>$row['word'],'loai'=>'en'];$the[]=['id'=>count($the),'noi_dung'=>$row['translation'],'loai'=>'vi'];}
        shuffle($the);
        render('tro_choi/ghep_cap',['pageTitle'=>'Memory Match – '.SITE_NAME,'the'=>$the,'soCapDo'=>$soCapDo,'capDo'=>$capDo]);
    }
    public function goNhanh(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id'];
        $r=$conn->query("SELECT content FROM lessons WHERE is_active=1 AND lesson_type='reading' ORDER BY RAND() LIMIT 1")->fetch_assoc();
        $text=strip_tags($r['content']??'');
        $words=array_filter(explode(' ',preg_replace('/\s+/',' ',$text)),fn($w)=>strlen($w)>1&&preg_match('/^[a-zA-Z]/',$w));
        $doan=implode(' ',array_slice(array_values($words),0,50));
        if(strlen($doan)<30)$doan='The quick brown fox jumps over the lazy dog. Learning English every day will help you improve your skills significantly.';
        render('tro_choi/go_nhanh',['pageTitle'=>'Typing Race – '.SITE_NAME,'doanVan'=>$doan,'diemCao'=>(new TroChoi($conn))->diemCao($uid,'typing')]);
    }
    public function doiKhang(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id']; $m=new TroChoi($conn);
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['tao_phong'])){redirect(BASE_URL.'/doi-khang?phong='.$m->taoPhong($uid));}
        if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['tham_gia'])){
            $pid=(int)$_POST['phong_id'];
            if($m->thamGia($pid,$uid))redirect(BASE_URL.'/doi-khang?phong='.$pid);
            else{$_SESSION['flash']=['type'=>'danger','message'=>'Phòng không tồn tại hoặc đã đầy.'];redirect(BASE_URL.'/doi-khang');}
        }
        $pid=(int)($_GET['phong']??0); $phong=$pid?$m->layPhong($pid):null;
        $cauHoi=$phong?(new BaiTap($conn))->ngauNhien(10):[];
        render('tro_choi/doi_khang',['pageTitle'=>'Duel 1v1 – '.SITE_NAME,'phong'=>$phong,'phongId'=>$pid,'cauHoi'=>$cauHoi]);
    }
    public function apiDuelDiem(): void {
        header('Content-Type: application/json');
        global $conn; if(!isLoggedIn()){echo json_encode(['ok'=>false]);exit();}
        $uid  = (int)$_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);
        $phongId = (int)($data['phong_id'] ?? 0);
        $diem    = (int)($data['diem'] ?? 0);
        $xong    = (bool)($data['xong'] ?? false);
        $m = new TroChoi($conn);
        if ($xong) $m->ketThucDuel($phongId, $uid, $diem);
        else        $m->capNhatDiemDuel($phongId, $uid, $diem);
        echo json_encode(['ok'=>true]);
    }

    public function apiDuelTrangThai(): void {
        header('Content-Type: application/json');
        global $conn; if(!isLoggedIn()){echo json_encode(['ok'=>false]);exit();}
        $phongId = (int)($_GET['phong'] ?? 0);
        $phong   = (new TroChoi($conn))->layTrangThaiDuel($phongId);
        echo json_encode($phong ?: ['ok'=>false]);
    }
    public function luuDiem(): void {
        header('Content-Type: application/json'); global $conn;
        if(!isLoggedIn()){echo json_encode(['ok'=>false]);exit();}
        $uid=(int)$_SESSION['user_id']; $data=json_decode(file_get_contents('php://input'),true);
        $loai=in_array($data['type']??'',['wordle','match','typing','duel'])?$data['type']:'wordle';
        (new TroChoi($conn))->luuDiem($uid,$loai,(int)($data['score']??0),$data['details']??[]);
        echo json_encode(['ok'=>true]);
    }
}
