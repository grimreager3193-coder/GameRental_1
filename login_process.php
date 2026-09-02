<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/index.php');
        }
        exit;
    }

    $_SESSION['error'] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// ถ้าไม่ได้ POST มา ให้กลับไปหน้า login
header('Location: ' . BASE_URL . '/login.php');
exit;