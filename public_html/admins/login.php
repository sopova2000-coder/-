<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nastenxa_db;charset=utf8", 'nastenxa_db', '123456Qwerty');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_POST) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare('SELECT id, password FROM admins WHERE login = ?');
        $stmt->execute([$login]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    } catch(PDOException $e) {
        $error = 'Ошибка БД: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ вход | КонкурсПрожектор</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Админ-панель</h1>
                <p>Вход для организаторов конкурса Прожектор</p>
            </div>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="login">Логин</label>
                    <input type="text" id="login" name="login" value="admin" required>
                </div>
                <div class="input-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Войти</button>
                <?php if ($error): ?>
                    <div class="error-message"><?=htmlspecialchars($error)?></div>
                <?php endif; ?>
            </form>
             <div style="margin-top:1rem; text-align:center;">
    <a href="../index.html" class="login-btn" style="display:inline-block; text-decoration:none;">
        На сайт
    </a>
</div>
            <div class="login-footer">
                <p>Демо-доступ: admin / password</p>
            </div>
        </div>
    </div>
</body>
</html>