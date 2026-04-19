<?php require_once 'auth_check.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = (float)$_POST['price'];
    $description = $_POST['description'];
    $category = trim($_POST['category']);
    $size = $_POST['size'];
    $material = $_POST['material'];
    $article = $_POST['article'];
    $insulation = $_POST['insulation'];
    $temp_range = $_POST['temp_range'];
    $stock = (int)$_POST['stock'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE products SET title=?, price=?, description=?, category=?, size=?, material=?, article=?, insulation=?, temp_range=?, stock=?, is_active=? WHERE id=?");
        $stmt->execute([$title, $price, $description, $category, $size, $material, $article, $insulation, $temp_range, $stock, $is_active, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (title, name, price, description, category, size, material, article, insulation, temp_range, stock, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $title, $price, $description, $category, $size, $material, $article, $insulation, $temp_range, $stock, $is_active]);
        $id = $pdo->lastInsertId();
    }
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id ? 'Редактировать' : 'Добавить' ?> товар</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <h2>Админ-панель</h2>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="products.php">Товары</a></li>
            <li><a href="categories.php">Категории</a></li>
            <li><a href="orders.php">Заказы</a></li>
            <li><a href="users.php">Пользователи</a></li>
            <li><a href="logout.php">Выход</a></li>
        </ul>
    </div>
    <div class="content">
        <h1><?= $id ? 'Редактирование' : 'Новый товар' ?></h1>
        <form method="post">
            <label>Название</label>
            <input type="text" name="title" value="<?= htmlspecialchars($product['title'] ?? '') ?>" required>

            <label>Цена (₽)</label>
            <input type="number" step="0.01" name="price" value="<?= $product['price'] ?? '' ?>" required>

            <label>Описание</label>
            <textarea name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>

            <label>Категория (текст)</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product['category'] ?? '') ?>">

            <label>Размеры (через запятую)</label>
            <input type="text" name="size" value="<?= htmlspecialchars($product['size'] ?? '') ?>">

            <label>Материал</label>
            <input type="text" name="material" value="<?= htmlspecialchars($product['material'] ?? '') ?>">

            <label>Артикул</label>
            <input type="text" name="article" value="<?= htmlspecialchars($product['article'] ?? '') ?>">

            <label>Утеплитель</label>
            <input type="text" name="insulation" value="<?= htmlspecialchars($product['insulation'] ?? '') ?>">

            <label>Температурный режим</label>
            <input type="text" name="temp_range" value="<?= htmlspecialchars($product['temp_range'] ?? '') ?>">

            <label>Остаток (stock)</label>
            <input type="number" name="stock" value="<?= $product['stock'] ?? 0 ?>">

            <label><input type="checkbox" name="is_active" <?= !$id || $product['is_active'] ? 'checked' : '' ?>> Товар активен</label>

            <button type="submit">Сохранить</button>
        </form>
    </div>
</div>
</body>
</html>