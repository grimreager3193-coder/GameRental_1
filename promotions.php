<?php
$page_title = 'โปรโมชัน';
require_once __DIR__ . '/includes/header.php';

// ---- โปรโมชันที่ยังไม่หมดอายุ ----
$promos = $pdo->query("SELECT title, body
                       FROM contents
                       WHERE status = 'active'
                         AND NOW() BETWEEN start_date AND end_date
                       ORDER BY start_date DESC")->fetchAll();
?>

<style>
  /* สำรองไว้เผื่อ style.css ยังไม่มี class เหล่านี้ */
  .poster-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.2rem;margin-top:1rem}
  .poster-card{border-radius:10px;overflow:hidden;background:var(--panel,#1b2838)}
  .poster-img{width:100%;aspect-ratio:3/4;background-size:cover;background-position:center;
              display:flex;align-items:flex-end;padding:1rem}
  .poster-img h3{color:#fff;margin:0;text-shadow:0 2px 6px rgba(0,0,0,.8)}
  .poster-body{padding:.9rem 1rem}
</style>

<section class="section">
  <h2 class="section-title">🔥 โปรโมชัน</h2>

  <?php if ($promos): ?>
    <div class="poster-grid">
      <?php foreach ($promos as $p):
        $img = 'https://placehold.co/400x530/2a475e/66c0f4?text=' . urlencode(mb_substr($p['title'], 0, 16)); ?>
        <div class="poster-card">
          <div class="poster-img" style="background-image:url('<?= $img ?>')">
            <h3><?= e($p['title']) ?></h3>
          </div>
          <div class="poster-body">
            <p><?= nl2br(e($p['body'])) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <!-- ยังไม่มีโปรโมชันจริงในระบบ วางโปสเตอร์ตัวอย่างไว้ก่อน -->
    <div class="poster-grid">
      <?php
      $placeholders = [
        ['ลด 20% ทุกเกม PS5', 'เฉพาะสมาชิกใหม่ วันนี้ - สิ้นเดือน'],
        ['เช่า 5 วัน แถม 1 วันฟรี', 'สำหรับเกม Nintendo Switch ทุกเรื่อง'],
        ['สมัครวันนี้ รับ 50 XP ทันที', 'แลกส่วนลดค่าเช่าได้ทันที'],
      ];
      foreach ($placeholders as $ph):
        $img = 'https://placehold.co/400x530/2a475e/66c0f4?text=' . urlencode($ph[0]);
      ?>
        <div class="poster-card">
          <div class="poster-img" style="background-image:url('<?= $img ?>')">
            <h3><?= e($ph[0]) ?></h3>
          </div>
          <div class="poster-body">
            <p><?= e($ph[1]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="hint" style="margin-top:1rem">* ตัวอย่างโปสเตอร์ชั่วคราว รอเพิ่มข้อมูลจริงในตาราง <code>contents</code></p>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
