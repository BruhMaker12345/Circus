<?php
session_start();
require 'db.php';

// 1. Проверка авторизации
if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit;
}

// 2. Читаем актуальные права ПРЯМО ИЗ БАЗЫ (это надёжнее сессии)
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$_SESSION['admin_username']]);
$me = $stmt->fetch();

if (!$me) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

$is_admin   = ($me['role'] === 'admin');
$can_view   = (bool)$me['perm_view'];
$can_delete = (bool)$me['perm_delete'];
$can_edit   = (bool)$me['perm_edit'];

$success_msg = '';
$error_msg   = '';

// 3. Удаление заявки (только если есть право)
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $can_delete) {
    $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([(int)$_GET['delete']]);
    header('Location: admin.php');
    exit;
}

// 4. Создание оператора (только админ)
if ($is_admin && isset($_POST['create_operator'])) {
    $u = trim($_POST['new_username'] ?? '');
    $p = trim($_POST['new_password'] ?? '');
    if ($u === '' || $p === '') {
        $error_msg = 'Заполните логин и пароль оператора.';
    } else {
        try {
            $pdo->prepare("INSERT INTO admins (username, password, role, perm_view, perm_delete, perm_edit)
                           VALUES (?, ?, 'operator', ?, ?, ?)")
                ->execute([
                    $u,
                    password_hash($p, PASSWORD_DEFAULT),
                    isset($_POST['p_view']) ? 1 : 0,
                    isset($_POST['p_delete']) ? 1 : 0,
                    isset($_POST['p_edit']) ? 1 : 0
                ]);
            $success_msg = "Оператор «$u» успешно создан.";
        } catch (PDOException $e) {
            $error_msg = 'Такой логин уже занят.';
        }
    }
}

// 5. Изменение прав оператора (только админ)
if ($is_admin && isset($_POST['update_operator'])) {
    $pdo->prepare("UPDATE admins SET perm_view = ?, perm_delete = ?, perm_edit = ?
                   WHERE id = ? AND role = 'operator'")
        ->execute([
            isset($_POST['p_view']) ? 1 : 0,
            isset($_POST['p_delete']) ? 1 : 0,
            isset($_POST['p_edit']) ? 1 : 0,
            (int)$_POST['op_id']
        ]);
    $success_msg = 'Права оператора обновлены.';
}

// 6. Удаление оператора (только админ)
if ($is_admin && isset($_GET['del_op']) && is_numeric($_GET['del_op'])) {
    $pdo->prepare("DELETE FROM admins WHERE id = ? AND role = 'operator'")->execute([(int)$_GET['del_op']]);
    header('Location: admin.php');
    exit;
}

// 7. Получение данных для отображения
$operators = [];
if ($is_admin) {
    $operators = $pdo->query("SELECT * FROM admins WHERE role = 'operator' ORDER BY id")->fetchAll();
}

$applications = [];
$total = 0;
if ($can_view) {
    $applications = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC")->fetchAll();
    $total = count($applications);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админ-панель | Diamond Circus</title>
<style>
:root { --primary:#ff6b35; --secondary:#f7931e; --bg:#fff8f0; --danger:#e74c3c; --success:#27ae60; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:var(--bg); color:#2b2d42; }
header { background:#fff; padding:20px 5%; box-shadow:0 2px 10px rgba(0,0,0,.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
.logo { font-size:1.4rem; font-weight:bold; color:var(--primary); }
.user-info { display:flex; align-items:center; gap:15px; flex-wrap:wrap; }
.logout-btn { background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; padding:9px 22px; border-radius:25px; text-decoration:none; font-weight:bold; }
.container { max-width:1200px; margin:35px auto; padding:0 20px; }
.role-badge { padding:4px 12px; border-radius:12px; font-size:.85rem; font-weight:bold; color:#fff; }
.role-badge.admin { background:var(--primary); }
.role-badge.operator { background:var(--secondary); }
.panel { background:#fff; padding:25px; border-radius:15px; box-shadow:0 5px 25px rgba(0,0,0,.08); margin-bottom:35px; border-left:5px solid var(--primary); }
.table-wrap { background:#fff; border-radius:15px; box-shadow:0 5px 25px rgba(0,0,0,.08); overflow-x:auto; margin-top:15px; }
table { width:100%; border-collapse:collapse; }
thead { background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; }
th,td { padding:12px 16px; text-align:left; border-bottom:1px solid #ffe5d0; vertical-align:top; }
.form-row { display:flex; gap:12px; flex-wrap:wrap; margin:12px 0; }
.form-row input[type=text], .form-row input[type=password] { flex:1; min-width:180px; padding:10px; border:2px solid #ffe5d0; border-radius:8px; }
.checkbox-group { display:flex; gap:18px; flex-wrap:wrap; margin:12px 0; }
.checkbox-group label { display:flex; align-items:center; gap:6px; cursor:pointer; }
.btn { border:none; padding:9px 20px; border-radius:8px; cursor:pointer; font-weight:bold; color:#fff; text-decoration:none; display:inline-block; font-size:.9rem; }
.btn-green { background:var(--success); }
.btn-blue { background:#3498db; }
.btn-red { background:var(--danger); }
.alert { padding:12px 15px; border-radius:8px; margin-bottom:15px; }
.alert-success { background:#d4edda; color:#155724; }
.alert-error { background:#f8d7da; color:#721c24; }
h1 { margin-bottom:10px; }
h3 { margin:18px 0 8px; }
</style>
</head>
<body>

<header>
    <div class="logo">⭐ Админ-панель</div>
    <div class="user-info">
        <span>👤 <?= htmlspecialchars($me['username']) ?></span>
        <span class="role-badge <?= $me['role'] ?>"><?= $is_admin ? 'Администратор' : 'Оператор' ?></span>
        <a href="logout.php" class="logout-btn">Выйти</a>
    </div>
</header>

<div class="container">

<?php if ($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<?php if ($is_admin): ?>
<div class="panel">
    <h2>⚙️ Управление операторами</h2>

    <h3>Создать нового оператора</h3>
    <form method="POST">
        <div class="form-row">
            <input type="text" name="new_username" placeholder="Логин оператора" required>
            <input type="password" name="new_password" placeholder="Пароль" required>
        </div>
        <div class="checkbox-group">
            <label><input type="checkbox" name="p_view" checked> Просмотр заявок</label>
            <label><input type="checkbox" name="p_delete"> Удаление заявок</label>
            <label><input type="checkbox" name="p_edit"> Редактирование сайта</label>
        </div>
        <button type="submit" name="create_operator" class="btn btn-green">Создать оператора</button>
    </form>

    <h3>Существующие операторы</h3>
    <?php if (!$operators): ?>
        <p style="color:#6c757d">Операторов пока нет.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Логин</th><th>Просмотр</th><th>Удаление</th><th>Редактирование</th><th>Действия</th></tr>
                </thead>
                <tbody>
                <?php foreach ($operators as $op): ?>
                    <tr>
                        <form method="POST">
                            <td>
                                <b><?= htmlspecialchars($op['username']) ?></b>
                                <input type="hidden" name="op_id" value="<?= $op['id'] ?>">
                            </td>
                            <td><input type="checkbox" name="p_view" <?= $op['perm_view'] ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="p_delete" <?= $op['perm_delete'] ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="p_edit" <?= $op['perm_edit'] ? 'checked' : '' ?>></td>
                            <td style="white-space:nowrap">
                                <button type="submit" name="update_operator" class="btn btn-blue">Сохранить</button>
                                <a href="?del_op=<?= $op['id'] ?>" class="btn btn-red"
                                   onclick="return confirm('Удалить оператора <?= htmlspecialchars($op['username']) ?>?');">Удалить</a>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="color:#6c757d;font-size:.85rem;margin-top:8px">Отметьте галочки и нажмите «Сохранить», чтобы изменить права.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<h1>📋 Заявки пользователей</h1>

<?php if (!$can_view): ?>
    <div class="alert alert-error">У вас нет прав на просмотр заявок.</div>
<?php elseif ($total === 0): ?>
    <div class="table-wrap" style="padding:35px;text-align:center;color:#6c757d">Заявок пока нет</div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Имя</th><th>Телефон</th><th>Email</th><th>Сообщение</th><th>Дата</th>
                    <?php if ($can_delete): ?><th>Действия</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($applications as $a): ?>
                <tr>
                    <td>#<?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><?= htmlspecialchars($a['phone']) ?></td>
                    <td><?= htmlspecialchars($a['email'] ?? '—') ?></td>
                    <td><?= nl2br(htmlspecialchars($a['message'] ?? '—')) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($a['created_at'])) ?></td>
                    <?php if ($can_delete): ?>
                        <td><a href="?delete=<?= $a['id'] ?>" class="btn btn-red"
                               onclick="return confirm('Удалить заявку #<?= $a['id'] ?>?');">Удалить</a></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($can_edit): ?>
<div class="panel" style="border-left-color:var(--success);margin-top:35px">
    <h3>🛠️ Редактирование сайта</h3>
    <p>У вас есть право редактирования контента сайта.</p>
</div>
<?php endif; ?>

</div>
</body>
</html>