<?php
header('Content-Type: application/json');

// Подключение к БД
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_name = trim($_POST['author_name']);
    $rating = (int)$_POST['rating'];
    $review_text = trim($_POST['review_text']);

    // Валидация
    if (empty($author_name) || strlen($author_name) < 2) {
        exit(json_encode(['error' => 'Укажите ваше имя']));
    }
    if ($rating < 1 || $rating > 5) {
        exit(json_encode(['error' => 'Выберите оценку от 1 до 5 звезд']));
    }
    if (empty($review_text) || strlen($review_text) < 10) {
        exit(json_encode(['error' => 'Отзыв должен содержать минимум 10 символов']));
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (author_name, rating, review_text) VALUES (?, ?, ?)");
        $stmt->execute([$author_name, $rating, $review_text]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Спасибо за ваш отзыв! Он будет опубликован после проверки.'
        ]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Ошибка при сохранении отзыва: ' . $e->getMessage()]);
    }
}

// Получение отзывов для отображения
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT author_name, rating, review_text, created_at FROM reviews WHERE is_approved = TRUE ORDER BY created_at DESC LIMIT 10");
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['reviews' => $reviews]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Ошибка при загрузке отзывов']);
    }
}
?>