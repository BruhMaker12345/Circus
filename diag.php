<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Диагностика окружения</h2><pre>";

echo "PHP: " . PHP_VERSION . "\n";
echo "pdo_mysql загружен: " . (extension_loaded('pdo_mysql') ? 'ДА' : 'НЕТ') . "\n";
echo "mysqli загружен:    " . (extension_loaded('mysqli') ? 'ДА' : 'НЕТ') . "\n\n";

echo "--- Поиск живого MySQL ---\n";
$hosts = ['127.0.0.1', 'localhost'];
$ports = [3306, 3307, 3308, 33060];
$creds = [['root',''], ['root','root'], ['root','mysql']];

$found = false;
foreach ($hosts as $h) {
    foreach ($ports as $p) {
        $fp = @fsockopen($h, $p, $errno, $errstr, 0.4);
        if ($fp) {
            fclose($fp);
            echo "ПОРТ ОТКРЫТ: $h:$p\n";
            foreach ($creds as $c) {
                try {
                    new PDO("mysql:host=$h;port=$p;charset=utf8mb4", $c[0], $c[1]);
                    echo "  >>> ПОДКЛЮЧЕНИЕ УСПЕШНО! host=$h port=$p user={$c[0]} pass='{$c[1]}'\n";
                    $found = true;
                } catch (PDOException $e) {
                    echo "  user={$c[0]} pass='{$c[1]}' -> " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "закрыт: $h:$p\n";
        }
    }
}
if (!$found) echo "\nНи один порт не ответил — сервер БД не запущен.\n";
echo "</pre>";