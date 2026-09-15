-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 01:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `municipal_kk_profiling`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_usage_log`
--

CREATE TABLE `ai_usage_log` (
  `id` int(11) NOT NULL,
  `endpoint` varchar(100) NOT NULL DEFAULT 'generateContent',
  `model` varchar(100) DEFAULT NULL,
  `prompt_tokens` int(11) DEFAULT 0,
  `response_tokens` int(11) DEFAULT 0,
  `total_tokens` int(11) DEFAULT 0,
  `http_status` int(5) DEFAULT 200,
  `success` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_usage_log`
--

INSERT INTO `ai_usage_log` (`id`, `endpoint`, `model`, `prompt_tokens`, `response_tokens`, `total_tokens`, `http_status`, `success`, `created_at`) VALUES
(1, 'generateContent', 'gemini-flash-latest', 124, 2, 480, 200, 1, '2026-05-05 21:23:35'),
(2, 'generateContent', 'gemini-flash-latest', 124, 2, 466, 200, 1, '2026-05-05 21:23:50'),
(3, 'generateContent', 'gemini-flash-latest', 111, 2, 466, 200, 1, '2026-05-05 21:23:53'),
(4, 'generateContent', 'gemini-flash-latest', 111, 2, 345, 200, 1, '2026-05-05 21:23:54'),
(5, 'generateContent', 'gemini-flash-latest', 119, 2, 385, 200, 1, '2026-05-05 21:23:59'),
(6, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:24:01'),
(7, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:24:03'),
(8, 'generateContent', 'gemini-flash-latest', 119, 2, 466, 200, 1, '2026-05-05 21:24:03'),
(9, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:24:05'),
(10, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:24:05'),
(11, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-05 21:24:07'),
(12, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:24:07'),
(13, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:24:09'),
(14, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-05 21:24:10'),
(15, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:24:11'),
(16, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:24:12'),
(17, 'generateContent', 'gemini-flash-latest', 118, 0, 118, 429, 0, '2026-05-05 21:24:15'),
(18, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:24:15'),
(19, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:24:16'),
(20, 'generateContent', 'gemini-flash-latest', 127, 0, 127, 429, 0, '2026-05-05 21:24:16'),
(21, 'generateContent', 'gemini-flash-latest', 118, 0, 118, 429, 0, '2026-05-05 21:24:18'),
(22, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:24:18'),
(23, 'generateContent', 'gemini-flash-latest', 120, 1, 253, 200, 1, '2026-05-05 21:24:22'),
(24, 'generateContent', 'gemini-flash-latest', 112, 2, 391, 200, 1, '2026-05-05 21:24:23'),
(25, 'generateContent', 'gemini-flash-latest', 113, 2, 280, 200, 1, '2026-05-05 21:24:27'),
(26, 'generateContent', 'gemini-flash-latest', 119, 2, 439, 200, 1, '2026-05-05 21:24:41'),
(27, 'generateContent', 'gemini-flash-latest', 124, 2, 424, 200, 1, '2026-05-05 21:24:52'),
(28, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:24:55'),
(29, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:24:57'),
(30, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:24:59'),
(31, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:25:01'),
(32, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:25:03'),
(33, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:25:05'),
(34, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:25:09'),
(35, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:25:11'),
(36, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:25:12'),
(37, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:25:14'),
(38, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:25:16'),
(39, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:25:18'),
(40, 'generateContent', 'gemini-flash-latest', 118, 2, 537, 200, 1, '2026-05-05 21:25:24'),
(41, 'generateContent', 'gemini-flash-latest', 124, 2, 419, 200, 1, '2026-05-05 21:25:32'),
(42, 'generateContent', 'gemini-flash-latest', 111, 2, 444, 200, 1, '2026-05-05 21:25:42'),
(43, 'generateContent', 'gemini-flash-latest', 119, 2, 477, 200, 1, '2026-05-05 21:25:56'),
(44, 'generateContent', 'gemini-flash-latest', 119, 2, 502, 200, 1, '2026-05-05 21:26:13'),
(45, 'generateContent', 'gemini-flash-latest', 112, 2, 249, 200, 1, '2026-05-05 21:26:17'),
(46, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:26:19'),
(47, 'generateContent', 'gemini-flash-latest', 118, 2, 519, 200, 1, '2026-05-05 21:26:24'),
(48, 'generateContent', 'gemini-flash-latest', 125, 2, 409, 200, 1, '2026-05-05 21:26:31'),
(49, 'generateContent', 'gemini-flash-latest', 112, 2, 364, 200, 1, '2026-05-05 21:26:42'),
(50, 'generateContent', 'gemini-flash-latest', 118, 0, 118, 429, 0, '2026-05-05 21:26:44'),
(51, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:26:46'),
(52, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:26:48'),
(53, 'generateContent', 'gemini-flash-latest', 117, 0, 117, 429, 0, '2026-05-05 21:26:50'),
(54, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:26:52'),
(55, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:26:55'),
(56, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:26:57'),
(57, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:26:59'),
(58, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:27:01'),
(59, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:27:03'),
(60, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:27:05'),
(61, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-05 21:27:07'),
(62, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:27:11'),
(63, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:27:13'),
(64, 'generateContent', 'gemini-flash-latest', 118, 0, 118, 429, 0, '2026-05-05 21:27:14'),
(65, 'generateContent', 'gemini-flash-latest', 127, 0, 127, 429, 0, '2026-05-05 21:27:16'),
(66, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:27:18'),
(67, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:27:20'),
(68, 'generateContent', 'gemini-flash-latest', 124, 0, 124, 429, 0, '2026-05-05 21:27:22'),
(69, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:27:26'),
(70, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:27:28'),
(71, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:27:30'),
(72, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:27:31'),
(73, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:27:33'),
(74, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:27:35'),
(75, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:27:37'),
(76, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:27:41'),
(77, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:27:43'),
(78, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:27:45'),
(79, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:27:46'),
(80, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:27:48'),
(81, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:27:50'),
(82, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:27:52'),
(83, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:27:56'),
(84, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:27:58'),
(85, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:27:59'),
(86, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:28:01'),
(87, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:28:03'),
(88, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:28:05'),
(89, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:28:07'),
(90, 'generateContent', 'gemini-flash-latest', 127, 0, 127, 429, 0, '2026-05-05 21:28:11'),
(91, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:28:13'),
(92, 'generateContent', 'gemini-flash-latest', 117, 0, 117, 429, 0, '2026-05-05 21:28:14'),
(93, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:28:16'),
(94, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:28:18'),
(95, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:28:20'),
(96, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-05 21:28:22'),
(97, 'generateContent', 'gemini-flash-latest', 112, 2, 382, 200, 1, '2026-05-05 21:28:38'),
(98, 'generateContent', 'gemini-flash-latest', 124, 0, 124, 429, 0, '2026-05-05 21:28:40'),
(99, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:28:44'),
(100, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:28:46'),
(101, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:28:47'),
(102, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:28:49'),
(103, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:28:51'),
(104, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:28:52'),
(105, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:28:54'),
(106, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:28:58'),
(107, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:28:59'),
(108, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:29:01'),
(109, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:29:03'),
(110, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:29:05'),
(111, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:29:06'),
(112, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:29:08'),
(113, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:29:12'),
(114, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:29:14'),
(115, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:29:15'),
(116, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:29:17'),
(117, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:29:19'),
(118, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:29:20'),
(119, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:29:22'),
(120, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:29:26'),
(121, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:29:28'),
(122, 'generateContent', 'gemini-flash-latest', 118, 0, 118, 429, 0, '2026-05-05 21:29:29'),
(123, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:29:31'),
(124, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:29:33'),
(125, 'generateContent', 'gemini-flash-latest', 117, 0, 117, 429, 0, '2026-05-05 21:29:35'),
(126, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:29:36'),
(127, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:29:40'),
(128, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:29:42'),
(129, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:29:43'),
(130, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:29:45'),
(131, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:29:46'),
(132, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:29:48'),
(133, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-05 21:29:50'),
(134, 'generateContent', 'gemini-flash-latest', 128, 0, 128, 429, 0, '2026-05-05 21:29:54'),
(135, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:29:55'),
(136, 'generateContent', 'gemini-flash-latest', 118, 0, 118, 429, 0, '2026-05-05 21:29:57'),
(137, 'generateContent', 'gemini-flash-latest', 127, 0, 127, 429, 0, '2026-05-05 21:29:59'),
(138, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:30:01'),
(139, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:30:02'),
(140, 'generateContent', 'gemini-flash-latest', 124, 0, 124, 429, 0, '2026-05-05 21:30:04'),
(141, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:30:08'),
(142, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:09'),
(143, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:30:11'),
(144, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:30:13'),
(145, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:30:15'),
(146, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:17'),
(147, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:30:19'),
(148, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:30:23'),
(149, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:24'),
(150, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:30:26'),
(151, 'generateContent', 'gemini-flash-latest', 125, 0, 125, 429, 0, '2026-05-05 21:30:28'),
(152, 'generateContent', 'gemini-flash-latest', 113, 0, 113, 429, 0, '2026-05-05 21:30:29'),
(153, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:32'),
(154, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:30:33'),
(155, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:30:37'),
(156, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:39'),
(157, 'generateContent', 'gemini-flash-latest', 116, 0, 116, 429, 0, '2026-05-05 21:30:41'),
(158, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:30:43'),
(159, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:44'),
(160, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:46'),
(161, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-05 21:30:48'),
(162, 'generateContent', 'gemini-flash-latest', 127, 0, 127, 429, 0, '2026-05-05 21:30:51'),
(163, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:30:53'),
(164, 'generateContent', 'gemini-flash-latest', 117, 0, 117, 429, 0, '2026-05-05 21:30:55'),
(165, 'generateContent', 'gemini-flash-latest', 126, 0, 126, 429, 0, '2026-05-05 21:30:57'),
(166, 'generateContent', 'gemini-flash-latest', 114, 0, 114, 429, 0, '2026-05-05 21:30:58'),
(167, 'generateContent', 'gemini-flash-latest', 115, 0, 115, 429, 0, '2026-05-05 21:31:00'),
(168, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-05 21:31:02'),
(169, 'generateContent', 'gemini-flash-latest', 147, 2, 422, 200, 1, '2026-05-06 08:55:22'),
(170, 'generateContent', 'gemini-flash-latest', 144, 0, 144, 429, 0, '2026-05-06 08:55:24'),
(171, 'generateContent', 'gemini-flash-latest', 146, 0, 146, 429, 0, '2026-05-06 08:55:26'),
(172, 'generateContent', 'gemini-flash-latest', 156, 0, 156, 429, 0, '2026-05-06 08:55:28'),
(173, 'generateContent', 'gemini-flash-latest', 144, 0, 144, 429, 0, '2026-05-06 08:55:29'),
(174, 'generateContent', 'gemini-flash-latest', 144, 0, 144, 429, 0, '2026-05-06 08:55:31'),
(175, 'generateContent', 'gemini-flash-latest', 152, 0, 152, 429, 0, '2026-05-06 08:55:33'),
(176, 'generateContent', 'gemini-flash-latest', 129, 2, 396, 200, 1, '2026-05-06 09:57:51'),
(177, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-06 09:57:52'),
(178, 'generateContent', 'gemini-flash-latest', 124, 0, 124, 429, 0, '2026-05-06 09:57:54'),
(179, 'generateContent', 'gemini-flash-latest', 134, 0, 134, 429, 0, '2026-05-06 09:57:56'),
(180, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-06 09:57:58'),
(181, 'generateContent', 'gemini-flash-latest', 122, 0, 122, 429, 0, '2026-05-06 09:58:00'),
(182, 'generateContent', 'gemini-flash-latest', 130, 0, 130, 429, 0, '2026-05-06 09:58:01'),
(183, 'generateContent', 'gemini-flash-latest', 143, 83, 674, 200, 1, '2026-05-06 14:16:38'),
(184, 'generateContent', 'gemini-flash-latest', 123, 2, 713, 200, 1, '2026-05-11 10:58:26'),
(185, 'generateContent', 'gemini-flash-latest', 110, 2, 325, 200, 1, '2026-05-11 10:58:31'),
(186, 'generateContent', 'gemini-flash-latest', 118, 2, 418, 200, 1, '2026-05-11 10:58:36'),
(187, 'generateContent', 'gemini-flash-latest', 118, 2, 326, 200, 1, '2026-05-11 10:58:40'),
(188, 'generateContent', 'gemini-flash-latest', 111, 2, 334, 200, 1, '2026-05-11 10:58:44'),
(189, 'generateContent', 'gemini-flash-latest', 110, 2, 383, 200, 1, '2026-05-11 10:58:48'),
(190, 'generateContent', 'gemini-flash-latest', 123, 0, 123, 429, 0, '2026-05-11 10:58:51');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `actor_role`, `action`, `target_type`, `target_id`, `metadata`, `created_at`) VALUES
(1, 17, 'youth', 'Youth self-registered with full profile for approval', 'OSYProfile', 15, '{\"barangay\":\"Baga\",\"id_type\":\"\"}', '2026-05-11 10:58:20'),
(2, 1, 'lydo', 'Approved provider account', 'User', 18, '{\"status\":\"Active\",\"remark\":\"\"}', '2026-05-13 00:13:50'),
(3, 1, 'lydo', 'Approved provider account', 'User', 19, '{\"status\":\"Active\",\"remark\":\"\"}', '2026-05-13 00:15:06'),
(4, 1, 'lydo', 'Approved provider account', 'User', 34, '{\"status\":\"Active\",\"remark\":\"\"}', '2026-05-14 22:50:48'),
(5, 1, 'lydo', 'Approved provider account', 'User', 35, '{\"status\":\"Active\",\"remark\":\"\"}', '2026-05-14 22:50:50'),
(6, 26, 'sk_chairman', 'Approved youth verification', 'OSYProfile', 15, '{\"remark\":\"\"}', '2026-05-30 10:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_type` enum('admin','osy') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_type` enum('admin','osy') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sms_status` enum('none','pending','success','failed') DEFAULT 'none',
  `email_status` enum('none','pending','success','failed') DEFAULT 'none',
  `sms_error` text DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('Opportunity','Match','System','Reminder') DEFAULT 'System',
  `recipient_type` enum('All','OSY','Specific') DEFAULT 'All',
  `recipient_id` int(11) DEFAULT NULL,
  `status` enum('Sent','Read','Failed') DEFAULT 'Sent',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `recipient_type`, `recipient_id`, `status`, `created_by`, `created_at`) VALUES
(1, 'New Opportunity Posted', 'TESDA NCII Welding training is now available with 15 slots. Qualified candidates will be notified.', 'Opportunity', 'OSY', NULL, 'Sent', 1, '2026-05-05 21:20:51'),
(2, 'Skills Match Found', 'You have been matched with a job opportunity in Logistics!', 'Match', 'Specific', NULL, 'Sent', 2, '2026-05-05 21:20:51'),
(3, 'Training Started', 'Congratulations! You have been enrolled in the Basic Web Design course.', 'System', 'Specific', NULL, 'Read', 1, '2026-05-05 21:20:51'),
(4, 'Application Deadline Reminder', 'STEM University Grant applications close in 5 days. Apply now!', 'Reminder', 'OSY', NULL, 'Sent', 1, '2026-05-05 21:20:51'),
(5, 'Training Opportunity Available', 'A new training opportunity is now available for qualified youth in your area. Check the dashboard for updates.', 'Opportunity', 'OSY', NULL, 'Sent', 1, '2026-05-05 21:20:51'),
(6, 'Profile Reminder', 'Complete your youth profile and update your skills to improve your match eligibility for upcoming opportunities.', 'Reminder', 'OSY', NULL, 'Sent', 1, '2026-05-05 21:20:51'),
(7, 'training', 'Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.', 'System', 'Specific', NULL, 'Sent', 1, '2026-05-06 09:59:31'),
(8, 'haha', 'Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.', 'System', 'Specific', NULL, 'Sent', 1, '2026-05-06 10:01:05'),
(9, 'job aler', 'Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.', 'System', 'Specific', NULL, 'Sent', 1, '2026-05-06 10:14:15'),
(10, 'Provider Account Approved', 'Your provider registration has been active', 'System', 'Specific', 18, 'Sent', 1, '2026-05-13 00:13:50'),
(11, 'Provider Account Approved', 'Your provider registration has been active', 'System', 'Specific', 19, 'Sent', 1, '2026-05-13 00:15:06'),
(12, 'Provider Account Approved', 'Your provider registration has been active', 'System', 'Specific', 34, 'Sent', 1, '2026-05-14 22:50:48'),
(13, 'Provider Account Approved', 'Your provider registration has been active', 'System', 'Specific', 35, 'Sent', 1, '2026-05-14 22:50:50'),
(14, 'Profile Verification Approved', 'Your youth registration has been verified.', 'System', 'Specific', 17, 'Sent', 26, '2026-05-30 10:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `type` enum('SMS','Email','SMS/Email') DEFAULT 'SMS',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `name`, `subject`, `body`, `type`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Training Invitation', 'New Training Opportunity', 'Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.', 'SMS/Email', 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(2, 'Job Match Alert', 'Job Opportunity Found', 'Hello {{name}}, a new job opportunity at {{company}} matches your skills. Apply now through the Opportunity Hub!', 'Email', 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(3, 'Registration Confirmation', 'Welcome to Barangay OSY', 'Hi {{name}}, your profile has been successfully registered. You are now part of our skills matching program.', 'SMS', 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(4, 'Skill Upgrade Suggestion', 'Upskill Recommendation', 'Hi {{name}}, completing the {{course}} course can increase your matching score by {{percentage}}%. Check it out!', 'Email', 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('Job Opening','Vocational Training','Scholarship') NOT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `work_schedule` varchar(50) DEFAULT NULL,
  `experience_req` varchar(50) DEFAULT NULL,
  `training_provider` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `modality` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `compensation` varchar(100) DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `age_min` int(3) DEFAULT NULL,
  `age_max` int(3) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_slots` int(5) DEFAULT 10,
  `deadline` date DEFAULT NULL,
  `status` enum('Open','Closed','Pending') DEFAULT 'Open',
  `created_by` int(11) DEFAULT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `opportunities`
--

INSERT INTO `opportunities` (`id`, `title`, `type`, `employment_type`, `work_schedule`, `experience_req`, `training_provider`, `duration`, `modality`, `location`, `compensation`, `benefits`, `certification`, `description`, `total_slots`, `deadline`, `status`, `created_by`, `provider_id`, `created_at`, `updated_at`) VALUES
(1, 'TESDA NCII Cookery', 'Vocational Training', NULL, NULL, NULL, NULL, NULL, NULL, 'TESDA-MisOr Hub', NULL, 'Free training materials', 'National Certificate II (NCII)', 'Comprehensive culinary arts and food safety training program', 20, '2024-10-24', 'Open', 1, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(2, 'Logistics Assistant', 'Job Opening', NULL, NULL, NULL, NULL, NULL, NULL, 'Port Logistics Corp.', '₱14,500 - ₱16,000', 'Full HMO', NULL, 'Inventory management and logistics support', 5, '2024-11-05', 'Open', 2, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(3, 'STEM University Grant', 'Scholarship', NULL, NULL, NULL, NULL, NULL, NULL, 'City Education Board', NULL, 'Full tuition coverage', NULL, 'For students with grade 85+ and indigent status', 50, '2024-11-15', 'Open', 1, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(4, 'Basic Web Design', 'Vocational Training', NULL, NULL, NULL, NULL, NULL, NULL, 'Digital Arts Institute', NULL, 'Certificate of Completion', 'Certificate of Completion', 'UI/UX principles and Figma training', 12, '2024-10-01', 'Closed', 2, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(5, 'Welding Specialist - NC II', 'Vocational Training', NULL, NULL, NULL, NULL, NULL, NULL, 'TESDA-MisOr Hub', NULL, 'Tools provided', 'NC II Certification', 'Industrial-grade welding expertise for infrastructure projects', 15, '2024-11-30', 'Open', 1, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(6, 'BPO Customer Service', 'Job Opening', NULL, NULL, NULL, NULL, NULL, NULL, 'TechCorp Solutions', '₱18,000 - ₱22,000', 'Medical benefits, Meal allowance', NULL, 'Virtual customer support representative', 10, '2024-11-20', 'Open', 2, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(7, 'Automotive Technician', 'Job Opening', NULL, NULL, NULL, NULL, NULL, NULL, 'AutoWorks Ltd.', '₱16,000 - ₱20,000', 'HMO, Hazard pay', NULL, 'Vehicle maintenance and repair technician', 8, '2024-11-25', 'Open', 1, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(8, 'Computer Literacy & Basic IT', 'Vocational Training', NULL, NULL, NULL, NULL, NULL, NULL, 'Barangay Tech Center', NULL, 'Free', 'Completion Certificate', 'MS Office, Internet basics, Email management', 25, '2024-12-15', 'Open', 2, NULL, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(9, 'upskill', 'Vocational Training', NULL, NULL, NULL, NULL, NULL, NULL, 'baga, panaon', NULL, NULL, 'tesda nc2', 'naay allowanace 500 per day', 100, '2026-06-17', 'Open', 31, 31, '2026-06-17 23:22:16', '2026-06-17 23:30:57');

-- --------------------------------------------------------

--
-- Table structure for table `opportunity_required_skills`
--

CREATE TABLE `opportunity_required_skills` (
  `id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `skill` varchar(255) NOT NULL COMMENT 'Skill name or description',
  `importance_level` enum('Required','Preferred','Nice to have') DEFAULT 'Required',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunity_skills`
--

CREATE TABLE `opportunity_skills` (
  `opportunity_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `importance_level` enum('Required','Preferred','Nice to have') DEFAULT 'Required',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `osy_matches`
--

CREATE TABLE `osy_matches` (
  `id` int(11) NOT NULL,
  `osy_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `match_score` int(3) DEFAULT 0,
  `status` enum('Pending','Accepted','Rejected','In Progress','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `ai_insight` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `osy_matches`
--

INSERT INTO `osy_matches` (`id`, `osy_id`, `opportunity_id`, `match_score`, `status`, `notes`, `ai_insight`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 55, 'Accepted', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:23:54'),
(2, 2, 1, 45, 'Accepted', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:30:23'),
(3, 3, 8, 55, 'Accepted', NULL, NULL, '2026-05-05 21:20:51', '2026-05-06 13:37:14'),
(4, 4, 5, 35, 'Accepted', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:30:57'),
(5, 5, 5, 45, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:28:49'),
(6, 6, 5, 45, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:30:13'),
(7, 7, 5, 45, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:29:03'),
(8, 8, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:29:19'),
(9, 9, 8, 45, 'Accepted', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:29:36'),
(10, 10, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:29:48'),
(11, 11, 1, 45, 'Accepted', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:30:37'),
(12, 12, 5, 35, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:29:59'),
(13, 1, 7, 45, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:24:09'),
(14, 2, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:30:30'),
(15, 3, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:20:51', '2026-05-05 21:24:15'),
(16, 1, 1, 30, 'Pending', NULL, NULL, '2026-05-05 21:23:35', '2026-05-05 21:23:50'),
(17, 1, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:23:59', '2026-05-05 21:23:59'),
(18, 1, 5, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:01', '2026-05-05 21:24:05'),
(19, 1, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:03', '2026-05-05 21:24:07'),
(21, 1, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:07', '2026-05-05 21:24:10'),
(22, 3, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:12', '2026-05-05 21:24:16'),
(23, 3, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:15', '2026-05-05 21:24:18'),
(24, 3, 5, 5, 'Pending', NULL, NULL, '2026-05-05 21:24:16', '2026-05-05 21:24:22'),
(25, 3, 6, 80, 'Pending', NULL, NULL, '2026-05-05 21:24:18', '2026-05-05 21:24:27'),
(26, 3, 7, 25, 'Pending', NULL, NULL, '2026-05-05 21:24:23', '2026-05-05 21:28:38'),
(27, 5, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:52', '2026-05-05 21:28:44'),
(28, 5, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:55', '2026-05-05 21:28:46'),
(29, 5, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:24:57', '2026-05-05 21:28:47'),
(30, 5, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:01', '2026-05-05 21:28:51'),
(31, 5, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:03', '2026-05-05 21:28:52'),
(32, 5, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:05', '2026-05-05 21:28:54'),
(33, 7, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:09', '2026-05-05 21:28:58'),
(34, 7, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:11', '2026-05-05 21:28:59'),
(35, 7, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:12', '2026-05-05 21:29:01'),
(36, 7, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:16', '2026-05-05 21:29:05'),
(37, 7, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:18', '2026-05-05 21:29:06'),
(38, 7, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:24', '2026-05-05 21:29:08'),
(39, 8, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:32', '2026-05-05 21:29:12'),
(40, 8, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:42', '2026-05-05 21:29:14'),
(41, 8, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:25:56', '2026-05-05 21:29:15'),
(42, 8, 5, 45, 'Pending', NULL, NULL, '2026-05-05 21:26:13', '2026-05-05 21:29:17'),
(43, 8, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:19', '2026-05-05 21:29:20'),
(44, 8, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:24', '2026-05-05 21:29:22'),
(45, 9, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:31', '2026-05-05 21:29:26'),
(46, 9, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:42', '2026-05-05 21:29:28'),
(47, 9, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:44', '2026-05-05 21:29:29'),
(48, 9, 5, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:46', '2026-05-05 21:29:31'),
(49, 9, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:48', '2026-05-05 21:29:33'),
(50, 9, 7, 55, 'Pending', NULL, NULL, '2026-05-05 21:26:50', '2026-05-05 21:29:35'),
(51, 10, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:55', '2026-05-05 21:29:40'),
(52, 10, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:57', '2026-05-05 21:29:42'),
(53, 10, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:26:59', '2026-05-05 21:29:43'),
(54, 10, 5, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:01', '2026-05-05 21:29:45'),
(55, 10, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:03', '2026-05-05 21:29:46'),
(56, 10, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:07', '2026-05-05 21:29:50'),
(57, 12, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:11', '2026-05-05 21:29:54'),
(58, 12, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:13', '2026-05-05 21:29:55'),
(59, 12, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:14', '2026-05-05 21:29:57'),
(60, 12, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:18', '2026-05-05 21:30:01'),
(61, 12, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:20', '2026-05-05 21:30:02'),
(62, 12, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:22', '2026-05-05 21:30:04'),
(63, 6, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:26', '2026-05-05 21:30:08'),
(64, 6, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:28', '2026-05-05 21:30:09'),
(65, 6, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:30', '2026-05-05 21:30:11'),
(66, 6, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:33', '2026-05-05 21:30:15'),
(67, 6, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:35', '2026-05-05 21:30:17'),
(68, 6, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:37', '2026-05-05 21:30:19'),
(69, 2, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:43', '2026-05-05 21:30:24'),
(70, 2, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:45', '2026-05-05 21:30:26'),
(71, 2, 5, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:46', '2026-05-05 21:30:28'),
(72, 2, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:50', '2026-05-05 21:30:32'),
(73, 2, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:52', '2026-05-05 21:30:33'),
(74, 11, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:58', '2026-05-05 21:30:39'),
(75, 11, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:27:59', '2026-05-05 21:30:41'),
(76, 11, 5, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:01', '2026-05-05 21:30:43'),
(77, 11, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:03', '2026-05-05 21:30:44'),
(78, 11, 7, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:05', '2026-05-05 21:30:46'),
(79, 11, 8, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:07', '2026-05-05 21:30:48'),
(80, 4, 1, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:11', '2026-05-05 21:30:51'),
(81, 4, 2, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:13', '2026-05-05 21:30:53'),
(82, 4, 3, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:14', '2026-05-05 21:30:55'),
(83, 4, 6, 35, 'Pending', NULL, NULL, '2026-05-05 21:28:18', '2026-05-05 21:30:58'),
(84, 4, 7, 55, 'Pending', NULL, NULL, '2026-05-05 21:28:20', '2026-05-05 21:31:00'),
(85, 4, 8, 55, 'Pending', NULL, NULL, '2026-05-05 21:28:22', '2026-05-05 21:31:02'),
(93, 14, 1, 92, 'Accepted', NULL, 'Your existing skills in cooking align perfectly with this program, allowing you to transform your practical experience into a professional culinary qualification. As a college undergraduate, you possess the strong educational foundation necessary to excel in the technical training and food safety standards required for this certification.\n\n**Specific Benefit:** This NCII certification provides you with a nationally recognized credential that significantly increases your employability for high-paying roles in professional kitchens and hotels.', '2026-05-06 09:57:51', '2026-05-06 14:16:45'),
(94, 14, 2, 55, 'Pending', NULL, NULL, '2026-05-06 09:57:52', '2026-05-06 09:57:52'),
(95, 14, 3, 35, 'Pending', NULL, NULL, '2026-05-06 09:57:54', '2026-05-06 09:57:54'),
(96, 14, 5, 35, 'Pending', NULL, NULL, '2026-05-06 09:57:56', '2026-05-06 09:57:56'),
(97, 14, 6, 35, 'Pending', NULL, NULL, '2026-05-06 09:57:58', '2026-05-06 09:57:58'),
(98, 14, 7, 55, 'Pending', NULL, NULL, '2026-05-06 09:58:00', '2026-05-06 09:58:00'),
(99, 14, 8, 35, 'Pending', NULL, NULL, '2026-05-06 09:58:01', '2026-05-06 09:58:01'),
(100, 15, 1, 35, 'Pending', NULL, NULL, '2026-05-11 10:58:26', '2026-05-11 10:58:26'),
(101, 15, 2, 60, 'Pending', NULL, NULL, '2026-05-11 10:58:31', '2026-05-11 10:58:31'),
(102, 15, 3, 45, 'Pending', NULL, NULL, '2026-05-11 10:58:36', '2026-05-11 10:58:36'),
(103, 15, 5, 10, 'Pending', NULL, NULL, '2026-05-11 10:58:40', '2026-05-11 10:58:40'),
(104, 15, 6, 65, 'Pending', NULL, NULL, '2026-05-11 10:58:44', '2026-05-11 10:58:44'),
(105, 15, 7, 35, 'Pending', NULL, NULL, '2026-05-11 10:58:48', '2026-05-11 10:58:48'),
(106, 15, 8, 45, 'Pending', NULL, NULL, '2026-05-11 10:58:51', '2026-05-11 10:58:51'),
(107, 15, 9, 60, 'Pending', NULL, NULL, '2026-06-17 23:22:16', '2026-06-17 23:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `osy_profiles`
--

CREATE TABLE `osy_profiles` (
  `id` int(11) NOT NULL,
  `profile_type` enum('OSY','Regular') DEFAULT 'Regular',
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Solo Parent') DEFAULT 'Single',
  `education_level` varchar(100) DEFAULT NULL,
  `barangay` varchar(50) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `primary_skill` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `govt_id_type` varchar(50) DEFAULT NULL,
  `govt_id_number` varchar(100) DEFAULT NULL,
  `govt_id_image` varchar(255) DEFAULT NULL,
  `reason_for_not_in_school` text DEFAULT NULL,
  `engagement_status` varchar(100) DEFAULT NULL,
  `status` enum('Active','In Training','Employed','Inactive') DEFAULT 'Active',
  `date_of_birth` date DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `registration_status` enum('Drafting','Submitted','Approved') DEFAULT 'Drafting',
  `verification_status` enum('Drafting','Pending','Verified','Action Required') DEFAULT 'Drafting',
  `verification_remark` text DEFAULT NULL,
  `consent_accepted` tinyint(1) DEFAULT 0,
  `identity_document_path` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `osy_profiles`
--

INSERT INTO `osy_profiles` (`id`, `profile_type`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `age`, `gender`, `civil_status`, `education_level`, `barangay`, `barangay_id`, `primary_skill`, `skills`, `interests`, `govt_id_type`, `govt_id_number`, `govt_id_image`, `reason_for_not_in_school`, `engagement_status`, `status`, `date_of_birth`, `image_path`, `registration_status`, `verification_status`, `verification_remark`, `consent_accepted`, `identity_document_path`, `approved_by`, `approved_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'OSY', 'Ricardo', NULL, 'Santos', 'rsantos.osy@email.com', '09171234567', 21, 'Male', 'Single', 'High School Graduate', 'Barangay 1', NULL, 'Automotive', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2003-05-15', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(2, 'OSY', 'Maria Elena', NULL, 'Dela Cruz', 'm.delacruz@email.com', '09175678901', 19, 'Female', 'Single', 'Elementary Graduate', 'Barangay 3', NULL, 'Culinary', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Employed', '2005-08-22', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(3, 'OSY', 'Roberto', NULL, 'Garcia', 'garcia.rob@email.com', '09179876543', 17, 'Male', 'Single', 'High School Undergraduate', 'Barangay 2', NULL, 'IT Support', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2006-11-30', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 2, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(4, 'OSY', 'Patricia', NULL, 'Lozano', 'p.lozano@email.com', '09178765432', 22, 'Female', 'Single', 'High School Graduate', 'Barangay 1', NULL, 'Hospitality', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Inactive', '2002-03-18', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(5, 'OSY', 'Juan', NULL, 'Dela Cruz', 'jdelacruz@email.com', '09177654321', 20, 'Male', 'Single', 'High School Graduate', 'Barangay 4', NULL, 'Welding', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2004-01-10', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 2, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(6, 'OSY', 'Ana', NULL, 'Rivera', 'arivera@email.com', '09176543210', 18, 'Female', 'Single', 'High School Graduate', 'Barangay 2', NULL, 'Welding', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'In Training', '2006-07-25', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 2, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(7, 'OSY', 'Maria', NULL, 'Santos', 'msantos@email.com', '09175432109', 19, 'Female', 'Single', 'High School Graduate', 'Barangay 1', NULL, 'Welding', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2005-09-12', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(8, 'OSY', 'Ricardo', NULL, 'Bautista', 'rbautista@email.com', '09174321098', 21, 'Male', 'Single', 'High School Graduate', 'Barangay 7', NULL, 'Welding', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2003-04-08', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 2, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(9, 'OSY', 'Sofia', NULL, 'Reyes', 'sreyes@email.com', '09173210987', 20, 'Female', 'Single', 'High School Graduate', 'Barangay 3', NULL, 'Computer Literacy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2004-06-20', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(10, 'OSY', 'Carlos', NULL, 'Mendoza', 'cmendoza@email.com', '09172109876', 19, 'Male', 'Single', 'High School Graduate', 'Barangay 5', NULL, 'Carpentry', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2005-02-14', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 2, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(11, 'OSY', 'Rosa', NULL, 'Fernandez', 'rfernandez@email.com', '09171098765', 22, 'Female', 'Single', 'High School Graduate', 'Barangay 2', NULL, 'Culinary', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Employed', '2002-11-28', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(12, 'OSY', 'Miguel', NULL, 'Torres', 'mtorres@email.com', '09170987654', 20, 'Male', 'Single', 'High School Undergraduate', 'Barangay 4', NULL, 'Electrical', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2004-12-05', NULL, 'Drafting', 'Drafting', NULL, 0, NULL, NULL, NULL, 2, '2026-05-05 21:20:51', '2026-05-05 21:20:51'),
(14, 'Regular', 'Jovilyn Mie ', '', 'Rada', 'jovilynmiers@gmail.com', '9466898063', 21, '', 'Single', 'College Undergraduate', 'Baga', NULL, 'Driving, Cooking', 'Driving, Cooking', 'Make-up', 'Driver\'s License', NULL, NULL, '', '', 'Active', '2004-11-25', NULL, 'Submitted', 'Drafting', NULL, 0, NULL, NULL, NULL, 1, '2026-05-06 09:57:47', '2026-05-06 09:57:47'),
(15, 'OSY', 'Test', 'M', 'Youth', 'test8@example.com', '09123456789', 26, 'Male', 'Single', 'High School', 'Baga', NULL, '', 'Test certification', '', '', '', NULL, '', '', 'Active', '2000-01-01', '/uploads/profiles/profile_1778468300_6a0145cc7129e.jpg', 'Approved', 'Verified', '', 1, NULL, 26, '2026-05-30 10:22:35', 17, '2026-05-11 10:58:20', '2026-05-30 10:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `system_references`
--

CREATE TABLE `system_references` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_references`
--

INSERT INTO `system_references` (`id`, `category`, `value`, `is_active`, `created_at`) VALUES
(1, 'govt_id_type', 'SSS', 1, '2026-05-06 08:44:39'),
(2, 'govt_id_type', 'GSIS', 1, '2026-05-06 08:44:39'),
(3, 'govt_id_type', 'PhilHealth', 1, '2026-05-06 08:44:39'),
(4, 'govt_id_type', 'Pag-IBIG', 1, '2026-05-06 08:44:39'),
(5, 'govt_id_type', 'Passport', 1, '2026-05-06 08:44:39'),
(6, 'govt_id_type', 'Driver\'s License', 1, '2026-05-06 08:44:39'),
(7, 'govt_id_type', 'Postal ID', 1, '2026-05-06 08:44:39'),
(8, 'govt_id_type', 'Voter\'s ID', 1, '2026-05-06 08:44:39'),
(9, 'govt_id_type', 'National ID', 1, '2026-05-06 08:44:39'),
(10, 'barangay', 'Baga', 1, '2026-05-06 08:44:39'),
(11, 'barangay', 'Bangko', 1, '2026-05-06 08:44:39'),
(12, 'barangay', 'Camanucan', 1, '2026-05-06 08:44:39'),
(13, 'barangay', 'Dela Paz', 1, '2026-05-06 08:44:39'),
(14, 'barangay', 'Lutao', 1, '2026-05-06 08:44:39'),
(15, 'barangay', 'Magsaysay', 1, '2026-05-06 08:44:39'),
(16, 'barangay', 'Map-an', 1, '2026-05-06 08:44:39'),
(17, 'barangay', 'Mohon', 1, '2026-05-06 08:44:39'),
(18, 'barangay', 'Poblacion', 1, '2026-05-06 08:44:39'),
(19, 'barangay', 'Punta', 1, '2026-05-06 08:44:39'),
(20, 'barangay', 'Salimpuno', 1, '2026-05-06 08:44:39'),
(21, 'barangay', 'San Andres', 1, '2026-05-06 08:44:39'),
(22, 'barangay', 'San Juan', 1, '2026-05-06 08:44:39'),
(23, 'barangay', 'San Roque', 1, '2026-05-06 08:44:39'),
(24, 'barangay', 'Sumasap', 1, '2026-05-06 08:44:39'),
(25, 'barangay', 'Villalin', 1, '2026-05-06 08:44:39'),
(26, 'education_level', 'Elementary Undergraduate', 1, '2026-05-06 08:44:39'),
(27, 'education_level', 'Elementary Graduate', 1, '2026-05-06 08:44:39'),
(28, 'education_level', 'High School Undergraduate', 1, '2026-05-06 08:44:39'),
(29, 'education_level', 'High School Graduate', 1, '2026-05-06 08:44:39'),
(30, 'education_level', 'College Undergraduate', 1, '2026-05-06 08:44:39'),
(31, 'education_level', 'College Graduate', 1, '2026-05-06 08:44:39'),
(32, 'education_level', 'Vocational', 1, '2026-05-06 08:44:39'),
(33, 'education_level', 'No Formal Education', 1, '2026-05-06 08:44:39'),
(34, 'reason', 'Financial Problem', 1, '2026-05-06 08:44:39'),
(35, 'reason', 'Lack of Interest', 1, '2026-05-06 08:44:39'),
(36, 'reason', 'Family Problem', 1, '2026-05-06 08:44:39'),
(37, 'reason', 'Illness/Disability', 1, '2026-05-06 08:44:39'),
(38, 'reason', 'Employment', 1, '2026-05-06 08:44:39'),
(39, 'reason', 'Marriage/Pregnancy', 1, '2026-05-06 08:44:39'),
(40, 'reason', 'Distance of School', 1, '2026-05-06 08:44:39'),
(41, 'reason', 'Others', 1, '2026-05-06 08:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'traccar_token', 'fEcWS61YQW25sobL2c5m5t:APA91bGo_rbxSVtCPGXkGSy5S6z8QfDbEG2gVmDOD1gkNXLYJVAxwkE2uVTei9O8pb4wtOJC1NG_QqFwNEHJCfWQ3mNZSwp2YRKEsctIApUPBrnlBs2_7XA', '2026-05-06 10:11:59'),
(2, 'gmail_user', 'aclonhemday@gmail.com', '2026-05-06 10:11:59'),
(3, 'gmail_app_password', 'jsit bytp oppd jcxo', '2026-05-06 10:11:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','lydo','sk_chairman','youth','employer','training_provider') DEFAULT 'lydo',
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('Active','Pending','Declined','Suspended','Action Required') DEFAULT 'Active',
  `barangay` varchar(100) DEFAULT NULL,
  `provider_type` enum('employer','training_provider') DEFAULT NULL,
  `provider_document_path` varchar(255) DEFAULT NULL,
  `temp_password_required` tinyint(1) DEFAULT 0,
  `approval_remark` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `fullname`, `role`, `is_active`, `status`, `barangay`, `provider_type`, `provider_document_path`, `temp_password_required`, `approval_remark`, `created_by`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin1', 'admin1@civichorizon.ph', '$2y$10$TG43P5/sT628EvnF.agoeufWjP/AzrWhROwZoAaoeyO51Z2lcb46G', 'Senior Administrator', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-12 23:45:22'),
(2, 'admin2', 'admin2@civichorizon.ph', '$2y$10$a3QPnmKLlwC/4k0q6clwju7aEZCkB/xpnZgy0ILElzfzCDyPYYw8W', 'System Manager', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-12 23:45:22'),
(3, 'admin3', 'admin3@civichorizon.ph', '$2y$10$mQ9PL5gA5MesA6AEBX5my.m8YUg/1MtLURUN55AygeKfew3zUmUUi', 'Database Administrator', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-12 23:45:22'),
(4, 'admin4', 'admin4@civichorizon.ph', '$2y$10$8rPiBNciUDFcyVe7kxlWxekm0dyomVQc5T.JglhAI2STl6KD9G5ve', 'Operations Lead', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-12 23:45:22'),
(5, 'jsmith', 'jsmith@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'John Smith', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-14 18:45:44'),
(6, 'mgarcia', 'mgarcia@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Maria Garcia', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-14 18:45:44'),
(7, 'rsantos', 'rsantos@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Ricardo Santos', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-14 18:45:44'),
(8, 'acruz', 'acruz@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Angela Cruz', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-14 18:45:44'),
(9, 'blopez', 'blopez@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Benjamin Lopez', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-05 21:20:51', '2026-05-14 18:45:44'),
(10, 'testyouth', 'test@example.com', '$2y$10$i7oI47MUllGyWkJEEd7IguSV/NbS4PeeueMFwmonO0AOH13zUYCzW', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:49:52', '2026-05-11 10:49:52'),
(11, 'testyouth2', 'test2@example.com', '$2y$10$dtiJ57js4oGGaiREUBicwe0qvFKoPgGiUjXNfsSe4wZTU2Z/Zwmay', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:54:38', '2026-05-11 10:54:38'),
(12, 'testyouth3', 'test3@example.com', '$2y$10$p8ntbwd5o5dvaO2tv2mlhuGTjMerNYX/4es.twVnVkXpW.S2os5DG', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:55:38', '2026-05-11 10:55:38'),
(13, 'testyouth4', 'test4@example.com', '$2y$10$3HJbTE8I.KKuuxyp7838W.vVlHcDP7W6jsbV/Vu1pxJUiNnhAri9e', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:56:10', '2026-05-11 10:56:10'),
(14, 'testyouth5', 'test5@example.com', '$2y$10$kHHalKrdPbwXXTL3KhtxwOYqVl22tLSCLb4LNgNIEyeSK5D95BuZe', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:56:40', '2026-05-11 10:56:40'),
(15, 'testyouth6', 'test6@example.com', '$2y$10$PsAseWJWteN4Tc0GfybReONMrPn.V8hvHlPJs2jH5Lxfb/lyc45r.', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:57:07', '2026-05-11 10:57:07'),
(16, 'testyouth7', 'test7@example.com', '$2y$10$l1og5iTIYTy3wVKXcnhNLu7RbSbuUO7sbquAbFe9HwflJ6SRlzgv6', 'Test Youth', 'youth', 1, 'Pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:57:38', '2026-05-11 10:57:38'),
(17, 'testyouth8', 'test8@example.com', '$2y$10$CiGWVMTrt3GQSpbgtMJGY.pDASGd6JgVJUfczXqW9xDLTrHYU812i', 'Test Youth', 'youth', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-11 10:58:20', '2026-05-30 10:22:35'),
(18, 'nhempharmacy', 'aclonhemday@gmail.com', '$2y$10$UOGNLaPEZOPoUJKqTnnow.MKfZ.zvHdDEpY043W12BTFWKfqfGEey', 'Nhem pharmacy', 'employer', 1, 'Active', 'Baga', 'employer', '/uploads/providers/provider_doc_1778587396_6a0317040f4bb.png', 0, '', NULL, NULL, '2026-05-12 20:03:16', '2026-06-17 22:25:51'),
(19, 'nhemdaygaclo', 'aclonhem@gmail.com', '$2y$10$DM6uD/IlNGnTVzI7YYyA7.iELHI9fA83X6hFVbg0GQZlfr0gyoKAO', 'Nhem Day G. Aclo', 'employer', 1, 'Active', 'Baga', 'employer', '/uploads/providers/provider_doc_1778591593_6a0327692d2c4.png', 1, '', NULL, NULL, '2026-05-12 21:13:13', '2026-05-13 00:15:06'),
(25, 'lydo_admin', 'lydo@civichorizon.ph', '$2y$10$3KdeE/FRF.VlY/KbjRAIL.8puPSJdchyksD04hnoXxmDQ74u/JCzq', 'Maria Teresa Lim', 'lydo', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-12 23:12:56', '2026-05-12 23:12:56'),
(26, 'sk_baga', 'sk.baga@civichorizon.ph', '$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G', 'Carlos Reyes', 'sk_chairman', 1, 'Active', 'Baga', NULL, NULL, 0, NULL, NULL, NULL, '2026-05-12 23:12:56', '2026-05-14 18:45:44'),
(27, 'sk_bangko', 'sk.bangko@civichorizon.ph', '$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G', 'Angela Mendoza', 'sk_chairman', 1, 'Active', 'Bangko', NULL, NULL, 0, NULL, NULL, NULL, '2026-05-12 23:12:56', '2026-05-14 18:45:44'),
(28, 'sk_camanucan', 'sk.camanucan@civichorizon.ph', '$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G', 'Jerome Villanueva', 'sk_chairman', 1, 'Active', 'Camanucan', NULL, NULL, 0, NULL, NULL, NULL, '2026-05-12 23:12:56', '2026-05-14 18:45:44'),
(29, 'sk_delapaz', 'sk.delapaz@civichorizon.ph', '$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G', 'Patricia Santos', 'sk_chairman', 1, 'Active', 'Dela Paz', NULL, NULL, 0, NULL, NULL, NULL, '2026-05-12 23:12:56', '2026-05-14 18:45:44'),
(30, 'sk_poblacion', 'sk.poblacion@civichorizon.ph', '$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G', 'Marco Dela Cruz', 'sk_chairman', 1, 'Active', 'Poblacion', NULL, NULL, 0, NULL, NULL, NULL, '2026-05-12 23:12:56', '2026-05-14 18:45:44'),
(31, 'test_provider', 'provider@test.com', '$2y$10$kg3vl5h7ehBf5G74aA0Puu4CiGj.nrIHSHda9nVJ.tIDtzbO3SyzS', 'Test Training Provider', 'training_provider', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-13 08:18:46', '2026-05-13 08:32:51'),
(32, 'test_youth', 'youth@test.com', '$2y$10$DPWDG.NCsUyecaxusU8DRuJOnfqDXNL3b2INFcilYPWbiCkZh53Vi', 'Test Youth User', 'youth', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-13 08:18:46', '2026-05-13 08:32:51'),
(33, 'youth_dev', 'youth_dev@example.com', '$2y$10$1YSDEG/m5ozh7oRL8ZRqU.VT4cJ9aV7QbSbt2QC44rCEKlWv26k5y', 'Dev Youth', 'youth', 1, 'Active', NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-05-14 18:39:50', '2026-05-14 18:39:50'),
(34, 'employer_dev', 'employer_dev@example.com', '$2y$10$kAwM/RYnAhBBC/ZehYI/kObEUCCHDstjRrxf6IzcrYlvsnA5UksZG', 'Dev Employer', 'employer', 1, 'Active', NULL, NULL, NULL, 0, '', NULL, NULL, '2026-05-14 18:39:51', '2026-05-14 22:50:48'),
(35, 'training_provider_dev', 'training_provider_dev@example.com', '$2y$10$iz/VulKpaspxifk9YPmmUOTu3AZCdDVObpjwcOzMBjFzpjz7Crvny', 'Dev Training_provider', 'training_provider', 1, 'Active', NULL, NULL, NULL, 0, '', NULL, NULL, '2026-05-14 18:39:51', '2026-05-14 22:50:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_usage_log`
--
ALTER TABLE `ai_usage_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_usage_date` (`created_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_actor` (`actor_id`),
  ADD KEY `idx_audit_target` (`target_type`,`target_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_recipient` (`recipient_type`,`recipient_id`),
  ADD KEY `idx_messages_sender` (`sender_type`,`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_notification_type` (`type`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_notification_user` (`notification_id`,`user_id`),
  ADD KEY `idx_notification` (`notification_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_opportunity_type` (`type`),
  ADD KEY `idx_opportunity_status` (`status`);

--
-- Indexes for table `opportunity_required_skills`
--
ALTER TABLE `opportunity_required_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_opportunity_skills` (`opportunity_id`);

--
-- Indexes for table `opportunity_skills`
--
ALTER TABLE `opportunity_skills`
  ADD PRIMARY KEY (`opportunity_id`,`skill_id`);

--
-- Indexes for table `osy_matches`
--
ALTER TABLE `osy_matches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_match` (`osy_id`,`opportunity_id`),
  ADD KEY `idx_matches_osy` (`osy_id`),
  ADD KEY `idx_matches_opportunity` (`opportunity_id`),
  ADD KEY `idx_matches_score` (`match_score`),
  ADD KEY `idx_matches_status` (`status`);

--
-- Indexes for table `osy_profiles`
--
ALTER TABLE `osy_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_osy_status` (`status`),
  ADD KEY `idx_osy_skill` (`primary_skill`),
  ADD KEY `idx_osy_barangay` (`barangay`);

--
-- Indexes for table `system_references`
--
ALTER TABLE `system_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_usage_log`
--
ALTER TABLE `ai_usage_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `opportunity_required_skills`
--
ALTER TABLE `opportunity_required_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `osy_matches`
--
ALTER TABLE `osy_matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `osy_profiles`
--
ALTER TABLE `osy_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `system_references`
--
ALTER TABLE `system_references`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3371;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_reads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD CONSTRAINT `notification_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `opportunity_required_skills`
--
ALTER TABLE `opportunity_required_skills`
  ADD CONSTRAINT `opportunity_required_skills_ibfk_1` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `opportunity_skills`
--
ALTER TABLE `opportunity_skills`
  ADD CONSTRAINT `opportunity_skills_ibfk_1` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `osy_matches`
--
ALTER TABLE `osy_matches`
  ADD CONSTRAINT `osy_matches_ibfk_1` FOREIGN KEY (`osy_id`) REFERENCES `osy_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `osy_matches_ibfk_2` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `osy_profiles`
--
ALTER TABLE `osy_profiles`
  ADD CONSTRAINT `osy_profiles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
