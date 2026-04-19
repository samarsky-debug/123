<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
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
        <h1>Добро пожаловать, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <div class="stats">
            <?php
            $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            ?>
            <div class="stat-card">Товаров: <?= $totalProducts ?></div>
            <div class="stat-card">Заказов: <?= $totalOrders ?></div>
            <div class="stat-card">Пользователей: <?= $totalUsers ?></div>
        </div>
    </div>
</div>
</body>
</html>