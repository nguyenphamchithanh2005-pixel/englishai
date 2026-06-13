<?php
/** Model TroChoi – mini game và duel */
class TroChoi {
    private $conn;
    public function __construct($conn) { $this->conn=$conn; }

    public function luuDiem(int $uid,string $loai,int $diem,array $ct=[]): void {
        $j=json_encode($ct);
        $s=$this->conn->prepare("INSERT INTO game_scores (user_id,game_type,score,details) VALUES (?,?,?,?)");
        $s->bind_param('isis',$uid,$loai,$diem,$j); $s->execute();
    }
    public function diemCao(int $uid,string $loai): ?array {
        $r=$this->conn->query("SELECT score,details,played_at FROM game_scores WHERE user_id=$uid AND game_type='$loai' ORDER BY score DESC LIMIT 1");
        return $r?$r->fetch_assoc():null;
    }
    public function bangXepHang(string $loai,int $n=3): array {
        $r=$this->conn->query("SELECT u.fullname,MAX(gs.score) diem FROM game_scores gs JOIN users u ON gs.user_id=u.id WHERE gs.game_type='$loai' GROUP BY gs.user_id ORDER BY diem DESC LIMIT $n");
        $rows=[]; while($row=$r->fetch_assoc())$rows[]=$row; return $rows;
    }
    public function taoPhong(int $uid): int {
        $this->conn->query("INSERT INTO duels (challenger,status) VALUES ($uid,'waiting')");
        return $this->conn->insert_id;
    }
    public function thamGia(int $phong,int $uid): bool {
        $d=$this->conn->query("SELECT * FROM duels WHERE id=$phong AND status='waiting'")->fetch_assoc();
        if(!$d||$d['challenger']==$uid)return false;
        $this->conn->query("UPDATE duels SET opponent=$uid,status='active' WHERE id=$phong"); return true;
    }
    public function layPhong(int $id): ?array {
        $r=$this->conn->query("SELECT * FROM duels WHERE id=$id"); return $r?$r->fetch_assoc():null;
    }
    public function thangCua(int $uid): int {
        return (int)$this->conn->query("SELECT COUNT(*) c FROM duels WHERE winner_id=$uid AND status='finished'")->fetch_assoc()['c'];
    }
    public function capNhatDiemDuel(int $phongId, int $uid, int $diem): void {
        $phong = $this->conn->query("SELECT * FROM duels WHERE id=$phongId")->fetch_assoc();
        if (!$phong) return;
        if ($phong['challenger'] == $uid) {
            $this->conn->query("UPDATE duels SET challenger_score=$diem WHERE id=$phongId");
        } else {
            $this->conn->query("UPDATE duels SET opponent_score=$diem WHERE id=$phongId");
        }
    }
    public function ketThucDuel(int $phongId, int $uid, int $diem): void {
        $phong = $this->conn->query("SELECT * FROM duels WHERE id=$phongId")->fetch_assoc();
        if (!$phong || $phong['status'] === 'finished') return;
        $this->capNhatDiemDuel($phongId, $uid, $diem);
        $phong = $this->conn->query("SELECT * FROM duels WHERE id=$phongId")->fetch_assoc();
        $cScore = (int)$phong['challenger_score'];
        $oScore = (int)$phong['opponent_score'];
        if ($cScore > 0 && $oScore > 0) {
            $winnerId = $cScore >= $oScore ? $phong['challenger'] : $phong['opponent'];
            $this->conn->query("UPDATE duels SET status='finished',winner_id=$winnerId,finished_at=NOW() WHERE id=$phongId");
        }
    }
    public function layTrangThaiDuel(int $phongId): ?array {
        $r = $this->conn->query("SELECT d.*,u1.fullname c_name,u2.fullname o_name FROM duels d LEFT JOIN users u1 ON d.challenger=u1.id LEFT JOIN users u2 ON d.opponent=u2.id WHERE d.id=$phongId");
        return $r ? $r->fetch_assoc() : null;
    }
}
