<?php
// personal_account.php
// Личный кабинет: авторизация + данные пользователя + смена пароля + история заказов + оплата + отмена + ИЗБРАННОЕ

session_start();

// Настройки подключения к БД
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'samarskiy';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения к БД: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ------------------------------------------------------------
// 1. ПРОВЕРКА НАЛИЧИЯ ПОЛЯ "password" В ТАБЛИЦЕ users
$checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'password'");
if ($checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT ''");
    $defaultHash = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$defaultHash' WHERE password = ''");
}

// 2. ПРОВЕРКА НАЛИЧИЯ ПОЛЕЙ "phone" и "address"
$checkPhone = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
if ($checkPhone->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
}
$checkAddress = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
if ($checkAddress->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL");
}
// ------------------------------------------------------------

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$loginError = '';
$passwordChangeMessage = '';
$passwordChangeError = '';
$orderActionMessage = '';
$profileUpdateMessage = '';
$profileUpdateError = '';

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $pass = $_POST['password'];

    if ($username === '' || $pass === '') {
        $loginError = 'Заполните логин и пароль.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $userRow = $res->fetch_assoc();
            if (password_verify($pass, $userRow['password']) || $userRow['password'] === $pass) {
                $_SESSION['user_id'] = $userRow['id'];
                $_SESSION['username'] = $userRow['username'];
                header("Location: personal_account.php");
                exit;
            } else {
                $loginError = 'Неверный пароль.';
            }
        } else {
            $loginError = 'Пользователь не найден.';
        }
        $stmt->close();
    }
}

// Обработка оплаты заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_order_id']) && isset($_SESSION['user_id'])) {
    $orderId = (int)$_POST['pay_order_id'];
    $userId = (int)$_SESSION['user_id'];

    $checkStmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $checkStmt->bind_param("ii", $orderId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows === 1) {
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
        $updateStmt->bind_param("i", $orderId);
        if ($updateStmt->execute()) {
            $orderActionMessage = "✅ Заказ №{$orderId} успешно оплачен!";
        } else {
            $orderActionMessage = "❌ Ошибка при оплате заказа. Попробуйте позже.";
        }
        $updateStmt->close();
    } else {
        $orderActionMessage = "❌ Заказ не найден или уже оплачен.";
    }
    $checkStmt->close();
}

// Обработка отмены заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id']) && isset($_SESSION['user_id'])) {
    $orderId = (int)$_POST['cancel_order_id'];
    $userId = (int)$_SESSION['user_id'];

    $checkStmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $checkStmt->bind_param("ii", $orderId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows === 1) {
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $updateStmt->bind_param("i", $orderId);
        if ($updateStmt->execute()) {
            $orderActionMessage = "❌ Заказ №{$orderId} отменён.";
        } else {
            $orderActionMessage = "❌ Ошибка при отмене заказа.";
        }
        $updateStmt->close();
    } else {
        $orderActionMessage = "❌ Заказ не найден или не может быть отменён.";
    }
    $checkStmt->close();
}

// Смена пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password']) && isset($_SESSION['user_id'])) {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    $userId = (int)$_SESSION['user_id'];

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $passwordChangeError = 'Заполните все поля.';
    } elseif ($newPass !== $confirmPass) {
        $passwordChangeError = 'Новый пароль и подтверждение не совпадают.';
    } elseif (strlen($newPass) < 4) {
        $passwordChangeError = 'Новый пароль должен содержать минимум 4 символа.';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $currentHash = $row['password'];
            if (password_verify($currentPass, $currentHash) || $currentHash === $currentPass) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newHash, $userId);
                if ($updateStmt->execute()) {
                    $passwordChangeMessage = 'Пароль успешно изменён! Пожалуйста, войдите снова.';
                    session_destroy();
                    header("refresh:2;url=personal_account.php");
                } else {
                    $passwordChangeError = 'Ошибка при обновлении пароля.';
                }
                $updateStmt->close();
            } else {
                $passwordChangeError = 'Неверный текущий пароль.';
            }
        } else {
            $passwordChangeError = 'Пользователь не найден.';
        }
        $stmt->close();
    }
}

// ОБНОВЛЕНИЕ ПРОФИЛЯ (телефон, адрес)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && isset($_SESSION['user_id'])) {
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssi", $phone, $address, $userId);
    if ($stmt->execute()) {
        $profileUpdateMessage = 'Контактные данные успешно обновлены.';
    } else {
        $profileUpdateError = 'Ошибка при сохранении данных.';
    }
    $stmt->close();
}

// Получение данных пользователя
$currentUser = null;
$orders = [];
$wishlistItems = [];

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email, phone, address, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $currentUser = $result->fetch_assoc();
    } else {
        session_destroy();
    }
    $stmt->close();

    // ---------- ПОЛУЧЕНИЕ ИСТОРИИ ЗАКАЗОВ ----------
    $orderQuery = "
        SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ";
    $stmtOrders = $conn->prepare($orderQuery);
    $stmtOrders->bind_param("i", $userId);
    $stmtOrders->execute();
    $ordersResult = $stmtOrders->get_result();
    while ($order = $ordersResult->fetch_assoc()) {
        $itemsQuery = "
            SELECT oi.quantity, oi.price, p.title as product_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ";
        $stmtItems = $conn->prepare($itemsQuery);
        $stmtItems->bind_param("i", $order['id']);
        $stmtItems->execute();
        $itemsRes = $stmtItems->get_result();
        $items = [];
        while ($item = $itemsRes->fetch_assoc()) {
            $items[] = $item;
        }
        $stmtItems->close();
        $order['items'] = $items;
        $orders[] = $order;
    }
    $stmtOrders->close();

    // ---------- ПОЛУЧЕНИЕ ИЗБРАННЫХ ТОВАРОВ ----------
    $wishlistQuery = "
        SELECT w.product_id, p.title, p.price, 
               (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_main DESC LIMIT 1) as image
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ";
    $stmtWish = $conn->prepare($wishlistQuery);
    $stmtWish->bind_param("i", $userId);
    $stmtWish->execute();
    $wishResult = $stmtWish->get_result();
    while ($row = $wishResult->fetch_assoc()) {
        $wishlistItems[] = $row;
    }
    $stmtWish->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | Fashion Future</title>
    <link rel="stylesheet" href="css/account.css">
</head>
<body>
<div class="account-container">
    <a href="index.php" class="back-link">← На главную</a>

    <?php if ($currentUser !== null): ?>
        <!-- Блок данных пользователя (расширен) -->
        <div class="login-form">
            <div class="info-row">
                <div class="label">Имя пользователя</div>
                <div class="value"><strong><?= htmlspecialchars($currentUser['username']) ?></strong></div>
            </div>
            <div class="info-row">
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars($currentUser['email']) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Телефон</div>
                <div class="value"><?= htmlspecialchars($currentUser['phone'] ?? '— не указан —') ?></div>
            </div>
            <div class="info-row">
                <div class="label">Адрес доставки</div>
                <div class="value"><?= nl2br(htmlspecialchars($currentUser['address'] ?? '— не указан —')) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Дата регистрации</div>
                <div class="value">
                    <?php
                    $created = $currentUser['created_at'] ?? '';
                    if (!empty($created)) {
                        $ts = strtotime($created);
                        echo $ts ? date('d.m.Y \в H:i', $ts) : htmlspecialchars($created);
                    } else {
                        echo "— не указана —";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div>
            <button id="toggleProfileBtn" class="toggle-profile-btn">✎ Редактировать профиль</button>
            <button id="togglePasswordBtn" class="toggle-password-btn">🔑 Сменить пароль</button>
        </div>

        <!-- Форма редактирования профиля (телефон, адрес) -->
        <div id="profileEditForm" class="login-form" style="display: none;">
            <h3 style="text-align: center; margin-bottom: 1rem;">Редактирование контактных данных</h3>
            <?php if ($profileUpdateMessage): ?>
                <div class="message-success">✅ <?= htmlspecialchars($profileUpdateMessage) ?></div>
            <?php endif; ?>
            <?php if ($profileUpdateError): ?>
                <div class="message-error">⚠️ <?= htmlspecialchars($profileUpdateError) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="+7 (123) 456-78-90">
                </div>
                <div class="form-group">
                    <label>Адрес доставки</label>
                    <textarea name="address" rows="3" placeholder="Город, улица, дом, квартира..."><?= htmlspecialchars($currentUser['address'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="update_profile">Сохранить изменения</button>
            </form>
        </div>

        <!-- Форма смены пароля -->
        <div id="passwordChangeForm" class="login-form" style="display: none;">
            <h3 style="text-align: center; margin-bottom: 1rem;">Смена пароля</h3>
            <?php if ($passwordChangeMessage): ?>
                <div class="message-success">✅ <?= htmlspecialchars($passwordChangeMessage) ?></div>
            <?php endif; ?>
            <?php if ($passwordChangeError): ?>
                <div class="message-error">⚠️ <?= htmlspecialchars($passwordChangeError) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>Новый пароль (мин. 4 символа)</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Подтверждение нового пароля</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password">Изменить пароль</button>
            </form>
        </div>

        <div>
            <a href="?logout=1" class="logout-link">Выйти из аккаунта</a>
        </div>

        <!-- ИСТОРИЯ ЗАКАЗОВ -->
        <div class="orders-section">
            <h3>📦 История заказов</h3>
            <?php if ($orderActionMessage): ?>
                <div class="message-success"><?= htmlspecialchars($orderActionMessage) ?></div>
            <?php endif; ?>
            <?php if (empty($orders)): ?>
                <p style="color: #666;">У вас пока нет заказов.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header" onclick="toggleOrderItems(this)">
                            <div>
                                <span class="order-number">Заказ №<?= htmlspecialchars($order['order_number']) ?></span>
                                <span class="order-date">от <?= date('d.m.Y', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="order-total"><?= number_format($order['total_amount'], 0, '.', ' ') ?> ₽</span>
                                <span class="order-status status-<?= $order['status'] ?>">
                                    <?php
                                    $statuses = [
                                        'pending' => 'Ожидает оплаты',
                                        'paid' => 'Оплачен',
                                        'shipped' => 'Отправлен',
                                        'delivered' => 'Доставлен',
                                        'cancelled' => 'Отменён'
                                    ];
                                    echo $statuses[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <div class="action-buttons-group">
                                        <form method="post" class="action-form">
                                            <input type="hidden" name="pay_order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" class="action-button pay-button">Оплатить</button>
                                        </form>
                                        <form method="post" class="action-form">
                                            <input type="hidden" name="cancel_order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" class="action-button cancel-button" onclick="return confirm('Вы уверены, что хотите отменить заказ?');">Отменить</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <span class="toggle-icon">▼</span>
                            </div>
                        </div>
                        <div class="order-items">
                            <table>
                                <thead><tr><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr></thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= number_format($item['price'], 0, '.', ' ') ?> ₽</td>
                                            <td><?= number_format($item['quantity'] * $item['price'], 0, '.', ' ') ?> ₽</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ИЗБРАННОЕ -->
        <div class="wishlist-section">
            <h3>❤️ Избранное</h3>
            <?php if (empty($wishlistItems)): ?>
                <p style="color: #666;">У вас пока нет избранных товаров. <a href="category.php">Перейти в каталог</a></p>
            <?php else: ?>
                <div class="wishlist-grid">
                    <?php foreach ($wishlistItems as $item):
                        $image = !empty($item['image']) ? htmlspecialchars($item['image']) : 'img/placeholder.jpg';
                        $priceFormatted = number_format($item['price'], 0, '.', ' ');
                    ?>
                    <div class="wishlist-card" data-id="<?= $item['product_id'] ?>">
                        <a href="items/product.php?id=<?= $item['product_id'] ?>">
                            <img src="<?= $image ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        </a>
                        <h4><?= htmlspecialchars($item['title']) ?></h4>
                        <div class="wishlist-price"><?= $priceFormatted ?> ₽</div>
                        <div class="wishlist-actions">
                            <button class="wishlist-remove" data-id="<?= $item['product_id'] ?>">Удалить</button>
                            <button class="wishlist-add-to-cart" data-id="<?= $item['product_id'] ?>">В корзину</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
            // Переключение формы редактирования профиля
            const toggleProfileBtn = document.getElementById('toggleProfileBtn');
            const profileForm = document.getElementById('profileEditForm');
            if (toggleProfileBtn && profileForm) {
                toggleProfileBtn.addEventListener('click', function() {
                    if (profileForm.style.display === 'none') {
                        profileForm.style.display = 'block';
                        toggleProfileBtn.textContent = '✎ Скрыть форму';
                    } else {
                        profileForm.style.display = 'none';
                        toggleProfileBtn.textContent = '✎ Редактировать профиль';
                    }
                });
            }

            // Переключение формы смены пароля
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordForm = document.getElementById('passwordChangeForm');
            if (togglePasswordBtn && passwordForm) {
                togglePasswordBtn.addEventListener('click', function() {
                    if (passwordForm.style.display === 'none') {
                        passwordForm.style.display = 'block';
                        togglePasswordBtn.textContent = '🔑 Скрыть форму';
                    } else {
                        passwordForm.style.display = 'none';
                        togglePasswordBtn.textContent = '🔑 Сменить пароль';
                    }
                });
            }

            // Переключение состава заказа
            function toggleOrderItems(header) {
                if (event.target.closest('.action-button')) return;
                const itemsDiv = header.nextElementSibling;
                const icon = header.querySelector('.toggle-icon');
                if (itemsDiv.classList.contains('show')) {
                    itemsDiv.classList.remove('show');
                    icon.classList.remove('rotated');
                } else {
                    itemsDiv.classList.add('show');
                    icon.classList.add('rotated');
                }
            }

            // Удаление из избранного
            document.querySelectorAll('.wishlist-remove').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const productId = this.dataset.id;
                    const card = this.closest('.wishlist-card');
                    try {
                        const formData = new FormData();
                        formData.append('action', 'remove');
                        formData.append('product_id', productId);
                        const resp = await fetch('wishlist_handler.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await resp.json();
                        if (data.success) {
                            card.remove();
                            if (document.querySelectorAll('.wishlist-card').length === 0) {
                                document.querySelector('.wishlist-section').innerHTML = '<h3>❤️ Избранное</h3><p style="color:#666;">У вас пока нет избранных товаров. <a href="category.php">Перейти в каталог</a></p>';
                            }
                        } else {
                            alert(data.error || 'Ошибка при удалении');
                        }
                    } catch(e) {
                        console.error(e);
                        alert('Ошибка соединения');
                    }
                });
            });

            // Добавление в корзину из избранного
            document.querySelectorAll('.wishlist-add-to-cart').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const productId = this.dataset.id;
                    try {
                        const formData = new FormData();
                        formData.append('action', 'add');
                        formData.append('id', productId);
                        formData.append('size', 'M');
                        formData.append('quantity', 1);
                        const resp = await fetch('cart_handler.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await resp.json();
                        if (data.success) {
                            alert('Товар добавлен в корзину');
                        } else {
                            alert('Ошибка добавления');
                        }
                    } catch(e) {
                        console.error(e);
                        alert('Ошибка соединения');
                    }
                });
            });
        </script>

    <?php else: ?>
        <!-- Форма входа для неавторизованных -->
        <div class="login-form">
            <h2>🔐 Вход в систему</h2>
            <?php if ($loginError): ?>
                <div class="message-error">⚠️ <?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">Войти</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html> 