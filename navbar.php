<header class="topbar">
  <div class="topbar-inner">
    <a href="<?= BASE_URL ?>/index.php" class="logo">🎮 GAME<span>RENTAL</span></a>

    <nav class="menu">
      <a href="<?= BASE_URL ?>/index.php">หน้าแรก</a>
      <a href="<?= BASE_URL ?>/games.php">คลังเกม</a>
      <a href="<?= BASE_URL ?>/promotions.php">โปรโมชัน</a>
      <?php if (is_login()): ?>
        <a href="<?= BASE_URL ?>/my_rentals.php">การเช่าของฉัน</a>
      <?php endif; ?>
    </nav>

    <div class="user-area">
      <?php if (is_login()): ?>
        <span class="hello">สวัสดี, <?= e($_SESSION['fullname'] ?? 'ผู้ใช้') ?></span>
        <?php if (is_admin()): ?>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/dashboard.php">จัดการระบบ</a>
        <?php endif; ?>
        <a class="btn btn-danger" href="<?= BASE_URL ?>/logout.php">ออกจากระบบ</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="<?= BASE_URL ?>/login.php">เข้าสู่ระบบ</a>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/register.php">สมัครสมาชิก</a>
      <?php endif; ?>
    </div>
  </div>
</header>