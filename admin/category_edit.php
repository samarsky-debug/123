<?php require_once 'auth_check.php';
$id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    if ($id) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
        $stmt->execute([$name, $slug, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
    }
    header('Location: categories.php');
    exit;
}
$category = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head><title><?= $id ? 'Редактировать' : 'Добавить' ?> категорию</title><link rel="stylesheet" href="css/admin.css"></head>
<body>
<div class="admin-container">
    <div class="sidebar">...</div>
    <div class="content">
        <form method="post">
            <label>Название</label>
            <input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
            <label>Slug (транслит)</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($category['slug'] ?? '') ?>">
            <button type="submit">Сохранить</button>
        </form>
    </div>
</div>
</body>
</html>