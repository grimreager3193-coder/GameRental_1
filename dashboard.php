<?php
$page_title = 'แผงควบคุมผู้ดูแลระบบ';
require_once __DIR__ . '/../includes/header.php';

// ---- ต้อง login และเป็น admin เท่านั้น ----
if (!is_login()) {
    header('Location: ' . BASE_URL . '/login.php'); exit;
}
if (!is_admin()) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

// ---- สรุปตัวเลขภาพรวม ----
$total_games   = (int)$pdo->query("SELECT COUNT(*) c FROM games")->fetch()['c'];
$total_users   = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role = 'user'")->fetch()['c'];
$active_rent   = (int)$pdo->query("SELECT COUNT(*) c FROM rentals WHERE status = 'renting'")->fetch()['c'];
$overdue_rent  = (int)$pdo->query("
    SELECT COUNT(*) c FROM rentals
    WHERE status = 'renting' AND expected_return_date < CURDATE()
")->fetch()['c'];

// ---- รายการเช่าล่าสุด 10 รายการ ----
$latest = $pdo->query("
    SELECT r.rental_id, r.rental_date, r.expected_return_date, r.status, r.total_price,
           u.fullname
    FROM rentals r
    JOIN users u ON u.user_id = r.user_id
    ORDER BY r.rental_id DESC
    LIMIT 10
")->fetchAll();

$label = ['pending'=>'รอยืนยัน','renting'=>'กำลังเช่า','returned'=>'คืนแล้ว','overdue'=>'เกินกำหนด'];
?>

<style>
  /* สำรองไว้เผื่อ style.css ยังไม่มี class เหล่านี้ */
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin:1rem 0}
  .stat-card{text-align:center;padding:1.2rem}
  .stat-num{font-size:2rem;font-weight:700}
  .stat-label{opacity:.75;font-size:.9rem;margin-top:.25rem}
</style>

<section class="section">
  <h2 class="section-title">🛠️ แผงควบคุมผู้ดูแลระบบ</h2>

  <div class="stat-grid">
    <div class="card stat-card">
      <div class="stat-num"><?= $total_games ?></div>
      <div class="stat-label">เกมทั้งหมด</div>
    </div>
    <div class="card stat-card">
      <div class="stat-num"><?= $total_users ?></div>
      <div class="stat-label">สมาชิกทั้งหมด</div>
    </div>
    <div class="card stat-card">
      <div class="stat-num"><?= $active_rent ?></div>
      <div class="stat-label">กำลังเช่าอยู่</div>
    </div>
    <div class="card stat-card">
      <div class="stat-num" style="color:#ff7b7b"><?= $overdue_rent ?></div>
      <div class="stat-label">เกินกำหนดคืน</div>
    </div>
  </div>

  <h3 class="section-title" style="margin-top:2rem">📋 รายการเช่าล่าสุด</h3>
  <?php if (!$latest): ?>
    <div class="empty">ยังไม่มีรายการเช่าในระบบ</div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="tbl">
      <thead><tr>
        <th>เลขที่</th><th>ผู้เช่า</th><th>วันที่เช่า</th><th>กำหนดคืน</th><th>ยอดรวม</th><th>สถานะ</th>
      </tr></thead>
      <tbody>
      <?php foreach ($latest as $r):
        $late = ($r['status']==='renting' && strtotime($r['expected_return_date']) < strtotime(date('Y-m-d')));
        $stt  = $late ? 'overdue' : $r['status']; ?>
        <tr>
          <td>#<?= (int)$r['rental_id'] ?></td>
          <td><?= e($r['fullname']) ?></td>
          <td><?= date('d/m/Y', strtotime($r['rental_date'])) ?></td>
          <td><?= date('d/m/Y', strtotime($r['expected_return_date'])) ?></td>
          <td class="price"><?= money($r['total_price']) ?> ฿</td>
          <td><span class="badge-<?= $stt ?>"><?= $label[$stt] ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
