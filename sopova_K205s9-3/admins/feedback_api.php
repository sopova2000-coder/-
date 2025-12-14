<?php
header('Content-Type: application/json; charset=utf-8');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'error' => 'Неверный метод запроса'));
    exit;
}

$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$errors = array();

if (mb_strlen($name) < 2) {
    $errors[] = 'Укажите имя (минимум 2 символа)';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Укажите корректный email';
}

if (mb_strlen($message) < 10) {
    $errors[] = 'Сообщение должно содержать минимум 10 символов';
}

if (!empty($errors)) {
    echo json_encode(array(
        'success' => false,
        'error'   => implode(', ', $errors)
    ));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO feedback (name, email, message, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute(array($name, $email, $message));

    echo json_encode(array(
        'success' => true,
        'message' => 'Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.'
    ));
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error'   => 'Ошибка БД при сохранении сообщения'
    ));
}
