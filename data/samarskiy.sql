-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 18 2026 г., 11:27
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `samarskiy`
--

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя, если авторизован',
  `order_number` varchar(50) NOT NULL COMMENT 'Уникальный номер заказа',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Общая сумма заказа',
  `status` enum('pending','paid','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Статус заказа',
  `shipping_address` text DEFAULT NULL COMMENT 'Адрес доставки',
  `contact_phone` varchar(50) DEFAULT NULL COMMENT 'Контактный телефон',
  `contact_email` varchar(255) DEFAULT NULL COMMENT 'Email для уведомлений',
  `comment` text DEFAULT NULL COMMENT 'Комментарий к заказу',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tracking_number` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `shipping_address`, `contact_phone`, `contact_email`, `comment`, `created_at`, `updated_at`, `tracking_number`) VALUES
(6, NULL, 'ORD-20260418071521-219', 117780.00, 'paid', 'хз', '21312312321', 'test@gmail.com', '213123', '2026-04-18 05:15:21', '2026-04-18 05:25:36', NULL),
(7, NULL, 'ORD-20260418072225-257', 21390.00, 'paid', '123', '123', 'test@gmail.com', '123', '2026-04-18 05:22:25', '2026-04-18 05:25:38', NULL),
(8, NULL, 'ORD-20260418072647-472', 6490.00, 'paid', '213', '3123', 'test@gmail.com', '123', '2026-04-18 05:26:47', '2026-04-18 05:35:49', NULL),
(9, NULL, 'ORD-20260418073615-384', 32000.00, 'cancelled', '123', '213', 'test@gmail.com', '123', '2026-04-18 05:36:15', '2026-04-18 05:36:19', NULL),
(10, NULL, 'ORD-20260418073727-724', 32000.00, 'cancelled', '123', '123', 'test@gmail.com', '123', '2026-04-18 05:37:27', '2026-04-18 05:42:46', NULL),
(11, NULL, 'ORD-20260418074312-731', 9900.00, 'paid', '123', '21312312321', 'test@gmail.com', '123', '2026-04-18 05:43:12', '2026-04-18 05:43:15', NULL),
(12, NULL, 'ORD-20260418074421-267', 12990.00, 'cancelled', '123', '21312312321', '123@gmail.com', '123', '2026-04-18 05:44:21', '2026-04-18 06:51:16', ''),
(13, 5, 'ORD-20260418111719-223', 34900.00, 'paid', '123', '123', '123@gmail.com', '123', '2026-04-18 09:17:19', '2026-04-18 09:17:37', NULL),
(14, 5, 'ORD-20260418111753-817', 17990.00, 'cancelled', '213', '123', '123@gmail.com', '123', '2026-04-18 09:17:53', '2026-04-18 09:17:59', NULL),
(15, 5, 'ORD-20260418111941-666', 12990.00, 'cancelled', '123', '123', '123@gmail.com', '123', '2026-04-18 09:19:41', '2026-04-18 09:19:55', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `price` decimal(10,2) NOT NULL COMMENT 'Цена товара на момент заказа',
  `total` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED COMMENT 'Сумма по позиции'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(7, 6, 1, 1, 12990.00),
(8, 6, 2, 1, 5000.00),
(10, 6, 4, 1, 34900.00),
(11, 6, 4, 1, 34900.00),
(12, 6, 5, 1, 4990.00),
(13, 7, 8, 1, 9490.00),
(14, 7, 23, 1, 11900.00),
(15, 8, 22, 1, 6490.00),
(16, 9, 20, 1, 32000.00),
(17, 10, 20, 1, 32000.00),
(18, 11, 18, 1, 9900.00),
(19, 12, 1, 1, 12990.00),
(20, 13, 4, 1, 34900.00),
(21, 14, 2, 1, 5000.00),
(22, 14, 1, 1, 12990.00),
(23, 15, 1, 1, 12990.00);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `article` varchar(100) DEFAULT NULL,
  `insulation` varchar(255) DEFAULT NULL,
  `temp_range` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `title`, `name`, `price`, `description`, `category`, `size`, `material`, `article`, `insulation`, `temp_range`, `stock`, `is_active`, `created_at`) VALUES
(1, 'Puffer Jacket \"Onyx Black\"', 'Puffer Jacket \"Onyx Black\"', 12990.00, 'Этот мужской пуховик — идеальный выбор для холодных дней. Выполнен из высококачественного прочного материала, который отлично защищает от ветра и влаги. Современный крой и глубокий черный цвет делают его универсальным элементом гардероба.', 'puffer', 'S,M,L,XL', 'Нейлон 100%', 'FF-0932', 'Эко-пух', 'до -25°C', 6, 1, '2026-04-16 15:02:10'),
(2, 'Longsleeve', 'Longsleeve', 5000.00, 'Этот лонгслив выполнен из мягкого и комфортного материала, идеально подходящего для повседневной носки. Стильный дизайн с классическими горизонтальными полосками в насыщенном бордовом цвете с серыми линиями делает его универсальным и легко сочетаемым с любой одеждой.', 'longsleeve', 'S,M,L,XL', 'Хлопок', 'FF-0939', NULL, NULL, 13, 1, '2026-04-16 15:02:10'),
(4, 'Кроссовки Dior Homme High Top', 'Кроссовки Dior Homme High Top', 34900.00, 'Эти кроссовки Dior Homme High Top Shoes выполнены в стильном и современном дизайне. Они представляют собой высокие кеды с кожаным верхом в темно-сером оттенке, что придает им элегантный и универсальный вид.', 'sneakers', '40,41,42,43,44,45', 'Кожа, замша', 'FF-0932', NULL, NULL, 2, 1, '2026-04-16 15:02:10'),
(5, 'Jeans', 'Jeans', 4990.00, 'Редкие и стильные мужские джинсы. Эта модель сочетает в себе классический крой и современные детали. Идеально подходят для создания как повседневного, так и более строгого образа.', 'bottoms', 'S,M,L,XL', 'Деним', 'FF-0930', NULL, NULL, 11, 1, '2026-04-16 15:02:10'),
(6, 'Puffer Dolce Gabbana', 'Puffer Dolce Gabbana', 24900.00, 'Этот пуховик от Dolce & Gabbana выполнен из гладкого, блестящего материала черного цвета, придающего ему стильный и современный вид. Он обладает объемной, просторной конструкцией с горизонтальными стежками, создающими классический утепляющий эффект. Пуховик оснащен капюшоном для защиты от ветра и осадков, а также застегивается на прочную двунаправленную молнию, что обеспечивает комфорт и удобство использования.', 'Winter Collection 2026', 'S,M,L,XL', 'Нейлон 100%', 'FF-0931', 'Эко-пух', 'до -25°C', 10, 1, '2026-04-16 15:05:18'),
(7, 'Ремень Dior', 'Ремень Dior', 10900.00, 'Этот ремень Christian Dior выполнен из гладкой черной кожи высокой качества, придающей ему элегантный и классический вид. Основной акцент — металлическая пряжка в золотистом цвете с характерной формой, которая является узнаваемым элементом бренда Dior. Ремень выглядит стильным и универсальным, отлично подходит как для деловых, так и для повседневных образов.', 'Summer Collection 2026', '', 'Кожа', 'FF-0975', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(8, 'If Six Was Nine', 'If Six Was Nine', 9490.00, 'Эта лимитированная архивная кофта от If Six Was Nine выполнена в классическом стиле с акцентом на минимализм и качество. Она выполнена из мягкого, прочного черного материала, создающего ощущение комфорта и долговечности. Модель имеет свободный, чуть oversize крой с длинными рукавами и простым, аккуратным силуэтом.', 'Autumn Collection 2026', 'S,M,L,XL', 'Хлопок', 'FF-0910', NULL, NULL, 9, 1, '2026-04-16 15:05:18'),
(9, 'Martin Rose Jacket', 'Martin Rose Jacket', 27900.00, 'Эта куртка Martin Rose — стильная и яркая вещь в спортивном стиле. Она выполнена из легкого, прочного материала в бежевом цвете с яркими контрастными вставками: на плечах и рукавах присутствуют черные и красные элементы, создающие динамичный и привлекательный дизайн. Куртка застегивается на молнию, которая скрыта под застежкой с клапаном. На груди расположена небольшая брендовая нашивка, а на рукаве есть дополнительно логотип или надпись, придающие изделию индивидуальность.', 'Autumn Collection 2026', 'S,M,L,XL', 'Мембрана', 'FF-0922', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(10, 'Puffer No Faith Studios', 'Puffer No Faith Studios', 18500.00, 'Этот пуховик от No Faith Studios выполнен в классическом черном цвете, что делает его универсальным и стильным. Он обладает коротким, объемным силуэтом с горизонтальной стежкой, создающей эффект сегментированного утепления. Пуховик оснащен просторным капюшоном, который защищает от ветра и снега, а также застегивается на надежную молнию по всему фронту.', 'Winter Collection 2026', 'S,M,L,XL', 'Нейлон 100%', 'FF-0940', 'Эко-пух', 'до -25°C', 10, 1, '2026-04-16 15:05:18'),
(11, 'Number Nine Jacket', 'Number Nine Jacket', 21500.00, 'Эта куртка Number Nine выполнена из легкого, водоотталкивающего материала серого цвета. Она имеет свободный крой с эластичными манжетами и нижней кромкой, что обеспечивает комфорт и хорошую посадку. На спине размещен крупный дизайн: в центре изображена гитара-капля, окруженная кругом из текста «Number Nine», выполненного в белом цвете, и внутри — рукописное слово «Number».', 'Autumn Collection 2026', 'S,M,L,XL', 'Мембрана', 'FF-0921', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(12, 'Pants grey', 'Pants grey', 5900.00, 'Эти штаны Ralph Lauren — это комфортные спортивные брюки. Они выполнены из мягкого, приятного на ощупь светло-серого материала с легким текстурированным узором. У них есть эластичный пояс с завязками, что обеспечивает хорошую посадку и комфорт при ношении.', 'Summer Collection 2026', 'S,M,L,XL', 'Хлопок', 'FF-0938', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(13, 'Andrew Mackenzie 2004 \"Embrace the lions\"', 'Andrew Mackenzie 2004 \"Embrace the lions\"', 5490.00, 'Этот лонгслив Andrew Mackenzie 2004 \"Embrace the lions\" — это уникальное и выразительное изделие с ярким дизайном. Он выполнен из мягкой ткани с классическими горизонтальными черно-желтыми полосами, создающими эффект пчелиных сот или полосатого тигрового принта. На передней части расположены несколько крупных, разноцветных и стилизованных изображений львов или львиных голов, выполненных в графическом стиле, что добавляет изделию выразительности и символики.', 'Summer Collection 2026', 'S,M,L,XL', 'Хлопок', 'FF-0978', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(14, 'Ремень Balenciaga', 'Ремень Balenciaga', 8900.00, 'Этот ремень Balenciaga выполнен из высококачественной 100% кожаной основы, что обеспечивает его прочность и долговечность. Он имеет классический черный цвет, что делает его универсальным и легко сочетаемым с разными стилями одежды. Основной акцент — крупная металлическая пряжка с двойным дизайном в серебристом цвете, на которой нанесено название бренда Balenciaga, что придает ремню современный и статусный вид.', 'Summer Collection 2026', '', 'Кожа', 'FF-0976', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(15, 'Очки Dolce Gabbana', 'Очки Dolce Gabbana', 11500.00, 'Эти очки Dolce & Gabbana — это стильный и выразительный аксессуар, выполненный в современном дизайне. Они имеют крупную оправу с металлическим каркасом, который придает им прочность и элегантность. Стекла темные с синеватым оттенком, обеспечивают защиту от солнца и создают эффект таинственности и шикарности. Верхняя часть оправы украшена тонкими декоративными элементами, что подчеркивает брендовый стиль и внимание к деталям.', 'Summer Collection 2026', '', NULL, 'FF-0955', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(16, 'Очки Gucci Gg1805', 'Очки Gucci Gg1805', 8900.00, 'Эти стильные очки сочетают в себе элегантность и современный дизайн. Корпус выполнен из прочного металла с золотой отделкой, что придает им роскошный и изысканный вид. Темные стекла обеспечивают отличную защиту от яркого солнца и добавляют образу нотки загадочности и шика. Удобные заушники с аккуратной детализацией делают их комфортными для ношения в течение всего дня.', 'Summer Collection 2026', '', NULL, 'FF-0952', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(18, 'Очки Prada', 'Очки Prada', 9900.00, 'Prada — это сочетание современного стиля и высокой моды. Они выполнены в классическом черном цвете с крупной прямоугольной оправой, которая придает образу выразительность и элегантность. Тонкие стекла с защитой от солнца дополнены функцией polarized, обеспечивая четкое и комфортное зрение в яркую погоду. На дужках аккуратно нанесен логотип Prada, что подчеркивает брендовый статус и качество изделия.', 'Summer Collection 2026', '', NULL, 'FF-0954', NULL, NULL, 9, 1, '2026-04-16 15:05:18'),
(19, 'Кеды Saint Laurent', 'Кеды Saint Laurent', 29900.00, 'Эти кеды от Saint Laurent выполнены из текстиля, что придает им легкий и современный вид. Верхняя часть обуви — классическая спортивная модель с шнуровкой, выполненная в чистом белом цвете, что делает их универсальными и легко сочетаемыми с разными образами. На боковой стороне есть логотип Saint Laurent, выполненный в черном цвете, что добавляет брендинга и стильности \nВыберите размер', 'Summer Collection 2026', '40,41,42,43,44,45', 'Текстиль', 'FF-0977', NULL, NULL, 10, 1, '2026-04-16 15:05:18'),
(20, 'Saint Laurent', 'Saint Laurent', 32000.00, 'Эта стильная ветровка — идеальный выбор для защиты от ветра и легкого дождя в прохладную погоду. Выполнена из прочного и водоотталкивающего материала, что обеспечивает надежную защиту и комфорт.', 'Autumn Collection 2026', 'S,M,L,XL', 'Мембрана', 'FF-0924', NULL, NULL, 8, 1, '2026-04-16 15:05:18'),
(22, 'Gr-Project', 'gr-project', 6490.00, 'Эта Zip худи Project Gr Army — стильная и практичная вещь для тех, кто ценит комфорт и современный дизайн. Выполнен из мягкого и приятного на ощупь материала, идеально подходит для повседневной носки и активных дней. Спереди расположен удобный застежка на молнии, а капюшон с регулируемым шнурком обеспечивает дополнительную защиту и комфорт.', 'Autumn Collection 2026', 'S,M,L,XL', 'Хлопок', 'FF-0935', NULL, NULL, 9, 1, '2026-04-16 15:05:18'),
(23, 'Mastermind', 'mastermind', 11900.00, 'Эта Zip худи Project Gr Army — стильная и практичная вещь для тех, кто ценит комфорт и современный дизайн. Выполнен из мягкого и приятного на ощупь материала, идеально подходит для повседневной носки и активных дней. Спереди расположен удобный застежка на молнии, а капюшон с регулируемым шнурком обеспечивает дополнительную защиту и комфорт.', 'Autumn Collection 2026', 'S,M,L,XL', 'Хлопок', 'FF-0915', NULL, NULL, 3, 1, '2026-04-16 15:05:18');

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `image_path`, `is_main`, `sort_order`) VALUES
(1, 1, 'jacket/puffer.jpg', 'jacket/puffer.jpg', 1, 1),
(2, 1, 'jacket/puffer2.jpg', 'jacket/puffer2.jpg', 0, 2),
(3, 1, 'jacket/puffer3.jpg', 'jacket/puffer3.jpg', 0, 3),
(4, 2, 'img/longsleeve.jpg', 'img/longsleeve.jpg', 1, 1),
(5, 2, 'img/longsleeve2.jpg', 'img/longsleeve2.jpg', 0, 2),
(6, 2, 'img/longsleeve3.jpg', 'img/longsleeve3.jpg', 0, 3),
(9, 4, 'img/sneakers.jpg', 'img/sneakers.jpg', 1, 1),
(10, 4, 'img/sneakers2.jpg', 'img/sneakers2.jpg', 0, 2),
(11, 4, 'img/sneakers3.jpg', 'img/sneakers3.jpg', 0, 3),
(12, 5, 'img/jeansman.jpg', 'img/jeansman.jpg', 1, 1),
(13, 5, 'img/jeansman2.jpg', 'img/jeansman2.jpg', 0, 2),
(14, 5, 'img/jeansman3.jpg', 'img/jeansman3.jpg', 0, 3),
(15, 6, 'jacket/dolcegabbanapuffer.jpg', 'jacket/dolcegabbanapuffer.jpg', 1, 0),
(16, 6, 'jacket/dolcegabbanapuffer2.jpg', 'jacket/dolcegabbanapuffer2.jpg', 0, 1),
(17, 6, 'jacket/dolcegabbanapuffer3.jpg', 'jacket/dolcegabbanapuffer3.jpg', 0, 2),
(18, 7, 'accessories/diorbelt.jpg', 'accessories/diorbelt.jpg', 1, 0),
(19, 7, 'accessories/diorbelt2.jpg', 'accessories/diorbelt2.jpg', 0, 1),
(20, 7, 'accessories/diorbelt3.jpg', 'accessories/diorbelt3.jpg', 0, 2),
(21, 8, 'img/If_Six_Was_Nine.jpg', 'img/If_Six_Was_Nine.jpg', 1, 0),
(22, 8, 'img/If_Six_Was_Nine2.jpg', 'img/If_Six_Was_Nine2.jpg', 0, 1),
(23, 9, 'img/MRjacket.jpg', 'img/MRjacket.jpg', 1, 0),
(24, 9, 'img/MRjacket2.jpg', 'img/MRjacket2.jpg', 0, 1),
(25, 9, 'img/MRjacket3.jpg', 'img/MRjacket3.jpg', 0, 2),
(26, 10, 'jacket/No-faith-studios.jpg', 'jacket/No-faith-studios.jpg', 1, 0),
(27, 10, 'jacket/No-faith-studios2.jpg', 'jacket/No-faith-studios2.jpg', 0, 1),
(28, 10, 'jacket/No-faith-studios2.jpg', 'jacket/No-faith-studios2.jpg', 0, 2),
(29, 10, 'jacket/No-faith-studios3.jpg', 'jacket/No-faith-studios3.jpg', 0, 3),
(30, 11, 'img/NNjacket.jpg', 'img/NNjacket.jpg', 1, 0),
(31, 11, 'img/NNjacket2.jpg', 'img/NNjacket2.jpg', 0, 1),
(32, 11, 'img/NNjacket3.jpg', 'img/NNjacket3.jpg', 0, 2),
(33, 12, 'img/pants.jpg', 'img/pants.jpg', 1, 0),
(34, 12, 'img/pants2.jpg', 'img/pants2.jpg', 0, 1),
(35, 13, 'img/andrewmackenzielong.jpg', 'img/andrewmackenzielong.jpg', 1, 0),
(36, 13, 'img/andrewmackenzielong2.jpg', 'img/andrewmackenzielong2.jpg', 0, 1),
(37, 13, 'img/andrewmackenzielong3.jpg', 'img/andrewmackenzielong3.jpg', 0, 2),
(38, 14, 'accessories/balenciagabelt.jpg', 'accessories/balenciagabelt.jpg', 1, 0),
(39, 14, 'accessories/balenciagabelt2.jpg', 'accessories/balenciagabelt2.jpg', 0, 1),
(40, 14, 'accessories/balenciagabelt3.jpg', 'accessories/balenciagabelt3.jpg', 0, 2),
(41, 15, 'accessories/dolcegabbana.jpg', 'accessories/dolcegabbana.jpg', 1, 0),
(42, 15, 'accessories/dolcegabbana2.jpg', 'accessories/dolcegabbana2.jpg', 0, 1),
(43, 15, 'accessories/dolcegabbana3.jpg', 'accessories/dolcegabbana3.jpg', 0, 2),
(44, 16, 'accessories/ochki.jpg', 'accessories/ochki.jpg', 1, 0),
(45, 16, 'accessories/ochki2.jpg', 'accessories/ochki2.jpg', 0, 1),
(46, 16, 'accessories/ochki3.jpg', 'accessories/ochki3.jpg', 0, 2),
(49, 18, 'accessories/prada.jpg', 'accessories/prada.jpg', 1, 0),
(50, 18, 'accessories/prada2.jpg', 'accessories/prada2.jpg', 0, 1),
(51, 18, 'accessories/prada3.jpg', 'accessories/prada3.jpg', 0, 2),
(52, 19, 'img/shoes.jpg', 'img/shoes.jpg', 1, 0),
(53, 19, 'img/shoes2.jpg', 'img/shoes2.jpg', 0, 1),
(54, 19, 'img/shoes3.jpg', 'img/shoes3.jpg', 0, 2),
(55, 20, 'img/vetrovka.jpg', 'img/vetrovka.jpg', 1, 0),
(56, 20, 'img/vetrovka2.jpg', 'img/vetrovka2.jpg', 0, 1),
(57, 20, 'img/vetrovka3.jpg', 'img/vetrovka3.jpg', 0, 2),
(59, 22, 'img/ziphoddie.jpg', 'img/ziphoddie.jpg', 1, 0),
(60, 22, 'img/ziphoddie2.jpg', 'img/ziphoddie2.jpg', 0, 1),
(61, 22, 'img/ziphoddie3.jpg', 'img/ziphoddie3.jpg', 0, 2),
(62, 23, 'img/zipmastermind.jpg', 'img/zipmastermind.jpg', 1, 0),
(63, 23, 'img/zipmastermind2.jpg', 'img/zipmastermind2.jpg', 0, 1),
(64, 23, 'img/zipmastermind3.jpg', 'img/zipmastermind3.jpg', 0, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `is_admin`, `phone`, `address`) VALUES
(4, 'admin', 'admin@gmail.com', '$2y$10$JbkbF7F9S5eRVkST8pYDFOHHNw1MNJ.8HGp/yPnB4O1WaGlDIlCNe', '2026-04-18 06:35:31', 1, NULL, NULL),
(5, 'user', 'user@gmail.com', '$2y$10$Wjc0ZOxTO/U4XWcXO.XbH.ZQ7EEx1W7qYesdrOErmLOjsE1LxNAm2', '2026-04-18 06:57:04', 1, '321312321', '123123213');

-- --------------------------------------------------------

--
-- Структура таблицы `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 4, 1, '2026-04-18 09:13:59');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Индексы таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
