<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Заказы</title><link rel="stylesheet" href="css/admin.css"></head>
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
        <h1>Заказы</h1>
        <table class="data-table">
            <thead><tr><th>ID</th><th>Номер</th><th>Пользователь</th><th>Сумма</th><th>Статус</th><th>Трек-номер</th><th>Действия</th></tr></thead>
            <tbody>
            <?php
            $stmt = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
            while ($order = $stmt->fetch()):
                $statuses = ['pending'=>'Ожидает оплаты','paid'=>'Оплачен','shipped'=>'Отправлен','delivered'=>'Доставлен','cancelled'=>'Отменён'];
            ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['order_number']) ?></td>
                <td><?= htmlspecialchars($order['username'] ?? 'Гость') ?></td>
                <td><?= number_format($order['total_amount'], 0, '.', ' ') ?> ₽</td>
                <td>
                    <form method="post" action="order_update.php" style="display:inline">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <?php foreach ($statuses as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $order['status']==$k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Обновить</button>
                    </form>
                </td>
                <td>
                    <form method="post" action="order_update.php" style="display:inline">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="text" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="Трек-номер">
                        <button type="submit">Сохранить</button>
                    </form>
                </td>
                <td><a href="order_view.php?id=<?= $order['id'] ?>">Детали</a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>