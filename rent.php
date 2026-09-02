<?php
$page_title = 'ทำรายการเช่าเกม';
require_once __DIR__ . '/includes/header.php';

// ---- ต้องล็อกอินก่อน ----
if (!is_login()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$game_id = (int)($_GET['game_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT g.*, c.category_name
    FROM games g
    LEFT JOIN game_categories c ON c.category_id = g.category_id
    WHERE g.game_id = :id
");
$stmt->execute([':id' => $game_id]);
$game = $stmt->fetch();

if (!$game) {
    echo '<div class="empty">ไม่พบเกมที่ต้องการเช่า</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$f = flash();
?>

<section class="section">
  <h2 class="section-title">🛒 ทำรายการเช่า</h2>

  <?php if ($f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
  <?php endif; ?>

  <div class="rent-wrap">
    <!-- ซ้าย: ข้อมูลเกม -->
    <div class="rent-game">
      <div class="card-img"
           style="background-image:url('https://placehold.co/500x260/2a475e/66c0f4?text=<?= urlencode(mb_substr($game['title'],0,14)) ?>')"></div>
      <div class="card-body">
        <h3><?= e($game['title']) ?></h3>
        <p class="cat"><?= e($game['category_name'] ?? 'ทั่วไป') ?></p>
        <table class="info-table">
          <tr><td>ค่าเช่าต่อวัน</td><td class="price"><?= money($game['rental_price_per_day']) ?> ฿</td></tr>
          <tr><td>ค่าประกัน (คืนเมื่อส่งคืนเกม)</td><td><?= money($game['deposit_fee']) ?> ฿</td></tr>
          <tr><td>คงเหลือในสต็อก</td>
              <td><span class="stock <?= $game['stock_qty'] > 0 ? 'ok' : 'no' ?>">
                <?= (int)$game['stock_qty'] ?> ชุด</span></td></tr>
        </table>
      </div>
    </div>

    <!-- ขวา: ฟอร์ม -->
    <div class="rent-form">
      <?php if ($game['stock_qty'] <= 0 || $game['status'] === 'out_of_stock'): ?>
        <div class="empty">ขออภัย เกมนี้ถูกเช่าหมดแล้ว</div>
        <a class="btn btn-ghost btn-block" href="<?= BASE_URL ?>/index.php">กลับไปเลือกเกมอื่น</a>
      <?php else: ?>
      <form method="post" action="<?= BASE_URL ?>/rent_process.php" id="rentForm">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="game_id" value="<?= (int)$game['game_id'] ?>">

        <label>จำนวนวันที่ต้องการเช่า</label>
        <input type="number" name="days" id="days" min="1" max="30" value="3" required>
        <small class="hint">เช่าได้ตั้งแต่ 1 – 30 วัน</small>

        <label>กำหนดคืน</label>
        <input type="text" id="dueDate" readonly>

        <div class="summary">
          <div class="row"><span>ค่าเช่า (<span id="dTxt">3</span> วัน)</span>
               <span id="sumRent">0.00 ฿</span></div>
          <div class="row"><span>ค่าประกัน</span>
               <span><?= money($game['deposit_fee']) ?> ฿</span></div>
          <hr>
          <div class="row total"><span>ยอดชำระรวม</span><span id="sumTotal">0.00 ฿</span></div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">ยืนยันการเช่า</button>
        <a class="btn btn-ghost btn-block" href="<?= BASE_URL ?>/index.php">ยกเลิก</a>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
const PRICE   = <?= (float)$game['rental_price_per_day'] ?>;
const DEPOSIT = <?= (float)$game['deposit_fee'] ?>;
const fmt = n => n.toLocaleString('th-TH',{minimumFractionDigits:2, maximumFractionDigits:2}) + ' ฿';

function calc() {
  let d = parseInt(document.getElementById('days').value) || 0;
  if (d < 1)  d = 1;
  if (d > 30) d = 30;

  const rent = PRICE * d;
  document.getElementById('dTxt').textContent     = d;
  document.getElementById('sumRent').textContent  = fmt(rent);
  document.getElementById('sumTotal').textContent = fmt(rent + DEPOSIT);

  const due = new Date();
  due.setDate(due.getDate() + d);
  document.getElementById('dueDate').value =
    due.toLocaleDateString('th-TH', {day:'2-digit', month:'long', year:'numeric'});
}
document.getElementById('days')?.addEventListener('input', calc);
calc();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>