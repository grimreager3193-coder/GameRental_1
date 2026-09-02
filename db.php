<?php
// ===== ตั้งค่าพื้นฐานทั้งระบบ =====
date_default_timezone_set('Asia/Bangkok');
mb_internal_encoding('UTF-8');

define('SITE_NAME', 'GameRental');
define('BASE_URL', '/gamerental');

// ===== เชื่อมต่อฐานข้อมูล =====
$host   = "localhost";
$dbname = "gamerental_db";   // << เช็กชื่อให้ตรงกับใน phpMyAdmin
$user   = "root";
$pass   = "";                // XAMPP ค่าเริ่มต้นเป็นค่าว่าง

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $conn = $pdo;   // เผื่อไฟล์เก่าที่ยังเรียก $conn อยู่ ใช้ได้ทั้งสองชื่อ
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $e->getMessage());
}

// ===== ฟังก์ชันช่วย =====
function e($str)     { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function money($n)   { return number_format((float)$n, 2); }
function is_login()  { return isset($_SESSION['user_id']); }
function is_admin()  { return (($_SESSION['role'] ?? '') === 'admin'); }

// ===== CSRF Token =====

function csrf_token() {

    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

    return $_SESSION['csrf'];

}

function csrf_check($token) {

    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token ?? '');

}

function flash($msg = null, $type = 'error') {

    if ($msg !== null) { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; return; }

    if (empty($_SESSION['flash'])) return null;

    $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f;

}