<?php
$page_title = 'คลังเกม';
require_once __DIR__ . '/includes/header.php';

// ---- หมวดหมู่ ----
$cats = $pdo->query("SELECT category_id, category_name
                     FROM game_categories
                     ORDER BY category_id")->fetchAll();

// ---- ตัวกรอง ----
$cat_id  = (int)($_GET['cat'] ?? 0);
$keyword = trim($_GET['q'] ?? '');

$sql = "SELECT g.game_id, g.title, g.rental_price_per_day, g.deposit_fee,
               g.stock_qty, g.status, c.category_name
        FROM games g
        LEFT JOIN game_categories c ON c.category_id = g.category_id
        WHERE 1=1";
$params = [];

if ($cat_id > 0)      { $sql .= " AND g.category_id = :cat"; $params[':cat'] = $cat_id; }
if ($keyword !== '')  { $sql .= " AND g.title LIKE :kw";     $params[':kw']  = '%' . $keyword . '%'; }

$sql .= " ORDER BY g.status = 'available' DESC, g.game_id DESC";

$st = $pdo->prepare($sql);
$st->execute($params);
$games = $st->fetchAll();
?>

<section class="section">
  <h2 class="section-title">🎮 คลังเกมทั้งหมด</h2>

  <form class="filter-bar" method="get" action="<?= BASE_URL ?>/games.php">
    <input type="text" name="q" placeholder="ค้นหาชื่อเกม..." value="<?= e($keyword) ?>">
    <select name="cat">
      <option value="0">ทุกแพลตฟอร์ม</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['category_id'] ?>" <?= $cat_id === (int)$c['category_id'] ? 'selected' : '' ?>>
          <?= e($c['category_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">ค้นหา</button>
    <?php if ($cat_id || $keyword !== ''): ?>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/games.php">ล้างตัวกรอง</a>
    <?php endif; ?>
  </form>

  <?php if (!$games): ?>
    <div class="empty">ไม่พบเกมที่ตรงกับเงื่อนไข</div>
  <?php else: ?>
  <div class="game-grid">
    <?php foreach ($games as $g):
      $img = 'https://placehold.co/400x220/2a475e/66c0f4?text=' . urlencode(mb_substr($g['title'], 0, 12));
      $available = ($g['stock_qty'] > 0 && $g['status'] === 'available'); ?>
      <div class="card">
        <div class="card-img" style="background-image:url('<?= $img ?>')">
          <span class="badge-<?= $available ? 'renting' : 'overdue' ?> card-badge">
            <?= $available ? 'ว่าง ' . (int)$g['stock_qty'] . ' ชุด' : 'ถูกเช่าหมด' ?>
          </span>
        </div>
        <div class="card-body">
          <h3><?= e($g['title']) ?></h3>
          <p class="cat"><?= e($g['category_name'] ?? 'ทั่วไป') ?></p>
          <div class="card-price">
            <span class="price"><?= money($g['rental_price_per_day']) ?> ฿</span>
            <small>/ วัน</small>
          </div>
          <small class="hint">ค่าประกัน <?= money($g['deposit_fee']) ?> ฿</small>

          <?php if ($available): ?>
            <a class="btn btn-primary btn-block" href="<?= BASE_URL ?>/rent.php?game_id=<?= (int)$g['game_id'] ?>">เช่าเลย</a>
          <?php else: ?>
            <button class="btn btn-ghost btn-block" disabled>ไม่ว่าง</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
