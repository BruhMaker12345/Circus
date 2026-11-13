<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $admin['username'];
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    } else {
        $error = 'Заполните все поля';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Вход администрации | Саха цирк</title>
<style>
:root { --primary: #ff6b35; --secondary: #f7931e; }
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #ff6b35, #f7931e);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-box {
    background: white;
    padding: 50px 40px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
}
.login-box h1 {
    text-align: center;
    color: #2b2d42;
    margin-bottom: 10px;
    font-size: 1.8rem;
}
.login-box .subtitle {
    text-align: center;
    color: #6c757d;
    margin-bottom: 30px;
}
.form-group { margin-bottom: 20px; }
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2b2d42;
    font-weight: 500;
}
.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ffe5d0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}
.form-group input:focus { outline: none; border-color: var(--primary); }
.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,107,53,0.4); }
.error {
    background: #ffe5e5;
    color: #c0392b;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 0.95rem;
}
.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #6c757d;
    text-decoration: none;
}
.back-link:hover { color: var(--primary); }
</style>
</head>
<body>
<div class="login-box">
    <h1>🔒 Вход администрации</h1>
    <p class="subtitle">ГБУК РС(Я) «Саха цирк»</p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_login.php">
        <div class="form-group">
            <label for="username">Логин</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">Войти</button>
    </form>
    <a href="index.html" class="back-link">← Вернуться на сайт</a>
</div>
</body>
</html>