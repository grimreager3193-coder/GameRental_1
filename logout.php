<?php
session_start();

// ล้างค่าทั้งหมดใน session
$_SESSION = [];

// ลบ session cookie ด้วย (กันบางเบราว์เซอร์แคชไว้)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// ทำลาย session
session_destroy();

// กลับไปหน้าแรก
require_once __DIR__ . '/config/db.php';
header('Location: ' . BASE_URL . '/index.php');
exit;
