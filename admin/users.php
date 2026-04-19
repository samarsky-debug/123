<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Пользователи</title><link rel="stylesheet" href="css/admin.css"></head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <h2>Админ-панель</h2>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="products.php">Товары</a></li>
            <li><a href="orders.php">Заказы</a></li>
            <li><a href="users.php">Пользователи</a></li>
            <li><a href="logout.php">Выход</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Пользователи</h1>
        <table class="data-table">
            <thead><tr><th>ID</th><th>Логин</th><th>Email</th><th>Дата регистрации</th><th>Админ</th><th>Действия</th></tr></thead>
            <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM users ORDER BY id");
            while ($user = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['created_at'] ?></td>
                <td><?= $user['is_admin'] ? 'Да' : 'Нет' ?></td>
                <td>
                    <form method="post" action="user_update.php" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="password" name="new_password" placeholder="Новый пароль">
                        <button type="submit">Сбросить пароль</button>
                    </form>
                    <form method="post" action="user_update.php" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="is_admin">
                            <option value="0" <?= !$user['is_admin'] ? 'selected' : '' ?>>Пользователь</option>
                            <option value="1" <?= $user['is_admin'] ? 'selected' : '' ?>>Администратор</option>
                        </select>
                        <button type="submit">Назначить</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>