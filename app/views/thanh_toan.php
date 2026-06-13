<?php
global $conn;
// $plan, $info, $vietqrUrl, $transferContent được truyền từ controller
?>
<?php include ROOT.'/app/views/layout/header.php'; ?>

<style>
    /* Đặt sau header để ghi đè Bootstrap */
    .tt-wrap {
        min-height: 80vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 16px;
        background: linear-gradient(135deg, #0f172a, #1e3a8a, #2563eb);
    }

    .tt-card {
        width: 100%;
        max-width: 460px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(18px);
        border-radius: 25px;
        padding: 35px;
        color: white;
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    }

    .tt-logo {
        width: 85px; height: 85px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex; justify-content: center; align-items: center;
        font-size: 38px;
    }

    .tt-card h1 { text-align:center; font-size: 22px; margin-bottom: 6px; }

    .tt-desc { text-align:center; color:#dbeafe; margin-bottom: 24px; font-size: 14px; }

    .tt-input-group { margin-bottom: 16px; }
    .tt-input-group label { display:block; margin-bottom: 7px; font-size: 14px; }
    .tt-input-group input {
        width:100%; padding:13px 14px;
        border:none; border-radius:12px;
        outline:none; font-size:15px;
        color: #1e293b;
    }

    .tt-price-box {
        margin: 18px 0;
        padding: 16px;
        border-radius: 15px;
        background: rgba(255,255,255,0.12);
        text-align: center;
    }
    .tt-price-box .gia { font-size: 36px; font-weight: bold; color: #93c5fd; }
    .tt-price-box small { color: #dbeafe; }

    .tt-qr { text-align:center; margin-top: 18px; }
    .tt-qr p { margin-bottom: 10px; font-size: 14px; color: #dbeafe; }
    .tt-qr img {
        width: 220px; max-width:100%;
        background: white; padding: 10px;
        border-radius: 18px;
    }

    .tt-bank {
        margin-top: 18px;
        background: rgba(255,255,255,0.1);
        padding: 14px 16px;
        border-radius: 14px;
        line-height: 2;
        font-size: 14px;
        color: #ffffff !important;
    }
    .tt-bank p,
    .tt-bank strong {
        color: #ffffff !important;
    }
    .tt-bank .noi-dung {
        background: rgba(255,255,255,0.15);
        padding: 2px 10px;
        border-radius: 8px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .tt-btn {
        width: 100%;
        margin-top: 22px;
        padding: 14px;
        border: none;
        border-radius: 14px;
        background: #2563eb;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
    }
    .tt-btn:hover { background: #1d4ed8; }

    .tt-back {
        display: block;
        text-align: center;
        margin-top: 14px;
        color: #bfdbfe;
        font-size: 14px;
        text-decoration: none;
    }
    .tt-back:hover { color: white; }

    /* ── Modal thành công ── */
    .tt-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(6px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .tt-modal-overlay.show {
        display: flex;
        animation: fadeIn 0.3s ease;
    }
    .tt-modal-box {
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        border-radius: 28px;
        padding: 45px 35px;
        text-align: center;
        color: white;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        animation: slideUp 0.4s cubic-bezier(0.34,1.56,0.64,1);
    }
    .tt-modal-icon {
        width: 100px; height: 100px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        display: flex; justify-content: center; align-items: center;
        font-size: 50px;
        box-shadow: 0 0 30px rgba(34,197,94,0.5);
        animation: pulse 1.5s infinite;
    }
    .tt-modal-box h2 { font-size: 22px; margin-bottom: 10px; }
    .tt-modal-box p  { color: #dbeafe; font-size: 14px; margin-bottom: 6px; }
    .tt-modal-actions { margin-top: 28px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .tt-modal-btn {
        padding: 12px 22px;
        border-radius: 12px;
        font-weight: bold;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: opacity 0.2s;
    }
    .tt-modal-btn:hover { opacity: 0.85; }
    .tt-modal-btn-primary { background: white; color: #1e3a8a; }
    .tt-modal-btn-secondary { background: rgba(255,255,255,0.15); color: white; }

    @keyframes fadeIn  { from { opacity:0 } to { opacity:1 } }
    @keyframes slideUp { from { transform:translateY(60px); opacity:0 } to { transform:translateY(0); opacity:1 } }
    @keyframes pulse   { 0%,100% { box-shadow:0 0 30px rgba(34,197,94,0.5) } 50% { box-shadow:0 0 50px rgba(34,197,94,0.8) } }
</style>

<div class="tt-wrap">
    <div class="tt-card">

        <div class="tt-logo">
            <i class="bi bi-crown-fill text-warning"></i>
        </div>

        <h1>Thanh toán Premium</h1>
        <p class="tt-desc">Mở khóa toàn bộ bài học và tính năng AI nâng cao</p>

        <form method="POST" action="<?= BASE_URL ?>/thanh-toan/xac-nhan">
            <input type="hidden" name="goi" value="<?= htmlspecialchars($plan) ?>">

            <div class="tt-input-group">
                <label>Họ và tên</label>
                <input type="text" name="ho_ten" placeholder="Nhập họ và tên đầy đủ" required>
            </div>

            <div class="tt-input-group">
                <label>Địa chỉ Email</label>
                <input type="email" name="email" placeholder="example@gmail.com" required>
            </div>

            <div class="tt-input-group">
                <label>Số điện thoại</label>
                <input type="text" name="so_dien_thoai" placeholder="0123456789" required
                       pattern="[0-9]{9,11}" title="Nhập số điện thoại hợp lệ">
            </div>

            <div class="tt-price-box">
                <p class="mb-1">Gói <strong><?= htmlspecialchars($info['name']) ?></strong></p>
                <div class="gia"><?= htmlspecialchars($info['price']) ?></div>
                <small>mỗi tháng &middot; hủy bất kỳ lúc nào</small>
            </div>

            <div class="tt-qr">
                <p><i class="bi bi-qr-code me-1"></i> Quét mã QR để chuyển khoản</p>
                <img src="<?= htmlspecialchars($vietqrUrl) ?>" alt="Mã QR VietQR MB Bank">
            </div>

            <div class="tt-bank" style="color:white">
                <p><strong>Ngân hàng:</strong> MB Bank</p>
                <p><strong>Chủ tài khoản:</strong> ENGLISH AI</p>
                <p><strong>Số tài khoản:</strong> 123456789</p>
                <p><strong>Số tiền:</strong> <?= htmlspecialchars($info['price']) ?></p>
                <p><strong>Nội dung CK:</strong>
                    <span class="noi-dung"><?= htmlspecialchars($transferContent) ?></span>
                </p>
            </div>

            <button type="submit" class="tt-btn">
                <i class="bi bi-lock-fill me-1"></i> Xác nhận đã chuyển khoản
            </button>
        </form>

        <a href="<?= BASE_URL ?>/nang-cap" class="tt-back">
            <i class="bi bi-arrow-left me-1"></i> Quay lại chọn gói
        </a>

    </div>
</div>

<!-- Modal thành công -->
<div class="tt-modal-overlay" id="successModal">
    <div class="tt-modal-box">
        <div class="tt-modal-icon">✓</div>
        <h2>Thanh toán thành công! 🎉</h2>
        <p>Chúc mừng! Gói <strong id="tenGoi"></strong> đã được kích hoạt.</p>
        <p>Bạn có thể sử dụng toàn bộ tính năng ngay bây giờ.</p>
        <div class="tt-modal-actions">
            <a href="<?= BASE_URL ?>/bang-dieu-khien" class="tt-modal-btn tt-modal-btn-primary">
                🎓 Vào học ngay
            </a>
            <a href="<?= BASE_URL ?>" class="tt-modal-btn tt-modal-btn-secondary">
                🏠 Trang chủ
            </a>
        </div>
    </div>
</div>

<script>
document.querySelector('form[action*="xac-nhan"]').addEventListener('submit', function(e) {
    e.preventDefault();
    const form   = this;
    const goi    = form.querySelector('[name="goi"]').value;
    const tenGoi = goi === 'premium' ? 'Cấp cao' : 'Nâng cao';

    // Gửi form ngầm
    fetch(form.action, { method: 'POST', body: new FormData(form) });

    // Hiện modal
    document.getElementById('tenGoi').textContent = tenGoi;
    document.getElementById('successModal').classList.add('show');
});
</script>

<?php include ROOT.'/app/views/layout/footer.php'; ?>
