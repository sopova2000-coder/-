<?php
require 'config.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$success = '';
$error   = '';

// Удаление сообщения
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $success = '✅ Сообщение удалено';
}

// Получение сообщений
$stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
$feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Количество для меню
$count_stmt      = $pdo->query("SELECT COUNT(*) FROM feedback");
$feedback_count  = $count_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обратная связь | Админ-панель</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="container admin-header__inner">
        <div class="admin-header__title">Админ-панель · КонкурсПрожектор</div>
        <nav class="admin-nav">
            <a href="index.php" class="admin-nav__link">Главная</a>
            <a href="users.php" class="admin-nav__link">Участники</a>
            <a href="reviews.php" class="admin-nav__link">Отзывы</a>
            <a href="feedback.php" class="admin-nav__link">Контакты (<?=htmlspecialchars($feedback_count)?>)</a>
            <a href="../index.html" class="admin-nav__link">На сайт</a>
            <a href="logout.php" class="btn-ghost logout-btn">Выход</a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="container">
        <h1 class="admin-page-title">Обратная связь</h1>

        <?php if ($success): ?>
            <div class="success"><?=htmlspecialchars($success)?></div>
        <?php endif; ?>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Сообщение</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$feedback): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;">
                        Сообщений пока нет
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($feedback as $f): ?>
                    <tr>
                        <td><?=htmlspecialchars($f['id'])?></td>
                        <td><?=htmlspecialchars($f['name'])?></td>
                        <td><?=htmlspecialchars($f['email'])?></td>
                        <td><?=nl2br(htmlspecialchars($f['message']))?></td>
                        <td><?=htmlspecialchars($f['created_at'])?></td>
                        <td>
                            <a href="?delete=<?=urlencode($f['id'])?>"
                               class="delete-btn"
                               onclick="return confirm('Удалить сообщение от <?=htmlspecialchars($f['name'])?>?');">
                                Удалить
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
