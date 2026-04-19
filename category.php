<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'samarskiy';

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
    die('Ошибка подключения: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

$base_url = '/';
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);
if ($script_dir != '/' && $script_dir != '\\') {
    $base_url = $script_dir . '/';
}

$sql = "
    SELECT p.*, 
        (SELECT image_url 
         FROM product_images 
         WHERE product_id = p.id 
         ORDER BY is_main DESC, sort_order ASC LIMIT 1) AS main_image
    FROM products p
    ORDER BY p.id ASC
";
$result = $mysqli->query($sql);
if (!$result) {
    die('Ошибка запроса: ' . $mysqli->error);
}
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог — Fashion Future</title>
    <link rel="stylesheet" href="css/one.css">
</head>
<body>
<header>
    <div class="header-content">
        <img class="header-logo" src="img/logo.png" alt="Fashion Future">
        <h1>Fashion Future</h1>
    </div>
</header>
<nav>
    <a href="index.php">Главная</a>
</nav>

<main>
    <div class="filter-container">
        <button class="filter-btn active" data-category="all">Все</button>
        <button class="filter-btn" data-category="puffer">Пуховики</button>
        <button class="filter-btn" data-category="outerwear">Верхняя одежда</button>
        <button class="filter-btn" data-category="longsleeve">Лонгсливы</button>
        <button class="filter-btn" data-category="tshirt">Футболки</button>
        <button class="filter-btn" data-category="bottoms">Штаны</button>
        <button class="filter-btn" data-category="footwear">Обувь</button>
        <button class="filter-btn" data-category="sneakers">Кроссовки</button>
        <button class="filter-btn" data-category="accessories">Аксессуары</button>
    </div>

    <h2 class="section-title">Man's clothes</h2>
    <div class="products" id="mens-clothes">
        <?php if (empty($products)): ?>
            <p>Нет товаров в базе данных.</p>
        <?php else: ?>
            <?php foreach ($products as $product):
                $id = (int)($product['id'] ?? 0);
                $name = htmlspecialchars($product['title'] ?? 'Без названия');
                $price = (float)($product['price'] ?? 0);
                $category = htmlspecialchars($product['category'] ?? 'other');
                $shortDesc = htmlspecialchars(mb_substr($product['description'] ?? '', 0, 100));
                
                $raw_image = $product['main_image'] ?? '';
                if (!empty($raw_image)) {
                    $clean_path = preg_replace('#^\.\./#', '', $raw_image);
                    $clean_path = ltrim($clean_path, '/');
                    $image = $base_url . $clean_path;
                } else {
                    $image = $base_url . 'img/placeholder.jpg';
                }
                $link = "items/product.php?id=$id";
            ?>
            <div class="product" data-category="<?= $category ?>" data-id="<?= $id ?>">
                <a href="<?= $link ?>" class="product-link">
                    <img src="<?= $image ?>" alt="<?= $name ?>" onerror="this.src='<?= $base_url ?>img/placeholder.jpg'">
                </a>
                <h3><?= $name ?></h3>
                <?php if ($shortDesc): ?>
                    <p><?= $shortDesc ?>…</p>
                <?php endif; ?>
                <div class="product-price"><?= number_format($price, 0, '', ' ') ?> ₽</div>
                <div class="product-meta-extra">
                    <a href="<?= $link ?>" class="btn-small">Подробнее</a>
                    <button class="wishlist-btn" data-id="<?= $id ?>">♡</button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-col logo-info">
            <img src="img/logo.png" alt="FFClogo" class="footer-logo">
            <div class="legal-info">
                <p>ИП Самарский Дмитрий Константинович</p>
                <p>ИНН 77777777777</p>
                <p>ОГРНИП 777777777777777</p>
                <p>FashionFutureCollection@yandex.ru</p>
            </div>
        </div>
        <div class="footer-col">
            <h3>Полезное</h3>
            <ul>
                <li><a href="#">Доставка и оплата</a></li>
                <li><a href="#">Политика конфиденциальности</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Контакты</h3>
            <p>Адрес оффлайн магазина — г. Кемерово, ул. Ленина, 73</p>
            <p class="phone">Для заказа онлайн и в другие города<br><strong>+7 777 777 77 77</strong></p>
            <div class="social-icons">
                <a href="https://t.me/">Telegram</a>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация
    const filterBtns = document.querySelectorAll('.filter-btn');
    const products = document.querySelectorAll('.product');
    function filterProducts(category) {
        products.forEach(p => {
            if (category === 'all' || p.dataset.category === category) {
                p.classList.remove('hidden');
            } else {
                p.classList.add('hidden');
            }
        });
    }
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterProducts(this.dataset.category);
        });
    });
    filterProducts('all');

    // Переход по карточке только при клике НЕ на кнопки
    document.querySelectorAll('.product').forEach(product => {
        product.addEventListener('click', function(e) {
            if (e.target.closest('.wishlist-btn') || e.target.closest('.btn-small')) return;
            const link = this.querySelector('.product-link');
            if (link) window.location.href = link.href;
        });
    });

    // Wishlist (избранное)
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    async function toggleWishlist(btn, productId) {
        const isActive = btn.classList.contains('active');
        const action = isActive ? 'remove' : 'add';
        try {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('product_id', productId);
            const resp = await fetch('wishlist_handler.php', { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) {
                if (action === 'add') {
                    btn.classList.add('active');
                    btn.textContent = '❤️';
                } else {
                    btn.classList.remove('active');
                    btn.textContent = '♡';
                }
            } else if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert(data.error || 'Ошибка');
            }
        } catch(e) { console.error(e); }
    }
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            e.preventDefault();
            await toggleWishlist(this, this.dataset.id);
        });
    });

    // Загрузка статуса избранного
    async function loadWishlistStatus() {
        try {
            const resp = await fetch('wishlist_handler.php?action=get_all');
            const data = await resp.json();
            if (data.success && data.items) {
                data.items.forEach(item => {
                    const btn = document.querySelector(`.wishlist-btn[data-id="${item.product_id}"]`);
                    if (btn && !btn.classList.contains('active')) {
                        btn.classList.add('active');
                        btn.textContent = '❤️';
                    }
                });
            }
        } catch(e) { console.error(e); }
    }
    loadWishlistStatus();
});
</script>
</body>
</html>