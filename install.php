<?php
require 'db.php';

// Создаём таблицу admins, если её нет
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Хеш для пароля "admin"
$username = 'admin';
$password = password_hash('admin', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?) 
                       ON DUPLICATE KEY UPDATE password = VALUES(password)");
$stmt->execute([$username, $password]);

echo "✅ Админ создан!<br>";
echo "Логин: <b>admin</b><br>";
echo "Пароль: <b>admin</b><br>";
echo "<br><a href='admin_login.php'>Перейти ко входу</a><br>";
echo "<br><span style='color:red'>⚠ Удалите install.php после установки!</span>";