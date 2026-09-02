<?php
$page_title = 'สมัครสมาชิก';
require_once __DIR__ . '/includes/header.php';

// ล็อกอินอยู่แล้วไม่ต้องสมัครซ้ำ
if (is_login()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$f   = flash();
$old = $_SESSION['old'] ?? [];   // ค่าที่กรอกไว้เดิม (กันกรอกใหม่ทั้งหมด)
unset($_SESSION['old']);
?>

<section class="section auth-section">
  <div class="auth-box">
    <h2 class="section-title">📝 สมัครสมาชิก</h2>
    <p class="auth-sub">สมัครฟรี รับ <strong>50 XP</strong> ทันทีเมื่อเปิดบัญชี</p>

    <?php if ($f): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/register_process.php" id="regForm">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

      <label>ชื่อ - นามสกุล</label>
      <input type="text" name="fullname" maxlength="100" required
             value="<?= e($old['fullname'] ?? '') ?>" placeholder="เช่น สมชาย ใจดี">

      <label>อีเมล</label>
      <input type="email" name="email" maxlength="100" required
             value="<?= e($old['email'] ?? '') ?>" placeholder="you@example.com">

      <label>รหัสผ่าน</label>
      <input type="password" name="password" id="pw" minlength="6" required
             placeholder="อย่างน้อย 6 ตัวอักษร">
      <div class="pw-meter"><div class="pw-bar" id="pwBar"></div></div>
      <small class="hint" id="pwTxt">ความปลอดภัย: -</small>

      <label>ยืนยันรหัสผ่าน</label>
      <input type="password" name="password_confirm" id="pw2" required
             placeholder="พิมพ์รหัสผ่านอีกครั้ง">
      <small class="hint" id="matchTxt"></small>

      <label class="check">
        <input type="checkbox" name="accept" value="1" required>
        <span>ยอมรับเงื่อนไขการเช่าและนโยบายความเป็นส่วนตัว</span>
      </label>

      <button type="submit" class="btn btn-primary btn-block btn-lg">สร้างบัญชี</button>
    </form>

    <p class="auth-foot">มีบัญชีอยู่แล้ว?
      <a href="<?= BASE_URL ?>/login.php">เข้าสู่ระบบ</a>
    </p>
  </div>
</section>

<script>
const pw = document.getElementById('pw'), pw2 = document.getElementById('pw2');

pw.addEventListener('input', () => {
  const v = pw.value;
  let s = 0;
  if (v.length >= 6)         s++;
  if (v.length >= 10)        s++;
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
  if (/[0-9]/.test(v))       s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;

  const lv   = ['-','อ่อนมาก','อ่อน','ปานกลาง','ดี','แข็งแรงมาก'][s];
  const col  = ['#8f98a0','#e74c3c','#e67e22','#f1c40f','#a4d007','#66c0f4'][s];
  const bar  = document.getElementById('pwBar');
  bar.style.width      = (s * 20) + '%';
  bar.style.background = col;
  document.getElementById('pwTxt').textContent = 'ความปลอดภัย: ' + lv;
});

pw2.addEventListener('input', () => {
  const t = document.getElementById('matchTxt');
  if (!pw2.value) { t.textContent = ''; return; }
  const ok = pw.value === pw2.value;
  t.textContent = ok ? '✓ รหัสผ่านตรงกัน' : '✗ รหัสผ่านไม่ตรงกัน';
  t.style.color = ok ? '#a4d007' : '#ff7b7b';
});

document.getElementById('regForm').addEventListener('submit', e => {
  if (pw.value !== pw2.value) { e.preventDefault(); alert('รหัสผ่านทั้งสองช่องไม่ตรงกัน'); }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>