<?php
session_start();

// ========== ПРОВЕРКА АВТОРИЗАЦИИ ==========
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}
// =========================================

require_once 'config.php';

// Функция получения корзины из сессии с данными из БД
function getCartDetails($pdo) {
    $cartItems = [];
    $total = 0;

    if (empty($_SESSION['cart'])) {
        return ['items' => [], 'total' => 0];
    }

    $productIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("
        SELECT id, title, price 
        FROM products 
        WHERE id IN ($placeholders) AND is_active = 1
    ");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $productsById = [];
    foreach ($products as $p) {
        $productsById[$p['id']] = $p;
    }

    foreach ($_SESSION['cart'] as $id => $sizes) {
        if (!isset($productsById[$id])) {
            unset($_SESSION['cart'][$id]);
            continue;
        }
        $product = $productsById[$id];
        foreach ($sizes as $size => $quantity) {
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            $cartItems[] = [
                'id'       => $id,
                'name'     => $product['title'],
                'price'    => (float)$product['price'],
                'quantity' => $quantity,
                'size'     => $size
            ];
        }
    }
    return ['items' => $cartItems, 'total' => $total];
}

$cartData = getCartDetails($pdo);
$cartItems = $cartData['items'];
$total = $cartData['total'];

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    $errors = [];
    if (empty($address)) $errors[] = 'Укажите адрес доставки';
    if (empty($phone)) $errors[] = 'Укажите телефон';
    if (empty($email)) $errors[] = 'Укажите email';
    
    if (empty($errors) && !empty($cartItems)) {
        try {
            $pdo->beginTransaction();
            
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . rand(100, 999);
            $userId = $_SESSION['user_id']; // теперь точно есть
            
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (user_id, order_number, total_amount, status, shipping_address, contact_phone, contact_email, comment)
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $orderNumber, $total, $address, $phone, $email, $comment]);
            $orderId = $pdo->lastInsertId();
            
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$item['quantity'], $item['id']]);
            }
            
            $pdo->commit();
            $_SESSION['cart'] = [];
            
            header('Location: order_success.php?order=' . urlencode($orderNumber));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Ошибка при оформлении заказа: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа | Fashion Future</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .cart-summary { background: #f8f8f8; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .cart-item { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #000; color: #fff; padding: 12px 24px; border: none; cursor: pointer; border-radius: 4px; }
        .error { color: red; margin-bottom: 15px; }
        .back-link { display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>

<!-- Хедер (как в других файлах) -->
<header>
    <div class="header-content">
        <a href="index.php" style="text-decoration: none; color: inherit;"><h1>Fashion Future</h1></a>
        <div class="shopping">
            <button id="cartIconBtn"><img src="img/shopping-cart.png" alt="Корзина"></button>
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

<div class="checkout-container">
    <h1>Оформление заказа</h1>
    
    <div class="cart-summary">
        <h3>Ваш заказ</h3>
        <?php if (empty($cartItems)): ?>
            <p>Корзина пуста. <a href="index.php">Вернуться к покупкам</a></p>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <span><?= htmlspecialchars($item['name']) ?> (Размер: <?= htmlspecialchars($item['size']) ?>) x <?= $item['quantity'] ?></span>
                    <span><?= number_format($item['price'] * $item['quantity'], 0, '.', ' ') ?> ₽</span>
                </div>
            <?php endforeach; ?>
            <div class="cart-total" style="font-size: 1.2em; font-weight: bold; margin-top: 10px;">
                Итого: <?= number_format($total, 0, '.', ' ') ?> ₽
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($cartItems)): ?>
    <form method="POST">
        <div class="form-group">
            <label>Адрес доставки *</label>
            <input type="text" name="address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Телефон *</label>
            <input type="tel" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['user_email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Комментарий к заказу</label>
            <textarea name="comment" rows="3"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
        </div>
        
        <button type="submit">Подтвердить заказ</button>
    </form>
    <?php endif; ?>
    <div class="back-link"><a href="index.php">← Вернуться в магазин</a></div>
</div>

<script src="js/korzina.js"></script>
</body>
</html>