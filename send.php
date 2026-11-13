<?php
require 'db.php';

// Принимаем только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Получаем и очищаем данные
$name    = trim($_POST['name']    ?? '');
$phone   = trim($_POST['phone']   ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

// Валидация обязательных полей
if ($name === '' || $phone === '') {
    die('<h2 style="text-align:center;margin-top:50px;font-family:sans-serif;color:#ff6b35;">
         ❌ Ошибка: заполните имя и телефон</h2>
         <p style="text-align:center;"><a href="index.html#contacts">← Вернуться</a></p>');
}

// Защита от XSS при выводе
$name    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
$phone   = htmlspecialchars($phone,   ENT_QUOTES, 'UTF-8');
$email   = htmlspecialchars($email,   ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Сохраняем в БД через prepared statement (защита от SQL-инъекций)
try {
    $stmt = $pdo->prepare("INSERT INTO applications (name, phone, email, message) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $email ?: null, $message ?: null]);

    // Успех
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Заявка отправлена</title>
        <style>
            body {
                font-family: 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #ff6b35, #f7931e);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            .success-box {
                background: white;
                padding: 50px 60px;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 500px;
            }
            .success-box h1 { color: #ff6b35; margin-bottom: 20px; }
            .success-box p { color: #6c757d; font-size: 1.1rem; line-height: 1.6; }
            .btn {
                display: inline-block;
                margin-top: 25px;
                padding: 12px 30px;
                background: linear-gradient(135deg, #ff6b35, #f7931e);
                color: white;
                text-decoration: none;
                border-radius: 25px;
                font-weight: bold;
                transition: 0.3s;
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,107,53,0.4); }
        </style>
    </head>
    <body>
        <div class="success-box">
            <h1>✅ Спасибо!</h1>
            <p>Ваша заявка успешно отправлена.<br>Мы свяжемся с вами в ближайшее время.</p>
            <a href="index.html" class="btn">← Вернуться на главную</a>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    die('Ошибка сохранения: ' . $e->getMessage());
}