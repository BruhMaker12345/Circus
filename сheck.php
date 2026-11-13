<?php
require 'db.php';

echo "<h2>🔍 Диагностика системы ролей</h2>";
echo "<style>body{font-family:Arial;padding:30px;} .ok{color:green;} .err{color:red;} table{border-collapse:collapse;margin:20px 0;} td,th{border:1px solid #ddd;padding:8px 15px;}</style>";

// 1. Проверяем структуру таблицы admins
echo "<h3>1. Структура таблицы admins:</h3>";
$cols = $pdo->query("SHOW COLUMNS FROM admins")->fetchAll();
echo "<table><tr><th>Колонка</th><th>Тип</th></tr>";
foreach ($cols as $col) {
    $exists = in_array($col['Field'], ['role', 'perm_view', 'perm_delete', 'perm_edit']);
    $class = $exists ? 'ok' : '';
    echo "<tr class='$class'><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
}
echo "</table>";

// 2. Проверяем, есть ли колонка role
$role_exists = false;
foreach ($cols as $col) {
    if ($col['Field'] === 'role') $role_exists = true;
}

if (!$role_exists) {
    echo "<p class='err'><b>❌ ПРОБЛЕМА НАЙДЕНА:</b> Колонка 'role' отсутствует в таблице admins.</p>";
    echo "<p>Нужно запустить <b>update_db.php</b> для добавления колонок.</p>";
} else {
    echo "<p class='ok'><b>✅ Колонка role существует.</b></p>";
    
    // 3. Показываем данные пользователей
    echo "<h3>2. Текущие пользователи:</h3>";
    $users = $pdo->query("SELECT id, username, role, perm_view, perm_delete, perm_edit FROM admins")->fetchAll();
    echo "<table><tr><th>ID</th><th>Логин</th><th>Роль</th><th>Просмотр</th><th>Удаление</th><th>Редакт.</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['username']}</td>";
        echo "<td><b>{$u['role']}</b></td>";
        echo "<td>" . ($u['perm_view'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($u['perm_delete'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($u['perm_edit'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Проверяем admin
    $admin = array_filter($users, fn($u) => $u['username'] === 'admin');
    if (empty($admin)) {
        echo "<p class='err'>❌ Пользователь 'admin' не найден!</p>";
    } else {
        $admin = reset($admin);
        if ($admin['role'] === 'admin') {
            echo "<p class='ok'>✅ Пользователь 'admin' имеет роль 'admin' — блок управления должен отображаться.</p>";
        } else {
            echo "<p class='err'>❌ У пользователя 'admin' роль '{$admin['role']}', а не 'admin'!</p>";
        }
    }
}

// 5. Проверяем версию admin.php
echo "<h3>3. Проверка файла admin.php:</h3>";
$admin_content = file_get_contents('admin.php');
if (strpos($admin_content, 'Управление операторами') !== false) {
    echo "<p class='ok'>✅ Файл admin.php содержит блок 'Управление операторами'.</p>";
} else {
    echo "<p class='err'>❌ Файл admin.php НЕ содержит блок 'Управление операторами'!</p>";
    echo "<p>Нужно загрузить новую версию admin.php через File Manager.</p>";
}
?>