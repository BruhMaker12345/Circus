<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<pre style='font:14px monospace;padding:20px'>";
echo "PHP версия: " . PHP_VERSION . "\n";
echo "admin.php существует: " . (file_exists('admin.php') ? 'ДА' : 'НЕТ') . "\n";
echo "admin.php размер: " . (file_exists('admin.php') ? filesize('admin.php') . ' байт' : '-') . "\n";
echo "admin_login.php существует: " . (file_exists('admin_login.php') ? 'ДА' : 'НЕТ') . "\n\n";

echo "--- Первые 120 символов admin.php ---\n";
echo htmlspecialchars(substr(file_get_contents('admin.php'), 0, 120)) . "\n\n";

echo "--- Последние 60 символов admin.php ---\n";
echo htmlspecialchars(substr(file_get_contents('admin.php'), -60)) . "\n\n";

echo "--- Содержимое сессии ---\n";
print_r($_SESSION);

echo "\n--- Проверка синтаксиса admin.php ---\n";
$code = file_get_contents('admin.php');
echo "Открывающих <?php: " . substr_count($code, '<?php') . "\n";
echo "Фигурных скобок: { = " . substr_count($code, '{') . " , } = " . substr_count($code, '}') . "\n";
echo "(если число { и } сильно расходится — код скопирован не полностью)\n";
echo "</pre>";