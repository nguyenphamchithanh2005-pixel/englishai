<?php
/** Model BaiTap – câu hỏi trắc nghiệm */
class BaiTap {
    private $conn;
    public function __construct($conn) { $this->conn=$conn; }

    public function layTheoBaiHoc(int $lid): array {
        $r=$this->conn->query("SELECT * FROM exercises WHERE lesson_id=$lid ORDER BY order_num,id");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
    public function cham(array $baiTap,array $dauVao): array {
        $dung=0; $ketQua=[];
        foreach($baiTap as $bt){
            $chon=strtoupper($dauVao[$bt['id']]??'');
            $ok=$chon===strtoupper($bt['correct_answer']);
            if($ok)$dung++;
            $ketQua[$bt['id']]=['nguoi_dung'=>$chon,'dap_an_dung'=>$bt['correct_answer'],'dung_khong'=>$ok,'giai_thich'=>$bt['explanation']];
        }
        $tong=count($baiTap);
        return ['dung'=>$dung,'tong'=>$tong,'diem'=>$tong>0?round($dung/$tong*100):0,'chi_tiet'=>$ketQua];
    }
    public function ngauNhien(int $so=10): array {
        $r=$this->conn->query("SELECT e.*,l.title ten_bai FROM exercises e JOIN lessons l ON e.lesson_id=l.id ORDER BY RAND() LIMIT $so");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
}
