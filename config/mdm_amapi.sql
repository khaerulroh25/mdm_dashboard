-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 30, 2025 at 01:09 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u317446943_mdm_amapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE `apps` (
  `id` int(11) NOT NULL,
  `app_name` varchar(150) NOT NULL,
  `package_name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apps`
--

INSERT INTO `apps` (`id`, `app_name`, `package_name`, `created_at`) VALUES
(21, 'Shopee', 'com.shopee.id', '2025-08-17 12:07:35'),
(22, 'Lazada', 'com.lazada.android', '2025-08-17 12:07:54'),
(23, 'Tokopedia', 'com.tokopedia.tkpd', '2025-08-17 12:08:15'),
(24, 'Gojek', 'com.gojek.app', '2025-08-17 12:08:48'),
(25, 'Grab', 'com.grabtaxi.passenger', '2025-08-17 12:09:04'),
(26, 'Youtube', 'com.google.android.youtube', '2025-08-17 12:09:23'),
(27, 'Google Chrome', 'com.android.chrome', '2025-08-17 12:09:44'),
(28, 'Google Sheets', 'com.google.android.apps.docs.editors.sheets', '2025-08-17 12:10:24'),
(29, 'Google slide', 'com.google.android.apps.docs.editors.slides', '2025-08-17 12:10:39'),
(30, 'Google Translate', 'com.google.android.apps.translate', '2025-08-17 12:11:01'),
(31, 'Google Meet', 'com.google.android.gm', '2025-08-17 12:11:17'),
(32, 'Gmail', 'com.google.android.gm', '2025-08-17 12:11:35'),
(33, 'Microsoft Word', 'com.microsoft.office.word', '2025-08-17 12:13:48'),
(34, 'Kalkulator', 'com.google.android.calculator', '2025-08-19 13:59:20'),
(35, 'BRI mobile', 'id.co.bri.brimo', '2025-08-19 14:03:28'),
(36, 'BCA mobile', 'com.bca', '2025-08-19 14:04:10'),
(37, 'Livin Mandiri', 'id.bmri.livin', '2025-08-19 14:05:07'),
(38, 'BSI mobile', 'id.co.bsm.mobile', '2025-08-19 14:06:04'),
(39, 'Akulaku', 'io.silvrr.installment', '2025-08-19 14:08:54'),
(40, 'Kredivo', 'com.finaccel.android', '2025-08-19 14:09:46'),
(41, 'UPDF', 'com.superace.updf', '2025-08-19 14:11:04'),
(42, 'Camscanner', 'com.intsig.camscanner', '2025-08-19 14:11:39'),
(43, 'Microsoft Excell', 'com.microsoft.office.excel', '2025-08-19 14:13:13'),
(44, 'Microsoft Powerpoint', 'com.microsoft.office.powerpoint', '2025-08-19 14:14:54'),
(45, 'Microsoft Outlook', 'com.microsoft.office.outlook', '2025-08-19 14:15:33'),
(46, 'OneDrive', 'com.microsoft.skydrive', '2025-08-19 14:16:31'),
(47, 'BMI mobile', 'com.bmi.mobile', '2025-08-19 14:18:43'),
(48, 'Family Link Orang Tua', 'com.google.android.apps.kids.familylink', '2025-08-19 14:19:38'),
(49, 'Family Link Orang Anak', 'com.google.android.apps.kids.familylinkhelper', '2025-08-19 14:20:07'),
(50, 'Netflix', 'com.netflix.mediaclient', '2025-08-19 14:21:22'),
(51, 'Viu', 'com.vuclip.viu', '2025-08-19 14:21:41'),
(52, 'WeTv', 'com.tencent.qqlivei18n', '2025-08-19 14:22:00'),
(53, 'Vidio', 'com.vidio.android', '2025-08-19 14:22:26'),
(54, 'Zoom', 'us.zoom.videomeetings', '2025-08-19 14:30:31'),
(55, 'JMO', 'com.bpjstku', '2025-08-19 14:32:11'),
(56, 'JKN mobile', 'app.bpjs.mobile', '2025-08-19 14:33:27'),
(57, 'Instagram', 'com.instagram.android', '2025-08-19 14:43:15'),
(58, 'Facebook', 'com.facebook.katana', '2025-08-19 14:43:37'),
(59, 'Messenger', 'com.facebook.orca', '2025-08-19 14:43:56'),
(60, 'Getrich', 'com.linecorp.LGGRTHN', '2025-08-19 14:45:52'),
(61, 'Pou', 'me.pou.app', '2025-08-19 14:46:10'),
(62, 'PUBG', 'com.tencent.ig', '2025-08-19 14:46:22'),
(63, 'Roblox', 'com.roblox.client', '2025-08-19 14:46:33'),
(64, 'Clas of clans', 'com.supercell.clashofclans', '2025-08-19 14:47:36'),
(65, 'OVO', 'ovo.id', '2025-08-19 14:49:35'),
(66, 'Halodoc', 'com.linkdokter.halodoc.android', '2025-08-19 14:50:49'),
(67, 'Free fire', 'com.dts.freefireth', '2025-08-19 14:51:54'),
(68, 'Subway surfers', 'com.kiloo.subwaysurf', '2025-08-19 14:52:16'),
(69, 'Ruang guru', 'com.ruangguru.livestudents', '2025-08-19 14:53:19'),
(70, 'Zenius', 'net.zenius.mobile', '2025-08-19 14:53:33'),
(74, 'whatsapp', 'com.whatsapp', '2025-08-23 13:21:11'),
(75, 'Telegram', 'org.telegram.messenger', '2025-08-23 15:42:08'),
(76, 'ChatGPT', 'com.openai.chatgpt', '2025-08-23 15:43:27'),
(78, 'Alqur\'an', 'com.quran.labs.androidquran', '2025-08-23 15:45:33'),
(79, 'Muslim pro', 'com.bitsmedia.android.muslimpro', '2025-08-23 15:46:59'),
(80, 'Clash Royal', 'com.supercell.clashroyale', '2025-08-23 15:48:43'),
(81, 'Candy crush', 'com.king.candycrushsaga', '2025-08-23 15:48:58'),
(82, 'Ludo king', 'com.ludo.king', '2025-08-23 15:49:17'),
(83, 'Picsart', 'com.picsart.studio', '2025-08-23 15:50:06'),
(84, 'Snapseed', 'com.niksoftware.snapseed', '2025-08-23 15:50:31'),
(85, 'Vsco', 'com.vsco.cam', '2025-08-23 15:50:43'),
(86, 'Lightroom', 'com.adobe.lrmobile', '2025-08-23 15:50:54'),
(87, 'Canva', 'com.canva.editor', '2025-08-23 15:51:06'),
(88, 'Pixelab', 'com.imaginstudio.imagetools.pixellab', '2025-08-23 15:51:19'),
(89, 'B612', 'com.linecorp.b612', '2025-08-23 15:51:31'),
(90, 'Capcut', 'com.lemon.lvoverseas', '2025-08-23 15:52:10'),
(91, 'Vn video edietor', 'com.frontrow.vlog', '2025-08-23 15:53:02'),
(92, 'Kinemaster', 'com.nexstreaming.app.kinemasterfree', '2025-08-23 15:53:13'),
(93, 'Inshoot', 'com.camerasideas.instashot', '2025-08-23 15:53:26'),
(94, 'Vivavideo', 'com.quvideo.xiaoying', '2025-08-23 15:53:39'),
(95, 'Youtube music', 'com.google.android.apps.youtube.music', '2025-08-23 15:55:35'),
(96, 'Jam', 'com.google.android.deskclock', '2025-08-23 16:25:35'),
(97, 'Samsung clock', 'com.sec.android.app.clockpackage', '2025-08-23 16:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `pemilik`
--

CREATE TABLE `pemilik` (
  `id` int(11) NOT NULL,
  `device` varchar(255) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `id_anggota` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemilik`
--

INSERT INTO `pemilik` (`id`, `device`, `nama`, `id_anggota`, `telepon`, `created_at`) VALUES
(9, 'enterprises/LC041be11h/devices/37db67c3e1be3423', 'DEDE ZUNAEDI - AGEN IIS', '0', '085814064274', '2025-08-19 11:09:48'),
(10, 'enterprises/LC041be11h/devices/376bbda960c6ed43', 'Erul r', '0', '085814064274', '2025-08-19 15:02:01'),
(12, 'enterprises/LC041be11h/devices/3ada653e396b0b6d', 'MUTMAINAH - AGEN NURHASANAH', '0', '085814064274', '2025-08-20 03:33:13'),
(13, 'enterprises/LC041be11h/devices/3fe47cf93d00a1d1', 'RENI - KANGKUNG', '0', '08888888888', '2025-08-23 12:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$5iTRPQp5XF.3UU/Iu6wXU.XUFuXkcVvqDOnNZFsXUko3ThQXpLFK2', 'admin', '2025-08-17 05:50:46'),
(11, 'khaerul', '$2y$10$oSYfL4TzKtK5MNx/H/kGJ.B1VXeAprJJmM6DBrfUTzc.4un4Ps9K2', 'admin', '2025-08-18 02:39:39'),
(14, 'Abai', '$2y$10$vyc7Uoe.l.tkLk/.4OGCyOkUZs3rxowUOdhAcgr9UeOTHa/8MIeoC', 'user', '2025-08-25 06:04:16'),
(15, 'Sendi', '$2y$10$R.4dEpOwDLZLnOTj4a81JOZzoKIsf7CG/R1K.NOod7ruc/etda4y6', 'admin', '2025-08-25 06:17:55'),
(16, 'Dzul', '$2y$10$4dOpLjskhbqdepMH8gN81eBiE0hW/bh0s3FurPSKI0ZAZcr2u6vIm', 'user', '2025-08-25 06:18:04'),
(17, 'Renaldi', '$2y$10$aVMef0q67azwAHDQR7vFP.FjXOQZVeIcMT2q3Lc4KrACut1.e4rgq', 'user', '2025-08-25 06:18:13'),
(18, 'Maul', '$2y$10$BFzpT/nfgG5TwzQS6Ykwi.CuVNfDGtn7IRu2Zt/yX6aaLhZM3zZW2', 'user', '2025-08-25 06:18:20'),
(19, 'Arif', '$2y$10$uLq7hLzkletAA5JvBtktduxSfBlUQ4sRQzEVl22HyV/fDb2MARjGm', 'admin', '2025-08-25 06:18:31'),
(20, 'Raidi', '$2y$10$bp0faBhcRhdYOuCSJ1RbqexKHYCg1XJSiokWVsx5GwYdFR4V3qAkK', 'admin', '2025-08-25 06:18:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pemilik`
--
ALTER TABLE `pemilik`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apps`
--
ALTER TABLE `apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `pemilik`
--
ALTER TABLE `pemilik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
