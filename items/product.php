<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 404 Not Found');
    die('Товар не найден');
}
$product_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT id, title, name, price, description, category, size, material, article, insulation, temp_range, stock, is_active
    FROM products
    WHERE id = ? AND is_active = 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    die('Товар не найден');
}

// --- ИЗОБРАЖЕНИЯ ---
$stmt_img = $pdo->prepare("
    SELECT image_url, image_path, is_main
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_main DESC, sort_order ASC
");
$stmt_img->execute([$product_id]);
$images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);

$main_image = '';
$thumbnails = [];

foreach ($images as $img) {
    $src = !empty($img['image_url']) ? $img['image_url'] : $img['image_path'];
    if (empty($src)) continue;
    
    // Нормализация пути
    $src = ltrim($src, '/');
    $src = preg_replace('#^\.\./#', '', $src);
    $src = '../' . $src;  // выходим из папки items
    
    if ($img['is_main']) {
        $main_image = $src;
    }
    $thumbnails[] = $src;
}

if (empty($main_image) && !empty($thumbnails)) {
    $main_image = $thumbnails[0];
}
if (empty($main_image)) {
    $main_image = '../img/placeholder.jpg';
}

$sizes = !empty($product['size']) ? array_map('trim', explode(',', $product['size'])) : [];
$price_formatted = number_format($product['price'], 0, '.', ' ');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($product['title']) ?> — Fashion Future</title>
    <link rel="stylesheet" href="../css/two.css">
</head>
<body>
<header>
    <div class="header-content">
        <a href="../index.php" style="text-decoration: none; color: inherit;"><h1>FASHION FUTURE</h1></a>
        <div class="shopping">
            <button id="cartIconBtn"><img src="../img/shopping-cart.png" alt="Корзина"></button>
        </div>
        <div class="user-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="../personal_account.php">Кабинет</a>
            <?php else: ?>
                <a href="../login.php">Войти</a>
                <a href="../registration.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<nav>
    <a href="../category.php">Мужчине</a>
    <a href="../about-us.html">О нас</a>
</nav>

<main>
    <div class="product-card-container">
        <div class="images-section">
            <div class="main-img-wrapper">
                <img src="<?= $main_image ?>" alt="<?= htmlspecialchars($product['title']) ?>" id="main-img">
            </div>
            <?php if (count($thumbnails) > 1): ?>
            <div class="thumbnails">
                <?php foreach ($thumbnails as $thumb): ?>
                    <img src="<?= $thumb ?>" alt="вид" class="thumbnail">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="description-section" 
             data-id="<?= $product['id'] ?>"
             data-name="<?= htmlspecialchars($product['title']) ?>"
             data-price="<?= $product['price'] ?>"
             data-image="<?= $main_image ?>">
            
            <span class="category"><?= htmlspecialchars($product['category'] ?: 'Коллекция 2026') ?></span>
            <h2 class="product-title"><?= htmlspecialchars($product['title']) ?></h2>
            <p class="description-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <?php if (!empty($sizes)): ?>
                <span class="section-label">Выберите размер</span>
                <div class="size-picker">
                    <?php foreach ($sizes as $size): ?>
                        <div class="size-badge" data-size="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="specs">
                <?php if (!empty($product['material'])): ?>
                    <div class="spec-item"><span>Материал</span><strong><?= htmlspecialchars($product['material']) ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($product['insulation'])): ?>
                    <div class="spec-item"><span>Утеплитель</span><strong><?= htmlspecialchars($product['insulation']) ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($product['temp_range'])): ?>
                    <div class="spec-item"><span>Темп. режим</span><strong><?= htmlspecialchars($product['temp_range']) ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($product['article'])): ?>
                    <div class="spec-item"><span>Артикул</span><strong><?= htmlspecialchars($product['article']) ?></strong></div>
                <?php endif; ?>
            </div>

            <div class="price"><?= $price_formatted ?> ₽</div>
            <button class="add-to-cart-btn">Добавить в корзину</button>
            <button class="wishlist-btn" data-id="<?= $product['id'] ?>">❤️ В избранное</button>

            <div class="delivery-info">
                <div class="info-item">📦 Бесплатная доставка от 15 000 ₽</div>
                <div class="info-item">🔄 Возврат в течение 14 дней</div>
                <div class="info-item">🛡️ Гарантия качества 1 год</div>
            </div>
        </div>
    </div>
</main>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3>Полезное</h3>
            <ul>
                <li><a href="#">Доставка и оплата</a></li>
                <li><a href="#">Политика конфиденциальности</a></li>
                <li><a href="#">Таблица размеров</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Контакты</h3>
            <ul>
                <li>г. Кемерово, ул. Ленина, 73</li>
                <li>8 (800) 555-35-35</li>
                <li>support@fashionfuture.ru</li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Мы в сети</h3>
            <div class="social-icons">Instagram / Telegram / VK</div>
        </div>
    </div>
</footer>

<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-panel" id="cartPanel">
    <div class="cart-header">
        <h2>Корзина</h2>
        <button class="close-cart" id="closeCart">&times;</button>
    </div>
    <div class="cart-items" id="cartItems"></div>
    <div class="cart-footer">
        <div class="cart-total">
            <span>Итого:</span>
            <span id="cartTotal">0 ₽</span>
        </div>
        <button class="checkout-btn" id="checkoutBtn">Оформить заказ</button>
    </div>
</div>

<script src="../js/korzina.js"></script>
<script src="../js/change-image.js"></script>
<script>
    // Обработчик добавления в корзину (дублируется из korzina.js на случай, если не сработает)
    document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.querySelector('.add-to-cart-btn');
        const container = document.querySelector('.description-section');
        if (addBtn && container) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = container.getAttribute('data-id');
                const name = container.getAttribute('data-name');
                const price = parseFloat(container.getAttribute('data-price'));
                const image = container.getAttribute('data-image');
                let selectedSize = null;
                const activeSize = document.querySelector('.size-badge.active');
                if (activeSize) selectedSize = activeSize.getAttribute('data-size');
                else if (document.querySelectorAll('.size-badge').length > 0) {
                    alert('Пожалуйста, выберите размер');
                    return;
                }
                const product = { id, name, price, image, size: selectedSize, quantity: 1 };
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                const existingIndex = cart.findIndex(item => item.id == product.id && item.size === product.size);
                if (existingIndex !== -1) cart[existingIndex].quantity += 1;
                else cart.push(product);
                localStorage.setItem('cart', JSON.stringify(cart));
                alert('Товар добавлен в корзину');
                if (typeof window.updateCartDisplay === 'function') window.updateCartDisplay();
                if (typeof window.openCart === 'function') window.openCart();
            });
        }
    });
</script>
<script>
// Избранное
document.addEventListener('DOMContentLoaded', async function() {
    const wishlistBtn = document.querySelector('.wishlist-btn');
    if (!wishlistBtn) return;
    const productId = wishlistBtn.dataset.id;
    
    // Проверка статуса
    try {
        const checkResponse = await fetch(`../wishlist_handler.php?action=check&product_id=${productId}`);
        const checkResult = await checkResponse.json();
        if (checkResult.in_wishlist) {
            wishlistBtn.classList.add('active');
            wishlistBtn.textContent = '✔️ В избранном';
        }
    } catch(e) { console.error(e); }
    
    wishlistBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        const isActive = this.classList.contains('active');
        const action = isActive ? 'remove' : 'add';
        const formData = new FormData();
        formData.append('action', action);
        formData.append('product_id', productId);
        
        try {
            const response = await fetch('../wishlist_handler.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                if (action === 'add') {
                    this.classList.add('active');
                    this.textContent = '✔️ В избранном';
                } else {
                    this.classList.remove('active');
                    this.textContent = '❤️ В избранное';
                }
            } else if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                alert(result.error || 'Ошибка');
            }
        } catch(e) {
            console.error(e);
            alert('Ошибка соединения');
        }
    });
});
</script>
</body>
</html>