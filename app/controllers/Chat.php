<?php
require_once ROOT.'/config/config.php';
require_once ROOT.'/app/models/NguoiDung.php';
class Chat {
    public function index(): void {
        global $conn; requireLogin(); $uid=(int)$_SESSION['user_id'];
        $aiUsed=(new NguoiDung($conn))->kiemTraAI($uid); $aiLimit=getAILimit();
        if(empty($_SESSION['ai_session']))$_SESSION['ai_session']=bin2hex(random_bytes(16));
        $sid=$_SESSION['ai_session'];
        $r=$conn->query("SELECT role,message,created_at FROM ai_conversations WHERE user_id=$uid AND session_id='$sid' ORDER BY id ASC LIMIT 40");
        $lichSu=[]; while($row=$r->fetch_assoc())$lichSu[]=$row;
        render('chat_ai',['pageTitle'=>'Chat AI – '.SITE_NAME,'aiUsed'=>$aiUsed,'aiLimit'=>$aiLimit,'canChat'=>($aiLimit===AI_LIMIT_PREMIUM)||($aiUsed<$aiLimit),'sessionId'=>$sid,'lichSu'=>$lichSu,'context'=>sanitize($_GET['context']??''),'cauHoiNhanh'=>sanitize($_GET['q']??'')]);
    }
    public function apiGroq(): void {
        require_once ROOT.'/config/config.php';
        header('Content-Type: application/json'); global $conn;
        if(!isLoggedIn()){echo json_encode(['error'=>'Chưa đăng nhập']);exit();}
        $uid=(int)$_SESSION['user_id']; $model=new NguoiDung($conn);
        $used=$model->kiemTraAI($uid); $limit=getAILimit();
        if($limit!==AI_LIMIT_PREMIUM&&$used>=$limit){echo json_encode(['error'=>"Đã dùng hết $limit tin.",'upgrade'=>true]);exit();}
        $body=json_decode(file_get_contents('php://input'),true);
        $msgs=$body['messages']??[]; $context=$body['context']??''; $sid=preg_replace('/[^a-f0-9]/','',($body['session_id']??''));
        $sys="Bạn là giáo viên tiếng Anh AI thông minh, hỗ trợ học viên Việt Nam.
- Giải thích bằng tiếng Việt, ví dụ bằng tiếng Anh
- Dùng **in đậm** cho từ quan trọng
- Trả lời súc tích, thân thiện".($context?"
Ngữ cảnh: $context":'');
        $gMsgs=array_map(fn($m)=>['role'=>$m['role'],'content'=>$m['content']],array_slice($msgs,-12));
        $payload=['model'=>GROQ_MODEL,'messages'=>array_merge([['role'=>'system','content'=>$sys]],$gMsgs),'max_tokens'=>1024,'temperature'=>0.7];
        $ch=curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.GROQ_API_KEY],CURLOPT_TIMEOUT=>30]);
        $resp=curl_exec($ch); $err=curl_error($ch); curl_close($ch);
        if($err){echo json_encode(['error'=>'Lỗi kết nối: '.$err]);exit();}
        $data=json_decode($resp,true); $aiMsg=$data['choices'][0]['message']['content']??null;
        if(!$aiMsg){echo json_encode(['error'=>$data['error']['message']??'Lỗi']);exit();}
        if($sid){$lu='';for($i=count($msgs)-1;$i>=0;$i--){if($msgs[$i]['role']==='user'){$lu=$msgs[$i]['content'];break;}}
            if($lu){$s=$conn->prepare("INSERT INTO ai_conversations(user_id,session_id,role,message)VALUES(?,?,'user',?)");$s->bind_param('iss',$uid,$sid,$lu);$s->execute();}
            $s=$conn->prepare("INSERT INTO ai_conversations(user_id,session_id,role,message)VALUES(?,?,'assistant',?)");$s->bind_param('iss',$uid,$sid,$aiMsg);$s->execute();}
        $model->tangAI($uid);
        echo json_encode(['ok'=>true,'message'=>$aiMsg,'used'=>$used+1,'limit'=>$limit]);
    }
    public function phienMoi(): void {
        require_once ROOT.'/config/config.php';
        if(isLoggedIn())$_SESSION['ai_session']=bin2hex(random_bytes(16));
        echo json_encode(['ok'=>true]);
    }
}
