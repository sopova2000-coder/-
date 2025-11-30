<?php
header('Content-Type: application/json');

// Подключение к БД
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Создаём папки для загрузки
    $uploadDir = 'uploads/';
    $photoDir = $uploadDir . 'photos/';
    $musicDir = $uploadDir . 'music/';
    
    foreach([$uploadDir, $photoDir, $musicDir] as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0777, true);
    }

    $full_name = trim($_POST['full_name']);
    $phone = preg_replace('/\D/', '', $_POST['phone']); // Только цифры
    $age = (int)$_POST['age'];
    $categories = $_POST['categories'] ?? [];

    // Валидация
    if (empty($full_name) || strlen($full_name) < 2) {
        exit(json_encode(['error' => 'Укажите ФИО']));
    }
    if (strlen($phone) < 10) {
        exit(json_encode(['error' => 'Некорректный телефон']));
    }
    if ($age < 16 || $age > 100) {
        exit(json_encode(['error' => 'Возраст 16-100 лет']));
    }
    if (empty($categories)) {
        exit(json_encode(['error' => 'Выберите категории']));
    }
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        exit(json_encode(['error' => 'Загрузите фото']));
    }

    $pdo->beginTransaction();

    try {
        // 1. Проверяем/создаём участника
        $stmt = $pdo->prepare("SELECT id FROM participants WHERE phone = ?");
        $stmt->execute([$phone]);
        $participant = $stmt->fetch();

        if (!$participant) {
            $stmt = $pdo->prepare("INSERT INTO participants (full_name, phone, age) VALUES (?, ?, ?)");
            $stmt->execute([$full_name, $phone, $age]);
            $participant_id = $pdo->lastInsertId();
        } else {
            $participant_id = $participant['id'];
        }

        // 2. Загружаем фото
        $photo = $_FILES['photo'];
        $photoExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $photoName = $participant_id . '_' . time() . '.' . $photoExt;
        $photoPath = $photoDir . $photoName;
        
        if ($photo['size'] > 5 * 1024 * 1024) {
            throw new Exception('Фото > 5MB');
        }
        move_uploaded_file($photo['tmp_name'], $photoPath);

        // 3. Загружаем музыку (если есть)
        $musicPath = null;
        if (isset($_FILES['music']) && $_FILES['music']['error'] === UPLOAD_ERR_OK) {
            $music = $_FILES['music'];
            if ($music['size'] <= 10 * 1024 * 1024) {
                $musicExt = pathinfo($music['name'], PATHINFO_EXTENSION);
                $musicName = $participant_id . '_' . time() . '.' . $musicExt;
                $musicPath = $musicDir . $musicName;
                move_uploaded_file($music['tmp_name'], $musicPath);
            }
        }

        // 4. Создаём заявки
        $stmt = $pdo->prepare("INSERT INTO registrations (participant_id, category_id, photo_path, music_path) VALUES (?, ?, ?, ?)");
        foreach($categories as $cat_id) {
            $stmt->execute([$participant_id, $cat_id, $photoPath, $musicPath]);
        }

        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => '✅ Заявка отправлена! ID: ' . $participant_id,
            'participant_id' => $participant_id
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>