<?php require 'config.php'; 
if (!isAdmin()) { header('Location: login.php'); exit; }
$stmt = $pdo->query('SELECT COUNT(*) FROM participants');
$users_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Админ-панель</title>
</head>
<body>
    <header>
        <h1>Админ-панель</h1>
        <a href="logout.php" class="logout">Выход</a>
    </header>
    <nav>
        <a href="index.php">Главная</a>
        <a href="users.php">Пользователи (<?=$users_count?>)</a>
    </nav>
    <main>
        <div class="dashboard">
            <div class="card">
                <h3>Пользователи</h3>
                <p><?=$users_count?></p>
            </div>
            <div class="card">
                <h3>Статистика</h3>
                <p>Здесь график посещений</p>
            </div>
        </div>
    </main>
</body>
</html>
