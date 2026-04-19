<?php
session_start();
if (!isset($_GET['order'])) {
    header('Location: index.php');
    exit;
}
$orderNumber = htmlspecialchars($_GET['order']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Заказ оформлен | Fashion Future</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-container { text-align: center; margin: 100px auto; max-width: 600px; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #000; color: #fff; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #333; }
    </style>
</head>
<body>

<!-- ========== ХЕДЕР (без include) ========== -->
<header>
    <div class="header-content">
        <a href="index.php" style="text-decoration: none; color: inherit;">
            <h1>Fashion Future</h1>
        </a>
        <div class="shopping">
            <button id="cartIconBtn">
                <img src="img/shopping-cart.png" alt="Корзина">
            </button>
        </div>
        <div class="user-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Привет, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <a href="personal_account.php">Кабинет</a>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Войти</a>
                <a href="registration.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<nav>
    <a href="category.php">Мужчине</a>
    <a href="womencategory.html">Женщине</a>
    <a href="about-us.html">О нас</a>
</nav>
<!-- ========== КОНЕЦ ХЕДЕРА ========== -->

<div class="success-container">
    <h1>Спасибо за заказ!</h1>
    <p>Ваш номер заказа: <strong><?= $orderNumber ?></strong></p>
    <p>Мы свяжемся с вами в ближайшее время.</p>
    <a href="index.php" class="btn">Вернуться на главную</a>
</div>

<!-- Подключаем скрипт корзины, чтобы иконка работала -->
<script src="js/korzina.js"></script>

</body>
</html>