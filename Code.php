<?php
define('BASE_URL', 'http://localhost/gamerental');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gamerental_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) { die("Database Error: " . $e->getMessage()); }

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function money($val) { return number_format((float)$val, 2); }
?>