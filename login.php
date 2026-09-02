<?php
$page_title = 'เข้าสู่ระบบ';
require_once __DIR__ . '/includes/header.php';

// ล็อกอินอยู่แล้วไม่ต้องเข้าอีก
if (is_login()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

if (isset($_SESSION['error'])) {
    $login_error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<section class="section auth-section">
  <div class="auth-box">
    <h2 class="section-title">🔐 เข้าสู่ระบบ</h2>

    <?php if (!empty($login_error)): ?>
      <div class="alert alert-error"><?= e($login_error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/login_process.php">
      <label>อีเมล</label>
      <input type="email" name="email" required placeholder="you@example.com">

      <label>รหัสผ่าน</label>
      <input type="password" name="password" required placeholder="รหัสผ่านของคุณ">

      <button type="submit" class="btn btn-primary btn-block btn-lg">เข้าสู่ระบบ</button>
    </form>

    <p class="auth-foot">ยังไม่มีบัญชี?
      <a href="<?= BASE_URL ?>/register.php">สมัครสมาชิก</a>
    </p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>