<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        if (!isset($pdo)) {
            $error = 'Ошибка подключения к базе данных';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Неверное имя пользователя или пароль';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ---------- ПОЛНОСТЬЮ СТИЛЬ ИЗ about-us.css + ЦЕНТРИРОВАНИЕ ---------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
            line-height: 1.5;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* АНИМАЦИИ */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* HEADER (как на основном сайте) */
        header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            gap: 20px;
            flex-wrap: wrap;
        }
        .header-content h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            font-family: 'Playfair Display', serif;
        }
        .header-content a {
            text-decoration: none;
            color: inherit;
        }
        .shopping button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
        .shopping img {
            width: 28px;
            height: 28px;
        }
        .user-links {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 14px;
        }
        .user-links a {
            text-decoration: none;
            color: #333;
            transition: color 0.2s;
        }
        .user-links a:hover {
            color: #000;
            text-decoration: underline;
        }
        nav {
            background: #f8f8f8;
            border-bottom: 1px solid #eee;
            padding: 10px 20px;
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        nav a:hover {
            color: #000;
            text-decoration: underline;
        }

        /* ЦЕНТРИРОВАННОЕ ОКНО АВТОРИЗАЦИИ */
        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
        }
        .auth-card {
            background: #ffffff;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem;
            border-radius: 24px;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeUp 0.8s ease forwards;
        }
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.15);
        }
        .auth-card h2 {
            text-align: center;
            margin-bottom: 1.75rem;
            color: #1a1a1a;
            font-weight: 700;
            font-size: 2rem;
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.01em;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background-color: #fefefe;
        }
        input:focus {
            outline: none;
            border-color: #000000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: #000000;
            border: none;
            border-radius: 40px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.75rem;
        }
        button[type="submit"]:hover {
            background: #2c2c2c;
            transform: scale(1.01);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
        }
        .message-error {
            background-color: #fff5f5;
            border-left: 4px solid #d93838;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            color: #b91c1c;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.9rem;
            color: #555;
        }
        .auth-footer a {
            color: #000000;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
            border-bottom: 1px solid transparent;
        }
        .auth-footer a:hover {
            color: #4a4a4a;
            border-bottom-color: #000000;
        }

        /* FOOTER (как в about-us.css) */
        .main-footer {
            background: #111;
            color: #aaa;
            padding: 60px 20px 30px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        .footer-col h3 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        .footer-col ul {
            list-style: none;
        }
        .footer-col li, .footer-col p {
            margin-bottom: 10px;
        }
        .footer-col a {
            color: #aaa;
            text-decoration: none;
        }
        .footer-col a:hover {
            color: #fff;
        }
        .legal-info {
            font-size: 0.85rem;
        }

        @media (max-width: 640px) {
            .auth-card { padding: 1.75rem; }
            .auth-card h2 { font-size: 1.6rem; }
            .footer-container { grid-template-columns: 1fr; text-align: center; }
        }
        @media (max-width: 480px) {
            .auth-wrapper { padding: 40px 16px; }
            .header-content { flex-direction: column; text-align: center; }
            nav { gap: 16px; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo">
            <a href="index.php"><h1>Fashion Future</h1></a>
        </div>
        <div style="display: flex; gap: 20px; align-items: center;">
        </div>
    </div>
</header>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Вход в систему</h2>

        <?php if ($error): ?>
            <div class="message-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Войти</button>
        </form>

        <div class="auth-footer">
            Нет аккаунта? <a href="registration.php">Зарегистрироваться</a>
        </div>
    </div>
</div>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3>BRAND</h3>
            <p>Стиль и качество с 2020 года</p>
        </div>
        <div class="footer-col">
            <h3>Помощь</h3>
            <ul>
                <li><a href="delivery.php">Доставка и оплата</a></li>
                <li><a href="returns.php">Возврат</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Контакты</h3>
            <p>Email: shop@brand.ru</p>
            <p>Тел: +7 (999) 123-45-67</p>
        </div>
    </div>
    <div class="legal-info" style="text-align: center; margin-top: 40px;">
        © 2025 BRAND. Все права защищены.
    </div>
</footer>

</body>
</html>