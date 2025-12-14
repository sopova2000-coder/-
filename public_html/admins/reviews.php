<?php
require 'config.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$success = '';
$error   = '';

// ✅ ОБНОВЛЕНИЕ СТАТУСА
if (isset($_GET['status'])) {
    $id = (int)$_GET['status'];
    $new_status = $_GET['status_value'] === '1' ? 1 : 0; // 1=Одобрен, 0=Ожидает/Отклонен
    
    $stmt = $pdo->prepare("UPDATE reviews SET is_approved = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    $success = '✅ Статус отзыва обновлён';
}

// ✅ УДАЛЕНИЕ
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    $success = '✅ Отзыв удалён';
}

// ✅ ВЫборка
$stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Счетчики для меню
$count_stmt = $pdo->query("SELECT COUNT(*) FROM reviews");
$reviews_count = $count_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отзывы | Админ-панель</title>
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
            <a href="reviews.php" class="admin-nav__link active">Отзывы (<?=htmlspecialchars($reviews_count)?>)</a>
            <a href="feedback.php" class="admin-nav__link">Контакты</a>
            <a href="../index.html" class="admin-nav__link">На сайт</a>
            <a href="logout.php" class="btn-ghost logout-btn">Выход</a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="container">
        <h1 class="admin-page-title">Управление отзывами</h1>

        <?php if ($success): ?>
            <div class="success-message"><?=htmlspecialchars($success)?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>

        <div class="stats-row">
            <?php 
            $pending = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0")->fetchColumn();
            $approved = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 1")->fetchColumn();
            ?>
            <div class="stat-card pending">
                <div class="stat-number"><?= $pending ?></div>
                <div class="stat-label">Ожидают</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?= $approved ?></div>
                <div class="stat-label">Одобрено</div>
            </div>
        </div>

        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Автор</th>
                <th>Оценка</th>
                <th>Отзыв</th>
                <th>Дата</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        📝 Отзывов пока нет
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                    <tr class="review-row <?= $r['is_approved'] ? 'approved' : 'pending' ?>">
                        <td class="id-cell"><?= htmlspecialchars($r['id']) ?></td>
                        <td class="author-cell"><?= htmlspecialchars($r['author_name']) ?></td>
                        <td class="rating-cell"><?= htmlspecialchars($r['rating']) ?> ★</td>
                        <td class="text-cell"><?= nl2br(htmlspecialchars(substr($r['review_text'], 0, 100))) ?>
                            <?php if (strlen($r['review_text']) > 100): ?>
                                <span class="more-text">... <a href="#" onclick="showFullText(<?= $r['id'] ?>)">подробнее</a></span>
                                <div class="full-text" id="full-<?= $r['id'] ?>" style="display:none;">
                                    <?= nl2br(htmlspecialchars($r['review_text'])) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="date-cell"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                        <td class="status-cell">
                            <span class="status-badge status-<?= $r['is_approved'] ? 'approved' : 'pending' ?>">
                                <?= $r['is_approved'] ? '✅ Одобрен' : '⏳ Ожидает' ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <a href="?status=<?= $r['id'] ?>&status_value=<?= $r['is_approved'] ? '0' : '1' ?>" 
                                   class="status-btn status-btn-<?= $r['is_approved'] ? 'pending' : 'approved' ?>"
                                   title="<?= $r['is_approved'] ? 'Перевести в ожидание' : 'Одобрить' ?>">
                                    <?= $r['is_approved'] ? '⏳ Ожидание' : '✅ Одобрить' ?>
                                </a>
                                <a href="?delete=<?= $r['id'] ?>" 
                                   class="delete-btn"
                                   onclick="return confirm('Удалить отзыв от «<?= htmlspecialchars($r['author_name']) ?>»?\n\nID: <?= $r['id'] ?>');">
                                    🗑️ Удалить
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function showFullText(id) {
    const fullText = document.getElementById('full-' + id);
    const moreText = fullText.previousElementSibling;
    fullText.style.display = fullText.style.display === 'none' ? 'block' : 'none';
    moreText.style.display = moreText.style.display === 'none' ? 'inline' : 'none';
}
</script>
</body>
</html>
