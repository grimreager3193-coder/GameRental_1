<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register.php'); exit;
}

function back($msg) {
    flash($msg);
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

// ---- CSRF ----
if (!csrf_check($_POST['csrf'] ?? '')) back('เซสชันหมดอายุ กรุณาลองใหม่อีกครั้ง');

// ---- รับค่า ----
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim(strtolower($_POST['email'] ?? ''));
$pass     = $_POST['password'] ?? '';
$pass2    = $_POST['password_confirm'] ?? '';
$accept   = $_POST['accept'] ?? '';

// เก็บค่าเดิมไว้เติมกลับในฟอร์ม (ยกเว้นรหัสผ่าน)
$_SESSION['old'] = ['fullname' => $fullname, 'email' => $email];

// ---- ตรวจสอบฝั่ง Server ----
if ($fullname === '' || $email === '' || $pass === '') back('กรุณากรอกข้อมูลให้ครบทุกช่อง');
if (mb_strlen($fullname) < 3 || mb_strlen($fullname) > 100) back('ชื่อ-นามสกุลต้องมี 3–100 ตัวอักษร');
if (!filter_var($email, FILTER_VALIDATE_EMAIL))            back('รูปแบบอีเมลไม่ถูกต้อง');
if (mb_strlen($email) > 100)                               back('อีเมลยาวเกินกำหนด (สูงสุด 100 ตัวอักษร)');
if (strlen($pass) < 6)                                     back('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
if ($pass !== $pass2)                                      back('รหัสผ่านทั้งสองช่องไม่ตรงกัน');
if ($accept !== '1')                                       back('กรุณายอมรับเงื่อนไขการใช้งานก่อนสมัคร');

try {
    // ---- เช็กอีเมลซ้ำ ----
    $chk = $pdo->prepare("SELECT user_id FROM users WHERE email = :em LIMIT 1");
    $chk->execute([':em' => $email]);
    if ($chk->fetch()) back('อีเมลนี้ถูกใช้สมัครไปแล้ว กรุณาใช้อีเมลอื่น');

    // ---- Hash รหัสผ่าน (BCRYPT ตาม Data Dictionary) ----
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    $ins = $pdo->prepare("
        INSERT INTO users (email, password, fullname, role, status, user_xp, created_at)
        VALUES (:em, :pw, :fn, 'user', 'active', 50, NOW())
    ");
    $ins->execute([':em' => $email, ':pw' => $hash, ':fn' => $fullname]);

    unset($_SESSION['old']);

    // ---- สมัครเสร็จ ล็อกอินให้อัตโนมัติเลย ----
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int)$pdo->lastInsertId();
    $_SESSION['fullname'] = $fullname;
    $_SESSION['role']     = 'user';

    flash("ยินดีต้อนรับคุณ {$fullname} 🎉 สมัครสมาชิกสำเร็จ รับไปเลย 50 XP", 'success');
    header('Location: ' . BASE_URL . '/index.php');
    exit;

} catch (PDOException $e) {
    // 1062 = Duplicate entry (กันกรณีกดสมัครรัว ๆ พร้อมกัน)
    if ($e->errorInfo[1] == 1062) back('อีเมลนี้ถูกใช้สมัครไปแล้ว');
    back('เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่ภายหลัง');
}