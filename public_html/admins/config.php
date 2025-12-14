<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_NAME', 'nastenxa_db');  
define('DB_USER', 'nastenxa_db');
define('DB_PASS', '123456Qwerty');     

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}
?>
