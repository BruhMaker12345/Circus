<?php
// db.php — подключение к MySQL через PDO
$host = '127.0.1.14';
//$port = '3306';
$db   = 'circus_db';
$user = 'root';          // стандартный пользователь OpenServer
$pass = '';              // стандартный пароль OpenServer (пустой)
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
    die('Ошибка подключения к БД: ' . $e->getMessage());
}