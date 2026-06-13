<?php // includes/footer.php – TemplateMo style ?>

</main><!-- /main -->

<!-- ── SITE FOOTER ── -->
<footer class="site-footer section-padding">
  <div class="container">
    <div class="row">

      <!-- Brand -->
      <div class="col-lg-3 col-12 mb-4">
        <a class="navbar-brand text-decoration-none d-flex align-items-center gap-2 mb-3" href="<?= BASE_URL ?>">
          <i class="bi bi-mortarboard-fill fs-4" style="color:#80d0c7"></i>
          <span class="fw-bold fs-5" style="font-family:'Montserrat',sans-serif;color:#13547a"><?= SITE_NAME ?></span>
        </a>
        <p style="font-size:.9rem">Nền tảng học tiếng Anh thông minh tích hợp AI. Học mọi lúc, mọi nơi.</p>
        <!-- Social -->
        <ul class="social-icon mt-3">
          <li class="social-icon-item"><a href="#" class="social-icon-link bi-facebook"></a></li>
          <li class="social-icon-item"><a href="#" class="social-icon-link bi-youtube"></a></li>
          <li class="social-icon-item"><a href="#" class="social-icon-link bi-twitter"></a></li>
        </ul>
      </div>

      <!-- Links -->
      <div class="col-lg-2 col-md-4 col-6">
        <h6 class="site-footer-title mb-3">Học tập</h6>
        <ul class="site-footer-links">
          <li class="site-footer-link-item"><a href="<?= BASE_URL ?>/bai-hoc"   class="site-footer-link">Bài học</a></li>
          <li class="site-footer-link-item"><a href="<?= BASE_URL ?>/tu-vung" class="site-footer-link">Từ vựng</a></li>
          <li class="site-footer-link-item"><a href="<?= BASE_URL ?>/chat-ai"   class="site-footer-link">Chat AI</a></li>
        </ul>
      </div>

      <!-- Gói học -->
      <div class="col-lg-2 col-md-4 col-6">
        <h6 class="site-footer-title mb-3">Gói học</h6>
        <ul class="site-footer-links">
          <li class="site-footer-link-item"><span class="site-footer-link">🟢 Cơ bản – Miễn phí</span></li>
          <li class="site-footer-link-item"><a href="<?= BASE_URL ?>/nang-cap" class="site-footer-link">🟡 Nâng cao</a></li>
          <li class="site-footer-link-item"><a href="<?= BASE_URL ?>/nang-cap" class="site-footer-link">🔴 Cấp cao</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div class="col-lg-3 col-md-4 col-12 ms-auto mt-4 mt-lg-0">
        <h6 class="site-footer-title mb-3">Liên hệ</h6>
        <p class="d-flex align-items-center mb-2">
          <i class="bi bi-envelope me-2" style="color:#80d0c7"></i>
          <a href="mailto:support@englishai.com" class="site-footer-link">support@englishai.com</a>
        </p>
        <p class="d-flex align-items-center">
          <i class="bi bi-geo-alt me-2" style="color:#80d0c7"></i>
          <span style="font-size:.9rem;color:#717275">Ho Chi Minh City, Vietnam</span>
        </p>
        <p class="copyright-text mt-4">
          &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.<br>
          <small>Powered by PHP + MySQL + AI</small>
        </p>
      </div>

    </div>
  </div>
</footer>

<!-- JAVASCRIPT -->
<script src="<?= BASE_URL ?>/public/assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/js/jquery.sticky.js"></script>
<script src="<?= BASE_URL ?>/public/assets/js/click-scroll.js"></script>
<script src="<?= BASE_URL ?>/public/assets/js/custom.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
