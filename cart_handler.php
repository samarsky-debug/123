<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($pdo)) {
    echo json_encode(['error' => 'Ошибка подключения к БД']);
    exit;
}

// ========== ОПРЕДЕЛЕНИЕ БАЗОВОГО URL САЙТА ==========
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseDir = rtrim(dirname($scriptName), '/\\');
$baseUrl = $protocol . '://' . $host . $baseDir . '/';

// ========== МИГРАЦИЯ СТАРОЙ КОРЗИНЫ (одномерный массив → двумерный) ==========
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $first = reset($_SESSION['cart']);
    if (is_int($first) || is_numeric($first)) {
        $oldCart = $_SESSION['cart'];
        $_SESSION['cart'] = [];
        foreach ($oldCart as $id => $qty) {
            $_SESSION['cart'][(int)$id] = ['M' => (int)$qty];
        }
    }
}
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ========== GET – ПОЛУЧЕНИЕ КОРЗИНЫ ==========
if ($action === 'get') {
    $cartItems = [];
    $total = 0;

    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $sizes) {
            if (!is_array($sizes)) continue;

            $stmt = $pdo->prepare("SELECT id, title, price FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                unset($_SESSION['cart'][$id]);
                continue;
            }
            foreach ($sizes as $size => $quantity) {
                $stmtImg = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY is_main DESC LIMIT 1");
                $stmtImg->execute([$id]);
                $img = $stmtImg->fetch(PDO::FETCH_ASSOC);
                
                // Формируем абсолютный URL картинки
                $imagePath = $img ? $img['image_url'] : 'img/placeholder.jpg';
                $image = $baseUrl . ltrim($imagePath, '/');
                
                $cartItems[] = [
                    'id'       => (int)$id,
                    'size'     => $size,
                    'name'     => $product['title'],
                    'price'    => (float)$product['price'],
                    'quantity' => (int)$quantity,
                    'image'    => $image
                ];
                $total += $product['price'] * $quantity;
            }
        }
    }

    echo json_encode(['items' => $cartItems, 'total' => $total]);
    exit;
}

// ========== ADD – ДОБАВЛЕНИЕ ТОВАРА ==========
if ($action === 'add') {
    $id = (int)($_POST['id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($id <= 0 || empty($size)) {
        echo json_encode(['error' => 'Неверные данные товара']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Товар не найден']);
        exit;
    }

    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = [];
    }
    if (isset($_SESSION['cart'][$id][$size])) {
        $_SESSION['cart'][$id][$size] += $quantity;
    } else {
        $_SESSION['cart'][$id][$size] = $quantity;
    }

    echo json_encode(['success' => true]);
    exit;
}

// ========== UPDATE – ИЗМЕНЕНИЕ КОЛИЧЕСТВА ==========
if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $delta = (int)($_POST['delta'] ?? 0);

    if (isset($_SESSION['cart'][$id][$size])) {
        $_SESSION['cart'][$id][$size] += $delta;
        if ($_SESSION['cart'][$id][$size] <= 0) {
            unset($_SESSION['cart'][$id][$size]);
            if (empty($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Товар не найден в корзине']);
    }
    exit;
}

// ========== REMOVE – УДАЛЕНИЕ ТОВАРА ==========
if ($action === 'remove') {
    $id = (int)($_POST['id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    if (isset($_SESSION['cart'][$id][$size])) {
        unset($_SESSION['cart'][$id][$size]);
        if (empty($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Товар не найден']);
    }
    exit;
}

// ========== CLEAR – ОЧИСТКА КОРЗИНЫ ==========
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode(['success' => true]);
    exit;
}

// ========== НЕИЗВЕСТНОЕ ДЕЙСТВИЕ ==========
echo json_encode(['error' => 'Неверное действие']);