-- MySQL dump 10.13  Distrib 8.0.39, for Win64 (x86_64)
--
-- Host: localhost    Database: restoran_digital
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('a75f3f172bfb296f2e10cbfc6dfc1883','i:2;',1750163412),('a75f3f172bfb296f2e10cbfc6dfc1883:timer','i:1750163412;',1750163412),('bfe29681cef37e767f5317b13a983e7f','i:6;',1750163403),('bfe29681cef37e767f5317b13a983e7f:timer','i:1750163403;',1750163403),('dcdbda746c5c7af82096edb6e1697205','i:43;',1750011514),('dcdbda746c5c7af82096edb6e1697205:timer','i:1750011514;',1750011514);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_notifications`
--

DROP TABLE IF EXISTS `customer_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reservasi_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_notifications_user_id_foreign` (`user_id`),
  KEY `customer_notifications_reservasi_id_foreign` (`reservasi_id`),
  CONSTRAINT `customer_notifications_reservasi_id_foreign` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=373 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_notifications`
--

LOCK TABLES `customer_notifications` WRITE;
/*!40000 ALTER TABLE `customer_notifications` DISABLE KEYS */;
INSERT INTO `customer_notifications` VALUES (314,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 19 Juni 2025 pukul 14:00.','{\"kode_reservasi\": \"RES-2Q3JVY\"}','2025-06-18 19:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:34:58','2025-06-17 12:18:55',374),(315,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 14:00.','{\"kode_reservasi\": \"RES-2Q3JVY\"}','2025-06-19 06:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:34:58','2025-06-17 12:18:55',374),(316,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-2Q3JVY\"}','2025-06-19 06:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:34:58','2025-06-17 12:18:55',374),(317,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 26 Juni 2025 pukul 16:00.','{\"kode_reservasi\": \"RES-UHJ7PH\"}','2025-06-25 21:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:36:39','2025-06-17 12:18:55',375),(318,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 16:00.','{\"kode_reservasi\": \"RES-UHJ7PH\"}','2025-06-26 08:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:36:39','2025-06-17 12:18:55',375),(319,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-UHJ7PH\"}','2025-06-26 08:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:36:39','2025-06-17 12:18:55',375),(320,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #375 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-375-1750091823\", \"dp_amount\": 132000, \"total_amount\": 264000}',NULL,0,NULL,'2025-06-17 12:18:55','2025-06-16 16:37:03','2025-06-17 12:18:55',375),(321,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 18 Juni 2025 pukul 16:00.','{\"kode_reservasi\": \"RES-8MJM5Y\"}','2025-06-17 21:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:38:44','2025-06-17 12:18:55',376),(322,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 16:00.','{\"kode_reservasi\": \"RES-8MJM5Y\"}','2025-06-18 08:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:38:44','2025-06-17 12:18:55',376),(323,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-8MJM5Y\"}','2025-06-18 08:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:38:44','2025-06-17 12:18:55',376),(324,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 19 Juni 2025 pukul 16:00.','{\"kode_reservasi\": \"RES-680IB0\"}','2025-06-18 21:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:39:40','2025-06-17 12:18:55',377),(325,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 16:00.','{\"kode_reservasi\": \"RES-680IB0\"}','2025-06-19 08:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:39:40','2025-06-17 12:18:55',377),(326,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-680IB0\"}','2025-06-19 08:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 16:39:40','2025-06-17 12:18:55',377),(327,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 18 Juni 2025 pukul 14:00.','{\"kode_reservasi\": \"RES-ABGYHP\"}','2025-06-17 19:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:01:56','2025-06-17 12:18:55',379),(328,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 14:00.','{\"kode_reservasi\": \"RES-ABGYHP\"}','2025-06-18 06:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:01:56','2025-06-17 12:18:55',379),(329,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-ABGYHP\"}','2025-06-18 06:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:01:56','2025-06-17 12:18:55',379),(330,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 19 Juni 2025 pukul 13:00.','{\"kode_reservasi\": \"RES-UWLLQC\"}','2025-06-18 18:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:04:05','2025-06-17 12:18:55',380),(331,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 13:00.','{\"kode_reservasi\": \"RES-UWLLQC\"}','2025-06-19 05:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:04:05','2025-06-17 12:18:55',380),(332,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-UWLLQC\"}','2025-06-19 05:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:04:05','2025-06-17 12:18:55',380),(333,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 21 Juni 2025 pukul 16:00.','{\"kode_reservasi\": \"RES-X2I8JZ\"}','2025-06-20 21:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:11:57','2025-06-17 12:18:55',381),(334,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 16:00.','{\"kode_reservasi\": \"RES-X2I8JZ\"}','2025-06-21 08:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:11:57','2025-06-17 12:18:55',381),(335,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-X2I8JZ\"}','2025-06-21 08:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:11:57','2025-06-17 12:18:55',381),(336,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 21 Juni 2025 pukul 16:00.','{\"kode_reservasi\": \"RES-YIF1YV\"}','2025-06-20 21:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:12:26','2025-06-17 12:18:55',382),(337,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 16:00.','{\"kode_reservasi\": \"RES-YIF1YV\"}','2025-06-21 08:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:12:26','2025-06-17 12:18:55',382),(338,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-YIF1YV\"}','2025-06-21 08:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:12:26','2025-06-17 12:18:55',382),(339,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 20 Juni 2025 pukul 15:00.','{\"kode_reservasi\": \"RES-O8DOA6\"}','2025-06-19 20:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:13:15','2025-06-17 12:18:55',383),(340,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 15:00.','{\"kode_reservasi\": \"RES-O8DOA6\"}','2025-06-20 07:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:13:15','2025-06-17 12:18:55',383),(341,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-O8DOA6\"}','2025-06-20 07:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:13:15','2025-06-17 12:18:55',383),(342,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #383 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-383-1750097610\", \"dp_amount\": 7700, \"total_amount\": 15400}',NULL,0,NULL,'2025-06-17 12:18:55','2025-06-16 18:13:30','2025-06-17 12:18:55',383),(343,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 26 Juni 2025 pukul 15:00.','{\"kode_reservasi\": \"RES-CD4DHZ\"}','2025-06-25 20:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:20:35','2025-06-17 12:18:55',384),(344,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 15:00.','{\"kode_reservasi\": \"RES-CD4DHZ\"}','2025-06-26 07:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:20:35','2025-06-17 12:18:55',384),(345,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-CD4DHZ\"}','2025-06-26 07:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:20:35','2025-06-17 12:18:55',384),(346,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #384 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-384-1750098051\", \"dp_amount\": 176000, \"total_amount\": 352000}',NULL,0,NULL,'2025-06-17 12:18:55','2025-06-16 18:20:51','2025-06-17 12:18:55',384),(347,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 20 Juni 2025 pukul 14:00.','{\"kode_reservasi\": \"RES-JMRXZR\"}','2025-06-19 19:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:23:11','2025-06-17 12:18:55',385),(348,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 14:00.','{\"kode_reservasi\": \"RES-JMRXZR\"}','2025-06-20 06:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:23:11','2025-06-17 12:18:55',385),(349,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-JMRXZR\"}','2025-06-20 06:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:23:11','2025-06-17 12:18:55',385),(350,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 26 Juni 2025 pukul 14:00.','{\"kode_reservasi\": \"RES-LUYR55\"}','2025-06-25 19:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:27:57','2025-06-17 12:18:55',386),(351,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 14:00.','{\"kode_reservasi\": \"RES-LUYR55\"}','2025-06-26 06:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:27:57','2025-06-17 12:18:55',386),(352,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-LUYR55\"}','2025-06-26 06:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:27:57','2025-06-17 12:18:55',386),(353,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #386 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-386-1750098495\", \"dp_amount\": 44000, \"total_amount\": 88000}',NULL,0,NULL,'2025-06-17 12:18:55','2025-06-16 18:28:15','2025-06-17 12:18:55',386),(354,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 26 Juni 2025 pukul 12:00.','{\"kode_reservasi\": \"RES-UZF4QQ\"}','2025-06-25 17:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:29:34','2025-06-17 12:18:55',387),(355,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 12:00.','{\"kode_reservasi\": \"RES-UZF4QQ\"}','2025-06-26 04:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:29:34','2025-06-17 12:18:55',387),(356,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-UZF4QQ\"}','2025-06-26 04:55:00',0,NULL,'2025-06-17 12:18:55','2025-06-16 18:29:34','2025-06-17 12:18:55',387),(357,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #387 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-387-1750098590\", \"dp_amount\": 44000, \"total_amount\": 88000}',NULL,0,NULL,'2025-06-17 12:18:55','2025-06-16 18:29:50','2025-06-17 12:18:55',387),(358,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 26 Juni 2025 pukul 14:00.','{\"kode_reservasi\": \"RES-NTEDAS\"}','2025-06-25 19:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-17 11:50:38','2025-06-17 12:18:55',388),(359,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 14:00.','{\"kode_reservasi\": \"RES-NTEDAS\"}','2025-06-26 06:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-17 11:50:38','2025-06-17 12:18:55',388),(360,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-NTEDAS\"}','2025-06-26 06:55:00',0,NULL,'2025-06-17 11:56:07','2025-06-17 11:50:38','2025-06-17 11:56:07',388),(362,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 26 Juni 2025 pukul 13:00.','{\"kode_reservasi\": \"RES-LTE4GT\"}','2025-06-25 18:00:00',0,NULL,'2025-06-17 12:18:55','2025-06-17 12:17:55','2025-06-17 12:18:55',389),(363,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 13:00.','{\"kode_reservasi\": \"RES-LTE4GT\"}','2025-06-26 05:00:00',0,NULL,'2025-06-17 12:18:42','2025-06-17 12:17:55','2025-06-17 12:18:42',389),(364,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-LTE4GT\"}','2025-06-26 05:55:00',0,NULL,'2025-06-17 12:18:34','2025-06-17 12:17:55','2025-06-17 12:18:34',389),(365,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 27 Juni 2025 pukul 16:00.','{\"kode_reservasi\": \"RES-XEVUVL\"}','2025-06-26 21:00:00',0,NULL,NULL,'2025-06-17 12:19:29','2025-06-17 12:19:29',390),(366,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 16:00.','{\"kode_reservasi\": \"RES-XEVUVL\"}','2025-06-27 08:00:00',0,NULL,NULL,'2025-06-17 12:19:29','2025-06-17 12:19:29',390),(367,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-XEVUVL\"}','2025-06-27 08:55:00',0,NULL,NULL,'2025-06-17 12:19:29','2025-06-17 12:19:29',390),(368,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #390 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-390-1750162783\", \"dp_amount\": 5500, \"total_amount\": 11000}',NULL,0,NULL,'2025-06-17 12:20:24','2025-06-17 12:19:43','2025-06-17 12:20:24',390),(369,16,'reminder_12_hours','Pengingat Reservasi (12 Jam)','Hai! Reservasi Anda akan dimulai besok pada 27 Juni 2025 pukul 14:00.','{\"kode_reservasi\": \"RES-NR7CIT\"}','2025-06-26 19:00:00',0,NULL,'2025-06-17 12:27:35','2025-06-17 12:25:04','2025-06-17 12:27:35',391),(370,16,'reminder_1_hour','Pengingat Reservasi (1 Jam)','Reservasi Anda dimulai 1 jam lagi pada pukul 14:00.','{\"kode_reservasi\": \"RES-NR7CIT\"}','2025-06-27 06:00:00',0,NULL,'2025-06-17 12:27:34','2025-06-17 12:25:04','2025-06-17 12:27:34',391),(371,16,'reminder_5_minutes','Pengingat Reservasi (5 Menit)','5 menit lagi! Reservasi Anda akan dimulai.','{\"kode_reservasi\": \"RES-NR7CIT\"}','2025-06-27 06:55:00',0,NULL,'2025-06-17 12:27:31','2025-06-17 12:25:04','2025-06-17 12:27:31',391),(372,16,'reservation_created','Menunggu Pembayaran','Pesanan Anda dengan kode #391 telah dibuat. Segera selesaikan pembayaran DP 50%.','{\"order_id\": \"RES-391-1750163123\", \"dp_amount\": 7700, \"total_amount\": 15400}',NULL,0,NULL,NULL,'2025-06-17 12:25:23','2025-06-17 12:25:23',391);
/*!40000 ALTER TABLE `customer_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservasi_id` bigint unsigned NOT NULL,
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `service_fee` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `remaining_amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_reservasi_id_payment_status_index` (`reservasi_id`,`payment_status`),
  CONSTRAINT `invoices_reservasi_id_foreign` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (85,375,'INV-20250617-U4BRIM',240000.00,24000.00,264000.00,132000.00,132000.00,NULL,'partial',NULL,'2025-06-17 12:20:54','2025-06-16 16:37:31','2025-06-17 12:20:54'),(86,377,'INV-20250616-VZQ04K',0.00,0.00,0.00,0.00,0.00,NULL,'paid',NULL,'2025-06-16 16:45:03','2025-06-16 16:40:48','2025-06-16 16:45:03'),(87,384,'INV-20250617-PL3CIV',320000.00,32000.00,352000.00,176000.00,176000.00,NULL,'partial',NULL,'2025-06-16 18:45:14','2025-06-16 18:22:26','2025-06-16 18:45:14'),(88,386,'INV-20250617-R6XJBN',80000.00,8000.00,88000.00,44000.00,44000.00,NULL,'partial',NULL,'2025-06-16 18:40:27','2025-06-16 18:40:27','2025-06-16 18:40:27'),(89,388,'INV-20250617-E2ARPC',14000.00,1400.00,15400.00,7700.00,7700.00,NULL,'partial',NULL,'2025-06-17 11:51:48','2025-06-17 11:51:48','2025-06-17 11:51:48'),(90,387,'INV-20250617-9ODHDJ',80000.00,8000.00,88000.00,44000.00,44000.00,NULL,'partial',NULL,'2025-06-17 11:51:57','2025-06-17 11:51:57','2025-06-17 11:51:57'),(91,391,'INV-20250617-CJKCKJ',14000.00,1400.00,15400.00,7700.00,7700.00,NULL,'partial',NULL,'2025-06-17 12:26:31','2025-06-17 12:26:31','2025-06-17 12:26:31');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meja`
--

DROP TABLE IF EXISTS `meja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_meja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` int NOT NULL,
  `status` enum('tersedia','terisi','dipesan','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tersedia',
  `current_reservasi_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meja_nomor_meja_unique` (`nomor_meja`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meja`
--

LOCK TABLES `meja` WRITE;
/*!40000 ALTER TABLE `meja` DISABLE KEYS */;
INSERT INTO `meja` VALUES (6,'1','indoor',2,'tersedia',NULL,'2025-06-01 08:12:55','2025-06-17 12:18:02'),(7,'2','indoor',2,'dipesan',NULL,'2025-06-01 08:13:02','2025-06-16 16:36:39'),(8,'3','indoor',2,'dipesan',NULL,'2025-06-01 08:13:11','2025-06-16 18:04:05'),(9,'4','indoor',2,'dipesan',NULL,'2025-06-01 08:13:20','2025-06-16 16:39:40'),(10,'5','indoor',4,'terisi',378,'2025-06-01 08:13:28','2025-06-16 17:08:46'),(11,'6','indoor',4,'dipesan',NULL,'2025-06-01 08:13:36','2025-06-16 18:20:35'),(12,'7','indoor',4,'dipesan',NULL,'2025-06-01 08:13:45','2025-06-16 18:13:15'),(13,'8','outdoor',2,'dipesan',NULL,'2025-06-01 08:15:09','2025-06-17 12:25:04'),(14,'9','outdoor',2,'tersedia',NULL,'2025-06-01 08:15:23','2025-06-16 16:33:21'),(15,'10','outdoor',4,'dipesan',NULL,'2025-06-01 08:15:31','2025-06-16 18:29:34');
/*!40000 ALTER TABLE `meja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meja_reservasi`
--

DROP TABLE IF EXISTS `meja_reservasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meja_reservasi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservasi_id` bigint unsigned NOT NULL,
  `meja_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meja_reservasi_reservasi_id_meja_id_unique` (`reservasi_id`,`meja_id`),
  KEY `meja_reservasi_meja_id_foreign` (`meja_id`),
  CONSTRAINT `meja_reservasi_meja_id_foreign` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meja_reservasi_reservasi_id_foreign` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=222 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meja_reservasi`
--

LOCK TABLES `meja_reservasi` WRITE;
/*!40000 ALTER TABLE `meja_reservasi` DISABLE KEYS */;
INSERT INTO `meja_reservasi` VALUES (206,375,7,'2025-06-16 16:36:39','2025-06-16 16:36:39'),(208,377,9,'2025-06-16 16:39:40','2025-06-16 16:39:40'),(210,380,8,'2025-06-16 18:04:05','2025-06-16 18:04:05'),(213,383,12,'2025-06-16 18:13:15','2025-06-16 18:13:15'),(214,384,11,'2025-06-16 18:20:35','2025-06-16 18:20:35'),(217,387,15,'2025-06-16 18:29:34','2025-06-16 18:29:34'),(221,391,13,'2025-06-17 12:25:04','2025-06-17 12:25:04');
/*!40000 ALTER TABLE `meja_reservasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('food','beverage','dessert','appetizer','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'food',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `preparation_time` int DEFAULT NULL COMMENT 'preparation time in minutes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'seblak',NULL,19000.00,NULL,NULL,'menu_images/DO0p8Z2d5QMt1ewn3ts03IMZkOl0He6rftz29G6x.png','food',1,10,'2025-05-14 08:14:08','2025-05-14 08:14:08'),(2,'Es Teh',NULL,19000.00,NULL,NULL,'menu_images/FNkZGasFtvWIbeDkZSw6RajcBGFgSCIPIgR2rPJr.jpg','other',1,4,'2025-05-21 04:49:57','2025-05-21 04:50:18'),(3,'Mojito','Consectetur consequ',10000.00,NULL,10000.00,'menu_images/VyMNVM1CqOhCtXoxAXOoFAcaFZI5Q4NcJYfltrQ6.jpg','beverage',1,2,'2025-05-22 10:48:41','2025-06-01 03:38:37'),(5,'Nasi Goreng','dfasf',14000.00,NULL,NULL,'menu_images/kOMhF0OvPf5vz4wBETvetIafAVGDygxpRK9uCnst.jpg','food',1,NULL,'2025-05-22 10:52:36','2025-05-22 10:52:36'),(6,'Adria Richmond','Occaecat et et minus',80000.00,NULL,80000.00,'menu_images/m2jMUfVH5WKawemrHNFpSTYOB3QZEuuXniFD4PU7.jpg','food',1,2,'2025-05-22 10:54:28','2025-06-01 03:39:09'),(7,'telor','Minim cupidatat even',20000.00,NULL,NULL,'menu_images/OjfvUs5my47MIN8jOJgvTXQKO7Drnrs1sUtSFhFW.jpg','appetizer',1,68,'2025-05-22 10:59:56','2025-05-22 11:00:27'),(8,'Noel Hurst','Fugiat aut laboris',10000.00,NULL,10000.00,'menu_images/XduaJaNu788KNVnOaQhvBXOLP2YivsBjgPLY2YNO.jpg','appetizer',1,83,'2025-05-24 10:34:00','2025-05-24 10:50:21'),(9,'Nasi Goreng Spesial','Pariatur Placeat i',10000.00,15.00,8500.00,'menu_images/isw0OPTjswlZ5PZFjgrajuKgB1vTllwCZs8hWYDs.jpg','food',1,1,'2025-06-05 04:53:41','2025-06-05 04:53:41');
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2025_04_30_171237_create_pengguna_table',1),(4,'2025_04_30_171347_create_meja_table',1),(5,'2025_04_30_171406_create_reservasi_table',1),(6,'2025_04_30_182322_create_sessions_table',1),(7,'2025_05_04_164828_create_menus_table',1),(8,'2025_05_06_112940_create_staff_table',1),(9,'2025_05_06_113008_create_transactions_table',1),(10,'2025_05_06_121448_create_users_table',1),(11,'2025_05_06_121455_create_orders_table',1),(12,'2025_05_06_121835_create_ratings_table',1),(13,'2025_05_13_151326_add_pelayan_and_total_to_reservasi_table',1),(14,'2025_05_13_152204_add_fields_to_reservasi_table',1),(15,'2025_05_13_153258_update_status_enum_values_in_reservasi_table',1),(16,'2025_05_14_103600_add_payment_details_to_reservasi_table',1),(17,'2025_05_14_145702_add_payment_columns_to_reservasi_table',1),(18,'2025_05_16_173318_add_kehadiran_status_to_reservasi_table',2),(19,'2025_05_16_174214_add_source_to_reservasi_table',3),(20,'2025_05_14_110220_create_menu_items_table',4),(21,'2025_05_19_172244_add_combined_tables_to_reservasi_table',4),(22,'2025_05_19_175644_add_current_reservasi_id_to_meja_table',4),(23,'2025_05_21_115505_add_sisa_tagihan_reservasi_to_reservasi_table',5),(24,'2025_05_22_053417_add_payment_method_to_reservasi_table',5),(25,'2025_05_22_174632_add_discount_to_menus_table',6),(26,'2025_06_02_062417_create_personal_access_tokens_table',7),(27,'2025_06_02_072028_create_customer_notifications_table',8),(28,'2025_06_02_081857_make_meja_id_nullable_in_reservasi_table',9),(29,'2025_06_02_082831_increase_kehadiran_status_length_in_reservasi_table',10),(30,'2025_06_02_083310_add_pre_order_to_reservasi_source_enum',11),(31,'2025_06_02_115925_add_deleted_at_to_reservasi_table',11),(32,'2025_06_04_000000_create_meja_reservasi_table',12),(33,'2025_06_07_032237_add_payment_token_to_reservasi_table',13),(34,'2025_06_07_032526_add_payment_amount_to_reservasi_table',14),(35,'2025_06_07_032733_change_payment_status_length_in_reservasi_table',15),(36,'2025_06_07_043735_remove_current_reservasi_id_from_meja_table',16),(37,'2025_06_08_082144_create_invoices_table',17),(38,'2025_06_09_062010_change_payment_status_column_in_invoices_table',18),(39,'2025_06_11_151033_add_current_reservasi_id_to_meja_table',19),(40,'2025_06_11_212151_add_title_message_to_customer_notifications_table',20),(41,'2025_06_11_221209_add_scheduling_columns_to_customer_notifications_table',20),(42,'2025_06_15_172100_add_dp_terbayar_to_reservasi_table',21),(43,'2025_06_16_225507_create_password_resets_tokens_table',22);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservasi_id` bigint unsigned NOT NULL,
  `menu_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `quantity` int NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_reservasi_id_foreign` (`reservasi_id`),
  KEY `orders_menu_id_foreign` (`menu_id`),
  KEY `orders_user_id_foreign` (`user_id`),
  CONSTRAINT `orders_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_reservasi_id_foreign` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=361 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (352,375,6,16,3,80000.00,240000.00,NULL,'pending','2025-06-16 16:37:03','2025-06-16 16:37:03'),(353,378,6,17,3,80000.00,240000.00,NULL,'pending','2025-06-16 17:08:46','2025-06-16 17:08:46'),(354,383,5,16,1,14000.00,14000.00,NULL,'pending','2025-06-16 18:13:30','2025-06-16 18:13:30'),(355,384,6,16,4,80000.00,320000.00,NULL,'pending','2025-06-16 18:20:51','2025-06-16 18:20:51'),(356,386,6,16,1,80000.00,80000.00,NULL,'pending','2025-06-16 18:28:15','2025-06-16 18:28:15'),(357,387,6,16,1,80000.00,80000.00,NULL,'pending','2025-06-16 18:29:50','2025-06-16 18:29:50'),(358,388,5,16,1,14000.00,14000.00,NULL,'pending','2025-06-17 11:50:51','2025-06-17 11:50:51'),(359,390,8,16,1,10000.00,10000.00,NULL,'pending','2025-06-17 12:19:43','2025-06-17 12:19:43'),(360,391,5,16,1,14000.00,14000.00,NULL,'pending','2025-06-17 12:25:23','2025-06-17 12:25:23');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets_tokens`
--

DROP TABLE IF EXISTS `password_resets_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `password_resets_tokens_token_unique` (`token`),
  KEY `password_resets_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `password_resets_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets_tokens`
--

LOCK TABLES `password_resets_tokens` WRITE;
/*!40000 ALTER TABLE `password_resets_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_hp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peran` enum('admin','pelayan','koki','pelanggan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pelanggan',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengguna_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna`
--

LOCK TABLES `pengguna` WRITE;
/*!40000 ALTER TABLE `pengguna` DISABLE KEYS */;
INSERT INTO `pengguna` VALUES (1,'Admin Restoran','admin@restoran.com','081234567890','2025-05-14 08:13:29','$2y$12$GieXzfezTNJm8HGDqDx.zelvqym13Lhb4cijMFSb2wOWAlvXmfXHa','admin',NULL,'2025-05-14 08:13:29','2025-05-14 08:13:29'),(2,'jv','javi@gmail.com','311213',NULL,'$2y$12$5//4QBcXc5.70612w7SMm.I4tESOIKW0mgK4r5wy0K5cnxyjD6opi','pelayan',NULL,'2025-05-14 08:13:54','2025-05-14 08:13:54'),(3,'agan','javieramanullah2@gmail.com','087811192779',NULL,'$2y$12$9d23rR5dGCDX1NYQnv3riOXTwngjSUlreaHre7OMsVUYwyB0YBH9W','koki',NULL,'2025-05-21 04:19:02','2025-05-21 04:19:02'),(4,'Nama Pelanggan Baru','pelanggan.baru@example.com','081234567890',NULL,'$2y$12$NKbKvjcaPq/DTbbQorj4bedb9n.CsIwQZ2zbYTbOEjU.BINbRpQ0y','pelanggan',NULL,'2025-06-02 00:49:32','2025-06-02 00:49:32'),(5,'dd','dd@gmail.com','1231231',NULL,'$2y$12$hwMM/eceem5rH2QTJAv50u0.Xuq.ZwAu4qpprdjr1j2uXEkeMn9V.','pelanggan',NULL,'2025-06-02 10:27:32','2025-06-02 10:27:32'),(6,'upi','upi@gmail.com','354354',NULL,'$2y$12$.Vzgs1BYQoqvmlLBjKVN/eSNrI4d7pOTV/bJgLzW//3U15kCKpZwG','pelanggan',NULL,'2025-06-02 11:03:18','2025-06-02 11:03:18'),(7,'Budi Santoso','budi@example.com','08123456789',NULL,'$2y$12$QyB671UWZ.HRa6QOMIvzXeQnnUuhxdjhEQL6Furk5SvtV93ifWfuC','pelanggan',NULL,'2025-06-03 05:05:05','2025-06-03 05:05:05'),(8,'Nama Lengkap','email@example.com','081234567890',NULL,'$2y$12$HFMD6lkoazNoAlki1tGUX.GnpK52zvguc9crFui4aoOONsSpmywy2','pelanggan',NULL,'2025-06-03 07:16:23','2025-06-03 07:16:23'),(9,'Test User Postman','testuser@example.com','081234567891',NULL,'$2y$12$VQckKE.nVFa3teTBh/jrbepbPThKfP9YeDwFc8c/yKjc7NWQa7RGu','pelanggan',NULL,'2025-06-03 07:52:14','2025-06-03 07:52:14'),(10,'varazubyga','gecit@mailinator.com','1',NULL,'$2y$12$qbl200l7gHfmYe.kyi2cFOM5iUS2eShneQmG1s9igduNwrZtmqAoq','pelanggan',NULL,'2025-06-03 07:53:32','2025-06-03 07:53:32'),(11,'zexut','gitari@mailinator.com','1',NULL,'$2y$12$EhN6AcJyz10gkCO/p7/65uTbRr1lTAIoKa4xGzRdlrlBadEGdqJOa','pelanggan',NULL,'2025-06-03 07:56:25','2025-06-03 07:56:25'),(12,'taxeq','kyjavunin@mailinator.com','1',NULL,'$2y$12$Ah/1M9hOrkgRb0VX6k62sOKRcFipYCgH/rbSZpvIY0wLMSyDbaDdO','pelanggan',NULL,'2025-06-03 08:15:45','2025-06-03 08:15:45'),(13,'zedyves','sonykis@mailinator.com','1',NULL,'$2y$12$700ZNEdr1pCxGGmTiDryW.fZS3fLh1r2sodW2lG86JtZ7GdcY3bKC','pelanggan',NULL,'2025-06-03 08:21:01','2025-06-03 08:21:01'),(14,'qwe','qw!@gmail.com','087821828',NULL,'$2y$12$/9/zl3VVh3P4GzkenhfT/epptOS6ZchG0WECch14EG7Rh.3sJo.ni','pelanggan',NULL,'2025-06-03 08:28:21','2025-06-03 08:28:21'),(15,'rey','rey@gmail.com','0983292983',NULL,'$2y$12$tuDHms4UephA29JFNaMxse/oHVl0AQS2ukN9G8OcrqFkxccKYtWu.','pelanggan',NULL,'2025-06-04 03:01:45','2025-06-04 03:01:45'),(16,'anjay','anjay@gmail.com','089854879034',NULL,'$2y$12$YWk8vjn1RPDHklNBpSzH5e4HNuB9aPE4XsjcYB6KJRd6Gz4YJSWJW','pelanggan',NULL,'2025-06-10 04:02:24','2025-06-17 11:54:15'),(17,'rei','rei@gmail.com','081277820911',NULL,'$2y$12$cVVockO70/8gIzkbjdAuPeNG9byRSucC4ce4n8JhvYAb4mKpO/kF2','pelayan',NULL,'2025-06-10 04:10:03','2025-06-10 04:10:03'),(18,'koko','koko@gmail.com','0898786258189',NULL,'$2y$12$1y.OTJvr/r1mmUG2X94qae5WP3OqWUv.SN4Z75Do/GUg3Wf8BvuaK','pelanggan',NULL,'2025-06-15 12:02:25','2025-06-15 12:02:25');
/*!40000 ALTER TABLE `pengguna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (2,'App\\Models\\Pengguna',4,'customer-api-token','487c606f62fd3849aa9fd608319eace5bbc08398ba4efa9d0d44e9cc5cd23b2a','[\"*\"]','2025-06-02 01:43:07',NULL,'2025-06-02 00:51:19','2025-06-02 01:43:07'),(13,'App\\Models\\Pengguna',5,'customer-api-token','5cb2f735f1e3ae790b5d651938da26fddff784180c872a98f5831fc55643cc8b','[\"*\"]',NULL,NULL,'2025-06-02 11:02:09','2025-06-02 11:02:09'),(18,'App\\Models\\Pengguna',7,'customer-api-token','05da988ab6858e489d300ef76422f247e137b174d9fd99ca1a78cdc168d53be7','[\"*\"]',NULL,NULL,'2025-06-03 05:06:01','2025-06-03 05:06:01'),(19,'App\\Models\\Pengguna',8,'customer-api-token','90e2aee7c7410f16cef6c5e90a70f0a9712e546a41e02f6d90ff8069af2f15df','[\"*\"]',NULL,NULL,'2025-06-03 07:16:24','2025-06-03 07:16:24'),(21,'App\\Models\\Pengguna',9,'customer-api-token','ea3f46b7322ec07a044458c234c3fc0d09ea6b7671f44d8fa1b942d21760fe73','[\"*\"]',NULL,NULL,'2025-06-03 07:52:36','2025-06-03 07:52:36'),(22,'App\\Models\\Pengguna',10,'customer-api-token','ee631f671e63c8260170eb500786317850693b6867e8615bbe9d3a1c47876af9','[\"*\"]',NULL,NULL,'2025-06-03 07:53:32','2025-06-03 07:53:32'),(23,'App\\Models\\Pengguna',11,'customer-api-token','fd6b1dbac45d1f8a85e793daebffcd7e6aac379cda6fa8fef20b77f5c3bb24c3','[\"*\"]',NULL,NULL,'2025-06-03 07:56:25','2025-06-03 07:56:25'),(24,'App\\Models\\Pengguna',12,'customer-api-token','50ff96f3cdbdd77001cf8c0fd203052e6c34b079c4aab477e6602bde4f092d5d','[\"*\"]','2025-06-03 08:20:42',NULL,'2025-06-03 08:15:45','2025-06-03 08:20:42'),(25,'App\\Models\\Pengguna',13,'customer-api-token','0b822241b8967bd82a5d03af8e3d622ec0eff408f379dd83a273f48dad149a68','[\"*\"]','2025-06-03 08:24:05',NULL,'2025-06-03 08:21:01','2025-06-03 08:24:05'),(27,'App\\Models\\Pengguna',14,'customer-api-token','958faebc70c6a21abfac180528f918070b2e656896bdbf7ed2fadaaea07b1d78','[\"*\"]','2025-06-03 23:09:59',NULL,'2025-06-03 08:28:38','2025-06-03 23:09:59'),(28,'App\\Models\\Pengguna',6,'customer-api-token','875176648ced9851e415946515f15cab42403dcfae1e4b5cb3c367b59bff2875','[\"*\"]','2025-06-04 03:00:57',NULL,'2025-06-03 23:10:46','2025-06-04 03:00:57'),(39,'App\\Models\\Pengguna',15,'customer-api-token','b0a1cfa53778caa09ea6e256e2548ddb59e0dcd8a76cc47bd1faa1e04e3aaa27','[\"*\"]','2025-06-09 00:25:25',NULL,'2025-06-09 00:20:38','2025-06-09 00:25:25'),(57,'App\\Models\\Pengguna',18,'customer-api-token','02cd849ad46d28952a1a08dd8f7db59d67851664f0950e81323f4a71bc4bcc9b','[\"*\"]','2025-06-15 18:17:55',NULL,'2025-06-15 18:13:20','2025-06-15 18:17:55'),(58,'App\\Models\\Pengguna',16,'customer-api-token','b5391923586ec690a2ca78a7bbad254c46da050279777b1abad57275b22b1cb0','[\"*\"]','2025-06-17 12:29:15',NULL,'2025-06-16 15:29:54','2025-06-17 12:29:15');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `food_rating` tinyint unsigned NOT NULL,
  `service_rating` tinyint unsigned NOT NULL,
  `app_rating` tinyint unsigned NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratings_user_id_foreign` (`user_id`),
  KEY `ratings_staff_id_foreign` (`staff_id`),
  CONSTRAINT `fk_ratings_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ratings_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
INSERT INTO `ratings` VALUES (7,184,3,NULL,5,4,3,'Makanannya enak, pelayanan cepat','2025-06-25 06:59:35',NULL),(8,185,4,NULL,4,5,5,'Pelayanannya ramah, aplikasi mudah digunakan',NULL,NULL),(9,186,5,NULL,3,4,2,'Aplikasinya sering error tapi makanannya ok',NULL,NULL);
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservasi`
--

DROP TABLE IF EXISTS `reservasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservasi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `meja_id` bigint unsigned DEFAULT NULL,
  `combined_tables` json DEFAULT NULL,
  `nama_pelanggan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `waktu_kedatangan` datetime NOT NULL,
  `jumlah_tamu` int NOT NULL,
  `kehadiran_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('dipesan','selesai','dibatalkan','pending_arrival','confirmed','active_order','pending_payment','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'dipesan',
  `payment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` enum('online','dine_in','pre_order') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_reservasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by_pelayan_id` bigint unsigned DEFAULT NULL,
  `total_bill` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_amount` decimal(15,2) DEFAULT NULL,
  `sisa_tagihan_reservasi` int DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `change_given` decimal(10,2) DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `dp_terbayar` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservasi_kode_reservasi_unique` (`kode_reservasi`),
  KEY `reservasi_user_id_foreign` (`user_id`),
  KEY `reservasi_meja_id_foreign` (`meja_id`),
  KEY `reservasi_staff_id_foreign` (`staff_id`),
  KEY `reservasi_created_by_pelayan_id_foreign` (`created_by_pelayan_id`),
  CONSTRAINT `reservasi_created_by_pelayan_id_foreign` FOREIGN KEY (`created_by_pelayan_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservasi_meja_id_foreign` FOREIGN KEY (`meja_id`) REFERENCES `meja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservasi_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservasi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=392 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservasi`
--

LOCK TABLES `reservasi` WRITE;
/*!40000 ALTER TABLE `reservasi` DISABLE KEYS */;
INSERT INTO `reservasi` VALUES (374,16,NULL,NULL,'anjay',NULL,'2025-06-19 14:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-2Q3JVY','jnj','2025-06-16 16:34:58','2025-06-16 16:35:04',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-16 23:35:04',NULL,0.00),(375,16,NULL,NULL,'anjay',NULL,'2025-06-26 16:00:00',2,'belum_dikonfirmasi','paid','paid','online','RES-UHJ7PH','nnn','2025-06-16 16:36:39','2025-06-16 17:45:28',NULL,264000.00,'tunai',NULL,NULL,0,NULL,NULL,'2025-06-17 00:45:28',NULL,0.00),(376,16,NULL,NULL,'anjay',NULL,'2025-06-18 16:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-8MJM5Y','untuk ulang tahun','2025-06-16 16:38:44','2025-06-16 16:38:50',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-16 23:38:50',NULL,0.00),(377,16,NULL,NULL,'anjay',NULL,'2025-06-19 16:00:00',2,'belum_dikonfirmasi','pending_payment','partial','online','RES-680IB0','knkjn','2025-06-16 16:39:40','2025-06-16 16:39:40',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,NULL,NULL,0.00),(378,NULL,10,'\"[10]\"','nn',17,'2025-06-17 00:08:46',2,'hadir','selesai','paid','dine_in','RES-202506170008462c5Cs3',NULL,'2025-06-16 17:08:46','2025-06-16 17:08:51',NULL,240000.00,'tunai',NULL,NULL,0,500000.00,260000.00,'2025-06-17 00:08:51',NULL,0.00),(379,16,NULL,NULL,'anjay',NULL,'2025-06-18 14:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-ABGYHP','ccc','2025-06-16 18:01:56','2025-06-16 18:02:05',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-17 01:02:05',NULL,0.00),(380,16,NULL,NULL,'anjay',NULL,'2025-06-19 13:00:00',2,'belum_dikonfirmasi','pending_payment','partial','online','RES-UWLLQC','vv','2025-06-16 18:04:05','2025-06-16 18:04:05',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,NULL,NULL,0.00),(381,16,NULL,NULL,'anjay',NULL,'2025-06-21 16:00:00',4,'tidak_hadir','dibatalkan','dibatalkan','online','RES-X2I8JZ','nnn','2025-06-16 18:11:57','2025-06-16 18:12:05',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-17 01:12:05',NULL,0.00),(382,16,NULL,NULL,'anjay',NULL,'2025-06-21 16:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-YIF1YV','jiji','2025-06-16 18:12:26','2025-06-16 18:12:41',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-17 01:12:41',NULL,0.00),(383,16,NULL,NULL,'anjay',NULL,'2025-06-20 15:00:00',4,'belum_dikonfirmasi','pending_payment','partial','online','RES-O8DOA6','sss','2025-06-16 18:13:15','2025-06-16 18:13:30',NULL,15400.00,'qris',NULL,NULL,7700,NULL,NULL,NULL,NULL,0.00),(384,16,NULL,NULL,'anjay',NULL,'2025-06-26 15:00:00',4,'belum_dikonfirmasi','pending_payment','partial','online','RES-CD4DHZ','ccc','2025-06-16 18:20:35','2025-06-16 18:20:51',NULL,352000.00,'qris',NULL,NULL,176000,NULL,NULL,NULL,NULL,0.00),(385,16,NULL,NULL,'anjay',NULL,'2025-06-20 14:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-JMRXZR','dd','2025-06-16 18:23:11','2025-06-16 18:23:19',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-17 01:23:19',NULL,0.00),(386,16,NULL,NULL,'anjay',NULL,'2025-06-26 14:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-LUYR55','vvv','2025-06-16 18:27:57','2025-06-16 18:28:30',NULL,88000.00,'qris',NULL,NULL,44000,NULL,NULL,'2025-06-17 01:28:30',NULL,0.00),(387,16,NULL,NULL,'anjay',NULL,'2025-06-26 12:00:00',4,'belum_dikonfirmasi','pending_payment','partial','online','RES-UZF4QQ','mm','2025-06-16 18:29:34','2025-06-16 18:29:50',NULL,88000.00,'qris',NULL,NULL,44000,NULL,NULL,NULL,NULL,0.00),(388,16,NULL,NULL,'anjay',NULL,'2025-06-26 14:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-NTEDAS','njnj bhb','2025-06-17 11:50:38','2025-06-17 11:51:05',NULL,15400.00,'qris',NULL,NULL,7700,NULL,NULL,'2025-06-17 18:51:05',NULL,0.00),(389,16,NULL,NULL,'anjay',NULL,'2025-06-26 13:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-LTE4GT','nnn','2025-06-17 12:17:55','2025-06-17 12:18:02',NULL,0.00,'qris',NULL,NULL,0,NULL,NULL,'2025-06-17 19:18:02',NULL,0.00),(390,16,NULL,NULL,'anjay',NULL,'2025-06-27 16:00:00',2,'tidak_hadir','dibatalkan','dibatalkan','online','RES-XEVUVL','nnn','2025-06-17 12:19:29','2025-06-17 12:19:56',NULL,11000.00,'qris',NULL,NULL,5500,NULL,NULL,'2025-06-17 19:19:56',NULL,0.00),(391,16,NULL,NULL,'anjay',NULL,'2025-06-27 14:00:00',2,'belum_dikonfirmasi','pending_payment','partial','online','RES-NR7CIT','mjiji','2025-06-17 12:25:04','2025-06-17 12:25:23',NULL,15400.00,'qris',NULL,NULL,7700,NULL,NULL,NULL,NULL,0.00);
/*!40000 ALTER TABLE `reservasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('0qN34KN7NYH1EtXOHiKY2ao3Po4hAimgMEWkzfhB',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicXB4SWFrVE8wQXZjOWxxVHJVckY2dllVTk1IRmUwaWVXMEdYcFRCNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162653),('0w7z4qR8Uh9ZmcEQ8F4xxHC7IqNmcGmIW7EsVMDG',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiaHpaQUxObXFPNHp0UzJyVlFaM05Td0ZjZ3lPeHhBRkxXTVpaR3NXNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162751),('1N7YbSxIkAnYxC4noStRmWsLQbgR80iD7hVnMyz4',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiM3djUTA5TkE5cWV1bGhNV0pUVE9veDZWWEVPbGlYMVJvSlR5YU1SOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162952),('1pc5OhjsLRRAJVnnVaxbkJLviDJZX6J1hWJAlG3A',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoid2x6NFNIMUVqU05sS21abzFQajZUcmROdWt1NmhINWxrOFU5eWxGeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163198),('1Ui1POPbCVtlASr4CC2c2SDO5eSyhv2xIC9Nu4cl',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNXBDYWJ5YWg0QW1WMVFsa0JFTU1ScDBqZENJdjI4QWtVTzk3V2VZZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162714),('2CEjTSx6DuffLrXFudnwVK6YXceMJszZKdjGcfAd',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVDJIY21JWHBRb1Y5aFhmcTA4akt6Rm1NWkFxcWNCVGNwRFE1bjdXcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163200),('35xVSHKdPaEXfmt8T4XfoxG99zy2i7jt0zRVJaVG',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ3V3RWlSOHFzY1NyMGpFaHNzM1kxYmFqdHZXbEdEcG5uYlFyV3p4TyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163089),('3X8F3Kks0oC64ul4bIizQdr0TMSFlOMbMV1qEBZq',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWjVBTHZ5Qzc4QlZqSDhIQ2R0Nm1rdGp2a0ZZdEtrWDFQWmd1OEVzMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161588),('4eKJY5RpOhQXYZzumgxRW7fbnwDukgPpVLzYnRlC',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUDM3eU16bE5uVnJUT0tHZGtEbWdBNjJOdkpKN2FPbk1POFhlallaSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162572),('4gK7fKPJPP8csNE2zNv41v6JFMTxsZtBNe365wFH',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoia3pQaFRFeDNBUjg1Ymg5eGRIeHhTWllUTDBoUGJkVHZiN2plTU1FRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162541),('4yEwM0FjvHtX0wrlWOObQbY6blRrcPUwIbCVW9LJ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVWZVa0s0TzRnSFNuYUFkUjdOZkxDQUd6UDlmNUxTeU5WaXJTSElEZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162665),('5gTCjhjNHyxiOcWiqL4h6JrRWFlvE44KOovVElcj',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSHcwR1J2WlY5d3BlTW0xT0FrTEcyUWREYUVtNVJBRFluckF6TjJ6RSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161269),('5sM6gyAOS8EOKYErbkoPBuIS4S1GK1TtYQNA36e5',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiaHZYbXlJdFdaekFXaHZYMUJhM2R2RmE4MjVhYXo0QWhwQ3hPQ2g3TSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161201),('5VGqaKYgPOXeP8F2502e99vwxyhzVYfGf7qgQm4q',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSzh3NFhtczM1WklSRWdXWERKNjRJZ3FxVFAzRjUzZzF4SkV6MGJLciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162562),('62d4UOGVqAm9rvG3aRASnM6Dvz7pJqb9l63pwFOn',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSjFNdVZvVmxVdzdxcVUzSlZVVnJOUDJGR1VjTU5TTVp0RGtnNFdNZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162569),('6hXDnYpTiDju0owFuCXll9Ky4vP247aFrlWgXL8w',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoic1VXWEF1WFFZTkZCQnZXVHMzdGNidFA5Wlc0ZHNDOWVhNFA5MW5kMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162746),('6leAXMaw0ao8Qi1NXXTUpYOumbSSYyTJlETRYTnm',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicnZrVnlRSlpGY0x0ODRuUW1Ob01JYTJNR21kT2JSNGdLY0ZJNmtmeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161083),('6rjRMuaEQSFRuuUVYSaK3qCYrA0kBZVJzPNJ24zb',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWRWVGdXSnVIVVdoZkx4Q2lreVQxYWJhUU9hNTVGNVZYdFZYNFhmUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162602),('6z7hxZK2SpWeE3RmpgkiEIJu41qUrvE5uDo6wTyi',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiR1pTVUFhNnpQZ3JlNTBJdVhJbG8zaldmRDBUZnh3SGE3MUplMEJOQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163198),('7LSvvQpafP3F549UsOI1kQdQqPSAD2q9aYtpNaoy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidkNqR0o0TzA5U1dkOHJZWEhscjBxc1dndHRRbEVNdE91dzE5eWxSZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160991),('7WXHtAtWe5hYocjRYcmyKg6s9TS2LEPrGAUcyb69',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoidXp6T1hmRnlGVWxrenFEQXRCR2VXQ3BRVkY4bFF6YkFPcklrMm1WeiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162685),('8ERqqlmJVQ61iX8WLz97M9lItGh4MrjWMO5Jq5Qz',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHJGTkd2Zld0b29JYm5IUHZJT0pua2FBU2dSN010YUZENDR3SFFHTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161585),('8Ksre30gyjvHTRizqZo2hSUh0IL1RyvNTdBfiM4Q',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZkxCOWVoWmFBWEdMNFhwM2tqZ1BjQ2k1RXl0U0dBWE9xejRSRWdnZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162541),('8SZNgRNe77eTmprqsujY09hUIJaAD8YBEa0ifvRT',16,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVzB6dEVaVDc3Y3ZzWmM2SjZYOWRkMktvcFBDejIxUEpmR1dDdEoyWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161371),('8UPrqyr7o8NgxeLeCw89mCuSCzK46CMCbQdn6mvI',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiYlJPeHVmYmxoRFg2Uk5xTnY0dFNGZm82c3Z5V0tyWmJXYzRva2kwdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162675),('8zWwMFUjnnczNvcKQW4BwOdLvPPRxqZJOYYeyE57',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiV3VZZjBOVjlMNEFJOUZNRm1SMmFybWhHdDY2Rm9lNkQ0OTlUU1NwUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162557),('9CGHGzxmhAvo8WLCyWUi5QmIqu6D0f4gTJT7dCfm',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVWE2RnRtZ2FtaVBmQnUzaW5qWEdwRDBmTjEycm1aaE9tb0YwcTFvMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163345),('9GBuN9da6pvBmTTBhroRX7jJRn64jYhsH9xECI6I',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUlFlVzRLcm9MRkN5TGNTNTdWd09uckFhbDJBTDBLaGxuOWxiaTN3OCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161086),('9OY5TQRh8UMbcYc2LC0hXrL1WuwW0HCGoBdjkjI5',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibU9BSGkxOEdTc3VaS1V4UmY2U2FCQzdRSDNHZ0ExTXVDVFhqRG01dyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750163201),('9Qble7Ldxcr2R4WqADi461QJh1AcF421xcqYjsKD',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoidUQ0ZmZNejFHWlVXUERaV0NPQUFqa1VOR0I3bUlXOUhpNjU1Z3N5dyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162689),('ACM3SeeEN8089i9FJMxutHSqjL2HjS1dRO4LaMbe',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSUtCa3JiOXFGc3I1N2Y1N1RVTXpTbEVoSmZxTmtBYmZEUlhLWk5aQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162677),('aCwv5Cdk36h4lEmqTwymfRJxmP9dq18QNL4i7HYq',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNkt5SXRFeUhzSjRFdzdIcnFJVTBYbVA2RTQ3ZWZScmxQYW5YNjEyZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162651),('afXc0Ctifcsjxr1qTYfkoc9RVa1WVwPOHyXc4vZW',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRGw0U2V4b2lSRTdsVnc0M2ZLRzJFUHpoUG9yN1ZPVTUyNGk2emFsQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160989),('aHtfNSFrgZq9gsSPCTqOkJPEbD4C6LeOexreErr9',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZXBEaEVIQU5ReDNXTFNBdzNROWNRSUVHaWtiVnVmS0tnSVEyNUprTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzkxIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163191),('akPo0CrWLMW7pO5kjvFlF5srNc1bpKel3sWRK8qH',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNGplUWRZMVNocDVYTXpXMkxidmlDVW5INjlTREN5VUFOUVY3YjZoYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161171),('AM7Ir7FRbrVrSGEZ6z7YkXSYTRnjxdYzoTSSo7ky',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNnlwVzc4cEd6S0Q1QXFNckFreE9FaWU2VmRMdjltQkdNWElwZmNyVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161082),('AnF9Br0I4KbrBGRBtGAPXNvQmtVDfAnPct3KYua2',16,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiM21lZzlYZUQ5a2NXRW54eFpoT1ZXRjRtOFlRVzFBdzN4eVdNQmdtRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161369),('apsHOc6oArOW1Ow9Z8yPIUFTQdT5QqnESzNnD7w7',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0ppOUFGc0VoeUFVM2pYWlpQMzZpRHNnVUNacVZiWVNNc0JVc2JEayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162590),('ARZxQ87XUc7nu7ZFH26Klr4AnmUg8vWy58hSdtYO',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRmdMS05QWTBEUlQ1ekxHY0xYRjN1VGpJd3l6OGxYTGdzMEgxb25IZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvdGFibGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160996),('AwjsJi0rE4OAYyRFhySV84lL26DxUR28fkfQ1GEa',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQzdEU1JRWnVxT3dDZEZyRjRLcEZNTnhEYTlqUTFqNjF0eFhoclNIZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162570),('B6CPSLahhiCh3SEcCpjfDqZxSu4rw9rVP4z9G1AC',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXlvcjhFQUFXYjlHNHhRSXlRNGVuOVhqbEVSeGt3RDhyUWIwb2RyVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zLzM4OCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1750161108),('BbQMmps6GCr7m8NIRtYQ0WsxrEHD85KXqJB3S6ul',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiakw1Mm1GdnlRZEF0a284S09pV3NsZDZaTnl3ZXlaOGgwQnlmRDV6UyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zL2Jvb2tlZC10aW1lcz9kYXRlPTIwMjUtMDYtMjciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162759),('Bicl6QhuWolMrphnKDiJZfo89BKtyJCTg4A1P5nJ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoib21CWlpPVlN4QkI0Mk9wYXpVNnA2Q3RmcUFpa2hPS2tla01MalRkNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162755),('bPMVfNndSAUh3AQfNqwF8qsar63zKgFm09cwHSk0',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2Y0RldNWGxVRklhblF5UWhVQzAxc1JUc2tQRk1oV3dUeW5iRkdUMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvdGFibGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162661),('bWLpruA91p9CPdA4hq3OcZuiO5oAbNY5JORhRIqE',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidzN6RXFCS0lpcDhLSWNaMEl3VUxMVW9ZbVl0YW4zTURDSUVPNU52SCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162617),('bWtiqIfOMYDWoBmv9Iz2tlGsX9lax3wJgv1Z83TB',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVDVabnlXeGI1Rzg2ajFLbEFZOUlJOGlkWE5ZbUZqc2RMbzlGTXQ5diI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163196),('caLlET7p2Tvl3NM9tmgdpTOESRYLqM3kcsTnwjnI',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoia3ZqQjJtTFJpMEhWU2ZMZXVNQXFBa0hIbUVjTjZnRTdOYnI5UG00ayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161040),('cH7RwMZAAZRIoIyi0NyHZllMsN00EFa9XsGeumhW',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoieU5ONGxaeVp5ajl6WnY4M3J4NEFEMXZtbWVGbGd3RmVHcDZZYXl2OSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160986),('cygna3u4GmwT0TgFUZHEWt3zEUYKzxkLJDcsBHsT',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUzBZSTZkZ2FXYXR2RmtNcVFTRlB6NGx5RmsxVDYwSTRHU0hRZ3o2QSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzkxL3FyLWNvZGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750163192),('D8cMxLwHYef5rNc5bN2MkpAYhE4Cl2THIkBXC87m',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVE05RGs0NG5UMGFLbUtKbEhDWldLSnJUZFZVY2huOVdaWjZtQzV2UiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zL2Jvb2tlZC10aW1lcz9kYXRlPTIwMjUtMDYtMjYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161028),('dDo8gqT0CmbcspuLIYrVeFoihhuznz6dl57jkhGe',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiOXpJclhPcDlsZFBEQnRlRE5La0dyOVFBdmVabXlZUW01a3ZsU3F6ciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163114),('DKiswspmmanVhOKH2U8DTlt2CsJ1OnrvVKXkRA4C',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiU3V6ekU5YkdxZ2JDVkRWN094S1ZvNm9aWUJLenJ2eEFMSmprRGk5QiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162525),('DMfswECBwpiU76AJQnekeeghoqyHmikAEwMoGVg9',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiYkEzWk1XNFc4M0t4VmtFbDBxRzNGcjhEWmdVQnN6a01sOWtVS0JtayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161265),('e9KYVNeeuWJ39aYLYNNFIjP2Jk8qSFuXxhAFc9AG',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiemRJQjU5NW5EWGlhS1JKNkFpYlVWWjZCZngyQXNTVWNnT2VZYVF3MyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162853),('Ea8T2MExLWhvsje8qSmEcpfSEFfhpvZFyKOsgIvu',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicE9QNkV0dXhVZTcwMU9IUGdqQ3hqakVKRU02aThmb0dRV1ViZ2VITyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162854),('eOVmCKkiatedYBQesqrc8XQonrDNgX7GAxk92hUm',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiUEdHYVRlcUZqSUJkeklxUnZYeEc4eGdCN283QUFoNzRJUDJFZFplOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161121),('EvtgWIA9ET1hoWWgjZQ9ahsWI2GxJ1HfRPL90Qqc',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiamhYY2ltbEV3MUdRaUVXczE0RExJMlJFMEU1Q1pKcUxPUE9BZnpDWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162563),('EXmPme3S6DetVkxZT0gH1t0A1QdEb7MC1kv5K8q9',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoib2o3aVZ4bWF4aU9Gb2tUU2RQNjdub3BHRUg0cnZwVURXeERXWXEwaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161266),('F8I8woB3hwxfZoxYi0t7ru8KR8iucXFMk7mYYA48',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoicDdXcnpYVkR3SmJKd0FHc0FlbFA1Z3FXbzFoNHpobzZ6cGZEVkJTTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161255),('FKiWdXl3KVWoDnwFJik9INaC6XPzhi2QhVkMwYPt',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSGs1d0Q5cng4RVhmWXQyZEo4dkRoeFc0UkpySVVSV3ltcDV1V2pPWCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160987),('fnUJJFRbRYkUGlNS7wITq3IVXXLdiPOZIpZcBuuA',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZDVVWWNwNVZaVzhiQ3lmcGNaZDIwdHIxaWZmcTVKa3Q2WEhydXkyOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161024),('FpwUEiw1BAvZgrIQ4f7g2VYgbTvju3tyjssstfnm',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibXdHNWhMd09xR1NUM1pBZ1hrU0ZyU29rbG5pNGg1dzVTajBkTDU5UCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvdGFibGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162752),('fUcFRnuMEUnTAY7SaF5fMd4X1ziN4wtEPPbslMrx',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSVg3cloyRTJNdkhEVTA2WTlTYjdhSG9IUUZMQUFmSkFQRkhaSlhSdCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160995),('fYtMMy3AlqzP7fALwsyzmAZd1FB0WM1sflHmUsot',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZ0FIM3QwbVNjamdSWkt3elZVT1d2em9lMkpBZGduR08ycEJ2QmJKYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162778),('g6UZBbmWyM9e9cwXzAnyalwsMNM86NnuBQUPsrIQ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiUmxpOEJ5UjFqZnFYRWttOWVwa1NyZGVtd3ZXcjhDR2t4MEZ0ZEFWVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163191),('GeNN5SLPg6AcpKflqsgxjx7IINDbc7c6zzWNHfp5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRWlSRnphM2daamh1aGZBdXhUZVI5NjJKeVNpcm5LNU9vRk90a3J0YyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162631),('gH78UmCIOxiONN2tw68BrY8ZsyiJGCLbfDJcWFk3',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiczJ6OUVGTkE2dUd3eGtzcEZ6RzdTMUlDeDYwZXdXZlNQdHpyOEtheCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzg4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161108),('gIGd5j9pxqDRF9qoGwkjD5wNkFg5Vftl7DXPEg4b',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVWM4Vmp6dTFXWVdTZUtQb0F6VFJ3dlNpZjZ5aVEyeHB0WWlMZGp4YSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162627),('GqM8e0uzIQmuBxK1AHs5mdvFWVVhUZ8oTMHr0vYZ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZDltQm9KUnoxYVVpNWZIMDR1VTB5dTFRc1B2dk9sRklGZ1NnYW5PNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162651),('GrXC9rzbEWKh0cj4HEhFhCvb3esLn0dijM44mLUD',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiOTFLc0U1N0xJUkwxdFhrSEhlTHA1NTkwNHdKRzNMbVJLMnphUUlyUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161116),('GUHMvEsLqHRiFnffwgVDIxGYxg8y5MwqNsGtksfJ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidkFHS3VXTHRaaDVoUW5WUGVWYTJqTHdJR2xIc3RxMWo0ZlVvWHNNMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163087),('HggudwnGzWu93GbkHTInzeV4FzHdXixWTi4UzOmE',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZUNGUFd5WlFZSEV0am9OYjFRRlloYXE3Y0tkZDJ4R2FtVkQ3RGFhTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161081),('hhPYoJUNDcdytsore3ktV7PLvBpZjxEMzYUxgCYC',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNDFIQUlwcnRZTnhTTGhoYzh2d2tlcGVFUkRoN0ZDRnEzQ1FKaTJaayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162619),('Hvgq175fjgA5c8VaFMJHfCbiAmuI0RHHhLKrxliY',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiejRPTEFQVXJPVmFNTVcwOG9ZUGxSY2hHWG1sYjM1emptT0VGYlJqWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zLzM4NyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1750161118),('I26zYAhIOIA9t8bYxCtsbBEFlHEPbTm9vHmMKOwe',16,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVVhhYUxncHFISjdFY1dPZ05qNGxpcXBIVWJ2cXJlbzlJVldGeEk4TyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161367),('I2pkbpATAmAQTJw0zz20J9peuvkGOjDdtDBhGSWb',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVnBPUkRodVJXcG9DSUpUVXZXYXV2NFozeDMxcURpTkxHSkE1dkt4ciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161038),('I9RVCIFW7Sge38M328mrxX4ALmb5Lure1S92o0N6',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMHRRTGRzZGU5Vms2bThzWVlxb2hxcTQwY3NXRFJZdG9MSUtUWENNVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162687),('iETnUsWeyR6sSWFgmkSC6VBLmW2By1NeIjp5PWdb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibHF6d05tYmc4SEpmVjl2emhtbmZ6VlJFSlpjd3R2bDFtYkhQdG55ayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvdGFibGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163090),('IH3SR6LAUyN7JT6McMBS9pWV6zo8RYB2431E9CqV',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVzh2NGZacnBKTnNSaHBXRzYzYjZLQ2lUR1FIU3kweE9rbzFKeEFzNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161178),('IjXnyzWokNmLrE24ns3WHYTbRU6tZ7gUePVyZt77',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibVh4T0N0RTNVMFB0VXgySUtYajROM1BCMlpUMk5neUZhV2I0bTBERCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162953),('j0l2q26UHQovE2PqVbPnB9XOLsin2YhzXGOpmwvB',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoieDh2QkNqNlJOaGZMblRSUG5yUmVnTDJRQ2Vva3RPWHpYQUxKNk91OCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162805),('j40R7by5gz1P4fUOoV2niRIdkytYz5emouVC6Nfv',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiaGxnR2hmZG5nWHl0d0ozOTJzR1NFcWdLRkZ5OE92aVZnNFpXSUtZQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162769),('JGloZso8LWh5gUitOgvag3nTaoi4yWYSaeyKtaq3',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNlZtNjR1T29sMkJOUElmZXVEdWE3ZGhWTFFGUGZqbGhYSm4xTzlzOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162722),('jIQjqR2VtYEZFWQmDNoT7COWrlS9ELoSz1KsQjIq',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZ0syQlI2N2Jra1lIcjQ2dkNHY2VuV3IxUjFmRVU4SGN6SVBrVWlHQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161352),('JmCUty8cIv6V6MAkrw7aprZqxP6ODT5Kv4x7DPgk',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVWtGZ3hTMnJremVHWG1qcGFEaFk1Z1VPbnlSUlkxc041Tkdzb3Z3RiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162681),('jx7z7o3BmEkyhgrQmgbPWhvIN2KAHriArJKTPzfB',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiUHdsaDI2QlYxUWp4SUVHd1B1cDhkZlF4c0N6UHhFVTFzRlh4VktNNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162800),('K0bKW33FFILDJESc6NW83H2rKhsEFNAkBepT4Oye',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXp0c1U4Q1B2andyclNsNDZnMURPZVRpYmZRcHpSREJMN2dFeFFmcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zL2Jvb2tlZC10aW1lcz9kYXRlPTIwMjUtMDYtMjciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750163097),('K1lM9TSB8JFEyqnT6XkYpYL2gsgzbIYGCmPTvtkE',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNG1RVHBPRXBjVFVqVmdnb1NremhKdDNnR0pzd0JLVDJrS1RLVEZ2aiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162660),('KJYNR0fRyOtkBYN9mRTk73rDVAH1BpAc2hitvdAU',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNnQ3eENMa0dlalZIQ1ZTSUZrOXlrYVRxYkRyT05PVWxqZ0o4Y0tHYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161105),('ksnTmjyYcTS671KMj3QeuRFKco3rveWyLAzigriL',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoidE01S1k0bXkwZlBQWW5sYlF1QkVOa0o0Uk9aQlY0TTlSSlZDUGdqYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161064),('kSo4quHHvI0CrtdP15pVdFGcQPxlM5OZjZqPVkui',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0xpVmh6OUtBV2VEN3BKZUtnWW5OamRHQ2VnYlk2VFVvMlI5TkY5eCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161584),('kZKuW3q8EL5ItTkjEEQL8TogGl8cC9AsVseLM2qJ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQjRvRnRzQVZhUTNROVBXd2l4Y3Vydk1KSkdEYlA3Z1ZpT09LVjVVVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161273),('lJoI1TBXlNLiSYpNbPz617sBM2XMEfPkPARmnwlB',16,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoidG03S1dRVUxJcXJNR2RjUGNQUUxTNE5qTnZrY3RpbHJGUlV2ZDNENSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161370),('lJuWP0UyaiUjMxZiQBpcVqkRVxe1CADpfBGBjNPU',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUERLUG1RYnIxcDVvN1FtWWtZYkdsS1FPUjFYMWJ4MUdXanA3SWsyOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163011),('lkNyrz3iyR3QObfNHjVZYSOO1NlOgVZK5soxiiZE',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiejZDS3pZVHZ4QlF5WUZDS1VXMDF5cHBFWWczMU9Xa1JqelN5RURERSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163346),('LMRgDNkhqlZNVEJz907BFhOCiVRMmsgFtF11YHDq',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibk1EVUI3azFCNjF6YVVMQmc1NFFvV3JIZjM3R1BsRGFyM0ZiYkg4dCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160990),('LoMwdUVHXj7SQFC3K7SM88f7rYQGEoSdmrgREnef',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSE1scjBuNnBTSkhmT0YxSVV4VGFqRnZzUTBOMXZtR2VRQ25RM0RqUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162630),('lSRapSEtBaLlbEftRafJdmeQ635M9Rj4c4JBv3po',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiQk1nWjRwTUJMZDgyUVlqMTZJdnRPQW4xcWFudHRNWVIwZGNYc0NDQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163255),('LtuSD5J5y6NLi12RuaXEOhkvanwcjl2JmO0xb1Vc',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoieE1uSmdUa0RLOENGT0pFQ1hoU0RlMVJ2UW9Ua0N4WXkxUmYwVmNSNSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163343),('luoO9MTTHiNT4lfgKYMOSYw9Inje3QAbDMPfxT7X',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibEc1S3ZzbVE5SGsyWmpuUFBoWDdFYlZPVVc3Uk85VmVUeXJsUElnYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162690),('lVfZRFjqNw6QlMtCWUDmlzlWOKDpO7bkslny6tih',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoickNsZG9kYjZXcTVTekczejdFcHpJMTVDOG1TVkJuTXJtVTREZVhWcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162854),('m22xchFVYx5qVesRXDfhu1aiGTFFrG90vvz3EMor',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXVPQjlnVk91VmRKbnRURjRhUWg2VDRtM0Qxa1J4c3JEa3M3M1IxVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162631),('m7v49cNGb2TZLcCEKw1qdZc0WWfQFfpPwKmwKrf2',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNXA4UU1Ta1NwMGFkazd5dENXS211SG5JNUVBNXBOWjhrZkMwamF1YiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161080),('mrdKm7wVyIYGkkr6VbbVs2xAvOJYt4Zqx2U5osC5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibUNIczgzTk00eEY2cTg0ZkZ6SDRWSmRkNmF2cm5qOGZVTHNTSm9xMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163106),('mwgnOWMsUM0PZkOhmswq0TcuRNpqxaawhQSF1Clo',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ254cnd6SHFPcHRTdTJ5SkIxYVlkRlZjeGE1QlBxVjlxOVdJdkl1WiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161075),('mX1Hk11obPCGTaqkW0KAgJNgMa1gWhyhFV82NDqk',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY1RWNVUyYnFSOHEzczNIaE5zVXpjNEpZdVdycmVhUWQwNEc4Wmx4bSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162807),('mz86Kbuj86QZShfTYyyuuiFKNb97Z3ZVwRzPJwfy',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNlkzUEtQMnFDYjkyT0JSYmZyUVczSXd0Y05yUmxpYkNhWEphZGNqbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161182),('N3Iwi1eHlc9rHNZ7lgD9MfUAilliiNxzwudnm9yr',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoidlFqckt3eFhDOWlxZjJNVTJNT001cnVrNGZFTmdzdkVoejZsVWh5TSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161045),('nesyOlzlXwVGWDKrcGtXwtoFKhIqG22jUbpSRup6',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoieWNabTRsNUpVRjVzeUZNazFiZnN2T2tRQXlHS3dWOVk5UnpnQkY5ZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163010),('NKMKDkFaPNlJ2Ay0WwFXJDRzbw5zMdMLS8PRafRt',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWxReE1hMU91Z2ZIVmp2WDlvZ29yMzk1bzhuU1BRYWR1eUVCNU1qNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162652),('NmdnNPBgLSob7vnV7xjt5d8FUz3BjF0K92trwnZc',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoib0lsYUpmd2pUMWdScmV1MFlUN0lFSlBRUVg5N3JMVG5Bc3JlR3F5YiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162785),('nSmdF9zWuCKGFY7jUvdySvsbaLITZTJ5r9aWkV9b',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoia2dNRFU2NEFqcjN6dFBCek9yOTRib0lHR2xKVko2WEJSb21kUjJDayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163105),('nxYsmrCXPlQM4yXw3TFSEUtS2BMIiG4PAJ3ZgiUt',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiUXNEcWxnTkZUSzRrQTMybWxCQkYzQTlrd3l1UFh4bmJlZXM2Z1JGVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162676),('oAPvh6GviVr7nFkgLInoyUw1A5QJUXbyr0uNeFSc',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVmdVTzgxVE85czJYTXJuQUFaamVxWkRsWEVOQWNXaFNXY0ttM1Y3dCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163124),('OBzsbd1uMPOboGUSWOvn79qaArO9fDDLK3WazI9b',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ0M0eHpxOFlQb0Uzc2ZQYk52UE5nVDdiUGNlUTJ3RmZ6VWU0ZE9FVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162735),('oJzre5sGO8thKaLEH7eDqmvja9SORcA9u8t9zjJ9',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoieEI1RDN6YmE2ZDhXeFlmcXVOYmFSNGM5REg4OFlTTDNLMERFaml0USI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163012),('OnMlh5QkyOWzAgBhk4GsSRdtfsnE21j5SkROHdJs',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSTBpSjUxc3hrRzM2aTljWlNpVUtIODdlY0RsZDB1bUVKcjBCdmxDUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163254),('ONUgjmoVRfFwqWXTNYQHYufckyk8XGQZPgwW7ROG',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTFpUM2ZuRFlqTHNqbGpCdzZlRnRXc1I2N3RiMDduMEl4d3BqUVZhUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162806),('OVR4y4VxJQo3lBcPw2O1F0lIGrunIPZHPbnTO1Br',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNzVoZFJvN21mQzZMb0pBRGFJMmlVYVBKVmg4TXQ1U3d5QWZoZzZiQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162770),('P3tIAEIbN8qIOBTtoeMVAadA2eoUMIx3rFtDZJPH',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV3BHaFV3allWcEdZdGQwTExTTkVpdEtBMVhjTnU4SGFGYnp2ZnA2WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160989),('pJi0fr34u2ae0hiKzruwN2m1fngmCB79Gm64dzWC',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiYXZlTURhM2RSTklIVm9UNUwyWFpGRmkxbnZYRXE5Z0VPYWxUbUdiViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162795),('PUfiJzzxnmGSFyKpqlHS4qSH5XkFI9GWcjyVmGjb',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiRmljTWhrY29oWWl6U3pYT1dVRXFmMlhVTnpkMXI0VDVOYVZEZ2ZLUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163346),('pvQv9ZPMXsAeLYmWa7GWXeGoOjWpg7dJ0u8pJ9Zk',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoic0hZeWU3WGdpZXJBSTNTQWZRSWdrSTNFR2ZkazI5RmFjcmNLdDkyVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162796),('Q1AeEQ6HjqQ8yVeaKwvKg9DC5DYGLhKGxf2bi6Br',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZmlDbEhneDFTSDJYR0xDMnlZOG9XMm00Z0cxVDcxV0RTdGI5ZDJROSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162682),('q9so0rwK5mEQWytHtsxhCYXxE64oFGjZuBCoucOy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoic01HZm5PMjZkNjBFS1dOWnJxUE1HSGltazhzdkVwdmZkV0hLWVVzdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162771),('qEOxvILQ3Hr3SGI9hNnRsYCaBzHxvDM4oNhTGd2D',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiQlh6QmJwVFFjOXFqcmFpcERSenJjUmF6VGxoRW84d1VnVzA2ejZ4cSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163093),('qj3M7ltDh6RnQtTU19RIB2jPvGJbbQ9fWXcovghi',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUENCWDZqb2gza0xqRGtYSzk5NVNRdGZLVzdPdnpwS0lZTnhRaGw1YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvdGFibGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162628),('QkUWb4Zu2z3W9Lf5DP22uOjb3YBDkJU3pXDUm8pQ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiRWlxVEtVVzJXdjVrek9XOTdSSngxSldwaDNtZ0NiMmtYRmZBdGU2cSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163355),('QovfTSfL7VbCCn8Hcs978m0SKEmQLoYTOqDLb8tL',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoicnpvekVsNk1aSkJ2YVlhTGdSRFdEM1pqVEZtZnp2M1l1M2dVZWwwVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161065),('QQBc2Hq3kbRFmJ46DpDSoQDoqrLu9f01gh7COdQJ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoibzNNNEdsRE1NalNKMGVqZ1lKY0x3SVhtMGtiWDVVbXVoaDZoWld2biI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162851),('qSpOArBfLzxAqsD0pvFEIuPA5fjF0PKlrZQRsknJ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNFFIQTc3cVBuS09aN3NmR0lrMnVNRmVIZTJvRWNRSkoyTTlFejVpaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162799),('RbR8w3iaWSXZNh4E1xO88E4krNrcbqc3LKf4opn0',16,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoicFcwdTZYQ3hIUldFMGdJMDljRFZNWllCeUxlMkQ2eFBXM3RJaTVFSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161370),('RfCfYcgwAXvISxgFJX0bxffmgtdInaMOif3FwG5f',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiRHBHVkdOU1V5U01oNFRNUFB2c2NxV1g3NmdXYmlUU3RkemJCQldTaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162571),('rTXEUerFl9K1wHE4sYtgoc3VDxAW5XUJhRORodIS',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSlN4bnhsUUg1UFpQbGw3MTYyT2tJWXZCbVlIRm9IdzRva2hKUkp4YyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162612),('rxHa0jpvNhC5ZUPNCGJ2jxGZrSj00NDc7MBkBH7i',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZEF4dTI0UmJFVERiUDRGZFhVZXpaODRpbjJDRUlUU3Y0Q3JLbXZxViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161039),('s8MghrfytVT8R5CZ5Emg8KV6mnVh705VzftRKPet',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVlhpMVkwNWZQbm9VUVJlRkNCTlEzaTdjQjNabUV5eGw0NUJ6NUxReSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161113),('SfEAx4KcAemk98EG3BCrU4JGD6lWbLGjkfJWKiiv',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiN0NrVll2bjVValhQM1VBbERtSUdjZVFxRWUwOThVTm5IVXptTHlJNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvcmVzZXJ2YXRpb25zL2Jvb2tlZC10aW1lcz9kYXRlPTIwMjUtMDYtMjYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162670),('SKf9fl2E0C0lGTFGiiLYLurGFre3QBqiE5RXWX7z',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmlVZmdJU2dNUE1Qc2ZXVXM1cncyTHQ3UEdyVVlrRHY3bk9taXhieCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161268),('srB1znCq8uIPAauy2qY2lKTY3KKGfv0aCySndM5R',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMVZ3Z3kyM05ialJMWjM0eUw1NFpJMzRZWHdXU1BFcnYxS21GeTJJUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161053),('TNJkBPnQuiMJXiMl7I7b9TzOjPklgO6bbekxgW5m',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnJ0d1BKV3Zwb2RiZGlYUFFKYTdpSXlGM1NOQTJsbjUzNnJoRk0ydCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163353),('TRbAkowdy0aovrfevM8vpFfCYRjxzg7QVr983EnN',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZEJQbVNqV2FuTmZSS3ZndXp1cEpBeUhpNFVTS0c5Vm5McXFlTVZmdCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162525),('u54KNzIgTrlEVeoNvK0glTMl3RTTb3weAd4XdOuI',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSUNoTm9MazFrOTk5dDlteGFvUGg0RlJlU2t2WmNNRUtHM2FteDZYZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161069),('UG8FzTpOMFrHybOpBJaRxkaEWiLEchFN43Ru5GVD',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT2laMHE3dEF1TktFS05BdXUyNHZYUVNkSldvazNrNVhFRTlaRkttNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161167),('uoD7ujsVPeKQMBOtDXgrjEtj5SBsD36XxQ44aZM4',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiaWtvMkU0VHB2R2kyc0pHNkFaM2lWTXdUTVJxdXBnMVdtR1A0NFU0eSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161071),('UsW6SD8INHjtcT6orm487Dyl0S77OQ71jvgWSEgc',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoieHZ3M0pYT2Q5ZjN2cVZkdUtlVEdSQ0xjUTYwbzR4WjdaQXVKdWRYViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162617),('V30PCWoNqdm4XctNFci5yoHaMkwrPHP3vJO0jYCZ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiV0ZQSUR6MmZsbDFlVDBtWWFaTWdOWmRmd2FPZjlHWHdRODBxdGM1SyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161170),('V7bnAvy04CMH0fGZvkVnWUdmiMi8dwIKQjlEavTH',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoialVJeEE2OWNFU3ZIVndjQW80ekkyRElod0N3aGJEa2N6YUEyVUNwZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzg3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161117),('VtqJ9FY9Is7MjTWirIEfGfuFx8jUTwULKKxTjkwc',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoibnRrTk8ySTRuM0h3T1dKdDFSd1JtRWV5WkJlc2djdFdRRHNQbWhNciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161271),('vwGTKVUI0vXYQggg3PxO6jyupdnQfE4tyBVSudOe',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoialN2WExUV3Fmb2JqTlhITDRDb2J2dkJYb0VnUXk0aXpyaVFwcUdoZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750163356),('WedCLYf3btKJkbfSWqW1Qsci2VT9zgTMEfFAIsAx',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiT3ZSUENvQlV2OGJZYzI5enBsNFMzZFV5aXJWOVV5cmpJS3FxR2JidyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163104),('WMZKXcx1pUhuv6ooZdYUf3jBIX13DJRybntkiKPZ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiWWR4aUxqTlRRQnBmdDE1dXkwWmJadE96d1BiOWtVOEtmdDlITlRTUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163251),('X7D2idAeoW7ToiDaxzmAor0KRrF4USbZ8EXXGa4Y',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiSmlHSjJ2cmpMYkU0a0o2REZPM1NZdW5Ebkh3SmE1eUEyT3BxOGtKbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162797),('XCZIdMa1cQX1FNgRs50ww40cCR55Nm1J7w5k83Zy',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiVm5nZXVPalhJREx0V3dxMldoSnBFSlZ4UHNJTGZrQXA0Tko0RzJYeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162808),('XufQmT5QYH9sDLMyFUqGYx20mMkyJvb9xldqA4o9',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiRkhPUjBNMXJrWm1ORWE5bmVsSlc3S3o3bUxsYkFGZ2tzRWFGSlczbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162824),('xuldGPYcUYBT6UdbD0mUbpAFia3ospwTcpCtIXDD',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoic1NZT3M4T2dVQ0paZkZHbnEwNDlnTzE5dDZ5RzBpV29GSVR2dEUyMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161260),('Xy2Dgp5UcXiSb9DqYbpQ1fYRHSg8LjxsY2Fc9yfD',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQWM5SXRxN2hDeTl3OE9jb1hRWEFQc3dBeGV1eXhFdmo5WmVnMU1PeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750162809),('yAJoGFukO2n2HnP87TethIsnqCdtG3RI2Nb3PF7U',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMUUxbldHZzVCNXJPUWcyUVRmcFRiWWUxYkNrWTByQ0ZQWUxsNXJ5MCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162749),('yd3t6YbQ5FEsPHR74pSwUsfaOvK9vyz4O9alf1j2',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiMWlLWlZObFhKQXZmSUc5MkRSRHd2OVZBWFVhYmIyVEZ0UEZwdVdSSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161066),('yFY4LK1aJ4SLAu35RK4tNap0YIFj0R9DlM35Pzj0',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZkdDT3dEUXBXalJCWGZxZndGOVRQeVVTR213bVFrNncwZWxGekZRaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750161167),('yMBv6YWcT7MKaeZR6yQ5yTc1kJmmLPrzWprNXydt',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnJ0dlFhZlhsUG1sNlhWOTZBcHB6T3RKUTQ5UGxoMVJ0WEd6dktEbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbm90aWZpY2F0aW9ucz9wYWdlPTIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161298),('yUw1hzc3ba31nNZWuIfB0MHHtSue8ZUcjEQUmEjr',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNHBLOG5FZ3J1YUZ3MUtYeXhzMmRMVnQ5c1MyVzdUTllVaWRYSzBXVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750160985),('ywVxBJfnoRn6VJuzUjTlj6yI2CclNRTrydLrG2nX',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVEJHVkhFOUQyaUNQVE1pa25Wc2RYUko3Sjh1NFA4MW85bUxSemhkbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvY3VzdG9tZXIvaW52b2ljZXMvMzc1L3FyLWNvZGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1750161172),('ZO7RtKxolyP5NrYokGnwSArZjOoChM6nQh8znOZW',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiNzUyekJjSjI5czNzNWFnc0tMTkhHZm85QThLUjI5aGFIN2RBdkZTcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162805),('ZpJyjXeb7BeTbB9yqTALXHPQC7PtYFGNNBZrYkHQ',16,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YToyOntzOjY6Il90b2tlbiI7czo0MDoiUHVRcTFCQ1NDSDJNc2p3bWhwSktSekRUTlhjMnN2MDlzMTlKT3F4TCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750162557),('zr4ZfZ2FlUCT0O1thdiawlAqdmdmdLBaRD1koosT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNmk3RFBKWmtEWnBvWWNTTkVBb0UyTHhPYUtMa0o4WGQ1bkd1d3ozcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvY3VzdG9tZXIvbWVudXM/cGFnZT0xIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1750163352);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservasi_id` bigint unsigned NOT NULL,
  `menu_id` bigint unsigned NOT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('belum dibayar','dibayar','batal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum dibayar',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_reservasi_id_foreign` (`reservasi_id`),
  KEY `transactions_menu_id_foreign` (`menu_id`),
  CONSTRAINT `transactions_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_reservasi_id_foreign` FOREIGN KEY (`reservasi_id`) REFERENCES `reservasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (276,375,6,'Adria Richmond',3,240000.00,'belum dibayar','2025-06-16 16:37:03','2025-06-16 16:37:03'),(277,378,6,'Adria Richmond',3,240000.00,'belum dibayar','2025-06-16 17:08:46','2025-06-16 17:08:46'),(278,383,5,'Nasi Goreng',1,14000.00,'belum dibayar','2025-06-16 18:13:30','2025-06-16 18:13:30'),(279,384,6,'Adria Richmond',4,320000.00,'belum dibayar','2025-06-16 18:20:51','2025-06-16 18:20:51'),(280,386,6,'Adria Richmond',1,80000.00,'belum dibayar','2025-06-16 18:28:15','2025-06-16 18:28:15'),(281,387,6,'Adria Richmond',1,80000.00,'belum dibayar','2025-06-16 18:29:50','2025-06-16 18:29:50'),(282,388,5,'Nasi Goreng',1,14000.00,'belum dibayar','2025-06-17 11:50:51','2025-06-17 11:50:51'),(283,390,8,'Noel Hurst',1,10000.00,'belum dibayar','2025-06-17 12:19:43','2025-06-17 12:19:43'),(284,391,5,'Nasi Goreng',1,14000.00,'belum dibayar','2025-06-17 12:25:23','2025-06-17 12:25:23');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-18 11:52:07
