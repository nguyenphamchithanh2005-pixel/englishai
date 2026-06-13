<?php
/** Model BaiHoc – bài học tiếng Anh */
class BaiHoc {
    private $conn;
    public function __construct($conn) { $this->conn=$conn; }

    public function danhSach(array $loc=[]): array {
        $w="WHERE l.is_active=1";
        if(!empty($loc['tim_kiem'])) $w.=" AND l.title LIKE '%".addslashes($loc['tim_kiem'])."%'";
        if(!empty($loc['cap_do']))   $w.=" AND l.level='".addslashes($loc['cap_do'])."'";
        if(!empty($loc['danh_muc'])) $w.=" AND l.category_id=".(int)$loc['danh_muc'];
        if(!empty($loc['loai_bai'])) $w.=" AND l.lesson_type='".addslashes($loc['loai_bai'])."'";
        $r=$this->conn->query("SELECT l.*,c.name ten_danh_muc FROM lessons l LEFT JOIN categories c ON l.category_id=c.id $w ORDER BY l.level,l.order_num,l.id");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
    public function timTheoId(int $id): ?array {
        $r=$this->conn->query("SELECT l.*,c.name ten_danh_muc FROM lessons l LEFT JOIN categories c ON l.category_id=c.id WHERE l.id=$id AND l.is_active=1");
        return $r?$r->fetch_assoc():null;
    }
    public function baiMoiNhat(int $gioi_han=6): array {
        $r=$this->conn->query("SELECT l.*,c.name ten_danh_muc FROM lessons l LEFT JOIN categories c ON l.category_id=c.id WHERE l.is_active=1 ORDER BY l.id DESC LIMIT $gioi_han");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
    public function tienDo(int $uid): array {
        $r=$this->conn->query("SELECT lesson_id,completed,score FROM user_progress WHERE user_id=$uid");
        $map=[]; while($row=$r->fetch_assoc())$map[$row['lesson_id']]=$row; return $map;
    }
    public function luuTienDo(int $uid,int $lid,int $diem): void {
        $now=date('Y-m-d H:i:s');
        $this->conn->query("INSERT INTO user_progress (user_id,lesson_id,completed,score,completed_at) VALUES ($uid,$lid,1,$diem,'$now') ON DUPLICATE KEY UPDATE completed=1,score=$diem,completed_at='$now'");
    }
    public function baiTiepTheo(int $id): ?array {
        $r=$this->conn->query("SELECT id,title FROM lessons WHERE is_active=1 AND id>$id ORDER BY id LIMIT 1");
        return $r?$r->fetch_assoc():null;
    }
    public function danhMuc(): array {
        $r=$this->conn->query("SELECT * FROM categories ORDER BY name");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
    public function them(array $d): int {
        $slug=preg_replace('/[^a-z0-9]+/','-',strtolower(iconv('UTF-8','ASCII//TRANSLIT',$d['title']))).'-'.time();
        $s=$this->conn->prepare("INSERT INTO lessons (title,slug,category_id,content,level,lesson_type,duration,is_active) VALUES (?,?,?,?,?,?,?,?)");
        $s->bind_param('ssisssii',$d['title'],$slug,$d['category_id'],$d['content'],$d['level'],$d['lesson_type'],$d['duration'],$d['is_active']??1);
        $s->execute(); return $this->conn->insert_id;
    }
    public function sua(int $id,array $d): void {
        $s=$this->conn->prepare("UPDATE lessons SET title=?,category_id=?,content=?,level=?,lesson_type=?,duration=?,is_active=? WHERE id=?");
        $s->bind_param('sissssii',$d['title'],$d['category_id'],$d['content'],$d['level'],$d['lesson_type'],$d['duration'],$d['is_active']??1,$id);
        $s->execute();
    }
    public function xoa(int $id): void { $this->conn->query("DELETE FROM lessons WHERE id=$id"); }
    public function thongKe(): array {
        return ['tong'=>(int)$this->conn->query("SELECT COUNT(*) c FROM lessons WHERE is_active=1")->fetch_assoc()['c']];
    }
}
