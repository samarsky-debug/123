<?php
/**
 * Импорт товаров из статических HTML-файлов в БД (таблицы products, product_images)
 */

require_once 'config.php'; // Подключение к БД (должен быть определён $pdo)

// Функция нормализации пути (убирает '../' в начале и добавляет '/')
function normalizePath($path) {
    if (empty($path)) return '';
    // Убираем ведущее '../' если есть
    $path = preg_replace('#^\.\./#', '', $path);
    // Убираем возможный дублирующийся слеш в начале
    $path = '/' . ltrim($path, '/');
    return $path;
}

$htmlDir = __DIR__ . '/items/';

$htmlFiles = glob($htmlDir . '*.html');
if (empty($htmlFiles)) {
    die("Нет HTML-файлов в папке $htmlDir");
}

$inserted = 0;
$errors = [];

$pdo->beginTransaction();

foreach ($htmlFiles as $filePath) {
    echo "Обработка: " . basename($filePath) . "\n";
    $html = file_get_contents($filePath);
    if ($html === false) {
        $errors[] = "Не удалось прочитать файл: " . basename($filePath);
        continue;
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    // ---- Название товара ----
    $titleNode = $xpath->query("//h2[contains(@class, 'product-title')]");
    $title = $titleNode->length ? trim($titleNode->item(0)->textContent) : 'Без названия';

    // ---- Цена ----
    $priceNode = $xpath->query("//div[contains(@class, 'price')]");
    $priceRaw = $priceNode->length ? trim($priceNode->item(0)->textContent) : '0';
    $price = (float) preg_replace('/[^0-9]/', '', $priceRaw);

    // ---- Описание ----
    $descNode = $xpath->query("//p[contains(@class, 'description-text')]");
    $description = $descNode->length ? trim($descNode->item(0)->textContent) : '';

    // ---- Категория ----
    $categoryNode = $xpath->query("//span[contains(@class, 'category')]");
    $category = $categoryNode->length ? trim($categoryNode->item(0)->textContent) : '';

    // ---- Характеристики ----
    $material = null;
    $insulation = null;
    $temp_range = null;
    $article = null;

    $specItems = $xpath->query("//div[contains(@class, 'spec-item')]");
    foreach ($specItems as $item) {
        $labelNode = $xpath->query(".//span", $item);
        $valueNode = $xpath->query(".//strong", $item);
        if ($labelNode->length && $valueNode->length) {
            $label = trim($labelNode->item(0)->textContent);
            $value = trim($valueNode->item(0)->textContent);
            if (strpos($label, 'Материал') !== false) $material = $value;
            elseif (strpos($label, 'Утеплитель') !== false) $insulation = $value;
            elseif (strpos($label, 'Темп') !== false) $temp_range = $value;
            elseif (strpos($label, 'Артикул') !== false) $article = $value;
        }
    }

    if (empty($article)) {
        $dataIdNode = $xpath->query("//div[contains(@class, 'description-section')]/@data-id");
        if ($dataIdNode->length) {
            $article = $dataIdNode->item(0)->value;
        } else {
            $article = pathinfo($filePath, PATHINFO_FILENAME);
        }
    }

    // ---- Размеры ----
    $sizes = [];
    $sizeNodes = $xpath->query("//div[contains(@class, 'size-badge')]");
    foreach ($sizeNodes as $node) {
        $size = trim($node->textContent);
        if (!empty($size)) $sizes[] = $size;
    }
    $sizeStr = implode(',', $sizes);

    // ---- Изображения ----
    $mainImage = '';
    $additionalImages = [];

    $mainImgNode = $xpath->query("//img[@id='main-img']");
    if ($mainImgNode->length) {
        $mainImage = $mainImgNode->item(0)->getAttribute('src');
    }

    $thumbNodes = $xpath->query("//div[contains(@class, 'thumbnails')]//img");
    foreach ($thumbNodes as $node) {
        $src = $node->getAttribute('src');
        if (!empty($src)) {
            if ($src === $mainImage) continue;
            $additionalImages[] = $src;
        }
    }

    if (empty($mainImage) && !empty($additionalImages)) {
        $mainImage = array_shift($additionalImages);
    }

    // Нормализация путей (используем вынесенную функцию)
    $mainImage = normalizePath($mainImage);
    $additionalImages = array_map('normalizePath', $additionalImages);

    // ---- Проверка дубликата по артикулу ----
    $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE article = ?");
    $stmtCheck->execute([$article]);
    if ($stmtCheck->fetch()) {
        echo "  Товар с артикулом $article уже существует, пропускаем.\n";
        continue;
    }

    // ---- Вставка товара ----
    $stmt = $pdo->prepare("
        INSERT INTO products 
        (title, name, price, description, category, size, material, article, insulation, temp_range, stock, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $title,
        $title,
        $price,
        $description,
        $category,
        $sizeStr,
        $material,
        $article,
        $insulation,
        $temp_range,
        10,
        1
    ]);
    $productId = $pdo->lastInsertId();

    // ---- Вставка главного изображения ----
    if (!empty($mainImage)) {
        $stmtImg = $pdo->prepare("
            INSERT INTO product_images (product_id, image_url, image_path, is_main, sort_order)
            VALUES (?, ?, ?, 1, 0)
        ");
        $stmtImg->execute([$productId, $mainImage, $mainImage]);
    }

    // ---- Вставка дополнительных изображений ----
    $order = 1;
    foreach ($additionalImages as $img) {
        $stmtImg = $pdo->prepare("
            INSERT INTO product_images (product_id, image_url, image_path, is_main, sort_order)
            VALUES (?, ?, ?, 0, ?)
        ");
        $stmtImg->execute([$productId, $img, $img, $order++]);
    }

    $inserted++;
    echo "  ✅ Добавлен товар: $title (ID $productId, артикул $article)\n";
}

$pdo->commit();

echo "\n===========================\n";
echo "Импорт завершён.\n";
echo "Добавлено товаров: $inserted\n";
if (!empty($errors)) {
    echo "Ошибки:\n";
    foreach ($errors as $err) echo "  - $err\n";
}