<?php
header('Content-Type: application/json; charset=utf-8');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}

$author_name = isset($_POST['author_name']) ? trim($_POST['author_name']) : '';
$rating      = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

$errors = array();

if (mb_strlen($author_name) < 2) {
    $errors[] = 'Укажите имя (минимум 2 символа)';
}
if ($rating < 1 || $rating > 5) {
    $errors[] = 'Оценка должна быть от 1 до 5';
}
if (mb_strlen($review_text) < 10) {
    $errors[] = 'Отзыв должен содержать минимум 10 символов';
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
        INSERT INTO reviews (author_name, rating, review_text, created_at, is_approved)
        VALUES (?, ?, ?, NOW(), 0)
    ");
    $stmt->execute(array($author_name, $rating, $review_text));

    echo json_encode(array(
        'success' => true,
        'message' => 'Спасибо за ваш отзыв! Он будет опубликован после проверки.'
    ));
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error'   => 'Ошибка БД при сохранении отзыва'
    ));
}
