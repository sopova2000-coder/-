<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['error' => 'Неверный метод']));
}

// ✅ ЛОГИ ДЛЯ ОТЛАДКИ
file_put_contents('debug.log', date('H:i:s') . " POST: " . json_encode($_POST) . "\nFILES: " . json_encode($_FILES) . "\n", FILE_APPEND);

// ✅ ВАЛИДАЦИЯ
$errors = [];
if (empty(trim($_POST['full_name'])) || strlen(trim($_POST['full_name'])) < 2) {
    $errors[] = 'ФИО: минимум 2 символа';
}
if (empty($_POST['phone']) || strlen(preg_replace('/\D/', '', $_POST['phone'])) < 10) {
    $errors[] = 'Телефон: 10 цифр';
}
if (empty($_POST['age']) || !is_numeric($_POST['age']) || $_POST['age'] < 16 || $_POST['age'] > 100) {
    $errors[] = 'Возраст: 16-100 лет';
}
if (empty($_POST['categories']) || !is_array($_POST['categories'])) {
    $errors[] = 'Выберите категории';
}
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Обязательно загрузите фото';
}

if (!empty($errors)) {
    exit(json_encode(['error' => implode(', ', $errors)]));
}

try {
    $pdo->beginTransaction();

    // ✅ 1. СОЗДАЁМ/ПОЛУЧАЕМ участника
    $stmt = $pdo->prepare("INSERT INTO participants (full_name, phone, age) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([trim($_POST['full_name']), trim($_POST['phone']), (int)$_POST['age']]);
    $participant_id = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM participants WHERE phone = '" . $pdo->quote($_POST['phone']) . "' LIMIT 1")->fetchColumn();

    // ✅ 2. СОЗДАЁМ ПАПКИ
    if (!file_exists('uploads/photos/')) mkdir('uploads/photos/', 0777, true);
    if (!file_exists('uploads/music/')) mkdir('uploads/music/', 0777, true);

    // ✅ 3. СОХРАНЯЕМ ФАЙЛЫ
    $photo_path = null;
    if ($_FILES['photo']['error'] == 0) {
        $photo_name = time() . '_' . basename($_FILES['photo']['name']);
        $photo_path = 'uploads/photos/' . $photo_name;
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    $music_path = null;
    if (isset($_FILES['music']) && $_FILES['music']['error'] == 0) {
        $music_name = time() . '_' . basename($_FILES['music']['name']);
        $music_path = 'uploads/music/' . $music_name;
        move_uploaded_file($_FILES['music']['tmp_name'], $music_path);
    }

    // ✅ 4. СОЗДАЁМ ЗАЯВКИ ПО КАТЕГОРИЯМ
    $stmt = $pdo->prepare("
        INSERT INTO registrations (participant_id, category_id, photo_path, music_path, status) 
        VALUES (?, ?, ?, ?, 'pending')
        ON DUPLICATE KEY UPDATE photo_path = VALUES(photo_path), music_path = VALUES(music_path), submitted_at = CURRENT_TIMESTAMP
    ");

    foreach ($_POST['categories'] as $category_id) {
        $stmt->execute([$participant_id, (int)$category_id, $photo_path, $music_path]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => '✅ Заявка #' . $participant_id . ' успешно сохранена!'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    file_put_contents('debug.log', date('H:i:s') . " ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
}
?>
