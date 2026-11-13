<?php
// НАСТРОЙКИ ДЛЯ INFINITYFREE
$host = 'sql213.infinityfree.com';       // <-- ЗАМЕНИТЕ на ваш MySQL Host Name
$db   = 'if0_43399251_db'; // <-- ЗАМЕНИТЕ на ваш MySQL Database Name
$user = 'if0_43399251';           // <-- ЗАМЕНИТЕ на ваш MySQL User Name
$pass = 'Kolya7564';       // <-- ЗАМЕНИТЕ на ваш MySQL Password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Временно включаем вывод ошибки, чтобы видеть, что именно не так
    die('Ошибка подключения к БД: ' . $e->getMessage());
}