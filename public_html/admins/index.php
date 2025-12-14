<?php
require 'config.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Количество участников
$stmt = $pdo->query('SELECT COUNT(*) FROM participants');
$users_count = (int)$stmt->fetchColumn();

// Количество отзывов
$stmt = $pdo->query('SELECT COUNT(*) FROM reviews');
$reviews_count = (int)$stmt->fetchColumn();

// Количество сообщений обратной связи
$stmt = $pdo->query('SELECT COUNT(*) FROM feedback');
$feedback_count = (int)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | КонкурсПрожектор</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="container admin-header__inner">
        <div class="admin-header__title">Админ-панель · КонкурсПрожектор</div>
        <nav class="admin-nav">
            <a href="index.php" class="admin-nav__link">Главная</a>
            <a href="users.php" class="admin-nav__link">Участники (<?=htmlspecialchars($users_count)?>)</a>
            <a href="reviews.php" class="admin-nav__link">Отзывы (<?=htmlspecialchars($reviews_count)?>)</a>
            <a href="feedback.php" class="admin-nav__link">Контакты (<?=htmlspecialchars($feedback_count)?>)</a>
            <a href="../index.html" class="admin-nav__link">На сайт</a>
            <a href="logout.php" class="btn-ghost logout-btn">Выход</a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="container">
        <h1 class="admin-page-title">Обзор</h1>

        <div class="admin-dashboard">
    <div class="admin-card">
        <h3 class="admin-card__title">Участники</h3>
        <p class="admin-card__value"><?=$users_count?></p>
        <p class="admin-card__hint">Всего зарегистрированных участников</p>
        <a href="users.php" class="admin-action-btn">Открыть список</a>
    </div>

    <div class="admin-card">
        <h3 class="admin-card__title">Отзывы</h3>
        <p class="admin-card__value"><?=$reviews_count?></p>
        <p class="admin-card__hint">Ожидают проверки и одобрения</p>
        <a href="reviews.php" class="admin-action-btn">Управлять отзывами</a>
    </div>

    <div class="admin-card">
        <h3 class="admin-card__title">Обратная связь</h3>
        <p class="admin-card__value"><?=$feedback_count?></p>
        <p class="admin-card__hint">Сообщений с формы контактов</p>
        <a href="feedback.php" class="admin-action-btn">Открыть сообщения</a>
    </div>

    <div class="admin-card">
        <h3 class="admin-card__title">Статистика</h3>
        <p class="admin-card__value">—</p>
        <p class="admin-card__hint">Здесь позже можно вывести графики и отчёты</p>
    </div>
</div>
        
    </div>
</main>
</body>
</html>
