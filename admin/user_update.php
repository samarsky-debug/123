<?php require_once 'auth_check.php';
$user_id = (int)$_POST['user_id'];
if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
    $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user_id]);
}
if (isset($_POST['is_admin'])) {
    $is_admin = (int)$_POST['is_admin'];
    $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?")->execute([$is_admin, $user_id]);
}
header('Location: users.php');