<?php
$page_title = 'การเช่าของฉัน';
require_once __DIR__ . '/includes/header.php';
if (!is_login()) { header('Location: '.BASE_URL.'/login.php'); exit; }

$st = $pdo->prepare("
    SELECT r.rental_id, r.rental_date, r.expected_return_date, r.actual_return_date,
           r.total_price, r.late_fine, r.status,
           GROUP_CONCAT(g.title SEPARATOR ', ') AS games,
           SUM(rd.days_rented) AS days
    FROM rentals r
    JOIN rental_details rd ON rd.rental_id = r.rental_id
    JOIN games g           ON g.game_id    = rd.game_id
    WHERE r.user_id = :uid
    GROUP BY r.rental_id
    ORDER BY r.rental_date DESC
");
$st->execute([':uid' => (int)$_SESSION['user_id']]);
$rows = $st->fetchAll();

$label = ['pending'=>'รอยืนยัน','renting'=>'กำลังเช่า','returned'=>'คืนแล้ว','overdue'=>'เกินกำหนด'];
$f = flash();
?>
<section class="section">
  <h2 class="section-title">📦 การเช่าของฉัน</h2>
  <?php if ($f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>

  <?php if (!$rows): ?>
    <div class="empty">ยังไม่มีประวัติการเช่า — <a href="<?= BASE_URL ?>/index.php" style="color:var(--blue)">เลือกเกมเลย</a></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="tbl">
      <thead><tr>
        <th>เลขที่</th><th>เกม</th><th>วันที่เช่า</th><th>กำหนดคืน</th>
        <th>จำนวนวัน</th><th>ยอดรวม</th><th>ค่าปรับ</th><th>สถานะ</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r):
        $late = ($r['status']==='renting' && strtotime($r['expected_return_date']) < strtotime(date('Y-m-d')));
        $stt  = $late ? 'overdue' : $r['status']; ?>
        <tr>
          <td>#<?= (int)$r['rental_id'] ?></td>
          <td><?= e($r['games']) ?></td>
          <td><?= date('d/m/Y', strtotime($r['rental_date'])) ?></td>
          <td><?= date('d/m/Y', strtotime($r['expected_return_date'])) ?></td>
          <td><?= (int)$r['days'] ?> วัน</td>
          <td class="price"><?= money($r['total_price']) ?> ฿</td>
          <td><?= $r['late_fine'] > 0 ? '<span style="color:#ff7b7b">'.money($r['late_fine']).' ฿</span>' : '-' ?></td>
          <td><span class="badge-<?= $stt ?>"><?= $label[$stt] ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>