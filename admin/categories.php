<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Категории</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-container">
    <div class="sidebar">...</div>
    <div class="content">
        <h1>Категории</h1>
        <form method="post" action="category_edit.php" style="margin-bottom:20px">
            <input type="text" name="name" placeholder="Новая категория" required>
            <input type="text" name="slug" placeholder="slug (например, muzhchinam)">
            <button type="submit">Добавить</button>
        </form>
        <table class="data-table">
            <thead><tr><th>ID</th><th>Название</th><th>Slug</th><th>Действия</th></tr></thead>
            <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
            while ($row = $stmt->fetch()):
            ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['slug']) ?></td>
                    <td>
                        <a href="category_edit.php?id=<?= $row['id'] ?>">Редактировать</a>
                        <a href="category_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Удалить категорию? Товары останутся без категории.')">Удалить</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>