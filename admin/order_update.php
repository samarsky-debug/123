<?php require_once 'auth_check.php';
$order_id = (int)$_POST['order_id'];
if (isset($_POST['status'])) {
    $status = $_POST['status'];
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $order_id]);
}
if (isset($_POST['tracking_number'])) {
    $track = trim($_POST['tracking_number']);
    $pdo->prepare("UPDATE orders SET tracking_number = ? WHERE id = ?")->execute([$track, $order_id]);
}
header('Location: orders.php');