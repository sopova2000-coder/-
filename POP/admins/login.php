<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=prozhektordb;charset=utf8", 'root', '');
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
<html>
<head>
    <meta charset="UTF-8">
    <title>Админ вход</title>
    <style>
        body { font-family: Arial; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f0f2f5; }
        .login-form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005a87; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Админ-панель</h2>
        <form method="POST">
            <input type="text" name="login" placeholder="Логин" value="admin" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <?php if ($error): ?><div class="error"><?=$error?></div><?php endif; ?>
        <p><small>Демо: admin / password</small></p>
    </div>
</body>
</html>
