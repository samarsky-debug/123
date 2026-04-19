<?php require_once 'auth_check.php';
$id = (int)$_GET['id'];
$pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
header('Location: categories.php');