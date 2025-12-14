<?php
header('Content-Type: application/json');

// Подключение к БД
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Валидация
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Укажите ваше имя (минимум 2 символа)';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный email адрес';
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = 'Сообщение должно содержать минимум 10 символов';
    }

    if (!empty($errors)) {
        echo json_encode(['error' => implode(', ', $errors)]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.'
        ]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Ошибка при сохранении сообщения: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Неверный метод запроса']);
}
?>