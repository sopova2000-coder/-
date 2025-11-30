<?php 
require 'config.php'; 

if (!isAdmin()) { 
    header('Location: login.php'); 
    exit; 
}
if (isset($_POST['add']) && !empty($_POST['name']) && !empty($_POST['phone'])) {
    $stmt = $pdo->prepare('INSERT INTO participants (full_name, phone, age, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['age'], $_POST['created_at']]);
    $success = '✅ Участник добавлен!';
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM participants WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    $success = '✅ Участник удален!';
}

$users = $pdo->query('SELECT * FROM participants ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Участники</title>
</head>
<body>
    <header>
        <h1>👥 Участники</h1>
        <a href="index.php">🏠 Главная</a> | <a href="logout.php">🚪 Выход</a>
    </header>
    
    <main>
        <?php if (isset($success)): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem;margin:1rem 0;border-radius:5px;">
                <?=htmlspecialchars($success)?>
            </div>
        <?php endif; ?>
        
        <!--  ФОРМА ДОБАВЛЕНИЯ -->
        <form method="post" class="add-form">
            <input type="text" name="name" placeholder="ФИО" required>
            <input type="tel" name="phone" placeholder="Телефон" required>
            <input type="number" name="age" placeholder="Возраст" min="1" max="120" required>
            <input type="date" name="created_at" required>
            <button type="submit" name="add">➕ Добавить</button>
        </form>
        
        <!--  ТАБЛИЦА С УДАЛЕНИЕМ -->
        <table>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Возраст</th>
                <th>Дата создания</th>
                <th>Действие</th>
            </tr>
            <?php if (empty($users)): ?>
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:#666;">Участники отсутствуют</td></tr>
            <?php else: ?>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?=$user['id']?></td>
                    <td><?=$user['full_name']?></td>
                    <td><?=$user['phone']?></td>
                    <td><?=$user['age']?></td>
                    <td><?=$user['created_at']?></td>
                    <td>
                        <a href="?delete=<?=$user['id']?>" 
                           class="delete-btn" 
                           onclick="return confirm('Удалить участника <?=$user['full_name']?>?')">
                             Удалить
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </main>
</body>
</html>
