<?php
require 'config.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$success = '';
$error   = '';

// Добавление участника вручную
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name  = isset($_POST['name'])       ? trim($_POST['name'])       : '';
    $phone = isset($_POST['phone'])      ? trim($_POST['phone'])      : '';
    $age   = isset($_POST['age'])        ? (int)$_POST['age']         : 0;
    $date  = isset($_POST['created_at']) ? $_POST['created_at']       : date('Y-m-d');

    if ($name === '' || mb_strlen($name) < 2) {
        $error = 'Укажите ФИО (минимум 2 символа)';
    } elseif ($phone === '') {
        $error = 'Укажите телефон';
    } elseif ($age < 1 || $age > 120) {
        $error = 'Возраст должен быть от 1 до 120';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO participants (full_name, phone, age, created_at) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $phone, $age, $date]);
        $success = '✅ Участник добавлен!';
    }
}

// Удаление участника
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM participants WHERE id = ?');
    $stmt->execute([$id]);
    $success = '✅ Участник удалён!';
}

// Список участников
$users = $pdo->query('SELECT * FROM participants ORDER BY id DESC')
             ->fetchAll(PDO::FETCH_ASSOC);

// Только счётчик участников для меню
$users_count = (int)$pdo->query('SELECT COUNT(*) FROM participants')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Участники | Админ-панель</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="container admin-header__inner">
        <div class="admin-header__title">Админ-панель · КонкурсПрожектор</div>
        <nav class="admin-nav">
            <a href="index.php"    class="admin-nav__link">Главная</a>
            <a href="users.php"    class="admin-nav__link">Участники (<?=htmlspecialchars($users_count)?>)</a>
            <a href="reviews.php"  class="admin-nav__link">Отзывы</a>
            <a href="feedback.php" class="admin-nav__link">Контакты</a>
            <a href="../index.html" class="admin-nav__link">На сайт</a>
            <a href="logout.php"   class="btn-ghost logout-btn">Выход</a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="container">
        <h1 class="admin-page-title">Участники</h1>

        <?php if ($success): ?>
            <div class="success"><?=htmlspecialchars($success)?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>
        <form method="post" class="add-form">
            <input type="text"   name="name"       placeholder="ФИО"      required>
            <input type="tel"    name="phone"      placeholder="Телефон"  required>
            <input type="number" name="age"        placeholder="Возраст"  min="1" max="120" required>
            <input type="date"   name="created_at" value="<?=date('Y-m-d')?>" required>
            <button type="submit" name="add">➕ Добавить</button>
        </form>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Возраст</th>
                <th>Дата создания</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#666;">
                        Участники отсутствуют
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?=htmlspecialchars($user['id'])?></td>
                        <td><?=htmlspecialchars($user['full_name'])?></td>
                        <td><?=htmlspecialchars($user['phone'])?></td>
                        <td><?=htmlspecialchars($user['age'])?></td>
                        <td><?=htmlspecialchars($user['created_at'])?></td>
                        <td>
                            <a href="?delete=<?=urlencode($user['id'])?>"
                               class="delete-btn"
                               onclick="return confirm('Удалить участника <?=htmlspecialchars($user['full_name'])?>?');">
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
