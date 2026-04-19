<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление товарами</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
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
        <h1>Товары</h1>
        <a href="product_edit.php" class="btn">+ Добавить товар</a>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Название</th><th>Цена</th><th>Категория</th><th>Остаток</th><th>Активен</th><th>Действия</th></tr>
            </thead>
            <tbody>
                <?php
                // Просто выбираем все товары без JOIN (так как категория хранится текстом)
                $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= number_format($row['price'], 0, '.', ' ') ?> ₽</td>
                    <td><?= htmlspecialchars($row['category'] ?? '—') ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td><?= $row['is_active'] ? 'Да' : 'Нет' ?></td>
                    <td>
                        <a href="product_edit.php?id=<?= $row['id'] ?>">Редактировать</a>
                        <a href="product_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Удалить товар?')">Удалить</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>