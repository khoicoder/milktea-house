-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 02, 2026 lúc 04:29 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `milktea_house`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Trà sữa', '2026-03-17 03:56:27'),
(2, 'Trà trái cây', '2026-03-17 03:56:27'),
(3, 'Đá xay', '2026-03-17 03:56:27'),
(4, 'Topping', '2026-03-17 03:56:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` enum('fixed','percentage') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(12,0) DEFAULT 0,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order_value`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 'CHAOBAN', 'fixed', 10000.00, 50000, 100, 0, NULL, NULL, 1, '2026-03-29 09:54:33'),
(2, 'MILKTEA20', 'percentage', 20.00, 100000, 50, 0, NULL, NULL, 1, '2026-03-29 09:54:33'),
(3, 'FREE5K', 'fixed', 5000.00, 0, 999, 0, NULL, NULL, 1, '2026-03-29 09:54:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `change_qty` int(11) NOT NULL,
  `type` enum('reserve','release','deduct') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `name`) VALUES
(1, '001_init_tables.php'),
(2, '002_seed_data.php'),
(3, '003_add_notifications.php'),
(4, '004_update_users_table.php'),
(5, '005_support_multiple_contact.php'),
(6, '006_update_orders_table.php'),
(7, '007_add_coupons.php'),
(8, '008_payment_waiting.php'),
(9, '009_add_product_size_to_order_items.php');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `is_read`, `created_at`, `type`, `ref_id`) VALUES
(8, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #80 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:12:14', NULL, NULL),
(9, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #81 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:16:45', NULL, NULL),
(10, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #82 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:22:42', NULL, NULL),
(11, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #83 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:26:02', NULL, NULL),
(12, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #84 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:40:04', NULL, NULL),
(13, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #85 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:43:12', NULL, NULL),
(14, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #86 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:50:00', NULL, NULL),
(15, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #87 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 18:58:08', NULL, NULL),
(16, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #88 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:03:00', NULL, NULL),
(17, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #89 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:31:35', NULL, NULL),
(18, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #90 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:32:56', NULL, NULL),
(19, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #91 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:41:01', NULL, NULL),
(20, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #92 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:41:23', NULL, NULL),
(21, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #93 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:43:18', NULL, NULL),
(22, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #94 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:47:37', NULL, NULL),
(23, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #95 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:48:05', NULL, NULL),
(24, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #96 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 19:49:31', NULL, NULL),
(25, 12, 'Đơn hàng đã hết hạn', 'Đơn hàng #97 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-20 20:09:59', NULL, NULL),
(28, NULL, 'dsdssd', 'dsdsds', 'file:///D:/z7641073947898_ff23682deaa66a03e812dc6299556653.jpg', 0, '2026-03-21 08:42:48', NULL, NULL),
(29, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:53:36', NULL, NULL),
(30, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:53:36', NULL, NULL),
(31, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:53:36', NULL, NULL),
(32, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:53:37', NULL, NULL),
(33, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:00', NULL, NULL),
(34, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:01', NULL, NULL),
(35, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:01', NULL, NULL),
(36, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:01', NULL, NULL),
(37, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:01', NULL, NULL),
(38, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:01', NULL, NULL),
(39, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:01', NULL, NULL),
(40, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:02', NULL, NULL),
(41, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 14:54:02', NULL, NULL),
(42, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user id :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:00:47', NULL, NULL),
(43, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user id :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:00:48', NULL, NULL),
(44, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user id :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:00:48', NULL, NULL),
(45, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user id :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:00:48', NULL, NULL),
(46, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user id :1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:00:48', NULL, NULL),
(47, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:49', NULL, NULL),
(48, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:49', NULL, NULL),
(49, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:49', NULL, NULL),
(50, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:50', NULL, NULL),
(51, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:50', NULL, NULL),
(52, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:50', NULL, NULL),
(53, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Pudding trứng\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:51', NULL, NULL),
(54, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Pudding trứng\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:51', NULL, NULL),
(55, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Pudding trứng\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:51', NULL, NULL),
(56, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Pudding trứng\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:51', NULL, NULL),
(57, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Pudding trứng\"', 'admin/pages/products.php', 0, '2026-03-27 15:00:51', NULL, NULL),
(58, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:10', NULL, NULL),
(59, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:10', NULL, NULL),
(60, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:10', NULL, NULL),
(61, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:10', NULL, NULL),
(62, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:11', NULL, NULL),
(63, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:11', NULL, NULL),
(64, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:11', NULL, NULL),
(65, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:11', NULL, NULL),
(67, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:13', NULL, NULL),
(68, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:13', NULL, NULL),
(69, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:13', NULL, NULL),
(70, NULL, '⚠️ Thiếu hàng', 'User đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:01:13', NULL, NULL),
(73, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:35', NULL, NULL),
(74, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:35', NULL, NULL),
(75, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:35', NULL, NULL),
(76, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:35', NULL, NULL),
(77, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:37', NULL, NULL),
(78, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:37', NULL, NULL),
(79, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:37', NULL, NULL),
(80, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:37', NULL, NULL),
(81, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:37', NULL, NULL),
(82, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:37', NULL, NULL),
(83, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(84, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(85, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(86, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(87, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(88, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(89, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(90, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:38', NULL, NULL),
(91, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(92, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(93, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(94, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(95, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(96, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(97, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(98, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(99, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(100, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(101, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(102, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(103, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(104, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:39', NULL, NULL),
(105, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(106, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(107, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(108, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(109, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(110, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(111, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(112, NULL, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:08:40', NULL, NULL),
(113, NULL, '⚠️ Thiếu hàng', 'User ID: 1 đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:14:36', NULL, NULL),
(114, NULL, '⚠️ Thiếu hàng', 'User ID: 1 đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:14:47', NULL, NULL),
(115, NULL, '⚠️ Thiếu hàng', 'User ID: 1 đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:14:47', NULL, NULL),
(116, NULL, '⚠️ Thiếu hàng', 'User ID: 1 đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:14:47', NULL, NULL),
(117, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:23:19', NULL, NULL),
(118, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:23:19', NULL, NULL),
(119, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:23:19', NULL, NULL),
(120, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:23:20', NULL, NULL),
(121, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:23:22', NULL, NULL),
(122, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:23:22', NULL, NULL),
(123, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:24:28', NULL, NULL),
(124, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:24:28', NULL, NULL),
(125, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:26:24', NULL, NULL),
(126, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:26:25', NULL, NULL),
(127, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:26:25', NULL, NULL),
(128, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:28:15', NULL, NULL),
(129, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:28:16', NULL, NULL),
(130, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:28:16', NULL, NULL),
(131, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:32:46', 'stock', NULL),
(132, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:32:47', 'stock', NULL),
(133, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:32:48', 'stock', NULL),
(134, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:32:54', 'stock', NULL),
(135, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:32:54', 'stock', NULL),
(137, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Nha đam\" (ID: 25) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:32:55', 'stock', NULL),
(138, 1, '⚠️ Thiếu hàng', 'User ID: 1 đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-27 15:36:05', 'stock', 24),
(139, 2, 'hz', 'test', 'file:///D:/z7641073947898_ff23682deaa66a03e812dc6299556653.jpg', 0, '2026-03-27 15:37:43', NULL, NULL),
(140, NULL, 'su kien moi', 'su kien moi', 'file:///D:/z7641073947898_ff23682deaa66a03e812dc6299556653.jpg', 0, '2026-03-27 15:37:59', NULL, NULL),
(141, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:28', 'stock', NULL),
(142, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:28', 'stock', NULL),
(143, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:28', 'stock', NULL),
(144, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:28', 'stock', NULL),
(145, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:29', 'stock', NULL),
(146, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:29', 'stock', NULL),
(147, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:29', 'stock', NULL),
(148, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:29', 'stock', NULL),
(149, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:31', 'stock', NULL),
(150, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:31', 'stock', NULL),
(151, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:31', 'stock', NULL),
(152, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:32', 'stock', NULL),
(153, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:32', 'stock', NULL),
(154, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:32', 'stock', NULL),
(155, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:32', 'stock', NULL),
(156, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:36', 'stock', NULL),
(157, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:37', 'stock', NULL),
(158, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:37', 'stock', NULL),
(159, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:37', 'stock', NULL),
(160, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà xoài\" (ID: 10) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:37', 'stock', NULL),
(161, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà đào\" (ID: 9) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:45', 'stock', NULL),
(162, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà đào\" (ID: 9) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:45', 'stock', NULL),
(163, 1, '⚠️ Sản phẩm hết hàng', 'Sản phẩm \"Trà đào\" (ID: 9) đã hết hàng khi user ID: 1 thao tác.', 'admin/pages/products.php', 0, '2026-03-27 15:45:45', 'stock', NULL),
(164, 1, '⚠️ Thiếu hàng', 'User ID: 17 đang cố mua vượt tồn kho sản phẩm \"Thạch dừa\"', 'admin/pages/products.php', 0, '2026-03-28 06:59:56', 'stock', 24),
(165, 1, 'Đơn hàng đã hết hạn', 'Đơn hàng #109 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-29 09:57:42', NULL, NULL),
(166, 1, 'Đơn hàng đã hết hạn', 'Đơn hàng #110 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-29 09:58:18', NULL, NULL),
(167, 1, 'Đơn hàng đã hết hạn', 'Đơn hàng #111 chưa được thanh toán trong 1 giờ và đã bị hủy.', 'pages/orders.php', 0, '2026-03-29 10:01:41', NULL, NULL),
(168, 1, 'Đặt hàng thành công', 'Đơn hàng #112 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=112', 0, '2026-03-29 10:05:19', NULL, NULL),
(169, 1, 'Đặt hàng thành công', 'Đơn hàng #113 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=113', 0, '2026-03-29 10:08:18', NULL, NULL),
(170, 1, 'Thanh toán thành công', 'Đơn hàng #114 của bạn đã thanh toán thành công và đang chờ xác nhận.', 'pages/order_detail.php?id=114', 0, '2026-03-29 10:09:57', NULL, NULL),
(171, 1, 'Đặt hàng thành công', 'Đơn hàng #115 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=115', 0, '2026-03-29 10:12:21', NULL, NULL),
(172, 1, 'Đặt hàng thành công', 'Đơn hàng #116 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=116', 0, '2026-03-29 10:14:15', NULL, NULL),
(173, 1, 'Đặt hàng thành công', 'Đơn hàng #117 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=117', 0, '2026-03-29 10:22:54', NULL, NULL),
(174, 1, 'Đặt hàng thành công', 'Đơn hàng #118 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=118', 0, '2026-03-29 11:19:22', NULL, NULL),
(175, 19, 'Thanh toán thành công', 'Đơn hàng #119 của bạn đã thanh toán thành công và đang chờ xác nhận.', 'pages/order_detail.php?id=119', 0, '2026-03-29 11:30:57', NULL, NULL),
(176, 20, 'Đặt hàng thành công', 'Đơn hàng #120 của bạn đã được tiếp nhận (COD).', 'pages/order_detail.php?id=120', 0, '2026-04-02 13:24:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `total` decimal(12,0) DEFAULT NULL,
  `discount_amount` decimal(12,0) DEFAULT 0,
  `status` enum('pending','processing','shipping','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'bank_transfer',
  `payment_status` enum('pending','paid','expired') DEFAULT 'pending',
  `payment_expires_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `qr_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `coupon_id`, `total`, `discount_amount`, `status`, `created_at`, `name`, `phone`, `address`, `note`, `payment_method`, `payment_status`, `payment_expires_at`, `paid_at`, `expired_at`, `qr_content`) VALUES
(1, 2, NULL, 100000, 0, 'completed', '2026-03-09 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(2, 3, NULL, 42000, 0, 'completed', '2026-03-10 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(3, 4, NULL, 90000, 0, 'completed', '2026-03-10 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(5, 5, NULL, 96000, 0, 'completed', '2026-03-12 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(8, 2, NULL, 38000, 0, 'completed', '2026-03-14 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(9, 4, NULL, 88000, 0, 'completed', '2026-03-15 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(11, 5, NULL, 147107, 0, 'completed', '2026-03-02 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(12, 4, NULL, 37978, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(13, 2, NULL, 146369, 0, 'completed', '2026-03-07 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(14, 5, NULL, 124178, 0, 'completed', '2026-03-19 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(15, 4, NULL, 102285, 0, 'completed', '2026-03-10 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(16, 4, NULL, 30005, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(17, 3, NULL, 118741, 0, 'completed', '2026-03-04 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(18, 5, NULL, 49135, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(19, 5, NULL, 124816, 0, 'completed', '2026-03-06 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(20, 2, NULL, 138644, 0, 'completed', '2026-02-28 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(21, 3, NULL, 41514, 0, 'completed', '2026-03-03 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(22, 4, NULL, 57744, 0, 'completed', '2026-03-10 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(23, 5, NULL, 138694, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(24, 4, NULL, 56432, 0, 'completed', '2026-03-08 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(25, 3, NULL, 49868, 0, 'completed', '2026-03-07 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(26, 3, NULL, 65812, 0, 'completed', '2026-03-13 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(27, 3, NULL, 78513, 0, 'completed', '2026-03-11 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(28, 4, NULL, 64176, 0, 'completed', '2026-03-12 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(29, 3, NULL, 134234, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(30, 4, NULL, 125971, 0, 'completed', '2026-03-16 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(31, 5, NULL, 93434, 0, 'completed', '2026-03-04 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(32, 3, NULL, 113494, 0, 'completed', '2026-03-01 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(33, 3, NULL, 76814, 0, 'completed', '2026-03-19 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(34, 4, NULL, 80798, 0, 'completed', '2026-03-02 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(35, 3, NULL, 69855, 0, 'completed', '2026-03-12 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(36, 2, NULL, 46917, 0, 'completed', '2026-03-01 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(37, 5, NULL, 67284, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(38, 3, NULL, 35976, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(39, 3, NULL, 101436, 0, 'completed', '2026-03-02 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(40, 4, NULL, 63587, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(41, 3, NULL, 68236, 0, 'completed', '2026-03-07 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(42, 5, NULL, 142078, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(43, 4, NULL, 43898, 0, 'completed', '2026-03-16 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(44, 4, NULL, 35716, 0, 'completed', '2026-03-03 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(45, 4, NULL, 112077, 0, 'completed', '2026-03-10 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(46, 4, NULL, 81863, 0, 'completed', '2026-03-06 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(47, 3, NULL, 43012, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(48, 5, NULL, 30690, 0, 'completed', '2026-03-03 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(49, 5, NULL, 93880, 0, 'completed', '2026-03-04 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(50, 4, NULL, 81801, 0, 'completed', '2026-03-05 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(51, 5, NULL, 49340, 0, 'completed', '2026-03-16 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(52, 5, NULL, 58599, 0, 'completed', '2026-03-18 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(53, 5, NULL, 115767, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(54, 3, NULL, 119191, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(55, 2, NULL, 49575, 0, 'completed', '2026-03-06 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(56, 2, NULL, 147854, 0, 'completed', '2026-03-06 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(57, 4, NULL, 49861, 0, 'completed', '2026-03-19 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(59, 2, NULL, 79848, 0, 'completed', '2026-03-17 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(60, 2, NULL, 113568, 0, 'completed', '2026-03-06 17:00:00', NULL, NULL, NULL, NULL, 'bank_transfer', 'pending', NULL, NULL, NULL, NULL),
(80, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:09:13', 'Khôi Lê Minh', '03626595', 'dsdsds', 'fsadsd', 'bank_transfer', 'expired', '2026-03-21 02:09:13', NULL, '2026-03-21 01:12:14', 'MILKTEA HOUSE|ORDER:80|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(81, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:12:28', 'Khôi Lê Minh', '03723232323', 'dsdsds', 'dsadd', 'bank_transfer', 'expired', '2026-03-21 02:12:28', NULL, '2026-03-21 01:16:45', 'MILKTEA HOUSE|ORDER:81|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(82, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:17:01', 'Khôi Lê Minh', '0362629334', 'dsdsds', 'dsdsd', 'bank_transfer', 'expired', '2026-03-21 02:17:01', NULL, '2026-03-21 01:22:42', 'MILKTEA HOUSE|ORDER:82|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(83, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:26:01', 'Khôi Lê Minh', '0837232323', 'dsdsds', 'SDASDADS', 'bank_transfer', 'expired', '2026-03-21 02:26:01', NULL, '2026-03-21 01:26:02', 'MILKTEA HOUSE|ORDER:83|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(84, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:40:02', 'Khôi Lê Minh', '0837232323', 'dsds', 'sdasdsad', 'bank_transfer', 'expired', '2026-03-21 02:40:02', NULL, '2026-03-21 01:40:04', 'MILKTEA HOUSE|ORDER:84|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(85, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:43:11', 'Khôi Lê Minh', '0837232323', 'dsdsds', 'dsadasdsa', 'bank_transfer', 'expired', '2026-03-21 02:43:11', NULL, '2026-03-21 01:43:12', 'MILKTEA HOUSE|ORDER:85|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(86, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:49:59', 'dsfdsadd', '123233', 'dsadsad', 'sấdadasd', 'bank_transfer', 'expired', '2026-03-21 02:49:59', NULL, '2026-03-21 01:50:00', 'MILKTEA HOUSE|ORDER:86|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(87, 12, NULL, 20000, 0, 'pending', '2026-03-20 18:58:07', 'tes', '1123', 'ds', 'dsds', 'bank_transfer', 'expired', '2026-03-21 02:58:07', NULL, '2026-03-21 01:58:08', 'MILKTEA HOUSE|ORDER:87|USER:12|AMOUNT:20000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(96, 12, NULL, 86000, 0, 'pending', '2026-03-20 19:49:30', 'Khôi Lê Minh', '0837232323', 'dsdsds', 'dsdsd', 'bank_transfer', 'pending', '2026-03-21 04:01:55', NULL, '2026-03-21 02:49:31', 'MILKTEA HOUSE|ORDER:96|USER:12|AMOUNT:86000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(98, 12, NULL, 24000, 0, 'cancelled', '2026-03-20 20:10:11', 'Khôi Lê Minh', '123233', 'ds', 'dsds', 'bank_transfer', 'expired', '2026-03-21 04:10:11', NULL, '2026-03-21 12:41:11', 'MILKTEA HOUSE|ORDER:98|USER:12|AMOUNT:24000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(100, 12, NULL, 34000, 0, 'pending', '2026-03-20 20:21:26', 'Khôi Lê Minh', '0837232323', 'dsd', 'dsdsds', 'bank_transfer', '', '2026-03-20 22:21:26', NULL, NULL, 'MILKTEA HOUSE|ORDER:100|USER:12|AMOUNT:34000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(101, 12, NULL, 34000, 0, 'pending', '2026-03-20 20:28:02', 'Khôi Lê Minh', '32', '3232', 'dsdsad', 'bank_transfer', '', '2026-03-20 22:28:02', NULL, NULL, 'MILKTEA HOUSE|ORDER:101|USER:12|AMOUNT:34000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(104, 14, NULL, 156000, 0, '', '2026-03-21 08:36:37', 'Khôi Lê Minh', '0837232323', 'dsds', 'dssd', 'fake_paypal', '', '2026-03-21 10:36:37', NULL, NULL, 'MILKTEA HOUSE|ORDER:104|USER:14|AMOUNT:156000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(105, 1, NULL, 93000, 0, '', '2026-03-21 08:54:56', 'Khôi Lê Minh', '0837232323', 'dsdsds', 'ggf', 'bank_transfer', '', '2026-03-21 10:54:56', NULL, NULL, 'MILKTEA HOUSE|ORDER:105|USER:1|AMOUNT:93000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(106, 16, NULL, 18000, 0, '', '2026-03-27 16:05:58', 'khôi', '0362629669', 'Cần thơ, Ninh Kiều', '', 'bank_transfer', '', '2026-03-27 18:05:58', NULL, NULL, 'MILKTEA HOUSE|ORDER:106|USER:16|AMOUNT:18000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(107, 17, NULL, 39000, 0, '', '2026-03-28 07:22:04', 'daz', '0362629669', 'Vĩnh Long', '', 'fake_paypal', '', '2026-03-28 09:22:04', NULL, NULL, 'MILKTEA HOUSE|ORDER:107|USER:17|AMOUNT:39000|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI'),
(109, 1, NULL, 9000, 0, 'cancelled', '2026-03-29 09:57:41', 'khôi', '0362629669', 'CT', '', 'cod', 'expired', '2026-03-29 12:57:41', NULL, '2026-03-29 16:57:42', 'MILKTEA HOUSE|ORDER:109|AMOUNT:9000'),
(111, 1, NULL, 9000, 0, 'cancelled', '2026-03-29 10:01:40', 'khôi', '0362629669', 'CT', '', 'cod', 'expired', '2026-03-29 13:01:40', NULL, '2026-03-29 17:01:41', 'MILKTEA HOUSE|ORDER:111|AMOUNT:9000'),
(112, 1, NULL, 21000, 0, 'cancelled', '2026-03-29 10:05:19', 'khôi', '0362629669', 'CT', '', 'cod', '', NULL, NULL, NULL, 'REF177477871341'),
(113, 1, NULL, 12000, 0, 'cancelled', '2026-03-29 10:08:18', 'khôi', '0362629669', 'CT', '', 'cod', '', NULL, NULL, NULL, 'REF177477889248'),
(115, 1, NULL, 12000, 0, 'pending', '2026-03-29 10:12:21', 'khôi', '0362629669', 'CT', '', 'cod', '', NULL, NULL, NULL, 'REF177477913850'),
(116, 1, NULL, 12000, 0, 'pending', '2026-03-29 10:14:15', 'khôi', '0362629669', 'CT', '', 'cod', '', NULL, NULL, NULL, 'REF177477925233'),
(117, 1, NULL, 12000, 0, 'shipping', '2026-03-29 10:22:54', 'khôi', '0362629669', 'CT', '', 'cod', '', NULL, NULL, NULL, 'REF177477977138'),
(118, 1, NULL, 24000, 0, 'completed', '2026-03-29 11:19:22', 'khôi', '0362629669', 'CT', '', 'cod', '', NULL, NULL, NULL, 'REF177478316052'),
(119, 19, NULL, 10000, 0, 'processing', '2026-03-29 11:30:57', 'Nha đam', '0362222222', 'zzz', 'VZ', 'fake_paypal', 'paid', NULL, '2026-03-29 18:30:57', NULL, 'REF177478385135'),
(120, 20, NULL, 12000, 0, 'completed', '2026-04-02 13:24:37', 'Nha đam', '0362222222', 'zzz', '', 'cod', '', NULL, NULL, NULL, 'REF177513627334');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_size_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_size_id`, `qty`, `price`) VALUES
(1, 1, 8, NULL, 2, 45000),
(2, 1, 21, NULL, 1, 10000),
(3, 2, 1, NULL, 1, 30000),
(4, 2, 23, NULL, 1, 12000),
(5, 3, 16, NULL, 2, 45000),
(6, 4, 2, NULL, 1, 35000),
(7, 4, 21, NULL, 2, 10000),
(8, 5, 18, NULL, 2, 48000),
(9, 6, 9, NULL, 2, 28000),
(10, 7, 8, NULL, 1, 45000),
(11, 7, 22, NULL, 2, 10000),
(12, 8, 3, NULL, 1, 38000),
(13, 9, 20, NULL, 2, 44000),
(14, 10, 5, NULL, 1, 42000),
(15, 10, 24, NULL, 2, 9000),
(16, 1, 8, NULL, 2, 45000),
(17, 1, 21, NULL, 1, 10000),
(18, 2, 1, NULL, 1, 30000),
(19, 2, 23, NULL, 1, 12000),
(20, 3, 16, NULL, 2, 45000),
(21, 4, 2, NULL, 1, 35000),
(22, 4, 21, NULL, 2, 10000),
(23, 5, 18, NULL, 2, 48000),
(24, 6, 9, NULL, 2, 28000),
(25, 7, 8, NULL, 1, 45000),
(26, 7, 22, NULL, 2, 10000),
(27, 8, 3, NULL, 1, 38000),
(28, 9, 20, NULL, 2, 44000),
(29, 74, 22, NULL, 3, 10000),
(30, 75, 23, NULL, 3, 12000),
(31, 76, 23, NULL, 4, 12000),
(32, 77, 23, NULL, 4, 12000),
(33, 78, 23, NULL, 3, 12000),
(34, 79, 23, NULL, 4, 12000),
(35, 80, 22, NULL, 2, 10000),
(36, 81, 22, NULL, 2, 10000),
(37, 82, 22, NULL, 2, 10000),
(38, 83, 22, NULL, 2, 10000),
(39, 84, 22, NULL, 2, 10000),
(40, 85, 22, NULL, 2, 10000),
(41, 86, 22, NULL, 2, 10000),
(42, 87, 22, NULL, 2, 10000),
(43, 88, 22, NULL, 2, 10000),
(44, 89, 17, NULL, 2, 45000),
(45, 89, 22, NULL, 5, 10000),
(46, 90, 17, NULL, 2, 45000),
(47, 90, 22, NULL, 5, 10000),
(48, 91, 17, NULL, 2, 45000),
(49, 91, 22, NULL, 5, 10000),
(50, 92, 17, NULL, 2, 45000),
(51, 92, 22, NULL, 5, 10000),
(52, 93, 17, NULL, 2, 45000),
(53, 93, 22, NULL, 5, 10000),
(54, 94, 22, NULL, 5, 10000),
(55, 95, 22, NULL, 5, 10000),
(56, 96, 22, NULL, 5, 10000),
(57, 96, 23, NULL, 3, 12000),
(58, 97, 23, NULL, 3, 12000),
(59, 98, 23, NULL, 2, 12000),
(60, 99, 22, NULL, 1, 10000),
(61, 99, 23, NULL, 2, 12000),
(62, 100, 22, NULL, 1, 10000),
(63, 100, 23, NULL, 2, 12000),
(64, 101, 22, NULL, 1, 10000),
(65, 101, 23, NULL, 2, 12000),
(66, 102, 23, NULL, 4, 12000),
(67, 102, 25, NULL, 1, 9000),
(68, 103, 22, NULL, 3, 10000),
(69, 104, 23, NULL, 13, 12000),
(70, 105, 23, NULL, 7, 12000),
(71, 105, 24, NULL, 1, 9000),
(72, 106, 24, NULL, 2, 9000),
(73, 107, 23, NULL, 1, 12000),
(74, 107, 24, NULL, 3, 9000),
(77, 109, 24, NULL, 1, 9000),
(78, 110, 24, NULL, 1, 9000),
(79, 111, 24, NULL, 1, 9000),
(80, 112, 23, NULL, 1, 12000),
(81, 112, 24, NULL, 1, 9000),
(82, 113, 23, NULL, 1, 12000),
(83, 114, 18, NULL, 1, 48000),
(84, 115, 23, NULL, 1, 12000),
(85, 116, 23, NULL, 1, 12000),
(86, 117, 23, NULL, 1, 12000),
(87, 118, 23, NULL, 2, 12000),
(88, 119, 22, NULL, 1, 10000),
(89, 120, 23, NULL, 1, 12000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `method` varchar(50) DEFAULT 'bank_transfer',
  `amount` decimal(12,0) NOT NULL,
  `status` enum('pending','paid','failed','expired') DEFAULT 'pending',
  `transaction_code` varchar(255) DEFAULT NULL,
  `qr_content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment_waiting`
--

CREATE TABLE `payment_waiting` (
  `id` int(11) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `order_data` text NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reserved_stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `description`, `category_id`, `stock`, `created_at`, `reserved_stock`) VALUES
(1, 'Trà sữa truyền thống', 30000.00, 'tra-sua-truyen-thong.jpg', 'Trà sữa truyền thống thơm béo, vị ngọt vừa phải, dễ uống và phù hợp với mọi lứa tuổi.', 1, 9, '2026-03-15 14:18:34', 0),
(2, 'Trà sữa khoai môn', 35000.00, 'tra-sua-khoai-mon.jpg', 'Trà sữa khoai môn với hương vị ngọt dịu, béo nhẹ và màu tím đặc trưng hấp dẫn.', 1, 8, '2026-03-15 14:18:34', 0),
(3, 'Trà sữa matcha', 38000.00, 'tra-sua-matcha.jpg', 'Trà sữa matcha đậm vị trà xanh Nhật Bản, thanh mát và thơm nhẹ.', 1, 10, '2026-03-15 14:18:34', 0),
(4, 'Trà sữa socola', 40000.00, 'tra-sua-socola.jpg', 'Trà sữa socola đậm đà, ngọt béo, dành cho tín đồ chocolate.', 1, 10, '2026-03-15 14:18:34', 0),
(5, 'Trà sữa caramel', 42000.00, 'tra-sua-caramel.jpg', 'Trà sữa caramel ngọt thơm, béo nhẹ, hậu vị dễ chịu.', 1, 9, '2026-03-15 14:18:34', 0),
(6, 'Trà sữa ô long', 39000.00, 'tra-sua-o-long.jpg', 'Trà sữa ô long đậm vị trà, thơm đặc trưng, hậu ngọt nhẹ.', 1, 10, '2026-03-15 14:18:34', 0),
(7, 'Trà sữa hoa nhài', 37000.00, 'tra-sua-hoa-nhai.jpg', 'Trà sữa hoa nhài thơm dịu, thanh nhẹ, rất dễ uống.', 1, 10, '2026-03-15 14:18:34', 0),
(8, 'Trà sữa trân châu', 45000.00, 'tra-sua-tran-chau.jpg', 'Trà sữa trân châu truyền thống kết hợp topping dai ngon hấp dẫn.', 1, 7, '2026-03-15 14:18:34', 0),
(9, 'Trà đào', 28000.00, 'tra-dao.jpg', 'Trà đào thanh mát, có miếng đào thật, vị ngọt nhẹ dễ uống.', 2, 0, '2026-03-15 14:19:18', 0),
(10, 'Trà xoài', 30000.00, 'tra-xoai.jpg', 'Trà xoài thơm ngon, vị trái cây rõ rệt, tươi mát.', 2, 0, '2026-03-15 14:18:34', 0),
(11, 'Trà dâu', 30000.00, 'tra-dau.jpg', 'Trà dâu chua nhẹ, ngọt thanh, màu sắc bắt mắt.', 2, 10, '2026-03-15 14:18:34', 0),
(12, 'Trà vải', 30000.00, 'tra-vai.jpg', 'Trà vải thơm dịu, ngọt nhẹ, giải nhiệt hiệu quả.', 2, 10, '2026-03-15 14:18:34', 0),
(13, 'Trà chanh', 25000.00, 'tra-chanh.jpg', 'Trà chanh chua ngọt thanh mát, giải khát cực tốt.', 2, 10, '2026-03-15 14:18:34', 0),
(14, 'Trà chanh dây', 32000.00, 'tra-chanh-day.jpg', 'Trà chanh dây đậm vị, thơm mạnh, cực kỳ sảng khoái.', 2, 10, '2026-03-15 14:18:34', 0),
(15, 'Trà việt quất', 35000.00, 'tra-viet-quat.jpg', 'Trà việt quất chua nhẹ, thơm mát, màu tím đẹp mắt.', 2, 10, '2026-03-15 14:18:34', 0),
(16, 'Matcha đá xay', 45000.00, 'matcha-da-xay.jpg', 'Matcha đá xay mát lạnh, đậm vị trà xanh và béo nhẹ.', 3, 9, '2026-03-15 14:18:34', 0),
(17, 'Socola đá xay', 45000.00, 'socola-da-xay.jpg', 'Socola đá xay đậm đà, béo ngậy, rất được yêu thích.', 3, 10, '2026-03-15 14:18:34', 0),
(18, 'Oreo đá xay', 48000.00, 'oreo-da-xay.jpg', 'Oreo đá xay thơm mùi bánh, ngọt béo, cực kỳ hấp dẫn.', 3, 7, '2026-03-15 14:18:34', 0),
(19, 'Dâu đá xay', 43000.00, 'dau-da-xay.jpg', 'Dâu đá xay chua ngọt, mát lạnh, rất dễ uống.', 3, 10, '2026-03-15 14:18:34', 0),
(20, 'Xoài đá xay', 44000.00, 'xoai-da-xay.jpg', 'Xoài đá xay thơm ngon, vị ngọt đậm, tươi mát.', 3, 10, '2026-03-15 14:18:34', 0),
(21, 'Trân châu đen', 10000.00, 'tran-chau-den.jpg', 'Trân châu đen dai mềm, ngọt nhẹ, topping quen thuộc.', 4, 99, '2026-03-15 14:18:34', 0),
(22, 'Trân châu trắng', 10000.00, 'tran-chau-trang.jpg', 'Trân châu trắng giòn dai, vị nhẹ, phù hợp nhiều loại đồ uống.', 4, 96, '2026-03-15 14:18:34', 0),
(23, 'Pudding trứng', 12000.00, 'pudding-trung.jpg', 'Pudding trứng mềm mịn, béo thơm, tan ngay trong miệng.', 4, 38, '2026-03-15 14:18:34', 1),
(24, 'Thạch dừa', 9000.00, 'thach-dua.jpg', 'Thạch dừa thanh mát, thơm nhẹ, giúp đồ uống dễ uống hơn.', 4, 7, '2026-03-15 14:18:34', 6),
(25, 'Nha đam', 9000.00, 'nha-dam.jpg', 'Nha đam giòn sần sật, mát lạnh, tốt cho sức khỏe.', 4, 3, '2026-03-15 14:18:34', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size_id`, `price`, `stock`) VALUES
(1, 1, 1, 21103, 17),
(2, 1, 2, 38796, 14),
(3, 1, 3, 47197, 8),
(4, 2, 1, 27578, 9),
(5, 2, 2, 39225, 22),
(6, 2, 3, 47075, 22),
(7, 3, 1, 21410, 7),
(8, 3, 2, 33123, 7),
(9, 3, 3, 46642, 24),
(10, 4, 1, 28091, 8),
(11, 4, 2, 33968, 14),
(12, 4, 3, 42578, 21),
(13, 5, 1, 23276, 8),
(14, 5, 2, 39042, 24),
(15, 5, 3, 42315, 8),
(16, 6, 1, 22617, 19),
(17, 6, 2, 38884, 9),
(18, 6, 3, 45251, 23),
(19, 7, 1, 29819, 8),
(20, 7, 2, 39301, 7),
(21, 7, 3, 48364, 21),
(22, 8, 1, 25205, 8),
(23, 8, 2, 33625, 10),
(24, 8, 3, 42028, 9),
(25, 9, 1, 25918, 9),
(26, 9, 2, 34188, 12),
(27, 9, 3, 46314, 5),
(28, 10, 1, 22288, 6),
(29, 10, 2, 36643, 7),
(30, 10, 3, 45631, 14),
(31, 11, 1, 27208, 8),
(32, 11, 2, 36274, 18),
(33, 11, 3, 44332, 8),
(34, 12, 1, 25858, 12),
(35, 12, 2, 32315, 24),
(36, 12, 3, 41260, 19),
(37, 13, 1, 23105, 11),
(38, 13, 2, 37671, 21),
(39, 13, 3, 47809, 14),
(40, 14, 1, 29337, 11),
(41, 14, 2, 37133, 18),
(42, 14, 3, 41494, 20),
(43, 15, 1, 24196, 20),
(44, 15, 2, 36273, 21),
(45, 15, 3, 41429, 10),
(46, 16, 1, 20693, 13),
(47, 16, 2, 30316, 21),
(48, 16, 3, 49778, 13),
(49, 17, 1, 22941, 7),
(50, 17, 2, 37886, 15),
(51, 17, 3, 43366, 6),
(52, 18, 1, 22984, 11),
(53, 18, 2, 36409, 10),
(54, 18, 3, 44915, 17),
(55, 19, 1, 25710, 5),
(56, 19, 2, 34260, 5),
(57, 19, 3, 49559, 17),
(58, 20, 1, 23283, 19),
(59, 20, 2, 36344, 5),
(60, 20, 3, 40996, 14),
(61, 21, 1, 21832, 13),
(62, 21, 2, 35841, 17),
(63, 21, 3, 44492, 11),
(64, 22, 1, 22788, 13),
(65, 22, 2, 32542, 5),
(66, 22, 3, 43205, 16),
(67, 23, 1, 28026, 12),
(68, 23, 2, 33652, 20),
(69, 23, 3, 47150, 10),
(70, 24, 1, 22993, 17),
(71, 24, 2, 32525, 12),
(72, 24, 3, 41035, 13),
(73, 25, 1, 26943, 10),
(74, 25, 2, 32609, 14),
(75, 25, 3, 47084, 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `name`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(3, 1, 'khoi', NULL, 5, 'Trà sữa rất ngon, đậm vị 👍', '2026-03-18 03:15:00'),
(4, 2, 'an', NULL, 4, 'Khá ổn, hơi ngọt chút', '2026-03-18 03:20:00'),
(5, 3, 'minh', NULL, 5, 'Matcha thơm, uống rất thích', '2026-03-18 03:25:00'),
(6, 4, 'linh', NULL, 4, 'Socola ok nhưng hơi béo', '2026-03-18 03:30:00'),
(7, 5, 'khoi', NULL, 5, 'Caramel ngon xuất sắc', '2026-03-18 03:35:00'),
(8, 6, 'an', NULL, 4, 'Ô long thơm, hậu vị tốt', '2026-03-18 04:00:00'),
(9, 7, 'minh', NULL, 5, 'Hoa nhài rất thơm, dễ uống', '2026-03-18 04:05:00'),
(10, 8, 'linh', NULL, 5, 'Trân châu dai ngon cực', '2026-03-18 04:10:00'),
(11, 9, 'khoi', NULL, 4, 'Trà đào mát, ổn áp', '2026-03-18 04:20:00'),
(12, 10, 'an', NULL, 5, 'Xoài thơm, vị đậm', '2026-03-18 04:25:00'),
(13, 11, 'minh', NULL, 4, 'Dâu hơi chua nhẹ', '2026-03-18 04:30:00'),
(14, 12, 'linh', NULL, 5, 'Vải ngon, dễ uống', '2026-03-18 04:35:00'),
(15, 13, 'khoi', NULL, 4, 'Trà chanh giải khát tốt', '2026-03-18 05:00:00'),
(16, 14, 'an', NULL, 5, 'Chanh dây đậm vị 👍', '2026-03-18 05:05:00'),
(17, 15, 'minh', NULL, 5, 'Việt quất rất thơm', '2026-03-18 05:10:00'),
(18, 16, 'linh', NULL, 5, 'Matcha đá xay mát lạnh', '2026-03-18 05:30:00'),
(19, 17, 'khoi', NULL, 4, 'Socola đá xay ngon', '2026-03-18 05:35:00'),
(20, 18, 'an', NULL, 5, 'Oreo béo, rất thích', '2026-03-18 05:40:00'),
(21, 19, 'minh', NULL, 4, 'Dâu đá xay ổn', '2026-03-18 05:45:00'),
(22, 20, 'linh', NULL, 5, 'Xoài đá xay tuyệt vời', '2026-03-18 05:50:00'),
(23, 21, 'khoi', NULL, 5, 'Trân châu đen dai ngon', '2026-03-18 06:00:00'),
(24, 22, 'an', NULL, 4, 'Trân châu trắng giòn', '2026-03-18 06:05:00'),
(25, 23, 'minh', NULL, 5, 'Pudding mềm mịn', '2026-03-18 06:10:00'),
(26, 24, 'linh', NULL, 4, 'Thạch dừa mát', '2026-03-18 06:15:00'),
(27, 25, 'khoi', NULL, 5, 'Nha đam giòn ngon', '2026-03-18 06:20:00'),
(28, 20, 'Khôi Lê Minh', NULL, 3, 'fdasdsd', '2026-03-21 06:03:16'),
(29, 23, 'admin', NULL, 5, 'tesr', '2026-03-21 06:07:21'),
(30, 23, 'admin', NULL, 3, 'test', '2026-03-21 06:07:29'),
(31, 7, 'Teest111', NULL, 1, 'binh thuong', '2026-03-21 06:42:17'),
(32, 24, 'admin', NULL, 2, 'khoi test', '2026-03-21 07:03:27'),
(33, 16, 'admin', NULL, 5, 'rf', '2026-03-21 08:55:26'),
(34, 24, 'tezzz', NULL, 5, 'zz', '2026-04-02 13:37:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sizes`
--

CREATE TABLE `sizes` (
  `id` int(11) NOT NULL,
  `name` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sizes`
--

INSERT INTO `sizes` (`id`, `name`, `created_at`) VALUES
(1, 'S', '2026-04-02 13:43:25'),
(2, 'M', '2026-04-02 13:43:25'),
(3, 'L', '2026-04-02 13:43:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('guest','user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `address`, `password`, `display_name`, `avatar`, `role`, `created_at`, `reset_token`, `reset_token_expire`) VALUES
(1, 'admin', 'admin@gmail.com', '[\"0362629669\"]', '[\"CT\"]', '$2y$10$LUgGOk8ana2/TXLCcpkMuuUlzpHnmTZkOWqtWS2KmRPLWQPFZIroy', 'khôi', NULL, 'admin', '2026-03-17 15:28:52', NULL, NULL),
(2, 'khoi', 'khoi@gmail.com', NULL, NULL, '123', NULL, NULL, 'user', '2026-03-17 15:28:52', NULL, NULL),
(3, 'an', 'an@gmail.com', NULL, NULL, '123', NULL, NULL, 'user', '2026-03-17 15:28:52', NULL, NULL),
(4, 'minh', 'minh@gmail.com', NULL, NULL, '123', NULL, NULL, 'user', '2026-03-17 15:28:52', NULL, NULL),
(5, 'linh', 'linh@gmail.com', NULL, NULL, '123', NULL, NULL, 'user', '2026-03-17 15:28:52', NULL, NULL),
(8, 'tes1', 'da@gmail.com', NULL, NULL, '$2y$10$jNSPwdDDpG6yIFHO100RVeC9kQo4NLFsTSTPOdOzCQD3vyXmF7D12', NULL, NULL, 'user', '2026-03-19 07:33:37', NULL, NULL),
(9, 'sArahed00@', 'nq5494155@gmail.com', NULL, NULL, '$2y$10$TY8TAb2XESbpYze2qR5E8Oo8M32cWU.wHTaviwMijwRHoKLUdBYhm', NULL, NULL, 'user', '2026-03-19 08:33:22', NULL, NULL),
(12, 'sarahed02', 'b@gmail.com', NULL, NULL, '$2y$10$pHDDmFnuFvlnlGq0vFSFUeRDLUvWMI.zkmKc1DSeZuUs5UgSA.332', NULL, NULL, 'user', '2026-03-20 17:59:48', NULL, NULL),
(14, 'khaa', 'nguyenhuynhkha0203@gmail.com', '[\"0362629669\"]', '[]', '$2y$10$TILv9Lg/VK1ZSP22V54G7uuhPjGsjCeYxwNkc2Syg.iFNyD7FdAJK', '', 'u14_1774082259.png', 'user', '2026-03-21 08:21:42', 'a00048315e590475c6044173bc390fe390adb64539f26255d4ac466a84ad1ebd', '2026-03-21 10:35:31'),
(15, 'zzzzz', 'nq5494150@gmail.com', NULL, NULL, '$2y$10$oloFtsaknZzQ3dYaia/24uXGJ0SyL3NnzyN74i299k263BQi.ocWq', NULL, NULL, 'user', '2026-03-21 08:23:04', '015e1917f39868d4c6ef48515f929c89c4a61d7419f6b3a5ccc3b3b9ede7b6fb', '2026-03-27 16:01:30'),
(16, 'khoi1', 'nq5494151@gmail.com', '[\"0362629669\"]', '[\"Cần thơ, Ninh Kiều\"]', '$2y$10$OaYbBL/vV/CF2K4fuvmdUenOUEJAR0xHAu0JJFz8FmBjZ.zAoRxBW', 'khôi', NULL, 'user', '2026-03-27 16:03:38', NULL, NULL),
(17, 'khoi111', 'a@gmail.com', '[\"0362629669\"]', '[\"Vĩnh Long\"]', '$2y$10$.G7Lh1.4mF8ySju.5t7uS.C7NiGyYnDr.WVWs8kskGJt/zASJ8hDq', 'daz', NULL, 'user', '2026-03-28 06:58:17', NULL, NULL),
(18, 'khz', 'a1@gmail.com', '[\"0362629669\"]', '[\"ninh kieu\"]', '$2y$10$9X8OD5h80ySwXPy1sAGmCetbRN1tgIC4sYxz/wHhoM/.dWV9mCldq', '', NULL, 'user', '2026-03-28 09:20:31', NULL, NULL),
(19, 'tez', 'z@gmail.com', '[\"0362222222\",\"312321321\",\"fdfdffdf\"]', '[\"zzz\"]', '$2y$10$LbQMoLws.BHcbjJhz83uqucUalGMDfIsEVa4UPZmX50fhnamKauyO', '', NULL, 'user', '2026-03-29 11:29:24', NULL, NULL),
(20, 'tezzz', '1z@gmail.com', NULL, NULL, '$2y$10$oJ45ElI2/PZdpsnzQ1nwc.8rse6mJrSYzcU2fMDblVZ3kh72yOawG', NULL, NULL, 'user', '2026-04-02 13:24:23', NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inventory_product` (`product_id`),
  ADD KEY `fk_inventory_order` (`order_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `idx_orders_date` (`created_at`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_product` (`product_id`),
  ADD KEY `fk_items_product_size` (`product_size_id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_order` (`order_id`);

--
-- Chỉ mục cho bảng `payment_waiting`
--
ALTER TABLE `payment_waiting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `payment_waiting`
--
ALTER TABLE `payment_waiting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `fk_inventory_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_product_size` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_sizes_ibfk_2` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
