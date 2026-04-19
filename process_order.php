<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];
$address = trim($input['address'] ?? '');
$phone = trim($input['phone'] ?? '');

if (empty($cart) || empty($address) || empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Не хватает данных']);
    exit;
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$orderNumber = 'ORD-' . date('YmdHis') . '-' . rand(100, 999);
$userId = $_SESSION['user_id'] ?? null;

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, contact_phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $orderNumber, $total, $address, $phone]);
    $orderId = $pdo->lastInsertId();
    
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
    }
    $pdo->commit();
    
    echo json_encode(['success' => true, 'order_number' => $orderNumber]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}