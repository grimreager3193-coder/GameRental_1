<?php
session_start();
require_once __DIR__ . '/config/db.php';

// ---- อนุญาตเฉพาะ POST ----
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}
if (!is_login()) {
    header('Location: ' . BASE_URL . '/login.php'); exit;
}
if (!csrf_check($_POST['csrf'] ?? '')) {
    flash('เซสชันหมดอายุ กรุณาลองใหม่อีกครั้ง');
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$user_id = (int)$_SESSION['user_id'];
$game_id = (int)($_POST['game_id'] ?? 0);
$days    = (int)($_POST['days'] ?? 0);

// ---- ตรวจสอบฝั่ง Server (ห้ามเชื่อ JS) ----
if ($game_id <= 0 || $days < 1 || $days > 30) {
    flash('ข้อมูลไม่ถูกต้อง กรุณาระบุจำนวนวัน 1–30 วัน');
    header('Location: ' . BASE_URL . '/rent.php?game_id=' . $game_id); exit;
}

try {
    $pdo->beginTransaction();

    // 🔒 ล็อกแถวไว้ กันคนกดเช่าพร้อมกันแล้วสต็อกติดลบ
    $st = $pdo->prepare("SELECT rental_price_per_day, deposit_fee, stock_qty, status
                         FROM games WHERE game_id = :id FOR UPDATE");
    $st->execute([':id' => $game_id]);
    $game = $st->fetch();

    if (!$game) throw new Exception('ไม่พบเกมที่ต้องการเช่า');
    if ($game['stock_qty'] <= 0 || $game['status'] === 'out_of_stock') {
        throw new Exception('ขออภัย เกมนี้เพิ่งถูกเช่าไปพอดี กรุณาเลือกเกมอื่น');
    }

    // ---- คำนวณราคา (คิดจากค่าในฐานข้อมูลเท่านั้น) ----
    $subtotal = round($game['rental_price_per_day'] * $days, 2);
    $total    = round($subtotal + $game['deposit_fee'], 2);
    $due_date = (new DateTime())->modify("+{$days} day")->format('Y-m-d');

    // ---- 1) บันทึกหัวบิล ----
    $ins = $pdo->prepare("INSERT INTO rentals
        (user_id, admin_id, rental_date, expected_return_date, total_price, late_fine, status)
        VALUES (:uid, NULL, NOW(), :due, :total, 0.00, 'renting')");
    $ins->execute([':uid' => $user_id, ':due' => $due_date, ':total' => $total]);
    $rental_id = (int)$pdo->lastInsertId();

    // ---- 2) บันทึกรายละเอียด ----
    $pdo->prepare("INSERT INTO rental_details (rental_id, game_id, days_rented, subtotal)
                   VALUES (:rid, :gid, :d, :sub)")
        ->execute([':rid'=>$rental_id, ':gid'=>$game_id, ':d'=>$days, ':sub'=>$subtotal]);

    // ---- 3) ตัดสต็อก + ปรับสถานะอัตโนมัติ ----
    $upd = $pdo->prepare("UPDATE games
        SET stock_qty = stock_qty - 1,
            status = CASE WHEN stock_qty - 1 <= 0 THEN 'out_of_stock' ELSE 'available' END
        WHERE game_id = :id AND stock_qty > 0");
    $upd->execute([':id' => $game_id]);
    if ($upd->rowCount() === 0) throw new Exception('ตัดสต็อกไม่สำเร็จ กรุณาลองใหม่');

    // ---- 4) แถม XP ให้ผู้เช่า ----
    $pdo->prepare("UPDATE users SET user_xp = user_xp + :xp WHERE user_id = :uid")
        ->execute([':xp' => $days * 10, ':uid' => $user_id]);

    $pdo->commit();

    flash("เช่าสำเร็จ! เลขที่รายการ #{$rental_id} กำหนดคืนวันที่ " .
          date('d/m/Y', strtotime($due_date)), 'success');
    header('Location: ' . BASE_URL . '/my_rentals.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage());
    header('Location: ' . BASE_URL . '/rent.php?game_id=' . $game_id);
    exit;
}