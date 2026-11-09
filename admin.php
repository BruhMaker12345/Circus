<?php
session_start();
require 'db.php';

// Проверка авторизации
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Удаление заявки
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: admin.php');
    exit;
}

// Получаем все заявки
$stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
$applications = $stmt->fetchAll();
$total = count($applications);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админ-панель | Саха цирк</title>
<style>
:root { --primary: #ff6b35; --secondary: #f7931e; --bg: #fff8f0; }
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Segoe UI', sans-serif;
    background: var(--bg);
    color: #2b2d42;
    min-height: 100vh;
}
header {
    background: white;
    padding: 20px 5%;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}
.logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary);
}
.user-info {
    display: flex;
    align-items: center;
    gap: 20px;
}
.user-info span { color: #6c757d; }
.logout-btn {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 10px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.logout-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,107,53,0.4); }

.container {
    max-width: 1400px;
    margin: 40px auto;
    padding: 0 20px;
}
.page-title {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #2b2d42;
}
.stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    border-left: 4px solid var(--primary);
}
.stat-card .num {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary);
}
.stat-card .label { color: #6c757d; font-size: 0.9rem; }

.table-wrap {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}
th, td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #ffe5d0;
    vertical-align: top;
}
th { font-weight: 600; font-size: 0.95rem; }
tbody tr:hover { background: #fff8f0; }
td.message {
    max-width: 300px;
    word-wrap: break-word;
}
.actions {
    display: flex;
    gap: 10px;
}
.btn-delete {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: 0.3s;
    text-decoration: none;
    display: inline-block;
}
.btn-delete:hover { background: #c0392b; }

.empty {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}
.empty i { font-size: 3rem; color: #ffe5d0; margin-bottom: 15px; }

@media (max-width: 768px) {
    th, td { padding: 10px; font-size: 0.85rem; }
    .stats { flex-direction: column; }
}
</style>
</head>
<body>

<header>
    <div class="logo">⭐ Админ-панель — Саха цирк</div>
    <div class="user-info">
        <span>👤 <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
        <a href="logout.php" class="logout-btn">Выйти</a>
    </div>
</header>

<div class="container">
    <h1 class="page-title">📋 Заявки пользователей</h1>

    <div class="stats">
        <div class="stat-card">
            <div class="num"><?= $total ?></div>
            <div class="label">Всего заявок</div>
        </div>
    </div>

    <div class="table-wrap">
        <?php if ($total === 0): ?>
            <div class="empty">
                <div style="font-size:3rem;">📭</div>
                <p>Заявок пока нет</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Сообщение</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><strong>#<?= $app['id'] ?></strong></td>
                            <td><?= htmlspecialchars($app['name']) ?></td>
                            <td><?= htmlspecialchars($app['phone']) ?></td>
                            <td><?= htmlspecialchars($app['email'] ?? '—') ?></td>
                            <td class="message"><?= nl2br(htmlspecialchars($app['message'] ?? '—')) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($app['created_at'])) ?></td>
                            <td class="actions">
                                <a href="?delete=<?= $app['id'] ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Удалить заявку #<?= $app['id'] ?>?');">
                                   Удалить
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>