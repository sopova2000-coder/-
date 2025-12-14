<?php
$host = 'localhost';
$dbname = 'nastenxa_db';
$username = 'nastenxa_db';
$password = '123456Qwerty';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Ошибка подключения к БД']));
}
?>