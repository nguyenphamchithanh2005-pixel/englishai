<?php
/** Model TuVung – từ vựng tiếng Anh */
class TuVung {
    private $conn;
    public function __construct($conn) { $this->conn=$conn; }

    public function danhSach(array $loc=[],int $trang=1,int $soMoi=60): array {
        $w="WHERE 1";
        if(!empty($loc['cap_do']))   $w.=" AND v.level='".addslashes($loc['cap_do'])."'";
        if(!empty($loc['chu_de']))   $w.=" AND v.category='".addslashes($loc['chu_de'])."'";
        if(!empty($loc['tim_kiem'])){$s=addslashes($loc['tim_kiem']); $w.=" AND (v.word LIKE '%$s%' OR v.translation LIKE '%$s%' OR v.definition LIKE '%$s%')";}
        $thu_tu="FIELD(v.level,'basic','advanced','premium')";
        $offset=($trang-1)*$soMoi;
        $tong=(int)$this->conn->query("SELECT COUNT(*) c FROM vocabulary v $w")->fetch_assoc()['c'];
        $uid=isset($_SESSION['user_id'])?(int)$_SESSION['user_id']:0;
        $sql=$uid?"SELECT v.*,uv.status,uv.review_count FROM vocabulary v LEFT JOIN user_vocabulary uv ON v.id=uv.vocab_id AND uv.user_id=$uid $w ORDER BY $thu_tu,v.word LIMIT $soMoi OFFSET $offset"
                 :"SELECT v.*,NULL AS status,0 AS review_count FROM vocabulary v $w ORDER BY $thu_tu,v.word LIMIT $soMoi OFFSET $offset";
        $r=$this->conn->query($sql); $rows=[];
        while($row=$r->fetch_assoc())$rows[]=$row;
        return ['danh_sach'=>$rows,'tong'=>$tong,'tong_trang'=>(int)ceil($tong/$soMoi),'trang_hien'=>$trang];
    }
    public function demCapDo(array $loc=[]): array {
        $counts=[];
        foreach(['basic','advanced','premium'] as $lv){
            $w="WHERE level='$lv'";
            if(!empty($loc['chu_de'])) $w.=" AND category='".addslashes($loc['chu_de'])."'";
            if(!empty($loc['tim_kiem'])) $w.=" AND (word LIKE '%".addslashes($loc['tim_kiem'])."%' OR translation LIKE '%".addslashes($loc['tim_kiem'])."%')";
            $counts[$lv]=(int)$this->conn->query("SELECT COUNT(*) c FROM vocabulary $w")->fetch_assoc()['c'];
        }
        return $counts;
    }
    public function danhSachChuDe(): array {
        $r=$this->conn->query("SELECT DISTINCT category FROM vocabulary WHERE category IS NOT NULL ORDER BY category");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row['category']; return $rows;
    }
    public function danhDau(int $uid,int $vid,string $tt): void {
        $now=date('Y-m-d H:i:s');
        $this->conn->query("INSERT INTO user_vocabulary (user_id,vocab_id,status,review_count,last_reviewed) VALUES ($uid,$vid,'$tt',1,'$now') ON DUPLICATE KEY UPDATE status='$tt',review_count=review_count+1,last_reviewed='$now'");
    }
    public function soTuDaHoc(int $uid): int {
        return (int)$this->conn->query("SELECT COUNT(*) c FROM user_vocabulary WHERE user_id=$uid AND status='learned'")->fetch_assoc()['c'];
    }
    public function them(array $d): int {
        $s=$this->conn->prepare("INSERT INTO vocabulary (word,pronunciation,definition,example,translation,level,category) VALUES (?,?,?,?,?,?,?)");
        $s->bind_param('sssssss',$d['word'],$d['pronunciation']??'',$d['definition'],$d['example']??'',$d['translation'],$d['level'],$d['category']??'');
        $s->execute(); return $this->conn->insert_id;
    }
    public function sua(int $id,array $d): void {
        $s=$this->conn->prepare("UPDATE vocabulary SET word=?,pronunciation=?,definition=?,example=?,translation=?,level=?,category=? WHERE id=?");
        $s->bind_param('sssssssi',$d['word'],$d['pronunciation']??'',$d['definition'],$d['example']??'',$d['translation'],$d['level'],$d['category']??'',$id);
        $s->execute();
    }
    public function xoa(int $id): void { $this->conn->query("DELETE FROM vocabulary WHERE id=$id"); }
    public function timTheoId(int $id): ?array { $r=$this->conn->query("SELECT * FROM vocabulary WHERE id=$id"); return $r?$r->fetch_assoc():null; }
    public function tongSo(): int { return (int)$this->conn->query("SELECT COUNT(*) c FROM vocabulary")->fetch_assoc()['c']; }
}
