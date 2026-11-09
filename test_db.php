<?php
$host = 'localhost';
$user = 'root';
$pass = '';

echo "<h2>Тест подключения к MySQL</h2>";

try {
    // Пытаемся подключиться
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    echo "<p style='color: green; font-weight: bold;'>✅ УСПЕХ! Подключение к серверу MySQL установлено.</p>";
    
    // Проверяем версию MySQL
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "<p>Версия MySQL: <b>" . htmlspecialchars($version) . "</b></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ ОШИБКА: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Попробуйте заменить 'localhost' на '127.0.0.1' или проверьте, запущен ли модуль MySQL в настройках OpenServer.</p>";
}
?>