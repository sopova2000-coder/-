<?php
session_start();

// Параметры вашей БД (замените на свои)
define('DB_HOST', 'localhost');
define('DB_NAME', 'prozhektordb');  // Имя вашей БД
define('DB_USER', 'root');
define('DB_PASS', '');     // Пароль MySQL (обычно пустой в XAMPP)

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
