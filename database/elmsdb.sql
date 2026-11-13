-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 03:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `target_type` varchar(40) DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `created_at`) VALUES
(1, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:34'),
(2, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:36'),
(3, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:37'),
(4, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:37'),
(5, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:37'),
(6, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:39'),
(7, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:39'),
(8, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:39'),
(9, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:40'),
(10, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:40'),
(11, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:40'),
(12, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:41'),
(13, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:43'),
(14, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:43'),
(15, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:43'),
(16, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:43'),
(17, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:43'),
(18, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:49'),
(19, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:49'),
(20, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:49'),
(21, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:49'),
(22, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:50'),
(23, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:50'),
(24, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:50'),
(25, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:50'),
(26, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:50'),
(27, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:54'),
(28, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:54'),
(29, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:54'),
(30, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:29:54'),
(31, 1, 'create_account', 'user', 17, 'Created Admin account \'thegreatone\'.', '2026-09-21 22:30:31'),
(32, 1, 'update_permissions', 'user', 17, 'Updated permission grants.', '2026-09-21 22:31:07'),
(33, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:32:52'),
(34, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:32:53'),
(35, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:32:53'),
(36, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:32:53'),
(37, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:32:54'),
(38, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:32:54'),
(39, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:01'),
(40, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:01'),
(41, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:01'),
(42, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:01'),
(43, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(44, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(45, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(46, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(47, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(48, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(49, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(50, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:41'),
(51, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:42'),
(52, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:42'),
(53, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:46'),
(54, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:46'),
(55, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:46'),
(56, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:48'),
(57, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:48'),
(58, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:33:48'),
(59, 1, 'update_permissions', 'user', 17, 'Updated permission grants.', '2026-09-21 22:33:54'),
(60, 1, 'update_permissions', 'user', 17, 'Updated permission grants.', '2026-09-21 22:33:55'),
(61, 1, 'update_permissions', 'user', 17, 'Updated permission grants.', '2026-09-21 22:34:25'),
(62, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:42'),
(63, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:42'),
(64, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:42'),
(65, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:46'),
(66, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:46'),
(67, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:48'),
(68, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:48'),
(69, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:38:48'),
(70, 1, 'update_permissions', 'user', 17, 'Updated permission grants.', '2026-09-21 22:38:59'),
(71, 1, 'update_permissions', 'user', 17, 'Updated permission grants.', '2026-09-21 22:40:48'),
(72, 17, 'update_account', 'user', 15, 'Updated account (role: Teacher).', '2026-09-21 22:42:17'),
(73, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:49:08'),
(74, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 22:49:12'),
(75, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 22:49:15'),
(76, 1, 'enroll_student', 'user', 8, '0 subject(s) enrolled, 13 skipped.', '2026-09-21 23:40:13'),
(77, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:45:01'),
(78, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:45:01'),
(79, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:45:02'),
(80, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:45:02'),
(81, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:45:02'),
(82, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:45:02'),
(83, 1, 'update_permissions', 'user', 16, 'Updated permission grants.', '2026-09-21 23:55:03'),
(84, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:05'),
(85, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:06'),
(86, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:06'),
(87, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:07'),
(88, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:07'),
(89, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:08'),
(90, 1, 'toggle_status', 'user', 17, 'Status changed to Inactive.', '2026-09-21 23:55:09'),
(91, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:11'),
(92, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:12'),
(93, 1, 'toggle_status', 'user', 17, 'Status changed to Active.', '2026-09-21 23:55:12'),
(94, 1, 'toggle_status', 'user', 17, 'Status changed to Inactive.', '2026-09-21 23:55:13'),
(95, 1, 'toggle_status', 'user', 17, 'Status changed to Active.', '2026-09-21 23:55:13'),
(96, 1, 'toggle_status', 'user', 17, 'Status changed to Inactive.', '2026-09-21 23:55:14'),
(97, 1, 'toggle_status', 'user', 17, 'Status changed to Active.', '2026-09-21 23:55:14'),
(98, 1, 'toggle_status', 'user', 17, 'Status changed to Inactive.', '2026-09-21 23:55:15'),
(99, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:16'),
(100, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:16'),
(101, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:16'),
(102, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:16'),
(103, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:17'),
(104, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:17'),
(105, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:18'),
(106, 1, 'toggle_status', 'user', 16, 'Status changed to Inactive.', '2026-09-21 23:55:18'),
(107, 1, 'toggle_status', 'user', 16, 'Status changed to Active.', '2026-09-21 23:55:21'),
(108, 1, 'toggle_status', 'user', 17, 'Status changed to Active.', '2026-09-21 23:55:21'),
(109, 1, 'enroll_student', 'user', 10, '1 subject(s) enrolled, 4 skipped.', '2026-09-22 14:19:58'),
(110, 1, 'enroll_student', 'user', 8, '0 subject(s) enrolled, 13 skipped.', '2026-09-22 15:27:31'),
(111, 1, 'publish_announcement', 'announcement', 8, 'Posted \'check\' to Everyone.', '2026-09-22 15:41:05'),
(112, 1, 'create_account', 'user', 18, 'Created Student account \'den\'.', '2026-09-22 19:09:39'),
(113, 1, 'assign_teacher', 'teacher_assignment', 8, 'Assigned teacher #15 to course #4 (Grade 11 - ABM-B).', '2026-09-22 19:11:19'),
(114, 1, 'enroll_student', 'user', 6, '5 subject(s) enrolled, 4 skipped.', '2026-09-22 19:14:54'),
(115, 1, 'publish_announcement', 'announcement', 10, 'Posted \'meeting\' to Students.', '2026-09-22 19:20:43'),
(116, 1, 'update_section', 'section', 3, 'Updated section \'Grade 9\'.', '2026-10-04 21:54:11'),
(117, 1, 'update_account', 'user', 18, 'Updated account (role: Student).', '2026-10-04 21:55:55'),
(118, 1, 'enroll_section', 'section', NULL, 'Grade 7: 8 enrollment(s) added.', '2026-10-04 21:56:11'),
(119, 1, 'enroll_student', 'user', 18, 'Grade 7: +0 / -0 subject(s).', '2026-10-04 21:56:22'),
(120, 1, 'assign_teacher', 'teacher_assignment', 33, 'Grade 10 · HS10-AP → teacher #14.', '2026-10-04 22:07:09'),
(121, 1, 'assign_teacher', 'teacher_assignment', 34, 'Grade 10 · HS10-ESP → teacher #14.', '2026-10-04 22:07:10'),
(122, 1, 'assign_teacher', 'teacher_assignment', 35, 'Grade 10 · HS10-MAPEH → teacher #3.', '2026-10-04 22:07:11'),
(123, 1, 'assign_teacher', 'teacher_assignment', 36, 'Grade 10 · HS10-TLE → teacher #3.', '2026-10-04 22:07:12'),
(124, 1, 'assign_teacher', 'teacher_assignment', 25, 'Grade 9 · HS9-FIL → teacher #15.', '2026-10-04 22:26:53'),
(125, 1, 'assign_teacher', 'teacher_assignment', 26, 'Grade 9 · HS9-ENGLISH → teacher #3.', '2026-10-04 22:26:57'),
(126, 1, 'update_account', 'user', 18, 'Updated account (role: Student).', '2026-10-04 22:30:42'),
(127, 1, 'create_module', 'module', 344, 'Course #28 · Module 6: module 6', '2026-10-05 00:40:54'),
(128, 1, 'reorder_module', 'module', 344, 'HS7-FIL: Module 6 → 5', '2026-10-05 00:41:14'),
(129, 1, 'reorder_module', 'module', 13, 'HS7-FIL: Module 6 → 5', '2026-10-05 00:41:18'),
(130, 1, 'assign_teacher', 'teacher_assignment', 40, 'Grade 11 - ABM-A · SHS-GENMATH → teacher #14.', '2026-10-05 01:02:23'),
(131, 1, 'assign_teacher', 'teacher_assignment', 41, 'Grade 11 - ABM-A · SHS-STATS → teacher #14.', '2026-10-05 01:02:36'),
(132, 1, 'update_account', 'user', 10, 'Updated account (role: Student).', '2026-10-05 01:03:10'),
(133, 1, 'update_account', 'user', 10, 'Updated account (role: Student).', '2026-10-05 01:03:31'),
(134, 1, 'enroll_section', 'section', NULL, 'Grade 11 - ABM-A: 9 enrollment(s) added.', '2026-10-05 01:03:37'),
(135, 1, 'create_account', 'user', 27, 'Created Student account \'test1\'.', '2026-10-08 00:20:57'),
(136, 1, 'create_account', 'user', 28, 'Created Teacher account \'test2\'.', '2026-10-08 00:22:10'),
(137, 1, 'drop_outside_enrollments', 'enrollment', NULL, 'Dropped 20 enrollment(s) in subjects outside the student\'s section.', '2026-10-08 02:22:16'),
(138, 1, 'enroll_student', 'user', 10, 'Grade 11 - ABM-A: +0 / -0 subject(s).', '2026-10-08 02:22:58'),
(139, 1, 'enroll_student', 'user', 27, 'Grade 10: +8 / -0 subject(s).', '2026-10-08 03:16:24'),
(140, 1, 'assign_teacher', 'teacher_assignment', 1, 'Grade 10 · HS10-ENGLISH → teacher #14.', '2026-10-08 03:21:19'),
(141, 1, 'update_banner', 'banner', 7, NULL, '2026-10-08 23:27:07'),
(142, 1, 'update_banner', 'banner', 7, NULL, '2026-10-08 23:27:08'),
(143, 1, 'create_account', 'user', 29, 'Created Student account \'Chavz\'.', '2026-10-09 14:03:18'),
(144, 1, 'create_subject', 'course', 88, 'HS7-CP · Computer Programming', '2026-10-09 19:50:14'),
(145, 1, 'create_subject', 'course', 89, 'HS7-ASD · test', '2026-10-09 19:53:44'),
(146, 1, 'delete_subject', 'course', 89, 'HS7-ASD · test', '2026-10-09 19:58:06'),
(147, 1, 'delete_subject', 'course', 88, 'HS7-CP · Computer Programming', '2026-10-09 19:58:10'),
(148, 1, 'create_subject', 'course', 90, 'HS7-ASD · test', '2026-10-09 19:58:20'),
(149, 1, 'delete_subject', 'course', 90, 'HS7-ASD · test', '2026-10-09 19:58:32'),
(150, 1, 'update_account', 'user', 27, 'Updated account (role: Student).', '2026-10-09 21:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `perm_key` varchar(60) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `perm_key`, `granted`, `updated_at`) VALUES
(1, 16, 'view_students', 1, '2026-09-21 22:29:34'),
(2, 16, 'manage_students', 1, '2026-09-21 22:29:34'),
(3, 16, 'enroll_students', 0, '2026-09-21 22:29:34'),
(4, 16, 'view_teachers', 1, '2026-09-21 22:29:34'),
(5, 16, 'manage_teachers', 1, '2026-09-21 22:29:34'),
(6, 16, 'assign_teachers', 0, '2026-09-21 22:29:34'),
(7, 16, 'view_subjects', 1, '2026-09-21 22:38:46'),
(8, 16, 'manage_subjects', 0, '2026-09-21 22:29:34'),
(9, 16, 'view_classes', 0, '2026-09-21 22:29:34'),
(10, 16, 'manage_classes', 0, '2026-09-21 22:29:34'),
(11, 16, 'view_enrollment', 1, '2026-09-21 22:38:48'),
(12, 16, 'view_reports', 1, '2026-09-21 22:38:48'),
(13, 16, 'publish_announcements', 1, '2026-09-21 22:38:48'),
(14, 16, 'manage_admin_accounts', 0, '2026-09-21 22:29:34'),
(421, 17, 'view_students', 1, '2026-09-21 22:30:31'),
(422, 17, 'manage_teachers', 1, '2026-09-21 22:40:48'),
(423, 17, 'view_subjects', 1, '2026-09-21 22:30:31'),
(424, 17, 'view_enrollment', 1, '2026-09-21 22:40:48'),
(425, 17, 'view_reports', 1, '2026-09-21 22:40:48'),
(426, 17, 'publish_announcements', 1, '2026-09-21 22:30:31'),
(428, 17, 'manage_students', 0, '2026-09-21 22:31:07'),
(429, 17, 'enroll_students', 0, '2026-09-21 22:31:07'),
(430, 17, 'view_teachers', 1, '2026-09-21 22:38:59'),
(432, 17, 'assign_teachers', 0, '2026-09-21 22:31:07'),
(434, 17, 'manage_subjects', 0, '2026-09-21 22:31:07'),
(435, 17, 'view_classes', 1, '2026-09-21 22:40:48'),
(436, 17, 'manage_classes', 0, '2026-09-21 22:31:07'),
(440, 17, 'manage_admin_accounts', 0, '2026-09-21 22:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `target_audience` enum('Everyone','Students','Teachers') NOT NULL DEFAULT 'Everyone',
  `posted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `author_id`, `course_id`, `title`, `body`, `target_audience`, `posted_at`) VALUES
(1, 1, NULL, 'Welcome to Arandia College eLMS — SHS & HS Portal!', 'The Electronic Learning Management System is now live for School Year 2025-2026. SHS and HS students and teachers may now log in using their assigned credentials to access subjects, modules, and activities.', 'Everyone', '2026-03-12 02:59:06'),
(2, 1, NULL, 'asdasd', 'adsasdasd', 'Everyone', '2026-05-15 18:59:58'),
(3, 3, 38, 'asdasd', 'asdasds', 'Everyone', '2026-05-15 19:08:07'),
(8, 1, 15, 'check', 'try', 'Everyone', '2026-09-22 15:41:05'),
(9, 15, 15, 'reminder', 'asd', 'Everyone', '2026-09-22 15:43:47'),
(10, 1, 15, 'meeting', 'punta kayo sa lobby gathering', 'Students', '2026-09-22 19:20:43');

--
-- Triggers `announcements`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_announcement_delete` AFTER DELETE ON `announcements` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('announcement.deleted', OLD.course_id, NULL, IF((SELECT role FROM users WHERE id = OLD.author_id) = 'Teacher', OLD.author_id, NULL), NULL, OLD.id, CASE OLD.target_audience WHEN 'Students' THEN 'students' WHEN 'Teachers' THEN 'teachers' ELSE 'all' END, OLD.title);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_announcement_insert` AFTER INSERT ON `announcements` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('announcement.new', NEW.course_id, NULL, IF((SELECT role FROM users WHERE id = NEW.author_id) = 'Teacher', NEW.author_id, NULL), NULL, NEW.id, CASE NEW.target_audience WHEN 'Students' THEN 'students' WHEN 'Teachers' THEN 'teachers' ELSE 'all' END, NEW.title);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `name` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`name`, `value`, `updated_at`) VALUES
('auto_enroll_backfill', 'done: 61 added', '2026-10-09 21:25:56'),
('module_links_backfill', 'done', '2026-10-09 21:31:48');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Teacher who created it; NULL = shared by every section',
  `title` varchar(200) NOT NULL,
  `instructions` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(300) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `max_score` decimal(6,2) NOT NULL DEFAULT 100.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `max_attempts` int(10) UNSIGNED DEFAULT NULL COMMENT 'Max submissions allowed per student; NULL = unlimited',
  `module_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `course_id`, `teacher_id`, `title`, `instructions`, `description`, `file_path`, `due_date`, `max_score`, `created_at`, `max_attempts`, `module_id`) VALUES
(3, 15, 15, 'Essay: Five Examples of Computers', 'Write a 500‑word essay that identifies and describes five distinct examples of computers (e.g., a smartphone, a laptop, a mainframe, a microcontroller, and a supercomputer). For each example, explain its main components, typical uses, and why it qualifies as a computer. Organize your essay with an introduction, a separate paragraph for each example, and a concluding paragraph summarizing the key points. Use Times New Roman, 12‑point font, double‑spaced, and include a title page with your name, class, and date. Submit the essay as a PDF file via the school’s learning platform by the deadline.', 'Write a 500‑word essay that identifies and describes five distinct examples of computers (e.g., a smartphone, a laptop, a mainframe, a microcontroller, and a supercomputer). For each example, explain its main components, typical uses, and why it qualifies as a computer. Organize your essay with an introduction, a separate paragraph for each example, and a concluding paragraph summarizing the key points. Use Times New Roman, 12‑point font, double‑spaced, and include a title page with your name, class, and date. Submit the essay as a PDF file via the school’s learning platform by the deadline.', NULL, '2026-10-14 19:59:00', 80.00, '2026-09-22 19:59:45', NULL, 264),
(4, 37, 15, 'Assigement 233', '123', '123', NULL, '2026-10-05 18:43:00', 100.00, '2026-10-05 18:43:54', NULL, 89),
(5, 37, 15, 'Gawain 1: Ang Haiku', 'Sumulat ng isang haiku (5-7-5) tungkol sa kalikasan at ipaliwanag ito sa 2-3 pangungusap.', 'Sumulat ng isang haiku (5-7-5) tungkol sa kalikasan at ipaliwanag ito sa 2-3 pangungusap.', NULL, '2026-10-08 18:48:43', 50.00, '2026-10-05 18:48:43', NULL, 89),
(6, 37, 15, 'Pagsusulat ng Sanaysay tungkol sa Batayang Tagalog', 'Sumulat ng sanaysay na 300-500 na salita na naglalarawan ng mga pangunahing kaalaman sa wikang Tagalog tulad ng alpabeto, pagbigkas, mga pangngalan, panghalip, at simpleng pangungusap. Gamitin ang sariling karanasan o halimbawa upang ipakita kung paano mo natutunan o ginagamit ang mga ito sa pang-araw-araw. Isama ang isang maikling talata na naglalahad ng kahalagahan ng pag-aaral ng Tagalog sa konteksto ng kultura at komunikasyon. I-format ang sanaysay gamit ang Times New Roman, 12‑pt font, at doble ang espasyo. Isumite ito sa Google Classroom bilang .doc o .pdf file bago ang takdang petsa.', 'Sumulat ng sanaysay na 300-500 na salita na naglalarawan ng mga pangunahing kaalaman sa wikang Tagalog tulad ng alpabeto, pagbigkas, mga pangngalan, panghalip, at simpleng pangungusap. Gamitin ang sariling karanasan o halimbawa upang ipakita kung paano mo natutunan o ginagamit ang mga ito sa pang-araw-araw. Isama ang isang maikling talata na naglalahad ng kahalagahan ng pag-aaral ng Tagalog sa konteksto ng kultura at komunikasyon. I-format ang sanaysay gamit ang Times New Roman, 12‑pt font, at doble ang espasyo. Isumite ito sa Google Classroom bilang .doc o .pdf file bago ang takdang petsa.', NULL, NULL, 85.00, '2026-10-05 20:28:01', NULL, 89),
(7, 37, 15, 'Essay on Panitikang Asyano', 'Write a 500-700 word essay in Filipino discussing the major literary forms of Asian countries, such as the Japanese tanka and haiku, and how they reflect the cultures they come from. Include at least two other Asian literary examples (e.g., Chinese poetry, Indian epics) and compare their themes or structures with tanka/haiku. Provide a personal reflection on why understanding Asian literature is important for appreciating cultural diversity. Type your essay, double‑space it, and include a title, your name, and the date at the top. Submit the document in .docx or .pdf format through the classroom portal.', 'Write a 500-700 word essay in Filipino discussing the major literary forms of Asian countries, such as the Japanese tanka and haiku, and how they reflect the cultures they come from. Include at least two other Asian literary examples (e.g., Chinese poetry, Indian epics) and compare their themes or structures with tanka/haiku. Provide a personal reflection on why understanding Asian literature is important for appreciating cultural diversity. Type your essay, double‑space it, and include a title, your name, and the date at the top. Submit the document in .docx or .pdf format through the classroom portal.', NULL, NULL, 80.00, '2026-10-05 20:31:50', NULL, 89),
(8, 37, 15, 'Gawain 2: Ang Tanka', 'Sumulat ng isang tanka (5-7-5-7-7) at ipaliwanag ito sa 1-2 pangungusap.', 'Sumulat ng isang tanka (5-7-5-7-7) at ipaliwanag ito sa 1-2 pangungusap.', NULL, '2026-10-08 00:02:57', 50.00, '2026-10-06 00:02:57', NULL, 89),
(9, 37, 15, 'Gawain 3: Sanaysay', 'Sumulat ng maikling sanaysay (2 talata) tungkol sa iyong pamilya.', 'Sumulat ng maikling sanaysay (2 talata) tungkol sa iyong pamilya.', NULL, '2026-10-04 00:02:57', 100.00, '2026-10-06 00:02:57', NULL, 89),
(10, 38, 14, 'chap1', 'testing', 'testing', NULL, '2026-10-15 03:21:00', 100.00, '2026-10-08 03:22:20', NULL, 134),
(11, 53, 14, 'testing', '', '', 'uploads/assignments/assign_6ac6a8338a111.pdf', '2026-10-08 04:16:00', 100.00, '2026-10-08 04:14:43', NULL, 150),
(12, 53, 14, 'assignment', 'testing', 'testing', NULL, '2026-10-09 14:08:00', 100.00, '2026-10-09 14:08:52', NULL, 150),
(13, 38, 14, 'test', '', '', NULL, '2026-10-16 20:26:00', 100.00, '2026-10-09 20:26:46', NULL, 134),
(14, 38, 14, 'test', '', '', NULL, '2026-10-30 20:29:00', 100.00, '2026-10-09 20:29:59', NULL, 134);

--
-- Triggers `assignments`
--
DELIMITER $$
CREATE TRIGGER `trg_gb_assignment_delete` AFTER DELETE ON `assignments` FOR EACH ROW BEGIN
  DELETE FROM gradebook_items WHERE source_type = 'assignment' AND source_id = OLD.id;
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('assignment.deleted', OLD.course_id, NULL, OLD.teacher_id, NULL, OLD.id, 'all', OLD.title);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_gb_assignment_insert` AFTER INSERT ON `assignments` FOR EACH ROW BEGIN
  INSERT IGNORE INTO gradebook_items (course_id, teacher_id, source_type, source_id, title, max_score, created_at)
  VALUES (NEW.course_id, NEW.teacher_id, 'assignment', NEW.id, NEW.title, COALESCE(NEW.max_score, 100), COALESCE(NEW.created_at, NOW()));
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('assignment.new', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_gb_assignment_update` AFTER UPDATE ON `assignments` FOR EACH ROW BEGIN
  UPDATE gradebook_items SET course_id = NEW.course_id, teacher_id = NEW.teacher_id, title = NEW.title, max_score = COALESCE(NEW.max_score, 100)
  WHERE source_type = 'assignment' AND source_id = NEW.id;
  IF NOT (NEW.title <=> OLD.title AND NEW.due_date <=> OLD.due_date AND NEW.max_score <=> OLD.max_score
          AND NEW.description <=> OLD.description AND NEW.instructions <=> OLD.instructions
          AND NEW.file_path <=> OLD.file_path AND NEW.max_attempts <=> OLD.max_attempts) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('assignment.updated', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present',
  `remark` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recorded_by` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `course_id`, `date`, `status`, `remark`, `created_at`, `updated_at`, `recorded_by`) VALUES
(1, 6, 41, '2026-05-16', 'Present', '', '2026-05-16 11:48:22', '2026-05-16 11:48:22', 3),
(2, 8, 15, '2026-09-21', 'Present', '1', '2026-09-21 13:14:50', '2026-09-21 13:14:50', 15),
(3, 8, 15, '2026-09-22', 'Present', '2', '2026-09-22 07:33:03', '2026-09-22 10:47:43', 15),
(4, 8, 15, '2026-09-20', 'Present', '1', '2026-09-22 10:47:31', '2026-09-22 10:47:31', 15),
(7, 19, 37, '2026-10-05', 'Present', '', '2026-10-05 11:39:18', '2026-10-05 11:39:18', 15),
(8, 19, 37, '2026-10-02', 'Present', '', '2026-10-05 11:46:51', '2026-10-05 11:46:51', 15),
(10, 20, 37, '2026-09-28', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(11, 21, 37, '2026-09-28', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(12, 22, 37, '2026-09-28', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(13, 23, 37, '2026-09-28', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(14, 24, 37, '2026-09-28', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(15, 20, 37, '2026-09-29', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(16, 21, 37, '2026-09-29', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(17, 22, 37, '2026-09-29', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(18, 23, 37, '2026-09-29', 'Late', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(19, 24, 37, '2026-09-29', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(20, 20, 37, '2026-09-30', 'Late', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(21, 21, 37, '2026-09-30', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(22, 22, 37, '2026-09-30', 'Absent', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(23, 23, 37, '2026-09-30', 'Excused', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(24, 24, 37, '2026-09-30', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(25, 20, 37, '2026-10-01', 'Excused', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(26, 21, 37, '2026-10-01', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(27, 22, 37, '2026-10-01', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(28, 23, 37, '2026-10-01', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(29, 24, 37, '2026-10-01', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(30, 20, 37, '2026-10-02', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(31, 21, 37, '2026-10-02', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(32, 22, 37, '2026-10-02', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(33, 23, 37, '2026-10-02', 'Present', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(34, 24, 37, '2026-10-02', 'Late', '', '2026-10-05 16:02:57', '2026-10-05 16:02:57', 15),
(41, 10, 4, '2026-10-08', 'Present', '', '2026-10-07 17:44:41', '2026-10-07 17:44:41', 14),
(42, 27, 38, '2026-10-08', 'Present', '', '2026-10-07 19:24:17', '2026-10-07 19:24:57', 14),
(43, 6, 38, '2026-10-08', 'Present', '', '2026-10-07 19:24:17', '2026-10-07 19:24:27', 14);

--
-- Triggers `attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_attendance_insert` AFTER INSERT ON `attendance` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('attendance.updated', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULLIF(NEW.recorded_by, 0), NEW.student_id, NULL, 'all', DATE_FORMAT(NEW.date, '%Y-%m-%d'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_attendance_update` AFTER UPDATE ON `attendance` FOR EACH ROW BEGIN
  IF NOT (NEW.status <=> OLD.status AND NEW.remark <=> OLD.remark AND NEW.date <=> OLD.date) THEN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('attendance.updated', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULLIF(NEW.recorded_by, 0), NEW.student_id, NULL, 'all', DATE_FORMAT(NEW.date, '%Y-%m-%d'));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `award_types`
--

CREATE TABLE `award_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'Academic Excellence',
  `icon` varchar(30) NOT NULL DEFAULT 'medal',
  `tone` varchar(20) NOT NULL DEFAULT 'navy',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `award_types`
--

INSERT INTO `award_types` (`id`, `name`, `description`, `category`, `icon`, `tone`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Subject Excellence', 'Recognizes outstanding mastery of the subject.', 'Academic Excellence', 'medal', 'navy', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(2, 'Outstanding Performance', 'Recognizes consistently high scores across quizzes and assignments.', 'Academic Excellence', 'trophy', 'gold', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(3, 'Academic Achievement', 'Recognizes a notable academic milestone in the subject.', 'Academic Excellence', 'book', 'maroon', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(4, 'Programming Excellence', 'Recognizes outstanding performance in programming work.', 'Subject Mastery', 'code', 'navy', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(5, 'Mathematics Achievement', 'Recognizes excellent problem solving and mathematical reasoning.', 'Subject Mastery', 'sigma', 'teal', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(6, 'Science Achievement', 'Recognizes excellent scientific inquiry and lab work.', 'Subject Mastery', 'flask', 'green', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(7, 'Outstanding Improvement', 'Recognizes significant growth in performance over the term.', 'Improvement', 'growth', 'green', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(8, 'Consistent Performance', 'Recognizes steady, reliable work throughout the term.', 'Consistency', 'target', 'slate', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(9, 'Class Participation', 'Recognizes active and thoughtful participation in class.', 'Participation', 'message', 'teal', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31'),
(10, 'Leadership Achievement', 'Recognizes leadership shown in group work and class activities.', 'Leadership', 'flag', 'maroon', 'Active', NULL, '2026-09-23 17:42:31', '2026-09-23 17:42:31');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(190) NOT NULL,
  `role` varchar(60) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read') NOT NULL DEFAULT 'New',
  `ip_address` varchar(45) DEFAULT NULL,
  `read_by` int(10) UNSIGNED DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `first_name`, `last_name`, `email`, `role`, `subject`, `message`, `status`, `ip_address`, `read_by`, `read_at`, `created_at`) VALUES
(1, 'jerby', 'ardiente', 'ardientejerby26@gmail.com', 'Student', 'elms account', 'disabled my account', 'Read', '::1', 1, '2026-10-08 23:11:27', '2026-10-08 23:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL DEFAULT '1st',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `description`, `school_year`, `semester`, `status`, `created_at`) VALUES
(1, 'SHS-ORALCOM', 'Oral Communication', 'Develops listening and speaking skills in various communicative contexts.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(2, 'SHS-RECOM', 'Reading and Writing', 'Strengthens reading comprehension and academic writing.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(3, 'SHS-21CLIT', '21st Century Literature', 'Survey of Philippine and world literature from the 21st century.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(4, 'SHS-GENMATH', 'General Mathematics', 'Functions, rational expressions, exponential and logarithmic functions, and finance.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(5, 'SHS-STATS', 'Statistics and Probability', 'Data collection, analysis, and interpretation; probability concepts.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(6, 'SHS-EARTH', 'Earth and Life Science', 'Geological, biological, and environmental processes on Earth.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(7, 'SHS-PHYSSCI', 'Physical Science', 'Principles of chemistry and physics and their real-world applications.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(8, 'SHS-PERDEV', 'Personal Development', 'Self-awareness, coping skills, and healthy interpersonal relationships.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(9, 'SHS-UCSP', 'Understanding Culture, Society & Politics', 'Concepts of culture, society, politics, and governance.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(10, 'SHS-CONTEMPH', 'Contemporary Philippine Arts', 'Art forms, their historical context, and creative expression in the Philippines.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(11, 'SHS-MIL', 'Media and Information Literacy', 'Critical analysis and responsible use of media and digital information.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(12, 'SHS-PE1', 'Physical Education and Health 1', 'Fitness, sports, and health promotion for senior high learners.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(13, 'ABM-FABM1', 'Fundamentals of Accountancy, Business and Management', 'Introduction to accounting and bookkeeping concepts.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(14, 'ABM-FABM2', 'Fundamentals of Accountancy, Business and Management 2', 'Advanced bookkeeping, financial statements, and business math.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(15, 'ABM-BUSMATH', 'Business Mathematics', 'Mathematical concepts applied in business settings.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(16, 'ABM-ENTREP', 'Entrepreneurship', 'Business planning, market analysis, and enterprise development.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(17, 'ABM-ORGMGT', 'Organization and Management', 'Principles and functions of management in organizations.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(18, 'HUMSS-APPSOCSCI', 'Applied Social Sciences', 'Introduction to counseling, social work, and communication.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(19, 'GAS-ENTREP', 'Entrepreneurship (GAS)', 'Business planning, market analysis, and enterprise development.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(20, 'SHS-PR', 'Practical Research', 'Introduction to qualitative research methods and writing.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(21, 'SHS-CREATIVEW', 'Creative Writing', 'Creative nonfiction, fiction, poetry, and Philippine popular culture.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(22, 'HUMSS-DIWA', 'Komunikasyon at Pananaliksik', 'Academic writing and research in Filipino.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(23, 'HUMSS-INTRO', 'Introduction to World Religions', 'Survey of major world religions and their cultural impact.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(24, 'HUMSS-DISAS', 'Disciplines and Ideas in Social Sciences', 'Key concepts from psychology, sociology, and anthropology.', '2025-2026', '2nd', 'Active', '2026-03-12 22:47:44'),
(25, 'HS7-ENGLISH', 'English 7', 'Reading comprehension, vocabulary, grammar, and composition.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(26, 'HS7-MATH', 'Mathematics 7', 'Sets, integers, rational numbers, algebraic expressions.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(27, 'HS7-SCI', 'Science 7', 'Matter, living things, ecosystems, and Earth science basics.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(28, 'HS7-FIL', 'Filipino 7', 'Komunikasyon at pag-unawa sa wikang Filipino.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(29, 'HS7-AP', 'Araling Panlipunan 7', 'Kasaysayan ng Asya at Pilipinas.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(30, 'HS8-ENGLISH', 'English 8', 'Literary genres, research skills, and advanced grammar.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(31, 'HS8-MATH', 'Mathematics 8', 'Patterns, functions, linear equations, and geometry.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(32, 'HS8-SCI', 'Science 8', 'Force, motion, waves, light, sound, and heredity.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(33, 'HS8-FIL', 'Filipino 8', 'Pagbasa at pagsulat sa ibat ibang disiplina.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(34, 'HS9-ENGLISH', 'English 9', 'World literature, critical thinking, and writing.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(35, 'HS9-MATH', 'Mathematics 9', 'Quadratic equations, functions, and trigonometry.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(36, 'HS9-SCI', 'Science 9', 'Biodiversity, evolution, and plate tectonics.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(37, 'HS9-FIL', 'Filipino 9', 'Panitikang Filipino at mga anyo ng komunikasyon.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(38, 'HS10-ENGLISH', 'English 10', 'Research-based writing, grammar review, and literary criticism.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(39, 'HS10-MATH', 'Mathematics 10', 'Sequences, polynomial functions, and combinatorics.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(40, 'HS10-SCI', 'Science 10', 'Universe, stars, climate, ecosystems, and genetic engineering.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(41, 'HS10-FIL', 'Filipino 10', 'Pagsulat ng pananaliksik at pagbasa ng ibat ibang teksto.', '2025-2026', '1st', 'Active', '2026-03-12 22:47:44'),
(42, 'HS7-ESP', 'EsP 7', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(43, 'HS7-MAPEH', 'MAPEH 7', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(44, 'HS7-TLE', 'TLE 7', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(45, 'HS8-AP', 'Araling Panlipunan 8', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(46, 'HS8-ESP', 'EsP 8', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(47, 'HS8-MAPEH', 'MAPEH 8', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(48, 'HS8-TLE', 'TLE 8', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(49, 'HS9-AP', 'Araling Panlipunan 9', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(50, 'HS9-ESP', 'EsP 9', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(51, 'HS9-MAPEH', 'MAPEH 9', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(52, 'HS9-TLE', 'TLE 9', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(53, 'HS10-AP', 'Araling Panlipunan 10', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(54, 'HS10-ESP', 'EsP 10', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(55, 'HS10-MAPEH', 'MAPEH 10', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(56, 'HS10-TLE', 'TLE 10', NULL, '2025-2026', '1st', 'Active', '2026-10-04 21:53:11'),
(57, 'SHS-EMPTECH', 'Empowerment Technologies', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(58, 'SHS-APPECON', 'Applied Economics', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(59, 'SHS-PE', 'Physical Education', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(60, 'SHS-WORKIMM', 'Work Immersion', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(61, 'STEM-PRECAL', 'Pre-Calculus', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(62, 'STEM-BIO1', 'General Biology 1', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(63, 'STEM-CHEM1', 'General Chemistry 1', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(64, 'STEM-BIO2', 'General Biology 2', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(65, 'STEM-CHEM2', 'General Chemistry 2', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(66, 'STEM-PHYSICS', 'General Physics', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(67, 'STEM-BASICCAL', 'Basic Calculus', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(68, 'ABM-MARKETING', 'Principles of Marketing', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(69, 'ABM-BUSFIN', 'Business Finance', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(70, 'ABM-BUSETHICS', 'Business Ethics', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(71, 'HUMSS-PPG', 'Philippine Politics and Governance', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(72, 'HUMSS-CNF', 'Creative Nonfiction', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(73, 'HUMSS-TNCT', 'Trends, Networks and Critical Thinking', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(74, 'HUMSS-CESC', 'Community Engagement', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(75, 'GAS-HUMANITIES', 'Humanities', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57'),
(76, 'GAS-SOCIALSCI', 'Social Sciences', NULL, '2025-2026', '1st', 'Active', '2026-10-04 22:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_banners`
--

CREATE TABLE `dashboard_banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(300) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `link_url` varchar(300) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dashboard_banners`
--

INSERT INTO `dashboard_banners` (`id`, `image_path`, `title`, `caption`, `link_url`, `sort_order`, `status`, `created_by`, `created_at`) VALUES
(4, 'uploads/banners/banner_free_tuition.jpg', 'Free Tuition — SY 2026-2027', 'Enrollment ongoing. Limited slots only!', NULL, 10, 'Active', NULL, '2026-09-22 15:14:01'),
(5, 'uploads/banners/banner_books_available.jpg', 'Books Are Now Available', 'Get your books at the Business Office.', NULL, 20, 'Active', NULL, '2026-09-22 15:14:01'),
(6, 'uploads/banners/banner_tesda_enrollment.jpg', 'TESDA: Computer Systems Servicing NC II', 'Application is now open — limited slots.', NULL, 30, 'Active', NULL, '2026-09-22 15:14:01'),
(7, 'uploads/banners/banner_college_admission.jpg', 'College Admission Is Still Open', 'Become President Yolanda Arandia\'s Scholar — free tuition, A.Y. 2026-2027.', NULL, 40, 'Active', NULL, '2026-09-22 15:14:01');

--
-- Triggers `dashboard_banners`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_banner_delete` AFTER DELETE ON `dashboard_banners` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_banner_insert` AFTER INSERT ON `dashboard_banners` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_banner_update` AFTER UPDATE ON `dashboard_banners` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Enrolled','Dropped','Completed') NOT NULL DEFAULT 'Enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`, `status`) VALUES
(15, 10, 30, '2026-03-12 22:47:44', 'Dropped'),
(16, 10, 31, '2026-03-12 22:47:44', 'Dropped'),
(17, 10, 32, '2026-03-12 22:47:44', 'Dropped'),
(18, 10, 33, '2026-03-12 22:47:44', 'Dropped'),
(35, 6, 38, '2026-05-13 20:07:13', 'Enrolled'),
(36, 6, 41, '2026-05-13 20:07:13', 'Enrolled'),
(37, 6, 39, '2026-05-13 20:07:13', 'Enrolled'),
(38, 6, 40, '2026-05-13 20:07:13', 'Enrolled'),
(39, 8, 3, '2026-05-16 18:31:14', 'Enrolled'),
(40, 8, 10, '2026-05-16 18:31:14', 'Dropped'),
(41, 8, 6, '2026-05-16 18:31:14', 'Dropped'),
(42, 8, 4, '2026-05-16 18:31:14', 'Enrolled'),
(43, 8, 11, '2026-05-16 18:31:14', 'Dropped'),
(44, 8, 1, '2026-05-16 18:31:14', 'Enrolled'),
(45, 8, 12, '2026-05-16 18:31:14', 'Dropped'),
(46, 8, 8, '2026-05-16 18:31:14', 'Dropped'),
(47, 8, 15, '2026-09-21 21:12:31', 'Dropped'),
(48, 8, 16, '2026-09-21 21:12:31', 'Dropped'),
(49, 8, 13, '2026-09-21 21:12:31', 'Dropped'),
(50, 8, 14, '2026-09-21 21:12:31', 'Dropped'),
(51, 8, 17, '2026-09-21 21:12:31', 'Dropped'),
(52, 10, 11, '2026-09-22 14:19:57', 'Dropped'),
(53, 6, 2, '2026-09-22 19:14:54', 'Dropped'),
(54, 6, 5, '2026-09-22 19:14:54', 'Dropped'),
(55, 6, 13, '2026-09-22 19:14:54', 'Dropped'),
(56, 6, 19, '2026-09-22 19:14:54', 'Dropped'),
(57, 6, 20, '2026-09-22 19:14:54', 'Dropped'),
(58, 18, 28, '2026-10-04 21:56:11', 'Enrolled'),
(59, 18, 25, '2026-10-04 21:56:11', 'Enrolled'),
(60, 18, 26, '2026-10-04 21:56:11', 'Enrolled'),
(61, 18, 27, '2026-10-04 21:56:11', 'Enrolled'),
(62, 18, 29, '2026-10-04 21:56:11', 'Enrolled'),
(63, 18, 42, '2026-10-04 21:56:11', 'Enrolled'),
(64, 18, 43, '2026-10-04 21:56:11', 'Enrolled'),
(65, 18, 44, '2026-10-04 21:56:11', 'Enrolled'),
(73, 10, 4, '2026-10-05 01:03:37', 'Enrolled'),
(74, 10, 5, '2026-10-05 01:03:37', 'Enrolled'),
(75, 10, 1, '2026-10-05 01:03:37', 'Enrolled'),
(76, 10, 2, '2026-10-05 01:03:37', 'Enrolled'),
(77, 10, 57, '2026-10-05 01:03:37', 'Enrolled'),
(78, 10, 15, '2026-10-05 01:03:37', 'Enrolled'),
(79, 10, 13, '2026-10-05 01:03:37', 'Enrolled'),
(80, 10, 17, '2026-10-05 01:03:37', 'Enrolled'),
(81, 10, 68, '2026-10-05 01:03:37', 'Enrolled'),
(88, 19, 37, '2026-10-05 18:48:43', 'Enrolled'),
(89, 20, 37, '2026-10-06 00:02:57', 'Enrolled'),
(90, 21, 37, '2026-10-06 00:02:57', 'Enrolled'),
(91, 22, 37, '2026-10-06 00:02:57', 'Enrolled'),
(92, 23, 37, '2026-10-06 00:02:57', 'Enrolled'),
(93, 24, 37, '2026-10-06 00:02:57', 'Enrolled'),
(96, 27, 38, '2026-10-08 03:16:24', 'Enrolled'),
(97, 27, 41, '2026-10-08 03:16:24', 'Enrolled'),
(98, 27, 39, '2026-10-08 03:16:24', 'Enrolled'),
(99, 27, 40, '2026-10-08 03:16:24', 'Enrolled'),
(100, 27, 53, '2026-10-08 03:16:24', 'Enrolled'),
(101, 27, 54, '2026-10-08 03:16:24', 'Enrolled'),
(102, 27, 55, '2026-10-08 03:16:24', 'Enrolled'),
(103, 27, 56, '2026-10-08 03:16:24', 'Enrolled'),
(104, 6, 53, '2026-10-09 21:25:56', 'Enrolled'),
(105, 6, 54, '2026-10-09 21:25:56', 'Enrolled'),
(106, 6, 55, '2026-10-09 21:25:56', 'Enrolled'),
(107, 6, 56, '2026-10-09 21:25:56', 'Enrolled'),
(108, 8, 5, '2026-10-09 21:25:56', 'Enrolled'),
(109, 8, 2, '2026-10-09 21:25:56', 'Enrolled'),
(110, 8, 57, '2026-10-09 21:25:56', 'Enrolled'),
(111, 8, 21, '2026-10-09 21:25:56', 'Enrolled'),
(112, 8, 23, '2026-10-09 21:25:56', 'Enrolled'),
(113, 8, 24, '2026-10-09 21:25:56', 'Enrolled'),
(114, 8, 71, '2026-10-09 21:25:56', 'Enrolled'),
(115, 19, 34, '2026-10-09 21:25:56', 'Enrolled'),
(116, 19, 35, '2026-10-09 21:25:56', 'Enrolled'),
(117, 19, 36, '2026-10-09 21:25:56', 'Enrolled'),
(118, 19, 49, '2026-10-09 21:25:56', 'Enrolled'),
(119, 19, 50, '2026-10-09 21:25:56', 'Enrolled'),
(120, 19, 51, '2026-10-09 21:25:56', 'Enrolled'),
(121, 19, 52, '2026-10-09 21:25:56', 'Enrolled'),
(122, 20, 34, '2026-10-09 21:25:56', 'Enrolled'),
(123, 20, 35, '2026-10-09 21:25:56', 'Enrolled'),
(124, 20, 36, '2026-10-09 21:25:56', 'Enrolled'),
(125, 20, 49, '2026-10-09 21:25:56', 'Enrolled'),
(126, 20, 50, '2026-10-09 21:25:56', 'Enrolled'),
(127, 20, 51, '2026-10-09 21:25:56', 'Enrolled'),
(128, 20, 52, '2026-10-09 21:25:56', 'Enrolled'),
(129, 21, 34, '2026-10-09 21:25:56', 'Enrolled'),
(130, 21, 35, '2026-10-09 21:25:56', 'Enrolled'),
(131, 21, 36, '2026-10-09 21:25:56', 'Enrolled'),
(132, 21, 49, '2026-10-09 21:25:56', 'Enrolled'),
(133, 21, 50, '2026-10-09 21:25:56', 'Enrolled'),
(134, 21, 51, '2026-10-09 21:25:56', 'Enrolled'),
(135, 21, 52, '2026-10-09 21:25:56', 'Enrolled'),
(136, 22, 34, '2026-10-09 21:25:56', 'Enrolled'),
(137, 22, 35, '2026-10-09 21:25:56', 'Enrolled'),
(138, 22, 36, '2026-10-09 21:25:56', 'Enrolled'),
(139, 22, 49, '2026-10-09 21:25:56', 'Enrolled'),
(140, 22, 50, '2026-10-09 21:25:56', 'Enrolled'),
(141, 22, 51, '2026-10-09 21:25:56', 'Enrolled'),
(142, 22, 52, '2026-10-09 21:25:56', 'Enrolled'),
(143, 23, 34, '2026-10-09 21:25:56', 'Enrolled'),
(144, 23, 35, '2026-10-09 21:25:56', 'Enrolled'),
(145, 23, 36, '2026-10-09 21:25:56', 'Enrolled'),
(146, 23, 49, '2026-10-09 21:25:56', 'Enrolled'),
(147, 23, 50, '2026-10-09 21:25:56', 'Enrolled'),
(148, 23, 51, '2026-10-09 21:25:56', 'Enrolled'),
(149, 23, 52, '2026-10-09 21:25:56', 'Enrolled'),
(150, 24, 34, '2026-10-09 21:25:56', 'Enrolled'),
(151, 24, 35, '2026-10-09 21:25:56', 'Enrolled'),
(152, 24, 36, '2026-10-09 21:25:56', 'Enrolled'),
(153, 24, 49, '2026-10-09 21:25:56', 'Enrolled'),
(154, 24, 50, '2026-10-09 21:25:56', 'Enrolled'),
(155, 24, 51, '2026-10-09 21:25:56', 'Enrolled'),
(156, 24, 52, '2026-10-09 21:25:56', 'Enrolled'),
(157, 29, 28, '2026-10-09 21:25:56', 'Enrolled'),
(158, 29, 25, '2026-10-09 21:25:56', 'Enrolled'),
(159, 29, 26, '2026-10-09 21:25:56', 'Enrolled'),
(160, 29, 27, '2026-10-09 21:25:56', 'Enrolled'),
(161, 29, 29, '2026-10-09 21:25:56', 'Enrolled'),
(162, 29, 42, '2026-10-09 21:25:56', 'Enrolled'),
(163, 29, 43, '2026-10-09 21:25:56', 'Enrolled'),
(164, 29, 44, '2026-10-09 21:25:56', 'Enrolled');

--
-- Triggers `enrollments`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_enrollment_delete` AFTER DELETE ON `enrollments` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('enrollment.changed', OLD.course_id, (SELECT section_dept FROM users WHERE id = OLD.student_id), NULL, OLD.student_id, NULL, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_enrollment_insert` AFTER INSERT ON `enrollments` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('enrollment.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULL, NEW.student_id, NULL, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_enrollment_update` AFTER UPDATE ON `enrollments` FOR EACH ROW BEGIN
  IF NOT (NEW.status <=> OLD.status AND NEW.course_id <=> OLD.course_id) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('enrollment.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULL, NEW.student_id, NULL, 'all', NULL);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `gradebook_items`
--

CREATE TABLE `gradebook_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Teacher who created it; NULL = shared by every section',
  `source_type` enum('assignment','quiz','manual') NOT NULL,
  `source_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'assignments.id or quizzes.id; NULL for manual items',
  `title` varchar(255) NOT NULL,
  `max_score` decimal(7,2) NOT NULL DEFAULT 100.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gradebook_items`
--

INSERT INTO `gradebook_items` (`id`, `course_id`, `teacher_id`, `source_type`, `source_id`, `title`, `max_score`, `created_at`) VALUES
(1, 15, 15, 'assignment', 3, 'Essay: Five Examples of Computers', 80.00, '2026-09-22 19:59:45'),
(2, 37, 15, 'assignment', 4, 'Assigement 233', 100.00, '2026-10-05 18:43:54'),
(3, 37, 15, 'assignment', 5, 'Gawain 1: Ang Haiku', 50.00, '2026-10-05 18:48:43'),
(4, 15, 15, 'quiz', 8, 'the great', 100.00, '2026-09-21 21:14:06'),
(5, 15, 15, 'quiz', 9, 'the great', 20.00, '2026-09-22 16:43:58'),
(6, 15, 15, 'quiz', 10, 'the great', 40.00, '2026-09-22 17:10:24'),
(7, 37, 15, 'quiz', 11, 'quiz 1', 60.00, '2026-10-05 19:19:20'),
(8, 37, 15, 'assignment', 6, 'Pagsusulat ng Sanaysay tungkol sa Batayang Tagalog', 85.00, '2026-10-05 20:28:01'),
(9, 37, 15, 'assignment', 7, 'Essay on Panitikang Asyano', 80.00, '2026-10-05 20:31:50'),
(10, 37, 15, 'assignment', 8, 'Gawain 2: Ang Tanka', 50.00, '2026-10-06 00:02:57'),
(11, 37, 15, 'assignment', 9, 'Gawain 3: Sanaysay', 100.00, '2026-10-06 00:02:57'),
(12, 37, 15, 'quiz', 12, 'Maikling Pagsusulit 1', 20.00, '2026-10-06 00:02:57'),
(13, 38, 14, 'assignment', 10, 'chap1', 100.00, '2026-10-08 03:22:20'),
(14, 38, 14, 'quiz', 13, 'test', 10.00, '2026-10-08 03:38:37'),
(15, 53, 14, 'assignment', 11, 'testing', 100.00, '2026-10-08 04:14:43'),
(16, 53, 14, 'assignment', 12, 'assignment', 100.00, '2026-10-09 14:08:52'),
(17, 38, 14, 'assignment', 13, 'test', 100.00, '2026-10-09 20:26:46'),
(18, 38, 14, 'assignment', 14, 'test', 100.00, '2026-10-09 20:29:59'),
(19, 38, 14, 'quiz', 14, 'Activity 1', 10.00, '2026-10-16 14:30:12'),
(20, 38, 14, 'quiz', 15, 'AI Automation', 20.00, '2026-10-16 15:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `gradebook_scores`
--

CREATE TABLE `gradebook_scores` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `score` decimal(7,2) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `gradebook_scores`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_gbscore_delete` AFTER DELETE ON `gradebook_scores` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM gradebook_items WHERE id = OLD.item_id), (SELECT section_dept FROM users WHERE id = OLD.student_id), (SELECT teacher_id FROM gradebook_items WHERE id = OLD.item_id), OLD.student_id, OLD.item_id, 'all', (SELECT title FROM gradebook_items WHERE id = OLD.item_id));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_gbscore_insert` AFTER INSERT ON `gradebook_scores` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM gradebook_items WHERE id = NEW.item_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM gradebook_items WHERE id = NEW.item_id), NEW.student_id, NEW.item_id, 'all', (SELECT title FROM gradebook_items WHERE id = NEW.item_id));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_gbscore_update` AFTER UPDATE ON `gradebook_scores` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM gradebook_items WHERE id = NEW.item_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM gradebook_items WHERE id = NEW.item_id), NEW.student_id, NEW.item_id, 'all', (SELECT title FROM gradebook_items WHERE id = NEW.item_id));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `max_score` decimal(6,2) DEFAULT NULL,
  `raw_score` decimal(6,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `final_score` decimal(6,4) DEFAULT NULL,
  `midterm_grade` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_items`
--

CREATE TABLE `grade_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `period` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `max_score` decimal(6,2) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `email` varchar(190) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_attempt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`email`, `attempts`, `locked_until`, `last_attempt`) VALUES
('2usagdgasdg@gmasil.com', 1, NULL, '2026-10-15 23:36:28'),
('adasdg@gmail.com', 3, '2026-10-15 23:39:53', '2026-10-15 23:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `login_otps`
--

CREATE TABLE `login_otps` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_otps`
--

INSERT INTO `login_otps` (`user_id`, `otp_hash`, `expires_at`, `used`) VALUES
(1, '$2y$10$vRfs8UtPG0UnFLqiq8CnDO8GdJIi2l2ZKz1GA88K.qlRkpsS8mHFS', '2026-10-16 22:02:36', 1),
(3, '$2y$10$omjq4clKGufCKenfJ4PMbu82YbrP5Js9GlXA75Q0wvTNvkAoamYHS', '2026-06-19 22:27:11', 1),
(6, '$2y$10$i9RxsWZXfcZXz.o4lB9hj.r9kRmyFqQlJJKw0eq2UBn8FWZR56MNG', '2026-06-17 22:17:33', 1),
(8, '$2y$10$QuEWkvY4cv2RbSCBQf8bN.l57bwGqGDax2U2nUyiSzsQ/OWQj3cIG', '2026-09-22 16:54:27', 1),
(10, '$2y$10$Z6BZ.rv5VdV/kpFIvJrU/ecKrd5D/hqVdxNLr8/rLkUb6mi/4rDqG', '2026-10-16 14:32:04', 1),
(14, '$2y$10$bNnnbM6ucEo93qmLXf76U./2Ebkvz3r2mXoFOtFnUy9jsYY9tJLX.', '2026-10-16 14:32:31', 1),
(15, '$2y$10$8.UEhpPjErfNrga3kLPkoO/y6KM733BvS3JsI2i.set9B50aVgQFq', '2026-10-07 10:54:58', 1),
(27, '$2y$10$Lenl.WRYybXQIdxTQuLJruOP5E/RXBaMDIeMVHIHLzPAlExZYmFfe', '2026-10-16 14:41:07', 1),
(29, '$2y$10$x/b4B67KMFco1ge5OSQjXObq7NEsJrm86QFIRIAjnxPjh.ESLosZi', '2026-10-09 21:39:19', 0);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `module_number` smallint(5) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  `file_path` varchar(300) DEFAULT NULL,
  `week_number` tinyint(3) UNSIGNED DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `description`, `content`, `file_path`, `week_number`, `published`, `updated_by`, `created_at`, `updated_at`) VALUES
(3, 39, 6, 'week1', 'module 1', NULL, 'uploads/modules/mod_69be939922922.pdf', NULL, 1, NULL, '2026-03-21 20:48:25', '2026-10-04 23:55:38'),
(4, 40, 6, 'week1', 'asdasdas', NULL, 'uploads/modules/mod_69be93b37e9b2.pdf', NULL, 1, NULL, '2026-03-21 20:48:51', '2026-10-04 23:55:38'),
(5, 38, 6, 'week2', '', NULL, 'uploads/modules/mod_69c14eb535714.pdf', 3, 1, NULL, '2026-03-23 22:31:17', '2026-10-04 23:55:38'),
(6, 15, 6, 'Module1 basic mathematics', '', NULL, 'uploads/modules/mod_6ab22e779d767.txt', 1, 1, NULL, '2026-09-22 15:29:59', '2026-10-04 23:55:38'),
(7, 15, 7, 'Module1 basic mathematics', '13', NULL, 'uploads/modules/mod_6ab22ebc94d9f.txt', 12, 1, NULL, '2026-09-22 15:31:08', '2026-10-04 23:55:38'),
(8, 15, 8, 'Module1 basic mathematics', 'checking', NULL, 'uploads/modules/mod_6ab235d99d91a.docx', 1, 1, NULL, '2026-09-22 16:01:29', '2026-10-04 23:55:38'),
(9, 28, 1, 'Kuwentong-bayan', 'Mga salaysay na nagpasalin-salin sa bibig ng mga tao at nagpapakita ng kultura ng isang pook.', '<p>Bago pa nagkaroon ng aklat at paaralan, ikinukuwento na ng ating mga ninuno ang mga salaysay tungkol sa kanilang buhay, paniniwala, at karanasan. Ipinapasa ang mga kuwentong ito mula sa magulang patungo sa anak, kaya umabot ang mga ito hanggang sa ating panahon.</p><p>Sa modyul na ito, pag-aaralan mo ang kuwentong-bayan, ang mga katangian nito, at kung bakit mahalaga pa rin ito sa atin bilang mga Pilipino.</p><h3>Ano ang kuwentong-bayan?</h3><ul><li><strong>Kuwentong-bayan</strong>: Ito ay salaysay na nagmula sa isang tiyak na pook at nagpasalin-salin sa bibig ng mga tao. Walang tiyak na may-akda ang karamihan sa mga ito dahil maraming tao ang nagkuwento at nagdagdag dito sa paglipas ng panahon. Dahil dito, maaaring magkakaiba ang bersyon ng iisang kuwento sa iba\'t ibang lugar.</li><li><strong>Pasalindila</strong>: Tumutukoy ito sa paraan ng pagpapasa ng kuwento sa pamamagitan ng pagsasalaysay, hindi pagsusulat. Ito ang pangunahing paraan ng pagpapanatili ng kaalaman noong wala pang palimbagan. Ito rin ang dahilan kung bakit madaling tandaan ang mga kuwentong-bayan: payak ang mga pangyayari at malinaw ang aral.</li></ul><h3>Mga katangian ng kuwentong-bayan</h3><ul><li><strong>Tauhan</strong>: Karaniwang tao, hayop, o nilalang na may kapangyarihan ang mga tauhan. Madalas na may tauhang kumakatawan sa kabutihan at tauhang kumakatawan sa kasamaan, gaya ng tapat at sakim. Sa pamamagitan ng kanilang mga ginagawa, nakikita ng mambabasa kung ano ang dapat at hindi dapat tularan.</li><li><strong>Tagpuan</strong>: Karaniwang nasa isang nayon, gubat, bundok, o tabing-dagat ang tagpuan, na pamilyar sa mga taong nagkukuwento. Ipinapakita nito ang uri ng pamumuhay sa pook na pinagmulan ng kuwento.</li><li><strong>Banghay</strong>: Payak at madaling sundan ang pagkakasunod-sunod ng mga pangyayari. May simula, gitna, at wakas, at kadalasang nagtatapos sa gantimpala para sa mabuti at parusa para sa masama.</li><li><strong>Aral</strong>: Ito ang pinakamahalagang bahagi ng kuwentong-bayan. Itinuturo nito ang mabubuting asal gaya ng sipag, katapatan, pagtulong sa kapwa, at paggalang sa nakatatanda.</li></ul><h3>Kuwentong-bayan bilang salamin ng kultura</h3><p>Ang kultura ay ang kabuuang paraan ng pamumuhay ng isang pangkat ng tao: ang kanilang paniniwala, kaugalian, pagkain, at hanapbuhay. Makikita ang kultura sa kuwentong-bayan sa iba\'t ibang paraan.</p><ul><li>Ipinapakita nito ang hanapbuhay ng mga tao, gaya ng pagsasaka, pangingisda, at pangangaso.</li><li>Ipinapakita nito ang paniniwala sa mga diwata, engkanto, at iba pang nilalang na nagbabantay sa kalikasan.</li><li>Ipinapakita nito ang mga pagpapahalaga ng pamayanan, gaya ng pagtutulungan at paggalang sa magulang.</li></ul><p>Halimbawa, sa maraming kuwento mula sa Mindanao, gaya ng sa mga Maranao at Manobo, makikita ang pagpapahalaga sa angkan, sa lupa, at sa mga ritwal ng pamayanan.</p><h3>Halimbawa: Si Juan Tamad</h3><table><thead><tr><th>Bahagi</th><th>Paliwanag</th></tr></thead><tbody><tr><td>Tauhan</td><td>Si Juan, isang batang ubod ng tamad</td></tr><tr><td>Pangyayari</td><td>Naghintay si Juan sa ilalim ng puno na kusang malaglag ang bunga sa kanyang bibig sa halip na pitasin ito</td></tr><tr><td>Bunga</td><td>Wala siyang nakain at napagtawanan ng mga tao</td></tr><tr><td>Aral</td><td>Hindi darating ang biyaya kung hindi kikilos; kailangan ang sipag upang makamit ang gusto</td></tr></tbody></table><h3>Pagsusulit</h3><ol><li>Ano ang ibig sabihin ng pasalindila?</li><li>Bakit walang tiyak na may-akda ang karamihan sa kuwentong-bayan?</li><li>Paano naipapakita ng kuwentong-bayan ang kultura ng isang pook?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pagpapasa ng kuwento sa pamamagitan ng pagsasalaysay, hindi pagsusulat.</li><li>Dahil maraming tao ang nagkuwento at nagdagdag dito sa paglipas ng panahon.</li><li>Sa pamamagitan ng hanapbuhay, paniniwala, at pagpapahalagang makikita sa kuwento.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(10, 28, 2, 'Pabula at Alamat', 'Dalawang anyo ng maikling kuwento na nagtuturo ng aral at nagpapaliwanag ng pinagmulan ng mga bagay.', '<p>Ang pabula at alamat ay kabilang sa mga pinakamatandang anyo ng panitikan. Pareho silang maikli, madaling tandaan, at may aral. Gayunman, magkaiba ang kanilang mga tauhan at ang layunin ng kanilang pagkukuwento.</p><p>Sa modyul na ito, kikilalanin mo ang bawat isa, ang kanilang mga elemento, at kung paano sila suriin.</p><h3>Ang pabula</h3><ul><li><strong>Kahulugan</strong>: Ang pabula ay maikling kuwento na ang mga tauhan ay hayop na kumikilos, nag-iisip, at nagsasalita na parang tao. Sa pamamagitan ng mga hayop, naipapakita ng kuwento ang ugali ng tao nang hindi tuwirang pinupuna ang sinuman.</li><li><strong>Simbolismo ng mga hayop</strong>: Karaniwang kumakatawan ang bawat hayop sa isang katangian. Halimbawa, ang matsing ay madalas na tuso, ang pagong ay mabagal ngunit matiyaga, at ang leon ay makapangyarihan. Dahil dito, madaling maunawaan ng mambabasa ang mensahe.</li><li><strong>Moral o aral</strong>: Laging may malinaw na aral ang pabula, at kung minsan ay tuwiran itong isinusulat sa dulo ng kuwento. Kilala sa daigdig ang mga pabula ni Aesop, isang manunulat mula sa sinaunang Gresya.</li></ul><h3>Ang alamat</h3><ul><li><strong>Kahulugan</strong>: Ang alamat ay kuwentong nagsasalaysay ng pinagmulan ng isang bagay, lugar, halaman, hayop, o pangalan. Sinasagot nito ang tanong na \"Paano ito nagsimula?\" sa paraang kawili-wili at kadalasang may halong hiwaga.</li><li><strong>Mga elemento</strong>: Tulad ng ibang kuwento, may tauhan, tagpuan, at banghay ang alamat. Ang kaibahan ay nagtatapos ito sa pagsilang o pagkakaroon ng isang bagay na kilala natin ngayon, gaya ng prutas o pangalan ng isang bayan.</li><li><strong>Aral sa alamat</strong>: Bagaman nagpapaliwanag ito ng pinagmulan, may aral din ang alamat. Madalas na itinuturo nito ang pagsunod sa magulang, ang pagiging masipag, at ang masamang bunga ng kasakiman.</li></ul><h3>Paano suriin ang pabula at alamat</h3><ul><li>Tukuyin ang mga tauhan at ang katangiang kinakatawan ng bawat isa.</li><li>Isulat ang mahahalagang pangyayari ayon sa pagkakasunod-sunod.</li><li>Alamin kung ano ang ipinaliliwanag ng kuwento (para sa alamat) o anong ugali ang pinupuna nito (para sa pabula).</li><li>Bumuo ng aral sa isang buong pangungusap at iugnay ito sa tunay na buhay.</li></ul><h3>Paghahambing ng pabula at alamat</h3><table><thead><tr><th></th><th>Pabula</th><th>Alamat</th></tr></thead><tbody><tr><td>Tauhan</td><td>Hayop na kumikilos na parang tao</td><td>Tao, diwata, o iba pang nilalang</td></tr><tr><td>Layunin</td><td>Magturo ng aral tungkol sa ugali</td><td>Ipaliwanag ang pinagmulan ng isang bagay</td></tr><tr><td>Halimbawa</td><td>Ang Pagong at ang Matsing</td><td>Alamat ng Pinya</td></tr></tbody></table><h3>Pagsusulit</h3><ol><li>Ano ang pagkakaiba ng tauhan sa pabula at sa alamat?</li><li>Anong tanong ang sinasagot ng alamat?</li><li>Sino ang kilalang manunulat ng pabula mula sa sinaunang Gresya?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Hayop ang tauhan sa pabula; tao o iba pang nilalang ang karaniwang tauhan sa alamat.</li><li>Paano o saan nagsimula ang isang bagay?</li><li>Aesop</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(11, 28, 3, 'Mga Bahagi ng Pananalita', 'Ang mga salita ay may kani-kaniyang gamit sa pangungusap, kaya mahalagang makilala ang bawat bahagi ng pananalita.', '<p>Ang mga salita ay may kani-kaniyang gamit sa pangungusap, kaya mahalagang makilala ang bawat bahagi ng pananalita.</p><h3>Mahahalagang Punto</h3><ul><li>Pangngalan ang tawag sa ngalan ng tao, bagay, hayop, pook, o pangyayari.</li><li>Pandiwa ang salitang nagsasaad ng kilos o galaw.</li><li>Pang-uri ang naglalarawan sa pangngalan, at pang-abay ang naglalarawan sa pandiwa, pang-uri, o kapwa pang-abay.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng limang pangungusap at salungguhitan ang pandiwa sa bawat isa.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(12, 28, 4, 'Ibong Adarna', 'Ang Ibong Adarna ay korido na nagsasalaysay ng paghahanap ng tatlong prinsipe sa mahiwagang ibon na lunas sa sakit ng kanilang amang hari.', '<p>Ang Ibong Adarna ay korido na nagsasalaysay ng paghahanap ng tatlong prinsipe sa mahiwagang ibon na lunas sa sakit ng kanilang amang hari.</p><h3>Mahahalagang Punto</h3><ul><li>Ang korido ay tulang may walong pantig bawat taludtod.</li><li>Pangunahing tauhan sina Don Pedro, Don Diego, at Don Juan.</li><li>Itinuturo nito ang kabutihang-loob, pagpapatawad, at katapatan sa pamilya.</li></ul><h3>Subukan Mo</h3><p>Ihambing ang ugali ni Don Juan sa kanyang mga kapatid.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(13, 28, 5, 'Pagsulat ng Talata', 'Ang talata ay pangkat ng mga pangungusap na may iisang paksa at malinaw na pagkakasunod-sunod ng ideya.', '<p>Ang talata ay pangkat ng mga pangungusap na may iisang paksa at malinaw na pagkakasunod-sunod ng ideya.</p><h3>Mahahalagang Punto</h3><ul><li>Nagsisimula ito sa paksang pangungusap na nagsasabi ng pangunahing ideya.</li><li>Sinusundan ito ng mga pansuportang pangungusap na nagbibigay ng detalye.</li><li>Nagtatapos ito sa pangungusap na nagbubuod o nagbibigay-diin.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng isang talata tungkol sa iyong paboritong lugar sa inyong bayan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:41:18'),
(14, 25, 1, 'Philippine Folk Literature', 'The stories, sayings, and songs our ancestors passed down by word of mouth.', '<p>Long before Filipinos had printed books, they already had a rich literature. Stories, songs, riddles, and sayings were shared aloud around the fire, in the fields, and during celebrations. This body of work is called folk literature, and it remains an important part of our identity.</p><p>In this module, you will learn the main forms of Philippine folk literature and what each form tells us about the people who created it.</p><h3>What is folk literature?</h3><ul><li><strong>Folk literature</strong>: Folk literature is the collection of stories, songs, and sayings created by ordinary people and passed down orally from one generation to the next. Because it was spoken rather than written, a single story can have many versions depending on the region or the storyteller.</li><li><strong>Oral tradition</strong>: Oral tradition is the practice of preserving knowledge through speech. Our ancestors used it to teach history, values, and practical skills. Repetition, rhythm, and simple plots made these works easy to remember and retell.</li></ul><h3>Forms of folk literature</h3><ul><li><strong>Myths</strong>: Myths are stories about gods, goddesses, and supernatural beings. They often explain how the world, people, or natural events came to be. Many Tagalog myths, for example, name Bathala as the creator of the world.</li><li><strong>Legends</strong>: Legends explain the origin of a place, plant, animal, or name. They are usually set in a real location and may include real people mixed with magical events. The legend of Maria Makiling tells of a kind spirit who protects Mount Makiling in Laguna.</li><li><strong>Folk tales</strong>: Folk tales are simple stories about ordinary people or animals that usually teach a lesson. Characters are often clearly good or bad, and the ending rewards kindness and punishes greed or laziness.</li><li><strong>Epics</strong>: Epics are long narrative poems about heroes who perform great deeds. They were chanted or sung during important gatherings. Examples include Biag ni Lam-ang of the Ilocanos and the Hudhud of the Ifugao.</li><li><strong>Riddles and proverbs</strong>: Riddles (bugtong) are puzzles that describe something in a clever, hidden way. Proverbs (salawikain) are short sayings that express wisdom about life, such as advice on hard work or respect.</li></ul><h3>Why folk literature matters</h3><ul><li>It preserves the history and beliefs of our ancestors.</li><li>It teaches values such as respect for elders, hard work, and honesty.</li><li>It shows how different regions of the Philippines see the world.</li></ul><p>When you read folk literature, you are hearing the voices of Filipinos who lived hundreds of years ago.</p><h3>Summary of forms</h3><table><thead><tr><th>Form</th><th>Main purpose</th><th>Example</th></tr></thead><tbody><tr><td>Myth</td><td>Explains creation and nature through gods</td><td>Bathala creating the world</td></tr><tr><td>Legend</td><td>Explains the origin of a place or thing</td><td>Maria Makiling</td></tr><tr><td>Folk tale</td><td>Teaches a lesson through a simple story</td><td>Juan Tamad</td></tr><tr><td>Epic</td><td>Celebrates the deeds of a hero</td><td>Biag ni Lam-ang</td></tr></tbody></table><h3>Quick check</h3><ol><li>What is oral tradition?</li><li>How is a legend different from a myth?</li><li>Give one Philippine epic.</li></ol><details><summary>Show answers</summary><ol><li>The practice of preserving knowledge and stories through speech.</li><li>A legend explains the origin of a place or thing; a myth is about gods and creation.</li><li>Any one: Biag ni Lam-ang, Hudhud</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(15, 25, 2, 'Parts of Speech', 'The eight kinds of words and the job each one does in a sentence.', '<p>Every word in a sentence has a job. Some words name things, some show action, and some connect ideas. These jobs are called the parts of speech. Knowing them helps you build correct sentences and understand grammar rules more easily.</p><p>English has eight parts of speech. In this module, you will learn what each one does and how to identify it.</p><h3>Words that name and replace</h3><ul><li><strong>Noun</strong>: A noun names a person, place, thing, or idea. Common nouns are general, like teacher or city, while proper nouns name specific ones and begin with a capital letter, like Mrs. Santos or Cebu. Nouns can also name things you cannot touch, such as honesty or happiness.</li><li><strong>Pronoun</strong>: A pronoun takes the place of a noun so we do not repeat the same word. Instead of saying \"Ana said Ana is tired,\" we say \"Ana said she is tired.\" Common pronouns include I, you, he, she, it, we, and they.</li></ul><h3>Words that show action and describe</h3><ul><li><strong>Verb</strong>: A verb shows an action (run, write, eat) or a state of being (is, am, are, was). Every complete sentence needs at least one verb. Verbs also change form to show time, such as walk, walked, and will walk.</li><li><strong>Adjective</strong>: An adjective describes a noun or pronoun. It answers questions like which one, what kind, or how many. In \"three tall trees,\" both three and tall are adjectives.</li><li><strong>Adverb</strong>: An adverb describes a verb, an adjective, or another adverb. It answers how, when, where, or to what extent. Many adverbs end in -ly, such as quickly and carefully, but words like very, often, and here are also adverbs.</li></ul><h3>Words that connect and express</h3><ul><li><strong>Preposition</strong>: A preposition shows the relationship between a noun and another word, often about place or time. Examples are in, on, under, before, and between, as in \"The book is on the table.\"</li><li><strong>Conjunction</strong>: A conjunction joins words, phrases, or sentences. The most common ones are and, but, or, and so. For example: \"I wanted to go, but it was raining.\"</li><li><strong>Interjection</strong>: An interjection is a word that expresses strong feeling or surprise. It is often followed by an exclamation point, as in \"Wow!\" or \"Ouch!\"</li></ul><h3>Identifying parts of speech</h3><table><thead><tr><th>Word in \"Wow, the tall boy quickly ran to the gate.\"</th><th>Part of speech</th></tr></thead><tbody><tr><td>Wow</td><td>Interjection</td></tr><tr><td>tall</td><td>Adjective</td></tr><tr><td>boy</td><td>Noun</td></tr><tr><td>quickly</td><td>Adverb</td></tr><tr><td>ran</td><td>Verb</td></tr><tr><td>to</td><td>Preposition</td></tr><tr><td>gate</td><td>Noun</td></tr></tbody></table><h3>Quick check</h3><ol><li>What part of speech replaces a noun?</li><li>In \"She sings beautifully,\" what part of speech is beautifully?</li><li>What is the difference between a common noun and a proper noun?</li></ol><details><summary>Show answers</summary><ol><li>Pronoun</li><li>Adverb</li><li>A common noun is general; a proper noun names a specific person, place, or thing and is capitalized.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(16, 25, 3, 'Subject-Verb Agreement', 'A verb must agree with its subject in number: singular subjects take singular verbs and plural subjects take plural verbs.', '<p>A verb must agree with its subject in number: singular subjects take singular verbs and plural subjects take plural verbs.</p><h3>Key points</h3><ul><li>&quot;She walks&quot; is singular; &quot;They walk&quot; is plural.</li><li>Subjects joined by &quot;and&quot; are usually plural.</li><li>With &quot;either...or&quot; and &quot;neither...nor,&quot; the verb agrees with the nearer subject.</li></ul><h3>Try this</h3><p>Correct this sentence: &quot;The list of names are on the board.&quot;</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(17, 25, 4, 'Reading for Main Idea and Details', 'Good readers find the main idea first, then the details that support it.', '<p>Good readers find the main idea first, then the details that support it.</p><h3>Key points</h3><ul><li>The main idea is what the whole text is mostly about.</li><li>Supporting details give examples, facts, or reasons.</li><li>The main idea is often, but not always, in the first or last sentence.</li></ul><h3>Try this</h3><p>Read a short news article and write its main idea in one sentence.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(18, 25, 5, 'Writing a Personal Narrative', 'A personal narrative tells a true story from your own life in a clear and interesting way.', '<p>A personal narrative tells a true story from your own life in a clear and interesting way.</p><h3>Key points</h3><ul><li>Use the first person (&quot;I&quot;) and tell events in order.</li><li>Include sensory details so readers can picture the scene.</li><li>End with what you learned or how the experience changed you.</li></ul><h3>Try this</h3><p>Write a personal narrative about a time you overcame a challenge.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(19, 26, 1, 'Sets', 'Collections of objects, how to describe them, and how to combine them.', '<p>We group things all the time: school supplies in a bag, classmates in a section, or songs in a playlist. In mathematics, a well-defined group of objects is called a set. Sets give us a clear language for describing and comparing groups.</p><p>In this module, you will learn how to write sets, the different kinds of sets, and the basic operations on them.</p><h3>Basic ideas</h3><ul><li><strong>Set</strong>: A set is a well-defined collection of objects. \"Well-defined\" means it is always clear whether an object belongs to the set or not. \"The vowels in the English alphabet\" is a set, but \"the beautiful flowers\" is not, because people may disagree on which flowers are beautiful.</li><li><strong>Element</strong>: An element is an object that belongs to a set. We use the symbol ∈ to mean \"is an element of.\" If A = {1, 2, 3}, then 2 ∈ A, while 5 ∉ A.</li><li><strong>Ways of writing sets</strong>: In roster form, we list the elements inside braces: V = {a, e, i, o, u}. In rule form, we describe the elements: V = {x | x is a vowel in the English alphabet}.</li></ul><h3>Kinds of sets</h3><ul><li><strong>Empty set</strong>: A set with no elements, written { } or ∅. For example, the set of months with 32 days is empty.</li><li><strong>Finite and infinite sets</strong>: A finite set has a countable number of elements, like the days of the week. An infinite set goes on forever, like the set of counting numbers {1, 2, 3, …}.</li><li><strong>Subset</strong>: Set A is a subset of set B if every element of A is also in B, written A ⊆ B. For example, {2, 4} ⊆ {1, 2, 3, 4}.</li><li><strong>Universal set</strong>: The universal set, U, contains all the elements being considered in a problem. Every other set in the problem is a subset of U.</li></ul><h3>Operations on sets</h3><ul><li><strong>Union (A ∪ B)</strong>: The union of two sets contains all elements that are in A, in B, or in both. Each element is listed only once.</li><li><strong>Intersection (A ∩ B)</strong>: The intersection contains only the elements that are in both A and B. If two sets have no common element, their intersection is the empty set.</li><li><strong>Complement (A′)</strong>: The complement of A contains all elements of the universal set that are not in A.</li></ul><p>Venn diagrams use overlapping circles inside a rectangle to show these operations. The overlap of two circles represents the intersection.</p><h3>Worked example: U = {1, 2, 3, 4, 5, 6}, A = {1, 2, 3, 4}, B = {3, 4, 5}</h3><table><thead><tr><th>Operation</th><th>Result</th></tr></thead><tbody><tr><td>A ∪ B</td><td>{1, 2, 3, 4, 5}</td></tr><tr><td>A ∩ B</td><td>{3, 4}</td></tr><tr><td>A′</td><td>{5, 6}</td></tr><tr><td>B′</td><td>{1, 2, 6}</td></tr></tbody></table><h3>Quick check</h3><ol><li>Is \"the set of tall students\" well-defined? Why?</li><li>If A = {a, b, c} and B = {b, c, d}, find A ∩ B.</li><li>Is {1, 5} a subset of {1, 2, 3, 4}?</li></ol><details><summary>Show answers</summary><ol><li>No. People may disagree on who is tall.</li><li>{b, c}</li><li>No, because 5 is not in the second set.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(20, 26, 2, 'Integers', 'Positive and negative whole numbers, absolute value, and the four operations.', '<p>A temperature of 3 degrees below zero, a basement floor, or a debt of ₱50 are all situations that need numbers less than zero. Integers let us describe amounts both above and below a starting point.</p><p>In this module, you will learn what integers are, how to compare them, and how to add, subtract, multiply, and divide them.</p><h3>Understanding integers</h3><ul><li><strong>Integers</strong>: Integers are the whole numbers and their opposites: …, −3, −2, −1, 0, 1, 2, 3, … They do not include fractions or decimals. Zero is an integer that is neither positive nor negative.</li><li><strong>Number line</strong>: On a number line, positive integers are to the right of zero and negative integers are to the left. A number farther to the right is always greater, so −2 is greater than −5.</li><li><strong>Opposites</strong>: Two numbers are opposites if they are the same distance from zero on opposite sides, such as 7 and −7. The sum of a number and its opposite is always zero.</li><li><strong>Absolute value</strong>: The absolute value of a number is its distance from zero, written |x|. Distance is never negative, so |−8| = 8 and |8| = 8.</li></ul><h3>Adding and subtracting</h3><ul><li><strong>Same signs</strong>: Add the absolute values and keep the common sign. For example, (−6) + (−4) = −10.</li><li><strong>Different signs</strong>: Subtract the smaller absolute value from the larger one, then use the sign of the number with the larger absolute value. For example, (−9) + 5 = −4.</li><li><strong>Subtraction</strong>: To subtract an integer, add its opposite. For example, 3 − (−5) becomes 3 + 5 = 8.</li></ul><h3>Multiplying and dividing</h3><ul><li><strong>Same signs</strong>: The product or quotient is positive. For example, (−4) × (−5) = 20 and (−18) ÷ (−3) = 6.</li><li><strong>Different signs</strong>: The product or quotient is negative. For example, 6 × (−3) = −18 and 20 ÷ (−4) = −5.</li></ul><p>A helpful way to remember: a negative times a negative is positive, just as removing a debt improves your situation.</p><h3>Rules at a glance</h3><table><thead><tr><th>Operation</th><th>Same signs</th><th>Different signs</th></tr></thead><tbody><tr><td>Addition</td><td>Add, keep the sign</td><td>Subtract, use the sign of the larger absolute value</td></tr><tr><td>Subtraction</td><td>Add the opposite, then follow addition rules</td><td>Add the opposite, then follow addition rules</td></tr><tr><td>Multiplication / Division</td><td>Positive</td><td>Negative</td></tr></tbody></table><h3>Quick check</h3><ol><li>Which is greater, −3 or −10?</li><li>Solve: (−7) + 12</li><li>Solve: 4 − (−6)</li><li>Solve: (−36) ÷ 9</li></ol><details><summary>Show answers</summary><ol><li>−3</li><li>5</li><li>10</li><li>−4</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(21, 26, 3, 'Measurement and Conversion', 'Measurement uses standard units so everyone gets the same result.', '<p>Measurement uses standard units so everyone gets the same result.</p><h3>Key points</h3><ul><li>The metric system uses base units: meter (length), gram (mass), and liter (volume).</li><li>Prefixes show size: kilo = 1,000, centi = 1/100, milli = 1/1,000.</li><li>To convert to a smaller unit multiply; to a larger unit divide.</li></ul><h3>Try this</h3><p>Convert 2.5 kilometers to meters and 450 centimeters to meters.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(22, 26, 4, 'Algebraic Expressions', 'An algebraic expression combines numbers, variables, and operations.', '<p>An algebraic expression combines numbers, variables, and operations.</p><h3>Key points</h3><ul><li>In 3x + 5, 3 is the coefficient, x is the variable, and 5 is the constant.</li><li>Like terms have the same variable and exponent and can be combined.</li><li>To evaluate an expression, substitute the value of the variable.</li></ul><h3>Try this</h3><p>Simplify 4x + 3 − 2x + 7, then evaluate it when x = 2.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(23, 26, 5, 'Linear Equations in One Variable', 'A linear equation in one variable can be solved by isolating the variable.', '<p>A linear equation in one variable can be solved by isolating the variable.</p><h3>Key points</h3><ul><li>Whatever you do to one side of the equation, do the same to the other side.</li><li>Use inverse operations: addition undoes subtraction, multiplication undoes division.</li><li>Check your answer by substituting it back into the equation.</li></ul><h3>Try this</h3><p>Solve 3x − 7 = 11 and check your answer.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(24, 27, 1, 'The Scientific Method', 'The organized process scientists use to find reliable answers.', '<p>Science is not just a collection of facts. It is a way of finding answers to questions about the natural world. Scientists follow an organized process so that their answers are reliable and can be checked by others. This process is called the scientific method.</p><p>In this module, you will learn each step and the important ideas behind it.</p><h3>Steps of the scientific method</h3><ul><li><strong>Observation</strong>: Every investigation begins when someone notices something using the five senses. For example, you might notice that the plants beside your window are taller than the ones in the corner of the room. Good observations are careful and accurate, and scientists often write them down.</li><li><strong>Question</strong>: After observing, a scientist asks a question that can be answered through investigation. \"Why are plants beautiful?\" is hard to test, but \"Does sunlight affect how tall a plant grows?\" can be answered with an experiment.</li><li><strong>Hypothesis</strong>: A hypothesis is an educated guess that answers the question. It is often written as an \"if…then\" statement, such as \"If a plant receives more sunlight, then it will grow taller.\" A hypothesis does not have to be correct; its purpose is to be tested.</li><li><strong>Experiment</strong>: An experiment is a planned test to check whether the hypothesis is true. Only one thing is changed at a time so that the cause of the result is clear. Experiments are repeated several times to make sure the results are not just luck.</li><li><strong>Data and analysis</strong>: Data are the measurements and observations collected during the experiment. Scientists organize data in tables and graphs so patterns are easier to see, then study them to find out what they show.</li><li><strong>Conclusion</strong>: The conclusion states whether the results support the hypothesis. If they do not, the scientist has still learned something and may form a new hypothesis. Conclusions are shared so others can check and repeat the work.</li></ul><h3>Variables in an experiment</h3><p>A variable is any factor that can change in an experiment. A fair experiment controls its variables carefully.</p><ul><li><strong>Independent variable</strong>: The factor the scientist changes on purpose. In the plant experiment, this is the amount of sunlight.</li><li><strong>Dependent variable</strong>: The factor that is measured to see the effect. In the plant experiment, this is the height of the plant.</li><li><strong>Controlled variables</strong>: The factors kept the same so they do not affect the result, such as the type of plant, the amount of water, and the kind of soil.</li></ul><h3>The plant experiment, step by step</h3><table><thead><tr><th>Step</th><th>Example</th></tr></thead><tbody><tr><td>Observation</td><td>Plants near the window are taller.</td></tr><tr><td>Question</td><td>Does sunlight affect plant height?</td></tr><tr><td>Hypothesis</td><td>If a plant gets more sunlight, then it will grow taller.</td></tr><tr><td>Experiment</td><td>Two identical plants: one by the window, one in a dark cabinet, with the same water and soil.</td></tr><tr><td>Data</td><td>Measure both plants every day for two weeks.</td></tr><tr><td>Conclusion</td><td>The plant in sunlight grew taller, so the hypothesis is supported.</td></tr></tbody></table><h3>Quick check</h3><ol><li>Why must a hypothesis be testable?</li><li>In an experiment on how salt affects how fast ice melts, what is the independent variable?</li><li>What should a scientist do if the results do not support the hypothesis?</li></ol><details><summary>Show answers</summary><ol><li>So it can be checked through an experiment.</li><li>The amount of salt.</li><li>Accept the result, then form and test a new hypothesis.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:02'),
(25, 27, 2, 'Solutions', 'Uniform mixtures, their parts, and the factors that affect dissolving.', '<p>When you stir sugar into a glass of water, the sugar seems to disappear, but the water now tastes sweet. The sugar did not vanish; it spread evenly throughout the water. This kind of mixture is called a solution.</p><p>In this module, you will learn the parts of a solution, the kinds of solutions, and what makes substances dissolve faster.</p><h3>Mixtures and solutions</h3><ul><li><strong>Mixture</strong>: A mixture is a combination of two or more substances that are not chemically joined. Each substance keeps its own properties, and the parts can often be separated by physical means.</li><li><strong>Solution</strong>: A solution is a homogeneous, or uniform, mixture. It looks the same throughout, and you cannot see the separate parts even with a magnifying glass. Saltwater, vinegar, and air are examples of solutions.</li><li><strong>Suspension</strong>: Unlike a solution, a suspension has particles large enough to settle at the bottom when left standing, such as muddy water. This is one way to tell a solution from other mixtures.</li></ul><h3>Parts of a solution</h3><ul><li><strong>Solute</strong>: The solute is the substance that is dissolved. It is usually present in the smaller amount. In sugar water, sugar is the solute.</li><li><strong>Solvent</strong>: The solvent is the substance that does the dissolving, usually present in the larger amount. Water is called the universal solvent because it can dissolve more substances than any other liquid.</li></ul><p>Solutions are not always liquid. Air is a gas solution, with oxygen and other gases dissolved in nitrogen. Brass is a solid solution of zinc in copper.</p><h3>Concentration</h3><ul><li><strong>Dilute solution</strong>: A dilute solution has only a small amount of solute. A glass of juice with a lot of water added is dilute.</li><li><strong>Concentrated solution</strong>: A concentrated solution has a large amount of solute. Undiluted juice concentrate is very concentrated.</li><li><strong>Saturated solution</strong>: A saturated solution holds the maximum amount of solute that can dissolve at a given temperature. If you add more, it will just settle at the bottom.</li></ul><h3>Factors that affect dissolving</h3><ul><li><strong>Temperature</strong>: Most solids dissolve faster in hot solvents because the particles move faster. This is why sugar dissolves quickly in hot coffee.</li><li><strong>Stirring</strong>: Stirring brings fresh solvent into contact with the solute, speeding up dissolving.</li><li><strong>Particle size</strong>: Smaller pieces dissolve faster than large chunks because more surface is exposed to the solvent. Powdered sugar dissolves faster than a sugar cube.</li></ul><h3>Examples of solutions</h3><table><thead><tr><th>Solution</th><th>Solute</th><th>Solvent</th></tr></thead><tbody><tr><td>Saltwater</td><td>Salt</td><td>Water</td></tr><tr><td>Sugar water</td><td>Sugar</td><td>Water</td></tr><tr><td>Vinegar</td><td>Acetic acid</td><td>Water</td></tr><tr><td>Air</td><td>Oxygen and other gases</td><td>Nitrogen</td></tr></tbody></table><h3>Quick check</h3><ol><li>How can you tell a solution from a suspension?</li><li>Why is water called the universal solvent?</li><li>Give two ways to make sugar dissolve faster.</li></ol><details><summary>Show answers</summary><ol><li>A solution is uniform and does not settle; particles in a suspension settle when left standing.</li><li>It can dissolve more substances than any other liquid.</li><li>Any two: heat the water, stir it, use smaller pieces of sugar.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(26, 27, 3, 'Cells', 'The cell is the basic unit of life, and all living things are made of cells.', '<p>The cell is the basic unit of life, and all living things are made of cells.</p><h3>Key points</h3><ul><li>Plant cells have a cell wall and chloroplasts; animal cells do not.</li><li>The nucleus controls the cell&#x27;s activities.</li><li>The cell membrane controls what enters and leaves the cell.</li></ul><h3>Try this</h3><p>Draw and label a plant cell and an animal cell.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(27, 27, 4, 'Ecosystems', 'An ecosystem is a community of living things interacting with their nonliving environment.', '<p>An ecosystem is a community of living things interacting with their nonliving environment.</p><h3>Key points</h3><ul><li>Producers make their own food; consumers eat other organisms.</li><li>Decomposers break down dead matter and return nutrients to the soil.</li><li>Food chains and food webs show how energy flows.</li></ul><h3>Try this</h3><p>Create a food chain with at least four organisms found in the Philippines.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(28, 27, 5, 'Motion', 'Motion is a change in an object\'s position over time.', '<p>Motion is a change in an object&#x27;s position over time.</p><h3>Key points</h3><ul><li>Distance is total path length; displacement is the straight-line change in position.</li><li>Speed = distance ÷ time.</li><li>Velocity includes both speed and direction.</li></ul><h3>Try this</h3><p>A jeepney travels 60 km in 1.5 hours. What is its average speed?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(29, 29, 1, 'Heograpiya ng Asya', 'Ang lokasyon, laki, mga rehiyon, at anyong-lupa at anyong-tubig ng Asya.', '<p>Ang Asya ang ating kontinente. Dito matatagpuan ang Pilipinas at ang mga bansang may malaking ambag sa kasaysayan ng daigdig. Upang maunawaan ang buhay ng mga Asyano, kailangan munang kilalanin ang lupain at karagatang kanilang tinitirhan.</p><p>Sa modyul na ito, pag-aaralan mo ang lokasyon, laki, at mga rehiyon ng Asya, pati ang mahahalagang anyong-lupa at anyong-tubig nito.</p><h3>Mahahalagang konsepto</h3><ul><li><strong>Heograpiya</strong>: Ang heograpiya ay pag-aaral ng katangiang pisikal ng daigdig, gaya ng lupa, tubig, at klima, at ng ugnayan ng mga ito sa tao. Ipinapaliwanag nito kung bakit magkakaiba ang pamumuhay ng mga tao sa iba\'t ibang lugar.</li><li><strong>Kontinente</strong>: Ang kontinente ay malaking masa ng lupa. Pito ang kontinente sa daigdig, at ang Asya ang pinakamalaki sa lawak at sa dami ng tao. Halos one-third ng kabuuang lupain ng mundo ay sakop ng Asya.</li><li><strong>Lokasyon</strong>: Ang lokasyong absolute ay tiyak na kinaroroonan gamit ang latitude at longitude. Ang lokasyong relatibo naman ay paglalarawan batay sa mga katabing lugar. Halimbawa, ang Asya ay napapaligiran ng Karagatang Pasipiko sa silangan, Karagatang Indian sa timog, at Karagatang Arctic sa hilaga.</li></ul><h3>Limang rehiyon ng Asya</h3><p>Hinahati ang Asya sa limang rehiyon batay sa lokasyon, kultura, at katangiang pisikal.</p><ul><li><strong>Hilagang Asya</strong>: Malawak at malamig na rehiyon na kinabibilangan ng Siberia at mga bansang tulad ng Kazakhstan at Mongolia.</li><li><strong>Kanlurang Asya</strong>: Kilala rin bilang Gitnang Silangan; mayaman sa langis at pinagmulan ng tatlong malalaking relihiyon. Kabilang dito ang Saudi Arabia at Iran.</li><li><strong>Timog Asya</strong>: Kinabibilangan ng India, Pakistan, at Bangladesh; dito matatagpuan ang Himalayas.</li><li><strong>Silangang Asya</strong>: Kinabibilangan ng China, Japan, at South Korea; kilala sa mabilis na pag-unlad ng ekonomiya.</li><li><strong>Timog-Silangang Asya</strong>: Kinabibilangan ng Pilipinas, Indonesia, Thailand, at Vietnam; maraming kapuluan at tropikal ang klima.</li></ul><h3>Anyong-lupa at anyong-tubig</h3><ul><li><strong>Bundok at kabundukan</strong>: Nasa Asya ang Mt. Everest, ang pinakamataas na bundok sa mundo, na bahagi ng Himalayas. Ang mga kabundukan ay nagsisilbing likas na hangganan ng mga bansa.</li><li><strong>Kapatagan at lambak</strong>: Ang matatabang kapatagan sa tabi ng mga ilog ang naging tirahan ng maraming tao at pinagmulan ng mga sinaunang kabihasnan.</li><li><strong>Ilog</strong>: Mahahalagang ilog ang Huang Ho at Yangtze sa China, ang Indus at Ganges sa Timog Asya, at ang Tigris at Euphrates sa Kanlurang Asya. Pinagkukunan ang mga ito ng tubig, pagkain, at daanan ng kalakalan.</li></ul><h3>Buod ng mga rehiyon</h3><table><thead><tr><th>Rehiyon</th><th>Halimbawang bansa</th><th>Katangian</th></tr></thead><tbody><tr><td>Hilaga</td><td>Mongolia, Kazakhstan</td><td>Malamig at malawak</td></tr><tr><td>Kanluran</td><td>Saudi Arabia, Iran</td><td>Mayaman sa langis</td></tr><tr><td>Timog</td><td>India, Pakistan</td><td>Kinaroroonan ng Himalayas</td></tr><tr><td>Silangan</td><td>China, Japan</td><td>Mauunlad na ekonomiya</td></tr><tr><td>Timog-Silangan</td><td>Pilipinas, Indonesia</td><td>Kapuluan at tropikal</td></tr></tbody></table><h3>Pagsusulit</h3><ol><li>Ano ang pagkakaiba ng lokasyong absolute at relatibo?</li><li>Saang rehiyon kabilang ang Pilipinas?</li><li>Bakit sa tabi ng ilog umusbong ang maraming pamayanan?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Ang absolute ay gumagamit ng latitude at longitude; ang relatibo ay batay sa mga katabing lugar.</li><li>Timog-Silangang Asya</li><li>Dahil may tubig, matabang lupa, at daanan ng kalakalan.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(30, 29, 2, 'Likas na Yaman ng Asya', 'Ang mga uri ng likas na yaman ng Asya at ang wastong paggamit nito.', '<p>Mula sa palay na kinakain natin hanggang sa langis na nagpapatakbo ng sasakyan, maraming bagay ang nagmumula sa kalikasan. Mayaman ang Asya sa ganitong mga yaman, at ito ang pinagkukunan ng kabuhayan ng milyon-milyong tao.</p><p>Sa modyul na ito, kikilalanin mo ang mga uri ng likas na yaman, kung saan ito matatagpuan sa Asya, at kung bakit kailangan itong pangalagaan.</p><h3>Mga uri ng likas na yaman</h3><ul><li><strong>Yamang-lupa</strong>: Kabilang dito ang lupang taniman, kagubatan, at pastulan. Ang malalawak na kapatagan ng Timog at Timog-Silangang Asya ay ginagamit sa pagtatanim ng palay, na pangunahing pagkain ng mga Asyano.</li><li><strong>Yamang-tubig</strong>: Kabilang dito ang isda, korales, perlas, at iba pang galing sa dagat, ilog, at lawa. Ang Pilipinas at Indonesia ay may malalawak na karagatan na mayaman sa yamang-dagat.</li><li><strong>Yamang-mineral</strong>: Kabilang dito ang langis, natural gas, ginto, tanso, at karbon. Ang Kanlurang Asya ay may isa sa pinakamalalaking reserba ng langis sa mundo.</li><li><strong>Yamang-gubat</strong>: Kabilang dito ang troso, rattan, kawayan, at mga halamang gamot. Nagbibigay rin ang kagubatan ng tirahan sa maraming hayop.</li></ul><h3>Kahalagahan ng likas na yaman</h3><ul><li>Pinagkukunan ito ng pagkain, tubig, at tirahan.</li><li>Pinagmumulan ito ng hanapbuhay gaya ng pagsasaka, pangingisda, at pagmimina.</li><li>Nagdadala ito ng kita sa bansa sa pamamagitan ng pagluluwas ng produkto.</li></ul><h3>Mga suliranin at pangangalaga</h3><ul><li><strong>Pagkaubos ng yaman</strong>: Kapag labis ang paggamit, nauubos ang yaman nang mas mabilis kaysa sa kakayahan nitong mapalitan. Halimbawa, ang labis na pagputol ng puno ay nagdudulot ng pagguho ng lupa at pagbaha.</li><li><strong>Konserbasyon</strong>: Ang konserbasyon ay matalino at matipid na paggamit ng likas na yaman upang may matira pa para sa susunod na henerasyon. Halimbawa nito ang reforestation, o muling pagtatanim ng puno.</li><li><strong>Sustainable development</strong>: Ito ay pag-unlad na tumutugon sa pangangailangan ngayon nang hindi isinasakripisyo ang pangangailangan ng hinaharap. Layunin nitong balansehin ang kabuhayan at ang kalikasan.</li></ul><h3>Likas na yaman ayon sa rehiyon</h3><table><thead><tr><th>Rehiyon</th><th>Pangunahing likas na yaman</th></tr></thead><tbody><tr><td>Kanlurang Asya</td><td>Langis at natural gas</td></tr><tr><td>Timog Asya</td><td>Palay, trigo, at bulak</td></tr><tr><td>Silangang Asya</td><td>Karbon at yamang-dagat</td></tr><tr><td>Timog-Silangang Asya</td><td>Palay, troso, at yamang-dagat</td></tr><tr><td>Hilagang Asya</td><td>Troso at mineral</td></tr></tbody></table><h3>Pagsusulit</h3><ol><li>Ano ang yamang-mineral? Magbigay ng halimbawa.</li><li>Ano ang konserbasyon?</li><li>Ano ang maaaring mangyari kapag labis ang pagputol ng puno?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Mga mineral mula sa lupa, gaya ng langis, ginto, at tanso.</li><li>Matalino at matipid na paggamit ng likas na yaman upang may matira para sa hinaharap.</li><li>Pagguho ng lupa at pagbaha.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(31, 29, 3, 'Sinaunang Kabihasnan sa Asya', 'Sa Asya umusbong ang ilan sa pinakamatatandang kabihasnan sa daigdig.', '<p>Sa Asya umusbong ang ilan sa pinakamatatandang kabihasnan sa daigdig.</p><h3>Mahahalagang Punto</h3><ul><li>Ang Sumer ay nasa Mesopotamia, sa pagitan ng ilog Tigris at Euphrates.</li><li>Ang Indus ay umusbong sa lambak ng Ilog Indus.</li><li>Ang Tsina ay nagsimula sa lambak ng Huang Ho.</li></ul><h3>Subukan Mo</h3><p>Bakit malapit sa ilog umusbong ang mga sinaunang kabihasnan?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(32, 29, 4, 'Kolonyalismo at Imperyalismo sa Asya', 'Sinakop ng mga Kanluranin ang maraming bansa sa Asya upang makuha ang yaman at kapangyarihan.', '<p>Sinakop ng mga Kanluranin ang maraming bansa sa Asya upang makuha ang yaman at kapangyarihan.</p><h3>Mahahalagang Punto</h3><ul><li>Layunin nila ang &quot;Gold, God, and Glory.&quot;</li><li>Sinakop ng Espanya ang Pilipinas at ng Britanya ang India.</li><li>Nag-iwan ito ng epekto sa wika, relihiyon, at pamahalaan.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng dalawang mabuti at dalawang di-mabuting epekto ng kolonyalismo.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(33, 29, 5, 'Nasyonalismo sa Asya', 'Ang nasyonalismo ay pagmamahal sa bayan na nagtulak sa mga Asyano na lumaya mula sa mananakop.', '<p>Ang nasyonalismo ay pagmamahal sa bayan na nagtulak sa mga Asyano na lumaya mula sa mananakop.</p><h3>Mahahalagang Punto</h3><ul><li>Si Mahatma Gandhi ay namuno sa mapayapang pakikibaka ng India.</li><li>Sa Pilipinas, kilala sina Rizal at Bonifacio sa pagsulong ng kalayaan.</li><li>Maaaring mapayapa o marahas ang paraan ng pakikibaka.</li></ul><h3>Subukan Mo</h3><p>Paano mo maipapakita ang nasyonalismo sa kasalukuyan?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(34, 42, 1, 'Pagkilala sa Sarili', 'Ang pag-unawa sa sariling katangian at sa mga pagbabago sa pagdadalaga at pagbibinata.', '<p>Sa yugtong ito ng iyong buhay, maraming nagbabago: ang iyong katawan, ang iyong mga kaibigan, at ang paraan ng iyong pag-iisip. Ang pagkilala sa sarili ang tutulong sa iyo upang harapin ang mga pagbabagong ito nang may tiwala.</p><p>Sa modyul na ito, pag-aaralan mo kung ano ang pagkilala sa sarili, ang mga pagbabago sa pagdadalaga at pagbibinata, at kung paano mo mapapaunlad ang iyong sarili.</p><h3>Ano ang pagkilala sa sarili?</h3><ul><li><strong>Pagkilala sa sarili</strong>: Ito ay ang pag-alam sa iyong mga katangian, damdamin, hilig, at pinahahalagahan. Kapag kilala mo ang iyong sarili, mas madali kang makagagawa ng tamang pasya at mas maayos mong mapakikitunguhan ang iba.</li><li><strong>Kalakasan</strong>: Ito ang mga bagay na mahusay mong ginagawa, gaya ng pagguhit, pakikinig sa kaibigan, o paglutas ng math problem. Ang pagkilala sa kalakasan ay nagbibigay ng tiwala sa sarili.</li><li><strong>Kahinaan</strong>: Ito ang mga bagay na kailangan mo pang paunlarin. Hindi ito dapat ikahiya; bahagi ito ng pagiging tao. Ang mahalaga ay kilalanin ito at gumawa ng hakbang upang mapabuti.</li></ul><h3>Mga pagbabago sa pagdadalaga at pagbibinata</h3><p>Ang pagdadalaga at pagbibinata ay ang yugto ng paglipat mula sa pagkabata tungo sa pagiging ganap na tao. Normal at inaasahan ang mga pagbabagong kaakibat nito.</p><ul><li><strong>Pisikal</strong>: Mabilis na paglaki ng katawan at iba pang pagbabago na dulot ng paglaki.</li><li><strong>Pangkaisipan</strong>: Mas malalim na pag-iisip, pagtatanong, at pagbuo ng sariling opinyon.</li><li><strong>Pandamdamin</strong>: Mas matinding damdamin at mabilis na pagbabago ng emosyon.</li><li><strong>Panlipunan</strong>: Mas lumalalim na pakikipagkaibigan at pagnanais na matanggap ng kapwa.</li></ul><h3>Mga paraan upang lalong makilala ang sarili</h3><ul><li>Magnilay o sumulat sa journal tungkol sa iyong mga karanasan at damdamin.</li><li>Humingi ng puna sa pinagkakatiwalaang tao, gaya ng magulang, guro, o kaibigan.</li><li>Subukan ang mga bagong gawain upang matuklasan ang iyong hilig at galing.</li><li>Magtakda ng maliit na layunin upang mapaunlad ang isang kahinaan.</li></ul><h3>Halimbawa: Ang pagkilala ni Leo sa sarili</h3><table><thead><tr><th>Aspekto</th><th>Paglalarawan</th></tr></thead><tbody><tr><td>Kalakasan</td><td>Magaling gumuhit at malikhain</td></tr><tr><td>Kahinaan</td><td>Nahihiyang magsalita sa harap ng klase</td></tr><tr><td>Hakbang</td><td>Sumali sa art club at nagsanay mag-report sa maliit na grupo</td></tr><tr><td>Resulta</td><td>Unti-unting lumakas ang loob niyang magsalita</td></tr></tbody></table><h3>Pagsusulit</h3><ol><li>Ano ang pagkilala sa sarili?</li><li>Normal ba ang mga pagbabago sa pagdadalaga at pagbibinata?</li><li>Magbigay ng isang paraan upang lalong makilala ang sarili.</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pag-alam sa sariling katangian, damdamin, hilig, at pinahahalagahan.</li><li>Oo, bahagi ito ng paglaki.</li><li>Halimbawa: pagsusulat sa journal, paghingi ng puna, pagsubok ng bagong gawain.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03');
INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `description`, `content`, `file_path`, `week_number`, `published`, `updated_by`, `created_at`, `updated_at`) VALUES
(35, 42, 2, 'Talento at Kakayahan', 'Ang likas na galing, ang galing na nalilinang, at kung paano ito pauunlarin.', '<p>Bawat tao ay may natatanging galing. May magaling kumanta, may magaling sa numero, at may mahusay makisama. Ang hamon ay tuklasin ang galing na ito at paunlarin upang magamit sa sarili at sa kapwa.</p><p>Sa modyul na ito, pag-aaralan mo ang pagkakaiba ng talento at kakayahan at kung paano ito mapauunlad.</p><h3>Mahahalagang konsepto</h3><ul><li><strong>Talento</strong>: Ang talento ay likas na galing o regalo na mayroon ang isang tao mula pa pagkabata, gaya ng magandang boses o galing sa pagguhit. Gayunman, kahit likas ito, kailangan pa rin itong linangin upang hindi mawala.</li><li><strong>Kakayahan</strong>: Ang kakayahan ay galing na natutunan at napaunlad sa pamamagitan ng pagsasanay at pag-aaral, gaya ng pagluluto, pagta-type, o pagtugtog ng gitara. Halos lahat ng kakayahan ay maaaring matutunan kung may tiyaga.</li><li><strong>Tiwala sa sarili</strong>: Ito ay paniniwala na kaya mong gawin ang isang bagay. Ito ang nagtutulak sa tao na subukan ang bagong gawain at hindi sumuko kapag nagkamali.</li></ul><h3>Mga uri ng talino</h3><p>Ayon kay Howard Gardner, hindi iisa ang uri ng talino. Ilan sa mga ito ay:</p><ul><li><strong>Linguistic</strong>: Galing sa salita, pagbasa, at pagsulat.</li><li><strong>Logical-Mathematical</strong>: Galing sa numero at lohikal na pag-iisip.</li><li><strong>Musical</strong>: Galing sa musika, ritmo, at himig.</li><li><strong>Bodily-Kinesthetic</strong>: Galing sa paggamit ng katawan, gaya ng sayaw at isports.</li><li><strong>Interpersonal</strong>: Galing sa pakikisama at pag-unawa sa ibang tao.</li></ul><p>Ipinapakita nito na matalino ang bawat isa sa sariling paraan.</p><h3>Paano paunlarin ang talento at kakayahan</h3><ul><li>Magsanay nang regular, kahit kaunti araw-araw.</li><li>Humanap ng guro o halimbawang matututunan.</li><li>Tanggapin ang puna bilang tulong, hindi bilang pang-iinsulto.</li><li>Gamitin ang galing sa paglilingkod sa pamilya, paaralan, at komunidad.</li></ul><h3>Talento laban sa kakayahan</h3><table><thead><tr><th></th><th>Talento</th><th>Kakayahan</th></tr></thead><tbody><tr><td>Pinagmulan</td><td>Likas o regalo</td><td>Natutunan sa pagsasanay</td></tr><tr><td>Halimbawa</td><td>Magandang boses</td><td>Pagtugtog ng gitara</td></tr><tr><td>Kailangan ba ng pagsasanay?</td><td>Oo, upang lumago</td><td>Oo, upang matutunan</td></tr></tbody></table><h3>Pagsusulit</h3><ol><li>Ano ang pagkakaiba ng talento at kakayahan?</li><li>Sino ang nagpaliwanag ng iba\'t ibang uri ng talino?</li><li>Paano magagamit ang talento sa paglilingkod sa kapwa?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Ang talento ay likas; ang kakayahan ay natutunan sa pagsasanay.</li><li>Howard Gardner</li><li>Halimbawa: pagtuturo sa kaklase, pagtulong sa proyekto ng paaralan o barangay.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(36, 42, 3, 'Mga Hilig', 'Ang hilig ay mga gawaing nagbibigay sa iyo ng kasiyahan at sigla.', '<p>Ang hilig ay mga gawaing nagbibigay sa iyo ng kasiyahan at sigla.</p><h3>Mahahalagang Punto</h3><ul><li>Maaaring makatulong ang hilig sa pagpili ng kurso o trabaho sa hinaharap.</li><li>Mahalagang balansehin ang hilig at ang tungkulin.</li><li>Ang hilig ay maaaring magbago habang lumalaki.</li></ul><h3>Subukan Mo</h3><p>Itala ang iyong mga hilig at isang trabahong maaaring tumugma rito.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(37, 42, 4, 'Isip at Kilos-loob', 'Ang tao ay may isip upang umunawa at kilos-loob upang pumili.', '<p>Ang tao ay may isip upang umunawa at kilos-loob upang pumili.</p><h3>Mahahalagang Punto</h3><ul><li>Ang isip ay naghahanap ng katotohanan.</li><li>Ang kilos-loob ay nagpapasya at kumikilos tungo sa kabutihan.</li><li>Ang tamang paggamit ng dalawa ay nagpapakita ng pagiging tunay na tao.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng sitwasyon kung saan ginamit mo ang iyong isip at kilos-loob sa tamang pasya.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(38, 42, 5, 'Konsensya', 'Ang konsensya ay ang munting tinig sa loob natin na nagsasabi kung ano ang tama at mali.', '<p>Ang konsensya ay ang munting tinig sa loob natin na nagsasabi kung ano ang tama at mali.</p><h3>Mahahalagang Punto</h3><ul><li>Hinuhubog ang konsensya ng pamilya, paaralan, at pananampalataya.</li><li>Ang maayos na konsensya ay gabay sa mabuting pagpapasya.</li><li>Mahalagang pakinggan at sanayin ang konsensya araw-araw.</li></ul><h3>Subukan Mo</h3><p>Isalaysay ang isang pagkakataong sinunod mo ang iyong konsensya.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(39, 43, 1, 'Music of the Philippine Lowlands', 'Folk songs, musical forms, and instruments of lowland communities.', '<p>The lowland areas of Luzon and the Visayas are home to music that blends native traditions with Spanish influences. This music is heard in fiestas, church celebrations, and everyday life, and many of its songs are still sung in schools today.</p><p>In this module, you will learn the common forms of lowland music, its instruments, and the basic musical elements found in folk songs.</p><h3>Forms of lowland music</h3><ul><li><strong>Folk song</strong>: A folk song is a simple traditional song about daily life, work, love, or nature. Folk songs are usually easy to sing and are passed down by ear. \"Bahay Kubo\" lists vegetables found around a nipa hut, while \"Leron Leron Sinta\" describes climbing a papaya tree.</li><li><strong>Kundiman</strong>: The kundiman is a Filipino love song that is slow and emotional. It usually starts in a minor key and may shift to a major key. During the Spanish and American periods, some kundiman hid messages of love for the country.</li><li><strong>Harana</strong>: The harana is a serenade sung by a young man outside the house of the woman he courts, often with a guitar. It shows respect and sincerity in traditional Filipino courtship.</li><li><strong>Religious music</strong>: Spanish colonization introduced church music. The pasyon, a chanted story of the life and death of Jesus, is sung during Holy Week in many lowland communities.</li></ul><h3>Lowland instruments</h3><ul><li><strong>Rondalla</strong>: The rondalla is an ensemble of plucked string instruments such as the bandurria, octavina, laud, guitar, and bass. It often plays at fiestas and cultural shows.</li><li><strong>Bandurria</strong>: The bandurria is a small, pear-shaped string instrument that usually plays the melody in a rondalla.</li><li><strong>Banda</strong>: A brass band, or banda, plays during town fiestas and processions.</li></ul><h3>Elements of folk songs</h3><ul><li><strong>Meter</strong>: Meter is the pattern of strong and weak beats. Many folk songs are in 2/4 or 3/4 time. A song in 3/4 time, like a waltz, has a strong beat followed by two weak beats.</li><li><strong>Melody</strong>: Melody is the tune of the song, the series of notes you remember and hum.</li><li><strong>Mood</strong>: Mood is the feeling the song creates. Bahay Kubo is cheerful, while many kundiman are sad and longing.</li></ul><h3>Summary of lowland music</h3><table><thead><tr><th>Form or instrument</th><th>Description</th><th>Example or use</th></tr></thead><tbody><tr><td>Folk song</td><td>Simple song about daily life</td><td>Bahay Kubo</td></tr><tr><td>Kundiman</td><td>Slow, emotional love song</td><td>Sung in recitals and programs</td></tr><tr><td>Harana</td><td>Serenade during courtship</td><td>Sung with a guitar</td></tr><tr><td>Rondalla</td><td>Plucked string ensemble</td><td>Fiestas and cultural shows</td></tr></tbody></table><h3>Quick check</h3><ol><li>What is a kundiman?</li><li>Which instrument usually plays the melody in a rondalla?</li><li>How many beats per measure does a song in 3/4 time have?</li></ol><details><summary>Show answers</summary><ol><li>A slow, emotional Filipino love song.</li><li>Bandurria</li><li>Three</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(40, 43, 2, 'Arts and Crafts of the Philippine Lowlands', 'Traditional crafts made from local materials and the elements of design they use.', '<p>Lowland Filipinos have long turned local materials such as clay, wood, fibers, and shells into useful and beautiful objects. These crafts show both the creativity of the people and the resources available in their communities.</p><p>In this module, you will learn about common lowland crafts, where they come from, and the art elements used in their designs.</p><h3>Common lowland crafts</h3><ul><li><strong>Weaving</strong>: Weaving is making cloth or mats by crossing threads or strips over and under each other. Ilocos is known for inabel, a handwoven cloth with bold patterns, while many provinces weave banig, sleeping mats made from leaves.</li><li><strong>Pottery</strong>: Pottery is shaping clay into jars, pots, and other objects, then hardening them with heat. Vigan in Ilocos Sur is known for burnay, sturdy brown jars used to store water, rice, and fermented fish.</li><li><strong>Wood carving</strong>: Wood carving is shaping wood into figures and designs using knives and chisels. Paete in Laguna is famous for its skilled carvers, who make religious images, furniture, and decorations.</li><li><strong>Papier-mâché</strong>: Papier-mâché is a craft that shapes paper and paste into objects. Paete also produces taka, colorful papier-mâché figures, often in the shape of horses.</li></ul><h3>Elements of art in crafts</h3><ul><li><strong>Line</strong>: Lines create borders and shapes in woven patterns and carvings.</li><li><strong>Shape</strong>: Shapes such as diamonds, squares, and flowers are repeated in many designs.</li><li><strong>Color</strong>: Natural dyes from plants were traditionally used, giving earthy reds, browns, and blues.</li><li><strong>Texture</strong>: Texture is how a surface feels or looks like it would feel, such as the rough weave of a mat or the smooth surface of a carved figure.</li></ul><h3>Principles of design</h3><ul><li><strong>Pattern</strong>: A pattern is a design that repeats. Patterns are found in woven cloth, mats, and borders of pots.</li><li><strong>Balance</strong>: Balance is the even distribution of elements so the design looks stable. Many lowland designs use symmetry, where both sides are the same.</li><li><strong>Rhythm</strong>: Rhythm is the sense of movement created by repeating elements, guiding the eye across the design.</li></ul><h3>Crafts and their origins</h3><table><thead><tr><th>Craft</th><th>Place known for it</th><th>Material</th></tr></thead><tbody><tr><td>Inabel weaving</td><td>Ilocos</td><td>Cotton thread</td></tr><tr><td>Burnay jars</td><td>Vigan, Ilocos Sur</td><td>Clay</td></tr><tr><td>Wood carving</td><td>Paete, Laguna</td><td>Wood</td></tr><tr><td>Taka</td><td>Paete, Laguna</td><td>Paper and paste</td></tr></tbody></table><h3>Quick check</h3><ol><li>What is burnay and where is it from?</li><li>What is a pattern in design?</li><li>What does symmetry mean in a design?</li></ol><details><summary>Show answers</summary><ol><li>A brown clay jar from Vigan, Ilocos Sur.</li><li>A design that repeats.</li><li>Both sides of the design are the same.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(41, 43, 3, 'PE: Physical Fitness', 'Physical fitness is the body\'s ability to do daily tasks without getting too tired.', '<p>Physical fitness is the body&#x27;s ability to do daily tasks without getting too tired.</p><h3>Key points</h3><ul><li>Health-related components: cardiovascular endurance, muscular strength, flexibility, and body composition.</li><li>Regular exercise improves both body and mind.</li><li>Always warm up before and cool down after exercise.</li></ul><h3>Try this</h3><p>Do a simple fitness test (push-ups, sit-and-reach) and record your results.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(42, 43, 4, 'Health: Growth and Development', 'Adolescence brings physical, mental, emotional, and social changes.', '<p>Adolescence brings physical, mental, emotional, and social changes.</p><h3>Key points</h3><ul><li>Puberty is a normal stage of growth.</li><li>Good hygiene becomes more important during this stage.</li><li>Talk to trusted adults about questions or concerns.</li></ul><h3>Try this</h3><p>List three personal hygiene habits you practice every day.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(43, 43, 5, 'Health: Nutrition', 'Proper nutrition gives the body energy and helps it grow and stay healthy.', '<p>Proper nutrition gives the body energy and helps it grow and stay healthy.</p><h3>Key points</h3><ul><li>Follow &quot;Pinggang Pinoy&quot;: half the plate fruits and vegetables, a quarter rice, a quarter protein.</li><li>Drink enough water every day.</li><li>Limit sugary drinks and junk food.</li></ul><h3>Try this</h3><p>Plan a one-day healthy meal using Pinggang Pinoy.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(44, 44, 1, 'Introduction to TLE', 'What Technology and Livelihood Education covers and why its skills matter.', '<p>Technology and Livelihood Education, or TLE, teaches practical skills you can use at home, in business, and in future work. Unlike many subjects that focus on ideas, TLE focuses on doing: cooking, building, repairing, growing, and using technology.</p><p>In this module, you will learn the four main areas of TLE, the skills each develops, and how these skills connect to jobs and livelihood.</p><h3>What is TLE?</h3><ul><li><strong>Technology and Livelihood Education</strong>: TLE is a learning area that develops practical knowledge and work skills. It prepares students for everyday tasks, for small business, and for further training in technical-vocational fields.</li><li><strong>Livelihood</strong>: Livelihood is the means by which a person earns a living. Skills learned in TLE, such as baking or electrical wiring, can become a source of income.</li><li><strong>Exploratory courses</strong>: In Grades 7 and 8, students take exploratory courses to try different TLE areas. This helps them discover what they enjoy and are good at before choosing a specialization in Grade 9.</li></ul><h3>The four areas of TLE</h3><ul><li><strong>Home Economics</strong>: Covers skills for the home and service industries, such as cookery, dressmaking, caregiving, and beauty care. These skills support businesses like food stalls and tailoring shops.</li><li><strong>Industrial Arts</strong>: Covers technical and construction skills, such as carpentry, masonry, plumbing, and electrical installation. These skills are needed in building and maintaining homes and structures.</li><li><strong>Agri-Fishery Arts</strong>: Covers farming, gardening, animal raising, and fishing. These skills support food production, an important industry in the Philippines.</li><li><strong>Information and Communications Technology (ICT)</strong>: Covers computer skills such as basic computer operations, computer hardware servicing, and creating digital content.</li></ul><h3>Why TLE skills matter</h3><ul><li>They make you more independent and helpful at home.</li><li>They can be used to start a small business.</li><li>They can lead to TESDA certification and employment.</li><li>They develop good work habits like safety, accuracy, and responsibility.</li></ul><h3>TLE areas and sample careers</h3><table><thead><tr><th>Area</th><th>Sample skill</th><th>Sample career</th></tr></thead><tbody><tr><td>Home Economics</td><td>Cookery</td><td>Cook, caterer</td></tr><tr><td>Industrial Arts</td><td>Electrical installation</td><td>Electrician</td></tr><tr><td>Agri-Fishery Arts</td><td>Crop production</td><td>Farmer, agriculturist</td></tr><tr><td>ICT</td><td>Computer servicing</td><td>Computer technician</td></tr></tbody></table><h3>Quick check</h3><ol><li>What does TLE stand for?</li><li>Which TLE area includes carpentry and plumbing?</li><li>Why do Grades 7 and 8 take exploratory courses?</li></ol><details><summary>Show answers</summary><ol><li>Technology and Livelihood Education</li><li>Industrial Arts</li><li>To try different TLE areas before choosing a specialization.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(45, 44, 2, 'Workplace Safety', 'Hazards, protective equipment, and safety rules in the TLE workplace.', '<p>Working with knives, stoves, electricity, and tools can be dangerous if we are careless. Most accidents can be prevented by following simple safety rules. Safety protects not only you but also the people working around you.</p><p>In this module, you will learn about hazards, personal protective equipment, and basic safety practices in the TLE workplace.</p><h3>Understanding hazards and risks</h3><ul><li><strong>Hazard</strong>: A hazard is anything that can cause harm, such as a sharp knife, a wet floor, an exposed wire, or a strong chemical. Recognizing hazards is the first step to preventing accidents.</li><li><strong>Risk</strong>: Risk is the chance that a hazard will actually cause harm. A sharp knife stored properly has low risk; the same knife left on the edge of a table has high risk.</li><li><strong>Accident</strong>: An accident is an unplanned event that causes injury or damage. Most accidents happen because of unsafe actions, unsafe conditions, or both.</li></ul><h3>Personal protective equipment (PPE)</h3><p>PPE is gear worn to protect the body from hazards. The right PPE depends on the task.</p><ul><li><strong>Apron</strong>: Protects clothing and skin from spills and stains in cooking.</li><li><strong>Gloves</strong>: Protect hands from cuts, heat, and chemicals.</li><li><strong>Safety goggles</strong>: Protect the eyes from flying particles and splashes.</li><li><strong>Closed shoes</strong>: Protect the feet from falling objects and sharp items.</li><li><strong>Face mask</strong>: Protects against dust and fumes.</li></ul><h3>Basic safety rules</h3><ul><li>Keep the work area clean, dry, and organized.</li><li>Use each tool only for its intended purpose, and inspect it before use.</li><li>Never touch electrical outlets or switches with wet hands.</li><li>Report broken tools and unsafe conditions to the teacher right away.</li><li>Know where the first aid kit and fire extinguisher are kept.</li></ul><h3>First aid</h3><ul><li><strong>First aid</strong>: First aid is the immediate care given to an injured person before professional help arrives. For a minor cut, wash the wound with clean water, apply pressure to stop bleeding, and cover it with a clean bandage. For a minor burn, cool the area under running water.</li></ul><h3>Hazards and how to prevent them</h3><table><thead><tr><th>Hazard</th><th>Possible injury</th><th>Prevention</th></tr></thead><tbody><tr><td>Wet floor</td><td>Slipping</td><td>Wipe spills immediately</td></tr><tr><td>Sharp knife</td><td>Cuts</td><td>Cut away from the body; store properly</td></tr><tr><td>Exposed wire</td><td>Electric shock</td><td>Report it; do not touch</td></tr><tr><td>Hot pan</td><td>Burns</td><td>Use pot holders or gloves</td></tr></tbody></table><h3>Quick check</h3><ol><li>What is the difference between a hazard and a risk?</li><li>What does PPE stand for?</li><li>What should you do first for a minor burn?</li></ol><details><summary>Show answers</summary><ol><li>A hazard can cause harm; risk is the chance that it will.</li><li>Personal Protective Equipment</li><li>Cool the area under running water.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:34:03'),
(46, 44, 3, 'Tools and Equipment', 'Using the right tool for the right job makes work safer and easier.', '<p>Using the right tool for the right job makes work safer and easier.</p><h3>Key points</h3><ul><li>Learn the name and proper use of each tool.</li><li>Check tools for damage before using them.</li><li>Clean and store tools properly after use.</li></ul><h3>Try this</h3><p>Identify five common hand tools and give the use of each.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(47, 44, 4, 'Measurement and Calculation', 'Accurate measurement is important in cooking, sewing, carpentry, and other tasks.', '<p>Accurate measurement is important in cooking, sewing, carpentry, and other tasks.</p><h3>Key points</h3><ul><li>Learn both metric and English units of measurement.</li><li>Use measuring tools like rulers, tape measures, and measuring cups.</li><li>Double-check measurements to avoid waste.</li></ul><h3>Try this</h3><p>Measure your study table in centimeters and inches.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(48, 44, 5, 'Basic Computer Operations', 'Computers are important tools in school and at work.', '<p>Computers are important tools in school and at work.</p><h3>Key points</h3><ul><li>Hardware is the physical parts; software is the programs.</li><li>Learn to properly start up and shut down a computer.</li><li>Practice good file management by naming and organizing files.</li></ul><h3>Try this</h3><p>Create a folder on a computer and save a document inside it with a clear filename.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(49, 33, 1, 'Karunungang-bayan', 'Mga pahayag ng ating mga ninuno na puno ng aral at talino.', '<p>Bago pa magkaroon ng paaralan, itinuturo na ng ating mga ninuno ang aral sa pamamagitan ng maiikling pahayag.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Karunungang-bayan</strong> – mga pahayag na nagmula sa ating mga ninuno at nagtataglay ng aral.</li><li><strong>Salawikain</strong> – pahayag na may malalim na aral tungkol sa buhay.</li><li><strong>Sawikain</strong> – idyoma o pahayag na hindi tuwiran ang kahulugan.</li><li><strong>Bugtong</strong> – palaisipang may nakatagong sagot.</li></ul><h3>Halimbawa</h3><blockquote><p>Salawikain: &quot;Kung ano ang itinanim, siya ring aanihin.&quot; Sawikain: &quot;Makapal ang mukha&quot; (walang hiya). Bugtong: &quot;Isang prinsesa, nakaupo sa tasa.&quot; Sagot: kasoy.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang salawikain?</li><li>Ano ang ibig sabihin ng sawikaing &quot;Makapal ang mukha&quot;?</li><li>Ano ang bugtong?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pahayag na may malalim na aral tungkol sa buhay.</li><li>Walang hiya</li><li>Palaisipang may nakatagong sagot.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(50, 33, 2, 'Florante at Laura: Kaligiran ng Akda', 'Ang may-akda at ang kasaysayan ng obra maestra ni Balagtas.', '<p>Ang Florante at Laura ay isa sa pinakamahahalagang akda sa panitikang Pilipino.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Francisco Balagtas</strong> – ang sumulat ng Florante at Laura, kilala bilang &quot;Prinsipe ng Makatang Tagalog.&quot;</li><li><strong>Awit</strong> – tulang may labindalawang pantig bawat taludtod.</li><li><strong>Tagpuan</strong> – ang Albanya, isang kaharian sa Europa.</li><li><strong>Alegorya</strong> – akdang may nakatagong kahulugan sa likod ng kuwento.</li></ul><h3>Halimbawa</h3><blockquote><p>Itinakda ni Balagtas ang kuwento sa Albanya upang maitago ang kanyang puna sa pang-aabuso ng mga Kastila sa Pilipinas.</p></blockquote><h3>Pagsusulit</h3><ol><li>Sino ang sumulat ng Florante at Laura?</li><li>Ilang pantig mayroon ang bawat taludtod ng awit?</li><li>Bakit sa Albanya itinakda ang kuwento?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Francisco Balagtas</li><li>Labindalawa</li><li>Upang maitago ang puna sa pamahalaang Kastila.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(51, 33, 3, 'Florante at Laura: Mga Tauhan at Aral', 'Umiikot ang akda sa pag-ibig, katapatan, at pakikibaka laban sa kataksilan at inggit.', '<p>Umiikot ang akda sa pag-ibig, katapatan, at pakikibaka laban sa kataksilan at inggit.</p><h3>Mahahalagang Punto</h3><ul><li>Si Florante ay maginoong mandirigma at si Laura ang kanyang iniibig.</li><li>Si Konde Adolfo ang kontrabidang puno ng inggit at kasakiman.</li><li>Si Aladin ay Morong tumulong kay Florante, patunay na walang pinipiling lahi ang kabutihan.</li></ul><h3>Subukan Mo</h3><p>Pumili ng isang tauhan at ilarawan kung paano siya kahawig ng isang tao sa kasalukuyan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(52, 33, 4, 'Pang-ugnay at Pagkakaugnay ng Ideya', 'Ang pang-ugnay ay mga salitang nag-uugnay ng salita, parirala, o sugnay upang maging malinaw ang daloy ng ideya.', '<p>Ang pang-ugnay ay mga salitang nag-uugnay ng salita, parirala, o sugnay upang maging malinaw ang daloy ng ideya.</p><h3>Mahahalagang Punto</h3><ul><li>Ang pangatnig ay nag-uugnay ng sugnay, gaya ng &quot;at,&quot; &quot;ngunit,&quot; at &quot;dahil.&quot;</li><li>Ang pang-ukol ay nagsasaad ng relasyon, gaya ng &quot;para sa&quot; at &quot;tungkol sa.&quot;</li><li>Ang pang-angkop ay &quot;na,&quot; &quot;-ng,&quot; at &quot;-g&quot; na nagpapadulas sa pagbigkas.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng tatlong pangungusap na gumagamit ng magkakaibang pangatnig.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(53, 33, 5, 'Pagsulat ng Sanaysay', 'Ang sanaysay ay akdang naglalahad ng sariling kuro-kuro o pananaw ng manunulat tungkol sa isang paksa.', '<p>Ang sanaysay ay akdang naglalahad ng sariling kuro-kuro o pananaw ng manunulat tungkol sa isang paksa.</p><h3>Mahahalagang Punto</h3><ul><li>Mayroon itong panimula, gitna o katawan, at wakas.</li><li>Ang pormal na sanaysay ay seryoso, samantalang ang di-pormal ay personal at magaan.</li><li>Mahalaga ang malinaw na pangunahing ideya at maayos na pagkakasunod-sunod.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng di-pormal na sanaysay tungkol sa isang aral na natutuhan mo sa iyong pamilya.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(54, 30, 1, 'Afro-Asian Literature', 'Stories and poems from the cultures of Africa and Asia.', '<p>Africa and Asia have rich literary traditions that show values like respect for elders, family, and nature.</p><h3>Key terms</h3><ul><li><strong>Afro-Asian literature</strong> – literary works from Africa and Asia.</li><li><strong>Haiku</strong> – a Japanese poem of three lines with 5, 7, and 5 syllables.</li><li><strong>Fable</strong> – a short story, often with animals, that teaches a lesson.</li><li><strong>Epic</strong> – a long poem about the adventures of a hero.</li></ul><h3>Example</h3><blockquote><p>A haiku: &quot;Rain on the tin roof / the old cat curls by the stove / waiting for the sun.&quot; It has 5, 7, and 5 syllables.</p></blockquote><h3>Quick check</h3><ol><li>What is a haiku?</li><li>What is an epic?</li><li>Name one value often seen in Afro-Asian literature.</li></ol><details><summary>Show answers</summary><ol><li>A Japanese poem of three lines with 5, 7, and 5 syllables.</li><li>A long poem about the adventures of a hero.</li><li>Any one: respect for elders, family, nature</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(55, 30, 2, 'Context Clues', 'Using nearby words to understand unfamiliar words.', '<p>You do not always need a dictionary. The words around an unfamiliar word can give you hints.</p><h3>Key terms</h3><ul><li><strong>Context clues</strong> – hints in a sentence that help explain the meaning of a word.</li><li><strong>Definition clue</strong> – the meaning is given directly.</li><li><strong>Synonym clue</strong> – a word with a similar meaning is nearby.</li><li><strong>Antonym clue</strong> – a word with the opposite meaning is nearby.</li><li><strong>Example clue</strong> – examples show the meaning.</li></ul><h3>Example</h3><blockquote><p>&quot;The house was immaculate, so clean that not a speck of dust could be found.&quot; The phrase &quot;so clean&quot; tells us immaculate means very clean.</p></blockquote><h3>Quick check</h3><ol><li>What are context clues?</li><li>In &quot;He was timid, not brave like his brother,&quot; what does timid mean?</li><li>What type of clue gives the meaning directly?</li></ol><details><summary>Show answers</summary><ol><li>Hints in a sentence that help explain a word&#x27;s meaning.</li><li>Shy or not brave (antonym clue)</li><li>Definition clue</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(56, 30, 3, 'Active and Passive Voice', 'In the active voice the subject does the action; in the passive voice the subject receives the action.', '<p>In the active voice the subject does the action; in the passive voice the subject receives the action.</p><h3>Key points</h3><ul><li>Active: &quot;The students cleaned the room.&quot;</li><li>Passive: &quot;The room was cleaned by the students.&quot;</li><li>Active voice is usually clearer and more direct.</li></ul><h3>Try this</h3><p>Change five passive sentences into the active voice.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(57, 30, 4, 'Analyzing Informational Texts', 'Informational texts give facts about a topic, and readers should judge how well the facts are organized and supported.', '<p>Informational texts give facts about a topic, and readers should judge how well the facts are organized and supported.</p><h3>Key points</h3><ul><li>Common structures include cause and effect, compare and contrast, and problem and solution.</li><li>Text features like headings, captions, and graphs help readers.</li><li>Check whether claims are supported by reliable evidence.</li></ul><h3>Try this</h3><p>Identify the text structure of a science article and explain your answer.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(58, 30, 5, 'Writing an Informative Essay', 'An informative essay explains a topic clearly using facts and examples.', '<p>An informative essay explains a topic clearly using facts and examples.</p><h3>Key points</h3><ul><li>Start with an introduction that states the topic.</li><li>Each body paragraph covers one point with supporting facts.</li><li>End with a conclusion that sums up the key information.</li></ul><h3>Try this</h3><p>Write a short informative essay about a festival in your province.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(59, 31, 1, 'Factoring Polynomials', 'Writing a polynomial as a product of simpler factors.', '<p>Factoring is the reverse of multiplying. It breaks an expression into parts that multiply to give it.</p><h3>Key terms</h3><ul><li><strong>Polynomial</strong> – an expression with one or more terms, like x² + 5x + 6.</li><li><strong>Factor</strong> – an expression that divides another evenly.</li><li><strong>Greatest common factor (GCF)</strong> – the largest factor shared by all terms.</li><li><strong>Difference of two squares</strong> – a² − b² = (a + b)(a − b).</li></ul><h3>Example</h3><blockquote><p>GCF: 6x + 9 = 3(2x + 3). Difference of squares: x² − 25 = (x + 5)(x − 5). Trinomial: x² + 5x + 6 = (x + 2)(x + 3), because 2 × 3 = 6 and 2 + 3 = 5.</p></blockquote><h3>Quick check</h3><ol><li>Factor 4x + 12.</li><li>Factor x² − 16.</li><li>Factor x² + 7x + 10.</li></ol><details><summary>Show answers</summary><ol><li>4(x + 3)</li><li>(x + 4)(x − 4)</li><li>(x + 2)(x + 5)</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(60, 31, 2, 'Rational Algebraic Expressions', 'Fractions with polynomials, and how to simplify them.', '<p>Just like fractions with numbers, we can have fractions with variables.</p><p>To simplify, factor the numerator and denominator, then cancel common factors.</p><h3>Key terms</h3><ul><li><strong>Rational algebraic expression</strong> – a fraction whose numerator and denominator are polynomials.</li><li><strong>Simplest form</strong> – when the numerator and denominator have no common factor.</li><li><strong>Restriction</strong> – a value that makes the denominator zero, which is not allowed.</li></ul><h3>Example</h3><blockquote><p>(x² − 9) / (x + 3) = (x + 3)(x − 3) / (x + 3) = x − 3, where x ≠ −3.</p></blockquote><h3>Quick check</h3><ol><li>What is a rational algebraic expression?</li><li>Simplify (2x + 4) / 2.</li><li>Why can&#x27;t the denominator be zero?</li></ol><details><summary>Show answers</summary><ol><li>A fraction whose numerator and denominator are polynomials.</li><li>x + 2</li><li>Division by zero is undefined.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(61, 31, 3, 'Linear Equations in Two Variables', 'A linear equation in two variables, like y = 2x + 1, has a straight-line graph.', '<p>A linear equation in two variables, like y = 2x + 1, has a straight-line graph.</p><h3>Key points</h3><ul><li>In y = mx + b, m is the slope and b is the y-intercept.</li><li>Slope measures steepness: rise over run.</li><li>Any point on the line is a solution of the equation.</li></ul><h3>Try this</h3><p>Find the slope and y-intercept of y = −3x + 4, then graph it.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(62, 31, 4, 'Systems of Linear Equations', 'A system of linear equations is two or more equations solved together.', '<p>A system of linear equations is two or more equations solved together.</p><h3>Key points</h3><ul><li>The solution is the point where the lines intersect.</li><li>Methods include graphing, substitution, and elimination.</li><li>Parallel lines have no solution; the same line has infinitely many.</li></ul><h3>Try this</h3><p>Solve the system x + y = 10 and x − y = 2 by elimination.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(63, 31, 5, 'Triangle Congruence', 'Two triangles are congruent if they have the same size and shape.', '<p>Two triangles are congruent if they have the same size and shape.</p><h3>Key points</h3><ul><li>SSS: three pairs of equal sides.</li><li>SAS: two sides and the included angle are equal.</li><li>ASA: two angles and the included side are equal.</li></ul><h3>Try this</h3><p>Draw two triangles and explain which postulate proves they are congruent.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(64, 32, 1, 'Forces and Newton\'s Laws', 'How forces change the motion of objects.', '<p>Every push or pull you make is a force. Newton&#x27;s three laws explain how forces affect motion.</p><h3>Key terms</h3><ul><li><strong>Force</strong> – a push or pull, measured in newtons (N).</li><li><strong>Inertia</strong> – the tendency of an object to resist change in its motion.</li><li><strong>First law</strong> – an object stays at rest or in motion unless a force acts on it.</li><li><strong>Second law</strong> – force = mass × acceleration (F = ma).</li><li><strong>Third law</strong> – for every action, there is an equal and opposite reaction.</li></ul><h3>Example</h3><blockquote><p>Passengers lurch forward when a jeepney stops suddenly (first law). A rocket pushes gas down, and the gas pushes the rocket up (third law).</p></blockquote><h3>Quick check</h3><ol><li>What is inertia?</li><li>What is the formula of the second law?</li><li>Which law explains a rocket launch?</li></ol><details><summary>Show answers</summary><ol><li>The tendency of an object to resist change in motion.</li><li>F = ma</li><li>Third law</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(65, 32, 2, 'Work, Power, and Energy', 'How force moves objects and how energy changes form.', '<p>When you lift your bag, you do work and use energy. These ideas are connected.</p><h3>Key terms</h3><ul><li><strong>Work</strong> – done when a force moves an object; work = force × distance, measured in joules (J).</li><li><strong>Energy</strong> – the ability to do work.</li><li><strong>Kinetic energy</strong> – energy of a moving object.</li><li><strong>Potential energy</strong> – stored energy, such as an object held high.</li><li><strong>Power</strong> – how fast work is done; power = work ÷ time, measured in watts (W).</li></ul><h3>Example</h3><blockquote><p>Lifting a 20 N box up 2 m does 20 × 2 = 40 J of work. If it takes 4 seconds, power = 40 ÷ 4 = 10 W.</p></blockquote><h3>Quick check</h3><ol><li>What is energy?</li><li>A ball rolling down a hill has what kind of energy?</li><li>Pushing with 10 N for 3 m does how much work?</li></ol><details><summary>Show answers</summary><ol><li>The ability to do work.</li><li>Kinetic energy</li><li>30 J</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:20:59'),
(66, 32, 3, 'Earthquakes and Faults', 'Earthquakes happen when rocks along a fault suddenly move and release energy.', '<p>Earthquakes happen when rocks along a fault suddenly move and release energy.</p><h3>Key points</h3><ul><li>The focus is where the earthquake starts underground; the epicenter is directly above it.</li><li>Magnitude measures energy; intensity measures the effects on people and structures.</li><li>The Philippines lies in the Pacific Ring of Fire, so earthquakes are common.</li></ul><h3>Try this</h3><p>Prepare an earthquake safety plan for your home.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(67, 32, 4, 'The Periodic Table', 'The periodic table organizes elements by atomic number and properties.', '<p>The periodic table organizes elements by atomic number and properties.</p><h3>Key points</h3><ul><li>Rows are called periods; columns are called groups or families.</li><li>Metals are on the left, nonmetals on the right, and metalloids in between.</li><li>Elements in the same group have similar properties.</li></ul><h3>Try this</h3><p>Identify the group and period of oxygen, sodium, and iron.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(68, 32, 5, 'The Digestive System', 'The digestive system breaks food down into nutrients the body can absorb.', '<p>The digestive system breaks food down into nutrients the body can absorb.</p><h3>Key points</h3><ul><li>Digestion begins in the mouth with chewing and saliva.</li><li>The stomach mixes food with acids and enzymes.</li><li>Most nutrients are absorbed in the small intestine.</li></ul><h3>Try this</h3><p>Trace the path of a piece of bread through the digestive system.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(69, 45, 1, 'Heograpiya ng Daigdig', 'Ang mga katangiang pisikal ng mundo at kung paano ito nakaaapekto sa tao.', '<p>Upang maunawaan ang kasaysayan ng daigdig, kailangan munang kilalanin ang mundong pinangyarihan nito.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Kontinente</strong> – malaking masa ng lupa; may pito sa daigdig.</li><li><strong>Karagatan</strong> – malaking anyong-tubig; may lima sa daigdig.</li><li><strong>Latitude</strong> – guhit na pahalang na sumusukat ng layo mula sa ekwador.</li><li><strong>Longitude</strong> – guhit na patayong sumusukat ng layo mula sa Prime Meridian.</li><li><strong>Klima</strong> – karaniwang kalagayan ng panahon sa isang lugar sa mahabang panahon.</li></ul><h3>Halimbawa</h3><blockquote><p>Ang Pilipinas ay malapit sa ekwador kaya tropikal ang klima nito: mainit at may tag-ulan at tag-araw.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ilan ang kontinente sa daigdig?</li><li>Ano ang sinusukat ng latitude?</li><li>Bakit tropikal ang klima ng Pilipinas?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pito</li><li>Layo mula sa ekwador</li><li>Dahil malapit ito sa ekwador.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:21:00'),
(70, 45, 2, 'Mga Sinaunang Kabihasnan', 'Ang mga unang kabihasnang umusbong sa daigdig.', '<p>Ang mga unang kabihasnan ay nagsimula malapit sa mga ilog kung saan may tubig at matabang lupa.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Kabihasnan</strong> – maunlad na pamumuhay na may pamahalaan, relihiyon, sistema ng pagsulat, at lungsod.</li><li><strong>Mesopotamia</strong> – &quot;lupain sa pagitan ng dalawang ilog,&quot; ang Tigris at Euphrates.</li><li><strong>Ehipto</strong> – kabihasnang umusbong sa tabi ng Ilog Nile; kilala sa mga piramide.</li><li><strong>Gresya</strong> – pinagmulan ng demokrasya at pilosopiya.</li></ul><h3>Halimbawa</h3><blockquote><p>Ang Ilog Nile ay bumabaha taon-taon at nag-iiwan ng matabang lupa, kaya naging masagana ang pagsasaka ng mga Ehipsiyo.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang kabihasnan?</li><li>Saan umusbong ang kabihasnang Ehipto?</li><li>Ano ang naging ambag ng Gresya?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Maunlad na pamumuhay na may pamahalaan, relihiyon, pagsulat, at lungsod.</li><li>Sa tabi ng Ilog Nile</li><li>Demokrasya at pilosopiya</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:21:00'),
(71, 45, 3, 'Gitnang Panahon', 'Ang Gitnang Panahon ay panahon sa Europa matapos bumagsak ang Imperyong Romano.', '<p>Ang Gitnang Panahon ay panahon sa Europa matapos bumagsak ang Imperyong Romano.</p><h3>Mahahalagang Punto</h3><ul><li>Lumaganap ang sistemang piyudalismo.</li><li>Malaki ang kapangyarihan ng Simbahang Katoliko.</li><li>Nangyari ang mga Krusada upang mabawi ang Banal na Lupain.</li></ul><h3>Subukan Mo</h3><p>Ilarawan ang ugnayan ng panginoong maylupa at ng magsasaka sa piyudalismo.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(72, 45, 4, 'Renaissance at Rebolusyong Siyentipiko', 'Ang Renaissance ay muling pagsilang ng sining, agham, at kaalaman sa Europa.', '<p>Ang Renaissance ay muling pagsilang ng sining, agham, at kaalaman sa Europa.</p><h3>Mahahalagang Punto</h3><ul><li>Kilala sina Leonardo da Vinci at Michelangelo sa sining.</li><li>Binago nina Copernicus at Galileo ang pananaw tungkol sa sansinukob.</li><li>Binigyang-halaga ang katwiran at pagsusuri.</li></ul><h3>Subukan Mo</h3><p>Pumili ng isang imbensyon o tuklas ng panahong ito at ipaliwanag ang kahalagahan nito.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(73, 45, 5, 'Mga Digmaang Pandaigdig', 'Dalawang malaking digmaan ang yumanig sa daigdig noong ika-20 siglo.', '<p>Dalawang malaking digmaan ang yumanig sa daigdig noong ika-20 siglo.</p><h3>Mahahalagang Punto</h3><ul><li>Unang Digmaang Pandaigdig: 1914 hanggang 1918.</li><li>Ikalawang Digmaang Pandaigdig: 1939 hanggang 1945, kasama ang Pilipinas.</li><li>Itinatag ang United Nations upang mapanatili ang kapayapaan.</li></ul><h3>Subukan Mo</h3><p>Ano ang naging epekto ng Ikalawang Digmaang Pandaigdig sa Pilipinas?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(74, 46, 1, 'Ang Pamilya', 'Ang pinakaunang paaralan ng pagmamahal at pagpapahalaga.', '<p>Sa pamilya tayo unang natututong magmahal, gumalang, at magtulungan.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Pamilya</strong> – ang pinakamaliit at pinakapangunahing yunit ng lipunan.</li><li><strong>Pagpapahalaga</strong> – mga paniniwalang itinuturing na mahalaga, gaya ng katapatan at paggalang.</li><li><strong>Tungkulin</strong> – mga gawaing inaasahan sa bawat kasapi ng pamilya.</li></ul><h3>Halimbawa</h3><blockquote><p>Tinuturuan ng magulang ang anak na magsabi ng &quot;po&quot; at &quot;opo.&quot; Ang anak naman ay tumutulong sa gawaing-bahay bilang kanyang tungkulin.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang pamilya?</li><li>Magbigay ng isang pagpapahalagang natutunan sa pamilya.</li><li>Ano ang isang tungkulin ng anak sa pamilya?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Ang pinakamaliit at pinakapangunahing yunit ng lipunan.</li><li>Halimbawa: paggalang, katapatan, pagtutulungan</li><li>Halimbawa: tumulong sa gawaing-bahay, mag-aral nang mabuti</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:21:00'),
(75, 46, 2, 'Komunikasyon sa Pamilya', 'Ang maayos na pag-uusap bilang susi sa matatag na pamilya.', '<p>Maraming hindi pagkakaunawaan sa pamilya ang naiiwasan kung maayos ang pag-uusap.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Komunikasyon</strong> – pagpapalitan ng mensahe, ideya, at damdamin.</li><li><strong>Aktibong pakikinig</strong> – pakikinig nang buong atensyon at pag-unawa.</li><li><strong>Pasalitang komunikasyon</strong> – gamit ang salita.</li><li><strong>Di-pasalitang komunikasyon</strong> – gamit ang kilos, ekspresyon ng mukha, at tono.</li></ul><h3>Halimbawa</h3><blockquote><p>Sa halip na sumigaw kapag naiinis, mahinahong sinabi ni Ben sa kanyang kapatid: &quot;Nalulungkot ako kapag ginagamit mo ang gamit ko nang hindi nagpapaalam.&quot;</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang komunikasyon?</li><li>Ano ang aktibong pakikinig?</li><li>Magbigay ng halimbawa ng di-pasalitang komunikasyon.</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pagpapalitan ng mensahe, ideya, at damdamin.</li><li>Pakikinig nang buong atensyon at pag-unawa.</li><li>Halimbawa: pagngiti, pagsimangot, pagtango</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:21:00'),
(76, 46, 3, 'Pakikipagkapwa', 'Ang tao ay likas na panlipunang nilalang at nangangailangan ng kapwa.', '<p>Ang tao ay likas na panlipunang nilalang at nangangailangan ng kapwa.</p><h3>Mahahalagang Punto</h3><ul><li>Ang pakikipagkapwa ay pagturing sa iba bilang kapantay.</li><li>Mahalaga ang empatiya, o pag-unawa sa damdamin ng iba.</li><li>Ang tunay na kaibigan ay tumutulong sa paglago ng isa&#x27;t isa.</li></ul><h3>Subukan Mo</h3><p>Ilarawan ang katangian ng isang mabuting kaibigan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(77, 46, 4, 'Pasasalamat at Paggalang', 'Ang pasasalamat at paggalang ay mahalagang pagpapahalagang Pilipino.', '<p>Ang pasasalamat at paggalang ay mahalagang pagpapahalagang Pilipino.</p><h3>Mahahalagang Punto</h3><ul><li>Ipinapakita ang paggalang sa pagmamano, &quot;po&quot; at &quot;opo,&quot; at pakikinig sa nakatatanda.</li><li>Ang pasasalamat ay pagkilala sa kabutihang natanggap.</li><li>Nagpapatibay ito ng ugnayan sa kapwa.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng liham ng pasasalamat sa isang taong tumulong sa iyo.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(78, 46, 5, 'Pagharap sa Emosyon', 'Ang emosyon ay natural na bahagi ng buhay, at mahalagang matutuhan itong pamahalaan.', '<p>Ang emosyon ay natural na bahagi ng buhay, at mahalagang matutuhan itong pamahalaan.</p><h3>Mahahalagang Punto</h3><ul><li>Kilalanin ang iyong nararamdaman bago kumilos.</li><li>Humanap ng malusog na paraan ng paglabas ng emosyon, gaya ng pakikipag-usap o ehersisyo.</li><li>Huwag mahiyang humingi ng tulong sa pinagkakatiwalaang tao.</li></ul><h3>Subukan Mo</h3><p>Ano ang ginagawa mo upang huminahon kapag ikaw ay galit?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(79, 47, 1, 'Music of Southeast Asia', 'Gong ensembles and traditional music of our neighbors.', '<p>Countries in Southeast Asia share musical traditions built around gongs and percussion.</p><h3>Key terms</h3><ul><li><strong>Gamelan</strong> – an Indonesian ensemble of gongs, drums, and metal xylophones.</li><li><strong>Piphat</strong> – a Thai ensemble of wind and percussion instruments used in ceremonies.</li><li><strong>Kulintang</strong> – a Philippine set of small gongs played in a row, from Mindanao.</li><li><strong>Ensemble</strong> – a group of musicians playing together.</li></ul><h3>Example</h3><blockquote><p>The kulintang of the Maguindanao and the gamelan of Indonesia both use tuned gongs, showing a shared regional tradition.</p></blockquote><h3>Quick check</h3><ol><li>What is a gamelan?</li><li>What Philippine instrument is similar to the gamelan?</li><li>What is an ensemble?</li></ol><details><summary>Show answers</summary><ol><li>An Indonesian ensemble of gongs, drums, and metal xylophones.</li><li>Kulintang</li><li>A group of musicians playing together.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:21:00'),
(80, 47, 2, 'Arts of Southeast Asia', 'Textiles, sculpture, and puppetry of the region.', '<p>Southeast Asian art often carries religious and cultural meaning.</p><h3>Key terms</h3><ul><li><strong>Batik</strong> – an Indonesian cloth decorated using wax and dye.</li><li><strong>Wayang kulit</strong> – shadow puppetry from Java using leather puppets.</li><li><strong>Angkor Wat</strong> – a huge temple complex in Cambodia known for stone carvings.</li><li><strong>Textile</strong> – woven or printed cloth.</li></ul><h3>Example</h3><blockquote><p>In batik, artists draw designs with hot wax. The waxed parts resist the dye, leaving patterns when the wax is removed.</p></blockquote><h3>Quick check</h3><ol><li>What is batik?</li><li>Where is wayang kulit from?</li><li>Where is Angkor Wat?</li></ol><details><summary>Show answers</summary><ol><li>Cloth decorated using wax and dye.</li><li>Java, Indonesia</li><li>Cambodia</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', '2026-10-05 00:21:00'),
(81, 47, 3, 'PE: Team Sports', 'Team sports develop fitness, teamwork, and sportsmanship.', '<p>Team sports develop fitness, teamwork, and sportsmanship.</p><h3>Key points</h3><ul><li>Basketball and volleyball are popular team sports in the Philippines.</li><li>Learn basic skills like passing, dribbling, and serving.</li><li>Follow rules and respect teammates, opponents, and officials.</li></ul><h3>Try this</h3><p>Explain three basic rules of volleyball.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(82, 47, 4, 'Health: Family Health', 'A healthy family supports the physical and emotional well-being of each member.', '<p>A healthy family supports the physical and emotional well-being of each member.</p><h3>Key points</h3><ul><li>Families can practice healthy habits together, like eating meals and exercising.</li><li>Open communication helps prevent and solve problems.</li><li>Know basic family planning and responsible parenthood concepts.</li></ul><h3>Try this</h3><p>List three activities your family can do together to stay healthy.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL);
INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `description`, `content`, `file_path`, `week_number`, `published`, `updated_by`, `created_at`, `updated_at`) VALUES
(83, 47, 5, 'Health: Communicable Diseases', 'Communicable diseases spread from person to person or through animals, food, and water.', '<p>Communicable diseases spread from person to person or through animals, food, and water.</p><h3>Key points</h3><ul><li>Examples include dengue, tuberculosis, and influenza.</li><li>Prevent them through handwashing, vaccination, and clean surroundings.</li><li>Dengue is prevented by removing mosquito breeding sites.</li></ul><h3>Try this</h3><p>Make a poster on how to prevent dengue in your community.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:13', NULL),
(84, 48, 1, 'Entrepreneurship Basics', 'Starting and running a small business.', '<p>Many Filipinos earn a living by starting their own small businesses, like sari-sari stores and food stalls.</p><h3>Key terms</h3><ul><li><strong>Entrepreneurship</strong> – the process of starting and managing a business.</li><li><strong>Entrepreneur</strong> – a person who starts a business and takes the risk.</li><li><strong>Profit</strong> – money earned after subtracting costs from sales.</li><li><strong>Capital</strong> – money used to start a business.</li></ul><h3>Example</h3><blockquote><p>With ₱2,000 capital, a student sells banana cue. She earns ₱2,800 from sales after a week. Profit = ₱2,800 − ₱2,000 = ₱800.</p></blockquote><h3>Quick check</h3><ol><li>What is an entrepreneur?</li><li>What is capital?</li><li>Sales are ₱1,500 and costs are ₱900. What is the profit?</li></ol><details><summary>Show answers</summary><ol><li>A person who starts a business and takes the risk.</li><li>Money used to start a business.</li><li>₱600</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(85, 48, 2, 'Food Preparation', 'Basic hygiene and safety in preparing food.', '<p>Safe food preparation prevents sickness and keeps food tasty and nutritious.</p><h3>Key terms</h3><ul><li><strong>Food safety</strong> – practices that prevent food from becoming harmful.</li><li><strong>Cross-contamination</strong> – spreading germs from raw food to cooked food.</li><li><strong>Sanitation</strong> – keeping tools and work areas clean.</li><li><strong>Perishable food</strong> – food that spoils quickly, like meat and milk.</li></ul><h3>Example</h3><blockquote><p>A cook uses one chopping board for raw chicken and another for vegetables to avoid cross-contamination.</p></blockquote><h3>Quick check</h3><ol><li>What is cross-contamination?</li><li>Give one example of perishable food.</li><li>Why should we wash hands before cooking?</li></ol><details><summary>Show answers</summary><ol><li>Spreading germs from raw food to cooked food.</li><li>Any one: meat, fish, milk</li><li>To remove germs and keep food safe.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(86, 48, 3, 'Basic Sewing', 'Sewing is a useful skill for making and repairing clothes.', '<p>Sewing is a useful skill for making and repairing clothes.</p><h3>Key points</h3><ul><li>Common hand stitches include running stitch, backstitch, and hemming stitch.</li><li>Use the right needle and thread for the fabric.</li><li>Measure and cut fabric carefully.</li></ul><h3>Try this</h3><p>Repair a loose button or seam using a hand stitch.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(87, 48, 4, 'Basic Electricity', 'Basic electricity skills help with simple and safe home repairs.', '<p>Basic electricity skills help with simple and safe home repairs.</p><h3>Key points</h3><ul><li>A circuit needs a source, a load, and a path for current.</li><li>Never work on live wires; always turn off the power first.</li><li>Use insulated tools and follow safety rules.</li></ul><h3>Try this</h3><p>Draw a simple circuit with a battery, switch, and bulb.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(88, 48, 5, 'ICT Productivity Tools', 'Productivity tools help us create documents, spreadsheets, and presentations.', '<p>Productivity tools help us create documents, spreadsheets, and presentations.</p><h3>Key points</h3><ul><li>Word processors are used for documents and reports.</li><li>Spreadsheets organize numbers and compute totals.</li><li>Presentation software helps share ideas visually.</li></ul><h3>Try this</h3><p>Make a simple budget table in a spreadsheet with a total formula.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(89, 37, 1, 'Panitikang Asyano', 'Mga akdang pampanitikan mula sa iba\'t ibang bansa sa Asya.', '<p>Ang mga bansa sa Asya ay may kani-kaniyang panitikan na nagpapakita ng kanilang kultura at pinahahalagahan.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Panitikang Asyano</strong> – mga akdang pampanitikan mula sa mga bansa sa Asya.</li><li><strong>Tanka</strong> – tulang Hapon na may limang taludtod at 31 pantig (5-7-5-7-7).</li><li><strong>Haiku</strong> – tulang Hapon na may tatlong taludtod at 17 pantig (5-7-5).</li><li><strong>Sanaysay</strong> – akdang naglalahad ng sariling kuro-kuro o pananaw.</li></ul><h3>Halimbawa</h3><blockquote><p><strong>Halimbawa ng haiku</strong></p><p>&quot;Ulan sa bubong / Ang pusa&#x27;y natutulog / Hintay ang araw.&quot; Mayroon itong 5, 7, at 5 na pantig.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ilang pantig mayroon ang haiku?</li><li>Saang bansa nagmula ang tanka at haiku?</li><li>Ano ang sanaysay?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>17 (5-7-5)</li><li>Japan (Hapon)</li><li>Akdang naglalahad ng sariling kuro-kuro o pananaw.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(90, 37, 2, 'Noli Me Tangere: Kaligiran ng Akda', 'Ang unang nobela ni Rizal at ang layunin nito.', '<p>Isinulat ni Dr. Jose Rizal ang Noli Me Tangere upang ipakita ang kalagayan ng Pilipinas sa ilalim ng Espanya.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Noli Me Tangere</strong> – unang nobela ni Rizal, nalathala noong 1887 sa Berlin, Alemanya.</li><li><strong>Kahulugan ng pamagat</strong> – salitang Latin na nangangahulugang &quot;Huwag Mo Akong Salingin.&quot;</li><li><strong>Nobela</strong> – mahabang akdang piksiyon na may maraming kabanata at tauhan.</li><li><strong>Kanser ng lipunan</strong> – tawag ni Rizal sa mga suliranin at pang-aabuso sa lipunan.</li></ul><h3>Halimbawa</h3><blockquote><p>Inialay ni Rizal ang nobela sa Inang Bayan. Naging inspirasyon niya ang nobelang Uncle Tom&#x27;s Cabin, na naglantad ng pang-aalipin sa Amerika.</p></blockquote><h3>Pagsusulit</h3><ol><li>Kailan nalathala ang Noli Me Tangere?</li><li>Ano ang ibig sabihin ng &quot;Noli Me Tangere&quot;?</li><li>Kanino inialay ni Rizal ang nobela?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>1887</li><li>Huwag Mo Akong Salingin</li><li>Sa Inang Bayan</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(91, 37, 3, 'Noli Me Tangere: Mga Tauhan', 'Ang mga tauhan sa Noli ay sumasagisag sa iba\'t ibang uri ng tao sa lipunang Pilipino noong panahon ng Kastila.', '<p>Ang mga tauhan sa Noli ay sumasagisag sa iba&#x27;t ibang uri ng tao sa lipunang Pilipino noong panahon ng Kastila.</p><h3>Mahahalagang Punto</h3><ul><li>Si Crisostomo Ibarra ay binatang nag-aral sa Europa at nais magpabuti sa bayan.</li><li>Si Maria Clara ay sumasagisag sa babaeng Pilipinang mahinhin at tapat.</li><li>Sina Padre Damaso at Padre Salvi ay larawan ng mapang-abusong prayle.</li></ul><h3>Subukan Mo</h3><p>Sino sa mga tauhan ang pinakanagustuhan mo? Ipaliwanag.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(92, 37, 4, 'Pang-abay at Pagpapahayag ng Damdamin', 'Ginagamit ang pang-abay at iba\'t ibang pahayag upang maging malinaw at buhay ang paglalarawan ng kilos at damdamin.', '<p>Ginagamit ang pang-abay at iba&#x27;t ibang pahayag upang maging malinaw at buhay ang paglalarawan ng kilos at damdamin.</p><h3>Mahahalagang Punto</h3><ul><li>Ang pang-abay na pamaraan ay nagsasabi kung paano ginawa ang kilos.</li><li>Ang pang-abay na pamanahon ay nagsasabi kung kailan naganap ang kilos.</li><li>Ang padamdam na pangungusap ay nagpapahayag ng matinding damdamin.</li></ul><h3>Subukan Mo</h3><p>Gumawa ng limang pangungusap na may pang-abay na pamaraan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(93, 37, 5, 'Pagsulat ng Maikling Kuwento', 'Ang maikling kuwento ay akdang may iisang kakintalan at nababasa sa isang upuan lamang.', '<p>Ang maikling kuwento ay akdang may iisang kakintalan at nababasa sa isang upuan lamang.</p><h3>Mahahalagang Punto</h3><ul><li>Elemento nito ang tauhan, tagpuan, banghay, tunggalian, at wakas.</li><li>Ang banghay ay may simula, saglit na kasiglahan, kasukdulan, at kakalasan.</li><li>Dapat may malinaw na tunggalian na nagpapausad sa kuwento.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng maikling kuwento na may tunggalian ng tao laban sa sarili.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(94, 34, 1, 'Anglo-American Literature', 'Works from Britain and the United States.', '<p>Many famous poems, plays, and stories in English come from Britain and America.</p><h3>Key terms</h3><ul><li><strong>Anglo-American literature</strong> – literature from Britain (Anglo) and the United States (American).</li><li><strong>Poetry</strong> – writing that uses rhythm, imagery, and sound to express ideas and feelings.</li><li><strong>Sonnet</strong> – a poem of 14 lines, often about love.</li><li><strong>Short story</strong> – a brief work of fiction that can be read in one sitting.</li></ul><h3>Example</h3><blockquote><p>William Shakespeare wrote many sonnets and plays. Robert Frost, an American poet, wrote &quot;The Road Not Taken,&quot; about making choices in life.</p></blockquote><h3>Quick check</h3><ol><li>What does &quot;Anglo&quot; refer to?</li><li>How many lines does a sonnet have?</li><li>Who wrote &quot;The Road Not Taken&quot;?</li></ol><details><summary>Show answers</summary><ol><li>Britain or England</li><li>14</li><li>Robert Frost</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(95, 34, 2, 'Elements of Drama', 'The parts that make up a play.', '<p>A drama, or play, is a story written to be acted out on stage.</p><h3>Key terms</h3><ul><li><strong>Drama</strong> – a story told through dialogue and action, meant to be performed.</li><li><strong>Dialogue</strong> – the words spoken by the characters.</li><li><strong>Stage directions</strong> – notes that tell actors how to move or speak.</li><li><strong>Act and scene</strong> – the main parts (acts) and smaller parts (scenes) of a play.</li><li><strong>Conflict</strong> – the problem or struggle in the story.</li></ul><h3>Example</h3><blockquote><p>MARIA (looking out the window): &quot;Is it still raining?&quot; — The words in parentheses are stage directions; the line after the colon is dialogue.</p></blockquote><h3>Quick check</h3><ol><li>What is dialogue?</li><li>What do stage directions tell actors?</li><li>What is the conflict in a story?</li></ol><details><summary>Show answers</summary><ol><li>The words spoken by the characters.</li><li>How to move or speak.</li><li>The problem or struggle.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(96, 34, 3, 'Conditional Sentences', 'Conditional sentences show that one thing depends on another, often using \"if.\"', '<p>Conditional sentences show that one thing depends on another, often using &quot;if.&quot;</p><h3>Key points</h3><ul><li>Zero conditional states facts: &quot;If you heat ice, it melts.&quot;</li><li>First conditional talks about real future possibilities: &quot;If it rains, I will stay home.&quot;</li><li>Second conditional imagines unlikely situations: &quot;If I were rich, I would travel.&quot;</li></ul><h3>Try this</h3><p>Write two sentences for each type of conditional.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(97, 34, 4, 'Making Inferences', 'An inference is a logical conclusion based on clues in the text and your own knowledge.', '<p>An inference is a logical conclusion based on clues in the text and your own knowledge.</p><h3>Key points</h3><ul><li>Authors do not always state everything directly.</li><li>Look at characters&#x27; actions, words, and feelings for clues.</li><li>Support each inference with evidence from the text.</li></ul><h3>Try this</h3><p>Read a short story and make two inferences about the main character.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(98, 34, 5, 'Writing a Literary Analysis', 'A literary analysis explains how an author uses elements like theme, character, or symbol to create meaning.', '<p>A literary analysis explains how an author uses elements like theme, character, or symbol to create meaning.</p><h3>Key points</h3><ul><li>State a clear thesis about the work.</li><li>Support the thesis with quotations and explanations.</li><li>Focus on how and why, not just what happens.</li></ul><h3>Try this</h3><p>Write a paragraph analyzing a symbol in a story you have read.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(99, 35, 1, 'Quadratic Equations', 'Equations where the highest power of the variable is 2.', '<p>When a ball is thrown or a garden&#x27;s area is computed, quadratic equations often appear.</p><h3>Key terms</h3><ul><li><strong>Quadratic equation</strong> – an equation in the form ax² + bx + c = 0, where a ≠ 0.</li><li><strong>Roots</strong> – the values of x that make the equation true.</li><li><strong>Factoring method</strong> – solving by writing the equation as a product equal to zero.</li><li><strong>Quadratic formula</strong> – x = [−b ± √(b² − 4ac)] / 2a.</li></ul><h3>Example</h3><blockquote><p>Solve x² − 5x + 6 = 0. Factor: (x − 2)(x − 3) = 0. So x = 2 or x = 3.</p></blockquote><h3>Quick check</h3><ol><li>Is x² + 3x − 4 = 0 a quadratic equation?</li><li>Solve x² − 9 = 0.</li><li>In 2x² + 3x − 5 = 0, what are a, b, and c?</li></ol><details><summary>Show answers</summary><ol><li>Yes</li><li>x = 3 or x = −3</li><li>a = 2, b = 3, c = −5</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(100, 35, 2, 'Quadratic Functions', 'Functions whose graph is a parabola.', '<p>The path of a basketball shot forms a curve called a parabola, which is the graph of a quadratic function.</p><p>If a is positive, the parabola opens upward. If a is negative, it opens downward.</p><h3>Key terms</h3><ul><li><strong>Quadratic function</strong> – a function in the form y = ax² + bx + c, where a ≠ 0.</li><li><strong>Parabola</strong> – the U-shaped graph of a quadratic function.</li><li><strong>Vertex</strong> – the highest or lowest point of the parabola.</li><li><strong>Axis of symmetry</strong> – the vertical line through the vertex that divides the parabola into two equal halves.</li></ul><h3>Example</h3><blockquote><p>y = x² opens upward and its vertex is (0, 0). y = −x² + 4 opens downward and its vertex is (0, 4).</p></blockquote><h3>Quick check</h3><ol><li>What is the graph of a quadratic function called?</li><li>Does y = −2x² open upward or downward?</li><li>What is the vertex of y = x² + 3?</li></ol><details><summary>Show answers</summary><ol><li>Parabola</li><li>Downward</li><li>(0, 3)</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(101, 35, 3, 'Variation', 'Variation describes how one quantity changes in relation to another.', '<p>Variation describes how one quantity changes in relation to another.</p><h3>Key points</h3><ul><li>Direct variation: y = kx, as x increases, y increases.</li><li>Inverse variation: y = k/x, as x increases, y decreases.</li><li>Joint variation: y = kxz, y varies with two quantities at once.</li></ul><h3>Try this</h3><p>If y varies directly with x and y = 12 when x = 3, find y when x = 7.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(102, 35, 4, 'Radicals', 'A radical expression contains a root, such as a square root.', '<p>A radical expression contains a root, such as a square root.</p><h3>Key points</h3><ul><li>Simplify by taking out perfect squares: √50 = 5√2.</li><li>Only like radicals can be added or subtracted.</li><li>Rationalize the denominator to remove radicals from the bottom of a fraction.</li></ul><h3>Try this</h3><p>Simplify √72 and 3√2 + 5√2.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(103, 35, 5, 'Basic Trigonometry', 'Trigonometry studies the relationship between the sides and angles of right triangles.', '<p>Trigonometry studies the relationship between the sides and angles of right triangles.</p><h3>Key points</h3><ul><li>SOH-CAH-TOA: sine = opposite/hypotenuse, cosine = adjacent/hypotenuse, tangent = opposite/adjacent.</li><li>The hypotenuse is the longest side, opposite the right angle.</li><li>Trigonometry is used to find heights and distances.</li></ul><h3>Try this</h3><p>A ladder 10 m long makes a 60° angle with the ground. How high does it reach?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(104, 36, 1, 'Respiratory and Circulatory Systems', 'How the body takes in oxygen and moves blood.', '<p>Every cell in your body needs oxygen. Two body systems work together to deliver it.</p><h3>Key terms</h3><ul><li><strong>Respiratory system</strong> – takes in oxygen and releases carbon dioxide; includes the nose, trachea, and lungs.</li><li><strong>Circulatory system</strong> – moves blood around the body; includes the heart, blood, and blood vessels.</li><li><strong>Alveoli</strong> – tiny air sacs in the lungs where oxygen enters the blood.</li><li><strong>Arteries and veins</strong> – arteries carry blood away from the heart; veins carry blood back to the heart.</li></ul><h3>Example</h3><blockquote><p>When you run, you breathe faster to take in more oxygen, and your heart beats faster to deliver it to your muscles.</p></blockquote><h3>Quick check</h3><ol><li>Where does oxygen enter the blood?</li><li>What do arteries do?</li><li>Why does your heart beat faster when you exercise?</li></ol><details><summary>Show answers</summary><ol><li>In the alveoli of the lungs</li><li>Carry blood away from the heart.</li><li>To deliver more oxygen to the muscles.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(105, 36, 2, 'Heredity', 'How traits are passed from parents to children.', '<p>You may have your mother&#x27;s eyes or your father&#x27;s height. These traits are passed on through heredity.</p><h3>Key terms</h3><ul><li><strong>Heredity</strong> – the passing of traits from parents to offspring.</li><li><strong>Gene</strong> – a part of DNA that carries a trait.</li><li><strong>Dominant trait</strong> – a trait that shows even if only one parent passes it.</li><li><strong>Recessive trait</strong> – a trait that shows only if both parents pass it.</li><li><strong>Gregor Mendel</strong> – known as the &quot;Father of Genetics&quot; for his experiments with pea plants.</li></ul><h3>Example</h3><blockquote><p>T = tall (dominant), t = short (recessive). A plant with Tt is tall. Only a plant with tt is short.</p></blockquote><h3>Quick check</h3><ol><li>What is heredity?</li><li>Who is the Father of Genetics?</li><li>Is a plant with Tt tall or short?</li></ol><details><summary>Show answers</summary><ol><li>The passing of traits from parents to offspring.</li><li>Gregor Mendel</li><li>Tall</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(106, 36, 3, 'Biodiversity and Extinction', 'Biodiversity is the variety of life in an area, and it keeps ecosystems stable.', '<p>Biodiversity is the variety of life in an area, and it keeps ecosystems stable.</p><h3>Key points</h3><ul><li>The Philippines is one of the world&#x27;s megadiverse countries.</li><li>Habitat loss, pollution, and overharvesting threaten species.</li><li>Endangered species include the Philippine eagle and the tamaraw.</li></ul><h3>Try this</h3><p>Suggest three actions students can take to protect local biodiversity.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(107, 36, 4, 'Volcanoes', 'Volcanoes form where magma rises to the Earth\'s surface.', '<p>Volcanoes form where magma rises to the Earth&#x27;s surface.</p><h3>Key points</h3><ul><li>Active volcanoes have erupted recently; inactive ones have not erupted in a long time.</li><li>Examples in the Philippines include Mayon, Taal, and Pinatubo.</li><li>Volcanic eruptions can be dangerous but also create fertile soil.</li></ul><h3>Try this</h3><p>Explain why Mayon Volcano has a near-perfect cone shape.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(108, 36, 5, 'Chemical Bonding', 'Atoms join together through chemical bonds to form compounds.', '<p>Atoms join together through chemical bonds to form compounds.</p><h3>Key points</h3><ul><li>Ionic bonds form when electrons are transferred, usually between a metal and a nonmetal.</li><li>Covalent bonds form when electrons are shared, usually between nonmetals.</li><li>Table salt (NaCl) is ionic; water (H₂O) is covalent.</li></ul><h3>Try this</h3><p>Classify CO₂, KCl, and CH₄ as ionic or covalent.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(109, 49, 1, 'Kahulugan ng Ekonomiks', 'Ang pag-aaral ng matalinong paggamit ng limitadong yaman.', '<p>Hindi lahat ng gusto natin ay makukuha natin. Dito pumapasok ang ekonomiks.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Ekonomiks</strong> – pag-aaral kung paano ginagamit ang limitadong yaman upang matugunan ang pangangailangan ng tao.</li><li><strong>Kakapusan</strong> – kakulangan ng yaman kumpara sa walang hanggang pangangailangan.</li><li><strong>Opportunity cost</strong> – ang halaga ng bagay na isinakripisyo kapalit ng pinili.</li><li><strong>Trade-off</strong> – pagpili ng isang bagay kapalit ng iba.</li></ul><h3>Halimbawa</h3><blockquote><p>May ₱100 si Carlo. Pinili niyang bumili ng libro kaysa manood ng sine. Ang opportunity cost ay ang panonood ng sine.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang ekonomiks?</li><li>Ano ang pangunahing suliranin ng ekonomiks?</li><li>Ano ang opportunity cost?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pag-aaral kung paano ginagamit ang limitadong yaman.</li><li>Kakapusan</li><li>Ang halaga ng bagay na isinakripisyo kapalit ng pinili.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:00'),
(110, 49, 2, 'Pangangailangan at Kagustuhan', 'Ang pagkakaiba ng kailangan at ng gusto lamang.', '<p>Mahalagang matutong pumili sa pagitan ng kailangan at gusto upang hindi masayang ang pera.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Pangangailangan</strong> – mga bagay na kailangan upang mabuhay, gaya ng pagkain, tubig, damit, at tirahan.</li><li><strong>Kagustuhan</strong> – mga bagay na nagbibigay-ginhawa ngunit hindi kailangan upang mabuhay.</li><li><strong>Hirarkiya ng Pangangailangan</strong> – teorya ni Abraham Maslow na nag-aayos ng pangangailangan mula pinakamahalaga.</li></ul><h3>Halimbawa</h3><blockquote><p>Ang bigas ay pangangailangan. Ang bagong cellphone kahit gumagana pa ang luma ay kagustuhan.</p></blockquote><h3>Pagsusulit</h3><ol><li>Magbigay ng isang pangangailangan.</li><li>Ano ang kagustuhan?</li><li>Sino ang bumuo ng Hirarkiya ng Pangangailangan?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Halimbawa: pagkain, tubig, damit, tirahan</li><li>Mga bagay na nagbibigay-ginhawa ngunit hindi kailangan upang mabuhay.</li><li>Abraham Maslow</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(111, 49, 3, 'Demand at Supply', 'Ang demand at supply ang nagtatakda ng presyo ng mga produkto sa pamilihan.', '<p>Ang demand at supply ang nagtatakda ng presyo ng mga produkto sa pamilihan.</p><h3>Mahahalagang Punto</h3><ul><li>Batas ng demand: kapag tumaas ang presyo, bumababa ang dami ng gustong bilhin.</li><li>Batas ng supply: kapag tumaas ang presyo, dumarami ang handang ipagbili.</li><li>Ang ekwilibriyo ay kung saan nagtatagpo ang demand at supply.</li></ul><h3>Subukan Mo</h3><p>Ipaliwanag kung bakit tumataas ang presyo ng gulay kapag may bagyo.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(112, 49, 4, 'Pamilihan', 'Ang pamilihan ay lugar o sistema kung saan nagtatagpo ang mamimili at nagbibili.', '<p>Ang pamilihan ay lugar o sistema kung saan nagtatagpo ang mamimili at nagbibili.</p><h3>Mahahalagang Punto</h3><ul><li>Sa ganap na kompetisyon, maraming nagbibili ng magkakatulad na produkto.</li><li>Ang monopolyo ay may iisang nagbibili.</li><li>Ang pamahalaan ay nagtatakda ng price ceiling at price floor upang protektahan ang mamimili.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng halimbawa ng monopolyo sa Pilipinas.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(113, 49, 5, 'Pambansang Ekonomiya', 'Sinusukat ang kalagayan ng ekonomiya ng bansa gamit ang iba\'t ibang panukat.', '<p>Sinusukat ang kalagayan ng ekonomiya ng bansa gamit ang iba&#x27;t ibang panukat.</p><h3>Mahahalagang Punto</h3><ul><li>Ang GDP ay kabuuang halaga ng produkto at serbisyong nagawa sa loob ng bansa.</li><li>Ang implasyon ay pagtaas ng pangkalahatang presyo ng bilihin.</li><li>May papel ang pamahalaan sa pagpapatatag ng ekonomiya.</li></ul><h3>Subukan Mo</h3><p>Paano nakaaapekto ang implasyon sa inyong pamilya?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(114, 50, 1, 'Lipunan at Kabutihang Panlahat', 'Ang layunin ng lipunan at ang kabutihang para sa lahat.', '<p>Hindi tayo nabubuhay nang mag-isa. Kasapi tayo ng lipunan na may iisang layunin.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Lipunan</strong> – pangkat ng mga taong may iisang layunin at magkakaugnay.</li><li><strong>Kabutihang panlahat</strong> – kabutihang nakatutulong sa lahat ng kasapi ng lipunan.</li><li><strong>Pakikilahok</strong> – aktibong pagsali sa gawain ng komunidad.</li></ul><h3>Halimbawa</h3><blockquote><p>Ang paglilinis ng barangay tuwing Sabado ay nakatutulong sa kalusugan ng lahat, hindi lang ng iilan.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang lipunan?</li><li>Ano ang kabutihang panlahat?</li><li>Magbigay ng isang paraan ng pakikilahok sa komunidad.</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Pangkat ng mga taong may iisang layunin at magkakaugnay.</li><li>Kabutihang nakatutulong sa lahat ng kasapi ng lipunan.</li><li>Halimbawa: clean-up drive, pagboboluntaryo</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(115, 50, 2, 'Karapatan at Tungkulin', 'Ang bawat karapatan ay may kaakibat na tungkulin.', '<p>Bilang tao at mamamayan, may mga karapatan tayo. Ngunit kasama nito ang mga tungkuling dapat gampanan.</p><h3>Mahahalagang Kahulugan</h3><ul><li><strong>Karapatan</strong> – kapangyarihang moral na gawin o tamasahin ang isang bagay.</li><li><strong>Tungkulin</strong> – obligasyong moral na dapat gawin.</li><li><strong>Paggalang</strong> – pagkilala sa karapatan ng iba.</li></ul><h3>Halimbawa</h3><blockquote><p>Karapatan ng mag-aaral na matuto; tungkulin niyang mag-aral nang mabuti at igalang ang guro at kaklase.</p></blockquote><h3>Pagsusulit</h3><ol><li>Ano ang karapatan?</li><li>Ano ang kaakibat ng bawat karapatan?</li><li>Kung karapatan mo ang malayang pagpapahayag, ano ang iyong tungkulin?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Kapangyarihang moral na gawin o tamasahin ang isang bagay.</li><li>Tungkulin</li><li>Magsalita nang may katotohanan at paggalang.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(116, 50, 3, 'Katarungang Panlipunan', 'Ang katarungang panlipunan ay pagbibigay sa bawat tao ng nararapat sa kanya.', '<p>Ang katarungang panlipunan ay pagbibigay sa bawat tao ng nararapat sa kanya.</p><h3>Mahahalagang Punto</h3><ul><li>Walang dapat maiwan, lalo na ang mahihirap at mahihina.</li><li>Ang patas na pagtrato ay simula ng katarungan.</li><li>Ang batas ay dapat ipatupad nang pantay sa lahat.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng halimbawa ng kawalang-katarungan at kung paano ito masosolusyonan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(117, 50, 4, 'Kagalingan sa Paggawa', 'Ang paggawa ay paraan ng tao upang paunlarin ang sarili at makatulong sa lipunan.', '<p>Ang paggawa ay paraan ng tao upang paunlarin ang sarili at makatulong sa lipunan.</p><h3>Mahahalagang Punto</h3><ul><li>Ang kagalingan sa paggawa ay paggawa nang may kalidad at katapatan.</li><li>Mahalaga ang sipag, tiyaga, at pananagutan.</li><li>Ang bawat trabaho, maliit man o malaki, ay may dignidad.</li></ul><h3>Subukan Mo</h3><p>Paano mo maipapakita ang kagalingan sa paggawa sa iyong pag-aaral?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(118, 50, 5, 'Pagpaplano ng Karera', 'Ang maagang pagpaplano ay tutulong sa iyo na makapili ng tamang landas sa hinaharap.', '<p>Ang maagang pagpaplano ay tutulong sa iyo na makapili ng tamang landas sa hinaharap.</p><h3>Mahahalagang Punto</h3><ul><li>Isaalang-alang ang talento, hilig, pagpapahalaga, at oportunidad.</li><li>Alamin ang mga track sa Senior High: Academic, TVL, Sports, at Arts and Design.</li><li>Kumonsulta sa magulang at guidance counselor.</li></ul><h3>Subukan Mo</h3><p>Isulat ang iyong pangarap na trabaho at ang mga hakbang upang maabot ito.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(119, 51, 1, 'Music of the Medieval to Classical Periods', 'How Western music developed over time.', '<p>Western music changed a lot from the songs of medieval churches to the symphonies of the Classical period.</p><h3>Key terms</h3><ul><li><strong>Medieval period</strong> – the time of Gregorian chant, simple religious songs sung in unison.</li><li><strong>Renaissance</strong> – music with richer harmony and many voice parts.</li><li><strong>Baroque period</strong> – music with lots of ornaments; composers include Bach and Vivaldi.</li><li><strong>Classical period</strong> – music with balance and clarity; composers include Mozart and Haydn.</li></ul><h3>Example</h3><blockquote><p>Vivaldi&#x27;s &quot;The Four Seasons&quot; (Baroque) describes spring, summer, autumn, and winter through music.</p></blockquote><h3>Quick check</h3><ol><li>What is Gregorian chant?</li><li>Name a Baroque composer.</li><li>What is Classical music known for?</li></ol><details><summary>Show answers</summary><ol><li>Simple religious songs sung in unison in the Medieval period.</li><li>Any one: Bach, Vivaldi, Handel</li><li>Balance and clarity</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(120, 51, 2, 'Western Classical Art', 'Major art periods from the Renaissance onward.', '<p>European art went through periods with different styles and goals.</p><h3>Key terms</h3><ul><li><strong>Renaissance art</strong> – realistic art that used perspective; artists include Leonardo da Vinci and Michelangelo.</li><li><strong>Perspective</strong> – a technique that makes a flat picture look three-dimensional.</li><li><strong>Baroque art</strong> – dramatic art with strong contrasts of light and dark.</li><li><strong>Fresco</strong> – painting on wet plaster on walls or ceilings.</li></ul><h3>Example</h3><blockquote><p>Michelangelo painted the ceiling of the Sistine Chapel as a fresco. Leonardo da Vinci painted the Mona Lisa.</p></blockquote><h3>Quick check</h3><ol><li>Who painted the Mona Lisa?</li><li>What is a fresco?</li><li>What does perspective do in a painting?</li></ol><details><summary>Show answers</summary><ol><li>Leonardo da Vinci</li><li>Painting on wet plaster on walls or ceilings.</li><li>Makes a flat picture look three-dimensional.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(121, 51, 3, 'PE: Dance', 'Dance is a form of exercise and expression that improves fitness and coordination.', '<p>Dance is a form of exercise and expression that improves fitness and coordination.</p><h3>Key points</h3><ul><li>Social dances, such as ballroom dances, are done with a partner.</li><li>Dance improves cardiovascular endurance and flexibility.</li><li>Practice proper posture and timing.</li></ul><h3>Try this</h3><p>Learn and perform the basic steps of a ballroom dance.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(122, 51, 4, 'Health: Substance Use and Abuse', 'Substance abuse harms the body, mind, family, and community.', '<p>Substance abuse harms the body, mind, family, and community.</p><h3>Key points</h3><ul><li>Commonly abused substances include tobacco, alcohol, and illegal drugs.</li><li>Effects include addiction, poor health, and problems at school.</li><li>Saying no and choosing healthy friends are strong protective factors.</li></ul><h3>Try this</h3><p>Write a short dialogue showing how to refuse an offer to smoke.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(123, 51, 5, 'Health: First Aid', 'First aid is the immediate care given to an injured or sick person before professional help arrives.', '<p>First aid is the immediate care given to an injured or sick person before professional help arrives.</p><h3>Key points</h3><ul><li>Check the scene for safety first.</li><li>For bleeding, apply direct pressure with a clean cloth.</li><li>Call emergency services when needed.</li></ul><h3>Try this</h3><p>Demonstrate the proper way to treat a minor cut or burn.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(124, 52, 1, 'Choosing a TLE Specialization', 'Picking one skill area to study in depth.', '<p>In Grade 9, students focus on one TLE area to build deeper skills.</p><h3>Key terms</h3><ul><li><strong>Specialization</strong> – one area of TLE studied in depth, such as Cookery or Computer Systems Servicing.</li><li><strong>Competency</strong> – a skill a worker must be able to do.</li><li><strong>TESDA</strong> – Technical Education and Skills Development Authority, which sets skill standards.</li><li><strong>National Certificate (NC)</strong> – a TESDA certification showing a person has the required skills.</li></ul><h3>Example</h3><blockquote><p>A student who likes fixing computers chooses Computer Systems Servicing and later takes the NC II assessment.</p></blockquote><h3>Quick check</h3><ol><li>What does TESDA stand for?</li><li>What is a competency?</li><li>What does an NC show?</li></ol><details><summary>Show answers</summary><ol><li>Technical Education and Skills Development Authority</li><li>A skill a worker must be able to do.</li><li>That a person has the required skills.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(125, 52, 2, 'Occupational Health and Safety', 'Protecting workers from hazards at work.', '<p>Every job has risks. Occupational health and safety helps prevent injuries and illness.</p><h3>Key terms</h3><ul><li><strong>Occupational health and safety (OHS)</strong> – practices that protect workers&#x27; health and safety.</li><li><strong>Physical hazard</strong> – things like noise, heat, and sharp objects.</li><li><strong>Chemical hazard</strong> – harmful substances like cleaning chemicals.</li><li><strong>Ergonomic hazard</strong> – poor posture or repetitive movements that strain the body.</li></ul><h3>Example</h3><blockquote><p>A computer technician uses an anti-static wrist strap and sits with proper posture to avoid electrical and ergonomic hazards.</p></blockquote><h3>Quick check</h3><ol><li>What is OHS?</li><li>Give one example of a chemical hazard.</li><li>Bad posture while using a computer is what type of hazard?</li></ol><details><summary>Show answers</summary><ol><li>Practices that protect workers&#x27; health and safety.</li><li>Any one: cleaning chemicals, paint, pesticides</li><li>Ergonomic hazard</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(126, 52, 3, 'Quality Standards', 'Quality standards ensure that products and services meet customer expectations.', '<p>Quality standards ensure that products and services meet customer expectations.</p><h3>Key points</h3><ul><li>Follow correct procedures every time.</li><li>Check your work for errors before finishing.</li><li>Customer satisfaction leads to repeat business.</li></ul><h3>Try this</h3><p>Create a checklist to inspect the quality of a finished product.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(127, 52, 4, 'Costing and Pricing', 'Knowing the cost of a product helps set a fair and profitable price.', '<p>Knowing the cost of a product helps set a fair and profitable price.</p><h3>Key points</h3><ul><li>Total cost includes materials, labor, and overhead.</li><li>Selling price = total cost + desired profit (markup).</li><li>Track all expenses accurately.</li></ul><h3>Try this</h3><p>Compute the selling price of a product that costs ₱80 with a 25% markup.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(128, 52, 5, 'Customer Service', 'Good customer service builds trust and loyalty.', '<p>Good customer service builds trust and loyalty.</p><h3>Key points</h3><ul><li>Greet customers politely and listen to their needs.</li><li>Handle complaints calmly and find solutions.</li><li>Keep promises and deliver on time.</li></ul><h3>Try this</h3><p>Role-play how to handle a customer who received a defective product.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(129, 41, 1, 'Mitolohiya', 'Mga kuwento ng mga diyos at bayani na nagpapaliwanag sa pinagmulan ng daigdig.', '<blockquote><p><strong>Sa pagtatapos ng modyul na ito, inaasahang kaya mong:</strong></p><ul><li>Ipaliwanag kung ano ang mitolohiya at ang layunin nito</li><li>Tukuyin ang mga elemento ng mitolohiya</li><li>Paghambingin ang mitolohiyang Griyego at mitolohiyang Pilipino</li></ul></blockquote><p>Bago pa umusbong ang agham, sinikap na ng mga sinaunang tao na ipaliwanag ang mga bagay na nakikita nila: bakit kumikidlat, saan nagmula ang tao, at bakit nagbabago ang panahon. Ang mga sagot nila ay isinalaysay sa anyo ng mga kuwento. Ito ang tinatawag nating <strong>mitolohiya</strong>.</p><h3>Ano ang mitolohiya?</h3><p>Ang mitolohiya ay kalipunan ng mga mito. Ang <strong>mito</strong> ay kuwentong may kinalaman sa mga diyos, diyosa, at bayani, at karaniwang nagpapaliwanag ng pinagmulan ng daigdig, ng tao, o ng mga pangyayari sa kalikasan. Itinuturing itong banal o totoo ng mga taong naniniwala rito noong unang panahon.</p><h3>Mga elemento ng mitolohiya</h3><ol><li><strong>Tauhan</strong> – mga diyos, diyosa, at bayaning may di-pangkaraniwang kapangyarihan.</li><li><strong>Tagpuan</strong> – karaniwang sinaunang panahon, sa langit, dagat, o ilalim ng lupa.</li><li><strong>Banghay</strong> – maaaring tungkol sa paglikha, pakikipagsapalaran, o parusa ng mga diyos.</li><li><strong>Tema</strong> – nagpapaliwanag ng pinagmulan ng isang bagay o nagtuturo ng aral tungkol sa ugali ng tao.</li></ol><h3>Mitolohiyang Griyego at Romano</h3><p>Ang mga Griyego ay may labindalawang pangunahing diyos na nakatira sa Bundok Olympus. Nang sakupin ng mga Romano ang Gresya, inangkin nila ang mga diyos na ito ngunit pinalitan ang pangalan.</p><table><thead><tr><th>Griyego</th><th>Romano</th><th>Kapangyarihan</th></tr></thead><tbody><tr><td>Zeus</td><td>Jupiter</td><td>Hari ng mga diyos, diyos ng langit at kidlat</td></tr><tr><td>Poseidon</td><td>Neptune</td><td>Diyos ng dagat</td></tr><tr><td>Hades</td><td>Pluto</td><td>Diyos ng ilalim ng lupa</td></tr><tr><td>Athena</td><td>Minerva</td><td>Diyosa ng karunungan</td></tr><tr><td>Aphrodite</td><td>Venus</td><td>Diyosa ng pag-ibig at kagandahan</td></tr></tbody></table><h3>Mitolohiyang Pilipino</h3><p>May sariling mitolohiya rin ang mga Pilipino bago pa dumating ang mga Espanyol. Sa mga Tagalog, si <strong>Bathala</strong> ang pinakamakapangyarihang diyos at lumikha ng daigdig. Kilala rin ang kuwento nina <strong>Malakas at Maganda</strong>, ang unang lalaki at babae na lumabas mula sa isang biyas ng kawayan.</p><blockquote><p><strong>Halimbawa ng pagsusuri</strong></p><p>Sa kuwento nina Malakas at Maganda, ipinapakita ang paniniwalang magkapantay ang babae at lalaki dahil sabay silang lumabas mula sa iisang kawayan. Ipinapaliwanag din nito ang pinagmulan ng tao gamit ang isang bagay na pamilyar sa mga Pilipino.</p></blockquote><h3>Bakit mahalaga ang mitolohiya?</h3><ul><li>Ipinapakita nito ang kultura at paniniwala ng sinaunang tao.</li><li>Pinagmulan ito ng maraming salita at simbolo ngayon, gaya ng \"Achilles\' heel\" at ng pangalan ng mga planeta.</li><li>Nagtuturo ito ng aral tungkol sa kayabangan, katapangan, at katapatan.</li></ul><h3>Gawain</h3><p>Pumili ng isang diyos o diyosa mula sa mitolohiyang Griyego at isang diyos mula sa mitolohiyang Pilipino. Gumawa ng maikling paghahambing (5–7 pangungusap) tungkol sa kanilang kapangyarihan at kung ano ang ipinapakita nito tungkol sa paniniwala ng mga taong lumikha ng kuwento.</p><h3>Pagsusulit</h3><ol><li>Ano ang pagkakaiba ng mito at mitolohiya?</li><li>Ano ang Romanong pangalan ni Zeus?</li><li>Saan lumabas sina Malakas at Maganda?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Ang mito ay isang kuwento; ang mitolohiya ay kalipunan ng mga mito.</li><li>Jupiter</li><li>Sa isang biyas ng kawayan</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(130, 41, 2, 'Parabula', 'Maikling kuwentong may aral na naghahatid ng katotohanang moral sa pamamagitan ng paghahambing.', '<blockquote><p><strong>Sa pagtatapos ng modyul na ito, inaasahang kaya mong:</strong></p><ul><li>Ipaliwanag ang katangian ng parabula</li><li>Tukuyin ang talinghaga at aral ng isang parabula</li><li>Iugnay ang aral ng parabula sa sariling karanasan</li></ul></blockquote><p>Kung minsan, mas madaling maintindihan ang isang aral kapag isinalaysay ito bilang kuwento. Ganito ang ginagawa ng <strong>parabula</strong>.</p><h3>Ano ang parabula?</h3><p>Ang parabula ay maikling salaysay na naghahatid ng <strong>aral na moral o espiritwal</strong>. Hango ito sa salitang Griyego na <em>parabole</em>, na nangangahulugang \"paghahambing.\" Karaniwang hango ang mga kilalang parabula sa Bibliya.</p><h3>Mga katangian ng parabula</h3><ol><li><strong>Payak na kuwento</strong> – simple ang mga pangyayari at kakaunti ang tauhan.</li><li><strong>Tao ang tauhan</strong> – hindi tulad ng pabula na hayop ang tauhan.</li><li><strong>May talinghaga</strong> – may nakatagong kahulugan sa likod ng mga pangyayari.</li><li><strong>Malinaw na aral</strong> – nagtuturo kung paano dapat mamuhay ang tao.</li></ol><h3>Halimbawa: Ang Alibughang Anak</h3><blockquote><p>Hiningi ng bunsong anak ang kanyang mana sa ama at nilustay ito sa malayong lugar. Nang maubos ang pera at dumating ang taggutom, nag-alaga siya ng baboy at nagutom. Nagpasya siyang umuwi at humingi ng tawad. Sinalubong siya ng ama nang may yakap at handaan. Nagtampo ang panganay na anak, ngunit sinabi ng ama na dapat silang magsaya dahil \"ang nawala ay natagpuan.\"</p></blockquote><table><thead><tr><th>Bahagi ng kuwento</th><th>Talinghaga (nakatagong kahulugan)</th></tr></thead><tbody><tr><td>Ang ama</td><td>Ang Diyos o ang mapagmahal na magulang</td></tr><tr><td>Ang bunsong anak</td><td>Taong nagkasala ngunit nagsisi</td></tr><tr><td>Ang handaan</td><td>Kagalakan sa pagbabalik-loob</td></tr><tr><td>Ang panganay na anak</td><td>Taong nahihirapang magpatawad</td></tr></tbody></table><h3>Paano suriin ang parabula</h3><ul><li>Alamin kung sino ang mga tauhan at ano ang ginawa nila.</li><li>Hanapin ang talinghaga: ano ang kinakatawan ng bawat tauhan o bagay?</li><li>Itanong: anong aral ang nais iparating ng kuwento?</li><li>Iugnay ang aral sa tunay na buhay.</li></ul><h3>Gawain</h3><p>Sumulat ng sariling maikling parabula (isang talata) na nagtuturo ng aral tungkol sa katapatan. Gumamit ng tauhang tao at tagpuang pamilyar sa iyo, gaya ng paaralan o palengke. Sa dulo, isulat sa isang pangungusap ang aral nito.</p><h3>Pagsusulit</h3><ol><li>Ano ang ibig sabihin ng salitang Griyego na <em>parabole</em>?</li><li>Ano ang pagkakaiba ng parabula at pabula?</li><li>Sa \"Alibughang Anak,\" ano ang aral tungkol sa pagpapatawad?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Paghahambing</li><li>Tao ang tauhan sa parabula; hayop ang tauhan sa pabula.</li><li>Dapat tanggapin at patawarin ang taong tunay na nagsisisi.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(131, 41, 3, 'El Filibusterismo: Kaligiran ng Akda', 'Ang El Filibusterismo ay ikalawang nobela ni Rizal, nalathala noong 1891, at karugtong ng Noli Me Tangere.', '<p>Ang El Filibusterismo ay ikalawang nobela ni Rizal, nalathala noong 1891, at karugtong ng Noli Me Tangere.</p><h3>Mahahalagang Punto</h3><ul><li>Inialay ito sa tatlong paring martir na GomBurZa.</li><li>Mas madilim at mapusok ang himig nito kaysa sa Noli.</li><li>Tinatalakay nito ang paghihimagsik at ang tamang paraan ng pagbabago.</li></ul><h3>Subukan Mo</h3><p>Bakit sa tingin mo inialay ni Rizal ang nobela sa GomBurZa?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(132, 41, 4, 'El Filibusterismo: Si Simoun at ang mga Tauhan', 'Bumalik si Crisostomo Ibarra bilang si Simoun, isang mayamang mag-aalahas na may lihim na planong maghiganti.', '<p>Bumalik si Crisostomo Ibarra bilang si Simoun, isang mayamang mag-aalahas na may lihim na planong maghiganti.</p><h3>Mahahalagang Punto</h3><ul><li>Ginagamit ni Simoun ang kanyang yaman upang palalain ang katiwalian sa pamahalaan.</li><li>Si Basilio ay estudyanteng nais makatapos at maglingkod sa bayan.</li><li>Si Isagani ay makatang kumakatawan sa kabataang puno ng pag-asa.</li></ul><h3>Subukan Mo</h3><p>Sang-ayon ka ba sa paraan ni Simoun ng pagbabago? Ipaliwanag.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(133, 41, 5, 'Pagsulat ng Talumpati', 'Ang talumpati ay pagpapahayag ng kaisipan sa harap ng madla upang humikayat, magpaliwanag, o magbigay-inspirasyon.', '<p>Ang talumpati ay pagpapahayag ng kaisipan sa harap ng madla upang humikayat, magpaliwanag, o magbigay-inspirasyon.</p><h3>Mahahalagang Punto</h3><ul><li>Ang panimula ay dapat makakuha agad ng atensyon ng tagapakinig.</li><li>Ang katawan ay naglalaman ng mga katwiran at patunay.</li><li>Ang wakas ay nag-iiwan ng malinaw na mensahe o hamon.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng tatlong minutong talumpati tungkol sa papel ng kabataan sa bayan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(134, 38, 1, 'Reading World Literature', 'How literature from many countries reveals shared human experiences.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Explain what world literature is and why we read it</li><li>Identify the theme of a literary work</li><li>Connect a work to its cultural and historical context</li></ul></blockquote><p>A story written centuries ago in Greece, Japan, or Nigeria can still make a Filipino student feel something today. That is the power of <strong>world literature</strong>: works from many cultures that speak to experiences all people share.</p><h3>What is world literature?</h3><p>World literature includes poems, stories, plays, and novels from different countries that are read beyond their place of origin, often in translation. Examples include Greek myths, the Japanese haiku, African folktales, and Latin American short stories.</p><h3>Finding the theme</h3><p>The <strong>theme</strong> is the central message or insight about life that a work expresses. It is not the topic. The topic is a word or phrase; the theme is a complete idea.</p><table><thead><tr><th>Topic</th><th>Possible theme</th></tr></thead><tbody><tr><td>Pride</td><td>Excessive pride can lead to a person\'s downfall.</td></tr><tr><td>Family</td><td>Love within a family can survive great hardship.</td></tr><tr><td>Freedom</td><td>True freedom requires courage and sacrifice.</td></tr></tbody></table><blockquote><p><strong>Example</strong></p><p>In the Greek myth of Icarus, a boy flies too close to the sun with wings of wax and falls into the sea. Topic: ambition. Theme: ignoring wise advice out of overconfidence can lead to disaster.</p></blockquote><h3>Reading in context</h3><p>Every work comes from a specific time and culture. Knowing the context helps you understand choices the writer made.</p><ul><li><strong>Historical context</strong> – events happening when the work was written, such as war or colonization.</li><li><strong>Cultural context</strong> – beliefs, values, and customs of the people in the work.</li><li><strong>Author\'s background</strong> – the writer\'s own experiences that shaped the work.</li></ul><h3>Universal themes</h3><p>Some themes appear in literature everywhere: love, loss, courage, justice, identity, and the struggle between good and evil. When you notice the same theme in a Filipino story and a foreign one, you are seeing what connects all people.</p><h3>Activity</h3><p>Choose any story, myth, or poem from another country that you have read or heard. In one paragraph, state its topic, its theme as a complete sentence, and one detail from the work that supports the theme.</p><h3>Quick check</h3><ol><li>What is the difference between a topic and a theme?</li><li>Name two kinds of context that help readers understand a work.</li><li>Give one universal theme.</li></ol><details><summary>Show answers</summary><ol><li>A topic is a word or phrase; a theme is a complete statement about life.</li><li>Any two: historical, cultural, author\'s background.</li><li>Any one: love, loss, courage, justice, identity, good versus evil.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01');
INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `description`, `content`, `file_path`, `week_number`, `published`, `updated_by`, `created_at`, `updated_at`) VALUES
(135, 38, 2, 'Writing an Argumentative Essay', 'Taking a clear position and supporting it with reasons and evidence.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Write a clear claim on an issue</li><li>Support the claim with reasons and evidence</li><li>Address the opposing view in a respectful way</li></ul></blockquote><p>When you convince your parents to extend your curfew or explain to a teacher why a deadline should move, you are arguing. An <strong>argumentative essay</strong> does the same thing in writing, using logic and evidence instead of emotion alone.</p><h3>Parts of an argument</h3><ol><li><strong>Claim</strong> – your position on the issue, stated in one clear sentence.</li><li><strong>Reasons</strong> – why you believe the claim is true.</li><li><strong>Evidence</strong> – facts, statistics, examples, or expert opinions that support each reason.</li><li><strong>Counterclaim</strong> – the opposing view.</li><li><strong>Rebuttal</strong> – your response showing why the opposing view is weaker.</li></ol><blockquote><p><strong>Example</strong></p><p><strong>Claim:</strong> Schools should allow students to use phones for learning during class.<br><strong>Reason:</strong> Phones give quick access to dictionaries, research, and learning apps.<br><strong>Evidence:</strong> A class survey shows most students use their phones to look up unfamiliar words.<br><strong>Counterclaim:</strong> Phones distract students from the lesson.<br><strong>Rebuttal:</strong> Clear rules, such as using phones only for assigned tasks, can reduce distraction while keeping the benefits.</p></blockquote><h3>Writing a strong claim</h3><ul><li>Take a side; avoid \"maybe\" or \"it depends.\"</li><li>Make it debatable; facts like \"Manila is the capital\" are not arguments.</li><li>Keep it specific.</li></ul><table><thead><tr><th>Weak claim</th><th>Strong claim</th></tr></thead><tbody><tr><td>Uniforms have good and bad sides.</td><td>Public high schools should keep school uniforms to reduce pressure over clothing.</td></tr></tbody></table><h3>Essay structure</h3><ul><li><strong>Introduction</strong> – hook the reader, give background, state your claim.</li><li><strong>Body paragraphs</strong> – one reason per paragraph with evidence.</li><li><strong>Counterargument paragraph</strong> – present and answer the opposing view.</li><li><strong>Conclusion</strong> – restate the claim and leave a final thought or call to action.</li></ul><h3>Useful transitions</h3><p>To add a reason: <em>furthermore, in addition</em>. To give evidence: <em>for example, according to</em>. To address the other side: <em>some may argue, however</em>. To conclude: <em>therefore, in conclusion</em>.</p><h3>Activity</h3><p>Write a five-paragraph argumentative essay on this question: <em>Should senior high students be required to do community service?</em> Include a claim, two reasons with evidence, a counterclaim with rebuttal, and a conclusion.</p><h3>Quick check</h3><ol><li>What is a rebuttal?</li><li>Why is \"Water boils at 100°C\" not a good claim?</li><li>Give two transitions used to present evidence.</li></ol><details><summary>Show answers</summary><ol><li>Your response explaining why the opposing view is weaker.</li><li>It is a fact, not a debatable position.</li><li>Any two: for example, for instance, according to, research shows.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(136, 38, 3, 'Evaluating Evidence and Bias', 'Critical readers check whether evidence is reliable and whether a writer shows bias.', '<p>Critical readers check whether evidence is reliable and whether a writer shows bias.</p><h3>Key points</h3><ul><li>Reliable sources are current, accurate, and from qualified authors.</li><li>Bias appears when a writer favors one side unfairly.</li><li>Look for loaded words and missing viewpoints.</li></ul><h3>Try this</h3><p>Compare two news reports on the same event and identify any bias.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(137, 38, 4, 'Reported Speech', 'Reported speech tells what someone said without quoting their exact words.', '<p>Reported speech tells what someone said without quoting their exact words.</p><h3>Key points</h3><ul><li>Direct: She said, &quot;I am tired.&quot;</li><li>Reported: She said that she was tired.</li><li>Verb tenses usually shift back one step in reported speech.</li></ul><h3>Try this</h3><p>Change five direct quotations into reported speech.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(138, 38, 5, 'Delivering a Persuasive Speech', 'A persuasive speech aims to convince the audience to believe or do something.', '<p>A persuasive speech aims to convince the audience to believe or do something.</p><h3>Key points</h3><ul><li>Use ethos (credibility), pathos (emotion), and logos (logic).</li><li>Organize your speech with a hook, arguments, and a call to action.</li><li>Practice eye contact, voice, and gestures.</li></ul><h3>Try this</h3><p>Prepare a two-minute speech persuading classmates to conserve water.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(139, 39, 1, 'Arithmetic Sequences and Series', 'Patterns that grow by adding the same number each time, and how to find their sums.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Identify an arithmetic sequence and its common difference</li><li>Find any term using the nth-term formula</li><li>Find the sum of the first n terms</li></ul></blockquote><p>If you save ₱20 on the first day, ₱25 on the second, ₱30 on the third, and so on, your daily savings form a pattern. Patterns like this are called <strong>sequences</strong>.</p><h3>What is an arithmetic sequence?</h3><p>An <strong>arithmetic sequence</strong> is a list of numbers where the same number is added to get the next term. That number is the <strong>common difference</strong>, <em>d</em>.</p><blockquote><p><strong>Example 1</strong></p><p>3, 7, 11, 15, … has d = 7 − 3 = 4. It is arithmetic.<br>2, 4, 8, 16, … is not arithmetic, because 4 − 2 = 2 but 8 − 4 = 4.</p></blockquote><h3>Finding the nth term</h3><p>Formula: <strong>aₙ = a₁ + (n − 1)d</strong>, where a₁ is the first term, n is the term number, and d is the common difference.</p><blockquote><p><strong>Example 2</strong></p><p>Find the 20th term of 5, 8, 11, 14, …<br>a₁ = 5, d = 3, n = 20<br>a₂₀ = 5 + (20 − 1)(3) = 5 + 57 = <strong>62</strong></p></blockquote><blockquote><p><strong>Example 3</strong></p><p>Which term of 4, 10, 16, … is 100?<br>100 = 4 + (n − 1)(6) → 96 = 6(n − 1) → n − 1 = 16 → <strong>n = 17</strong></p></blockquote><h3>Arithmetic series</h3><p>A <strong>series</strong> is the sum of the terms. The sum of the first n terms is:</p><p><strong>Sₙ = n/2 (a₁ + aₙ)</strong> &nbsp;or&nbsp; <strong>Sₙ = n/2 [2a₁ + (n − 1)d]</strong></p><blockquote><p><strong>Example 4</strong></p><p>Back to the savings: ₱20, ₱25, ₱30, … How much is saved in 30 days?<br>a₁ = 20, d = 5, n = 30<br>S₃₀ = 30/2 [2(20) + (29)(5)] = 15 [40 + 145] = 15(185) = <strong>₱2,775</strong></p></blockquote><h3>Common mistakes</h3><ul><li>Using n instead of (n − 1) in the nth-term formula.</li><li>Getting d backwards: always subtract a term from the term after it.</li><li>Forgetting that d can be negative, as in 50, 46, 42, … where d = −4.</li></ul><h3>Activity</h3><p>A theater has 18 seats in the first row, and each row after has 3 more seats than the row before. There are 25 rows. Find the number of seats in the last row and the total number of seats.</p><h3>Quick check</h3><ol><li>Find d in 12, 9, 6, 3, …</li><li>Find the 15th term of 2, 9, 16, …</li><li>Find the sum of the first 10 terms of 1, 4, 7, …</li></ol><details><summary>Show answers</summary><ol><li>d = −3</li><li>a₁₅ = 2 + 14(7) = 100</li><li>S₁₀ = 5 [2 + 27] = 145</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:01'),
(140, 39, 2, 'Geometric Sequences and Series', 'Patterns that grow by multiplying by the same number, and their sums.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Identify a geometric sequence and its common ratio</li><li>Find any term using the nth-term formula</li><li>Find the sum of a finite geometric series</li></ul></blockquote><p>When a video gets shared by 3 people, and each of them shares it with 3 more, the number of viewers grows very quickly: 1, 3, 9, 27, 81. This kind of growth is <strong>geometric</strong>.</p><h3>What is a geometric sequence?</h3><p>A <strong>geometric sequence</strong> is a list where each term is found by <strong>multiplying</strong> the previous term by the same number, called the <strong>common ratio</strong>, <em>r</em>.</p><blockquote><p><strong>Example 1</strong></p><p>5, 10, 20, 40, … has r = 10 ÷ 5 = 2.<br>81, 27, 9, 3, … has r = 27 ÷ 81 = 1/3.</p></blockquote><table><thead><tr><th>Arithmetic</th><th>Geometric</th></tr></thead><tbody><tr><td>Add the same number (d)</td><td>Multiply by the same number (r)</td></tr><tr><td>d = a₂ − a₁</td><td>r = a₂ ÷ a₁</td></tr><tr><td>Grows steadily</td><td>Grows or shrinks quickly</td></tr></tbody></table><h3>Finding the nth term</h3><p>Formula: <strong>aₙ = a₁ · rⁿ⁻¹</strong></p><blockquote><p><strong>Example 2</strong></p><p>Find the 8th term of 3, 6, 12, …<br>a₁ = 3, r = 2, n = 8<br>a₈ = 3 · 2⁷ = 3 · 128 = <strong>384</strong></p></blockquote><h3>Geometric series</h3><p>The sum of the first n terms (when r ≠ 1) is: <strong>Sₙ = a₁(1 − rⁿ) / (1 − r)</strong></p><blockquote><p><strong>Example 3</strong></p><p>Find the sum of the first 6 terms of 2, 6, 18, …<br>a₁ = 2, r = 3, n = 6<br>S₆ = 2(1 − 3⁶) / (1 − 3) = 2(1 − 729) / (−2) = 2(−728) / (−2) = <strong>728</strong></p></blockquote><h3>Real-life uses</h3><ul><li>Population growth and the spread of information</li><li>Depreciation, where a gadget loses a fixed percent of its value each year</li><li>Compound interest on savings</li></ul><blockquote><p><strong>Example 4</strong></p><p>A laptop worth ₱40,000 loses 20% of its value each year, so it keeps 80% (r = 0.8). Its value after 3 years is 40,000 · (0.8)³ = 40,000 · 0.512 = <strong>₱20,480</strong>.</p></blockquote><h3>Activity</h3><p>A bacteria culture starts with 50 bacteria and doubles every hour. Make a table of the number of bacteria for hours 0 to 6, then use the formula to find how many there are after 10 hours.</p><h3>Quick check</h3><ol><li>Find r in 64, 16, 4, 1, …</li><li>Find the 6th term of 2, 10, 50, …</li><li>Find the sum of the first 5 terms of 1, 2, 4, …</li></ol><details><summary>Show answers</summary><ol><li>r = 1/4</li><li>a₆ = 2 · 5⁵ = 6,250</li><li>S₅ = (1 − 2⁵)/(1 − 2) = 31</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(141, 39, 3, 'Circles', 'A circle is the set of all points equally distant from a center.', '<p>A circle is the set of all points equally distant from a center.</p><h3>Key points</h3><ul><li>A chord connects two points on a circle; the diameter is the longest chord.</li><li>A central angle equals its intercepted arc.</li><li>An inscribed angle is half its intercepted arc.</li></ul><h3>Try this</h3><p>An inscribed angle intercepts an 80° arc. What is its measure?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(142, 39, 4, 'Permutations and Combinations', 'Counting techniques help us find how many ways things can be arranged or chosen.', '<p>Counting techniques help us find how many ways things can be arranged or chosen.</p><h3>Key points</h3><ul><li>Permutation: order matters; nPr = n! / (n − r)!.</li><li>Combination: order does not matter; nCr = n! / [r!(n − r)!].</li><li>Factorial: n! = n × (n − 1) × ... × 1.</li></ul><h3>Try this</h3><p>How many ways can 3 officers be chosen from 8 students for president, vice president, and secretary?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(143, 39, 5, 'Probability and Statistics', 'Probability measures how likely an event is, and statistics organizes and interprets data.', '<p>Probability measures how likely an event is, and statistics organizes and interprets data.</p><h3>Key points</h3><ul><li>Probability = favorable outcomes / total outcomes.</li><li>Measures of position include quartiles, deciles, and percentiles.</li><li>Data can be summarized using tables and graphs.</li></ul><h3>Try this</h3><p>What is the probability of drawing a red card from a standard deck of 52 cards?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(144, 40, 1, 'Plate Tectonics', 'Earth\'s outer layer is made of moving plates, and how they move shapes the planet.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Describe the layers of the Earth</li><li>Explain what tectonic plates are and why they move</li><li>Identify the three types of plate boundaries</li></ul></blockquote><p>The ground beneath you feels still, but it is moving a few centimeters every year, about as fast as your fingernails grow. This slow movement explains earthquakes, volcanoes, and mountains.</p><h3>Layers of the Earth</h3><table><thead><tr><th>Layer</th><th>Description</th></tr></thead><tbody><tr><td>Crust</td><td>Thin, solid outer layer; continental crust is thicker, oceanic crust is denser</td></tr><tr><td>Mantle</td><td>Thick layer of hot rock; the upper part can flow very slowly</td></tr><tr><td>Outer core</td><td>Liquid iron and nickel; its movement creates Earth\'s magnetic field</td></tr><tr><td>Inner core</td><td>Solid iron and nickel under extreme pressure</td></tr></tbody></table><p>The crust and the uppermost mantle together form the rigid <strong>lithosphere</strong>, which floats on the softer, flowing <strong>asthenosphere</strong>.</p><h3>What are tectonic plates?</h3><p>The lithosphere is broken into large pieces called <strong>tectonic plates</strong>. Major plates include the Pacific, Eurasian, and Indo-Australian plates. The Philippines sits on the <strong>Philippine Sea Plate</strong> and the <strong>Sunda Plate</strong>, near where several plates meet.</p><h3>Why do plates move?</h3><p>Heat from deep inside the Earth causes <strong>convection currents</strong> in the mantle. Hot rock rises, cools, and sinks again, slowly dragging the plates above it, like objects on top of boiling soup.</p><h3>Types of plate boundaries</h3><ol><li><strong>Divergent</strong> – plates move apart. Magma rises and forms new crust.\n<blockquote><p>Example: the Mid-Atlantic Ridge.</p></blockquote></li><li><strong>Convergent</strong> – plates move toward each other. One plate may sink under another (subduction), or both may crumple upward.\n<blockquote><p>Example: the Philippine Trench, where the Philippine Sea Plate sinks beneath the Philippine Mobile Belt.</p></blockquote></li><li><strong>Transform</strong> – plates slide past each other sideways.\n<blockquote><p>Example: the San Andreas Fault in California.</p></blockquote></li></ol><h3>Evidence for moving plates</h3><ul><li>The coastlines of South America and Africa fit together like puzzle pieces.</li><li>Matching fossils are found on continents now separated by oceans.</li><li>Earthquakes and volcanoes cluster along plate edges.</li></ul><h3>Activity</h3><p>Draw a simple diagram of each type of plate boundary using arrows to show plate movement. Under each drawing, write one landform or event that forms there.</p><h3>Quick check</h3><ol><li>What is the lithosphere made of?</li><li>What causes plates to move?</li><li>What type of boundary forms the Philippine Trench?</li></ol><details><summary>Show answers</summary><ol><li>The crust and the uppermost mantle</li><li>Convection currents in the mantle</li><li>Convergent</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(145, 40, 2, 'Earthquakes, Volcanoes, and Mountains', 'What happens where plates meet, and why the Philippines experiences these events.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Explain how plate movement causes earthquakes, volcanoes, and mountains</li><li>Describe the Pacific Ring of Fire</li><li>Explain why the Philippines has many earthquakes and volcanoes</li></ul></blockquote><p>In the last module you learned that plates move. When they push, pull, and grind against each other, energy and molten rock build up. The results are some of the most powerful events on Earth.</p><h3>Earthquakes</h3><p>Rocks along plate boundaries get stuck while the plates keep moving. Stress builds until the rocks suddenly slip along a <strong>fault</strong>, releasing energy as seismic waves.</p><ul><li><strong>Focus</strong> – the point underground where the slip starts.</li><li><strong>Epicenter</strong> – the point on the surface directly above the focus.</li><li><strong>Magnitude</strong> – the energy released, measured by instruments.</li><li><strong>Intensity</strong> – the effects felt by people and buildings at a location.</li></ul><blockquote><p><strong>Example</strong></p><p>The Philippine Fault Zone runs about 1,200 km through the country. Movement along it has caused strong earthquakes, including the 1990 Luzon earthquake.</p></blockquote><h3>Volcanoes</h3><p>At convergent boundaries where one plate sinks under another, the sinking plate melts deep in the mantle. The magma rises through the crust and erupts, forming <strong>volcanoes</strong>.</p><table><thead><tr><th>Volcano</th><th>Notable fact</th></tr></thead><tbody><tr><td>Mayon (Albay)</td><td>Known for its near-perfect cone; one of the most active in the country</td></tr><tr><td>Taal (Batangas)</td><td>A volcano island in the middle of Taal Lake</td></tr><tr><td>Pinatubo (Zambales)</td><td>Its 1991 eruption was one of the largest of the 20th century</td></tr></tbody></table><h3>Mountains</h3><p>When two continental plates collide, neither sinks easily, so the crust crumples and folds upward to form mountain ranges.</p><blockquote><p><strong>Example</strong></p><p>The Himalayas, home of Mount Everest, formed when the Indian Plate collided with the Eurasian Plate, and they are still rising today.</p></blockquote><h3>The Pacific Ring of Fire</h3><p>The <strong>Ring of Fire</strong> is a horseshoe-shaped belt around the Pacific Ocean where many plates meet. Most of the world\'s earthquakes and active volcanoes occur here. The Philippines lies along this belt, which is why we experience frequent earthquakes and have many active volcanoes.</p><h3>Staying safe</h3><ul><li>During an earthquake: Duck, Cover, and Hold.</li><li>Know your community\'s evacuation routes for volcanic eruptions.</li><li>Follow PHIVOLCS for official earthquake and volcano bulletins.</li></ul><h3>Activity</h3><p>Make a table with three columns: earthquake, volcano, and mountain range. For each, write the type of plate boundary where it usually forms and one real example from the Philippines or the world.</p><h3>Quick check</h3><ol><li>What is the difference between the focus and the epicenter?</li><li>How do volcanoes form at convergent boundaries?</li><li>Why does the Philippines have many earthquakes?</li></ol><details><summary>Show answers</summary><ol><li>The focus is underground where the slip starts; the epicenter is on the surface directly above it.</li><li>A sinking plate melts, and the magma rises and erupts.</li><li>It lies on the Pacific Ring of Fire, where plates meet.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(146, 40, 3, 'The Nervous and Endocrine Systems', 'These systems control and coordinate the body\'s activities.', '<p>These systems control and coordinate the body&#x27;s activities.</p><h3>Key points</h3><ul><li>The nervous system sends fast electrical signals through neurons.</li><li>The endocrine system releases hormones into the blood for slower, longer effects.</li><li>Both help the body maintain balance, called homeostasis.</li></ul><h3>Try this</h3><p>Explain what happens in your body when you touch something hot.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(147, 40, 4, 'DNA and Evolution', 'DNA carries genetic instructions, and changes over generations lead to evolution.', '<p>DNA carries genetic instructions, and changes over generations lead to evolution.</p><h3>Key points</h3><ul><li>DNA is a double helix made of nucleotides.</li><li>Mutations are changes in DNA that can create new traits.</li><li>Natural selection favors traits that help organisms survive and reproduce.</li></ul><h3>Try this</h3><p>Give an example of an adaptation that helps an animal survive in its environment.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(148, 40, 5, 'Gas Laws', 'Gas laws describe how pressure, volume, and temperature of gases are related.', '<p>Gas laws describe how pressure, volume, and temperature of gases are related.</p><h3>Key points</h3><ul><li>Boyle&#x27;s Law: at constant temperature, pressure and volume are inversely related.</li><li>Charles&#x27;s Law: at constant pressure, volume and temperature are directly related.</li><li>Temperature must be in Kelvin for gas law calculations.</li></ul><h3>Try this</h3><p>Why does a balloon shrink when placed in a refrigerator?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(149, 53, 1, 'Kontemporaryong Isyu', 'Kung ano ang kontemporaryong isyu at paano ito masusuri nang may pananagutan.', '<blockquote><p><strong>Sa pagtatapos ng modyul na ito, inaasahang kaya mong:</strong></p><ul><li>Ipaliwanag ang kahulugan ng kontemporaryong isyu</li><li>Uriin ang mga isyu ayon sa saklaw nito</li><li>Suriin ang isang isyu gamit ang mapagkakatiwalaang sanggunian</li></ul></blockquote><p>Araw-araw, nakakabasa tayo ng balita tungkol sa pagtaas ng presyo, bagyo, o bagong batas. Ang mga ito ay hindi lang balita; ito ay mga isyung direktang nakaaapekto sa ating buhay.</p><h3>Ano ang kontemporaryong isyu?</h3><p>Ang <strong>kontemporaryong isyu</strong> ay mga pangyayari, suliranin, o paksang napapanahon, pinag-uusapan ng maraming tao, at may epekto sa lipunan. Ang salitang <em>kontemporaryo</em> ay nangangahulugang \"kasalukuyan\" o \"napapanahon.\"</p><h3>Mga katangian ng kontemporaryong isyu</h3><ul><li>Napapanahon at kasalukuyang nangyayari</li><li>May malaking epekto sa maraming tao</li><li>Maaaring may magkakaibang pananaw ang mga tao tungkol dito</li><li>Nangangailangan ng solusyon o pagkilos</li></ul><h3>Uri ng kontemporaryong isyu</h3><table><thead><tr><th>Uri</th><th>Halimbawa</th></tr></thead><tbody><tr><td>Pangkapaligiran</td><td>Climate change, polusyon sa ilog, pagbaha</td></tr><tr><td>Pang-ekonomiya</td><td>Implasyon, kawalan ng trabaho, migrasyon</td></tr><tr><td>Panlipunan at pampulitika</td><td>Karapatang pantao, korapsyon, halalan</td></tr><tr><td>Pangkalusugan</td><td>Pandemya, malnutrisyon</td></tr></tbody></table><h3>Saklaw ng isyu</h3><p>Maaaring <strong>lokal</strong> (sa barangay o lungsod), <strong>pambansa</strong> (sa buong bansa), o <strong>pandaigdig</strong> (sa maraming bansa) ang isang isyu.</p><blockquote><p><strong>Halimbawa</strong></p><p>Ang pagbaha sa isang barangay tuwing tag-ulan ay lokal na isyu. Ang pagtaas ng presyo ng bigas ay pambansang isyu. Ang climate change ay pandaigdigang isyu na nakaaapekto sa lahat ng bansa.</p></blockquote><h3>Paano suriin ang isang isyu</h3><ol><li><strong>Alamin ang mga datos</strong> – ano, sino, kailan, saan, at bakit.</li><li><strong>Suriin ang sanggunian</strong> – galing ba sa mapagkakatiwalaang ahensya o pahayagan?</li><li><strong>Kilalanin ang magkakaibang pananaw</strong> – ano ang panig ng bawat grupo?</li><li><strong>Tukuyin ang sanhi at bunga</strong> – ano ang pinagmulan at epekto nito?</li><li><strong>Bumuo ng sariling paninindigan</strong> – batay sa ebidensya, hindi sa sabi-sabi.</li></ol><h3>Pag-iwas sa maling impormasyon</h3><ul><li>Huwag agad maniwala sa post na walang malinaw na pinagmulan.</li><li>Ihambing ang balita sa dalawa o higit pang mapagkakatiwalaang sanggunian.</li><li>Tingnan ang petsa; baka luma na ang balita.</li></ul><h3>Gawain</h3><p>Pumili ng isang balita ngayong linggo. Isulat ang (1) uri ng isyu, (2) saklaw nito, (3) dalawang sanggunian na nagbalita nito, at (4) ang iyong paninindigan sa 3–5 pangungusap.</p><h3>Pagsusulit</h3><ol><li>Ano ang ibig sabihin ng \"kontemporaryo\"?</li><li>Anong uri ng isyu ang implasyon?</li><li>Magbigay ng isang paraan upang maiwasan ang maling impormasyon.</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Kasalukuyan o napapanahon</li><li>Pang-ekonomiya</li><li>Alinman sa: suriin ang pinagmulan, ihambing sa ibang sanggunian, tingnan ang petsa</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(150, 53, 2, 'Mga Isyung Pangkapaligiran at Paghahanda sa Kalamidad', 'Ang mga suliraning pangkapaligiran ng bansa at kung paano maghanda sa kalamidad.', '<blockquote><p><strong>Sa pagtatapos ng modyul na ito, inaasahang kaya mong:</strong></p><ul><li>Tukuyin ang mga pangunahing suliraning pangkapaligiran ng Pilipinas</li><li>Ipaliwanag ang konsepto ng disaster risk reduction</li><li>Gumawa ng plano ng paghahanda sa kalamidad</li></ul></blockquote><p>Isa ang Pilipinas sa mga bansang pinakaapektado ng kalamidad sa mundo. Taon-taon, humigit-kumulang dalawampung bagyo ang pumapasok sa ating teritoryo, bukod pa sa lindol at pagputok ng bulkan.</p><h3>Mga suliraning pangkapaligiran</h3><ol><li><strong>Climate change</strong> – pagbabago ng klima na nagdudulot ng mas malalakas na bagyo, tagtuyot, at pagtaas ng dagat.</li><li><strong>Solid waste</strong> – basurang bumabara sa kanal at nagdudulot ng pagbaha.</li><li><strong>Pagkasira ng kagubatan</strong> – nagdudulot ng landslide at pagkawala ng tirahan ng mga hayop.</li><li><strong>Polusyon sa tubig at hangin</strong> – nakasasama sa kalusugan at kabuhayan.</li></ol><h3>Hazard, vulnerability, at disaster</h3><table><thead><tr><th>Konsepto</th><th>Kahulugan</th><th>Halimbawa</th></tr></thead><tbody><tr><td>Hazard</td><td>Banta na maaaring magdulot ng pinsala</td><td>Malakas na bagyo</td></tr><tr><td>Vulnerability</td><td>Kahinaan ng komunidad na harapin ang banta</td><td>Bahay na yari sa magagaang materyales malapit sa ilog</td></tr><tr><td>Disaster</td><td>Malawakang pinsala kapag tinamaan ng hazard ang bulnerableng komunidad</td><td>Pagbaha na sumira sa daan-daang bahay</td></tr></tbody></table><blockquote><p><strong>Tandaan</strong></p><p>Hindi natin mapipigilan ang bagyo, ngunit mababawasan natin ang pinsala nito kung babawasan natin ang ating vulnerability. Ito ang layunin ng <strong>Disaster Risk Reduction and Management (DRRM)</strong>.</p></blockquote><h3>Mga yugto ng DRRM</h3><ol><li><strong>Bago ang kalamidad</strong> – paghahanda: go-bag, evacuation plan, pagsunod sa babala ng PAGASA at PHIVOLCS.</li><li><strong>Habang may kalamidad</strong> – pagtugon: lumikas kung kinakailangan at manatiling ligtas.</li><li><strong>Pagkatapos ng kalamidad</strong> – rehabilitasyon: paglilinis, pagsasaayos, at pagtulong sa kapwa.</li></ol><h3>Laman ng go-bag</h3><ul><li>Tubig at pagkaing hindi madaling masira</li><li>Flashlight, extra baterya, at power bank</li><li>Gamot at first aid kit</li><li>Kopya ng mahahalagang dokumento sa plastic na lalagyan</li><li>Damit at kumot</li></ul><h3>Gawain</h3><p>Gumawa ng <strong>family disaster plan</strong>: (1) mga posibleng hazard sa inyong lugar, (2) pinakamalapit na evacuation center, (3) laman ng inyong go-bag, at (4) kung saan magkikita ang pamilya kung magkahiwa-hiwalay.</p><h3>Pagsusulit</h3><ol><li>Ano ang pagkakaiba ng hazard at disaster?</li><li>Anong ahensya ang nagbibigay ng babala tungkol sa bagyo?</li><li>Magbigay ng dalawang laman ng go-bag.</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Ang hazard ay banta; ang disaster ay ang malawakang pinsalang nangyari na.</li><li>PAGASA</li><li>Alinman sa: tubig, pagkain, flashlight, gamot, dokumento, damit</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(151, 53, 3, 'Globalisasyon', 'Ang globalisasyon ay mabilis na pagdaloy ng produkto, tao, kaalaman, at kultura sa buong mundo.', '<p>Ang globalisasyon ay mabilis na pagdaloy ng produkto, tao, kaalaman, at kultura sa buong mundo.</p><h3>Mahahalagang Punto</h3><ul><li>Pinabilis ito ng teknolohiya at internet.</li><li>Nagbibigay ito ng oportunidad sa trabaho at kalakalan.</li><li>Maaari rin itong magdulot ng pagkawala ng lokal na industriya at kultura.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng tatlong produktong ginagamit mo na gawa sa ibang bansa.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(152, 53, 4, 'Isyu sa Paggawa at Migrasyon', 'Maraming Pilipino ang nangingibang-bansa upang maghanap ng mas magandang trabaho.', '<p>Maraming Pilipino ang nangingibang-bansa upang maghanap ng mas magandang trabaho.</p><h3>Mahahalagang Punto</h3><ul><li>Ang OFW ay may malaking ambag sa ekonomiya sa pamamagitan ng remittance.</li><li>May epekto ito sa pamilyang naiiwan.</li><li>Mahalaga ang karapatan ng manggagawa, gaya ng tamang sahod at ligtas na trabaho.</li></ul><h3>Subukan Mo</h3><p>Ano ang mabuti at di-mabuting epekto ng migrasyon sa pamilyang Pilipino?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(153, 53, 5, 'Karapatang Pantao at Aktibong Pagkamamamayan', 'Ang karapatang pantao ay mga karapatang taglay ng bawat tao dahil siya ay tao.', '<p>Ang karapatang pantao ay mga karapatang taglay ng bawat tao dahil siya ay tao.</p><h3>Mahahalagang Punto</h3><ul><li>Nakasaad ito sa Universal Declaration of Human Rights at sa Saligang Batas.</li><li>Ang aktibong mamamayan ay bumoboto, nakikilahok, at nagbabantay sa pamahalaan.</li><li>May kaakibat na tungkulin ang bawat karapatan.</li></ul><h3>Subukan Mo</h3><p>Paano ka makikilahok sa paglutas ng isang problema sa inyong barangay?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(154, 54, 1, 'Isip at Kilos-loob', 'Ang dalawang kakayahang nagpapakilala sa tao: ang pag-unawa at ang malayang pagpili.', '<blockquote><p><strong>Sa pagtatapos ng modyul na ito, inaasahang kaya mong:</strong></p><ul><li>Ipaliwanag ang gamit ng isip at kilos-loob</li><li>Ipakita kung paano nagtutulungan ang dalawa sa pagpapasya</li><li>Suriin ang sariling pasya batay sa tamang gamit ng isip at kilos-loob</li></ul></blockquote><p>Ano ang pinagkaiba ng tao sa hayop? Parehong kumakain, natutulog, at nararamdaman ang sakit. Ngunit ang tao lamang ang kayang mag-isip kung tama o mali ang isang bagay at pumili kung gagawin ito.</p><h3>Ang isip</h3><p>Ang <strong>isip</strong> ay kakayahan ng tao na <strong>umunawa, magsuri, at maghanap ng katotohanan</strong>. Sa pamamagitan nito, natututo tayo, nakapagtatanong, at nakabubuo ng konklusyon.</p><ul><li>Layunin ng isip: ang <strong>katotohanan</strong></li><li>Gawain: umunawa, maghusga, mangatwiran</li></ul><h3>Ang kilos-loob</h3><p>Ang <strong>kilos-loob</strong> ay kakayahan ng tao na <strong>pumili at kumilos</strong> ayon sa nauunawaan ng isip. Ito ang nagpapasya kung gagawin o hindi ang isang bagay.</p><ul><li>Layunin ng kilos-loob: ang <strong>kabutihan</strong></li><li>Gawain: pumili, magpasya, kumilos</li></ul><table><thead><tr><th></th><th>Isip</th><th>Kilos-loob</th></tr></thead><tbody><tr><td>Kakayahan</td><td>Umunawa</td><td>Pumili</td></tr><tr><td>Hinahanap</td><td>Katotohanan</td><td>Kabutihan</td></tr><tr><td>Tanong na sinasagot</td><td>\"Ano ang tama?\"</td><td>\"Gagawin ko ba ito?\"</td></tr></tbody></table><h3>Paano sila nagtutulungan</h3><p>Inaalam ng isip kung ano ang tama; pinipili naman ng kilos-loob kung susundin ito. Hindi sapat na alam mo ang tama; kailangan mo ring piliin ito.</p><blockquote><p><strong>Halimbawa</strong></p><p>Napansin ni Ana na sobra ang sukling ibinigay sa kanya ng tindera. Alam ng kanyang isip na hindi kanya ang sobrang pera (katotohanan). Pinili ng kanyang kilos-loob na ibalik ito (kabutihan). Dito, ginamit niya nang tama ang dalawa.</p></blockquote><h3>Paglinang ng isip at kilos-loob</h3><ul><li><strong>Para sa isip:</strong> magbasa, magtanong, makinig sa iba\'t ibang pananaw, at suriin ang impormasyon bago maniwala.</li><li><strong>Para sa kilos-loob:</strong> sanayin ang sarili sa maliliit na mabuting pasya araw-araw, gaya ng pagtupad sa pangako at pag-iwas sa tukso.</li></ul><h3>Gawain</h3><p>Isalaysay ang isang mahirap na pasya na ginawa mo kamakailan. Isulat (1) ano ang naunawaan ng iyong isip tungkol sa sitwasyon, (2) ano ang pinili ng iyong kilos-loob, at (3) kung gagawin mo ba itong muli at bakit.</p><h3>Pagsusulit</h3><ol><li>Ano ang layunin ng isip?</li><li>Ano ang layunin ng kilos-loob?</li><li>Bakit hindi sapat na alam lang ang tama?</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Katotohanan</li><li>Kabutihan</li><li>Dahil kailangan pa ring piliin at gawin ng kilos-loob ang tama.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(155, 54, 2, 'Konsensya at ang Likas na Batas Moral', 'Ang panloob na gabay sa tama at mali, at ang batas moral na likas sa lahat ng tao.', '<blockquote><p><strong>Sa pagtatapos ng modyul na ito, inaasahang kaya mong:</strong></p><ul><li>Ipaliwanag ang konsensya at ang likas na batas moral</li><li>Tukuyin ang mga uri ng konsensya</li><li>Gamitin ang mga hakbang sa paghubog ng tamang konsensya</li></ul></blockquote><p>Naranasan mo na bang makaramdam ng bigat ng loob pagkatapos magsinungaling? Iyan ang tinig ng iyong <strong>konsensya</strong>.</p><h3>Ano ang konsensya?</h3><p>Ang konsensya ay ang <strong>paghuhusga ng isip</strong> kung mabuti o masama ang isang kilos sa isang tiyak na sitwasyon. Ito ay gumagabay bago tayo kumilos at humuhusga pagkatapos nating kumilos.</p><h3>Ang likas na batas moral</h3><p>Ang <strong>likas na batas moral</strong> ay ang pangkalahatang batas na nakaukit sa puso ng bawat tao: <strong>\"Gawin ang mabuti, iwasan ang masama.\"</strong> Hindi ito isinulat ng tao; likas itong alam ng lahat, anuman ang lahi, relihiyon, o panahon.</p><ul><li><strong>Pangkalahatan</strong> – para sa lahat ng tao</li><li><strong>Hindi nagbabago</strong> – pareho noon at ngayon</li><li><strong>Walang hanggan</strong> – hindi nawawala</li></ul><blockquote><p><strong>Halimbawa</strong></p><p>Sa lahat ng kultura, itinuturing na masama ang pagpatay sa inosente at mabuti ang pagtulong sa nangangailangan. Patunay ito na may likas na batas moral na alam ng lahat.</p></blockquote><h3>Mga uri ng konsensya</h3><table><thead><tr><th>Uri</th><th>Paliwanag</th></tr></thead><tbody><tr><td>Tama</td><td>Wastong nakahuhusga ng mabuti at masama</td></tr><tr><td>Mali</td><td>Nagkakamali sa paghuhusga, madalas dahil sa kakulangan ng kaalaman</td></tr><tr><td>Tiyak</td><td>Sigurado sa kanyang paghuhusga</td></tr><tr><td>Nag-aalinlangan</td><td>Hindi sigurado; dapat munang alamin bago kumilos</td></tr></tbody></table><h3>Paghubog ng tamang konsensya</h3><ol><li><strong>Alamin at naisin ang mabuti</strong> – magkaroon ng tamang kaalaman tungkol sa moral na isyu.</li><li><strong>Suriin ang sitwasyon</strong> – tingnan ang lahat ng detalye at posibleng bunga.</li><li><strong>Humingi ng payo</strong> – sa magulang, guro, o taong may malawak na karanasan.</li><li><strong>Magnilay at magpasya</strong> – pumili ng kilos na tugma sa likas na batas moral.</li></ol><blockquote><p><strong>Tandaan</strong></p><p>Kapag nag-aalinlangan ang konsensya, huwag munang kumilos hangga\'t hindi malinaw kung tama ang gagawin.</p></blockquote><h3>Gawain</h3><p>Basahin ang sitwasyon: <em>Nakita mong kinokopya ng iyong matalik na kaibigan ang sagot ng iba sa pagsusulit.</em> Gamit ang apat na hakbang sa paghubog ng konsensya, isulat kung ano ang gagawin mo at bakit.</p><h3>Pagsusulit</h3><ol><li>Ano ang pangunahing prinsipyo ng likas na batas moral?</li><li>Ano ang dapat gawin kapag nag-aalinlangan ang konsensya?</li><li>Magbigay ng isang katangian ng likas na batas moral.</li></ol><details><summary>Ipakita ang sagot</summary><ol><li>Gawin ang mabuti, iwasan ang masama.</li><li>Huwag munang kumilos; alamin muna ang tama.</li><li>Alinman sa: pangkalahatan, hindi nagbabago, walang hanggan</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(156, 54, 3, 'Kalayaan', 'Ang tunay na kalayaan ay ang kakayahang piliin ang mabuti, hindi ang gawin ang lahat ng gusto.', '<p>Ang tunay na kalayaan ay ang kakayahang piliin ang mabuti, hindi ang gawin ang lahat ng gusto.</p><h3>Mahahalagang Punto</h3><ul><li>Ang kalayaan ay may kasamang pananagutan.</li><li>Ang maling paggamit ng kalayaan ay nakasasama sa sarili at sa iba.</li><li>Ang taong malaya ay hindi alipin ng masamang bisyo.</li></ul><h3>Subukan Mo</h3><p>Paano mo ginagamit ang iyong kalayaan sa social media nang may pananagutan?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(157, 54, 4, 'Dignidad ng Tao', 'Ang bawat tao ay may dignidad na hindi nawawala anuman ang kanyang kalagayan.', '<p>Ang bawat tao ay may dignidad na hindi nawawala anuman ang kanyang kalagayan.</p><h3>Mahahalagang Punto</h3><ul><li>Hindi nakabatay ang dignidad sa yaman, itsura, o katayuan.</li><li>Dapat igalang ang dignidad ng lahat, kasama ang may kapansanan at mahihirap.</li><li>Ang pambu-bully ay paglabag sa dignidad ng tao.</li></ul><h3>Subukan Mo</h3><p>Ano ang gagawin mo kung may nakita kang binu-bully sa paaralan?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(158, 54, 5, 'Mga Isyung Moral', 'Maraming isyung moral sa lipunan na nangangailangan ng malalim na pagninilay.', '<p>Maraming isyung moral sa lipunan na nangangailangan ng malalim na pagninilay.</p><h3>Mahahalagang Punto</h3><ul><li>Kabilang dito ang isyu sa buhay, sekswalidad, katapatan, at paggamit ng droga.</li><li>Suriin ang bawat isyu gamit ang katwiran at konsensya.</li><li>Igalang ang ibang pananaw habang naninindigan sa tama.</li></ul><h3>Subukan Mo</h3><p>Pumili ng isang isyung moral at isulat ang iyong paninindigan at dahilan.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(159, 55, 1, 'Music and Arts of the 20th Century', 'How music and art broke old rules and found new forms in the modern era.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Describe major styles of 20th-century music and art</li><li>Identify Filipino artists who shaped modern Philippine music and art</li><li>Compare traditional and modern styles</li></ul></blockquote><p>The 20th century brought world wars, new technology, and fast social change. Musicians and artists responded by experimenting with sounds, colors, and shapes that earlier generations had never tried.</p><h3>Part 1: Music</h3><table><thead><tr><th>Style</th><th>Main features</th><th>Composer</th></tr></thead><tbody><tr><td>Impressionism</td><td>Soft, dreamy sounds that suggest moods and images</td><td>Claude Debussy</td></tr><tr><td>Expressionism</td><td>Intense emotion, harsh harmonies, no clear key</td><td>Arnold Schoenberg</td></tr><tr><td>Neoclassicism</td><td>Modern sounds in older, balanced forms</td><td>Igor Stravinsky</td></tr><tr><td>Electronic music</td><td>Sounds made or changed with machines and recordings</td><td>Karlheinz Stockhausen</td></tr></tbody></table><h4>Filipino composers</h4><ul><li><strong>Lucio San Pedro</strong> – composed \"Sa Ugoy ng Duyan\"; National Artist for Music.</li><li><strong>Antonio Molina</strong> – known for \"Hatinggabi,\" blending Filipino melody with impressionist style.</li><li><strong>Ryan Cayabyab</strong> – modern composer of choral works, musicals, and popular songs.</li></ul><h3>Part 2: Arts</h3><table><thead><tr><th>Movement</th><th>Main features</th><th>Artist</th></tr></thead><tbody><tr><td>Cubism</td><td>Objects broken into geometric shapes, shown from many angles</td><td>Pablo Picasso</td></tr><tr><td>Surrealism</td><td>Dreamlike, impossible scenes</td><td>Salvador Dalí</td></tr><tr><td>Pop Art</td><td>Images from ads, comics, and everyday products</td><td>Andy Warhol</td></tr></tbody></table><h4>Filipino modern artists</h4><ul><li><strong>Vicente Manansala</strong> – developed \"transparent cubism,\" as in his paintings of jeepneys and vendors.</li><li><strong>Victorio Edades</strong> – called the father of modern Philippine painting.</li><li><strong>Fernando Amorsolo</strong> – a contrast to the modernists; famous for sunlit rural scenes.</li></ul><blockquote><p><strong>Compare</strong></p><p>Amorsolo\'s paintings show the Philippine countryside realistically, with glowing light. Manansala\'s cubist works break the same everyday subjects into overlapping shapes. Both are Filipino, but one is traditional and the other modern.</p></blockquote><h3>Activity</h3><p>Listen to \"Sa Ugoy ng Duyan\" and to a piece by Debussy. In a short paragraph, describe the mood of each and one difference in how they sound. Then sketch a simple object from your home in a cubist style.</p><h3>Quick check</h3><ol><li>Which music style creates soft, dreamy moods?</li><li>Who is called the father of modern Philippine painting?</li><li>What art movement uses images from ads and comics?</li></ol><details><summary>Show answers</summary><ol><li>Impressionism</li><li>Victorio Edades</li><li>Pop Art</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(160, 55, 2, 'Active Recreation and Consumer Health', 'Staying active through recreation, and making wise choices about health products and services.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Plan safe active recreational activities</li><li>Explain the benefits of active recreation</li><li>Evaluate health products and services as a smart consumer</li></ul></blockquote><h3>Part 1: Physical Education – Active Recreation</h3><p><strong>Active recreation</strong> is any physical activity done during free time for enjoyment, such as hiking, biking, swimming, dancing, or playing sports with friends.</p><h4>Benefits</h4><ul><li><strong>Physical:</strong> improves heart health, strength, and flexibility.</li><li><strong>Mental:</strong> reduces stress and improves mood.</li><li><strong>Social:</strong> builds friendships and teamwork.</li></ul><h4>Planning a safe activity</h4><ol><li>Choose an activity that fits your fitness level.</li><li>Check the weather and the place beforehand.</li><li>Bring water, proper clothing, and a first aid kit.</li><li>Warm up before and cool down after.</li><li>Never go alone; tell someone where you are going.</li><li>Follow \"Leave No Trace\": bring your trash home.</li></ol><blockquote><p><strong>Example</strong></p><p>For a morning hike, a group starts at 6 a.m. to avoid the midday heat, brings two liters of water each, wears closed shoes, and informs the barangay or park office of their route.</p></blockquote><h3>Part 2: Health – Consumer Health</h3><p><strong>Consumer health</strong> is about choosing health products, services, and information wisely. A smart consumer protects both their health and their money.</p><h4>Reading a product label</h4><table><thead><tr><th>Check</th><th>Why it matters</th></tr></thead><tbody><tr><td>Expiration date</td><td>Expired products may be unsafe</td></tr><tr><td>Ingredients</td><td>Avoid allergens and harmful substances</td></tr><tr><td>Directions and dosage</td><td>Using too much can be dangerous</td></tr><tr><td>FDA registration</td><td>Shows the product passed safety standards in the Philippines</td></tr></tbody></table><h4>Spotting false claims</h4><ul><li>Promises like \"cures all diseases\" or \"lose 10 kg in a week\"</li><li>Testimonials only, with no scientific evidence</li><li>Pressure to \"buy now\" before a deadline</li></ul><blockquote><p><strong>Remember</strong></p><p>Food supplements are not medicine. Products labeled \"No approved therapeutic claims\" cannot claim to cure or treat illness.</p></blockquote><h3>Activity</h3><p>Part A: Plan a one-day active recreation outing for your family, listing the activity, schedule, things to bring, and safety rules. Part B: Examine one health product at home and record its expiration date, main ingredients, dosage, and whether it has an FDA registration number.</p><h3>Quick check</h3><ol><li>Give one mental benefit of active recreation.</li><li>Why should you check the FDA registration of a product?</li><li>Give one sign of a false health claim.</li></ol><details><summary>Show answers</summary><ol><li>Any one: reduces stress, improves mood.</li><li>It shows the product passed safety standards.</li><li>Any one: promises a cure for everything, unrealistic results, testimonials only, pressure to buy now.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(161, 55, 3, 'PE: Active Recreation', 'Active recreation includes outdoor and leisure activities that keep the body fit.', '<p>Active recreation includes outdoor and leisure activities that keep the body fit.</p><h3>Key points</h3><ul><li>Examples are hiking, swimming, biking, and camping.</li><li>Plan activities with safety and the environment in mind.</li><li>Recreation reduces stress and builds friendships.</li></ul><h3>Try this</h3><p>Plan a safe weekend recreational activity for your family.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(162, 55, 4, 'Health: Consumer Health', 'Consumer health is about making wise choices when buying health products and services.', '<p>Consumer health is about making wise choices when buying health products and services.</p><h3>Key points</h3><ul><li>Check product labels and expiration dates.</li><li>Be careful of false advertising and quack remedies.</li><li>Know your rights as a consumer.</li></ul><h3>Try this</h3><p>Examine a medicine label and list the important information found on it.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(163, 55, 5, 'Health: Global Health Issues', 'Global health issues affect people across many countries.', '<p>Global health issues affect people across many countries.</p><h3>Key points</h3><ul><li>Examples include pandemics, malnutrition, and climate-related diseases.</li><li>International groups like the World Health Organization coordinate responses.</li><li>Each person can help by practicing healthy habits and sharing correct information.</li></ul><h3>Try this</h3><p>Research a global health issue and present three ways to address it.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(164, 56, 1, 'Planning a Small Business', 'Turning a product idea into a simple, workable business plan.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Identify a business opportunity in your community</li><li>Compute the cost and selling price of a product</li><li>Prepare a simple business plan</li></ul></blockquote><p>Many big businesses started small: a home kitchen, a single sewing machine, or a repair table in a garage. The difference between an idea and a business is a <strong>plan</strong>.</p><h3>Finding an opportunity</h3><p>A good business answers a <strong>need</strong> or <strong>want</strong> in the community.</p><ul><li>What do people in your area often look for but cannot easily buy?</li><li>What skill from your TLE specialization can you turn into a product or service?</li><li>Who would buy it, and how often?</li></ul><blockquote><p><strong>Example</strong></p><p>A student taking Cookery notices that office workers near the school have few healthy snack options. She plans to sell vegetable lumpia packs during lunch breaks.</p></blockquote><h3>Computing cost and price</h3><p><strong>Total cost</strong> = materials + labor + overhead (electricity, gas, packaging)<br><strong>Cost per unit</strong> = total cost ÷ number of units<br><strong>Selling price</strong> = cost per unit + markup</p><table><thead><tr><th>Item (for 20 packs)</th><th>Cost</th></tr></thead><tbody><tr><td>Ingredients</td><td>₱400</td></tr><tr><td>Packaging</td><td>₱60</td></tr><tr><td>Gas and utilities</td><td>₱40</td></tr><tr><td><strong>Total cost</strong></td><td><strong>₱500</strong></td></tr></tbody></table><p>Cost per pack = ₱500 ÷ 20 = ₱25. With a 40% markup: ₱25 + (0.40 × ₱25) = <strong>₱35 per pack</strong>.<br>Profit for 20 packs = (₱35 × 20) − ₱500 = <strong>₱200</strong>.</p><h3>Parts of a simple business plan</h3><ol><li><strong>Business name and description</strong> – what you sell and why.</li><li><strong>Target market</strong> – who will buy.</li><li><strong>Competitors</strong> – who else sells something similar, and how you are different.</li><li><strong>Costing and pricing</strong> – your computations.</li><li><strong>Operations</strong> – where, when, and how you will produce and sell.</li><li><strong>Goals</strong> – what you want to achieve in the first month.</li></ol><h3>Activity</h3><p>Write a one-page business plan for a product or service related to your TLE specialization. Include all six parts and a costing table with the selling price per unit.</p><h3>Quick check</h3><ol><li>What three costs make up total cost?</li><li>If total cost is ₱600 for 30 units, what is the cost per unit?</li><li>Why should a business plan include competitors?</li></ol><details><summary>Show answers</summary><ol><li>Materials, labor, and overhead</li><li>₱20</li><li>To know how your product is different and price it competitively.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02');
INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `description`, `content`, `file_path`, `week_number`, `published`, `updated_by`, `created_at`, `updated_at`) VALUES
(165, 56, 2, 'Marketing Your Product', 'Using the 4Ps and simple promotion to reach customers.', '<blockquote><p><strong>By the end of this module, you should be able to:</strong></p><ul><li>Apply the 4Ps of marketing to a product</li><li>Identify a target market</li><li>Create a simple promotion for a small business</li></ul></blockquote><p>A great product will not sell if nobody knows about it. <strong>Marketing</strong> connects your product to the people who need it.</p><h3>The 4Ps of marketing</h3><table><thead><tr><th>P</th><th>Question it answers</th><th>Example (vegetable lumpia packs)</th></tr></thead><tbody><tr><td>Product</td><td>What are you selling, and what makes it special?</td><td>Fresh, healthy lumpia with vinegar dip</td></tr><tr><td>Price</td><td>How much will customers pay?</td><td>₱35 per pack, ₱100 for 3</td></tr><tr><td>Place</td><td>Where will customers buy it?</td><td>Near the office building gate, 11 a.m. to 1 p.m.</td></tr><tr><td>Promotion</td><td>How will people find out about it?</td><td>Flyers, Facebook page, free taste on opening day</td></tr></tbody></table><h3>Knowing your target market</h3><p>Your <strong>target market</strong> is the specific group most likely to buy. Describe them by:</p><ul><li><strong>Age and occupation</strong> – e.g., office workers aged 25–45</li><li><strong>Location</strong> – e.g., working near the school area</li><li><strong>Needs</strong> – e.g., quick, affordable, healthy lunch</li></ul><h3>Low-cost promotion ideas</h3><ul><li>A Facebook or Instagram page with clear photos and prices</li><li>Simple flyers with your product, price, and contact number</li><li>Bundle deals and loyalty cards (buy 5, get 1 free)</li><li>Asking satisfied customers to recommend you</li></ul><blockquote><p><strong>Example of a good social media post</strong></p><p>\"Healthy na, busog pa! 🥬 Fresh vegetable lumpia, ₱35 per pack or 3 for ₱100. Available Mon–Fri, 11 AM–1 PM at the school gate. Message us to reserve!\"<br>It states the benefit, product, price, place, time, and how to order.</p></blockquote><h3>Packaging matters</h3><ul><li>Keeps the product clean and safe</li><li>Shows your brand name and contact details</li><li>Makes the product attractive and easy to carry</li></ul><h3>Activity</h3><p>Using the product from your business plan, fill in a 4Ps table, describe your target market in three sentences, and write one social media post that includes the benefit, price, place, time, and how to order.</p><h3>Quick check</h3><ol><li>What are the 4Ps of marketing?</li><li>What is a target market?</li><li>Give two low-cost ways to promote a small business.</li></ol><details><summary>Show answers</summary><ol><li>Product, Price, Place, Promotion</li><li>The specific group of people most likely to buy the product.</li><li>Any two: social media page, flyers, bundle deals, loyalty cards, word of mouth.</li></ol></details>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', '2026-10-05 00:21:02'),
(166, 56, 3, 'Planning a Small Business', 'A business plan describes how a business will operate and earn money.', '<p>A business plan describes how a business will operate and earn money.</p><h3>Key points</h3><ul><li>Include the product, target market, costs, and pricing.</li><li>Study competitors in your area.</li><li>Set clear goals and a timeline.</li></ul><h3>Try this</h3><p>Write a one-page business plan for a product related to your specialization.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(167, 56, 4, 'Marketing Your Product', 'Marketing helps customers learn about and buy your product.', '<p>Marketing helps customers learn about and buy your product.</p><h3>Key points</h3><ul><li>The 4Ps of marketing are Product, Price, Place, and Promotion.</li><li>Social media is a low-cost way to promote small businesses.</li><li>Good packaging attracts customers.</li></ul><h3>Try this</h3><p>Design a social media post promoting your product.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(168, 56, 5, 'Preparing for Assessment and Certification', 'TESDA assessments test whether you meet the standards of a qualification.', '<p>TESDA assessments test whether you meet the standards of a qualification.</p><h3>Key points</h3><ul><li>Review the competencies required for your National Certificate.</li><li>Practice the tasks under timed conditions.</li><li>Passing gives you a credential useful for work.</li></ul><h3>Try this</h3><p>Prepare a review plan for a TESDA assessment in your specialization.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(169, 4, 1, 'Functions and Their Graphs', 'A function is a relation in which each input has exactly one output.', '<p>A function is a relation in which each input has exactly one output.</p><h3>Key points</h3><ul><li>Functions can be shown as equations, tables, graphs, or mapping diagrams.</li><li>The vertical line test checks whether a graph is a function.</li><li>Function notation f(x) means &quot;the value of f at x.&quot;</li></ul><h3>Try this</h3><p>Evaluate f(x) = 2x² − 3x + 1 when x = 4.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(170, 4, 2, 'Rational Functions', 'A rational function is a ratio of two polynomial functions.', '<p>A rational function is a ratio of two polynomial functions.</p><h3>Key points</h3><ul><li>Vertical asymptotes occur where the denominator equals zero.</li><li>Horizontal asymptotes describe the graph&#x27;s behavior for very large x.</li><li>Rational functions model rates, averages, and work problems.</li></ul><h3>Try this</h3><p>Find the vertical asymptote of f(x) = (x + 1) / (x − 3).</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(171, 4, 3, 'Exponential and Logarithmic Functions', 'Exponential functions model rapid growth or decay, and logarithms are their inverses.', '<p>Exponential functions model rapid growth or decay, and logarithms are their inverses.</p><h3>Key points</h3><ul><li>Exponential form: f(x) = a · bˣ.</li><li>Logarithmic form: log_b(y) = x means bˣ = y.</li><li>These functions model population growth, interest, and radioactive decay.</li></ul><h3>Try this</h3><p>Solve 2ˣ = 32 and rewrite it in logarithmic form.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(172, 4, 4, 'Simple and Compound Interest', 'Interest is the cost of borrowing money or the earnings from saving it.', '<p>Interest is the cost of borrowing money or the earnings from saving it.</p><h3>Key points</h3><ul><li>Simple interest: I = Prt.</li><li>Compound interest earns interest on both the principal and past interest.</li><li>Compound amount: A = P(1 + r/n)ⁿᵗ.</li></ul><h3>Try this</h3><p>How much is ₱10,000 worth after 3 years at 5% interest compounded annually?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:14', NULL),
(173, 4, 5, 'Logic', 'Logic is the study of correct reasoning using statements called propositions.', '<p>Logic is the study of correct reasoning using statements called propositions.</p><h3>Key points</h3><ul><li>A proposition is a statement that is either true or false.</li><li>Logical connectives include and, or, not, and if-then.</li><li>Truth tables show all possible truth values of a compound proposition.</li></ul><h3>Try this</h3><p>Build a truth table for &quot;p and q.&quot;</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(174, 5, 1, 'Random Variables', 'A random variable assigns a number to each outcome of a random experiment.', '<p>A random variable assigns a number to each outcome of a random experiment.</p><h3>Key points</h3><ul><li>Discrete random variables have countable values, like the number of heads.</li><li>Continuous random variables can take any value in a range, like height.</li><li>A probability distribution lists each value and its probability.</li></ul><h3>Try this</h3><p>Make a probability distribution for the number of heads when tossing two coins.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(175, 5, 2, 'The Normal Distribution', 'The normal distribution is a bell-shaped curve that describes many natural measurements.', '<p>The normal distribution is a bell-shaped curve that describes many natural measurements.</p><h3>Key points</h3><ul><li>It is symmetric around the mean.</li><li>About 68% of data falls within one standard deviation of the mean.</li><li>The z-score shows how many standard deviations a value is from the mean.</li></ul><h3>Try this</h3><p>A test has mean 75 and standard deviation 5. Find the z-score of a score of 85.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(176, 5, 3, 'Sampling and Sampling Distributions', 'A sample is a smaller group chosen to represent a larger population.', '<p>A sample is a smaller group chosen to represent a larger population.</p><h3>Key points</h3><ul><li>Random sampling gives every member an equal chance to be chosen.</li><li>Types include simple random, stratified, systematic, and cluster sampling.</li><li>Larger samples usually give more reliable results.</li></ul><h3>Try this</h3><p>Describe how you would choose a random sample of 30 students from your school.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(177, 5, 4, 'Estimation of Parameters', 'Estimation uses sample data to make educated guesses about a population.', '<p>Estimation uses sample data to make educated guesses about a population.</p><h3>Key points</h3><ul><li>A point estimate is a single value, like the sample mean.</li><li>A confidence interval gives a range likely to contain the true value.</li><li>Higher confidence levels give wider intervals.</li></ul><h3>Try this</h3><p>Explain what a 95% confidence interval means in your own words.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(178, 5, 5, 'Hypothesis Testing', 'Hypothesis testing uses data to decide whether to reject a claim about a population.', '<p>Hypothesis testing uses data to decide whether to reject a claim about a population.</p><h3>Key points</h3><ul><li>The null hypothesis states no effect or no difference.</li><li>The alternative hypothesis states what we want to prove.</li><li>If the p-value is less than the significance level, we reject the null hypothesis.</li></ul><h3>Try this</h3><p>Write a null and alternative hypothesis for testing whether a new study method improves scores.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(179, 6, 1, 'The Earth as a Planet', 'Earth is the only known planet with conditions that support life.', '<p>Earth is the only known planet with conditions that support life.</p><h3>Key points</h3><ul><li>Earth is the right distance from the Sun to have liquid water.</li><li>Its atmosphere protects life and regulates temperature.</li><li>Earth has four subsystems: geosphere, hydrosphere, atmosphere, and biosphere.</li></ul><h3>Try this</h3><p>Explain how two of Earth&#x27;s subsystems interact.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(180, 6, 2, 'Minerals and Rocks', 'Rocks are made of minerals and are classified by how they form.', '<p>Rocks are made of minerals and are classified by how they form.</p><h3>Key points</h3><ul><li>Igneous rocks form from cooled magma or lava.</li><li>Sedimentary rocks form from compressed sediments.</li><li>Metamorphic rocks form when heat and pressure change existing rocks.</li></ul><h3>Try this</h3><p>Describe the rock cycle using a simple diagram.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(181, 6, 3, 'Geologic Processes and Hazards', 'Processes inside and on the surface of the Earth shape the land and can cause hazards.', '<p>Processes inside and on the surface of the Earth shape the land and can cause hazards.</p><h3>Key points</h3><ul><li>Weathering and erosion break down and move rocks.</li><li>Earthquakes, landslides, and volcanic eruptions are geologic hazards.</li><li>Hazard maps help communities prepare.</li></ul><h3>Try this</h3><p>Identify the geologic hazards that affect your municipality.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(182, 6, 4, 'Cells and Life Processes', 'All living things are made of cells that carry out life processes.', '<p>All living things are made of cells that carry out life processes.</p><h3>Key points</h3><ul><li>Cells come from pre-existing cells.</li><li>Living things grow, reproduce, respond, and maintain homeostasis.</li><li>Photosynthesis and respiration are key energy processes.</li></ul><h3>Try this</h3><p>Compare photosynthesis and cellular respiration.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(183, 6, 5, 'Evolution and Ecology', 'Life on Earth changes over time, and organisms interact with their environment.', '<p>Life on Earth changes over time, and organisms interact with their environment.</p><h3>Key points</h3><ul><li>Natural selection drives evolution.</li><li>Ecosystems depend on interactions among organisms.</li><li>Human activities can disrupt ecosystems.</li></ul><h3>Try this</h3><p>Give an example of how humans have changed a local ecosystem.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(184, 7, 1, 'Formation of Elements', 'Elements were formed in the Big Bang and inside stars.', '<p>Elements were formed in the Big Bang and inside stars.</p><h3>Key points</h3><ul><li>Hydrogen and helium formed shortly after the Big Bang.</li><li>Heavier elements formed through nuclear fusion in stars.</li><li>The heaviest elements formed in supernova explosions.</li></ul><h3>Try this</h3><p>Explain why we are said to be made of &quot;star stuff.&quot;</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(185, 7, 2, 'Properties of Matter', 'The properties of a substance depend on its structure and bonding.', '<p>The properties of a substance depend on its structure and bonding.</p><h3>Key points</h3><ul><li>Polarity affects how substances mix, like oil and water.</li><li>Intermolecular forces affect boiling and melting points.</li><li>Structure explains why diamonds are hard and graphite is soft.</li></ul><h3>Try this</h3><p>Explain why oil does not mix with water.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(186, 7, 3, 'Chemical Reactions', 'A chemical reaction changes substances into new substances.', '<p>A chemical reaction changes substances into new substances.</p><h3>Key points</h3><ul><li>Reactions follow the law of conservation of mass.</li><li>Reaction rate depends on temperature, concentration, surface area, and catalysts.</li><li>Combustion, neutralization, and decomposition are common types.</li></ul><h3>Try this</h3><p>Why does food spoil faster at room temperature than in the refrigerator?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(187, 7, 4, 'Motion and Forces', 'Physics explains how and why objects move.', '<p>Physics explains how and why objects move.</p><h3>Key points</h3><ul><li>Aristotle believed heavy objects fall faster; Galileo showed all objects fall at the same rate without air.</li><li>Newton&#x27;s laws explain motion using force, mass, and acceleration.</li><li>Momentum = mass × velocity.</li></ul><h3>Try this</h3><p>Explain why passengers lurch forward when a jeepney suddenly stops.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(188, 7, 5, 'Light and Electromagnetism', 'Light behaves as both a wave and a particle.', '<p>Light behaves as both a wave and a particle.</p><h3>Key points</h3><ul><li>Reflection, refraction, and dispersion explain rainbows and mirages.</li><li>Electricity and magnetism are connected and produce electromagnetic waves.</li><li>Einstein&#x27;s work changed how we understand light and energy.</li></ul><h3>Try this</h3><p>Explain how a rainbow forms.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(189, 1, 1, 'Nature and Process of Communication', 'Communication is the process of sharing meaning through verbal and nonverbal messages.', '<p>Communication is the process of sharing meaning through verbal and nonverbal messages.</p><h3>Key points</h3><ul><li>Elements: speaker, message, channel, receiver, feedback, and context.</li><li>Communication can be verbal or nonverbal.</li><li>Noise or barriers can block understanding.</li></ul><h3>Try this</h3><p>Draw a communication model and label its parts.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(190, 1, 2, 'Communication Breakdown', 'A communication breakdown happens when the message is not understood as intended.', '<p>A communication breakdown happens when the message is not understood as intended.</p><h3>Key points</h3><ul><li>Barriers can be physical, psychological, semantic, or cultural.</li><li>Clear language and active listening reduce misunderstanding.</li><li>Feedback helps confirm the message was understood.</li></ul><h3>Try this</h3><p>Describe a misunderstanding you experienced and how it could have been avoided.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(191, 1, 3, 'Intercultural Communication', 'Intercultural communication is communication between people from different cultures.', '<p>Intercultural communication is communication between people from different cultures.</p><h3>Key points</h3><ul><li>Respect cultural differences in language, gestures, and values.</li><li>Avoid stereotypes and generalizations.</li><li>Be open-minded and willing to learn.</li></ul><h3>Try this</h3><p>Give an example of a gesture that means different things in different cultures.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(192, 1, 4, 'Speech Acts and Communicative Strategies', 'Speech acts are what we do with words, like requesting, promising, or apologizing.', '<p>Speech acts are what we do with words, like requesting, promising, or apologizing.</p><h3>Key points</h3><ul><li>Locutionary act: the actual words said.</li><li>Illocutionary act: the intention behind the words.</li><li>Perlocutionary act: the effect on the listener.</li></ul><h3>Try this</h3><p>Identify the intention behind &quot;It&#x27;s cold in here.&quot;</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(193, 1, 5, 'Types of Speeches and Delivery', 'Speeches can be classified by purpose and by delivery.', '<p>Speeches can be classified by purpose and by delivery.</p><h3>Key points</h3><ul><li>Purposes: informative, persuasive, and entertainment.</li><li>Delivery types: reading from a manuscript, memorized, impromptu, and extemporaneous.</li><li>Good delivery uses clear voice, eye contact, and natural gestures.</li></ul><h3>Try this</h3><p>Deliver a one-minute impromptu speech on your favorite hobby.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(194, 2, 1, 'Reading and Thinking Strategies', 'Good readers use strategies to understand and remember what they read.', '<p>Good readers use strategies to understand and remember what they read.</p><h3>Key points</h3><ul><li>Preview the text by reading titles, headings, and summaries.</li><li>Ask questions and make predictions while reading.</li><li>Summarize the main ideas after reading.</li></ul><h3>Try this</h3><p>Use the SQ3R method on a chapter of any textbook.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(195, 2, 2, 'Patterns of Development', 'Writers organize ideas using patterns of development.', '<p>Writers organize ideas using patterns of development.</p><h3>Key points</h3><ul><li>Narration tells a story; description paints a picture with words.</li><li>Definition explains meaning; classification groups ideas into categories.</li><li>Comparison-contrast and cause-effect show relationships.</li></ul><h3>Try this</h3><p>Identify the pattern of development used in a short article.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(196, 2, 3, 'Properties of a Well-Written Text', 'A well-written text is organized, coherent, and appropriate for its audience.', '<p>A well-written text is organized, coherent, and appropriate for its audience.</p><h3>Key points</h3><ul><li>Organization is the logical arrangement of ideas.</li><li>Coherence and cohesion make ideas flow smoothly.</li><li>Language use should fit the audience and purpose.</li></ul><h3>Try this</h3><p>Revise a messy paragraph to improve its coherence.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(197, 2, 4, 'Critical Reading', 'Critical reading means evaluating a text instead of simply accepting it.', '<p>Critical reading means evaluating a text instead of simply accepting it.</p><h3>Key points</h3><ul><li>Identify claims of fact, value, and policy.</li><li>Check whether the evidence is enough and reliable.</li><li>Notice the author&#x27;s purpose and possible bias.</li></ul><h3>Try this</h3><p>Identify the claim in an editorial and evaluate its supporting evidence.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(198, 2, 5, 'Writing Professional Correspondence', 'Professional writing follows formats that are clear and polite.', '<p>Professional writing follows formats that are clear and polite.</p><h3>Key points</h3><ul><li>Letters of application include your purpose, qualifications, and contact details.</li><li>Resumes summarize education, skills, and experience.</li><li>Use formal language and proofread carefully.</li></ul><h3>Try this</h3><p>Write a letter of application for a part-time job.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(199, 57, 1, 'ICT in the Philippines', 'Information and communication technology has changed how Filipinos learn, work, and connect.', '<p>Information and communication technology has changed how Filipinos learn, work, and connect.</p><h3>Key points</h3><ul><li>The Philippines is a leader in business process outsourcing.</li><li>Web 1.0 was read-only, Web 2.0 is interactive, and Web 3.0 is the semantic web.</li><li>ICT trends include mobile technology, social media, and cloud computing.</li></ul><h3>Try this</h3><p>Give three ways ICT has changed your daily life.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(200, 57, 2, 'Online Safety and Netiquette', 'Staying safe online protects your identity, privacy, and reputation.', '<p>Staying safe online protects your identity, privacy, and reputation.</p><h3>Key points</h3><ul><li>Use strong passwords and do not share personal information.</li><li>Beware of phishing, malware, and scams.</li><li>Netiquette means being respectful and responsible online.</li></ul><h3>Try this</h3><p>Create five rules of netiquette for your class group chat.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(201, 57, 3, 'Productivity Tools', 'Productivity tools help create professional documents, spreadsheets, and presentations.', '<p>Productivity tools help create professional documents, spreadsheets, and presentations.</p><h3>Key points</h3><ul><li>Mail merge creates personalized letters from one template.</li><li>Spreadsheet functions like SUM, AVERAGE, and IF automate calculations.</li><li>Presentations should be simple, visual, and readable.</li></ul><h3>Try this</h3><p>Use the IF function to mark scores 75 and above as &quot;Passed.&quot;</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(202, 57, 4, 'Imaging and Design for Online Media', 'Good design makes online content clear and attractive.', '<p>Good design makes online content clear and attractive.</p><h3>Key points</h3><ul><li>Principles of design include balance, contrast, emphasis, and unity.</li><li>Use compressed images for faster loading.</li><li>Respect copyright when using images.</li></ul><h3>Try this</h3><p>Design a simple infographic about online safety.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(203, 57, 5, 'ICT Project for Social Change', 'ICT can be used to solve problems and help communities.', '<p>ICT can be used to solve problems and help communities.</p><h3>Key points</h3><ul><li>Identify a community need.</li><li>Plan a website, campaign, or app to address it.</li><li>Measure the impact of your project.</li></ul><h3>Try this</h3><p>Plan an online campaign that promotes a cause in your community.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(204, 58, 1, 'Introduction to Applied Economics', 'Applied economics uses economic principles to solve real problems.', '<p>Applied economics uses economic principles to solve real problems.</p><h3>Key points</h3><ul><li>Scarcity forces individuals and societies to make choices.</li><li>Economics is divided into microeconomics and macroeconomics.</li><li>Economic decisions affect jobs, prices, and growth.</li></ul><h3>Try this</h3><p>Identify an economic problem in your community and its possible causes.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(205, 58, 2, 'Market Demand and Supply', 'Prices are determined by the interaction of demand and supply.', '<p>Prices are determined by the interaction of demand and supply.</p><h3>Key points</h3><ul><li>Changes in income, tastes, and prices of related goods shift demand.</li><li>Changes in technology and input costs shift supply.</li><li>Shortages and surpluses push prices toward equilibrium.</li></ul><h3>Try this</h3><p>Explain what happens to the price of rice after a bad harvest.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(206, 58, 3, 'Contemporary Economic Issues', 'Many current issues affect the Filipino consumer and worker.', '<p>Many current issues affect the Filipino consumer and worker.</p><h3>Key points</h3><ul><li>Inflation reduces the purchasing power of money.</li><li>Minimum wage policies affect workers and businesses.</li><li>Rising fuel prices affect transport and food costs.</li></ul><h3>Try this</h3><p>Interview a parent on how rising prices have affected household spending.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(207, 58, 4, 'Industry Analysis', 'Industry analysis studies the strengths and weaknesses of an industry.', '<p>Industry analysis studies the strengths and weaknesses of an industry.</p><h3>Key points</h3><ul><li>Consider demand, competition, suppliers, and government regulations.</li><li>SWOT analysis looks at strengths, weaknesses, opportunities, and threats.</li><li>Good analysis helps entrepreneurs choose the right business.</li></ul><h3>Try this</h3><p>Make a SWOT analysis of a local business in your area.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(208, 58, 5, 'Socioeconomic Impact of Business', 'Businesses affect communities through jobs, taxes, and the environment.', '<p>Businesses affect communities through jobs, taxes, and the environment.</p><h3>Key points</h3><ul><li>They create employment and income.</li><li>They should follow laws and practice social responsibility.</li><li>Sustainable businesses balance profit with care for people and the planet.</li></ul><h3>Try this</h3><p>Evaluate the positive and negative effects of a business near your home.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(209, 21, 1, 'Imagery and Figurative Language', 'Creative writers use imagery and figures of speech to make writing vivid.', '<p>Creative writers use imagery and figures of speech to make writing vivid.</p><h3>Key points</h3><ul><li>Imagery appeals to the five senses.</li><li>Simile compares using &quot;like&quot; or &quot;as&quot;; metaphor compares directly.</li><li>Personification gives human traits to non-human things.</li></ul><h3>Try this</h3><p>Write a paragraph describing the rain using at least three senses.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(210, 21, 2, 'Writing Poetry', 'Poetry expresses ideas and emotions through carefully chosen words, sound, and form.', '<p>Poetry expresses ideas and emotions through carefully chosen words, sound, and form.</p><h3>Key points</h3><ul><li>Elements include rhyme, rhythm, meter, and stanza.</li><li>Free verse does not follow fixed rhyme or meter.</li><li>Every word should earn its place.</li></ul><h3>Try this</h3><p>Write a free-verse poem about your hometown.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(211, 21, 3, 'Writing Fiction', 'Fiction tells imagined stories with characters, setting, plot, and conflict.', '<p>Fiction tells imagined stories with characters, setting, plot, and conflict.</p><h3>Key points</h3><ul><li>Show, don&#x27;t tell: reveal emotions through actions and details.</li><li>Point of view affects how readers experience the story.</li><li>Strong conflict keeps readers interested.</li></ul><h3>Try this</h3><p>Write the opening scene of a short story with a strong conflict.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(212, 21, 4, 'Writing Drama', 'Drama is written to be performed, using dialogue and stage directions.', '<p>Drama is written to be performed, using dialogue and stage directions.</p><h3>Key points</h3><ul><li>Characters reveal themselves through what they say and do.</li><li>A one-act play focuses on a single situation.</li><li>Stage directions guide actors and set designers.</li></ul><h3>Try this</h3><p>Write a one-page scene for two characters.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(213, 21, 5, 'Revising and Workshopping', 'Good writing comes from revision and honest feedback.', '<p>Good writing comes from revision and honest feedback.</p><h3>Key points</h3><ul><li>Read your work aloud to catch awkward lines.</li><li>In a workshop, give specific and kind feedback.</li><li>Revise for clarity, then for style.</li></ul><h3>Try this</h3><p>Exchange a piece with a classmate and give three specific suggestions.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(214, 20, 1, 'Nature of Research', 'Research is a systematic process of finding answers to questions.', '<p>Research is a systematic process of finding answers to questions.</p><h3>Key points</h3><ul><li>Quantitative research uses numbers and statistics.</li><li>Qualitative research explores experiences and meanings.</li><li>Good research is objective, ethical, and replicable.</li></ul><h3>Try this</h3><p>Give one research topic suited for quantitative and one for qualitative research.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(215, 20, 2, 'Identifying the Research Problem', 'A clear research problem guides the whole study.', '<p>A clear research problem guides the whole study.</p><h3>Key points</h3><ul><li>Choose a topic that is relevant, specific, and doable.</li><li>Write a research title and statement of the problem.</li><li>Define the scope and limitations of the study.</li></ul><h3>Try this</h3><p>Write a research title and three specific research questions.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(216, 20, 3, 'Review of Related Literature', 'The review of related literature shows what is already known about the topic.', '<p>The review of related literature shows what is already known about the topic.</p><h3>Key points</h3><ul><li>Use reliable sources such as journals and books.</li><li>Summarize and synthesize, not just copy.</li><li>Cite sources properly to avoid plagiarism.</li></ul><h3>Try this</h3><p>Find and summarize two studies related to your research topic.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(217, 20, 4, 'Research Design and Data Gathering', 'The research design explains how data will be collected and analyzed.', '<p>The research design explains how data will be collected and analyzed.</p><h3>Key points</h3><ul><li>Choose respondents through appropriate sampling.</li><li>Instruments include questionnaires, interviews, and tests.</li><li>Ask for informed consent from respondents.</li></ul><h3>Try this</h3><p>Draft five questionnaire items for your study.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(218, 20, 5, 'Data Analysis and Reporting', 'Data analysis turns raw data into findings that answer the research questions.', '<p>Data analysis turns raw data into findings that answer the research questions.</p><h3>Key points</h3><ul><li>Use tables and graphs to present results.</li><li>Interpret results and connect them to the literature.</li><li>Write conclusions and recommendations based on findings.</li></ul><h3>Try this</h3><p>Present sample data in a table and write a short interpretation.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(219, 59, 1, 'Fitness and Healthy Lifestyle', 'A healthy lifestyle combines regular activity, good nutrition, and enough rest.', '<p>A healthy lifestyle combines regular activity, good nutrition, and enough rest.</p><h3>Key points</h3><ul><li>Adults and teens need regular moderate-to-vigorous activity.</li><li>Sleep and stress management affect fitness.</li><li>Set realistic and measurable fitness goals.</li></ul><h3>Try this</h3><p>Create a personal fitness plan for one week.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(220, 59, 2, 'Exercise Principles', 'Following exercise principles makes training safe and effective.', '<p>Following exercise principles makes training safe and effective.</p><h3>Key points</h3><ul><li>FITT: Frequency, Intensity, Time, and Type.</li><li>Progressive overload means gradually increasing difficulty.</li><li>Rest and recovery prevent injury.</li></ul><h3>Try this</h3><p>Apply the FITT principle to your favorite exercise.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(221, 59, 3, 'Sports and Recreation', 'Sports and recreational activities improve fitness and social skills.', '<p>Sports and recreational activities improve fitness and social skills.</p><h3>Key points</h3><ul><li>Choose activities you enjoy so you stay active.</li><li>Learn the rules and basic skills of the sport.</li><li>Practice good sportsmanship.</li></ul><h3>Try this</h3><p>Organize a short game with classmates and explain its rules.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(222, 59, 4, 'Dance as Fitness', 'Dance builds endurance, coordination, and confidence.', '<p>Dance builds endurance, coordination, and confidence.</p><h3>Key points</h3><ul><li>Types include folk dance, hip-hop, and contemporary dance.</li><li>Dance can be done alone or in groups.</li><li>Warm up properly to avoid injury.</li></ul><h3>Try this</h3><p>Learn and perform a short dance routine with your group.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(223, 59, 5, 'Leading Physical Activities', 'Leaders help others stay active and safe.', '<p>Leaders help others stay active and safe.</p><h3>Key points</h3><ul><li>Plan activities that suit the group&#x27;s fitness level.</li><li>Give clear instructions and demonstrations.</li><li>Encourage participation and inclusion.</li></ul><h3>Try this</h3><p>Lead a five-minute warm-up for your class.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(224, 60, 1, 'Introduction to Work Immersion', 'Work immersion lets students experience real work in a chosen field.', '<p>Work immersion lets students experience real work in a chosen field.</p><h3>Key points</h3><ul><li>It connects classroom learning to actual work settings.</li><li>Students observe and perform tasks under supervision.</li><li>It helps students decide on future careers.</li></ul><h3>Try this</h3><p>List three things you hope to learn during work immersion.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(225, 60, 2, 'Workplace Behavior and Ethics', 'Proper behavior in the workplace shows professionalism.', '<p>Proper behavior in the workplace shows professionalism.</p><h3>Key points</h3><ul><li>Be punctual, wear proper attire, and follow company rules.</li><li>Respect supervisors and co-workers.</li><li>Keep company information confidential.</li></ul><h3>Try this</h3><p>Write a list of do&#x27;s and don&#x27;ts for your immersion site.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(226, 60, 3, 'Safety and Health at Work', 'Every worker must follow safety rules to prevent accidents.', '<p>Every worker must follow safety rules to prevent accidents.</p><h3>Key points</h3><ul><li>Know the emergency exits and procedures.</li><li>Use protective equipment when needed.</li><li>Report hazards or accidents immediately.</li></ul><h3>Try this</h3><p>Identify possible hazards at your immersion site.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(227, 60, 4, 'Performing Work Tasks', 'During immersion, students apply their skills to real tasks.', '<p>During immersion, students apply their skills to real tasks.</p><h3>Key points</h3><ul><li>Follow instructions carefully and ask questions when unsure.</li><li>Keep a daily log of tasks and learnings.</li><li>Accept feedback and improve.</li></ul><h3>Try this</h3><p>Keep a daily journal of your tasks for one week.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(228, 60, 5, 'Portfolio and Reflection', 'A portfolio documents your work immersion experience.', '<p>A portfolio documents your work immersion experience.</p><h3>Key points</h3><ul><li>Include your daily logs, photos, and outputs.</li><li>Reflect on what you learned and how you grew.</li><li>Present your experience clearly and honestly.</li></ul><h3>Try this</h3><p>Write a reflection paper on your work immersion experience.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(229, 61, 1, 'Conic Sections', 'Conic sections are curves formed when a plane cuts a cone.', '<p>Conic sections are curves formed when a plane cuts a cone.</p><h3>Key points</h3><ul><li>The four conics are the circle, parabola, ellipse, and hyperbola.</li><li>Each has a standard equation and key parts like center and foci.</li><li>Conics appear in satellite dishes, orbits, and bridges.</li></ul><h3>Try this</h3><p>Find the center and radius of x² + y² − 6x + 4y − 3 = 0.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(230, 61, 2, 'Systems of Nonlinear Equations', 'Nonlinear systems include at least one equation that is not linear.', '<p>Nonlinear systems include at least one equation that is not linear.</p><h3>Key points</h3><ul><li>Solve by substitution or elimination.</li><li>Solutions are the intersection points of the graphs.</li><li>A system can have zero, one, or many solutions.</li></ul><h3>Try this</h3><p>Solve the system y = x² and y = x + 2.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(231, 61, 3, 'Sequences, Series, and Mathematical Induction', 'Series add the terms of a sequence, and induction proves statements for all natural numbers.', '<p>Series add the terms of a sequence, and induction proves statements for all natural numbers.</p><h3>Key points</h3><ul><li>Sigma notation writes sums compactly.</li><li>The binomial theorem expands (a + b)ⁿ.</li><li>Mathematical induction has a base case and an inductive step.</li></ul><h3>Try this</h3><p>Expand (x + 2)⁴ using the binomial theorem.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(232, 61, 4, 'Angles and the Unit Circle', 'Trigonometry extends to any angle using the unit circle.', '<p>Trigonometry extends to any angle using the unit circle.</p><h3>Key points</h3><ul><li>Angles can be measured in degrees or radians: 180° = π radians.</li><li>On the unit circle, cos θ is the x-coordinate and sin θ is the y-coordinate.</li><li>Reference angles help find trigonometric values.</li></ul><h3>Try this</h3><p>Convert 135° to radians and find sin 135°.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(233, 61, 5, 'Trigonometric Identities and Equations', 'Identities are equations true for all values, and they help simplify expressions.', '<p>Identities are equations true for all values, and they help simplify expressions.</p><h3>Key points</h3><ul><li>Pythagorean identity: sin²θ + cos²θ = 1.</li><li>Sum and difference formulas expand sin(A ± B) and cos(A ± B).</li><li>Trigonometric equations may have many solutions.</li></ul><h3>Try this</h3><p>Prove that (1 − cos²θ) / sin θ = sin θ.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(234, 62, 1, 'Cell Theory and Cell Structure', 'The cell theory states that all living things are made of cells, the basic unit of life.', '<p>The cell theory states that all living things are made of cells, the basic unit of life.</p><h3>Key points</h3><ul><li>Prokaryotic cells have no nucleus; eukaryotic cells do.</li><li>Organelles have specific functions, like mitochondria for energy.</li><li>Cell structure is related to cell function.</li></ul><h3>Try this</h3><p>Make a table of five organelles and their functions.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(235, 62, 2, 'Cell Cycle and Division', 'Cells divide to grow, repair, and reproduce.', '<p>Cells divide to grow, repair, and reproduce.</p><h3>Key points</h3><ul><li>Mitosis produces two identical cells for growth and repair.</li><li>Meiosis produces four sex cells with half the chromosomes.</li><li>Errors in cell division can lead to diseases like cancer.</li></ul><h3>Try this</h3><p>Compare mitosis and meiosis using a Venn diagram.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(236, 62, 3, 'Transport Mechanisms', 'Cells move substances across the membrane to stay alive.', '<p>Cells move substances across the membrane to stay alive.</p><h3>Key points</h3><ul><li>Passive transport, like diffusion and osmosis, needs no energy.</li><li>Active transport uses energy to move substances against a gradient.</li><li>Endocytosis and exocytosis move large particles.</li></ul><h3>Try this</h3><p>Explain why salt on a slug causes it to shrink.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(237, 62, 4, 'Enzymes', 'Enzymes are proteins that speed up chemical reactions in living things.', '<p>Enzymes are proteins that speed up chemical reactions in living things.</p><h3>Key points</h3><ul><li>Each enzyme fits a specific substrate, like a lock and key.</li><li>Temperature and pH affect enzyme activity.</li><li>Digestive enzymes break down food.</li></ul><h3>Try this</h3><p>Explain why a high fever can be dangerous to the body&#x27;s enzymes.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(238, 62, 5, 'Energy in Cells', 'Cells capture and use energy through photosynthesis and cellular respiration.', '<p>Cells capture and use energy through photosynthesis and cellular respiration.</p><h3>Key points</h3><ul><li>ATP is the energy currency of the cell.</li><li>Photosynthesis stores energy in glucose using sunlight.</li><li>Cellular respiration releases energy from glucose.</li></ul><h3>Try this</h3><p>Write the overall equations for photosynthesis and cellular respiration.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(239, 63, 1, 'Matter and Its Properties', 'Matter is anything that has mass and takes up space.', '<p>Matter is anything that has mass and takes up space.</p><h3>Key points</h3><ul><li>Physical properties can be observed without changing the substance.</li><li>Chemical properties describe how a substance reacts.</li><li>Matter can be a pure substance or a mixture.</li></ul><h3>Try this</h3><p>Classify ten household items as elements, compounds, or mixtures.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(240, 63, 2, 'Measurements', 'Chemistry relies on accurate and precise measurements.', '<p>Chemistry relies on accurate and precise measurements.</p><h3>Key points</h3><ul><li>The SI system is used in science.</li><li>Significant figures show the precision of a measurement.</li><li>Accuracy is closeness to the true value; precision is consistency.</li></ul><h3>Try this</h3><p>Round 0.0045670 to three significant figures.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(241, 63, 3, 'Atoms, Molecules, and Ions', 'Atoms are the building blocks of matter, made of protons, neutrons, and electrons.', '<p>Atoms are the building blocks of matter, made of protons, neutrons, and electrons.</p><h3>Key points</h3><ul><li>The atomic number is the number of protons.</li><li>Isotopes have the same protons but different neutrons.</li><li>Ions form when atoms gain or lose electrons.</li></ul><h3>Try this</h3><p>Find the number of protons, neutrons, and electrons in ²³Na⁺.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(242, 63, 4, 'Stoichiometry', 'Stoichiometry uses balanced equations to calculate amounts of reactants and products.', '<p>Stoichiometry uses balanced equations to calculate amounts of reactants and products.</p><h3>Key points</h3><ul><li>One mole contains 6.022 × 10²³ particles.</li><li>Molar mass converts grams to moles.</li><li>The limiting reactant determines how much product forms.</li></ul><h3>Try this</h3><p>How many moles are in 36 grams of water (H₂O)?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(243, 63, 5, 'Gases', 'The behavior of gases is described by gas laws.', '<p>The behavior of gases is described by gas laws.</p><h3>Key points</h3><ul><li>The ideal gas law: PV = nRT.</li><li>Dalton&#x27;s law: total pressure is the sum of partial pressures.</li><li>The kinetic molecular theory explains gas behavior.</li></ul><h3>Try this</h3><p>Use PV = nRT to find the volume of 1 mole of gas at standard conditions.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(244, 64, 1, 'Genetics', 'Genetics studies how traits are inherited through genes.', '<p>Genetics studies how traits are inherited through genes.</p><h3>Key points</h3><ul><li>Mendel&#x27;s laws: segregation and independent assortment.</li><li>Some traits show incomplete dominance or codominance.</li><li>Sex-linked traits are carried on the X chromosome.</li></ul><h3>Try this</h3><p>Explain why color blindness is more common in males.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(245, 64, 2, 'Recombinant DNA and Biotechnology', 'Biotechnology uses living things to make useful products.', '<p>Biotechnology uses living things to make useful products.</p><h3>Key points</h3><ul><li>Recombinant DNA combines DNA from different sources.</li><li>Applications include insulin production and genetically modified crops.</li><li>Biotechnology raises ethical questions that need careful thought.</li></ul><h3>Try this</h3><p>Debate one benefit and one risk of genetically modified organisms.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(246, 64, 3, 'Evolution', 'Evolution is the change in species over many generations.', '<p>Evolution is the change in species over many generations.</p><h3>Key points</h3><ul><li>Evidence includes fossils, comparative anatomy, and DNA.</li><li>Darwin proposed natural selection as its main mechanism.</li><li>New species form through isolation and adaptation.</li></ul><h3>Try this</h3><p>Explain how antibiotic resistance in bacteria is an example of evolution.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(247, 64, 4, 'Systematics and Classification', 'Systematics classifies living things based on their evolutionary relationships.', '<p>Systematics classifies living things based on their evolutionary relationships.</p><h3>Key points</h3><ul><li>Taxonomic levels: domain, kingdom, phylum, class, order, family, genus, species.</li><li>Scientific names use binomial nomenclature, like Homo sapiens.</li><li>Phylogenetic trees show common ancestry.</li></ul><h3>Try this</h3><p>Write the full classification of the Philippine eagle.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(248, 64, 5, 'Organ Systems', 'Plants and animals have organ systems that work together to keep them alive.', '<p>Plants and animals have organ systems that work together to keep them alive.</p><h3>Key points</h3><ul><li>Animals have digestive, circulatory, respiratory, and nervous systems.</li><li>Plants have root and shoot systems.</li><li>Organ systems maintain homeostasis.</li></ul><h3>Try this</h3><p>Compare how plants and animals transport materials.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(249, 65, 1, 'Intermolecular Forces', 'Intermolecular forces are attractions between molecules that affect physical properties.', '<p>Intermolecular forces are attractions between molecules that affect physical properties.</p><h3>Key points</h3><ul><li>Types include dispersion forces, dipole-dipole forces, and hydrogen bonds.</li><li>Stronger forces lead to higher boiling points.</li><li>Hydrogen bonding explains water&#x27;s unique properties.</li></ul><h3>Try this</h3><p>Explain why water has a higher boiling point than methane.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(250, 65, 2, 'Solutions and Concentration', 'Concentration describes how much solute is in a solution.', '<p>Concentration describes how much solute is in a solution.</p><h3>Key points</h3><ul><li>Molarity (M) = moles of solute ÷ liters of solution.</li><li>Percent by mass and mole fraction are other units.</li><li>Colligative properties depend on the number of solute particles.</li></ul><h3>Try this</h3><p>Find the molarity of 58.5 g of NaCl dissolved in 1 liter of solution.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(251, 65, 3, 'Thermochemistry', 'Thermochemistry studies the heat involved in chemical reactions.', '<p>Thermochemistry studies the heat involved in chemical reactions.</p><h3>Key points</h3><ul><li>Exothermic reactions release heat; endothermic reactions absorb heat.</li><li>Enthalpy change (ΔH) measures heat at constant pressure.</li><li>Hess&#x27;s law adds reaction steps to find total ΔH.</li></ul><h3>Try this</h3><p>Classify burning wood and melting ice as exothermic or endothermic.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(252, 65, 4, 'Chemical Kinetics and Equilibrium', 'Kinetics studies reaction rates, and equilibrium describes balanced reversible reactions.', '<p>Kinetics studies reaction rates, and equilibrium describes balanced reversible reactions.</p><h3>Key points</h3><ul><li>Catalysts speed up reactions without being used up.</li><li>At equilibrium, forward and reverse rates are equal.</li><li>Le Chatelier&#x27;s principle predicts how equilibrium shifts.</li></ul><h3>Try this</h3><p>Predict what happens to an equilibrium when more reactant is added.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL);
INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `description`, `content`, `file_path`, `week_number`, `published`, `updated_by`, `created_at`, `updated_at`) VALUES
(253, 65, 5, 'Acids, Bases, and Electrochemistry', 'Acids and bases react in predictable ways, and electrochemistry relates chemistry to electricity.', '<p>Acids and bases react in predictable ways, and electrochemistry relates chemistry to electricity.</p><h3>Key points</h3><ul><li>pH below 7 is acidic; above 7 is basic.</li><li>Neutralization forms salt and water.</li><li>Batteries produce electricity through redox reactions.</li></ul><h3>Try this</h3><p>Test common household items with a natural indicator like red cabbage.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(254, 66, 1, 'Kinematics', 'Kinematics describes motion without considering its causes.', '<p>Kinematics describes motion without considering its causes.</p><h3>Key points</h3><ul><li>Displacement, velocity, and acceleration are vector quantities.</li><li>For constant acceleration: v = v₀ + at.</li><li>Free-falling objects accelerate at about 9.8 m/s².</li></ul><h3>Try this</h3><p>A ball is dropped from rest. How fast is it moving after 3 seconds?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(255, 66, 2, 'Newton\'s Laws and Dynamics', 'Dynamics explains motion using forces.', '<p>Dynamics explains motion using forces.</p><h3>Key points</h3><ul><li>Draw free-body diagrams to show all forces on an object.</li><li>Net force = mass × acceleration.</li><li>Friction opposes motion between surfaces.</li></ul><h3>Try this</h3><p>Draw a free-body diagram of a box being pushed across the floor.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(256, 66, 3, 'Work, Energy, and Momentum', 'Energy and momentum are conserved quantities that help solve physics problems.', '<p>Energy and momentum are conserved quantities that help solve physics problems.</p><h3>Key points</h3><ul><li>Work-energy theorem: work done equals change in kinetic energy.</li><li>Mechanical energy is conserved when only gravity acts.</li><li>Total momentum is conserved in collisions.</li></ul><h3>Try this</h3><p>Find the speed of a 2 kg ball dropped from 5 m just before it hits the ground.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(257, 66, 4, 'Electricity and Circuits', 'Electric circuits allow charges to flow and do useful work.', '<p>Electric circuits allow charges to flow and do useful work.</p><h3>Key points</h3><ul><li>Ohm&#x27;s law: V = IR.</li><li>Series circuits have one path; parallel circuits have many paths.</li><li>Power = voltage × current.</li></ul><h3>Try this</h3><p>Find the current through a 10-ohm resistor connected to a 12 V battery.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(258, 66, 5, 'Magnetism and Electromagnetic Induction', 'Moving charges create magnetic fields, and changing magnetic fields create currents.', '<p>Moving charges create magnetic fields, and changing magnetic fields create currents.</p><h3>Key points</h3><ul><li>Magnets have north and south poles.</li><li>Faraday&#x27;s law explains how generators produce electricity.</li><li>Transformers change voltage levels.</li></ul><h3>Try this</h3><p>Explain how a generator in a power plant produces electricity.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(259, 67, 1, 'Limits', 'A limit describes the value a function approaches as the input gets close to a number.', '<p>A limit describes the value a function approaches as the input gets close to a number.</p><h3>Key points</h3><ul><li>Notation: lim (x→a) f(x) = L.</li><li>Limits can be found from tables, graphs, or algebra.</li><li>One-sided limits approach from the left or the right.</li></ul><h3>Try this</h3><p>Find lim (x→2) (x² − 4) / (x − 2).</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(260, 67, 2, 'Continuity', 'A function is continuous at a point if its graph has no break there.', '<p>A function is continuous at a point if its graph has no break there.</p><h3>Key points</h3><ul><li>f(a) must exist.</li><li>The limit as x approaches a must exist.</li><li>The limit must equal f(a).</li></ul><h3>Try this</h3><p>Is f(x) = 1/x continuous at x = 0? Explain.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(261, 67, 3, 'Derivatives', 'The derivative measures the instantaneous rate of change of a function.', '<p>The derivative measures the instantaneous rate of change of a function.</p><h3>Key points</h3><ul><li>It is the slope of the tangent line at a point.</li><li>Power rule: d/dx (xⁿ) = nxⁿ⁻¹.</li><li>Product, quotient, and chain rules handle more complex functions.</li></ul><h3>Try this</h3><p>Find the derivative of f(x) = 3x⁴ − 2x² + 7.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(262, 67, 4, 'Applications of Derivatives', 'Derivatives help solve problems about rates and optimization.', '<p>Derivatives help solve problems about rates and optimization.</p><h3>Key points</h3><ul><li>Critical points occur where the derivative is zero or undefined.</li><li>Maximum and minimum values solve optimization problems.</li><li>Related rates connect changing quantities.</li></ul><h3>Try this</h3><p>Find two positive numbers whose sum is 20 and whose product is as large as possible.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(263, 67, 5, 'Integration', 'Integration is the reverse of differentiation and finds areas and accumulated change.', '<p>Integration is the reverse of differentiation and finds areas and accumulated change.</p><h3>Key points</h3><ul><li>Antiderivative of xⁿ is xⁿ⁺¹/(n + 1) + C.</li><li>The definite integral gives the area under a curve.</li><li>The Fundamental Theorem of Calculus links derivatives and integrals.</li></ul><h3>Try this</h3><p>Evaluate the integral of 2x from 0 to 3.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(264, 15, 1, 'Fractions, Decimals, and Percent', 'Business calculations often require converting between fractions, decimals, and percent.', '<p>Business calculations often require converting between fractions, decimals, and percent.</p><h3>Key points</h3><ul><li>To change a decimal to percent, multiply by 100.</li><li>To change a percent to a decimal, divide by 100.</li><li>Percent is used in discounts, interest, and taxes.</li></ul><h3>Try this</h3><p>Convert 3/8 to a decimal and a percent.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(265, 15, 2, 'Ratio and Proportion', 'Ratios compare quantities, and proportions show that two ratios are equal.', '<p>Ratios compare quantities, and proportions show that two ratios are equal.</p><h3>Key points</h3><ul><li>A ratio can be written as a:b or a/b.</li><li>Proportions solve problems like sharing profits.</li><li>Cross-multiplication solves for a missing term.</li></ul><h3>Try this</h3><p>Three partners share ₱90,000 in the ratio 2:3:4. How much does each get?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(266, 15, 3, 'Buying and Selling', 'Pricing involves cost, markup, markdown, and profit.', '<p>Pricing involves cost, markup, markdown, and profit.</p><h3>Key points</h3><ul><li>Selling price = cost + markup.</li><li>Markdown is a reduction from the original selling price.</li><li>Gross profit = net sales − cost of goods sold.</li></ul><h3>Try this</h3><p>An item costs ₱400 and is sold with a 30% markup. Find the selling price.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:15', NULL),
(267, 15, 4, 'Salaries and Wages', 'Employees earn income through salaries, wages, and commissions.', '<p>Employees earn income through salaries, wages, and commissions.</p><h3>Key points</h3><ul><li>Wages are usually paid per hour or per day; salaries per month.</li><li>Overtime is paid at a higher rate.</li><li>Deductions include SSS, PhilHealth, Pag-IBIG, and withholding tax.</li></ul><h3>Try this</h3><p>Compute the gross pay of a worker who earns ₱80 per hour for 48 hours with 8 hours overtime at 125%.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(268, 15, 5, 'Presentation and Analysis of Business Data', 'Business data is organized in tables and graphs to support decisions.', '<p>Business data is organized in tables and graphs to support decisions.</p><h3>Key points</h3><ul><li>Line graphs show trends over time.</li><li>Bar graphs compare categories.</li><li>Pie charts show parts of a whole.</li></ul><h3>Try this</h3><p>Create a bar graph of a store&#x27;s sales for one week.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(269, 13, 1, 'Introduction to Accounting', 'Accounting is the process of recording, classifying, and summarizing financial information.', '<p>Accounting is the process of recording, classifying, and summarizing financial information.</p><h3>Key points</h3><ul><li>Users include owners, investors, lenders, and the government.</li><li>Branches include financial, managerial, and tax accounting.</li><li>Accounting helps businesses make informed decisions.</li></ul><h3>Try this</h3><p>List three users of accounting information and what each needs to know.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(270, 13, 2, 'The Accounting Equation', 'Every transaction keeps the accounting equation in balance.', '<p>Every transaction keeps the accounting equation in balance.</p><h3>Key points</h3><ul><li>Assets = Liabilities + Owner&#x27;s Equity.</li><li>Assets are resources owned; liabilities are debts owed.</li><li>Owner&#x27;s equity is the owner&#x27;s claim on the assets.</li></ul><h3>Try this</h3><p>If assets are ₱500,000 and liabilities are ₱200,000, find owner&#x27;s equity.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(271, 13, 3, 'Types of Business Organizations', 'Businesses are organized in different forms with different rules and risks.', '<p>Businesses are organized in different forms with different rules and risks.</p><h3>Key points</h3><ul><li>Sole proprietorship: owned by one person.</li><li>Partnership: owned by two or more people.</li><li>Corporation: a separate legal entity owned by stockholders.</li></ul><h3>Try this</h3><p>Compare the advantages and disadvantages of a sole proprietorship and a partnership.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(272, 13, 4, 'The Accounting Cycle', 'The accounting cycle is the step-by-step process of handling business transactions.', '<p>The accounting cycle is the step-by-step process of handling business transactions.</p><h3>Key points</h3><ul><li>Analyze transactions and record them in the journal.</li><li>Post entries to the ledger and prepare a trial balance.</li><li>Prepare financial statements and close the books.</li></ul><h3>Try this</h3><p>Arrange the steps of the accounting cycle in order.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(273, 13, 5, 'Journalizing Transactions', 'Journal entries record transactions using debits and credits.', '<p>Journal entries record transactions using debits and credits.</p><h3>Key points</h3><ul><li>Every entry has at least one debit and one credit.</li><li>Total debits must equal total credits.</li><li>Assets increase with a debit; liabilities and equity increase with a credit.</li></ul><h3>Try this</h3><p>Journalize: the owner invested ₱100,000 cash into the business.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(274, 14, 1, 'The Statement of Financial Position', 'The statement of financial position shows a business\'s assets, liabilities, and equity at a point in time.', '<p>The statement of financial position shows a business&#x27;s assets, liabilities, and equity at a point in time.</p><h3>Key points</h3><ul><li>It is also called the balance sheet.</li><li>Assets are classified as current or non-current.</li><li>It must always balance.</li></ul><h3>Try this</h3><p>Prepare a simple statement of financial position from given balances.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(275, 14, 2, 'The Statement of Comprehensive Income', 'This statement shows a business\'s income and expenses over a period.', '<p>This statement shows a business&#x27;s income and expenses over a period.</p><h3>Key points</h3><ul><li>Revenue minus expenses equals net income or net loss.</li><li>It shows how well the business performed.</li><li>Expenses include cost of sales and operating expenses.</li></ul><h3>Try this</h3><p>Compute net income if revenue is ₱250,000 and expenses are ₱180,000.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(276, 14, 3, 'Statement of Changes in Equity and Cash Flows', 'These statements explain changes in owner\'s equity and the movement of cash.', '<p>These statements explain changes in owner&#x27;s equity and the movement of cash.</p><h3>Key points</h3><ul><li>Equity changes through investments, withdrawals, and net income.</li><li>Cash flows are grouped into operating, investing, and financing activities.</li><li>A profitable business can still run out of cash.</li></ul><h3>Try this</h3><p>Classify five transactions into operating, investing, or financing activities.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(277, 14, 4, 'Financial Statement Analysis', 'Analysis helps users understand a business\'s financial health.', '<p>Analysis helps users understand a business&#x27;s financial health.</p><h3>Key points</h3><ul><li>Horizontal analysis compares amounts across years.</li><li>Vertical analysis shows each item as a percent of a base.</li><li>Ratios measure liquidity, profitability, and solvency.</li></ul><h3>Try this</h3><p>Compute the current ratio if current assets are ₱120,000 and current liabilities are ₱60,000.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(278, 14, 5, 'Income Tax and Banking', 'Businesses must pay taxes and use banking services wisely.', '<p>Businesses must pay taxes and use banking services wisely.</p><h3>Key points</h3><ul><li>The Bureau of Internal Revenue collects taxes in the Philippines.</li><li>Banks offer savings, checking, and loan services.</li><li>Keeping records helps prepare accurate tax returns.</li></ul><h3>Try this</h3><p>List the documents a small business needs to register with the BIR.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(279, 17, 1, 'Nature and Concept of Management', 'Management is the process of reaching goals through people and resources.', '<p>Management is the process of reaching goals through people and resources.</p><h3>Key points</h3><ul><li>Functions of management: planning, organizing, leading, and controlling.</li><li>Managers work at top, middle, and lower levels.</li><li>Good management makes organizations efficient and effective.</li></ul><h3>Try this</h3><p>Interview a manager about their daily tasks and classify them by function.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(280, 17, 2, 'The Business Environment', 'Businesses are affected by forces inside and outside the organization.', '<p>Businesses are affected by forces inside and outside the organization.</p><h3>Key points</h3><ul><li>The internal environment includes employees and culture.</li><li>The external environment includes customers, competitors, and the economy.</li><li>Filipino business culture values relationships and trust.</li></ul><h3>Try this</h3><p>Identify three external factors that affect a local sari-sari store.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(281, 17, 3, 'Planning', 'Planning sets goals and decides how to achieve them.', '<p>Planning sets goals and decides how to achieve them.</p><h3>Key points</h3><ul><li>Strategic plans are long-term; operational plans are short-term.</li><li>Goals should be SMART: specific, measurable, achievable, relevant, and time-bound.</li><li>Planning reduces uncertainty.</li></ul><h3>Try this</h3><p>Write a SMART goal for a student organization.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(282, 17, 4, 'Organizing and Staffing', 'Organizing arranges people and tasks, and staffing fills positions with the right people.', '<p>Organizing arranges people and tasks, and staffing fills positions with the right people.</p><h3>Key points</h3><ul><li>An organizational chart shows roles and reporting lines.</li><li>Staffing includes recruitment, selection, training, and evaluation.</li><li>Clear roles prevent confusion.</li></ul><h3>Try this</h3><p>Draw an organizational chart for a small business.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(283, 17, 5, 'Leading and Controlling', 'Leading motivates people, and controlling checks whether goals are met.', '<p>Leading motivates people, and controlling checks whether goals are met.</p><h3>Key points</h3><ul><li>Leadership styles include autocratic, democratic, and laissez-faire.</li><li>Motivation theories include Maslow&#x27;s hierarchy of needs.</li><li>Controlling compares actual results to standards and corrects gaps.</li></ul><h3>Try this</h3><p>Describe the leadership style you prefer and why.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(284, 68, 1, 'Introduction to Marketing', 'Marketing is the process of creating value for customers and building strong relationships.', '<p>Marketing is the process of creating value for customers and building strong relationships.</p><h3>Key points</h3><ul><li>Marketing focuses on customer needs and wants.</li><li>Good marketing creates satisfied, loyal customers.</li><li>Marketing includes research, product, pricing, distribution, and promotion.</li></ul><h3>Try this</h3><p>Explain how a local brand meets the needs of its customers.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(285, 68, 2, 'The Marketing Mix', 'The marketing mix is a set of tools used to reach the target market.', '<p>The marketing mix is a set of tools used to reach the target market.</p><h3>Key points</h3><ul><li>Product: what is being sold.</li><li>Price: how much customers pay.</li><li>Place and Promotion: where it is sold and how it is advertised.</li></ul><h3>Try this</h3><p>Analyze the 4Ps of your favorite snack product.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(286, 68, 3, 'Market Segmentation and Targeting', 'Segmentation divides a market into groups with similar needs.', '<p>Segmentation divides a market into groups with similar needs.</p><h3>Key points</h3><ul><li>Bases include geographic, demographic, psychographic, and behavioral factors.</li><li>Targeting chooses which segments to serve.</li><li>Positioning creates a clear image in the customer&#x27;s mind.</li></ul><h3>Try this</h3><p>Identify the target market of a popular fast-food chain.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(287, 68, 4, 'Consumer Behavior', 'Consumer behavior studies how people decide what to buy.', '<p>Consumer behavior studies how people decide what to buy.</p><h3>Key points</h3><ul><li>The buying process: need recognition, information search, evaluation, purchase, and post-purchase.</li><li>Culture, family, and personal factors influence choices.</li><li>Understanding consumers helps design better offers.</li></ul><h3>Try this</h3><p>Describe the buying process you followed in your last major purchase.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(288, 68, 5, 'Digital Marketing', 'Digital marketing uses online channels to reach customers.', '<p>Digital marketing uses online channels to reach customers.</p><h3>Key points</h3><ul><li>Channels include social media, websites, email, and online marketplaces.</li><li>Content should be useful, engaging, and consistent.</li><li>Results can be measured using likes, shares, clicks, and sales.</li></ul><h3>Try this</h3><p>Plan three social media posts for a small business.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(289, 69, 1, 'Introduction to Business Finance', 'Business finance is about managing money to reach business goals.', '<p>Business finance is about managing money to reach business goals.</p><h3>Key points</h3><ul><li>Financial managers decide on investing, financing, and managing assets.</li><li>The goal is to maximize the value of the business.</li><li>Good financial decisions balance risk and return.</li></ul><h3>Try this</h3><p>List three financial decisions a small business owner makes.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(290, 69, 2, 'Financial Planning', 'Financial planning sets financial goals and prepares budgets to achieve them.', '<p>Financial planning sets financial goals and prepares budgets to achieve them.</p><h3>Key points</h3><ul><li>A budget estimates income and expenses.</li><li>Cash budgets help avoid running out of money.</li><li>Plans should be reviewed and updated.</li></ul><h3>Try this</h3><p>Prepare a one-month budget for a small food stall.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(291, 69, 3, 'Sources of Funds', 'Businesses raise money from different sources.', '<p>Businesses raise money from different sources.</p><h3>Key points</h3><ul><li>Equity financing comes from owners or investors.</li><li>Debt financing comes from loans and credit.</li><li>Short-term funds pay daily needs; long-term funds pay for big assets.</li></ul><h3>Try this</h3><p>Compare borrowing from a bank and getting money from investors.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(292, 69, 4, 'Time Value of Money', 'Money today is worth more than the same amount in the future.', '<p>Money today is worth more than the same amount in the future.</p><h3>Key points</h3><ul><li>Future value grows money using interest.</li><li>Present value finds today&#x27;s worth of future money.</li><li>These concepts guide investment and loan decisions.</li></ul><h3>Try this</h3><p>Find the future value of ₱5,000 after 2 years at 6% compounded annually.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(293, 69, 5, 'Personal Finance and Investments', 'Managing personal money wisely builds long-term security.', '<p>Managing personal money wisely builds long-term security.</p><h3>Key points</h3><ul><li>Save first, then spend.</li><li>Investments include savings accounts, bonds, stocks, and mutual funds.</li><li>Higher returns usually come with higher risk.</li></ul><h3>Try this</h3><p>Create a personal savings plan for the next six months.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(294, 70, 1, 'Ethics in Business', 'Business ethics guides how companies and people behave in commerce.', '<p>Business ethics guides how companies and people behave in commerce.</p><h3>Key points</h3><ul><li>Ethics is about right and wrong conduct.</li><li>Ethical businesses earn trust and long-term success.</li><li>Unethical acts include fraud, bribery, and false advertising.</li></ul><h3>Try this</h3><p>Give an example of an unethical business practice and its effects.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(295, 70, 2, 'Ethical Frameworks', 'Frameworks help people decide what is right.', '<p>Frameworks help people decide what is right.</p><h3>Key points</h3><ul><li>Utilitarianism chooses the greatest good for the greatest number.</li><li>Deontology focuses on duty and rules.</li><li>Virtue ethics focuses on good character.</li></ul><h3>Try this</h3><p>Apply two frameworks to a business dilemma.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(296, 70, 3, 'Corporate Social Responsibility', 'CSR is a company\'s commitment to benefit society and the environment.', '<p>CSR is a company&#x27;s commitment to benefit society and the environment.</p><h3>Key points</h3><ul><li>Examples include community programs and eco-friendly operations.</li><li>CSR builds brand reputation.</li><li>True CSR goes beyond marketing.</li></ul><h3>Try this</h3><p>Research a Philippine company&#x27;s CSR program and evaluate it.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(297, 70, 4, 'Social Entrepreneurship', 'Social enterprises solve social problems through business.', '<p>Social enterprises solve social problems through business.</p><h3>Key points</h3><ul><li>Profits support a social mission.</li><li>Examples include businesses that hire marginalized workers.</li><li>Success is measured by both profit and impact.</li></ul><h3>Try this</h3><p>Propose a social enterprise idea for your community.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(298, 70, 5, 'Ethical Decision-Making in the Workplace', 'Workers face ethical choices every day.', '<p>Workers face ethical choices every day.</p><h3>Key points</h3><ul><li>Identify the problem and the people affected.</li><li>Consider options and their consequences.</li><li>Choose the action you can defend openly.</li></ul><h3>Try this</h3><p>What would you do if you saw a co-worker taking company supplies?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(299, 23, 1, 'Understanding Religion', 'Religion is a system of beliefs and practices related to the sacred.', '<p>Religion is a system of beliefs and practices related to the sacred.</p><h3>Key points</h3><ul><li>Religion, spirituality, and philosophy are related but different.</li><li>Religions shape culture, laws, and values.</li><li>Respect for others&#x27; beliefs promotes peace.</li></ul><h3>Try this</h3><p>Interview someone of a different faith about a belief they hold important.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(300, 23, 2, 'Judaism, Christianity, and Islam', 'These three monotheistic religions trace their roots to Abraham.', '<p>These three monotheistic religions trace their roots to Abraham.</p><h3>Key points</h3><ul><li>Judaism follows the Torah and believes in one God.</li><li>Christianity centers on Jesus Christ and the Bible.</li><li>Islam follows the Qur&#x27;an and the teachings of Prophet Muhammad.</li></ul><h3>Try this</h3><p>Compare one practice from each of the three religions.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(301, 23, 3, 'Hinduism and Buddhism', 'Hinduism and Buddhism began in India and share ideas like karma and rebirth.', '<p>Hinduism and Buddhism began in India and share ideas like karma and rebirth.</p><h3>Key points</h3><ul><li>Hinduism has many gods and sacred texts like the Vedas.</li><li>Buddhism was founded by Siddhartha Gautama, the Buddha.</li><li>The Four Noble Truths explain suffering and its end.</li></ul><h3>Try this</h3><p>Explain the meaning of karma in your own words.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(302, 23, 4, 'Confucianism, Taoism, and Shinto', 'These East Asian belief systems emphasize harmony and proper relationships.', '<p>These East Asian belief systems emphasize harmony and proper relationships.</p><h3>Key points</h3><ul><li>Confucianism stresses respect, family, and moral leadership.</li><li>Taoism teaches living in harmony with the Tao, or &quot;the Way.&quot;</li><li>Shinto honors kami, or spirits, in nature.</li></ul><h3>Try this</h3><p>How do Confucian values appear in Filipino families?</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(303, 23, 5, 'Religion in the Modern World', 'Religions continue to shape societies today.', '<p>Religions continue to shape societies today.</p><h3>Key points</h3><ul><li>Religious diversity calls for tolerance and dialogue.</li><li>Some conflicts are linked to religion, but so are many peace efforts.</li><li>New religious movements also appear.</li></ul><h3>Try this</h3><p>Propose a school activity that promotes interfaith understanding.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(304, 24, 1, 'The Social Sciences', 'The social sciences study human behavior and society.', '<p>The social sciences study human behavior and society.</p><h3>Key points</h3><ul><li>Disciplines include anthropology, economics, geography, history, political science, psychology, and sociology.</li><li>Each discipline asks different questions about people.</li><li>They help us understand and improve society.</li></ul><h3>Try this</h3><p>Choose one social problem and explain how two disciplines would study it.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(305, 24, 2, 'Structural-Functionalism', 'Structural-functionalism sees society as parts working together for stability.', '<p>Structural-functionalism sees society as parts working together for stability.</p><h3>Key points</h3><ul><li>Each institution, like family and school, has a function.</li><li>Problems arise when parts fail to work.</li><li>Émile Durkheim is a key thinker of this view.</li></ul><h3>Try this</h3><p>Explain the function of schools in society.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(306, 24, 3, 'Marxism', 'Marxism looks at society through class struggle and economic inequality.', '<p>Marxism looks at society through class struggle and economic inequality.</p><h3>Key points</h3><ul><li>Karl Marx described conflict between owners and workers.</li><li>Economic systems shape other parts of society.</li><li>It has influenced labor movements and politics.</li></ul><h3>Try this</h3><p>Identify an example of economic inequality in your community.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(307, 24, 4, 'Symbolic Interactionism', 'Symbolic interactionism studies how people create meaning through everyday interaction.', '<p>Symbolic interactionism studies how people create meaning through everyday interaction.</p><h3>Key points</h3><ul><li>Symbols like language and gestures shape behavior.</li><li>Identity is formed through how others see us.</li><li>George Herbert Mead is a key thinker.</li></ul><h3>Try this</h3><p>Describe how a simple gesture can mean different things to different people.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(308, 24, 5, 'Filipino Thinkers in the Social Sciences', 'Filipino scholars have developed ideas that reflect Philippine experience.', '<p>Filipino scholars have developed ideas that reflect Philippine experience.</p><h3>Key points</h3><ul><li>Virgilio Enriquez developed Sikolohiyang Pilipino.</li><li>Renato Constantino wrote about colonial mentality.</li><li>Their work helps us understand Filipino identity.</li></ul><h3>Try this</h3><p>Explain the concept of &quot;kapwa&quot; in Sikolohiyang Pilipino.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(309, 71, 1, 'Politics and Governance', 'Politics is how power is gained and used, and governance is how a society is managed.', '<p>Politics is how power is gained and used, and governance is how a society is managed.</p><h3>Key points</h3><ul><li>Power, authority, and legitimacy are key concepts.</li><li>Good governance is transparent, accountable, and participatory.</li><li>Citizens play an active role in politics.</li></ul><h3>Try this</h3><p>Give an example of good governance in your local government.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(310, 71, 2, 'Political Ideologies', 'Ideologies are sets of beliefs about how society should be organized.', '<p>Ideologies are sets of beliefs about how society should be organized.</p><h3>Key points</h3><ul><li>Liberalism values individual freedom.</li><li>Socialism values equality and shared ownership.</li><li>Conservatism values tradition and stability.</li></ul><h3>Try this</h3><p>Identify an ideology reflected in a current government policy.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(311, 71, 3, 'The Philippine Government', 'The Philippines is a democratic and republican state with three branches of government.', '<p>The Philippines is a democratic and republican state with three branches of government.</p><h3>Key points</h3><ul><li>The executive branch is led by the President.</li><li>The legislative branch is Congress: the Senate and the House of Representatives.</li><li>The judiciary is headed by the Supreme Court.</li></ul><h3>Try this</h3><p>Explain the system of checks and balances among the three branches.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(312, 71, 4, 'Local Government', 'Local government units bring public services closer to the people.', '<p>Local government units bring public services closer to the people.</p><h3>Key points</h3><ul><li>LGUs include provinces, cities, municipalities, and barangays.</li><li>The Local Government Code of 1991 gave LGUs more power.</li><li>The barangay is the smallest political unit.</li></ul><h3>Try this</h3><p>Interview a barangay official about the services they provide.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(313, 71, 5, 'Civil Society and Citizenship', 'Civil society groups and active citizens strengthen democracy.', '<p>Civil society groups and active citizens strengthen democracy.</p><h3>Key points</h3><ul><li>Civil society includes NGOs, cooperatives, and people&#x27;s organizations.</li><li>Voting is a right and a responsibility.</li><li>Citizens can join consultations and volunteer programs.</li></ul><h3>Try this</h3><p>Plan a voter education activity for first-time voters.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(314, 72, 1, 'Introduction to Creative Nonfiction', 'Creative nonfiction tells true stories using literary techniques.', '<p>Creative nonfiction tells true stories using literary techniques.</p><h3>Key points</h3><ul><li>It must be factual and honest.</li><li>It uses scenes, dialogue, and imagery like fiction.</li><li>Forms include memoir, personal essay, and literary journalism.</li></ul><h3>Try this</h3><p>Read a personal essay and identify the literary techniques used.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(315, 72, 2, 'The Personal Essay', 'A personal essay reflects on an experience and what it means.', '<p>A personal essay reflects on an experience and what it means.</p><h3>Key points</h3><ul><li>It is written in the first person.</li><li>It combines storytelling and reflection.</li><li>A good essay connects personal experience to a larger idea.</li></ul><h3>Try this</h3><p>Write a personal essay about a place that shaped you.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(316, 72, 3, 'Memoir and Biography', 'Memoirs and biographies tell the stories of real lives.', '<p>Memoirs and biographies tell the stories of real lives.</p><h3>Key points</h3><ul><li>A memoir focuses on a specific part of the writer&#x27;s own life.</li><li>A biography tells the story of another person&#x27;s life.</li><li>Research and interviews add accuracy.</li></ul><h3>Try this</h3><p>Interview an elder and write a short biographical sketch.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(317, 72, 4, 'Travel Writing', 'Travel writing describes places and experiences on a journey.', '<p>Travel writing describes places and experiences on a journey.</p><h3>Key points</h3><ul><li>It captures sights, sounds, people, and culture.</li><li>Good travel writing shows the writer&#x27;s discovery.</li><li>Respect the people and places you write about.</li></ul><h3>Try this</h3><p>Write a travel piece about a trip within your province.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(318, 72, 5, 'Literary Journalism', 'Literary journalism reports real events with the style of storytelling.', '<p>Literary journalism reports real events with the style of storytelling.</p><h3>Key points</h3><ul><li>It is based on careful research and interviews.</li><li>It uses scenes and characters to engage readers.</li><li>Accuracy and ethics are essential.</li></ul><h3>Try this</h3><p>Write a feature story about a hardworking person in your community.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(319, 73, 1, 'Understanding Trends', 'A trend is a pattern of change that develops over time.', '<p>A trend is a pattern of change that develops over time.</p><h3>Key points</h3><ul><li>Trends differ from fads, which are short-lived.</li><li>Identify trends by looking at data over time.</li><li>Trends can be social, technological, or environmental.</li></ul><h3>Try this</h3><p>List three current trends among young people and explain whether each is a trend or a fad.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(320, 73, 2, 'Strategic Analysis and Intuitive Thinking', 'Critical thinking combines careful analysis with intuition.', '<p>Critical thinking combines careful analysis with intuition.</p><h3>Key points</h3><ul><li>Strategic analysis breaks a problem into parts.</li><li>Intuitive thinking uses experience and insight.</li><li>Both are needed to solve complex problems.</li></ul><h3>Try this</h3><p>Analyze a school problem using both strategic and intuitive thinking.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(321, 73, 3, 'Networks', 'A network is a set of connected people, places, or systems.', '<p>A network is a set of connected people, places, or systems.</p><h3>Key points</h3><ul><li>Social networks shape how information spreads.</li><li>Networks can help or harm, depending on how they are used.</li><li>Strong networks support communities in crisis.</li></ul><h3>Try this</h3><p>Draw a map of your personal support network.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(322, 73, 4, 'Global Networks and Technology', 'Technology connects the world in fast-changing networks.', '<p>Technology connects the world in fast-changing networks.</p><h3>Key points</h3><ul><li>Information and communication technology speeds up global exchange.</li><li>Benefits include learning and opportunity; risks include fake news and privacy loss.</li><li>Responsible use is everyone&#x27;s duty.</li></ul><h3>Try this</h3><p>Evaluate a viral post and check whether its information is accurate.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(323, 73, 5, 'Democratic Interventions', 'Democratic interventions are ways people work together to improve society.', '<p>Democratic interventions are ways people work together to improve society.</p><h3>Key points</h3><ul><li>Participation, deliberation, and voting are democratic tools.</li><li>Communities can propose solutions to local issues.</li><li>Inclusive decisions consider all voices.</li></ul><h3>Try this</h3><p>Propose a democratic way to solve a problem in your school.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(324, 18, 1, 'Applying the Social Sciences', 'The social sciences provide tools to solve real problems in society.', '<p>The social sciences provide tools to solve real problems in society.</p><h3>Key points</h3><ul><li>Counseling, social work, and communication are applied fields.</li><li>Professionals use theory to guide practice.</li><li>They work in schools, hospitals, and communities.</li></ul><h3>Try this</h3><p>Identify a professional in your community who applies social science knowledge.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(325, 18, 2, 'Counseling', 'Counseling helps people understand and solve personal problems.', '<p>Counseling helps people understand and solve personal problems.</p><h3>Key points</h3><ul><li>Counselors listen, guide, and support clients.</li><li>Core values include respect, confidentiality, and empathy.</li><li>Guidance offices in schools provide counseling services.</li></ul><h3>Try this</h3><p>List the services offered by your school&#x27;s guidance office.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(326, 18, 3, 'Social Work', 'Social work helps individuals, families, and communities improve their well-being.', '<p>Social work helps individuals, families, and communities improve their well-being.</p><h3>Key points</h3><ul><li>Social workers serve vulnerable groups like children and the elderly.</li><li>They connect people to resources and services.</li><li>The DSWD leads social welfare programs in the Philippines.</li></ul><h3>Try this</h3><p>Research a DSWD program and explain who it helps.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(327, 18, 4, 'Communication as an Applied Social Science', 'Communication professionals inform, educate, and influence the public.', '<p>Communication professionals inform, educate, and influence the public.</p><h3>Key points</h3><ul><li>Fields include journalism, public relations, and development communication.</li><li>Ethical communication is truthful and responsible.</li><li>Media literacy helps the public judge information.</li></ul><h3>Try this</h3><p>Create a public service announcement on a social issue.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(328, 18, 5, 'Ethics in Applied Social Sciences', 'Professionals in applied social sciences follow strict ethical standards.', '<p>Professionals in applied social sciences follow strict ethical standards.</p><h3>Key points</h3><ul><li>Respect the dignity and rights of clients.</li><li>Keep information confidential.</li><li>Avoid conflicts of interest.</li></ul><h3>Try this</h3><p>Explain why confidentiality is important in counseling.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(329, 74, 1, 'Understanding Community', 'A community is a group of people who share a place, interests, or identity.', '<p>A community is a group of people who share a place, interests, or identity.</p><h3>Key points</h3><ul><li>Communities can be geographic or based on shared interests.</li><li>Every community has strengths and needs.</li><li>Knowing a community is the first step to helping it.</li></ul><h3>Try this</h3><p>Describe your community&#x27;s strengths and needs.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(330, 74, 2, 'Community Action', 'Community action is when people work together to bring change.', '<p>Community action is when people work together to bring change.</p><h3>Key points</h3><ul><li>It can be through volunteering, advocacy, or organizing.</li><li>Participation should be voluntary and inclusive.</li><li>Small actions can lead to big changes.</li></ul><h3>Try this</h3><p>List three community action projects that students can join.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(331, 74, 3, 'Solidarity and Citizenship', 'Solidarity means standing together for the common good.', '<p>Solidarity means standing together for the common good.</p><h3>Key points</h3><ul><li>Citizenship includes rights and responsibilities.</li><li>Filipino values like bayanihan show solidarity.</li><li>Active citizens care about people beyond their own family.</li></ul><h3>Try this</h3><p>Give an example of bayanihan you have seen in your community.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(332, 74, 4, 'Planning a Community Project', 'A good community project is planned with the community, not just for it.', '<p>A good community project is planned with the community, not just for it.</p><h3>Key points</h3><ul><li>Identify the need through surveys or interviews.</li><li>Set goals, tasks, timeline, and budget.</li><li>Involve community members in planning and doing the project.</li></ul><h3>Try this</h3><p>Draft a plan for a small community project.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(333, 74, 5, 'Evaluating Community Engagement', 'Evaluation shows whether a project made a difference.', '<p>Evaluation shows whether a project made a difference.</p><h3>Key points</h3><ul><li>Gather feedback from participants and the community.</li><li>Compare results with the original goals.</li><li>Use lessons learned to improve future projects.</li></ul><h3>Try this</h3><p>Create a short feedback form for a community project.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(334, 75, 1, 'Introduction to the Humanities', 'The humanities study human culture, expression, and values.', '<p>The humanities study human culture, expression, and values.</p><h3>Key points</h3><ul><li>Disciplines include art, literature, music, philosophy, and history.</li><li>They help us understand what it means to be human.</li><li>They develop critical thinking and empathy.</li></ul><h3>Try this</h3><p>Choose an artwork and explain what it says about human experience.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(335, 75, 2, 'Art Appreciation', 'Art appreciation is the ability to understand and value works of art.', '<p>Art appreciation is the ability to understand and value works of art.</p><h3>Key points</h3><ul><li>Elements of art include line, shape, color, texture, and space.</li><li>Principles include balance, contrast, and unity.</li><li>Context helps us understand an artwork&#x27;s meaning.</li></ul><h3>Try this</h3><p>Analyze a painting using the elements of art.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(336, 75, 3, 'Philippine Arts', 'Philippine arts reflect the history and identity of the Filipino people.', '<p>Philippine arts reflect the history and identity of the Filipino people.</p><h3>Key points</h3><ul><li>National Artists include Fernando Amorsolo, Lucio San Pedro, and Lino Brocka.</li><li>Indigenous arts include weaving, carving, and dance.</li><li>Contemporary Filipino artists explore modern issues.</li></ul><h3>Try this</h3><p>Research a National Artist and present one of their works.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(337, 75, 4, 'Introduction to Philosophy', 'Philosophy is the love of wisdom and the search for answers to life\'s big questions.', '<p>Philosophy is the love of wisdom and the search for answers to life&#x27;s big questions.</p><h3>Key points</h3><ul><li>It asks about knowledge, truth, reality, and ethics.</li><li>Philosophers include Socrates, Plato, and Aristotle.</li><li>Philosophy teaches us to reason clearly.</li></ul><h3>Try this</h3><p>Write your answer to the question &quot;What makes a good life?&quot;</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(338, 75, 5, 'The Human Person', 'Philosophy studies the human person as a being with body, mind, and freedom.', '<p>Philosophy studies the human person as a being with body, mind, and freedom.</p><h3>Key points</h3><ul><li>Humans are embodied spirits.</li><li>Freedom comes with responsibility.</li><li>Humans live in relation to others and the environment.</li></ul><h3>Try this</h3><p>Reflect on a decision that showed both your freedom and your responsibility.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(339, 76, 1, 'Introduction to the Social Sciences', 'The social sciences study how people live and interact in society.', '<p>The social sciences study how people live and interact in society.</p><h3>Key points</h3><ul><li>They include sociology, anthropology, psychology, economics, and political science.</li><li>They use research methods to understand social behavior.</li><li>They help address social issues.</li></ul><h3>Try this</h3><p>List the social science disciplines and one question each would ask about poverty.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(340, 76, 2, 'Culture and Society', 'Culture is the shared way of life of a group of people.', '<p>Culture is the shared way of life of a group of people.</p><h3>Key points</h3><ul><li>Culture includes beliefs, values, norms, language, and material objects.</li><li>Ethnocentrism judges other cultures by one&#x27;s own standards.</li><li>Cultural relativism understands cultures on their own terms.</li></ul><h3>Try this</h3><p>Give an example of ethnocentrism and how to avoid it.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(341, 76, 3, 'Socialization and Identity', 'Socialization is the process of learning how to live in society.', '<p>Socialization is the process of learning how to live in society.</p><h3>Key points</h3><ul><li>Agents of socialization include family, school, peers, and media.</li><li>Identity develops through socialization.</li><li>Socialization continues throughout life.</li></ul><h3>Try this</h3><p>Identify which agent of socialization has influenced you the most.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(342, 76, 4, 'Social Institutions', 'Social institutions are organized systems that meet society\'s needs.', '<p>Social institutions are organized systems that meet society&#x27;s needs.</p><h3>Key points</h3><ul><li>Major institutions include family, education, religion, economy, and government.</li><li>Institutions shape behavior and expectations.</li><li>Institutions change over time.</li></ul><h3>Try this</h3><p>Describe how the institution of education has changed in your lifetime.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(343, 76, 5, 'Social Change and Issues', 'Society changes over time because of technology, population, and ideas.', '<p>Society changes over time because of technology, population, and ideas.</p><h3>Key points</h3><ul><li>Social movements push for change.</li><li>Change can bring both progress and new problems.</li><li>Understanding change helps citizens respond wisely.</li></ul><h3>Try this</h3><p>Identify a social change in the Philippines in the last ten years and its effects.</p>', NULL, NULL, 1, NULL, '2026-10-04 23:57:16', NULL),
(344, 28, 6, 'module 6', '', NULL, 'uploads/modules/mod_6ac281965d71e.pdf', NULL, 1, 1, '2026-10-05 00:40:54', '2026-10-05 00:41:18');

--
-- Triggers `modules`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_module_delete` AFTER DELETE ON `modules` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.changed', OLD.course_id, NULL, NULL, NULL, OLD.id, 'all', OLD.title);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_module_insert` AFTER INSERT ON `modules` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.changed', NEW.course_id, NULL, NULL, NULL, NEW.id, 'all', NEW.title);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_module_update` AFTER UPDATE ON `modules` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.changed', NEW.course_id, NULL, NULL, NULL, NEW.id, 'all', NEW.title);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `module_progress`
--

CREATE TABLE `module_progress` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `module_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `completed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module_progress`
--

INSERT INTO `module_progress` (`id`, `student_id`, `module_id`, `course_id`, `completed_at`) VALUES
(11, 6, 4, 40, '2026-05-18 15:38:37'),
(12, 6, 3, 39, '2026-05-18 15:42:31'),
(13, 6, 5, 38, '2026-06-17 22:43:14'),
(17, 8, 6, 15, '2026-09-22 15:34:26'),
(19, 8, 8, 15, '2026-09-22 16:02:42'),
(20, 27, 149, 53, '2026-10-08 03:34:20'),
(21, 10, 8, 15, '2026-10-08 23:15:40'),
(22, 27, 150, 53, '2026-10-16 14:58:48');

--
-- Triggers `module_progress`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_progress_insert` AFTER INSERT ON `module_progress` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('module.progress', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NULL, NEW.student_id, NEW.module_id, 'teachers', NULL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `module_views`
--

CREATE TABLE `module_views` (
  `student_id` int(10) UNSIGNED NOT NULL,
  `module_id` int(10) UNSIGNED NOT NULL,
  `first_opened_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_opened_at` datetime NOT NULL DEFAULT current_timestamp(),
  `open_count` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module_views`
--

INSERT INTO `module_views` (`student_id`, `module_id`, `first_opened_at`, `last_opened_at`, `open_count`) VALUES
(6, 3, '2026-05-18 15:42:31', '2026-05-18 15:42:31', 1),
(6, 4, '2026-05-18 15:38:37', '2026-05-18 15:38:37', 1),
(6, 5, '2026-06-17 22:43:14', '2026-06-17 22:43:14', 1),
(8, 6, '2026-09-22 15:34:26', '2026-09-22 15:34:26', 1),
(8, 8, '2026-09-22 16:02:42', '2026-09-22 16:02:42', 1),
(10, 7, '2026-10-08 23:15:33', '2026-10-08 23:15:33', 1),
(10, 8, '2026-10-08 23:15:39', '2026-10-08 23:15:39', 1),
(10, 264, '2026-10-09 20:51:57', '2026-10-09 20:52:00', 2),
(27, 134, '2026-10-16 15:47:34', '2026-10-16 15:47:34', 1),
(27, 149, '2026-10-08 03:18:38', '2026-10-16 15:34:59', 4),
(27, 150, '2026-10-16 14:58:46', '2026-10-16 15:35:53', 3),
(27, 151, '2026-10-16 14:58:53', '2026-10-16 14:58:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`user_id`, `token`, `expires_at`, `used`) VALUES
(3, '461a4000d2d4cb88b41f63457e54149f3a7c90d9d744008598d6f1fd5304c465', '2026-07-04 21:09:26', 0),
(10, 'dd811819d7edadc89060cd9cca6684fe7ffa7659e1d9709ccd2e485d9704daf8', '2026-10-09 01:05:01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `perm_key` varchar(60) NOT NULL,
  `label` varchar(120) NOT NULL,
  `type` enum('view','manage','special') NOT NULL,
  `module` varchar(60) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`perm_key`, `label`, `type`, `module`, `sort_order`) VALUES
('assign_teachers', 'Assign Teachers', 'special', 'teachers', 22),
('enroll_students', 'Enroll Students', 'special', 'students', 12),
('manage_admin_accounts', 'Manage Admin Accounts', 'special', 'admin', 80),
('manage_banners', 'Manage Dashboard Banners', 'special', 'banners', 90),
('manage_classes', 'Manage Sections', 'manage', 'sections', 41),
('manage_modules', 'Manage Modules', 'manage', 'modules', 35),
('manage_students', 'Manage Students', 'manage', 'students', 11),
('manage_subjects', 'Manage Subjects', 'manage', 'subjects', 31),
('manage_teachers', 'Manage Teachers', 'manage', 'teachers', 21),
('publish_announcements', 'Publish Announcements', 'special', 'announcements', 70),
('view_classes', 'View Sections', 'view', 'sections', 40),
('view_enrollment', 'View Enrollment Records', 'view', 'enrollment', 50),
('view_reports', 'View Reports', 'view', 'reports', 60),
('view_students', 'View Students', 'view', 'students', 10),
('view_subjects', 'View Subjects', 'view', 'subjects', 30),
('view_teachers', 'View Teachers', 'view', 'teachers', 20);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Teacher who created it; NULL = shared by every section',
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `time_limit` smallint(5) UNSIGNED DEFAULT NULL,
  `max_score` decimal(6,2) NOT NULL DEFAULT 100.00,
  `passing_score` decimal(6,2) DEFAULT NULL,
  `open_at` datetime DEFAULT NULL,
  `close_at` datetime DEFAULT NULL,
  `quiz_type` enum('questions','file') NOT NULL DEFAULT 'questions',
  `question_count` int(10) UNSIGNED DEFAULT NULL,
  `points_per_question` decimal(5,2) DEFAULT NULL,
  `status` enum('Draft','Published') NOT NULL DEFAULT 'Published',
  `show_results` tinyint(1) NOT NULL DEFAULT 1,
  `show_correct_answers` tinyint(1) NOT NULL DEFAULT 0,
  `file_path` varchar(300) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `module_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `teacher_id`, `title`, `description`, `time_limit`, `max_score`, `passing_score`, `open_at`, `close_at`, `quiz_type`, `question_count`, `points_per_question`, `status`, `show_results`, `show_correct_answers`, `file_path`, `created_at`, `module_id`) VALUES
(8, 15, 15, 'the great', 'basta', 123, 100.00, NULL, '2026-09-09 21:13:00', NULL, 'questions', NULL, NULL, 'Published', 1, 0, NULL, '2026-09-21 21:14:06', 264),
(9, 15, 15, 'the great', '123', 50, 20.00, 15.00, NULL, '2026-09-22 18:34:00', 'questions', 10, 2.00, 'Published', 1, 1, NULL, '2026-09-22 16:43:58', 264),
(10, 15, 15, 'the great', 'jk', 50, 40.00, NULL, NULL, NULL, 'questions', 20, 2.00, 'Published', 1, 0, NULL, '2026-09-22 17:10:24', 264),
(11, 37, 15, 'quiz 1', 'module 1', 20, 60.00, 30.00, NULL, '2026-10-13 19:19:00', 'questions', 6, 2.00, 'Published', 1, 0, NULL, '2026-10-05 19:19:20', 89),
(12, 37, 15, 'Maikling Pagsusulit 1', 'Sagutan ang 4 na tanong tungkol sa Panitikang Asyano at i-upload ang sagot (PDF).', NULL, 20.00, NULL, '2026-10-03 00:02:57', '2026-10-09 00:02:57', 'file', NULL, NULL, 'Published', 1, 0, NULL, '2026-10-06 00:02:57', 89),
(13, 38, 14, 'test', '', 10, 10.00, 8.00, '2026-10-08 03:35:00', '2026-10-09 03:35:00', 'questions', 10, 1.00, 'Published', 1, 0, NULL, '2026-10-08 03:38:37', 134),
(14, 38, 14, 'Activity 1', '', NULL, 10.00, NULL, '2026-10-23 14:29:00', '2026-10-23 14:30:00', 'questions', 10, 1.00, 'Published', 1, 1, NULL, '2026-10-16 14:30:12', 134),
(15, 38, 14, 'AI Automation', '', NULL, 20.00, NULL, NULL, NULL, 'questions', 10, 2.00, 'Published', 1, 0, NULL, '2026-10-16 15:45:36', 134);

--
-- Triggers `quizzes`
--
DELIMITER $$
CREATE TRIGGER `trg_gb_quiz_delete` AFTER DELETE ON `quizzes` FOR EACH ROW BEGIN
  DELETE FROM gradebook_items WHERE source_type = 'quiz' AND source_id = OLD.id;
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.deleted', OLD.course_id, NULL, OLD.teacher_id, NULL, OLD.id, IF(OLD.status = 'Published', 'all', 'teachers'), OLD.title);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_gb_quiz_insert` AFTER INSERT ON `quizzes` FOR EACH ROW BEGIN
  INSERT IGNORE INTO gradebook_items (course_id, teacher_id, source_type, source_id, title, max_score, created_at)
  VALUES (NEW.course_id, NEW.teacher_id, 'quiz', NEW.id, NEW.title, COALESCE(NEW.max_score, 100), COALESCE(NEW.created_at, NOW()));
  IF NEW.status = 'Published' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.new', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  ELSE
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.draft', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'teachers', NEW.title);
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_gb_quiz_update` AFTER UPDATE ON `quizzes` FOR EACH ROW BEGIN
  UPDATE gradebook_items SET course_id = NEW.course_id, teacher_id = NEW.teacher_id, title = NEW.title, max_score = COALESCE(NEW.max_score, 100)
  WHERE source_type = 'quiz' AND source_id = NEW.id;
  IF NEW.status = 'Published' AND OLD.status <> 'Published' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.new', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  ELSEIF NEW.status <> 'Published' AND OLD.status = 'Published' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.deleted', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, 'all', NEW.title);
  ELSEIF NOT (NEW.title <=> OLD.title AND NEW.open_at <=> OLD.open_at AND NEW.close_at <=> OLD.close_at
              AND NEW.max_score <=> OLD.max_score AND NEW.time_limit <=> OLD.time_limit AND NEW.description <=> OLD.description) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.updated', NEW.course_id, NULL, NEW.teacher_id, NULL, NEW.id, IF(NEW.status = 'Published', 'all', 'teachers'), NEW.title);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `file_path` varchar(300) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_active_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `status` enum('In Progress','Submitted','Graded') NOT NULL DEFAULT 'In Progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `quiz_id`, `student_id`, `score`, `file_path`, `remarks`, `finished_at`, `started_at`, `submitted_at`, `status`) VALUES
(7, 8, 8, 100.00, NULL, NULL, '2026-09-22 15:59:29', '2026-09-22 15:34:36', '2026-09-22 15:59:29', 'Submitted'),
(8, 9, 8, 12.00, NULL, NULL, '2026-09-22 16:45:50', '2026-09-22 16:45:15', '2026-09-22 16:45:50', 'Submitted'),
(9, 12, 20, NULL, 'uploads/quiz_submissions/demo_ana_quiz1.pdf', NULL, '2026-10-05 00:02:57', '2026-10-05 00:02:57', '2026-10-05 00:02:57', 'Submitted'),
(10, 12, 21, NULL, 'uploads/quiz_submissions/demo_ben_quiz1.pdf', NULL, '2026-10-05 04:02:57', '2026-10-05 04:02:57', '2026-10-05 04:02:57', 'Submitted'),
(11, 12, 23, NULL, 'uploads/quiz_submissions/demo_diego_quiz1.pdf', NULL, '2026-10-05 19:02:57', '2026-10-05 19:02:57', '2026-10-05 19:02:57', 'Submitted'),
(12, 12, 24, NULL, 'uploads/quiz_submissions/demo_ella_quiz1.pdf', NULL, '2026-10-05 23:02:57', '2026-10-05 23:02:57', '2026-10-05 23:02:57', 'Submitted'),
(16, 13, 27, 5.00, NULL, NULL, '2026-10-08 03:39:03', '2026-10-08 03:38:44', '2026-10-08 03:39:03', 'Submitted'),
(17, 15, 27, 4.00, NULL, NULL, '2026-10-16 15:47:19', '2026-10-16 15:47:07', '2026-10-16 15:47:19', 'Submitted');

--
-- Triggers `quiz_attempts`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_attempt_insert` AFTER INSERT ON `quiz_attempts` FOR EACH ROW BEGIN
  IF NEW.status IN ('Submitted', 'Graded') THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.submitted', (SELECT course_id FROM quizzes WHERE id = NEW.quiz_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM quizzes WHERE id = NEW.quiz_id), NEW.student_id, NEW.quiz_id, 'teachers', (SELECT title FROM quizzes WHERE id = NEW.quiz_id));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_attempt_update` AFTER UPDATE ON `quiz_attempts` FOR EACH ROW BEGIN
  IF NEW.status <> OLD.status AND OLD.status = 'In Progress' THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('quiz.submitted', (SELECT course_id FROM quizzes WHERE id = NEW.quiz_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM quizzes WHERE id = NEW.quiz_id), NEW.student_id, NEW.quiz_id, 'teachers', (SELECT title FROM quizzes WHERE id = NEW.quiz_id));
  END IF;
  IF NOT (NEW.score <=> OLD.score AND NEW.remarks <=> OLD.remarks) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM quizzes WHERE id = NEW.quiz_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM quizzes WHERE id = NEW.quiz_id), NEW.student_id, NEW.quiz_id, 'all', (SELECT title FROM quizzes WHERE id = NEW.quiz_id));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_choices`
--

CREATE TABLE `quiz_choices` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `choice_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_choices`
--

INSERT INTO `quiz_choices` (`id`, `question_id`, `choice_text`, `is_correct`) VALUES
(6, 4, 'True', 1),
(7, 4, 'False', 0),
(8, 5, '123123', 1),
(39, 16, 'A. Hyper Text Markup Language', 1),
(40, 16, 'B. High Text Machine Language', 0),
(41, 16, 'C. Hyperlink Text Management Language', 0),
(42, 16, 'D. Home Tool Markup Language', 0),
(43, 17, '. HTML', 0),
(44, 17, 'CSS', 0),
(45, 17, 'PHP', 1),
(46, 17, 'no', 0),
(47, 18, 'A. To edit images', 0),
(48, 18, 'B. To store and manage data', 1),
(49, 18, 'C. To design websites', 0),
(50, 18, 'no', 0),
(51, 19, 'A. Monitor', 0),
(52, 19, 'B. Keyboard', 0),
(53, 19, 'C. Switch', 1),
(54, 19, 'D. Printer', 0),
(55, 20, 'A. Central Processing Unit', 1),
(56, 20, 'B. Computer Personal Unit', 0),
(57, 20, 'C. Central Program Utility', 0),
(58, 20, 'no', 0),
(59, 21, 'T', 1),
(60, 21, 'F', 0),
(61, 22, 'T', 0),
(62, 22, 'F', 1),
(63, 23, 'T', 1),
(64, 23, 'F', 0),
(65, 24, 'T', 1),
(66, 24, 'F', 0),
(67, 25, 'T', 0),
(68, 25, 'F', 1),
(69, 26, 'Knowledge', 1),
(70, 26, 'Competition', 0),
(71, 26, 'Escapism', 0),
(72, 26, 'Addiction', 0),
(73, 27, 'True', 0),
(74, 27, 'False', 1),
(75, 28, 'Only game strategies', 0),
(76, 28, 'Non-game-related topics', 1),
(77, 28, 'Real-life politics', 0),
(78, 28, 'Technical troubleshooting', 0),
(79, 29, 'True', 1),
(80, 29, 'False', 0),
(81, 30, 'Extrovert vs Introvert', 1),
(82, 30, 'Sensing vs Intuitive', 0),
(83, 30, 'Thinking vs Feeling', 0),
(84, 30, 'Judging vs Perceiving', 0),
(85, 31, 'True', 0),
(86, 31, 'False', 1),
(87, 32, 'Silent Generation', 0),
(88, 32, 'Boom Generation', 0),
(89, 32, 'Generation X', 0),
(90, 32, 'Millennial Generation', 1),
(91, 33, 'True', 1),
(92, 33, 'False', 0),
(93, 34, 'Competition', 0),
(94, 34, 'Escapism', 1),
(95, 34, 'Knowledge', 0),
(96, 34, 'Addiction', 0),
(97, 35, 'True', 0),
(98, 35, 'False', 1),
(99, 36, 'Present-oriented perception', 1),
(100, 36, 'Future-oriented perception', 0),
(101, 36, 'Objective judgment', 0),
(102, 36, 'Subjective judgment', 0),
(103, 37, 'True', 0),
(104, 37, 'False', 1),
(105, 38, 'Mastery', 1),
(106, 38, 'Knowledge', 0),
(107, 38, 'Escapism', 0),
(108, 38, 'Competition', 0),
(109, 39, 'True', 0),
(110, 39, 'False', 1),
(111, 40, 'Age', 0),
(112, 40, 'Income level', 0),
(113, 40, 'Personality type', 1),
(114, 40, 'Ethnicity', 0),
(115, 41, 'True', 0),
(116, 41, 'False', 1),
(117, 42, 'Boom Generation', 0),
(118, 42, 'Generation X', 1),
(119, 42, 'Millennial Generation', 0),
(120, 42, 'Silent Generation', 0),
(121, 43, 'True', 0),
(122, 43, 'False', 1),
(123, 44, 'Knowledge', 1),
(124, 44, 'Competition', 0),
(125, 44, 'Escapism', 0),
(126, 44, 'Addiction', 0),
(127, 45, 'True', 1),
(128, 45, 'False', 0),
(129, 46, 'Filipino', 1),
(130, 46, 'Tagalog', 0),
(131, 46, 'Pilipino', 0),
(132, 46, 'Bisaya', 0),
(133, 47, 'Nagpapakita ng direksyon', 1),
(134, 47, 'Nagpapakita ng pag-aari', 0),
(135, 47, 'Nagpapakita ng dahilan', 0),
(136, 47, 'Nagpapakita ng oras', 0),
(137, 48, 'Reduplication', 1),
(138, 48, 'Alliteration', 0),
(139, 48, 'Assonance', 0),
(140, 48, 'Onomatopoeia', 0),
(141, 49, '2', 0),
(142, 49, '4', 0),
(143, 49, '6', 0),
(144, 50, 'Beauty', 1),
(145, 50, 'Strength', 0),
(146, 50, 'Wisdom', 0),
(147, 50, 'Joy', 0),
(148, 51, 'Araw', 1),
(149, 51, 'Mabilis', 0),
(150, 51, 'Tumakbo', 0),
(151, 51, 'Masaya', 0),
(152, 52, 'haha', 1),
(153, 52, 'hahaha', 0),
(154, 53, 'ad', 1),
(155, 53, 'as', 0),
(156, 54, 'ad', 1),
(157, 54, 'as', 0),
(158, 55, 'ad', 0),
(159, 55, 'asd', 1),
(160, 56, 'as', 1),
(161, 56, 'asd', 0),
(162, 57, 'ad', 1),
(163, 57, 'as', 0),
(164, 58, 'as', 1),
(165, 58, 'ads', 0),
(166, 59, 'ad', 1),
(167, 59, 'asd', 0),
(168, 60, 'dra', 0),
(169, 60, 'as', 1),
(170, 61, 'asd', 0),
(171, 61, 'asdw', 1),
(172, 62, 'True', 1),
(173, 62, 'False', 0),
(174, 63, 'True', 1),
(175, 63, 'False', 0),
(176, 64, 'True', 1),
(177, 64, 'False', 0),
(178, 65, 'True', 0),
(179, 65, 'False', 1),
(180, 66, 'True', 0),
(181, 66, 'False', 1),
(182, 67, 'True', 1),
(183, 67, 'False', 0),
(184, 68, 'True', 1),
(185, 68, 'False', 0),
(186, 69, 'True', 0),
(187, 69, 'False', 1),
(188, 70, 'True', 1),
(189, 70, 'False', 0),
(190, 71, 'True', 0),
(191, 71, 'False', 1),
(192, 72, 'Increased manual labor', 0),
(193, 72, 'Higher production costs', 0),
(194, 72, 'Improved efficiency and consistency', 1),
(195, 72, 'Reduced product quality', 0),
(196, 73, 'A spreadsheet used for data entry', 0),
(197, 73, 'A robotic arm assembling parts based on visual inspection', 1),
(198, 73, 'A human supervisor checking inventory manually', 0),
(199, 73, 'A paper-based filing system', 0),
(200, 74, 'Computer Vision', 0),
(201, 74, 'Natural Language Processing', 1),
(202, 74, 'Quantum Computing', 0),
(203, 74, 'Blockchain', 0),
(204, 75, 'Chatbot', 0),
(205, 75, 'Robotic Process Automation (RPA)', 1),
(206, 75, 'Deep Learning Neural Network', 0),
(207, 75, 'Augmented Reality', 0),
(208, 76, 'A bug in the code that slows down processing', 0),
(209, 76, 'A preference for faster algorithms', 0),
(210, 76, 'Systematic unfairness in outcomes due to biased data or design', 1),
(211, 76, 'The tendency of algorithms to favor open-source software', 0),
(212, 77, 'Healthcare', 0),
(213, 77, 'Education', 0),
(214, 77, 'Agriculture', 0),
(215, 77, 'Traditional handcrafting without any digital component', 1),
(216, 78, 'Data Labeler', 1),
(217, 78, 'Typewriter Operator', 0),
(218, 78, 'Telegraph Operator', 0),
(219, 78, 'Film Reel Splicer', 0),
(220, 79, 'Reinforcement learning', 0),
(221, 79, 'Supervised learning', 0),
(222, 79, 'Unsupervised learning', 0),
(223, 79, 'Machine learning', 1),
(224, 80, 'It can replace all human workers', 0),
(225, 80, 'It enables handling unstructured data and complex tasks', 1),
(226, 80, 'It eliminates the need for any programming', 0),
(227, 80, 'It reduces the speed of task execution', 0),
(228, 81, 'Using AI to create new hardware devices', 0),
(229, 81, 'Automating a single repetitive task with a simple script', 0),
(230, 81, 'Integrating multiple AI and automation tools to automate end-to-end processes', 1),
(231, 81, 'Outsourcing all business processes to external AI vendors', 0);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','essay') NOT NULL DEFAULT 'multiple_choice',
  `points` decimal(5,2) NOT NULL DEFAULT 1.00,
  `identification_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `question_type`, `points`, `identification_answer`) VALUES
(4, 8, 'sadas', 'true_false', 1.00, NULL),
(5, 8, '12312', 'multiple_choice', 1.00, NULL),
(16, 9, 'What does HTML stand for?', 'multiple_choice', 2.00, NULL),
(17, 9, 'Which programming language is commonly used for web development on the server side?', 'multiple_choice', 2.00, NULL),
(18, 9, 'What is the main purpose of a database?', 'multiple_choice', 2.00, NULL),
(19, 9, 'Which device is used to connect multiple devices within a network?', 'multiple_choice', 2.00, NULL),
(20, 9, 'What does CPU stand for?', 'multiple_choice', 2.00, NULL),
(21, 9, 'HTML is used to structure the content of a webpage.', 'multiple_choice', 2.00, NULL),
(22, 9, 'CSS is mainly used to store data in a database.', 'multiple_choice', 2.00, NULL),
(23, 9, 'A password helps protect a user\'s account from unauthorized access.', 'multiple_choice', 2.00, NULL),
(24, 9, 'MySQL is a type of database management system.', 'multiple_choice', 2.00, NULL),
(25, 9, 'A computer can operate without any operating system in all normal situations.', 'multiple_choice', 2.00, NULL),
(26, 10, 'Which player motivation involves learning concepts or strategies while playing?', 'multiple_choice', 0.00, NULL),
(27, 10, 'Physical seclusion means a player prefers to play in public spaces.', 'true_false', 0.00, NULL),
(28, 10, 'In MMOs, players often communicate about topics ___', 'multiple_choice', 0.00, NULL),
(29, 10, 'Mastery motivation is primarily about achieving high scores and rankings.', 'true_false', 0.00, NULL),
(30, 10, 'Which MBTI dichotomy describes where a person directs their energy?', 'multiple_choice', 0.00, NULL),
(31, 10, 'Psychographics include observable statistics such as age and gender.', 'true_false', 0.00, NULL),
(32, 10, 'Which generation is characterized by growing up during the 1982–2000 economic boom?', 'multiple_choice', 0.00, NULL),
(33, 10, 'Social interaction can occur both in real life and within the game environment.', 'true_false', 0.00, NULL),
(34, 10, 'Which motivation is most associated with players seeking to escape real-life stress?', 'multiple_choice', 0.00, NULL),
(35, 10, 'Addiction motivation is considered a passive form of entertainment.', 'true_false', 0.00, NULL),
(36, 10, 'What does the \'S\' in the MBTI Sensing (S) vs Intuitive (N) dimension represent?', 'multiple_choice', 0.00, NULL),
(37, 10, 'Geographic targeting can allow a game to use a single language for all players worldwide.', 'true_false', 0.00, NULL),
(38, 10, 'Which player motivation is driven by the desire to dominate the game world and improve skills?', 'multiple_choice', 0.00, NULL),
(39, 10, 'Players motivated by competition are less likely to engage in cooperative gameplay.', 'true_false', 0.00, NULL),
(40, 10, 'Which demographic factor is NOT typically included in player demographics?', 'multiple_choice', 0.00, NULL),
(41, 10, 'The Silent Generation experienced World War II as adults.', 'true_false', 0.00, NULL),
(42, 10, 'Which generation is most likely to appreciate games that emphasize independent, risk‑taking characters?', 'multiple_choice', 0.00, NULL),
(43, 10, 'Physical seclusion contradicts the concept of social interaction in gaming.', 'true_false', 0.00, NULL),
(44, 10, 'Which motivation can be unconscious yet still influence gameplay?', 'multiple_choice', 0.00, NULL),
(45, 10, 'Understanding the target audience helps developers market games cost‑effectively.', 'true_false', 0.00, NULL),
(46, 11, 'Ano ang opisyal na tawag sa pambansang wika ng Pilipinas?', 'multiple_choice', 0.00, NULL),
(47, 11, 'Alin sa mga sumusunod ang tamang gamit ng pang-ukol na \"sa\"?', 'multiple_choice', 0.00, NULL),
(48, 11, 'Ano ang tawag sa pag-uulit ng tunog sa dulo ng salita?', 'multiple_choice', 0.00, NULL),
(49, 11, 'Sa alfabeto ng Filipino, ilang titik ang may tuldik (diacritics)?', 'multiple_choice', 0.00, NULL),
(50, 11, 'Ano ang ibig sabihin ng salitang \"kagandahan\"?', 'multiple_choice', 0.00, NULL),
(51, 11, 'Alin sa mga sumusunod ang halimbawa ng pangngalan?', 'multiple_choice', 0.00, NULL),
(52, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(53, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(54, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(55, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(56, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(57, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(58, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(59, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(60, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(61, 13, 'What is your name?', 'multiple_choice', 1.00, NULL),
(62, 14, 'Children have a higher metabolic rate than adults.', 'true_false', 1.00, NULL),
(63, 14, 'The recommended amount of sleep for teenagers (13-18) is 8-10 hours per night.', 'true_false', 1.00, NULL),
(64, 14, 'Play is considered essential for cognitive development in early childhood.', 'true_false', 1.00, NULL),
(65, 14, 'Vaccines can cause autism in children.', 'true_false', 1.00, NULL),
(66, 14, 'The average child can read at grade level by age 5.', 'true_false', 1.00, NULL),
(67, 14, 'Screen time exceeding 2 hours per day is linked to attention problems in kids.', 'true_false', 1.00, NULL),
(68, 14, 'Children\'s brains reach about 90% of adult size by age 6.', 'true_false', 1.00, NULL),
(69, 14, 'The majority of childhood obesity cases are linked to genetic factors alone.', 'true_false', 1.00, NULL),
(70, 14, 'Early exposure to music can improve language development in toddlers.', 'true_false', 1.00, NULL),
(71, 14, 'Children under 12 should not consume any caffeine-containing beverages.', 'true_false', 1.00, NULL),
(72, 15, 'What is the primary benefit of AI automation in manufacturing?', 'multiple_choice', 2.00, NULL),
(73, 15, 'Which of the following is an example of AI-driven process automation?', 'multiple_choice', 2.00, NULL),
(74, 15, 'What technology enables AI systems to understand and generate human language?', 'multiple_choice', 2.00, NULL),
(75, 15, 'Which AI automation tool is commonly used for repetitive digital tasks like data scraping?', 'multiple_choice', 2.00, NULL),
(76, 15, 'In the context of AI ethics, what does \'algorithmic bias\' refer to?', 'multiple_choice', 2.00, NULL),
(77, 15, 'Which sector is least likely to be transformed by AI automation in the next decade?', 'multiple_choice', 2.00, NULL),
(78, 15, 'AI automation can lead to job displacement, but it also creates new roles. Which of the following is a new job type emerging from AI adoption?', 'multiple_choice', 2.00, NULL),
(79, 15, 'Which AI technique is primarily used for automating decision-making based on large datasets?', 'multiple_choice', 2.00, NULL),
(80, 15, 'What is a key advantage of robotic process automation (RPA) when combined with AI?', 'multiple_choice', 2.00, NULL),
(81, 15, 'Which of the following best describes \"hyperautomation\"?', 'multiple_choice', 2.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_responses`
--

CREATE TABLE `quiz_responses` (
  `id` int(10) UNSIGNED NOT NULL,
  `attempt_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `choice_id` int(10) UNSIGNED DEFAULT NULL,
  `answer_text` varchar(500) DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `points_earned` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_responses`
--

INSERT INTO `quiz_responses` (`id`, `attempt_id`, `question_id`, `choice_id`, `answer_text`, `is_correct`, `points_earned`) VALUES
(1, 8, 16, 39, NULL, 1, 2.00),
(2, 8, 17, 44, NULL, 0, 0.00),
(3, 8, 18, 48, NULL, 1, 2.00),
(4, 8, 19, 51, NULL, 0, 0.00),
(5, 8, 20, 55, NULL, 1, 2.00),
(6, 8, 21, 59, NULL, 1, 2.00),
(7, 8, 22, 62, NULL, 1, 2.00),
(8, 8, 23, 63, NULL, 1, 2.00),
(9, 8, 24, 66, NULL, 0, 0.00),
(10, 8, 25, 67, NULL, 0, 0.00),
(11, 16, 52, 152, NULL, 1, 1.00),
(12, 16, 53, 155, NULL, 0, 0.00),
(13, 16, 54, 157, NULL, 0, 0.00),
(14, 16, 55, 158, NULL, 0, 0.00),
(15, 16, 56, 160, NULL, 1, 1.00),
(16, 16, 57, 162, NULL, 1, 1.00),
(17, 16, 58, 164, NULL, 1, 1.00),
(18, 16, 59, 167, NULL, 0, 0.00),
(19, 16, 60, 169, NULL, 1, 1.00),
(20, 16, 61, 170, NULL, 0, 0.00),
(21, 17, 72, 193, NULL, 0, 0.00),
(22, 17, 73, 197, NULL, 1, 2.00),
(24, 17, 75, 205, NULL, 1, 2.00),
(25, 17, 76, 208, NULL, 0, 0.00),
(27, 17, 78, 219, NULL, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` char(24) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` enum('SHS','HS') NOT NULL DEFAULT 'HS',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `name`, `level`, `sort_order`, `status`, `created_at`) VALUES
(1, 'Grade 7', 'HS', 10, 'Active', '2026-10-04 21:17:26'),
(2, 'Grade 8', 'HS', 20, 'Active', '2026-10-04 21:17:26'),
(3, 'Grade 9', 'HS', 30, 'Active', '2026-10-04 21:17:26'),
(4, 'Grade 10', 'HS', 40, 'Active', '2026-10-04 21:17:26'),
(5, 'Grade 11 - ABM-A', 'SHS', 110, 'Active', '2026-10-04 21:17:26'),
(6, 'Grade 11 - ABM-B', 'SHS', 120, 'Active', '2026-10-04 21:17:26'),
(7, 'Grade 11 - GAS-A', 'SHS', 130, 'Active', '2026-10-04 21:17:26'),
(8, 'Grade 11 - GAS-B', 'SHS', 140, 'Active', '2026-10-04 21:17:26'),
(9, 'Grade 11 - HUMSS-A', 'SHS', 150, 'Active', '2026-10-04 21:17:26'),
(10, 'Grade 11 - HUMSS-B', 'SHS', 160, 'Active', '2026-10-04 21:17:26'),
(11, 'Grade 12 - ABM-A', 'SHS', 210, 'Active', '2026-10-04 21:17:26'),
(12, 'Grade 12 - ABM-B', 'SHS', 220, 'Active', '2026-10-04 21:17:26'),
(13, 'Grade 12 - GAS-A', 'SHS', 230, 'Active', '2026-10-04 21:17:26'),
(14, 'Grade 12 - GAS-B', 'SHS', 240, 'Active', '2026-10-04 21:17:26'),
(15, 'Grade 12 - HUMSS-A', 'SHS', 250, 'Active', '2026-10-04 21:17:26'),
(16, 'Grade 12 - HUMSS-B', 'SHS', 260, 'Active', '2026-10-04 21:17:26'),
(18, 'Grade 11 - STEM-A', 'SHS', 170, 'Active', '2026-10-04 22:06:57'),
(19, 'Grade 11 - STEM-B', 'SHS', 180, 'Active', '2026-10-04 22:06:57'),
(20, 'Grade 12 - STEM-A', 'SHS', 270, 'Active', '2026-10-04 22:06:57'),
(21, 'Grade 12 - STEM-B', 'SHS', 280, 'Active', '2026-10-04 22:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `student_awards`
--

CREATE TABLE `student_awards` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `award_type_id` int(10) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `status` enum('Active','Revoked') NOT NULL DEFAULT 'Active',
  `awarded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int(10) UNSIGNED DEFAULT NULL,
  `request_token` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_awards`
--

INSERT INTO `student_awards` (`id`, `student_id`, `teacher_id`, `course_id`, `award_type_id`, `reason`, `school_year`, `status`, `awarded_at`, `revoked_at`, `revoked_by`, `request_token`, `created_at`, `updated_at`) VALUES
(1, 10, 18, 30, 8, 'Awarded for maintaining consistent performance, completing learning activities responsibly, and showing continuous effort and dedication throughout the subject.', '2025-2026', 'Active', '2026-09-23 17:43:21', NULL, NULL, '19181de0-cd10-4468-826c-a8612214c43e', '2026-09-23 17:43:21', '2026-09-23 17:53:48'),
(2, 10, 18, 33, 5, 'Awarded for consistently showing strong mathematical reasoning, accuracy in solving problems, and excellent performance in Mathematics activities and assessments.', '2025-2026', 'Active', '2026-09-23 17:47:35', NULL, NULL, 'ac51ca12-8b8e-4d05-937d-2e99c02c244e', '2026-09-23 17:47:35', '2026-09-23 17:53:41'),
(3, 10, 18, 30, 6, 'Awarded for demonstrating excellent understanding of scientific concepts, active participation in learning activities, and consistent performance in Science assessments.', '2025-2026', 'Active', '2026-09-23 17:48:05', NULL, NULL, '69038456-ef80-4926-aa54-52fa19ebdcf8', '2026-09-23 17:48:05', '2026-09-23 17:53:31'),
(4, 10, 18, 33, 4, 'Awarded for demonstrating strong programming skills, logical thinking, and effective problem-solving in programming activities, exercises, and projects.', '2025-2026', 'Active', '2026-09-23 17:48:32', NULL, NULL, '78d237b2-aee1-470a-a534-c768fd816d9a', '2026-09-23 17:48:32', '2026-09-23 17:53:24'),
(5, 10, 18, 30, 5, 'Awarded for demonstrating excellent understanding of mathematical concepts, strong problem-solving skills, and consistent performance in Mathematics activities and assessments.', '2025-2026', 'Active', '2026-09-23 17:51:05', NULL, NULL, '2b7e5572-56ea-4adb-bbbd-bf2af40111b1', '2026-09-23 17:51:05', '2026-09-23 17:53:08'),
(6, 27, 14, 53, 3, 'The student demonstrated excellent academic performance and consistently showed a strong understanding of the lessons.', '2025-2026', 'Active', '2026-10-08 04:25:40', NULL, NULL, '8de0cc53-5930-4103-a6dd-18702cef625e', '2026-10-08 04:25:40', '2026-10-08 04:25:40'),
(7, 27, 14, 53, 9, 'The student actively participated in class discussions, activities, and learning exercises.', '2025-2026', 'Active', '2026-10-08 04:25:59', NULL, NULL, 'a9ad6e07-8a12-42c0-b3aa-312ba336744b', '2026-10-08 04:25:59', '2026-10-08 04:25:59'),
(8, 27, 14, 38, 8, 'The student maintained consistent and reliable performance throughout the academic period.', '2025-2026', 'Active', '2026-10-08 04:26:22', NULL, NULL, '1dfcd5a8-246a-4a77-87c6-fa573eef1cb3', '2026-10-08 04:26:22', '2026-10-08 04:26:22'),
(9, 27, 14, 53, 10, 'The student demonstrated strong leadership skills by taking responsibility, guiding classmates, and contributing to group success.', '2025-2026', 'Active', '2026-10-08 04:26:37', NULL, NULL, 'c086625b-9297-424b-9316-82cf7fb933a2', '2026-10-08 04:26:37', '2026-10-08 04:26:37'),
(10, 27, 14, 53, 5, 'The student showed excellent understanding of mathematical concepts and performed well in Mathematics activities.', '2025-2026', 'Active', '2026-10-08 04:26:56', NULL, NULL, 'd3947cf1-7627-4871-a1b2-bfbcfb5a2197', '2026-10-08 04:26:56', '2026-10-08 04:26:56'),
(11, 27, 14, 53, 7, 'The student showed significant improvement in academic performance, skills, and participation.', '2025-2026', 'Active', '2026-10-08 04:27:11', NULL, NULL, '30379e3c-1f64-4dc1-8473-3c1c3feebe4a', '2026-10-08 04:27:11', '2026-10-08 04:27:11'),
(12, 27, 14, 53, 2, 'The student consistently demonstrated excellent performance and exceeded expectations in academic activities.', '2025-2026', 'Active', '2026-10-08 04:27:27', NULL, NULL, '02938308-ee1c-45c6-94ee-1dd222f68881', '2026-10-08 04:27:27', '2026-10-08 04:27:27');

--
-- Triggers `student_awards`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_award_insert` AFTER INSERT ON `student_awards` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('award.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NEW.teacher_id, NEW.student_id, NEW.id, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_award_update` AFTER UPDATE ON `student_awards` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('award.changed', NEW.course_id, (SELECT section_dept FROM users WHERE id = NEW.student_id), NEW.teacher_id, NEW.student_id, NEW.id, 'all', NULL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(300) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `graded_at` datetime DEFAULT NULL,
  `status` enum('Submitted','Graded','Late') NOT NULL DEFAULT 'Submitted',
  `attempt_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'How many times this student has submitted this assignment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `assignment_id`, `student_id`, `file_path`, `remarks`, `score`, `submitted_at`, `graded_at`, `status`, `attempt_count`) VALUES
(1, 5, 19, 'uploads/submissions/sample_submission_juan.pdf', NULL, NULL, '2026-10-05 17:48:43', NULL, 'Submitted', 1),
(2, 8, 20, 'uploads/submissions/demo_ana_tanka.pdf', NULL, NULL, '2026-10-05 04:02:57', NULL, 'Submitted', 1),
(3, 8, 21, 'uploads/submissions/demo_ben_tanka.pdf', NULL, NULL, '2026-10-05 09:02:57', NULL, 'Submitted', 1),
(4, 8, 22, 'uploads/submissions/demo_carla_tanka.pdf', NULL, NULL, '2026-10-05 18:02:57', NULL, 'Submitted', 1),
(5, 8, 23, 'uploads/submissions/demo_diego_tanka.pdf', NULL, NULL, '2026-10-05 22:02:57', NULL, 'Submitted', 1),
(6, 9, 20, 'uploads/submissions/demo_ana_sanaysay.pdf', NULL, NULL, '2026-10-02 00:02:57', NULL, 'Submitted', 1),
(7, 9, 22, 'uploads/submissions/demo_carla_sanaysay.pdf', NULL, NULL, '2026-10-03 00:02:57', NULL, 'Submitted', 1),
(8, 9, 24, 'uploads/submissions/demo_ella_sanaysay.pdf', '', 100.00, '2026-10-03 00:02:57', '2026-10-06 00:08:34', 'Graded', 1),
(9, 10, 27, 'uploads/submissions/sub_6ac69c15c7bf7.pdf', 'excellent!', 95.00, '2026-10-08 03:23:01', '2026-10-08 03:23:41', 'Graded', 1),
(10, 11, 27, NULL, '', 30.00, '2026-10-08 04:15:51', '2026-10-08 04:15:51', 'Graded', 0);

--
-- Triggers `submissions`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_submission_delete` AFTER DELETE ON `submissions` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM assignments WHERE id = OLD.assignment_id), (SELECT section_dept FROM users WHERE id = OLD.student_id), (SELECT teacher_id FROM assignments WHERE id = OLD.assignment_id), OLD.student_id, OLD.assignment_id, 'all', (SELECT title FROM assignments WHERE id = OLD.assignment_id));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_submission_insert` AFTER INSERT ON `submissions` FOR EACH ROW BEGIN
  IF NEW.attempt_count > 0 THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('submission.new', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'teachers', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  END IF;
  IF NEW.score IS NOT NULL OR (NEW.remarks IS NOT NULL AND NEW.remarks <> '') THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'all', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_submission_update` AFTER UPDATE ON `submissions` FOR EACH ROW BEGIN
  IF NEW.attempt_count > OLD.attempt_count THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('submission.new', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'teachers', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  ELSEIF NOT (NEW.score <=> OLD.score AND NEW.remarks <=> OLD.remarks) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('grade.updated', (SELECT course_id FROM assignments WHERE id = NEW.assignment_id), (SELECT section_dept FROM users WHERE id = NEW.student_id), (SELECT teacher_id FROM assignments WHERE id = NEW.assignment_id), NEW.student_id, NEW.assignment_id, 'all', (SELECT title FROM assignments WHERE id = NEW.assignment_id));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sync_events`
--

CREATE TABLE `sync_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kind` varchar(40) NOT NULL COMMENT 'e.g. assignment.new, submission.new, grade.updated',
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `section` varchar(100) DEFAULT NULL COMMENT 'student section for student-specific events',
  `owner_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'teacher who owns the item (NULL = shared)',
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'the one user the event is about (student / teacher / account)',
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `audience` enum('all','students','teachers','admins') NOT NULL DEFAULT 'all',
  `title` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sync_events`
--

INSERT INTO `sync_events` (`id`, `kind`, `course_id`, `section`, `owner_id`, `user_id`, `ref_id`, `audience`, `title`, `created_at`) VALUES
(1, 'announcement.new', 15, NULL, NULL, NULL, 8, 'all', 'check', '2026-09-22 15:41:05'),
(2, 'announcement.new', 15, NULL, 15, NULL, 9, 'all', 'reminder', '2026-09-22 15:43:47'),
(3, 'announcement.new', 15, NULL, NULL, NULL, 10, 'students', 'meeting', '2026-09-22 19:20:43'),
(4, 'submission.new', 37, 'Grade 9', 15, 20, 9, 'teachers', 'Gawain 3: Sanaysay', '2026-10-02 00:02:57'),
(5, 'submission.new', 37, 'Grade 9', 15, 22, 9, 'teachers', 'Gawain 3: Sanaysay', '2026-10-03 00:02:57'),
(6, 'submission.new', 37, 'Grade 9', 15, 24, 9, 'teachers', 'Gawain 3: Sanaysay', '2026-10-03 00:02:57'),
(7, 'submission.new', 37, 'Grade 9', 15, 20, 8, 'teachers', 'Gawain 2: Ang Tanka', '2026-10-05 04:02:57'),
(8, 'submission.new', 37, 'Grade 9', 15, 21, 8, 'teachers', 'Gawain 2: Ang Tanka', '2026-10-05 09:02:57'),
(9, 'submission.new', 37, 'Grade 9', 15, 19, 5, 'teachers', 'Gawain 1: Ang Haiku', '2026-10-05 17:48:43'),
(10, 'submission.new', 37, 'Grade 9', 15, 22, 8, 'teachers', 'Gawain 2: Ang Tanka', '2026-10-05 18:02:57'),
(11, 'submission.new', 37, 'Grade 9', 15, 23, 8, 'teachers', 'Gawain 2: Ang Tanka', '2026-10-05 22:02:57'),
(19, 'grade.updated', 37, 'Grade 9', 15, 24, 9, 'all', 'Gawain 3: Sanaysay', '2026-10-06 00:08:34'),
(20, 'account.changed', NULL, NULL, NULL, 27, NULL, 'admins', NULL, '2026-10-08 00:20:57'),
(21, 'account.changed', NULL, NULL, NULL, 28, NULL, 'admins', NULL, '2026-10-08 00:22:10'),
(22, 'attendance.updated', 4, 'Grade 11 - ABM-A', 14, 10, NULL, 'all', '2026-10-08', '2026-10-08 01:44:41'),
(23, 'enrollment.changed', 2, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(24, 'enrollment.changed', 5, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(25, 'enrollment.changed', 13, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(26, 'enrollment.changed', 19, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(27, 'enrollment.changed', 20, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(28, 'enrollment.changed', 6, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(29, 'enrollment.changed', 8, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(30, 'enrollment.changed', 10, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(31, 'enrollment.changed', 11, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(32, 'enrollment.changed', 12, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(33, 'enrollment.changed', 13, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(34, 'enrollment.changed', 14, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(35, 'enrollment.changed', 15, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(36, 'enrollment.changed', 16, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(37, 'enrollment.changed', 17, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(38, 'enrollment.changed', 11, 'Grade 11 - ABM-A', NULL, 10, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(39, 'enrollment.changed', 30, 'Grade 11 - ABM-A', NULL, 10, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(40, 'enrollment.changed', 31, 'Grade 11 - ABM-A', NULL, 10, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(41, 'enrollment.changed', 32, 'Grade 11 - ABM-A', NULL, 10, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(42, 'enrollment.changed', 33, 'Grade 11 - ABM-A', NULL, 10, NULL, 'all', NULL, '2026-10-08 02:22:16'),
(43, 'account.changed', NULL, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 02:37:10'),
(44, 'enrollment.changed', 38, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(45, 'enrollment.changed', 41, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(46, 'enrollment.changed', 39, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(47, 'enrollment.changed', 40, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(48, 'enrollment.changed', 53, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(49, 'enrollment.changed', 54, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(50, 'enrollment.changed', 55, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(51, 'enrollment.changed', 56, 'Grade 10', NULL, 27, NULL, 'all', NULL, '2026-10-08 03:16:24'),
(52, 'class.changed', 38, 'Grade 10', NULL, 14, 1, 'all', NULL, '2026-10-08 03:21:19'),
(53, 'class.changed', 38, 'Grade 10', NULL, 3, 1, 'all', NULL, '2026-10-08 03:21:19'),
(54, 'assignment.new', 38, NULL, 14, NULL, 10, 'all', 'chap1', '2026-10-08 03:22:20'),
(55, 'submission.new', 38, 'Grade 10', 14, 27, 10, 'teachers', 'chap1', '2026-10-08 03:23:01'),
(56, 'grade.updated', 38, 'Grade 10', 14, 27, 10, 'all', 'chap1', '2026-10-08 03:23:41'),
(57, 'attendance.updated', 38, 'Grade 10', 14, 27, NULL, 'all', '2026-10-08', '2026-10-08 03:24:17'),
(58, 'attendance.updated', 38, 'Grade 10', 14, 6, NULL, 'all', '2026-10-08', '2026-10-08 03:24:17'),
(59, 'attendance.updated', 38, 'Grade 10', 14, 27, NULL, 'all', '2026-10-08', '2026-10-08 03:24:27'),
(60, 'attendance.updated', 38, 'Grade 10', 14, 6, NULL, 'all', '2026-10-08', '2026-10-08 03:24:27'),
(61, 'attendance.updated', 38, 'Grade 10', 14, 27, NULL, 'all', '2026-10-08', '2026-10-08 03:24:57'),
(62, 'module.progress', 53, 'Grade 10', NULL, 27, 149, 'teachers', NULL, '2026-10-08 03:34:20'),
(63, 'quiz.new', 38, NULL, NULL, NULL, 13, 'all', 'test', '2026-10-08 03:38:37'),
(64, 'quiz.submitted', 38, 'Grade 10', 14, 27, 13, 'teachers', 'test', '2026-10-08 03:39:03'),
(65, 'grade.updated', 38, 'Grade 10', 14, 27, 13, 'all', 'test', '2026-10-08 03:39:03'),
(66, 'assignment.new', 53, NULL, 14, NULL, 11, 'all', 'testing', '2026-10-08 04:14:43'),
(67, 'grade.updated', 53, 'Grade 10', 14, 27, 11, 'all', 'testing', '2026-10-08 04:15:51'),
(68, 'award.changed', 53, 'Grade 10', 14, 27, 6, 'all', NULL, '2026-10-08 04:25:40'),
(69, 'award.changed', 53, 'Grade 10', 14, 27, 7, 'all', NULL, '2026-10-08 04:25:59'),
(70, 'award.changed', 38, 'Grade 10', 14, 27, 8, 'all', NULL, '2026-10-08 04:26:22'),
(71, 'award.changed', 53, 'Grade 10', 14, 27, 9, 'all', NULL, '2026-10-08 04:26:37'),
(72, 'award.changed', 53, 'Grade 10', 14, 27, 10, 'all', NULL, '2026-10-08 04:26:56'),
(73, 'award.changed', 53, 'Grade 10', 14, 27, 11, 'all', NULL, '2026-10-08 04:27:11'),
(74, 'award.changed', 53, 'Grade 10', 14, 27, 12, 'all', NULL, '2026-10-08 04:27:27'),
(77, 'module.progress', 15, 'Grade 11 - ABM-A', NULL, 10, 8, 'teachers', NULL, '2026-10-08 23:15:40'),
(78, 'banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL, '2026-10-08 23:27:07'),
(79, 'banner.changed', NULL, NULL, NULL, NULL, NULL, 'all', NULL, '2026-10-08 23:27:08'),
(80, 'account.changed', NULL, NULL, NULL, 29, NULL, 'admins', NULL, '2026-10-09 14:03:18'),
(81, 'assignment.new', 53, NULL, 14, NULL, 12, 'all', 'assignment', '2026-10-09 14:08:52'),
(84, 'subject.changed', NULL, NULL, NULL, NULL, NULL, 'admins', 'test', '2026-10-09 19:58:06'),
(85, 'subject.changed', NULL, NULL, NULL, NULL, NULL, 'admins', 'Computer Programming', '2026-10-09 19:58:10'),
(87, 'subject.changed', NULL, NULL, NULL, NULL, NULL, 'admins', 'test', '2026-10-09 19:58:32'),
(88, 'assignment.new', 38, NULL, 14, NULL, 13, 'all', 'test', '2026-10-09 20:26:46'),
(89, 'assignment.new', 38, NULL, 14, NULL, 14, 'all', 'test', '2026-10-09 20:29:59'),
(90, 'enrollment.changed', 53, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(91, 'enrollment.changed', 54, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(92, 'enrollment.changed', 55, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(93, 'enrollment.changed', 56, 'Grade 10', NULL, 6, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(94, 'enrollment.changed', 5, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(95, 'enrollment.changed', 2, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(96, 'enrollment.changed', 57, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(97, 'enrollment.changed', 21, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(98, 'enrollment.changed', 23, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(99, 'enrollment.changed', 24, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(100, 'enrollment.changed', 71, 'Grade 11 - HUMSS-B', NULL, 8, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(101, 'enrollment.changed', 34, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(102, 'enrollment.changed', 35, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(103, 'enrollment.changed', 36, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(104, 'enrollment.changed', 49, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(105, 'enrollment.changed', 50, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(106, 'enrollment.changed', 51, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(107, 'enrollment.changed', 52, 'Grade 9', NULL, 19, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(108, 'enrollment.changed', 34, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(109, 'enrollment.changed', 35, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(110, 'enrollment.changed', 36, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(111, 'enrollment.changed', 49, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(112, 'enrollment.changed', 50, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(113, 'enrollment.changed', 51, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(114, 'enrollment.changed', 52, 'Grade 9', NULL, 20, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(115, 'enrollment.changed', 34, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(116, 'enrollment.changed', 35, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(117, 'enrollment.changed', 36, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(118, 'enrollment.changed', 49, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(119, 'enrollment.changed', 50, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(120, 'enrollment.changed', 51, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(121, 'enrollment.changed', 52, 'Grade 9', NULL, 21, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(122, 'enrollment.changed', 34, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(123, 'enrollment.changed', 35, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(124, 'enrollment.changed', 36, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(125, 'enrollment.changed', 49, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(126, 'enrollment.changed', 50, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(127, 'enrollment.changed', 51, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(128, 'enrollment.changed', 52, 'Grade 9', NULL, 22, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(129, 'enrollment.changed', 34, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(130, 'enrollment.changed', 35, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(131, 'enrollment.changed', 36, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(132, 'enrollment.changed', 49, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(133, 'enrollment.changed', 50, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(134, 'enrollment.changed', 51, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(135, 'enrollment.changed', 52, 'Grade 9', NULL, 23, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(136, 'enrollment.changed', 34, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(137, 'enrollment.changed', 35, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(138, 'enrollment.changed', 36, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(139, 'enrollment.changed', 49, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(140, 'enrollment.changed', 50, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(141, 'enrollment.changed', 51, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(142, 'enrollment.changed', 52, 'Grade 9', NULL, 24, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(143, 'enrollment.changed', 28, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(144, 'enrollment.changed', 25, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(145, 'enrollment.changed', 26, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(146, 'enrollment.changed', 27, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(147, 'enrollment.changed', 29, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(148, 'enrollment.changed', 42, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(149, 'enrollment.changed', 43, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(150, 'enrollment.changed', 44, 'Grade 7', NULL, 29, NULL, 'all', NULL, '2026-10-09 21:25:56'),
(151, 'quiz.new', 38, NULL, NULL, NULL, 14, 'all', 'Activity 1', '2026-10-16 14:30:12'),
(152, 'module.progress', 53, 'Grade 10', NULL, 27, 150, 'teachers', NULL, '2026-10-16 14:58:48'),
(153, 'quiz.new', 38, NULL, NULL, NULL, 15, 'all', 'AI Automation', '2026-10-16 15:45:36'),
(154, 'quiz.submitted', 38, 'Grade 10', 14, 27, 15, 'teachers', 'AI Automation', '2026-10-16 15:47:19'),
(155, 'grade.updated', 38, 'Grade 10', 14, 27, 15, 'all', 'AI Automation', '2026-10-16 15:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `section` varchar(100) NOT NULL,
  `school_year` varchar(20) NOT NULL DEFAULT '2025-2026',
  `semester` enum('1st','2nd','Summer') NOT NULL DEFAULT '1st',
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `teacher_id`, `course_id`, `section`, `school_year`, `semester`, `assigned_at`) VALUES
(1, 14, 38, 'Grade 10', '2025-2026', '1st', '2026-05-13 20:06:23'),
(2, 3, 41, 'Grade 10', '2025-2026', '1st', '2026-05-13 20:06:31'),
(3, 3, 39, 'Grade 10', '2025-2026', '1st', '2026-05-13 20:06:44'),
(4, 3, 40, 'Grade 10', '2025-2026', '1st', '2026-05-13 20:06:53'),
(5, 3, 3, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-05-16 18:35:41'),
(6, 15, 2, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-09-21 21:10:37'),
(7, 15, 15, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-09-21 21:10:37'),
(8, 15, 4, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-09-22 19:11:19'),
(9, NULL, 28, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(10, NULL, 25, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(11, NULL, 26, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(12, NULL, 27, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(13, NULL, 29, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(14, NULL, 42, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(15, NULL, 43, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(16, NULL, 44, 'Grade 7', '2025-2026', '1st', '2026-10-04 21:53:11'),
(17, NULL, 33, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(18, NULL, 30, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(19, NULL, 31, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(20, NULL, 32, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(21, NULL, 45, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(22, NULL, 46, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(23, NULL, 47, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(24, NULL, 48, 'Grade 8', '2025-2026', '1st', '2026-10-04 21:53:11'),
(25, 15, 37, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(26, 3, 34, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(27, NULL, 35, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(28, NULL, 36, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(29, NULL, 49, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(30, NULL, 50, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(31, NULL, 51, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(32, NULL, 52, 'Grade 9', '2025-2026', '1st', '2026-10-04 21:53:11'),
(33, 14, 53, 'Grade 10', '2025-2026', '1st', '2026-10-04 21:53:11'),
(34, 14, 54, 'Grade 10', '2025-2026', '1st', '2026-10-04 21:53:11'),
(35, 3, 55, 'Grade 10', '2025-2026', '1st', '2026-10-04 21:53:11'),
(36, 3, 56, 'Grade 10', '2025-2026', '1st', '2026-10-04 21:53:11'),
(40, 14, 4, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(41, 14, 5, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(42, NULL, 1, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(43, NULL, 2, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(44, NULL, 57, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(45, NULL, 15, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(46, NULL, 13, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(47, NULL, 17, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(48, NULL, 68, 'Grade 11 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(49, NULL, 5, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(50, NULL, 1, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(51, NULL, 2, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(52, NULL, 57, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(53, NULL, 15, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(54, NULL, 13, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(55, NULL, 17, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(56, NULL, 68, 'Grade 11 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(57, NULL, 4, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(58, NULL, 5, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(59, NULL, 1, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(60, NULL, 2, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(61, NULL, 57, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(62, NULL, 75, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(63, NULL, 76, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(64, NULL, 58, 'Grade 11 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(65, NULL, 4, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(66, NULL, 5, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(67, NULL, 1, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(68, NULL, 2, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(69, NULL, 57, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(70, NULL, 75, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(71, NULL, 76, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(72, NULL, 58, 'Grade 11 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(73, NULL, 4, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(74, NULL, 5, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(75, NULL, 1, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(76, NULL, 2, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(77, NULL, 57, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(78, NULL, 21, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(79, NULL, 23, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(80, NULL, 24, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(81, NULL, 71, 'Grade 11 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(82, NULL, 4, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(83, NULL, 5, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(84, NULL, 1, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(85, NULL, 2, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(86, NULL, 57, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(87, NULL, 21, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(88, NULL, 23, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(89, NULL, 24, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(90, NULL, 71, 'Grade 11 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(91, NULL, 4, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(92, NULL, 5, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(93, NULL, 6, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(94, NULL, 7, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(95, NULL, 1, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(96, NULL, 2, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(97, NULL, 57, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(98, NULL, 61, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(99, NULL, 62, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(100, NULL, 63, 'Grade 11 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(101, NULL, 4, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(102, NULL, 5, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(103, NULL, 6, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(104, NULL, 7, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(105, NULL, 1, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(106, NULL, 2, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(107, NULL, 57, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(108, NULL, 61, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(109, NULL, 62, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(110, NULL, 63, 'Grade 11 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(111, NULL, 14, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(112, NULL, 58, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(113, NULL, 69, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(114, NULL, 70, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(115, NULL, 20, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(116, NULL, 59, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(117, NULL, 60, 'Grade 12 - ABM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(118, NULL, 14, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(119, NULL, 58, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(120, NULL, 69, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(121, NULL, 70, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(122, NULL, 20, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(123, NULL, 59, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(124, NULL, 60, 'Grade 12 - ABM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(125, NULL, 58, 'Grade 12 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(126, NULL, 21, 'Grade 12 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(127, NULL, 71, 'Grade 12 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(128, NULL, 20, 'Grade 12 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(129, NULL, 59, 'Grade 12 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(130, NULL, 60, 'Grade 12 - GAS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(131, NULL, 58, 'Grade 12 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(132, NULL, 21, 'Grade 12 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(133, NULL, 71, 'Grade 12 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(134, NULL, 20, 'Grade 12 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(135, NULL, 59, 'Grade 12 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(136, NULL, 60, 'Grade 12 - GAS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(137, NULL, 72, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(138, NULL, 73, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(139, NULL, 18, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(140, NULL, 74, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(141, NULL, 20, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(142, NULL, 59, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(143, NULL, 60, 'Grade 12 - HUMSS-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(144, NULL, 72, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(145, NULL, 73, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(146, NULL, 18, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(147, NULL, 74, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(148, NULL, 20, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(149, NULL, 59, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(150, NULL, 60, 'Grade 12 - HUMSS-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(151, NULL, 64, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(152, NULL, 65, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(153, NULL, 66, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(154, NULL, 67, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(155, NULL, 20, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(156, NULL, 59, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(157, NULL, 60, 'Grade 12 - STEM-A', '2025-2026', '1st', '2026-10-04 22:06:57'),
(158, NULL, 64, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(159, NULL, 65, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(160, NULL, 66, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(161, NULL, 67, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(162, NULL, 20, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(163, NULL, 59, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57'),
(164, NULL, 60, 'Grade 12 - STEM-B', '2025-2026', '1st', '2026-10-04 22:06:57');

--
-- Triggers `teacher_assignments`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_class_delete` AFTER DELETE ON `teacher_assignments` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', OLD.course_id, OLD.section, NULL, OLD.teacher_id, OLD.id, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_class_insert` AFTER INSERT ON `teacher_assignments` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', NEW.course_id, NEW.section, NULL, NEW.teacher_id, NEW.id, 'all', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_class_update` AFTER UPDATE ON `teacher_assignments` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', NEW.course_id, NEW.section, NULL, NEW.teacher_id, NEW.id, 'all', NULL);
  IF NOT (NEW.teacher_id <=> OLD.teacher_id AND NEW.section <=> OLD.section AND NEW.course_id <=> OLD.course_id) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('class.changed', OLD.course_id, OLD.section, NULL, OLD.teacher_id, OLD.id, 'all', NULL);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` varchar(30) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Head Admin','Admin','Admission','Teacher','Student') NOT NULL DEFAULT 'Student',
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(300) DEFAULT NULL,
  `section_dept` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `notif_seen_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Last sync_events id this user marked as read',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_seen_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `school_id`, `username`, `password`, `role`, `first_name`, `last_name`, `middle_name`, `email`, `contact`, `profile_picture`, `section_dept`, `status`, `notif_seen_id`, `created_at`, `last_seen_at`, `updated_at`) VALUES
(1, 'ADMIN-001', 'admin', '$2y$10$U3NNlwXPs3d5npHbuVVbRO3AZvBOU4sWLH2r4I5GGheLT63tgbOgu', 'Head Admin', 'System', 'Administrator', '', 'jerby173@gmail.com', '', NULL, NULL, 'Active', 0, '2026-03-12 02:59:06', '2026-10-16 21:58:46', '2026-10-16 21:58:46'),
(3, '011', 'marc', '$2y$10$3diO1Gd4ISU7q/f1KCu/c.peV3DH.fN9A3x60VvIxGk7LWgFvYOWy', 'Teacher', 'Marc', 'Macarubbo', 'Vincent', 'marc.macarubbo@gmail.com', '', NULL, NULL, 'Active', 0, '2026-05-13 19:23:29', NULL, '2026-10-07 21:23:36'),
(6, '02', 'denver', '$2y$10$HVKJzBZwhkgett0JFtiOHusFD84EY9oh0N/kYytRkvWklcb8bN4wi', 'Student', 'Denver', 'Magdaong', '', 'macarubbo.marc13@gmail.com', '', NULL, 'Grade 10', 'Active', 0, '2026-05-13 19:49:40', '2026-10-08 22:50:29', '2026-10-08 22:50:29'),
(8, '1`2312', 'J.dnl_', '$2y$10$MGPkv1Hu3a634YL4V7RkXuJZZ14PBDeoYlc1loqWJjEwRzSy7JnwO', 'Student', 'JOSE FERNANDO', 'DINGLASAN', 'LORENZANA', 'jopperdinglasan37@gmail.com', '3', NULL, 'Grade 11 - HUMSS-B', 'Active', 0, '2026-05-16 18:25:42', NULL, '2026-09-21 21:05:12'),
(10, '1234124123123', 'jasdasd', '$2y$10$FaSDBgFCP5Ic3eyKidMtwe29.bkAuhOzPGp3KeXcPFB9R1/G21vl.', 'Student', 'Jerby', 'Ardiente', 'Lelis', 'ardientejerby26@gmail.com', '09094077216', NULL, 'Grade 11 - ABM-A', 'Active', 0, '2026-05-16 19:31:04', '2026-10-16 16:24:36', '2026-10-16 16:24:36'),
(14, '1231231231', 'asdadsasd', '$2y$10$wdJinXfN8O8KZm5dKscoreDz2WdsnfuIyKwpRwCYaYCdcKKGxoA9u', 'Teacher', 'asdadasd', 'asdasdasd', 'asdasdasd', 'angelolelis17@gmail.com', '12312313123', NULL, 'Grade 8', 'Active', 0, '2026-09-20 22:54:54', '2026-10-16 16:25:18', '2026-10-16 16:25:18'),
(15, '123123', '123123', '$2y$10$Y/w840ShV7BfeY4UTdjNDuRTMiqvObK6kLmZ4Bk62OWRItJJ/c1r2', 'Teacher', 'Jose Fernando', 'Dinglasan', '123', 'dinglasan.363935@globalcity.sti.edu.ph', '09935083218', NULL, 'Grade 10', 'Active', 0, '2026-09-21 21:08:23', '2026-10-07 10:50:26', '2026-10-07 10:50:26'),
(16, '323917251', 'perper123', '$2y$10$KHvw37aCNnBsSkVT5t5hZO75xtmyFgJgSossRT48YKJY7qnPL13ea', 'Admin', 'Jose Fernando', '123123', '123123', 'dinglasan.363935@globalcity.sti.edu.ph', '09935083218', NULL, 'Grade 10', 'Active', 0, '2026-09-21 21:59:29', NULL, '2026-09-21 23:55:21'),
(17, 'ADM-A6D43779', 'thegreatone', '$2y$10$OP6Rl96U/otu0ZR5BshZF.SUolEpFUwTwEqWxxqjj9I/D8BTWhzl.', 'Admin', 'Jose Fernando', 'Dinglasan', '123', 'dinglasan.363935@globalcity.sti.edu.ph', '09935083218', NULL, NULL, 'Active', 0, '2026-09-21 22:30:31', NULL, '2026-09-21 23:55:21'),
(18, '12345678910', 'den', '$2y$10$pBizh0tHjOjsUXCgI81FH.4mGq1kCHel7OY8bJF9Lnjm1A8LdQdOu', 'Student', 'denver', 'magdaong', 'brezuela', 'Geometrydenver04@gmail.com', '09278379364', NULL, 'Grade 7', 'Active', 0, '2026-09-22 19:09:39', NULL, '2026-10-04 21:55:55'),
(19, 'TEST-0001', 'juan.test', '$2y$10$4MDdy1OQno2RUhSXeWX.wOJ1iofVmv83rSYc0UWFxFsYbR69iqfuG', 'Student', 'Juan', 'Dela Cruz', NULL, 'juan.test@example.com', NULL, NULL, 'Grade 9', 'Active', 0, '2026-10-05 18:48:43', NULL, '2026-10-05 18:48:43'),
(20, 'DEMO-0001', 'demo.ana', '$2y$10$4MDdy1OQno2RUhSXeWX.wOJ1iofVmv83rSYc0UWFxFsYbR69iqfuG', 'Student', 'Ana', 'Reyes', NULL, 'demo.ana@example.com', NULL, NULL, 'Grade 9', 'Active', 0, '2026-10-06 00:02:57', NULL, '2026-10-06 00:02:57'),
(21, 'DEMO-0002', 'demo.ben', '$2y$10$4MDdy1OQno2RUhSXeWX.wOJ1iofVmv83rSYc0UWFxFsYbR69iqfuG', 'Student', 'Ben', 'Santos', NULL, 'demo.ben@example.com', NULL, NULL, 'Grade 9', 'Active', 0, '2026-10-06 00:02:57', NULL, '2026-10-06 00:02:57'),
(22, 'DEMO-0003', 'demo.carla', '$2y$10$4MDdy1OQno2RUhSXeWX.wOJ1iofVmv83rSYc0UWFxFsYbR69iqfuG', 'Student', 'Carla', 'Mendoza', NULL, 'demo.carla@example.com', NULL, NULL, 'Grade 9', 'Active', 0, '2026-10-06 00:02:57', NULL, '2026-10-06 00:02:57'),
(23, 'DEMO-0004', 'demo.diego', '$2y$10$4MDdy1OQno2RUhSXeWX.wOJ1iofVmv83rSYc0UWFxFsYbR69iqfuG', 'Student', 'Diego', 'Ramos', NULL, 'demo.diego@example.com', NULL, NULL, 'Grade 9', 'Active', 0, '2026-10-06 00:02:57', NULL, '2026-10-06 00:02:57'),
(24, 'DEMO-0005', 'demo.ella', '$2y$10$4MDdy1OQno2RUhSXeWX.wOJ1iofVmv83rSYc0UWFxFsYbR69iqfuG', 'Student', 'Ella', 'Cruz', NULL, 'demo.ella@example.com', NULL, NULL, 'Grade 9', 'Active', 0, '2026-10-06 00:02:57', NULL, '2026-10-06 00:02:57'),
(27, '1231231231231', 'test1', '$2y$10$gGDJItrWyj/d./12K9vwB.B99haCsH49BzX1nYf5nB/pg6uBU8My6', 'Student', 'Jerby', 'Ardiente', '', 'ven061307@gmail.com', '12313123123', NULL, 'Grade 10', 'Active', 0, '2026-10-08 00:20:57', '2026-10-16 16:25:19', '2026-10-16 16:25:19'),
(28, '12312312312', 'test2', '$2y$10$Ue6i4Zuilk5Qu2ThWwgJ3uEekqESNW9nvigSATuTIUbKcZvLWHyYa', 'Teacher', 'test2', 'test2', '', 'angelolelis17@gmail.com', '12123123', NULL, 'Grade 10', 'Active', 0, '2026-10-08 00:22:10', NULL, '2026-10-08 00:22:10'),
(29, '12345', 'Chavz', '$2y$10$ETC/WquLY47o3dTNkHai6undKkXLB49hYkgWuxa.LyqVBbPZN.6Bm', 'Student', 'Aron', 'Chavz', '', 'vamchloveogr@gmail.com', '12345', NULL, 'Grade 7', 'Active', 0, '2026-10-09 14:03:18', NULL, '2026-10-09 14:03:18');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_user_delete` AFTER DELETE ON `users` FOR EACH ROW INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title)
  VALUES ('account.changed', NULL, NULL, NULL, NULL, OLD.id, 'admins', NULL)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
  INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('account.changed', NULL, NULL, NULL, NEW.id, NULL, 'admins', NULL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sync_user_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
  IF NOT (NEW.status <=> OLD.status AND NEW.role <=> OLD.role AND NEW.section_dept <=> OLD.section_dept
          AND NEW.first_name <=> OLD.first_name AND NEW.last_name <=> OLD.last_name AND NEW.username <=> OLD.username) THEN
    INSERT INTO sync_events (kind, course_id, section, owner_id, user_id, ref_id, audience, title) VALUES ('account.changed', NULL, NEW.section_dept, NULL, NEW.id, NULL, 'all', NULL);
  END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_activity_admin` (`admin_id`),
  ADD KEY `idx_admin_activity_created` (`created_at`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_perm_unique` (`admin_id`,`perm_key`),
  ADD KEY `fk_admin_permissions_perm` (`perm_key`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_assignments_teacher` (`teacher_id`),
  ADD KEY `idx_assignments_module` (`module_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_attendance` (`student_id`,`course_id`,`date`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `award_types`
--
ALTER TABLE `award_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_award_types_status` (`status`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_ip_created` (`ip_address`,`created_at`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dashboard_banners`
--
ALTER TABLE `dashboard_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_banner_created_by` (`created_by`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enroll` (`student_id`,`course_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `gradebook_items`
--
ALTER TABLE `gradebook_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_gb_source` (`source_type`,`source_id`),
  ADD KEY `idx_gb_course` (`course_id`),
  ADD KEY `idx_gradebook_items_teacher` (`teacher_id`);

--
-- Indexes for table `gradebook_scores`
--
ALTER TABLE `gradebook_scores`
  ADD PRIMARY KEY (`item_id`,`student_id`),
  ADD KEY `fk_gbs_student` (`student_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`course_id`,`item_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `student_course` (`student_id`,`course_id`);

--
-- Indexes for table `grade_items`
--
ALTER TABLE `grade_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `login_otps`
--
ALTER TABLE `login_otps`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_module` (`course_id`,`module_number`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `fk_mod_updated_by` (`updated_by`);

--
-- Indexes for table `module_progress`
--
ALTER TABLE `module_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_progress` (`student_id`,`module_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `mp_ibfk_2` (`module_id`);

--
-- Indexes for table `module_views`
--
ALTER TABLE `module_views`
  ADD PRIMARY KEY (`student_id`,`module_id`),
  ADD KEY `idx_mv_module` (`module_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`perm_key`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_quizzes_teacher` (`teacher_id`),
  ADD KEY `idx_quizzes_module` (`module_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `quiz_choices`
--
ALTER TABLE `quiz_choices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attempt_question` (`attempt_id`,`question_id`),
  ADD KEY `fk_qr_question` (`question_id`),
  ADD KEY `fk_qr_choice` (`choice_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_selector` (`selector`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section_name` (`name`);

--
-- Indexes for table `student_awards`
--
ALTER TABLE `student_awards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_award_request` (`request_token`),
  ADD KEY `idx_sa_student` (`student_id`,`status`),
  ADD KEY `idx_sa_teacher` (`teacher_id`,`status`),
  ADD KEY `idx_sa_course` (`course_id`),
  ADD KEY `idx_sa_type` (`award_type_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_submit` (`assignment_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `sync_events`
--
ALTER TABLE `sync_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_se_created` (`created_at`),
  ADD KEY `idx_se_user` (`user_id`,`id`),
  ADD KEY `idx_se_course` (`course_id`,`id`),
  ADD KEY `fk_se_owner` (`owner_id`),
  ADD KEY `fk_se_section` (`section`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section_subject` (`course_id`,`section`,`school_year`,`semester`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_ta_teacher` (`teacher_id`),
  ADD KEY `idx_ta_section` (`section`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_id` (`school_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_users_last_seen` (`last_seen_at`),
  ADD KEY `fk_users_section` (`section_dept`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1113;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `award_types`
--
ALTER TABLE `award_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `dashboard_banners`
--
ALTER TABLE `dashboard_banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `gradebook_items`
--
ALTER TABLE `gradebook_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `grade_items`
--
ALTER TABLE `grade_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=345;

--
-- AUTO_INCREMENT for table `module_progress`
--
ALTER TABLE `module_progress`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `quiz_choices`
--
ALTER TABLE `quiz_choices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `student_awards`
--
ALTER TABLE `student_awards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sync_events`
--
ALTER TABLE `sync_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `fk_activity_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `fk_admin_permissions_perm` FOREIGN KEY (`perm_key`) REFERENCES `permissions` (`perm_key`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admin_permissions_user` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_ann_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ann_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assign_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assignments_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_att_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dashboard_banners`
--
ALTER TABLE `dashboard_banners`
  ADD CONSTRAINT `fk_banner_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enr_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enr_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gradebook_items`
--
ALTER TABLE `gradebook_items`
  ADD CONSTRAINT `fk_gb_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gradebook_items_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `gradebook_scores`
--
ALTER TABLE `gradebook_scores`
  ADD CONSTRAINT `fk_gbs_item` FOREIGN KEY (`item_id`) REFERENCES `gradebook_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_gbs_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grade_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_grade_item` FOREIGN KEY (`item_id`) REFERENCES `grade_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_grade_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `login_otps`
--
ALTER TABLE `login_otps`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `fk_mod_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mod_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `module_progress`
--
ALTER TABLE `module_progress`
  ADD CONSTRAINT `fk_mp_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mp_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mp_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `module_views`
--
ALTER TABLE `module_views`
  ADD CONSTRAINT `fk_mv_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mv_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quizzes_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_qa_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_qa_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_choices`
--
ALTER TABLE `quiz_choices`
  ADD CONSTRAINT `fk_qc_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD CONSTRAINT `fk_qr_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_qr_choice` FOREIGN KEY (`choice_id`) REFERENCES `quiz_choices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_qr_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_awards`
--
ALTER TABLE `student_awards`
  ADD CONSTRAINT `fk_sa_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_type` FOREIGN KEY (`award_type_id`) REFERENCES `award_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `fk_sub_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sync_events`
--
ALTER TABLE `sync_events`
  ADD CONSTRAINT `fk_se_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_se_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_se_section` FOREIGN KEY (`section`) REFERENCES `sections` (`name`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_se_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `fk_ta_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ta_section` FOREIGN KEY (`section`) REFERENCES `sections` (`name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ta_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_section` FOREIGN KEY (`section_dept`) REFERENCES `sections` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
