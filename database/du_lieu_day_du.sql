-- ================================================
-- EnglishAI - Cơ sở dữ liệu học tiếng Anh tích hợp AI
-- ================================================

CREATE DATABASE IF NOT EXISTS english_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE english_learning;

-- Bảng người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    membership ENUM('basic','advanced','premium') DEFAULT 'basic',
    avatar VARCHAR(255) DEFAULT NULL,
    ai_messages_today INT DEFAULT 0,
    ai_last_reset DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng danh mục bài học
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-book',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng bài học
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    level ENUM('basic','advanced','premium') DEFAULT 'basic',
    lesson_type ENUM('reading','listening','grammar','writing','speaking') DEFAULT 'reading',
    duration INT DEFAULT 15,
    thumbnail VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    order_num INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng câu hỏi / bài tập
CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_answer CHAR(1) NOT NULL,
    explanation TEXT,
    order_num INT DEFAULT 0,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Bảng tiến độ học của người dùng
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    score INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    UNIQUE KEY unique_progress (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Bảng từ vựng
CREATE TABLE vocabulary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(100) NOT NULL,
    pronunciation VARCHAR(100),
    definition TEXT NOT NULL,
    example TEXT,
    translation VARCHAR(255),
    level ENUM('basic','advanced','premium') DEFAULT 'basic',
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng từ vựng người dùng đã học
CREATE TABLE user_vocabulary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vocab_id INT NOT NULL,
    status ENUM('new','learning','learned') DEFAULT 'new',
    review_count INT DEFAULT 0,
    last_reviewed TIMESTAMP NULL,
    UNIQUE KEY unique_vocab (user_id, vocab_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vocab_id) REFERENCES vocabulary(id) ON DELETE CASCADE
);

-- Bảng lịch sử chat AI
CREATE TABLE ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(64) NOT NULL,
    role ENUM('user','assistant') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng thông báo
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','success','warning') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ================================================
-- Dữ liệu mẫu
-- ================================================

-- Admin mặc định (pass: Admin@123)
INSERT INTO users (fullname, email, password, role, membership) VALUES
('Administrator', 'admin@englishai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'premium'),
('Nguyễn Văn A', 'user@englishai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'basic');

-- Danh mục
INSERT INTO categories (name, slug, icon, description) VALUES
('Ngữ pháp', 'ngu-phap', 'bi-diagram-3', 'Học ngữ pháp tiếng Anh từ cơ bản đến nâng cao'),
('Từ vựng', 'tu-vung', 'bi-translate', 'Mở rộng vốn từ vựng phong phú'),
('Luyện nghe', 'luyen-nghe', 'bi-headphones', 'Cải thiện kỹ năng nghe'),
('Kỹ năng đọc', 'ky-nang-doc', 'bi-file-text', 'Đọc hiểu văn bản tiếng Anh'),
('Giao tiếp', 'giao-tiep', 'bi-chat-dots', 'Luyện tập hội thoại thực tế');

-- Bài học mẫu
INSERT INTO lessons (category_id, title, slug, content, level, lesson_type, duration) VALUES
(1, 'Thì Hiện tại Đơn (Present Simple)', 'thi-hien-tai-don', 
'<h4>Thì Hiện tại Đơn</h4>
<p>Thì hiện tại đơn diễn tả hành động xảy ra thường xuyên, thói quen hoặc sự thật hiển nhiên.</p>
<h5>Cấu trúc:</h5>
<ul>
<li><strong>Khẳng định:</strong> S + V(s/es) + O</li>
<li><strong>Phủ định:</strong> S + do/does + not + V + O</li>
<li><strong>Nghi vấn:</strong> Do/Does + S + V + O?</li>
</ul>
<h5>Ví dụ:</h5>
<ul>
<li>She <strong>works</strong> at a hospital.</li>
<li>They <strong>do not</strong> speak French.</li>
<li><strong>Does</strong> he like coffee?</li>
</ul>
<h5>Dấu hiệu nhận biết:</h5>
<p>always, usually, often, sometimes, rarely, never, every day/week/month</p>',
'basic', 'grammar', 20),

(1, 'Thì Quá khứ Đơn (Past Simple)', 'thi-qua-khu-don',
'<h4>Thì Quá khứ Đơn</h4>
<p>Diễn tả hành động đã xảy ra và kết thúc trong quá khứ.</p>
<h5>Cấu trúc:</h5>
<ul>
<li><strong>Khẳng định:</strong> S + V2/ed + O</li>
<li><strong>Phủ định:</strong> S + did not + V + O</li>
<li><strong>Nghi vấn:</strong> Did + S + V + O?</li>
</ul>
<h5>Ví dụ:</h5>
<ul>
<li>She <strong>visited</strong> Paris last year.</li>
<li>He <strong>did not go</strong> to school yesterday.</li>
</ul>',
'basic', 'grammar', 25),

(1, 'Câu điều kiện (Conditionals)', 'cau-dieu-kien',
'<h4>Câu điều kiện trong Tiếng Anh</h4>
<h5>Loại 0 – Sự thật hiển nhiên:</h5>
<p>If + Present Simple, Present Simple</p>
<p><em>If you heat water to 100°C, it boils.</em></p>
<h5>Loại 1 – Có thể xảy ra:</h5>
<p>If + Present Simple, will + V</p>
<p><em>If it rains, I will stay home.</em></p>
<h5>Loại 2 – Không có thật ở hiện tại:</h5>
<p>If + Past Simple, would + V</p>
<p><em>If I were rich, I would travel the world.</em></p>
<h5>Loại 3 – Không có thật trong quá khứ:</h5>
<p>If + Past Perfect, would have + V3</p>
<p><em>If I had studied harder, I would have passed the exam.</em></p>',
'advanced', 'grammar', 35),

(5, 'Giao tiếp cơ bản: Giới thiệu bản thân', 'gioi-thieu-ban-than',
'<h4>Giới thiệu bản thân trong tiếng Anh</h4>
<h5>Mẫu câu cơ bản:</h5>
<ul>
<li>Hi, my name is... / I am...</li>
<li>I am from Vietnam.</li>
<li>I am ... years old.</li>
<li>I work as a... / I am a student.</li>
<li>Nice to meet you!</li>
</ul>
<h5>Hội thoại mẫu:</h5>
<p><strong>A:</strong> Hello! My name is Sarah. What is your name?</p>
<p><strong>B:</strong> Hi Sarah! I am Minh. Nice to meet you.</p>
<p><strong>A:</strong> Nice to meet you too! Where are you from?</p>
<p><strong>B:</strong> I am from Vietnam. And you?</p>
<p><strong>A:</strong> I am from Australia.</p>',
'basic', 'speaking', 15),

(2, 'Từ vựng IELTS Band 7+', 'tu-vung-ielts-band-7',
'<h4>Từ vựng học thuật – IELTS Band 7+</h4>
<p>Danh sách từ vựng quan trọng cho IELTS Academic:</p>
<h5>Chủ đề Môi trường:</h5>
<ul>
<li><strong>Sustainable</strong> (adj): bền vững</li>
<li><strong>Renewable energy</strong>: năng lượng tái tạo</li>
<li><strong>Carbon footprint</strong>: dấu chân carbon</li>
<li><strong>Biodiversity</strong>: đa dạng sinh học</li>
</ul>
<h5>Chủ đề Xã hội:</h5>
<ul>
<li><strong>Demographic</strong> (n): nhân khẩu học</li>
<li><strong>Socioeconomic</strong> (adj): kinh tế xã hội</li>
<li><strong>Urbanization</strong> (n): đô thị hóa</li>
</ul>',
'premium', 'reading', 40);

-- Bài tập mẫu
INSERT INTO exercises (lesson_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation) VALUES
(1, 'She ___ to school every day.', 'go', 'goes', 'going', 'went', 'B', 'Chủ ngữ là "She" (ngôi 3 số ít) nên động từ phải thêm "s" → goes'),
(1, 'They ___ not like spicy food.', 'does', 'do', 'is', 'are', 'B', 'Với "They" ta dùng "do not"'),
(1, '___ he play football on weekends?', 'Do', 'Does', 'Is', 'Has', 'B', 'Chủ ngữ "he" dùng "Does" để đặt câu hỏi'),
(2, 'She ___ to Paris last summer.', 'go', 'goes', 'went', 'going', 'C', '"Last summer" là dấu hiệu của thì quá khứ đơn, dùng V2 = went'),
(2, 'They ___ not finish the project yesterday.', 'do', 'does', 'did', 'was', 'C', 'Câu phủ định quá khứ đơn dùng "did not"'),
(4, 'How do you respond when someone says "Nice to meet you"?', 'Thank you', 'Nice to meet you too', 'I am fine', 'See you later', 'B', 'Câu trả lời lịch sự là "Nice to meet you too!"');

-- Từ vựng mẫu
INSERT INTO vocabulary (word, pronunciation, definition, example, translation, level, category) VALUES
('Hello', '/həˈloʊ/', 'A greeting used when meeting someone', 'Hello! How are you?', 'Xin chào', 'basic', 'Greetings'),
('Thank you', '/θæŋk juː/', 'Expression of gratitude', 'Thank you for your help.', 'Cảm ơn', 'basic', 'Greetings'),
('Apologize', '/əˈpɒlədʒaɪz/', 'To say sorry for something', 'I apologize for being late.', 'Xin lỗi', 'basic', 'Greetings'),
('Magnificent', '/mæɡˈnɪfɪsənt/', 'Impressively beautiful or elaborate', 'The view from the mountain was magnificent.', 'Hùng vĩ, tráng lệ', 'advanced', 'Description'),
('Perseverance', '/ˌpɜːsɪˈvɪərəns/', 'Continued effort despite difficulty', 'Success requires perseverance.', 'Sự kiên trì', 'advanced', 'Character'),
('Ambiguous', '/æmˈbɪɡjuəs/', 'Having more than one possible meaning', 'His answer was ambiguous.', 'Mơ hồ, lập lờ', 'advanced', 'Academic'),
('Paradigm', '/ˈpærədaɪm/', 'A typical example or pattern of something', 'This represents a paradigm shift.', 'Mô hình, hệ tư tưởng', 'premium', 'Academic'),
('Ubiquitous', '/juːˈbɪkwɪtəs/', 'Present everywhere', 'Smartphones are now ubiquitous.', 'Có mặt khắp nơi', 'premium', 'Academic'),
('Ephemeral', '/ɪˈfemərəl/', 'Lasting for a very short time', 'Fame can be ephemeral.', 'Phù du, thoáng qua', 'premium', 'Academic');

-- Thông báo chào mừng
INSERT INTO notifications (user_id, title, message, type) VALUES
(2, 'Chào mừng đến với EnglishAI! 🎉', 'Tài khoản của bạn đã được tạo thành công. Bắt đầu hành trình học tiếng Anh ngay hôm nay!', 'success');

-- ============================================================
-- PHẦN MỞ RỘNG: 100+ bài học + 500+ từ vựng
-- Import file này SAU database.sql GỐC
-- Hoặc dùng database_full.sql (đã gộp sẵn) để import 1 lần
-- ============================================================

-- Tắt kiểm tra khoá ngoại tạm thời để insert an toàn
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- BÀI HỌC MỞ RỘNG (100+ bài)
-- category_id: 1=Ngữ pháp, 2=Từ vựng/IELTS, 3=Luyện nghe, 4=Kỹ năng đọc, 5=Giao tiếp
-- ============================================================

INSERT IGNORE INTO lessons (category_id, title, slug, content, level, lesson_type, duration, is_active, order_num) VALUES

-- ========== CƠ BẢN – NGỮ PHÁP ==========
(1,'Đại từ nhân xưng (Personal Pronouns)','dai-tu-nhan-xung',
'<h4>Đại từ nhân xưng trong Tiếng Anh</h4>
<p>Đại từ nhân xưng thay thế cho danh từ chỉ người hoặc vật.</p>
<h5>Bảng đại từ:</h5>
<table class="table table-bordered"><thead><tr><th>Ngôi</th><th>Chủ ngữ</th><th>Tân ngữ</th><th>Sở hữu</th></tr></thead><tbody>
<tr><td>Ngôi 1 số ít</td><td>I</td><td>me</td><td>my/mine</td></tr>
<tr><td>Ngôi 2</td><td>you</td><td>you</td><td>your/yours</td></tr>
<tr><td>Ngôi 3 số ít</td><td>he/she/it</td><td>him/her/it</td><td>his/her/its</td></tr>
<tr><td>Ngôi 1 số nhiều</td><td>we</td><td>us</td><td>our/ours</td></tr>
<tr><td>Ngôi 3 số nhiều</td><td>they</td><td>them</td><td>their/theirs</td></tr>
</tbody></table>
<h5>Ví dụ:</h5>
<ul><li><strong>She</strong> is my friend. I love <strong>her</strong>.</li>
<li><strong>They</strong> gave <strong>us</strong> a present.</li>
<li>This book is <strong>mine</strong>.</li></ul>',
'basic','grammar',20,1,10),

(1,'Mạo từ A, An, The','mao-tu-a-an-the',
'<h4>Mạo từ trong Tiếng Anh</h4>
<h5>Mạo từ không xác định: A / AN</h5>
<p>Dùng <strong>A</strong> trước từ bắt đầu bằng phụ âm: <em>a book, a cat, a university</em></p>
<p>Dùng <strong>AN</strong> trước từ bắt đầu bằng nguyên âm (âm): <em>an apple, an hour, an umbrella</em></p>
<h5>Mạo từ xác định: THE</h5>
<p>Dùng khi vật/người đã được đề cập trước hoặc chỉ có một trên thế giới:</p>
<ul><li><em>The sun rises in the east.</em></li>
<li><em>I saw a dog. <strong>The</strong> dog was very big.</em></li>
<li><em>She plays <strong>the</strong> piano beautifully.</em></li></ul>
<h5>Không dùng mạo từ (Zero article):</h5>
<ul><li>Trước danh từ số nhiều chung chung: <em>Cats are cute.</em></li>
<li>Trước tên riêng: <em>Vietnam, London, Mary</em></li>
<li>Trước bữa ăn, môn thể thao: <em>I play football. We had lunch.</em></li></ul>',
'basic','grammar',25,1,11),

(1,'Thì Hiện tại Tiếp diễn (Present Continuous)','thi-hien-tai-tiep-dien',
'<h4>Thì Hiện tại Tiếp diễn</h4>
<p>Diễn tả hành động đang xảy ra tại thời điểm nói.</p>
<h5>Cấu trúc:</h5>
<ul><li><strong>Khẳng định:</strong> S + am/is/are + V-ing</li>
<li><strong>Phủ định:</strong> S + am/is/are + not + V-ing</li>
<li><strong>Nghi vấn:</strong> Am/Is/Are + S + V-ing?</li></ul>
<h5>Quy tắc thêm -ing:</h5>
<ul><li>Thêm trực tiếp: <em>work → working</em></li>
<li>Động từ kết thúc -e: bỏ e rồi thêm -ing: <em>make → making</em></li>
<li>Động từ ngắn (phụ âm+nguyên âm+phụ âm): gấp đôi phụ âm: <em>run → running</em></li></ul>
<h5>Dấu hiệu nhận biết:</h5>
<p>now, right now, at the moment, at present, look!, listen!</p>
<h5>Ví dụ:</h5>
<ul><li>She <strong>is studying</strong> English now.</li>
<li>They <strong>are not playing</strong> football at the moment.</li>
<li><strong>Is</strong> he <strong>sleeping</strong>? – Yes, he is.</li></ul>',
'basic','grammar',25,1,12),

(1,'Danh từ số ít và số nhiều','danh-tu-so-it-so-nhieu',
'<h4>Danh từ số nhiều trong Tiếng Anh</h4>
<h5>Quy tắc thêm -S:</h5><p>Hầu hết danh từ: <em>book → books, cat → cats</em></p>
<h5>Thêm -ES:</h5><p>Kết thúc bằng -s, -ss, -sh, -ch, -x, -z: <em>bus → buses, match → matches</em></p>
<h5>Đổi -Y thành -IES:</h5><p>Trước -y là phụ âm: <em>city → cities, baby → babies</em></p>
<h5>Số nhiều bất quy tắc:</h5>
<table class="table table-sm table-bordered"><thead><tr><th>Số ít</th><th>Số nhiều</th><th>Số ít</th><th>Số nhiều</th></tr></thead><tbody>
<tr><td>man</td><td>men</td><td>woman</td><td>women</td></tr>
<tr><td>child</td><td>children</td><td>tooth</td><td>teeth</td></tr>
<tr><td>foot</td><td>feet</td><td>mouse</td><td>mice</td></tr>
<tr><td>sheep</td><td>sheep</td><td>fish</td><td>fish</td></tr></tbody></table>',
'basic','grammar',20,1,13),

(1,'Tính từ và Trạng từ (Adjectives & Adverbs)','tinh-tu-trang-tu',
'<h4>Tính từ và Trạng từ</h4>
<h5>Tính từ (Adjectives):</h5>
<p>Mô tả danh từ, đứng trước danh từ hoặc sau động từ liên kết:</p>
<ul><li><em>She has <strong>beautiful</strong> eyes.</em></li><li><em>The weather is <strong>cold</strong> today.</em></li></ul>
<h5>Trạng từ (Adverbs):</h5>
<p>Thường thêm <strong>-ly</strong> vào tính từ: quick→<strong>quickly</strong>, careful→<strong>carefully</strong></p>
<p>Ngoại lệ: good→<strong>well</strong>, fast→<strong>fast</strong>, hard→<strong>hard</strong></p>
<h5>Vị trí trạng từ tần suất:</h5>
<p>S + ADV + V: <em>She <strong>always</strong> smiles. He <strong>often</strong> studies late.</em></p>
<h5>So sánh tính từ:</h5>
<ul><li>Short: tall → taller → tallest</li>
<li>Long: beautiful → more beautiful → most beautiful</li>
<li>Bất quy tắc: good→better→best, bad→worse→worst</li></ul>',
'basic','grammar',20,1,14),

(1,'Câu hỏi Yes/No và Wh-questions','cau-hoi-yes-no-wh',
'<h4>Các loại câu hỏi trong Tiếng Anh</h4>
<h5>Câu hỏi Yes/No:</h5>
<ul><li><em><strong>Do</strong> you like coffee? – Yes, I do. / No, I do not.</em></li>
<li><em><strong>Is</strong> she a teacher? – Yes, she is.</em></li>
<li><em><strong>Did</strong> they go yesterday? – Yes, they did.</em></li></ul>
<h5>Câu hỏi Wh-:</h5>
<table class="table table-sm table-bordered"><thead><tr><th>Từ hỏi</th><th>Hỏi về</th><th>Ví dụ</th></tr></thead><tbody>
<tr><td>What</td><td>Vật/Việc gì</td><td>What do you do?</td></tr>
<tr><td>Who/Whom</td><td>Ai</td><td>Who called you?</td></tr>
<tr><td>Where</td><td>Ở đâu</td><td>Where do you live?</td></tr>
<tr><td>When</td><td>Khi nào</td><td>When is your birthday?</td></tr>
<tr><td>Why</td><td>Tại sao</td><td>Why are you late?</td></tr>
<tr><td>How</td><td>Như thế nào</td><td>How are you?</td></tr>
<tr><td>How many/much</td><td>Bao nhiêu</td><td>How many students are there?</td></tr></tbody></table>',
'basic','grammar',20,1,15),

(1,'Động từ Khuyết thiếu (Modal Verbs)','dong-tu-khuyet-thieu',
'<h4>Động từ khuyết thiếu (Modals)</h4>
<p>Modals đứng trước động từ nguyên mẫu, không chia theo chủ ngữ.</p>
<h5>Can / Could – Khả năng:</h5>
<ul><li><em>She <strong>can</strong> swim very fast.</em></li><li><em><strong>Could</strong> you help me, please?</em> (lịch sự)</li></ul>
<h5>May / Might – Khả năng có thể xảy ra:</h5>
<ul><li><em>It <strong>may</strong> rain tomorrow.</em> (50%)</li><li><em>He <strong>might</strong> be late.</em> (ít hơn 50%)</li></ul>
<h5>Must / Have to – Bắt buộc:</h5>
<ul><li><em>You <strong>must</strong> wear a seatbelt.</em> (quy định)</li>
<li><em>I <strong>have to</strong> finish this today.</em> (hoàn cảnh bắt buộc)</li></ul>
<h5>Should – Lời khuyên:</h5>
<ul><li><em>You <strong>should</strong> study harder.</em></li></ul>
<h5>Mustn''t vs Don''t have to:</h5>
<ul><li><strong>mustn''t</strong>: cấm tuyệt đối – <em>You mustn''t smoke here.</em></li>
<li><strong>don''t have to</strong>: không cần thiết – <em>You don''t have to come early.</em></li></ul>',
'basic','grammar',30,1,16),

(1,'Giới từ (Prepositions)','gioi-tu',
'<h4>Giới từ trong Tiếng Anh</h4>
<h5>Giới từ chỉ thời gian:</h5>
<ul><li><strong>AT:</strong> giờ cụ thể, ngày lễ: <em>at 7am, at Christmas, at noon</em></li>
<li><strong>ON:</strong> ngày cụ thể: <em>on Monday, on March 8th, on my birthday</em></li>
<li><strong>IN:</strong> tháng, năm, buổi: <em>in July, in 2024, in the morning</em></li></ul>
<h5>Giới từ chỉ nơi chốn:</h5>
<ul><li><strong>AT:</strong> điểm cụ thể: <em>at the bus stop, at school, at home</em></li>
<li><strong>ON:</strong> bề mặt: <em>on the table, on the wall, on the floor</em></li>
<li><strong>IN:</strong> bên trong: <em>in the box, in Vietnam, in the room</em></li>
<li><strong>NEXT TO / BESIDE:</strong> bên cạnh</li>
<li><strong>BETWEEN:</strong> ở giữa (2 vật)</li>
<li><strong>AMONG:</strong> ở giữa (nhiều vật)</li></ul>
<h5>Cụm giới từ hay gặp:</h5>
<ul><li>interested <strong>in</strong>, good <strong>at</strong>, afraid <strong>of</strong>, tired <strong>of</strong></li>
<li>responsible <strong>for</strong>, different <strong>from</strong>, married <strong>to</strong></li></ul>',
'basic','grammar',25,1,17),

(1,'Thì Tương lai (Future Tenses)','thi-tuong-lai',
'<h4>Các cách diễn đạt Tương lai</h4>
<h5>Will + V: Quyết định tức thì / Dự đoán:</h5>
<ul><li><em>I <strong>will</strong> help you. (quyết định ngay lúc nói)</em></li>
<li><em>It <strong>will</strong> rain tomorrow. (dự đoán không có bằng chứng)</em></li></ul>
<h5>Be going to + V: Kế hoạch / Bằng chứng hiện tại:</h5>
<ul><li><em>I <strong>am going to</strong> study medicine. (đã có kế hoạch)</em></li>
<li><em>Look at those clouds – it <strong>is going to</strong> rain! (có dấu hiệu)</em></li></ul>
<h5>Present Continuous: Sắp xếp cụ thể:</h5>
<ul><li><em>We <strong>are meeting</strong> the client tomorrow at 2pm.</em></li></ul>
<h5>Will vs Going to:</h5>
<table class="table table-sm table-bordered"><tbody>
<tr><td><strong>Will</strong></td><td>Spontaneous decision, offer, promise, prediction</td></tr>
<tr><td><strong>Going to</strong></td><td>Prior plan, intention, evidence-based prediction</td></tr></tbody></table>',
'basic','grammar',25,1,18),

-- ========== CƠ BẢN – ĐỌC HIỂU ==========
(4,'Đọc hiểu: Gia đình tôi','doc-hieu-gia-dinh-toi',
'<h4>My Family – Gia đình tôi</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>My name is Minh. I am 20 years old. I live in Ho Chi Minh City with my family. My family has five members: my father, my mother, my grandmother, my sister, and me.</p>
<p>My father is a doctor. He works at a big hospital. My mother is a teacher. She teaches mathematics at a primary school. My grandmother is 70 years old. She stays at home and cooks delicious food for us. My sister is 15 years old. She is a high school student. She likes reading books and listening to music.</p>
</div>
<h5>Từ vựng quan trọng:</h5>
<ul><li><strong>member</strong> (n): thành viên</li><li><strong>hospital</strong> (n): bệnh viện</li>
<li><strong>primary school</strong> (n): trường tiểu học</li><li><strong>delicious</strong> (adj): ngon</li></ul>',
'basic','reading',20,1,20),

(4,'Đọc hiểu: Một ngày của tôi','doc-hieu-mot-ngay-cua-toi',
'<h4>My Daily Routine – Một ngày của tôi</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>I wake up at 6 o''clock every morning. I brush my teeth and wash my face. Then I have breakfast with my family. I go to school by bicycle at 7 o''clock. Classes start at 7:30. I study many subjects: English, Math, Science, and History.</p>
<p>After school, I come home at 5 pm. I do my homework and then watch TV. I have dinner with my family at 6:30. At night, I read books or chat with friends online. I go to bed at 10:30 pm.</p>
</div>
<h5>Từ vựng:</h5>
<ul><li><strong>wake up</strong>: thức dậy</li><li><strong>brush teeth</strong>: đánh răng</li>
<li><strong>by bicycle</strong>: bằng xe đạp</li><li><strong>homework</strong>: bài tập về nhà</li></ul>',
'basic','reading',20,1,21),

(4,'Đọc hiểu: Thời tiết và Mùa','doc-hieu-thoi-tiet-mua',
'<h4>Weather and Seasons – Thời tiết và Mùa</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>Vietnam has two main seasons: the dry season and the rainy season. In the north, there are four seasons: spring, summer, autumn, and winter. Spring is from February to April – the weather is warm and flowers bloom beautifully. Summer is hot and sunny. Autumn has cool and comfortable weather. Winter is cold, especially in the mountains.</p>
<p>People check the weather forecast every day. When it rains, they use umbrellas. The weather affects our daily activities and mood.</p>
</div>
<h5>Từ vựng:</h5>
<ul><li><strong>dry/rainy season</strong>: mùa khô/mưa</li><li><strong>bloom</strong> (v): nở hoa</li>
<li><strong>forecast</strong> (n): dự báo</li><li><strong>affect</strong> (v): ảnh hưởng</li></ul>',
'basic','reading',20,1,22),

(4,'Viết: Email xin lỗi','viet-email-xin-loi',
'<h4>Writing an Apology Email</h4>
<h5>Cấu trúc email:</h5>
<ul><li><strong>Subject:</strong> Tiêu đề rõ ràng</li><li><strong>Opening:</strong> Dear Mr./Ms. [Name],</li>
<li><strong>Body:</strong> Xin lỗi + lý do + đề xuất giải pháp</li><li><strong>Closing:</strong> Best regards / Sincerely,</li></ul>
<h5>Mẫu email:</h5>
<div class="p-3 bg-light rounded border">
<p><strong>Subject:</strong> Apology for Missing the Meeting</p>
<p>Dear Mr. Johnson,<br>I am writing to sincerely apologize for missing our meeting yesterday. I had a family emergency that required my immediate attention. I would like to reschedule at your earliest convenience.</p>
<p>Best regards,<br>Minh Nguyen</p></div>
<h5>Từ vựng:</h5>
<ul><li><strong>sincerely apologize</strong>: thành thật xin lỗi</li>
<li><strong>inconvenience</strong>: sự bất tiện</li><li><strong>reschedule</strong>: sắp xếp lại</li></ul>',
'basic','writing',25,1,23),

(4,'Viết: Mô tả bản thân','viet-mo-ta-ban-than',
'<h4>Writing About Yourself</h4>
<h5>Cấu trúc đoạn giới thiệu:</h5>
<ol><li>Tên và tuổi</li><li>Quê quán / nơi sống</li><li>Nghề nghiệp / học vấn</li>
<li>Sở thích</li><li>Tính cách</li><li>Kế hoạch/Ước mơ</li></ol>
<h5>Ví dụ mẫu:</h5>
<div class="p-3 bg-light rounded border">
<p>My name is Linh Tran. I am 22 years old and currently living in Ho Chi Minh City. I am a final-year student at the University of Economics, majoring in International Business. In my free time, I enjoy reading, cooking, and exploring new cafes. I would describe myself as hardworking, creative, and optimistic. After graduation, I hope to work for a multinational company.</p></div>
<h5>Từ vựng tính cách:</h5>
<ul><li>hardworking, dedicated, diligent – chăm chỉ</li>
<li>creative, innovative – sáng tạo</li><li>responsible, reliable – có trách nhiệm</li></ul>',
'basic','writing',25,1,24),

-- ========== CƠ BẢN – GIAO TIẾP ==========
(5,'Hội thoại: Hỏi đường','hoi-thoai-hoi-duong',
'<h4>Asking for Directions</h4>
<h5>Mẫu câu hỏi đường:</h5>
<ul><li>Excuse me, where is the nearest...?</li><li>How do I get to...?</li><li>Could you tell me the way to...?</li></ul>
<h5>Mẫu câu chỉ đường:</h5>
<ul><li>Go straight ahead.</li><li>Turn left / right at the traffic light.</li>
<li>It is on your left/right.</li><li>You can''t miss it!</li></ul>
<h5>Hội thoại mẫu:</h5>
<div class="p-3 bg-light rounded border">
<p><strong>A:</strong> Excuse me, how do I get to the post office?</p>
<p><strong>B:</strong> Go straight ahead for two blocks, then turn left at the traffic light. The post office is on your right. You can''t miss it.</p>
<p><strong>A:</strong> Is it far? <strong>B:</strong> No, about 300 metres. 5 minutes on foot.</p>
<p><strong>A:</strong> Thank you! <strong>B:</strong> You''re welcome!</p></div>',
'basic','speaking',20,1,30),

(5,'Hội thoại: Mua sắm','hoi-thoai-mua-sam',
'<h4>Shopping</h4>
<h5>Từ vựng cần biết:</h5>
<ul><li><strong>How much is this?</strong> – Cái này giá bao nhiêu?</li>
<li><strong>Can I try it on?</strong> – Tôi có thể thử không?</li>
<li><strong>Do you have it in size...?</strong> – Có size... không?</li>
<li><strong>I''ll take it.</strong> – Tôi lấy cái này.</li>
<li><strong>Do you accept credit cards?</strong> – Có nhận thẻ không?</li></ul>
<h5>Hội thoại mẫu:</h5>
<div class="p-3 bg-light rounded border">
<p><strong>Customer:</strong> Excuse me, how much is this shirt?</p>
<p><strong>Staff:</strong> It''s 250,000 VND. We have a 20% sale today.</p>
<p><strong>Customer:</strong> Great! Can I try it on?</p>
<p><strong>Staff:</strong> Of course! Fitting rooms are over there.</p>
<p><strong>Customer:</strong> It fits perfectly. I''ll take it. Do you accept cards?</p>
<p><strong>Staff:</strong> Yes, follow me to the cashier.</p></div>',
'basic','speaking',20,1,31),

(5,'Hội thoại: Đặt bàn nhà hàng','hoi-thoai-dat-ban-nha-hang',
'<h4>At the Restaurant</h4>
<h5>Mẫu câu hữu ích:</h5>
<ul><li>A table for two, please.</li><li>Could I see the menu?</li>
<li>What do you recommend?</li><li>I''d like to order...</li>
<li>Could I have the bill, please?</li></ul>
<h5>Hội thoại mẫu:</h5>
<div class="p-3 bg-light rounded border">
<p><strong>Waiter:</strong> Good evening! Do you have a reservation?</p>
<p><strong>Guest:</strong> Yes, for two under the name Linh.</p>
<p><strong>Waiter:</strong> Perfect! Here is your table. Can I get drinks first?</p>
<p><strong>Guest:</strong> Two glasses of water, please. Could we see the menu?</p>
<p><strong>Waiter:</strong> Our special today is grilled salmon.</p>
<p><strong>Guest:</strong> I''ll have the salmon. My friend would like beef steak, medium well.</p></div>',
'basic','speaking',20,1,32),

(5,'Hội thoại: Khám bệnh','hoi-thoai-kham-benh',
'<h4>At the Doctor</h4>
<h5>Từ vựng triệu chứng:</h5>
<ul><li>I have a headache / stomachache / backache / sore throat.</li>
<li>I have a fever / cold / cough.</li>
<li>I feel dizzy / nauseous / tired.</li></ul>
<h5>Hội thoại mẫu:</h5>
<div class="p-3 bg-light rounded border">
<p><strong>Doctor:</strong> What seems to be the problem?</p>
<p><strong>Patient:</strong> I''ve had a terrible headache and fever for two days.</p>
<p><strong>Doctor:</strong> Any other symptoms?</p>
<p><strong>Patient:</strong> Yes, a sore throat and I feel very tired.</p>
<p><strong>Doctor:</strong> You seem to have the flu. Take one tablet three times a day after meals. Drink plenty of water and rest.</p>
<p><strong>Patient:</strong> Should I come back? <strong>Doctor:</strong> Yes, in three days if not better.</p></div>',
'basic','speaking',25,1,33),

(5,'Hội thoại: Phỏng vấn xin việc','phong-van-xin-viec-co-ban',
'<h4>Job Interview Basics</h4>
<h5>Câu hỏi phổ biến:</h5>
<ul><li>Tell me about yourself.</li><li>Why do you want this job?</li>
<li>What are your strengths and weaknesses?</li><li>Where do you see yourself in 5 years?</li></ul>
<h5>Cách trả lời "Tell me about yourself":</h5>
<div class="p-3 bg-light rounded border">
<p>"My name is Lan. I graduated from HCMC University with a degree in Business Administration. I have two years of experience in customer service. I am hardworking and detail-oriented. I am excited about this opportunity to join your team."</p></div>
<h5>Từ vựng:</h5>
<ul><li><strong>strength/weakness</strong>: điểm mạnh/yếu</li>
<li><strong>experience</strong>: kinh nghiệm</li><li><strong>opportunity</strong>: cơ hội</li></ul>',
'basic','speaking',30,1,34),

-- ========== CƠ BẢN – LUYỆN NGHE ==========
(3,'Luyện nghe: Số đếm và Số thứ tự','luyen-nghe-so-dem',
'<h4>Numbers</h4>
<h5>Số đếm:</h5>
<table class="table table-sm table-bordered"><tbody>
<tr><td>1–one</td><td>11–eleven</td><td>21–twenty-one</td><td>100–one hundred</td></tr>
<tr><td>2–two</td><td>12–twelve</td><td>30–thirty</td><td>1,000–one thousand</td></tr>
<tr><td>3–three</td><td>13–thirteen</td><td>40–forty</td><td>1,000,000–one million</td></tr>
<tr><td>5–five</td><td>15–fifteen</td><td>50–fifty</td><td>1,000,000,000–one billion</td></tr></tbody></table>
<h5>Số thứ tự:</h5>
<ul><li>1st–first, 2nd–second, 3rd–third, 4th–fourth, 5th–fifth</li>
<li>11th–eleventh, 12th–twelfth, 20th–twentieth, 21st–twenty-first</li></ul>
<h5>Đọc năm:</h5>
<ul><li>1990 → nineteen ninety</li><li>2000 → two thousand</li><li>2025 → twenty twenty-five</li></ul>',
'basic','listening',20,1,40),

(3,'Luyện nghe: Bảng chữ cái và Chính tả','luyen-nghe-chinh-ta',
'<h4>Alphabet & Spelling</h4>
<div class="p-3 bg-light rounded border mb-3 fw-bold" style="letter-spacing:.15rem">
A B C D E F G H I J K L M N O P Q R S T U V W X Y Z
</div>
<h5>Cách đánh vần tên:</h5>
<div class="p-3 bg-light rounded border mb-3">
<p><strong>A:</strong> How do you spell your name?</p>
<p><strong>B:</strong> Nguyen – N-G-U-Y-E-N. First name: Minh – M-I-N-H.</p></div>
<h5>NATO Phonetic Alphabet:</h5>
<ul><li>A–Alpha, B–Bravo, C–Charlie, D–Delta, E–Echo, F–Foxtrot</li>
<li>G–Golf, H–Hotel, I–India, J–Juliet, K–Kilo, L–Lima</li>
<li>M–Mike, N–November, O–Oscar, P–Papa, Q–Quebec, R–Romeo</li>
<li>S–Sierra, T–Tango, U–Uniform, V–Victor, W–Whiskey, X–X-ray, Y–Yankee, Z–Zulu</li></ul>',
'basic','listening',20,1,41),

(3,'Luyện nghe: Ngày tháng và Lịch','luyen-nghe-ngay-thang',
'<h4>Dates and Calendar</h4>
<h5>Các tháng trong năm:</h5>
<p>January, February, March, April, May, June, July, August, September, October, November, December</p>
<h5>Các ngày trong tuần:</h5>
<p>Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday</p>
<h5>Cách đọc ngày tháng:</h5>
<ul><li>UK: 25th March 2025 → "the twenty-fifth of March, twenty twenty-five"</li>
<li>US: March 25, 2025 → "March twenty-fifth, twenty twenty-five"</li></ul>
<h5>Hỏi và đáp về ngày:</h5>
<ul><li>What day is it today? – It''s Monday.</li>
<li>What''s the date today? – It''s March 25th.</li>
<li>When is your birthday? – My birthday is on July 4th.</li></ul>',
'basic','listening',20,1,42),

-- ========== NÂNG CAO – NGỮ PHÁP ==========
(1,'Thì Hiện tại Hoàn thành (Present Perfect)','thi-hien-tai-hoan-thanh',
'<h4>Thì Hiện tại Hoàn thành</h4>
<h5>Cấu trúc:</h5>
<ul><li><strong>Khẳng định:</strong> S + have/has + V3/ed</li>
<li><strong>Phủ định:</strong> S + have/has + not + V3/ed</li>
<li><strong>Nghi vấn:</strong> Have/Has + S + V3/ed?</li></ul>
<h5>Cách dùng:</h5>
<ol><li>Hành động quá khứ, thời gian không xác định:<br><em>I <strong>have visited</strong> Paris three times.</em></li>
<li>Hành động kéo dài đến hiện tại:<br><em>She <strong>has lived</strong> here since 2019.</em></li>
<li>Hành động vừa xảy ra:<br><em>He <strong>has just left</strong> the office.</em></li>
<li>Kinh nghiệm:<br><em>Have you ever <strong>eaten</strong> sushi?</em></li></ol>
<h5>FOR vs SINCE:</h5>
<ul><li><strong>FOR</strong> + khoảng thời gian: <em>for 3 years, for a week</em></li>
<li><strong>SINCE</strong> + mốc thời gian: <em>since 2020, since last Monday</em></li></ul>
<h5>Dấu hiệu:</h5>
<p>already, yet, just, ever, never, recently, lately, so far, for, since</p>',
'advanced','grammar',35,1,50),

(1,'Thì Quá khứ Hoàn thành (Past Perfect)','thi-qua-khu-hoan-thanh',
'<h4>Thì Quá khứ Hoàn thành</h4>
<p>Diễn tả hành động xảy ra <strong>trước</strong> một hành động khác trong quá khứ.</p>
<h5>Cấu trúc:</h5><p>S + <strong>had</strong> + V3/ed</p>
<h5>Ví dụ:</h5>
<ul><li><em>When I arrived, she <strong>had already left</strong>.</em></li>
<li><em>He <strong>had never seen</strong> snow before he went to Japan.</em></li>
<li><em>By the time I called, he <strong>had gone</strong> to bed.</em></li></ul>
<h5>Dấu hiệu:</h5><p>before, after, when, by the time, already, just, never, until</p>',
'advanced','grammar',30,1,51),

(1,'Mệnh đề quan hệ (Relative Clauses)','menh-de-quan-he',
'<h4>Mệnh đề quan hệ</h4>
<table class="table table-sm table-bordered"><thead><tr><th>Đại từ</th><th>Dùng cho</th><th>Ví dụ</th></tr></thead><tbody>
<tr><td>WHO</td><td>Người (chủ ngữ)</td><td>The girl <strong>who</strong> lives next door is friendly.</td></tr>
<tr><td>WHOM</td><td>Người (tân ngữ)</td><td>The man <strong>whom</strong> I met was kind.</td></tr>
<tr><td>WHICH</td><td>Vật / động vật</td><td>The book <strong>which</strong> I read was great.</td></tr>
<tr><td>THAT</td><td>Người hoặc vật</td><td>The car <strong>that</strong> he bought is expensive.</td></tr>
<tr><td>WHOSE</td><td>Sở hữu</td><td>The student <strong>whose</strong> bag is red got an A.</td></tr>
<tr><td>WHERE</td><td>Nơi chốn</td><td>The city <strong>where</strong> I grew up is beautiful.</td></tr>
<tr><td>WHEN</td><td>Thời gian</td><td>The year <strong>when</strong> I graduated was 2022.</td></tr>
</tbody></table>
<h5>Xác định vs Không xác định:</h5>
<ul><li><strong>Xác định:</strong> không có dấu phẩy – thông tin thiết yếu</li>
<li><strong>Không xác định:</strong> có dấu phẩy – thông tin bổ sung<br>
<em>My mother, <strong>who</strong> is a doctor, works in Hanoi.</em></li></ul>',
'advanced','grammar',35,1,52),

(1,'Câu bị động nâng cao (Passive Voice)','cau-bi-dong-nang-cao',
'<h4>Câu bị động nâng cao</h4>
<table class="table table-sm table-bordered"><thead><tr><th>Thì</th><th>Bị động</th></tr></thead><tbody>
<tr><td>Hiện tại đơn</td><td>Cars <strong>are made</strong> here.</td></tr>
<tr><td>Hiện tại tiếp diễn</td><td>The report <strong>is being written</strong>.</td></tr>
<tr><td>Quá khứ đơn</td><td>This house <strong>was built</strong> by him.</td></tr>
<tr><td>Hiện tại hoàn thành</td><td>The work <strong>has been finished</strong>.</td></tr>
<tr><td>Tương lai</td><td>It <strong>will be delivered</strong> tomorrow.</td></tr>
</tbody></table>
<h5>Bị động với Modal verbs:</h5>
<ul><li><em>The problem <strong>should be solved</strong> immediately.</em></li>
<li><em>Applications <strong>must be submitted</strong> before Friday.</em></li></ul>
<h5>Khi nào dùng bị động?</h5>
<ul><li>Không biết ai thực hiện hành động</li>
<li>Muốn nhấn mạnh vào đối tượng bị tác động</li>
<li>Văn phong trang trọng, khoa học</li></ul>',
'advanced','grammar',35,1,53),

(1,'Câu tường thuật (Reported Speech)','cau-tuong-thuat',
'<h4>Câu tường thuật</h4>
<h5>Chuyển thì (Backshift):</h5>
<table class="table table-sm table-bordered"><thead><tr><th>Trực tiếp</th><th>Gián tiếp</th></tr></thead><tbody>
<tr><td>am/is/are</td><td>was/were</td></tr>
<tr><td>do/does</td><td>did</td></tr>
<tr><td>will</td><td>would</td></tr>
<tr><td>can</td><td>could</td></tr>
<tr><td>has/have + V3</td><td>had + V3</td></tr>
<tr><td>Past Simple</td><td>Past Perfect</td></tr></tbody></table>
<h5>Câu phát biểu:</h5>
<ul><li>"I love English," she said. → She said (that) she <strong>loved</strong> English.</li>
<li>"I will help you," he told me. → He told me he <strong>would</strong> help me.</li></ul>
<h5>Câu hỏi tường thuật:</h5>
<ul><li>"Where do you live?" → She asked me where I <strong>lived</strong>.</li>
<li>"Are you tired?" → He asked me <strong>if/whether</strong> I was tired.</li></ul>',
'advanced','grammar',40,1,54),

(1,'Đảo ngữ (Inversion)','dao-ngu',
'<h4>Đảo ngữ trong Tiếng Anh</h4>
<h5>Đảo ngữ với Never/Rarely/Seldom/Hardly:</h5>
<ul><li><em><strong>Never</strong> have I seen such beautiful scenery.</em></li>
<li><em><strong>Rarely</strong> does she complain about anything.</em></li>
<li><em><strong>Hardly</strong> had I arrived when it started raining.</em></li></ul>
<h5>Đảo ngữ với Only:</h5>
<ul><li><em><strong>Only then</strong> did I understand the truth.</em></li>
<li><em><strong>Only by working hard</strong> can you succeed.</em></li></ul>
<h5>Đảo ngữ với Not only...but also:</h5>
<ul><li><em><strong>Not only</strong> did she win the race, but she also broke the record.</em></li></ul>
<h5>Đảo ngữ với So/Neither:</h5>
<ul><li>A: I like coffee. B: <strong>So do I.</strong></li><li>A: I can''t swim. B: <strong>Neither can I.</strong></li></ul>
<h5>Đảo ngữ giả định:</h5>
<ul><li><em><strong>Had</strong> I known, I would have helped.</em></li>
<li><em><strong>Were</strong> I you, I would study harder.</em></li></ul>',
'advanced','grammar',40,1,55),

(1,'Mệnh đề nhượng bộ và Đối lập','menh-de-nhuong-bo',
'<h4>Mệnh đề nhượng bộ và Đối lập</h4>
<h5>Although / Though / Even though:</h5>
<ul><li><em><strong>Although</strong> it was raining, we went for a walk.</em></li>
<li><em>I enjoyed the film <strong>even though</strong> it was long.</em></li></ul>
<h5>However / Nevertheless / Nonetheless:</h5>
<ul><li><em>The exam was difficult. <strong>However</strong>, most students passed.</em></li></ul>
<h5>Despite / In spite of + Noun/V-ing:</h5>
<ul><li><em><strong>Despite</strong> the rain, they played football.</em></li>
<li><em><strong>In spite of</strong> being tired, he continued working.</em></li></ul>
<h5>While / Whereas (đối lập):</h5>
<ul><li><em><strong>While</strong> Tom is outgoing, his sister is shy.</em></li>
<li><em>I prefer tea, <strong>whereas</strong> my husband loves coffee.</em></li></ul>
<h5>No matter how/what/when:</h5>
<ul><li><em><strong>No matter how hard</strong> I try, I always make mistakes.</em></li></ul>',
'advanced','grammar',35,1,56),

-- ========== NÂNG CAO – ĐỌC HIỂU ==========
(4,'Đọc hiểu: Biến đổi khí hậu','doc-hieu-bien-doi-khi-hau',
'<h4>Climate Change</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>Climate change is one of the most pressing issues facing our planet today. Scientific evidence shows that global temperatures have risen by approximately 1.1°C since pre-industrial times, primarily due to burning fossil fuels and deforestation.</p>
<p>The consequences are far-reaching. Rising sea levels threaten coastal communities, while extreme weather events are becoming more frequent. However, solutions exist: renewable energy, energy efficiency, and forest protection can significantly reduce greenhouse gas emissions.</p>
<p>The Paris Agreement of 2015, signed by 196 countries, aims to limit global warming to 1.5°C. Meeting this target requires urgent and ambitious action from governments, businesses, and individuals alike.</p>
</div>
<h5>Từ vựng học thuật:</h5>
<ul><li><strong>pressing</strong> (adj): cấp bách</li><li><strong>deforestation</strong>: nạn phá rừng</li>
<li><strong>far-reaching</strong>: có tầm ảnh hưởng rộng</li><li><strong>ambitious</strong>: tham vọng, quyết tâm</li></ul>',
'advanced','reading',35,1,60),

(4,'Đọc hiểu: Trí tuệ nhân tạo','doc-hieu-tri-tue-nhan-tao',
'<h4>Artificial Intelligence</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>Artificial Intelligence (AI) is transforming virtually every aspect of modern life. From voice assistants to medical diagnosis systems, AI technologies are becoming increasingly integrated into our daily routines.</p>
<p>Machine learning enables computers to learn from data without being explicitly programmed. Deep learning, using neural networks inspired by the human brain, has led to breakthroughs in image recognition and natural language processing.</p>
<p>Ethical considerations are crucial. Issues such as algorithmic bias, privacy violations, and autonomous systems require careful regulation. Many experts advocate for transparent, accountable, and fair AI development.</p>
</div>
<h5>Từ vựng:</h5>
<ul><li><strong>sophisticated</strong>: phức tạp, tinh vi</li><li><strong>neural network</strong>: mạng nơ-ron</li>
<li><strong>displacement</strong>: sự thay thế</li><li><strong>oversight</strong>: sự giám sát</li></ul>',
'advanced','reading',35,1,61),

(4,'Đọc hiểu: Sức khỏe tâm thần','doc-hieu-suc-khoe-tam-than',
'<h4>Mental Health</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>Mental health encompasses emotional, psychological, and social well-being. Despite its importance, mental health remains stigmatized in many societies, preventing millions from seeking help.</p>
<p>Common disorders include depression, anxiety, and bipolar disorder. Depression affects over 280 million people worldwide. Treatment options have advanced: Cognitive Behavioral Therapy (CBT) is highly effective, and mindfulness practices play crucial roles in mental health management.</p>
<p>Raising awareness and reducing stigma are vital. Open conversations about mental health, combined with accessible healthcare, can help create a more supportive environment.</p>
</div>
<h5>Từ vựng:</h5>
<ul><li><strong>stigmatized</strong>: bị kỳ thị</li><li><strong>persistent</strong>: dai dẳng</li>
<li><strong>cognitive</strong>: thuộc nhận thức</li><li><strong>accessible</strong>: dễ tiếp cận</li></ul>',
'advanced','reading',35,1,62),

-- ========== NÂNG CAO – GIAO TIẾP & VIẾT ==========
(5,'Thuyết trình tiếng Anh hiệu quả','thuyet-trinh-tieng-anh',
'<h4>Effective Presentations in English</h4>
<h5>Cấu trúc bài thuyết trình:</h5>
<ol><li><strong>Introduction:</strong> Hook + topic + outline</li>
<li><strong>Body:</strong> 3-4 main points with evidence</li>
<li><strong>Conclusion:</strong> Summary + call to action</li></ol>
<h5>Mẫu câu mở đầu:</h5>
<ul><li>Good morning everyone. Today I''m going to talk about...</li>
<li>By the end of this presentation, you will know...</li></ul>
<h5>Chuyển ý:</h5>
<ul><li>Moving on to my next point...</li><li>This brings me to...</li>
<li>As I mentioned earlier...</li></ul>
<h5>Kết luận:</h5>
<ul><li>In conclusion / To sum up...</li>
<li>The key takeaways are...</li><li>Thank you for your attention. Any questions?</li></ul>
<h5>Xử lý câu hỏi:</h5>
<ul><li>That''s a great question. / Let me clarify that point.</li>
<li>I''ll get back to you on that.</li></ul>',
'advanced','speaking',40,1,70),

(5,'Tranh luận và Thảo luận','tranh-luan-thao-luan',
'<h4>Debate & Discussion</h4>
<h5>Đồng ý:</h5>
<ul><li>I completely agree. / You''re absolutely right.</li>
<li>That''s a valid point. / I couldn''t agree more.</li></ul>
<h5>Không đồng ý lịch sự:</h5>
<ul><li>I see your point, but I think...</li>
<li>With all due respect, I disagree because...</li>
<li>That''s interesting, however...</li></ul>
<h5>Đưa ra ý kiến:</h5>
<ul><li>In my opinion / From my perspective...</li>
<li>I strongly believe that...</li><li>As far as I''m concerned...</li></ul>
<h5>Yêu cầu làm rõ:</h5>
<ul><li>Could you elaborate on that?</li><li>What exactly do you mean by...?</li></ul>',
'advanced','speaking',35,1,71),

(5,'Tiếng Anh thương mại: Email chuyên nghiệp','tieng-anh-thuong-mai-email',
'<h4>Business English: Professional Emails</h4>
<h5>Cấu trúc email kinh doanh:</h5>
<ul><li><strong>Subject:</strong> Ngắn gọn, rõ ràng</li>
<li><strong>Salutation:</strong> Dear Mr./Ms./Dr. [Surname],</li>
<li><strong>Opening:</strong> I am writing to...</li>
<li><strong>Closing:</strong> Best regards / Sincerely,</li></ul>
<h5>Mẫu email đề xuất hợp tác:</h5>
<div class="p-3 bg-light rounded border" style="font-size:.88rem">
<p>Dear Ms. Thompson,</p>
<p>I hope this email finds you well. I am reaching out to explore a potential partnership. We specialize in digital marketing solutions that have helped 200+ clients increase online visibility by 150% on average.</p>
<p>I would welcome a 30-minute video call next week to discuss further.</p>
<p>Best regards,<br>Minh Tran</p></div>',
'advanced','speaking',35,1,72),

(4,'Viết: Tiểu luận (Essay writing)','viet-tieu-luan',
'<h4>Essay Writing</h4>
<h5>Cấu trúc 5 đoạn chuẩn:</h5>
<ol><li><strong>Introduction:</strong> Hook → Background → Thesis statement</li>
<li><strong>Body 1, 2, 3:</strong> Topic sentence → Evidence → Explanation</li>
<li><strong>Conclusion:</strong> Restate thesis → Summary → Final thought</li></ol>
<h5>Thesis statement mạnh:</h5>
<p><em>Weak:</em> "Social media has effects on society."</p>
<p><em>Strong:</em> "While social media facilitates connectivity, its detrimental impact on mental health necessitates stricter regulation."</p>
<h5>Từ nối (Cohesive devices):</h5>
<ul><li><strong>Thêm ý:</strong> Furthermore, Moreover, In addition</li>
<li><strong>Đối lập:</strong> However, Nevertheless, On the other hand</li>
<li><strong>Kết quả:</strong> Therefore, Consequently, As a result</li>
<li><strong>Kết luận:</strong> In conclusion, To summarize, Overall</li></ul>',
'advanced','writing',40,1,73),

(4,'Viết: Thư xin việc (Cover Letter)','viet-thu-xin-viec',
'<h4>Cover Letter</h4>
<h5>Cấu trúc:</h5>
<ol><li>Lời chào + vị trí ứng tuyển</li>
<li>Kỹ năng và kinh nghiệm phù hợp</li>
<li>Lý do muốn làm tại công ty đó</li>
<li>Đề nghị phỏng vấn</li></ol>
<h5>Mẫu:</h5>
<div class="p-3 bg-light rounded border" style="font-size:.88rem">
<p>Dear Hiring Manager,</p>
<p>I am writing to express my strong interest in the Marketing Executive position. With three years of experience in digital marketing and a proven track record of driving 40% revenue growth, I am confident I would be a valuable addition to your team.</p>
<p>I would welcome the opportunity to discuss how my skills can contribute to your growth. Thank you for considering my application.</p>
<p>Sincerely, Lan Nguyen</p></div>',
'advanced','writing',40,1,74),

-- ========== NÂNG CAO – LUYỆN NGHE ==========
(3,'Luyện nghe: Tin tức tiếng Anh','luyen-nghe-tin-tuc',
'<h4>Listening to English News</h4>
<h5>Chiến lược nghe tin tức:</h5>
<ol><li><strong>Listen for gist:</strong> Nghe ý chính trước</li>
<li><strong>5W1H:</strong> Who, What, When, Where, Why, How</li>
<li><strong>Don''t stop:</strong> Không dừng khi gặp từ khó</li></ol>
<h5>Từ vựng tin tức:</h5>
<ul><li><strong>breaking news</strong>: tin nóng</li><li><strong>according to</strong>: theo như</li>
<li><strong>spokesperson</strong>: người phát ngôn</li>
<li><strong>surge</strong> (v/n): tăng vọt</li><li><strong>plunge</strong> (v/n): giảm mạnh</li></ul>
<h5>Nguồn nghe tốt:</h5>
<ul><li>BBC World Service, VOA Learning English</li>
<li>CNN 10 (10 phút/ngày), NPR</li></ul>',
'advanced','listening',35,1,80),

(3,'Luyện nghe: Podcast và Phim','luyen-nghe-podcast-phim',
'<h4>Learning through Podcasts & Films</h4>
<h5>Podcast hay cho học tiếng Anh:</h5>
<ul><li><strong>The English We Speak</strong> (BBC) – idioms thông dụng</li>
<li><strong>6 Minute English</strong> (BBC) – chủ đề đa dạng</li>
<li><strong>All Ears English</strong> – tiếng Anh Mỹ tự nhiên</li>
<li><strong>TED Talks Daily</strong> – chủ đề học thuật</li></ul>
<h5>Cách học qua phim:</h5>
<ol><li>Xem lần 1: phụ đề tiếng Anh</li>
<li>Xem lần 2: không phụ đề</li>
<li>Ghi chú từ mới và cụm từ hay</li>
<li>Lặp lại theo diễn viên (shadowing)</li></ol>
<h5>Phim theo level:</h5>
<ul><li><strong>Cơ bản:</strong> Friends, Modern Family</li>
<li><strong>Nâng cao:</strong> The Crown, Suits</li>
<li><strong>Cấp cao:</strong> The Newsroom, Succession</li></ul>',
'advanced','listening',35,1,81),

-- ========== CẤP CAO – NGỮ PHÁP & KỸ NĂNG ==========
(1,'Gerund vs Infinitive (Chuyên sâu)','gerund-vs-infinitive',
'<h4>Gerund vs Infinitive</h4>
<h5>Động từ + Gerund (V-ing):</h5>
<p>enjoy, avoid, consider, deny, finish, mind, practice, suggest, admit, delay, risk, keep, miss</p>
<ul><li><em>I enjoy <strong>swimming</strong>. She avoided <strong>making</strong> eye contact.</em></li></ul>
<h5>Động từ + Infinitive (to + V):</h5>
<p>want, need, hope, plan, decide, afford, agree, manage, refuse, expect, promise, offer</p>
<ul><li><em>She decided <strong>to study</strong> abroad. He managed <strong>to solve</strong> it.</em></li></ul>
<h5>Đổi nghĩa khi dùng cả hai:</h5>
<table class="table table-sm table-bordered"><tbody>
<tr><td><strong>remember</strong></td><td>I remember <strong>meeting</strong> her. (nhớ đã gặp)</td></tr>
<tr><td></td><td>Remember <strong>to call</strong> him. (nhớ phải gọi)</td></tr>
<tr><td><strong>stop</strong></td><td>He stopped <strong>smoking</strong>. (bỏ hút thuốc)</td></tr>
<tr><td></td><td>He stopped <strong>to smoke</strong>. (dừng lại để hút)</td></tr>
<tr><td><strong>try</strong></td><td>Try <strong>adding</strong> salt. (thử xem sao)</td></tr>
<tr><td></td><td>Try <strong>to finish</strong> on time. (cố gắng)</td></tr></tbody></table>',
'premium','grammar',45,1,90),

(1,'Cấu trúc nhấn mạnh: Cleft Sentences','cleft-sentences',
'<h4>Cleft Sentences – Câu tách đôi nhấn mạnh</h4>
<h5>It-Cleft:</h5>
<p>Cấu trúc: <strong>It + be + nhấn mạnh + that/who + phần còn lại</strong></p>
<ul><li>Normal: <em>Mary solved the problem.</em></li>
<li>Cleft: <em><strong>It was Mary who</strong> solved the problem.</em></li>
<li>Cleft: <em><strong>It was yesterday that</strong> she called.</em></li></ul>
<h5>Wh-Cleft (Pseudo-cleft):</h5>
<ul><li><em><strong>What I need</strong> is a good night''s sleep.</em></li>
<li><em><strong>What surprised me</strong> was her calm reaction.</em></li></ul>
<h5>All-Cleft:</h5>
<ul><li><em><strong>All I want</strong> is a cup of tea.</em></li>
<li><em><strong>All she did</strong> was smile.</em></li></ul>',
'premium','grammar',45,1,91),

(1,'Phrasal Verbs nâng cao','phrasal-verbs-nang-cao',
'<h4>Advanced Phrasal Verbs</h4>
<h5>Nhóm BRING:</h5>
<ul><li><strong>bring about</strong>: gây ra – <em>The storm brought about widespread damage.</em></li>
<li><strong>bring up</strong>: nuôi dưỡng; nêu ra – <em>She brought up an important issue.</em></li></ul>
<h5>Nhóm COME:</h5>
<ul><li><strong>come across</strong>: tình cờ gặp</li>
<li><strong>come up with</strong>: nghĩ ra (ý tưởng)</li>
<li><strong>come to terms with</strong>: chấp nhận thực tế</li></ul>
<h5>Nhóm GIVE:</h5>
<ul><li><strong>give away</strong>: cho không; vô tình tiết lộ</li>
<li><strong>give in</strong>: nhượng bộ, đầu hàng</li>
<li><strong>give up</strong>: bỏ cuộc</li></ul>
<h5>Nhóm LOOK:</h5>
<ul><li><strong>look into</strong>: điều tra, tìm hiểu</li>
<li><strong>look up to</strong>: ngưỡng mộ</li>
<li><strong>look down on</strong>: coi thường</li>
<li><strong>look forward to</strong>: mong đợi (+gerund)</li></ul>',
'premium','grammar',45,1,92),

(1,'Collocations nâng cao','collocations-nang-cao',
'<h4>Advanced Collocations</h4>
<h5>Động từ + Danh từ phổ biến:</h5>
<ul><li><strong>make:</strong> make a decision, make progress, make an effort, make a mistake</li>
<li><strong>do:</strong> do research, do damage, do business, do your best</li>
<li><strong>take:</strong> take action, take advantage, take responsibility, take a risk</li>
<li><strong>give:</strong> give advice, give a presentation, give permission, give priority</li>
<li><strong>reach:</strong> reach a conclusion, reach a compromise, reach an agreement</li></ul>
<h5>Tính từ + Danh từ:</h5>
<ul><li>strong: strong evidence, strong coffee, strong opinion</li>
<li>heavy: heavy traffic, heavy rain, heavy workload</li>
<li>high: high standards, high quality, high priority</li>
<li>deep: deep concern, deep sleep, deep understanding</li></ul>',
'premium','grammar',45,1,93),

(1,'Idioms trong giao tiếp','idioms-giao-tiep',
'<h4>Common Idioms in Communication</h4>
<h5>Work/Business:</h5>
<ul><li><strong>hit the ground running</strong>: bắt đầu tích cực ngay từ đầu</li>
<li><strong>the bottom line</strong>: điều quan trọng nhất; lợi nhuận ròng</li>
<li><strong>on the same page</strong>: có cùng quan điểm</li>
<li><strong>think outside the box</strong>: suy nghĩ sáng tạo</li></ul>
<h5>Life/Feelings:</h5>
<ul><li><strong>under the weather</strong>: không khỏe</li>
<li><strong>bite the bullet</strong>: chấp nhận điều khó khăn</li>
<li><strong>once in a blue moon</strong>: rất hiếm khi</li>
<li><strong>cost an arm and a leg</strong>: rất đắt tiền</li></ul>
<h5>Decision/Action:</h5>
<ul><li><strong>sit on the fence</strong>: không đứng về phía nào</li>
<li><strong>go back to square one</strong>: làm lại từ đầu</li>
<li><strong>burn bridges</strong>: phá hủy quan hệ</li></ul>',
'premium','grammar',45,1,94),

-- ========== CẤP CAO – IELTS & TOEIC ==========
(2,'IELTS Writing Task 1: Line Graphs','ielts-writing-task1-line-graph',
'<h4>IELTS Writing Task 1: Biểu đồ đường</h4>
<h5>Cấu trúc bài viết:</h5>
<ol><li><strong>Introduction:</strong> Paraphrase đề bài (không chép nguyên)</li>
<li><strong>Overview:</strong> 2-3 xu hướng chính (không có số liệu)</li>
<li><strong>Body 1 & 2:</strong> Mô tả chi tiết với số liệu cụ thể</li></ol>
<h5>Từ vựng mô tả tăng:</h5>
<p>rose, increased, grew, climbed, surged, soared + steadily, sharply, dramatically, gradually</p>
<h5>Từ vựng mô tả giảm:</h5>
<p>fell, decreased, declined, dropped, plummeted, dipped</p>
<h5>Điểm cao/thấp/ổn định:</h5>
<ul><li>reached a peak/highest point of... in...</li>
<li>remained stable/steady/constant at...</li>
<li>fluctuated between... and...</li></ul>
<h5>Mẫu Overview:</h5>
<p><em>Overall, it is clear that [main trend 1], while [main trend 2].</em></p>',
'premium','writing',50,1,100),

(2,'IELTS Writing Task 2: Opinion Essay','ielts-writing-task2-opinion',
'<h4>IELTS Writing Task 2: Opinion Essay</h4>
<h5>Các dạng đề:</h5>
<ul><li><strong>Opinion:</strong> Do you agree or disagree?</li>
<li><strong>Discussion:</strong> Discuss both views and give your opinion.</li>
<li><strong>Problem/Solution:</strong> What are the causes? What solutions?</li></ul>
<h5>Cấu trúc Opinion Essay:</h5>
<ol><li><strong>Introduction:</strong> Paraphrase + thesis (I strongly agree/disagree)</li>
<li><strong>Body 1 & 2:</strong> Lý do + ví dụ cụ thể</li>
<li><strong>Concession:</strong> Thừa nhận mặt đối lập + phản bác</li>
<li><strong>Conclusion:</strong> Tóm tắt + restate opinion</li></ol>
<h5>Band 7+ Vocabulary:</h5>
<ul><li>It is widely acknowledged that...</li>
<li>Proponents of this view argue that...</li>
<li>The ramifications of... are far-reaching.</li></ul>
<h5>Tiêu chí chấm (4 tiêu chí × 25%):</h5>
<ul><li>Task Achievement · Coherence & Cohesion · Lexical Resource · Grammatical Range</li></ul>',
'premium','writing',55,1,101),

(2,'IELTS Speaking: Part 1, 2, 3','ielts-speaking-all-parts',
'<h4>IELTS Speaking</h4>
<h5>Part 1 (4-5 phút): Introduction</h5>
<p>Câu hỏi về bản thân. Trả lời 2-3 câu dùng PEER: Point–Expand–Example–Reason.</p>
<h5>Part 2 (2 phút): Individual Long Turn</h5>
<p>Describe topic từ cue card. Chuẩn bị 1 phút. Dùng SPSE: Setting–People–Senses–Emotions.</p>
<h5>Part 3 (4-5 phút): Discussion</h5>
<p>Câu hỏi trừu tượng. Cần phân tích và đưa ý kiến có lý lẽ.</p>
<h5>Cách tăng Band Score:</h5>
<ul><li><strong>Fluency:</strong> Dùng fillers: "That''s an interesting question..."</li>
<li><strong>Vocabulary:</strong> Synonyms, collocations, idioms tự nhiên</li>
<li><strong>Grammar:</strong> Trộn lẫn thì và cấu trúc khác nhau</li>
<li><strong>Pronunciation:</strong> Nhấn từ đúng, ngữ điệu tự nhiên</li></ul>',
'premium','speaking',55,1,102),

(2,'IELTS Reading: Chiến lược làm bài','ielts-reading-chien-luoc',
'<h4>IELTS Reading Strategies</h4>
<h5>Các dạng câu hỏi:</h5>
<ul><li>Multiple choice · True/False/Not Given · Matching headings</li>
<li>Sentence completion · Summary completion · Short-answer</li></ul>
<h5>Chiến lược chung:</h5>
<ol><li><strong>Skimming:</strong> Đọc lướt nắm ý chính (2-3 phút/bài)</li>
<li><strong>Scanning:</strong> Tìm thông tin cụ thể</li>
<li><strong>Keywords:</strong> Gạch chân từ khóa trong câu hỏi</li>
<li><strong>Paraphrasing:</strong> Đáp án thường paraphrase thông tin trong bài</li></ol>
<h5>True/False/Not Given:</h5>
<ul><li><strong>TRUE:</strong> Bài xác nhận đúng</li>
<li><strong>FALSE:</strong> Bài mâu thuẫn</li>
<li><strong>NOT GIVEN:</strong> Không đề cập – không tự suy luận!</li></ul>
<h5>Quản lý thời gian:</h5>
<p>60 phút / 40 câu / 3 bài → ~20 phút/bài. Đánh dấu câu khó và bỏ qua trước.</p>',
'premium','reading',50,1,103),

(2,'TOEIC: Chiến lược Listening','toeic-listening-strategy',
'<h4>TOEIC Listening Parts 1–4</h4>
<h5>Part 1: Photographs (6 câu)</h5>
<p>Nhìn ảnh → nghe 4 mô tả → chọn đúng nhất. Chú ý: người, hành động, đồ vật, vị trí.</p>
<h5>Part 2: Question-Response (25 câu)</h5>
<p>Nghe câu hỏi → chọn câu trả lời đúng nhất. Cẩn thận "echo trap": lặp từ không liên quan.</p>
<h5>Part 3: Short Conversations (39 câu)</h5>
<p>Đọc câu hỏi TRƯỚC khi nghe. Chú ý: who/what/where/when/why.</p>
<h5>Part 4: Short Talks (30 câu)</h5>
<p>Announcements, ads, news. Đọc câu hỏi trước → dự đoán nội dung.</p>
<h5>Bẫy phổ biến:</h5>
<ul><li>Sound-alike: hear/here, know/no, write/right</li>
<li>Indirect answers: câu trả lời không trực tiếp vẫn đúng</li></ul>',
'premium','listening',45,1,104),

(2,'TOEIC: Reading Parts 5–7','toeic-reading-strategy',
'<h4>TOEIC Reading Parts 5–7</h4>
<h5>Part 5: Incomplete Sentences (30 câu)</h5>
<ul><li><strong>Từ loại:</strong> Nhìn vị trí trong câu → noun/verb/adjective/adverb</li>
<li><strong>Giới từ:</strong> at/in/on/for/since/during</li>
<li><strong>Từ nối:</strong> although/despite/however/therefore</li></ul>
<h5>Part 6: Text Completion (16 câu)</h5>
<p>Cần hiểu context cả đoạn văn, không chỉ 1 câu.</p>
<h5>Part 7: Reading Comprehension (54 câu)</h5>
<p>Single/Double/Triple passages. Đọc câu hỏi trước → scan đáp án.</p>
<h5>Từ vựng TOEIC kinh doanh:</h5>
<ul><li>invoice, quotation, agenda, deadline, outstanding</li>
<li>reimburse, comply with, prior to, in lieu of</li></ul>',
'premium','reading',50,1,105),

(2,'Business English: Cuộc họp chuyên nghiệp','business-english-cuoc-hop',
'<h4>Business Meetings</h4>
<h5>Khai mạc:</h5>
<ul><li>Let''s get started / Let''s get down to business.</li>
<li>The purpose of today''s meeting is...</li></ul>
<h5>Kiểm soát cuộc họp:</h5>
<ul><li>Let''s stay on topic. / That''s slightly off the agenda.</li>
<li>We''re running short on time. Let''s move on.</li>
<li>Let''s table this for now.</li></ul>
<h5>Đưa ra đề xuất:</h5>
<ul><li>I''d like to propose that... / What if we...?</li>
<li>My recommendation would be...</li></ul>
<h5>Kết thúc:</h5>
<ul><li>To recap what we''ve agreed on...</li>
<li>Action items: [person] will [task] by [deadline].</li></ul>',
'premium','speaking',50,1,106),

(2,'Phát âm: Âm cuối và Liên âm','phat-am-am-cuoi-lien-am',
'<h4>Pronunciation: Final Sounds & Linking</h4>
<h5>Âm cuối hay bị bỏ:</h5>
<ul><li>/t/: cat, sit, hot, fact</li><li>/d/: bad, said, played</li>
<li>/k/: book, back, topic</li><li>/θ/: month, health, fourth</li>
<li>/ŋ/: thing, long, bring</li></ul>
<h5>Liên âm (Linking):</h5>
<ul><li><strong>Phụ âm + Nguyên âm:</strong> "pick it up" → /pɪ.kɪ.tʌp/</li>
<li><strong>Phụ âm giống nhau:</strong> "black coffee" → /blæ-kɒfi/</li></ul>
<h5>Weak forms:</h5>
<p>and→/ən/ · to→/tə/ · for→/fə/ · of→/əv/ · at→/ət/ · can→/kən/</p>
<h5>Kỹ thuật Shadowing:</h5>
<p>Nghe 1 câu → dừng → lặp lại chính xác cách phát âm, nhịp điệu, ngữ điệu. Luyện 10-15 phút/ngày.</p>',
'premium','listening',45,1,107),

(2,'Văn phong học thuật (Academic Writing)','van-phong-hoc-thuat',
'<h4>Academic Writing Style</h4>
<h5>Đặc điểm:</h5>
<ul><li>Khách quan, tránh ngôi I khi có thể</li>
<li>Chính xác, cụ thể, có dẫn chứng</li>
<li>Trang trọng, tránh viết tắt không chính thức</li></ul>
<h5>Informal → Formal:</h5>
<ul><li>lots of → a significant number of</li>
<li>show → demonstrate / indicate / illustrate</li>
<li>because → due to / owing to / as a result of</li>
<li>I think → It can be argued that / evidence suggests that</li></ul>
<h5>Hedging – Giảm độ chắc chắn:</h5>
<ul><li>It appears/seems that...</li><li>This may/could suggest that...</li>
<li>There is some evidence to suggest...</li></ul>
<h5>Dẫn nguồn:</h5>
<ul><li>According to Smith (2023)...</li>
<li>As noted by Johnson et al. (2022)...</li>
<li>Research has demonstrated that... (Brown, 2021)</li></ul>',
'premium','writing',50,1,108),

(2,'Đọc hiểu học thuật: Kinh tế học','doc-hieu-kinh-te-hoc',
'<h4>Academic Reading: Economics</h4>
<div class="p-3 bg-light rounded border mb-3">
<p>Supply and demand is one of the most fundamental concepts in economics. The law of demand states that as the price of a good increases, the quantity demanded decreases. Conversely, the law of supply posits that producers will supply more of a good when its price is higher.</p>
<p>When supply and demand are in equilibrium, the market clears at a price where amounts produced and consumed are equal. Market failures occur when this mechanism breaks down. Externalities—costs or benefits not reflected in market prices—are a classic example. Pollution represents a negative externality: factories impose costs on society not captured in the price of goods.</p>
</div>
<h5>Từ vựng:</h5>
<ul><li><strong>equilibrium</strong>: trạng thái cân bằng</li>
<li><strong>externality</strong>: ngoại tác</li>
<li><strong>intervention</strong>: sự can thiệp</li>
<li><strong>mechanism</strong>: cơ chế</li></ul>',
'premium','reading',50,1,109);


-- ============================================================
-- BÀI TẬP cho bài học mới (dùng slug để tránh hardcode ID)
-- ============================================================

INSERT IGNORE INTO exercises (lesson_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation, order_num)

SELECT l.id,'She gave ___ a gift yesterday.','I','me','my','mine','B','Sau động từ cần tân ngữ (object pronoun) → me',1
FROM lessons l WHERE l.slug='dai-tu-nhan-xung'
UNION ALL
SELECT l.id,'___ book is this?','Who','Whose','Whom','Which','B','Hỏi sở hữu → Whose',2
FROM lessons l WHERE l.slug='dai-tu-nhan-xung'
UNION ALL
SELECT l.id,'The children enjoyed ___ during the trip.','they','them','themselves','their','C','Chủ ngữ = tân ngữ → reflexive: themselves',3
FROM lessons l WHERE l.slug='dai-tu-nhan-xung'

UNION ALL
SELECT l.id,'She plays ___ violin in the orchestra.','a','an','the','no article','C','Nhạc cụ dùng "the"',1
FROM lessons l WHERE l.slug='mao-tu-a-an-the'
UNION ALL
SELECT l.id,'I saw ___ interesting documentary last night.','a','an','the','no article','B','documentary bắt đầu bằng âm /ɪ/ → an',2
FROM lessons l WHERE l.slug='mao-tu-a-an-the'
UNION ALL
SELECT l.id,'___ Eiffel Tower is in Paris.','A','An','The','no article','C','Công trình nổi tiếng duy nhất → the',3
FROM lessons l WHERE l.slug='mao-tu-a-an-the'
UNION ALL
SELECT l.id,'We had ___ dinner with friends last Friday.','a','an','the','no article','D','Bữa ăn không dùng mạo từ',4
FROM lessons l WHERE l.slug='mao-tu-a-an-the'

UNION ALL
SELECT l.id,'Look! The children ___ in the park.','play','plays','are playing','played','C','"Look!" là dấu hiệu của Present Continuous',1
FROM lessons l WHERE l.slug='thi-hien-tai-tiep-dien'
UNION ALL
SELECT l.id,'She ___ a report at the moment.','writes','is writing','has written','wrote','B','"at the moment" → Present Continuous',2
FROM lessons l WHERE l.slug='thi-hien-tai-tiep-dien'
UNION ALL
SELECT l.id,'___ you ___ to music right now?','Do/listen','Are/listening','Have/listened','Did/listen','B','"right now" → Present Continuous dạng nghi vấn',3
FROM lessons l WHERE l.slug='thi-hien-tai-tiep-dien'

UNION ALL
SELECT l.id,'I ___ will go to the beach tomorrow (kế hoạch đã lên trước).','will go','am going to go','go','went','B','Kế hoạch đã có trước → be going to',1
FROM lessons l WHERE l.slug='thi-tuong-lai'
UNION ALL
SELECT l.id,'Look at those clouds! It ___ rain.','will','is going to','rains','rained','B','Có bằng chứng hiện tại → is going to',2
FROM lessons l WHERE l.slug='thi-tuong-lai'

UNION ALL
SELECT l.id,'You look tired. You ___ take a rest.','must','should','would','might','B','Lời khuyên → should',1
FROM lessons l WHERE l.slug='dong-tu-khuyet-thieu'
UNION ALL
SELECT l.id,'Visitors ___ smoke here. It is strictly forbidden.','shouldn''t','might not','must not','don''t have to','C','Cấm tuyệt đối → must not',2
FROM lessons l WHERE l.slug='dong-tu-khuyet-thieu'
UNION ALL
SELECT l.id,'He ___ be at home now. I just saw him at the office.','can''t','mustn''t','shouldn''t','might not','A','Loại trừ khả năng → can''t',3
FROM lessons l WHERE l.slug='dong-tu-khuyet-thieu'

UNION ALL
SELECT l.id,'I ___ never ___ sushi before.','have/eaten','had/eaten','have/eat','did/eat','A','"never" + kinh nghiệm → Present Perfect: have + never + V3',1
FROM lessons l WHERE l.slug='thi-hien-tai-hoan-thanh'
UNION ALL
SELECT l.id,'She ___ in this city ___ 2015.','lived/for','has lived/since','has lived/for','lived/since','B','Kéo dài đến nay + mốc thời gian → has lived + since',2
FROM lessons l WHERE l.slug='thi-hien-tai-hoan-thanh'
UNION ALL
SELECT l.id,'The team ___ the project yet.','didn''t finish','hasn''t finished','don''t finish','hadn''t finished','B','"yet" ở câu phủ định → Present Perfect',3
FROM lessons l WHERE l.slug='thi-hien-tai-hoan-thanh'

UNION ALL
SELECT l.id,'When I arrived, she ___ already ___.','has/left','had/left','was/leaving','did/leave','B','Hành động xảy ra trước trong quá khứ → Past Perfect',1
FROM lessons l WHERE l.slug='thi-qua-khu-hoan-thanh'
UNION ALL
SELECT l.id,'By the time I called, he ___ to bed.','went','has gone','had gone','was going','C','"By the time" + Past Perfect',2
FROM lessons l WHERE l.slug='thi-qua-khu-hoan-thanh'

UNION ALL
SELECT l.id,'The woman ___ I spoke to was very helpful.','who','whom','which','whose','B','Người làm tân ngữ (I spoke to her) → whom',1
FROM lessons l WHERE l.slug='menh-de-quan-he'
UNION ALL
SELECT l.id,'This is the city ___ I was born.','which','that','where','when','C','Chỉ nơi chốn → where',2
FROM lessons l WHERE l.slug='menh-de-quan-he'
UNION ALL
SELECT l.id,'The students ___ scores were highest got scholarships.','who','whom','which','whose','D','Sở hữu (their scores) → whose',3
FROM lessons l WHERE l.slug='menh-de-quan-he'

UNION ALL
SELECT l.id,'The new hospital ___ by the government next year.','will build','will be built','is built','has built','B','Tương lai bị động: will be built',1
FROM lessons l WHERE l.slug='cau-bi-dong-nang-cao'
UNION ALL
SELECT l.id,'A new solution ___ by the team at the moment.','is finding','is being found','has been found','was finding','B','Present Continuous Passive: is being + V3',2
FROM lessons l WHERE l.slug='cau-bi-dong-nang-cao'

UNION ALL
SELECT l.id,'He said, "I am tired." → He said he ___ tired.','is','was','were','had been','B','am/is → was trong Reported Speech',1
FROM lessons l WHERE l.slug='cau-tuong-thuat'
UNION ALL
SELECT l.id,'"Do you like coffee?" → She asked me if I ___ coffee.','like','liked','had liked','would like','B','Chuyển thì: do → did',2
FROM lessons l WHERE l.slug='cau-tuong-thuat'

UNION ALL
SELECT l.id,'I enjoy ___ in the rain.','to walk','walking','walk','walked','B','enjoy + gerund (V-ing)',1
FROM lessons l WHERE l.slug='gerund-vs-infinitive'
UNION ALL
SELECT l.id,'She decided ___ a new language.','learning','learned','to learn','learn','C','decide + to-infinitive',2
FROM lessons l WHERE l.slug='gerund-vs-infinitive'
UNION ALL
SELECT l.id,'Remember ___ off the lights before you leave.','turning','to turn','turn','turned','B','remember + to-inf = nhớ phải làm gì',3
FROM lessons l WHERE l.slug='gerund-vs-infinitive'
UNION ALL
SELECT l.id,'He stopped ___ and listened carefully.','talking','to talk','talk','talked','A','stop + gerund = dừng hành động đó lại',4
FROM lessons l WHERE l.slug='gerund-vs-infinitive'

UNION ALL
SELECT l.id,'___ have I seen such a beautiful sunset.','Never','Rarely','Seldom','Hardly','A','Never đảo ngữ đứng đầu câu + have/has',1
FROM lessons l WHERE l.slug='dao-ngu'
UNION ALL
SELECT l.id,'___ did I realize the truth until it was too late.','Not only','Only then','Hardly','Rarely','B','Only then đảo ngữ + did',2
FROM lessons l WHERE l.slug='dao-ngu'
UNION ALL
SELECT l.id,'A: I love Italian food. B: ___ do I.','Either','Neither','So','Nor','C','Đồng ý khẳng định → So do I',3
FROM lessons l WHERE l.slug='dao-ngu'

UNION ALL
SELECT l.id,'___ it was raining, we went for a walk.','Despite','Although','In spite of','However','B','Trước mệnh đề (S+V) dùng Although/Though',1
FROM lessons l WHERE l.slug='menh-de-nhuong-bo'
UNION ALL
SELECT l.id,'___ the rain, they played football.','Although','Even though','Despite','However','C','Trước danh từ dùng Despite/In spite of',2
FROM lessons l WHERE l.slug='menh-de-nhuong-bo'

UNION ALL
SELECT l.id,'___ was Mary who solved the problem.','That','It','This','What','B','It-cleft: It was [người] who...',1
FROM lessons l WHERE l.slug='cleft-sentences'
UNION ALL
SELECT l.id,'___ I need is a good night''s sleep.','That','It','What','Which','C','Wh-cleft: What + clause + be + nhấn mạnh',2
FROM lessons l WHERE l.slug='cleft-sentences';

-- ============================================================
-- TỪ VỰNG MỞ RỘNG (500+ từ)
-- ============================================================

INSERT IGNORE INTO vocabulary (word, pronunciation, definition, example, translation, level, category) VALUES

-- ===== CƠ BẢN – CHÀO HỎI =====
('good morning','/ɡʊd ˈmɔːrnɪŋ/','Greeting used in the morning','Good morning! Did you sleep well?','Chào buổi sáng','basic','Greetings'),
('good afternoon','/ɡʊd ˌæftərˈnuːn/','Greeting used in the afternoon','Good afternoon, everyone!','Chào buổi chiều','basic','Greetings'),
('good evening','/ɡʊd ˈiːvnɪŋ/','Greeting used in the evening','Good evening! Welcome.','Chào buổi tối','basic','Greetings'),
('goodbye','/ɡʊdˈbaɪ/','Said when leaving someone','Goodbye! See you tomorrow!','Tạm biệt','basic','Greetings'),
('see you later','/siː juː ˈleɪtər/','Informal farewell','See you later!','Hẹn gặp lại','basic','Greetings'),
('how are you','/haʊ ɑːr juː/','Asking about wellbeing','How are you today?','Bạn có khỏe không?','basic','Greetings'),
('fine','/faɪn/','In good condition','I am fine, thank you.','Khỏe','basic','Greetings'),
('pleased to meet you','/pliːzd tə miːt juː/','Said when meeting someone for the first time','Pleased to meet you, Dr. Smith.','Rất vui được gặp bạn','basic','Greetings'),
('excuse me','/ɪkˈskjuːz miː/','Used to politely get attention','Excuse me, could you help me?','Xin lỗi (để hỏi)','basic','Greetings'),
('pardon','/ˈpɑːrdən/','Ask someone to repeat','Pardon? I did not catch that.','Xin lỗi, bạn nói gì?','basic','Greetings'),
('you are welcome','/juː ɑːr ˈwelkəm/','Response to thank you','You are welcome!','Không có gì','basic','Greetings'),
('congratulations','/kənˌɡrætʃʊˈleɪʃənz/','Expression of praise for achievement','Congratulations on your graduation!','Chúc mừng','basic','Greetings'),

-- ===== CƠ BẢN – GIA ĐÌNH =====
('family','/ˈfæmɪli/','A group of related people','I love my family.','Gia đình','basic','Family'),
('father','/ˈfɑːðər/','A male parent','My father is a doctor.','Cha, bố','basic','Family'),
('mother','/ˈmʌðər/','A female parent','My mother cooks delicious food.','Mẹ','basic','Family'),
('brother','/ˈbrʌðər/','A male sibling','I have two brothers.','Anh/em trai','basic','Family'),
('sister','/ˈsɪstər/','A female sibling','My sister is a teacher.','Chị/em gái','basic','Family'),
('grandfather','/ˈɡrændfɑːðər/','The father of your parent','My grandfather is 80.','Ông nội/ngoại','basic','Family'),
('grandmother','/ˈɡrændmʌðər/','The mother of your parent','My grandmother tells great stories.','Bà nội/ngoại','basic','Family'),
('husband','/ˈhʌzbənd/','A married man','Her husband works abroad.','Chồng','basic','Family'),
('wife','/waɪf/','A married woman','His wife is a nurse.','Vợ','basic','Family'),
('child','/tʃaɪld/','A young person','They have three children.','Đứa trẻ','basic','Family'),
('uncle','/ˈʌŋkəl/','Brother of your parent','My uncle lives in Canada.','Chú, cậu, bác','basic','Family'),
('aunt','/ænt/','Sister of your parent','My aunt makes the best cake.','Cô, dì, bác gái','basic','Family'),
('cousin','/ˈkʌzən/','Child of your aunt or uncle','I grew up with my cousins.','Anh/chị/em họ','basic','Family'),
('nephew','/ˈnefjuː/','Brother''s or sister''s son','My nephew is very smart.','Cháu trai','basic','Family'),
('niece','/niːs/','Brother''s or sister''s daughter','My niece loves dancing.','Cháu gái','basic','Family'),

-- ===== CƠ BẢN – MÀU SẮC =====
('red','/red/','The color of blood','She wore a red dress.','Màu đỏ','basic','Colors'),
('blue','/bluː/','The color of the sky','The sky is so blue today.','Màu xanh dương','basic','Colors'),
('green','/ɡriːn/','The color of grass','The forest is deep green.','Màu xanh lá','basic','Colors'),
('yellow','/ˈjeloʊ/','The color of the sun','She painted the room yellow.','Màu vàng','basic','Colors'),
('black','/blæk/','The darkest color','He always wears black suits.','Màu đen','basic','Colors'),
('white','/waɪt/','The lightest color','She wore a white dress.','Màu trắng','basic','Colors'),
('purple','/ˈpɜːrpəl/','A mix of red and blue','Lavender is a type of purple.','Màu tím','basic','Colors'),
('orange','/ˈɔːrɪndʒ/','A mix of red and yellow','The sunset turned the sky orange.','Màu cam','basic','Colors'),
('pink','/pɪŋk/','A light red color','The baby room is painted pink.','Màu hồng','basic','Colors'),
('brown','/braʊn/','The color of wood','He has brown eyes.','Màu nâu','basic','Colors'),
('grey','/ɡreɪ/','Between black and white','It was a grey, cloudy day.','Màu xám','basic','Colors'),
('silver','/ˈsɪlvər/','A shiny grey-white color','She has silver hair.','Màu bạc','basic','Colors'),
('gold','/ɡoʊld/','A shiny yellow color like gold metal','She wore a gold necklace.','Màu vàng kim','basic','Colors'),

-- ===== CƠ BẢN – ĐỒ ĂN & UỐNG =====
('rice','/raɪs/','A common grain, staple in Asia','Vietnamese people eat rice daily.','Cơm, gạo','basic','Food'),
('noodles','/ˈnuːdəlz/','Long thin strips of pasta','I love pho noodle soup.','Mì, bún','basic','Food'),
('bread','/bred/','Baked food from flour','I have toast for breakfast.','Bánh mì','basic','Food'),
('meat','/miːt/','Flesh from animals','She does not eat meat.','Thịt','basic','Food'),
('fish','/fɪʃ/','Aquatic animal used as food','Grilled fish is my favourite.','Cá','basic','Food'),
('vegetable','/ˈvedʒtəbəl/','A plant used as food','Eat more vegetables.','Rau củ','basic','Food'),
('fruit','/fruːt/','Sweet product of a plant','Fresh fruit is full of vitamins.','Hoa quả','basic','Food'),
('water','/ˈwɔːtər/','A clear liquid essential for life','Drink 8 glasses of water a day.','Nước','basic','Food'),
('milk','/mɪlk/','White liquid from mammals','She drinks milk every morning.','Sữa','basic','Food'),
('egg','/eɡ/','Round object laid by birds','I had scrambled eggs for breakfast.','Trứng','basic','Food'),
('soup','/suːp/','A liquid food','Chicken soup is good when sick.','Súp, canh','basic','Food'),
('salad','/ˈsæləd/','A dish of raw vegetables','Caesar salad is a classic.','Rau trộn, salad','basic','Food'),
('cake','/keɪk/','A sweet baked food','We had chocolate cake at the party.','Bánh ngọt','basic','Food'),
('coffee','/ˈkɒfi/','A dark, bitter hot drink','Vietnamese coffee is very strong.','Cà phê','basic','Food'),
('tea','/tiː/','Hot drink made from leaves','I prefer green tea.','Trà','basic','Food'),
('juice','/dʒuːs/','Liquid from fruits/vegetables','She drank orange juice for breakfast.','Nước ép','basic','Food'),
('delicious','/dɪˈlɪʃəs/','Having a very pleasant taste','This dish is absolutely delicious!','Ngon','basic','Food'),
('hungry','/ˈhʌŋɡri/','Feeling the need to eat','I am starving!','Đói bụng','basic','Food'),
('thirsty','/ˈθɜːrsti/','Feeling the need to drink','It is hot and I am very thirsty.','Khát nước','basic','Food'),
('cook','/kʊk/','To prepare food by heating','She loves to cook for her family.','Nấu ăn','basic','Food'),
('recipe','/ˈresɪpi/','Instructions for cooking a dish','She followed her grandmother''s recipe.','Công thức nấu ăn','basic','Food'),
('ingredient','/ɪnˈɡriːdiənt/','A component of a recipe','The main ingredient is garlic.','Nguyên liệu','basic','Food'),
('restaurant','/ˈrestrɒnt/','A place where meals are served','Let''s go to that new restaurant.','Nhà hàng','basic','Food'),
('menu','/ˈmenjuː/','A list of dishes available','Can I see the menu, please?','Thực đơn','basic','Food'),

-- ===== CƠ BẢN – NGHỀ NGHIỆP =====
('doctor','/ˈdɒktər/','A person who treats sick people','She wants to become a doctor.','Bác sĩ','basic','Occupations'),
('nurse','/nɜːrs/','A person who cares for patients','The nurse checked his blood pressure.','Y tá','basic','Occupations'),
('teacher','/ˈtiːtʃər/','A person who teaches','My English teacher is very patient.','Giáo viên','basic','Occupations'),
('student','/ˈstjuːdənt/','A person who studies','She is a dedicated student.','Học sinh, sinh viên','basic','Occupations'),
('engineer','/ˌendʒɪˈnɪər/','A person who designs and builds','He is a software engineer.','Kỹ sư','basic','Occupations'),
('lawyer','/ˈlɔːjər/','A person who practices law','She hired a lawyer for the contract.','Luật sư','basic','Occupations'),
('accountant','/əˈkaʊntənt/','A person who manages finances','He is a certified accountant.','Kế toán','basic','Occupations'),
('farmer','/ˈfɑːrmər/','A person who works on a farm','Farmers wake up very early.','Nông dân','basic','Occupations'),
('chef','/ʃef/','A professional cook','The chef created a new menu.','Đầu bếp','basic','Occupations'),
('police officer','/pəˈliːs ˌɒfɪsər/','A member of the police','The officer directed traffic.','Cảnh sát','basic','Occupations'),
('firefighter','/ˈfaɪərfaɪtər/','A person who fights fires','Firefighters are very brave.','Lính cứu hỏa','basic','Occupations'),
('dentist','/ˈdentɪst/','A doctor who treats teeth','I go to the dentist twice a year.','Nha sĩ','basic','Occupations'),
('journalist','/ˈdʒɜːrnəlɪst/','A person who writes news','The journalist wrote a great story.','Nhà báo','basic','Occupations'),
('pilot','/ˈpaɪlət/','A person who flies aircraft','The pilot announced a safe landing.','Phi công','basic','Occupations'),
('programmer','/ˈproʊɡræmər/','A person who writes code','She is a talented programmer.','Lập trình viên','basic','Occupations'),
('manager','/ˈmænɪdʒər/','A person who controls a business','The manager handled the complaint.','Quản lý','basic','Occupations'),
('secretary','/ˈsekrətri/','A person who handles office work','The secretary organized the meeting.','Thư ký','basic','Occupations'),
('scientist','/ˈsaɪəntɪst/','A person who studies science','The scientist made a discovery.','Nhà khoa học','basic','Occupations'),
('artist','/ˈɑːrtɪst/','A person who creates art','The artist painted beautiful portraits.','Nghệ sĩ, họa sĩ','basic','Occupations'),
('musician','/mjuːˈzɪʃən/','A person who plays music','She is a talented musician.','Nhạc sĩ','basic','Occupations'),

-- ===== CƠ BẢN – PHƯƠNG TIỆN =====
('car','/kɑːr/','A motor vehicle with four wheels','She drives to work every day.','Ô tô','basic','Transportation'),
('bus','/bʌs/','A large vehicle for passengers','I take the bus to school.','Xe buýt','basic','Transportation'),
('motorcycle','/ˈmoʊtərsaɪkəl/','A two-wheeled motor vehicle','Most people in Vietnam ride motorcycles.','Xe máy','basic','Transportation'),
('bicycle','/ˈbaɪsɪkəl/','A vehicle powered by pedaling','He cycles to the park.','Xe đạp','basic','Transportation'),
('train','/treɪn/','A vehicle running on rails','The train from Hanoi takes 30 hours.','Tàu hỏa','basic','Transportation'),
('airplane','/ˈeərpleɪn/','A machine that flies','We took an airplane to Japan.','Máy bay','basic','Transportation'),
('ship','/ʃɪp/','A large boat','The cruise ship was enormous.','Tàu thủy','basic','Transportation'),
('taxi','/ˈtæksi/','A car for hire with driver','She took a taxi to the airport.','Xe taxi','basic','Transportation'),
('traffic','/ˈtræfɪk/','Vehicles moving on roads','Heavy traffic during rush hour.','Giao thông','basic','Transportation'),
('traffic jam','/ˈtræfɪk dʒæm/','When traffic stops or slows greatly','I was stuck in a traffic jam.','Kẹt xe','basic','Transportation'),
('driving licence','/ˈdraɪvɪŋ ˈlaɪsəns/','A permit to drive','You need a licence to drive.','Bằng lái xe','basic','Transportation'),
('petrol/gas','/ˈpetrəl/','Fuel for vehicles','The car needs petrol.','Xăng','basic','Transportation'),

-- ===== CƠ BẢN – THỂ THAO =====
('football','/ˈfʊtbɔːl/','A team sport played with a ball','Football is the most popular sport.','Bóng đá','basic','Sports'),
('basketball','/ˈbɑːskɪtbɔːl/','Sport where players score in a basket','He plays basketball every weekend.','Bóng rổ','basic','Sports'),
('swimming','/ˈswɪmɪŋ/','Moving through water by body movement','Swimming is great exercise.','Bơi lội','basic','Sports'),
('running','/ˈrʌnɪŋ/','Moving fast on foot','She goes running every morning.','Chạy bộ','basic','Sports'),
('tennis','/ˈtenɪs/','Racket sport with a net','He plays tennis on weekends.','Quần vợt','basic','Sports'),
('badminton','/ˈbædmɪntən/','Racket sport with a shuttlecock','Badminton is popular in Vietnam.','Cầu lông','basic','Sports'),
('volleyball','/ˈvɒlɪbɔːl/','Team sport over a net','They play volleyball on the beach.','Bóng chuyền','basic','Sports'),
('cycling','/ˈsaɪklɪŋ/','Riding a bicycle for sport/exercise','Cycling is eco-friendly.','Đạp xe','basic','Sports'),
('yoga','/ˈjoʊɡə/','Mind-body exercise from India','She does yoga every morning.','Yoga','basic','Sports'),
('gym','/dʒɪm/','A place for physical exercise','He goes to the gym three times a week.','Phòng tập thể dục','basic','Sports'),

-- ===== CƠ BẢN – THỜI TIẾT =====
('sunny','/ˈsʌni/','Bright with sunlight','It is a warm, sunny day.','Trời nắng','basic','Weather'),
('cloudy','/ˈklaʊdi/','Covered with clouds','It was cloudy and cold.','Trời nhiều mây','basic','Weather'),
('rainy','/ˈreɪni/','Having a lot of rain','I love rainy days.','Trời mưa','basic','Weather'),
('windy','/ˈwɪndi/','Having strong wind','It was too windy to fly a kite.','Gió mạnh','basic','Weather'),
('hot','/hɒt/','Having a high temperature','It is very hot in the summer.','Nóng','basic','Weather'),
('cold','/koʊld/','Having a low temperature','It is cold in winter.','Lạnh','basic','Weather'),
('humid','/ˈhjuːmɪd/','Having a lot of water vapor','HCMC is hot and humid.','Ẩm ướt','basic','Weather'),
('storm','/stɔːrm/','Bad weather with strong winds','The storm caused damage.','Bão','basic','Weather'),
('flood','/flʌd/','Overflow of water onto land','Heavy rain caused flooding.','Lũ lụt','basic','Weather'),
('temperature','/ˈtemprətʃər/','The degree of heat','The temperature dropped to 10°C.','Nhiệt độ','basic','Weather'),
('forecast','/ˈfɔːrkæst/','Prediction of future weather','Let me check the forecast.','Dự báo thời tiết','basic','Weather'),
('umbrella','/ʌmˈbrelə/','Device to protect from rain','Don''t forget your umbrella.','Ô, dù','basic','Weather'),
('snow','/snoʊ/','Frozen water that falls as flakes','It snows in Sapa in winter.','Tuyết','basic','Weather'),
('fog','/fɒɡ/','Thick mist close to ground','The fog made driving dangerous.','Sương mù','basic','Weather'),

-- ===== CƠ BẢN – CƠ THỂ NGƯỜI =====
('head','/hed/','The top part of the body','She shook her head.','Đầu','basic','Body'),
('hair','/her/','Strands growing from the head','She has long curly hair.','Tóc','basic','Body'),
('eye','/aɪ/','The organ for seeing','He has beautiful blue eyes.','Mắt','basic','Body'),
('nose','/noʊz/','The organ for smelling','My nose is running.','Mũi','basic','Body'),
('mouth','/maʊθ/','Opening used for eating/speaking','Open your mouth wide.','Miệng','basic','Body'),
('ear','/ɪər/','The organ for hearing','He has a problem in his left ear.','Tai','basic','Body'),
('hand','/hænd/','The part at the end of the arm','She waved her hand.','Bàn tay','basic','Body'),
('arm','/ɑːrm/','The upper limb','He broke his arm playing football.','Cánh tay','basic','Body'),
('leg','/leɡ/','The lower limb','She has long, slim legs.','Chân','basic','Body'),
('heart','/hɑːrt/','The organ that pumps blood','Exercise strengthens your heart.','Tim','basic','Body'),
('stomach','/ˈstʌmək/','The organ that digests food','My stomach hurts.','Dạ dày, bụng','basic','Body'),
('back','/bæk/','The rear part of the body','He has back pain from sitting too long.','Lưng','basic','Body'),
('shoulder','/ˈʃoʊldər/','The joint connecting arm to body','She has strong shoulders.','Vai','basic','Body'),
('knee','/niː/','The joint in the middle of the leg','He hurt his knee playing football.','Đầu gối','basic','Body'),
('finger','/ˈfɪŋɡər/','One of the digits on the hand','She wears a ring on her finger.','Ngón tay','basic','Body'),

-- ===== CƠ BẢN – NHÀ Ở =====
('bedroom','/ˈbedruːm/','A room for sleeping','My bedroom is on the second floor.','Phòng ngủ','basic','Home'),
('kitchen','/ˈkɪtʃɪn/','A room for preparing food','She was cooking in the kitchen.','Nhà bếp','basic','Home'),
('bathroom','/ˈbæθruːm/','A room with a bath and toilet','The bathroom is down the hall.','Phòng tắm','basic','Home'),
('living room','/ˈlɪvɪŋ ruːm/','A room for relaxing','We watched TV in the living room.','Phòng khách','basic','Home'),
('dining room','/ˈdaɪnɪŋ ruːm/','A room for eating meals','We eat in the dining room.','Phòng ăn','basic','Home'),
('table','/ˈteɪbəl/','Furniture with a flat top','Put the books on the table.','Bàn','basic','Home'),
('chair','/tʃer/','A seat with a back','Please have a seat.','Ghế','basic','Home'),
('bed','/bed/','Furniture for sleeping','This bed is very comfortable.','Giường','basic','Home'),
('sofa','/ˈsoʊfə/','A long padded seat','She relaxed on the sofa.','Ghế sofa','basic','Home'),
('door','/dɔːr/','A movable barrier','Please close the door.','Cửa','basic','Home'),
('window','/ˈwɪndoʊ/','Opening that lets in light','Open the window – it''s stuffy.','Cửa sổ','basic','Home'),
('stairs','/sterz/','Steps connecting floors','Be careful on the stairs.','Cầu thang','basic','Home'),
('roof','/ruːf/','The top covering of a building','The roof needs repairing.','Mái nhà','basic','Home'),
('wall','/wɔːl/','A vertical surface dividing spaces','The wall is painted white.','Tường','basic','Home'),
('floor','/flɔːr/','The lower surface of a room','The floor is made of wood.','Sàn nhà','basic','Home'),

-- ===== NÂNG CAO – KINH DOANH =====
('negotiate','/nɪˈɡoʊʃieɪt/','To discuss to reach an agreement','They negotiated the contract.','Đàm phán, thương lượng','advanced','Business'),
('revenue','/ˈrevənjuː/','Income a company receives','Revenue grew by 20% this year.','Doanh thu','advanced','Business'),
('profit','/ˈprɒfɪt/','Financial gain after expenses','The profit margin is very slim.','Lợi nhuận','advanced','Business'),
('budget','/ˈbʌdʒɪt/','A plan for spending money','We need to stay within budget.','Ngân sách','advanced','Business'),
('deadline','/ˈdedlaɪn/','The latest time to complete something','The deadline is Friday.','Hạn chót','advanced','Business'),
('strategy','/ˈstrætədʒi/','A plan to achieve a goal','We need a new marketing strategy.','Chiến lược','advanced','Business'),
('stakeholder','/ˈsteɪkhoʊldər/','A person with interest in a company','Consider all stakeholders.','Bên liên quan','advanced','Business'),
('merger','/ˈmɜːrdʒər/','Combining of two companies','The merger created the largest bank.','Sự sáp nhập','advanced','Business'),
('acquisition','/ˌækwɪˈzɪʃən/','Purchase of one company by another','The tech giant announced an acquisition.','Việc mua lại','advanced','Business'),
('dividend','/ˈdɪvɪdend/','A payment to shareholders from profits','The company paid a $2 dividend.','Cổ tức','advanced','Business'),
('invoice','/ˈɪnvɔɪs/','A bill for goods or services','Please send the invoice to accounting.','Hóa đơn','advanced','Business'),
('asset','/ˈæset/','A valuable resource owned by a company','Real estate is a safe asset.','Tài sản','advanced','Business'),
('liability','/ˌlaɪəˈbɪlɪti/','Legal responsibility; debts','The company has significant liabilities.','Khoản nợ; trách nhiệm','advanced','Business'),
('portfolio','/pɔːrtˈfoʊlioʊ/','A collection of investments or work','She showed her design portfolio.','Danh mục đầu tư; hồ sơ','advanced','Business'),
('entrepreneur','/ˌɒntrəprəˈnɜːr/','A person who starts a business','She became an entrepreneur at 25.','Doanh nhân, nhà khởi nghiệp','advanced','Business'),
('franchise','/ˈfrænˌtʃaɪz/','A license to operate a business model','He owns five McDonald''s franchises.','Nhượng quyền kinh doanh','advanced','Business'),
('turnover','/ˈtɜːrnoʊvər/','Total revenue; staff replacement rate','Staff turnover is high in retail.','Doanh thu; tỷ lệ nhân viên nghỉ','advanced','Business'),
('benchmark','/ˈbentʃmɑːrk/','A standard for comparison','We use industry benchmarks.','Tiêu chuẩn tham chiếu','advanced','Business'),
('quota','/ˈkwoʊtə/','A fixed share or limit','The team exceeded their quota.','Hạn mức, chỉ tiêu','advanced','Business'),
('overhead','/ˈoʊvərhed/','Regular operating expenses','Reducing overhead improves profit.','Chi phí cố định','advanced','Business'),

-- ===== NÂNG CAO – CÔNG NGHỆ =====
('artificial intelligence','/ˌɑːrtɪˈfɪʃəl ɪnˈtelɪdʒəns/','Simulation of human intelligence by machines','AI is transforming industries.','Trí tuệ nhân tạo','advanced','Technology'),
('algorithm','/ˈælɡərɪðəm/','A set of rules for solving a problem','The search algorithm was updated.','Thuật toán','advanced','Technology'),
('database','/ˈdeɪtəbeɪs/','An organized collection of data','Customer data is in the database.','Cơ sở dữ liệu','advanced','Technology'),
('cloud computing','/klaʊd kəmˈpjuːtɪŋ/','Storing data on remote servers','We moved to cloud computing.','Điện toán đám mây','advanced','Technology'),
('cybersecurity','/ˌsaɪbərsɪˈkjʊərɪti/','Protection of computer systems','Cybersecurity is a top priority.','An ninh mạng','advanced','Technology'),
('encryption','/ɪnˈkrɪpʃən/','Converting data into coded form','End-to-end encryption protects messages.','Mã hóa dữ liệu','advanced','Technology'),
('bandwidth','/ˈbændwɪdθ/','Capacity of data transmission','We need more bandwidth.','Băng thông','advanced','Technology'),
('software','/ˈsɒftweər/','Programs used on a computer','This software is not compatible.','Phần mềm','advanced','Technology'),
('hardware','/ˈhɑːrdweər/','Physical parts of a computer','The hardware needs upgrading.','Phần cứng','advanced','Technology'),
('interface','/ˈɪntərfeɪs/','How a user interacts with software','The user interface is clean.','Giao diện','advanced','Technology'),
('developer','/dɪˈveləpər/','A person who creates software','She is a full-stack developer.','Lập trình viên','advanced','Technology'),
('debugging','/diːˈbʌɡɪŋ/','Finding and fixing errors in code','Debugging takes most of his day.','Gỡ lỗi','advanced','Technology'),
('server','/ˈsɜːrvər/','A computer providing data to others','The server was down.','Máy chủ','advanced','Technology'),
('network','/ˈnetwɜːrk/','A system of connected computers','Our office network is fast.','Mạng máy tính','advanced','Technology'),
('firewall','/ˈfaɪərwɔːl/','A security system for networks','The firewall blocked the attack.','Tường lửa','advanced','Technology'),
('automation','/ˌɔːtəˈmeɪʃən/','Using technology to do tasks automatically','Factory automation increased output.','Tự động hóa','advanced','Technology'),

-- ===== NÂNG CAO – MÔI TRƯỜNG =====
('sustainable','/səˈsteɪnəbəl/','Not harming the environment','We need sustainable practices.','Bền vững','advanced','Environment'),
('renewable','/rɪˈnjuːəbəl/','Able to be replaced naturally','Solar is a renewable energy source.','Tái tạo được','advanced','Environment'),
('carbon footprint','/ˈkɑːrbən ˈfʊtprɪnt/','Total greenhouse gases by an individual','Reduce your carbon footprint.','Dấu chân carbon','advanced','Environment'),
('ecosystem','/ˈiːkoʊsɪstəm/','A community of organisms','The forest ecosystem is complex.','Hệ sinh thái','advanced','Environment'),
('greenhouse gas','/ˈɡriːnhaʊs ɡæs/','Gas contributing to warming','CO2 is the main greenhouse gas.','Khí nhà kính','advanced','Environment'),
('deforestation','/ˌdiːˌfɒrɪˈsteɪʃən/','Clearing of forests','Deforestation in the Amazon is alarming.','Nạn phá rừng','advanced','Environment'),
('pollution','/pəˈluːʃən/','Contamination of the environment','Air pollution is serious in cities.','Ô nhiễm','advanced','Environment'),
('conservation','/ˌkɒnsəˈveɪʃən/','Protecting nature','Wildlife conservation is crucial.','Bảo tồn','advanced','Environment'),
('emissions','/ɪˈmɪʃənz/','Gases released into atmosphere','The factory must reduce emissions.','Khí thải','advanced','Environment'),
('biodiversity','/ˌbaɪoʊdaɪˈvɜːrsɪti/','Variety of life in an area','Biodiversity is declining.','Đa dạng sinh học','advanced','Environment'),
('habitat','/ˈhæbɪtæt/','Natural environment of an organism','The polar bear''s habitat is melting.','Môi trường sống','advanced','Environment'),
('recycle','/ˌriːˈsaɪkəl/','To convert waste into reusable material','Please recycle plastic bottles.','Tái chế','advanced','Environment'),
('drought','/draʊt/','Prolonged period of low rainfall','The drought destroyed the harvest.','Hạn hán','advanced','Environment'),
('erosion','/ɪˈroʊʒən/','Wearing away by wind or water','Soil erosion threatens farmland.','Xói mòn','advanced','Environment'),

-- ===== NÂNG CAO – TÂM LÝ & CẢM XÚC =====
('anxiety','/æŋˈzaɪəti/','Feeling of worry or nervousness','She has anxiety about public speaking.','Lo lắng, lo âu','advanced','Psychology'),
('resilience','/rɪˈzɪliəns/','Ability to recover from difficulties','Resilience is key for success.','Sự kiên cường','advanced','Psychology'),
('empathy','/ˈempəθi/','Understanding others'' feelings','Good leaders show empathy.','Sự đồng cảm','advanced','Psychology'),
('motivation','/ˌmoʊtɪˈveɪʃən/','The desire to do something','Intrinsic motivation lasts longer.','Động lực','advanced','Psychology'),
('self-esteem','/ˌself ɪˈstiːm/','Confidence in one''s own worth','Low self-esteem affects mental health.','Lòng tự trọng','advanced','Psychology'),
('mindfulness','/ˈmaɪndflnəs/','Awareness of the present moment','Mindfulness meditation reduces stress.','Chánh niệm','advanced','Psychology'),
('cognitive','/ˈkɒɡnɪtɪv/','Related to mental processes','Cognitive skills improve with practice.','Thuộc nhận thức','advanced','Psychology'),
('procrastinate','/prəˈkræstɪneɪt/','To delay doing something','Stop procrastinating!','Trì hoãn','advanced','Psychology'),
('burnout','/ˈbɜːrnaʊt/','Exhaustion from overwork','She suffered burnout after a year of overtime.','Kiệt sức vì làm việc quá mức','advanced','Psychology'),
('introspection','/ˌɪntrəˈspekʃən/','Examination of one''s own thoughts','Introspection helps self-awareness.','Tự suy xét nội tâm','advanced','Psychology'),

-- ===== NÂNG CAO – GIÁO DỤC =====
('curriculum','/kəˈrɪkjʊləm/','Subjects comprising a course of study','The school updated its curriculum.','Chương trình giảng dạy','advanced','Education'),
('scholarship','/ˈskɒlərʃɪp/','A grant for educational purposes','She won a full scholarship to Oxford.','Học bổng','advanced','Education'),
('dissertation','/ˌdɪsəˈteɪʃən/','A long essay for a degree','Her dissertation was on AI ethics.','Luận văn, luận án','advanced','Education'),
('semester','/sɪˈmestər/','Half of an academic year','Exams are at the end of each semester.','Học kỳ','advanced','Education'),
('prerequisite','/ˌpriːˈrekwɪzɪt/','Something required beforehand','Math is a prerequisite.','Điều kiện tiên quyết','advanced','Education'),
('tuition','/tjuːˈɪʃən/','A fee for teaching','Tuition fees have increased.','Học phí','advanced','Education'),
('internship','/ˈɪntɜːrnʃɪp/','Temporary work experience','He did an internship at Google.','Kỳ thực tập','advanced','Education'),
('graduate','/ˈɡrædʒueɪt/','To complete a university course','She graduated with honours.','Tốt nghiệp','advanced','Education'),
('literacy','/ˈlɪtərəsi/','Ability to read and write','Digital literacy is essential.','Khả năng đọc viết','advanced','Education'),
('extracurricular','/ˌekstrəkəˈrɪkjʊlər/','Outside the regular curriculum','She is active in extracurricular activities.','Ngoại khóa','advanced','Education'),

-- ===== CẤP CAO – HỌC THUẬT =====
('paradigm','/ˈpærədaɪm/','A typical model or pattern','A paradigm shift in thinking.','Mô hình tư duy','premium','Academic'),
('hypothesis','/haɪˈpɒθɪsɪs/','A proposed explanation to be tested','The scientist tested the hypothesis.','Giả thuyết','premium','Academic'),
('empirical','/ɪmˈpɪrɪkəl/','Based on observation','We need empirical evidence.','Dựa trên thực nghiệm','premium','Academic'),
('methodology','/ˌmeθəˈdɒlədʒi/','System of methods in research','The methodology was rigorous.','Phương pháp luận','premium','Academic'),
('quantitative','/ˈkwɒntɪtətɪv/','Relating to quantity','Quantitative data from surveys.','Định lượng','premium','Academic'),
('qualitative','/ˈkwɒlɪtətɪv/','Relating to quality','Qualitative research uses interviews.','Định tính','premium','Academic'),
('correlation','/ˌkɒrəˈleɪʃən/','Relationship between variables','A strong correlation was found.','Tương quan','premium','Academic'),
('causation','/kɔːˈzeɪʃən/','Cause and effect relationship','Correlation does not imply causation.','Quan hệ nhân quả','premium','Academic'),
('inference','/ˈɪnfərəns/','A conclusion reached by reasoning','The inference was based on limited data.','Suy luận, kết luận','premium','Academic'),
('synthesis','/ˈsɪnθəsɪs/','Combining elements into a new whole','The essay synthesizes three perspectives.','Tổng hợp','premium','Academic'),
('rationale','/ˌræʃəˈnæl/','The reasons for an action','Explain the rationale for your decision.','Lý do, cơ sở lý luận','premium','Academic'),
('inherent','/ɪnˈhɪərənt/','Existing naturally as a quality','There are inherent risks.','Vốn có, cố hữu','premium','Academic'),
('ambiguous','/æmˈbɪɡjuəs/','Open to more than one interpretation','The instructions were ambiguous.','Mơ hồ','premium','Academic'),
('coherent','/koʊˈhɪərənt/','Logical and consistent','Give a coherent explanation.','Mạch lạc, nhất quán','premium','Academic'),
('contradict','/ˌkɒntrəˈdɪkt/','To deny or oppose','His actions contradict his words.','Mâu thuẫn, bác bỏ','premium','Academic'),

-- ===== CẤP CAO – IELTS =====
('alleviate','/əˈliːvieɪt/','To make suffering less severe','Exercise can alleviate stress.','Giảm nhẹ, làm dịu','premium','IELTS'),
('exacerbate','/ɪɡˈzæsərbeɪt/','To make a problem worse','The drought exacerbated food shortages.','Làm trầm trọng thêm','premium','IELTS'),
('proliferation','/prəˌlɪfəˈreɪʃən/','A rapid increase in number','The proliferation of smartphones changed society.','Sự phát triển mạnh mẽ','premium','IELTS'),
('predominantly','/prɪˈdɒmɪnəntli/','Mainly, for the most part','The population is predominantly young.','Chủ yếu, phần lớn','premium','IELTS'),
('detrimental','/ˌdetrɪˈmentəl/','Causing harm or damage','Smoking is detrimental to health.','Có hại, gây tổn hại','premium','IELTS'),
('inevitable','/ɪnˈevɪtəbəl/','Certain to happen','Change is inevitable.','Không thể tránh khỏi','premium','IELTS'),
('controversial','/ˌkɒntrəˈvɜːrʃəl/','Causing disagreement','Capital punishment is controversial.','Gây tranh cãi','premium','IELTS'),
('facilitate','/fəˈsɪlɪteɪt/','To make something easier','Technology facilitates communication.','Tạo điều kiện, thúc đẩy','premium','IELTS'),
('accumulate','/əˈkjuːmjʊleɪt/','To gather together over time','He accumulated wealth over 30 years.','Tích lũy','premium','IELTS'),
('deteriorate','/dɪˈtɪərɪəreɪt/','To become progressively worse','His health deteriorated rapidly.','Trở nên tồi tệ hơn','premium','IELTS'),
('substantial','/səbˈstænʃəl/','Of considerable importance','There is substantial evidence.','Đáng kể, quan trọng','premium','IELTS'),
('implement','/ˈɪmplɪment/','To put a plan into action','The policy will be implemented next year.','Thực hiện, triển khai','premium','IELTS'),
('consequence','/ˈkɒnsɪkwəns/','A result or effect','Consider the consequences.','Hậu quả, kết quả','premium','IELTS'),
('advocate','/ˈædvəkeɪt/','To publicly support a cause','She advocates for gender equality.','Ủng hộ, vận động','premium','IELTS'),
('autonomy','/ɔːˈtɒnəmi/','Independence, self-governance','Schools need more autonomy.','Quyền tự chủ','premium','IELTS'),
('infrastructure','/ˈɪnfrəstrʌktʃər/','Basic systems of a country','The country needs better infrastructure.','Cơ sở hạ tầng','premium','IELTS'),
('urbanization','/ˌɜːrbənɪˈzeɪʃən/','Process of becoming more like a city','Rapid urbanization is changing rural life.','Đô thị hóa','premium','IELTS'),
('globalization','/ˌɡloʊbəlɪˈzeɪʃən/','Process of global integration','Globalization has pros and cons.','Toàn cầu hóa','premium','IELTS'),
('disparity','/dɪˈspærɪti/','A great difference','There is a huge income disparity.','Sự chênh lệch','premium','IELTS'),
('mitigate','/ˈmɪtɪɡeɪt/','To make less severe','Measures to mitigate climate change.','Giảm thiểu','premium','IELTS'),
('rhetoric','/ˈretərɪk/','Persuasive language','Politicians use empty rhetoric.','Tu từ, lời lẽ hoa mỹ','premium','IELTS'),
('pervasive','/pərˈveɪsɪv/','Spreading widely','Social media is pervasive.','Lan rộng, tràn lan','premium','IELTS'),
('phenomenon','/fɪˈnɒmɪnən/','A fact or event that is observable','Climate change is a global phenomenon.','Hiện tượng','premium','IELTS'),
('segregation','/ˌseɡrɪˈɡeɪʃən/','The separation of different groups','Racial segregation was abolished.','Sự phân biệt, tách biệt','premium','IELTS'),
('marginalize','/ˈmɑːrdʒɪnəlaɪz/','To treat as unimportant','Immigrants are often marginalized.','Gạt ra ngoài lề','premium','IELTS'),

-- ===== CẤP CAO – PHÁP LÝ & CHÍNH TRỊ =====
('legislation','/ˌledʒɪsˈleɪʃən/','Laws enacted by government','New legislation was passed.','Pháp luật, lập pháp','premium','Law & Politics'),
('constitution','/ˌkɒnstɪˈtjuːʃən/','Fundamental principles of a state','The right to free speech is in the constitution.','Hiến pháp','premium','Law & Politics'),
('jurisdiction','/ˌdʒʊərɪsˈdɪkʃən/','The authority to apply laws','This falls outside our jurisdiction.','Thẩm quyền','premium','Law & Politics'),
('sovereignty','/ˈsɒvrənti/','Authority of a state to govern itself','The country asserted its sovereignty.','Chủ quyền','premium','Law & Politics'),
('sanction','/ˈsæŋkʃən/','A penalty for breaking rules','Economic sanctions were imposed.','Trừng phạt, chế tài','premium','Law & Politics'),
('treaty','/ˈtriːti/','A formal agreement between countries','They signed a trade treaty.','Hiệp ước, hiệp định','premium','Law & Politics'),
('amendment','/əˈmendmənt/','A change made to a law','The first amendment protects free speech.','Sửa đổi, điều chỉnh','premium','Law & Politics'),
('veto','/ˈviːtoʊ/','The power to reject a decision','The president used the veto.','Quyền phủ quyết','premium','Law & Politics'),

-- ===== CẤP CAO – Y KHOA =====
('diagnosis','/ˌdaɪəɡˈnoʊsɪs/','Identification of a disease','The diagnosis was confirmed.','Chẩn đoán bệnh','premium','Medical'),
('prognosis','/prɒɡˈnoʊsɪs/','The likely outcome of a disease','The prognosis for recovery is good.','Tiên lượng bệnh','premium','Medical'),
('symptom','/ˈsɪmptəm/','A sign of a disease','Fever is a common symptom.','Triệu chứng','premium','Medical'),
('chronic','/ˈkrɒnɪk/','Persisting for a long time','He has chronic back pain.','Mãn tính, kinh niên','premium','Medical'),
('acute','/əˈkjuːt/','Severe and sudden in onset','She was admitted for acute appendicitis.','Cấp tính, nghiêm trọng','premium','Medical'),
('benign','/bɪˈnaɪn/','Not harmful; not cancerous','The tumor was benign.','Lành tính','premium','Medical'),
('malignant','/məˈlɪɡnənt/','Dangerous; cancerous','A malignant tumor was found.','Ác tính','premium','Medical'),
('immunity','/ɪˈmjuːnɪti/','The body''s resistance to disease','Vaccines build immunity.','Miễn dịch','premium','Medical'),
('pandemic','/pænˈdemɪk/','An outbreak across a wide area','COVID-19 was a global pandemic.','Đại dịch','premium','Medical'),
('pathogen','/ˈpæθədʒən/','A disease-causing microorganism','Bacteria can be pathogens.','Mầm bệnh','premium','Medical'),

-- ===== CẤP CAO – TRIẾT HỌC =====
('ideology','/ˌaɪdiˈɒlədʒi/','A system of ideas and beliefs','The party''s ideology is unclear.','Hệ tư tưởng','premium','Philosophy'),
('ethics','/ˈeθɪks/','Moral principles governing behavior','Business ethics are essential.','Đạo đức','premium','Philosophy'),
('pragmatic','/præɡˈmætɪk/','Dealing with things practically','Take a pragmatic approach.','Thực dụng, thực tế','premium','Philosophy'),
('altruism','/ˈæltruɪzəm/','Concern for others'' wellbeing','Donating anonymously is pure altruism.','Lòng vị tha','premium','Philosophy'),
('paradox','/ˈpærədɒks/','A seemingly contradictory statement','It is a paradox that knowing more makes you realize you know less.','Nghịch lý','premium','Philosophy'),
('utopia','/juːˈtoʊpiə/','An imagined perfect society','His plan sounds like a utopia.','Xã hội lý tưởng','premium','Philosophy'),
('dogma','/ˈdɒɡmə/','A principle held as undisputable','The church defended its dogma.','Giáo điều','premium','Philosophy'),
('egalitarian','/ɪˌɡælɪˈteəriən/','Believing in equal rights','He holds egalitarian views.','Theo chủ nghĩa bình đẳng','premium','Philosophy'),
('hegemony','/hɪˈdʒemənɪ/','Dominance of one group','US hegemony shaped the 20th century.','Bá quyền','premium','Philosophy'),

-- ===== CẤP CAO – VĂN HỌC =====
('metaphor','/ˈmetəfər/','Comparing unlike things','Time is money is a metaphor.','Ẩn dụ','premium','Literature'),
('irony','/ˈaɪrəni/','Saying the opposite of what you mean','It''s ironic that a fire station burned down.','Sự mỉa mai','premium','Literature'),
('satire','/ˈsætaɪər/','Using humour to criticise','The novel is a satire on politics.','Châm biếm','premium','Literature'),
('narrative','/ˈnærətɪv/','An account of events','The narrative is in first person.','Cốt truyện, tường thuật','premium','Literature'),
('protagonist','/prəˈtæɡənɪst/','The main character','Hamlet is the protagonist.','Nhân vật chính','premium','Literature'),
('antagonist','/ænˈtæɡənɪst/','A character opposing the protagonist','The villain is the antagonist.','Nhân vật phản diện','premium','Literature'),
('allegory','/ˈæləɡɒri/','A story with a hidden meaning','Animal Farm is an allegory.','Ẩn dụ, ngụ ngôn','premium','Literature'),
('foreshadowing','/fɔːˈʃædoʊɪŋ/','Hints about what will happen later','The dark clouds were foreshadowing.','Điềm báo trước','premium','Literature');

-- Bật lại kiểm tra khoá ngoại
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- KIỂM TRA KẾT QUẢ
-- ============================================================
SELECT 'Tổng bài học:' label, COUNT(*) total FROM lessons
UNION ALL SELECT 'Tổng từ vựng:', COUNT(*) FROM vocabulary
UNION ALL SELECT 'Tổng bài tập:', COUNT(*) FROM exercises
UNION ALL SELECT 'Tổng người dùng:', COUNT(*) FROM users;
