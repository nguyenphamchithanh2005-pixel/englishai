<?php
/** Model NguoiDung – người dùng */
class NguoiDung {
    private $conn;
    public function __construct($conn) { $this->conn=$conn; }

    public function timTheoEmail(string $email): ?array {
        $s=$this->conn->prepare("SELECT * FROM users WHERE email=?");
        $s->bind_param('s',$email); $s->execute();
        return $s->get_result()->fetch_assoc()?:null;
    }
    public function timTheoId(int $id): ?array {
        $r=$this->conn->query("SELECT * FROM users WHERE id=$id");
        return $r?$r->fetch_assoc():null;
    }
    public function dangKy(string $hoTen,string $email,string $matKhau): int {
        $hash=password_hash($matKhau,PASSWORD_DEFAULT);
        $s=$this->conn->prepare("INSERT INTO users (fullname,email,password) VALUES (?,?,?)");
        $s->bind_param('sss',$hoTen,$email,$hash); $s->execute();
        return $this->conn->insert_id;
    }
    public function capNhat(int $id,array $data): void {
        $fields=[]; $types=''; $vals=[];
        if(isset($data['fullname'])){$fields[]='fullname=?';$types.='s';$vals[]=$data['fullname'];}
        if(isset($data['password'])){$fields[]='password=?';$types.='s';$vals[]=password_hash($data['password'],PASSWORD_DEFAULT);}
        if(isset($data['membership'])){$fields[]='membership=?';$types.='s';$vals[]=$data['membership'];}
        if(empty($fields))return;
        $types.='i'; $vals[]=$id;
        $s=$this->conn->prepare("UPDATE users SET ".implode(',',$fields)." WHERE id=?");
        $s->bind_param($types,...$vals); $s->execute();
    }
    public function danhSach(string $timKiem=''): array {
        $w="WHERE role='user'";
        if($timKiem) $w.=" AND (fullname LIKE '%".addslashes($timKiem)."%' OR email LIKE '%".addslashes($timKiem)."%')";
        $r=$this->conn->query("SELECT u.*,(SELECT COUNT(*) FROM user_progress WHERE user_id=u.id AND completed=1) bai_xong FROM users u $w ORDER BY u.id DESC");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
    public function kiemTraAI(int $id): int {
        $today=date('Y-m-d');
        $row=$this->conn->query("SELECT ai_messages_today,ai_last_reset FROM users WHERE id=$id")->fetch_assoc();
        if($row['ai_last_reset']!==$today){$this->conn->query("UPDATE users SET ai_messages_today=0,ai_last_reset='$today' WHERE id=$id");return 0;}
        return (int)$row['ai_messages_today'];
    }
    public function tangAI(int $id): void {
        $today=date('Y-m-d');
        $this->conn->query("UPDATE users SET ai_messages_today=ai_messages_today+1,ai_last_reset='$today' WHERE id=$id");
    }
    public function thongKe(): array {
        return ['tong'=>(int)$this->conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'],
                'co_ban'=>(int)$this->conn->query("SELECT COUNT(*) c FROM users WHERE membership='basic'")->fetch_assoc()['c'],
                'nang_cao'=>(int)$this->conn->query("SELECT COUNT(*) c FROM users WHERE membership='advanced'")->fetch_assoc()['c'],
                'cap_cao'=>(int)$this->conn->query("SELECT COUNT(*) c FROM users WHERE membership='premium'")->fetch_assoc()['c']];
    }
}
