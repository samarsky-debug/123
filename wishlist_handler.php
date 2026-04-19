<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Авторизуйтесь, чтобы добавить в избранное', 'redirect' => '../login.php']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if (!$product_id) {
    echo json_encode(['error' => 'Не указан товар']);
    exit;
}

// Проверка существования товара
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Товар не найден']);
    exit;
}

if ($action === 'add') {
    // Добавляем в избранное (игнорируем дубликаты)
    $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$userId, $product_id]);
    echo json_encode(['success' => true]);
} 
elseif ($action === 'remove') {
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $product_id]);
    echo json_encode(['success' => true]);
} 
elseif ($action === 'check') {
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $product_id]);
    $inWishlist = (bool)$stmt->fetch();
    echo json_encode(['in_wishlist' => $inWishlist]);
}
elseif ($action === 'get_all') {
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'items' => $items]);
}
else {
    echo json_encode(['error' => 'Неизвестное действие']);
}