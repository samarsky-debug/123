<?php
require_once 'auth_check.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);
header('Location: products.php');