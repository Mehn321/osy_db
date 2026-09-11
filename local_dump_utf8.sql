-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: host.docker.internal    Database: municipal_kk_profiling
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `ai_usage_log`
--

DROP TABLE IF EXISTS `ai_usage_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_usage_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generateContent',
  `model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_tokens` int DEFAULT '0',
  `response_tokens` int DEFAULT '0',
  `total_tokens` int DEFAULT '0',
  `http_status` int DEFAULT '200',
  `success` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_usage_date` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_usage_log`
--

LOCK TABLES `ai_usage_log` WRITE;
/*!40000 ALTER TABLE `ai_usage_log` DISABLE KEYS */;
INSERT INTO `ai_usage_log` VALUES (1,'generateContent','gemini-flash-latest',124,2,480,200,1,'2026-05-05 21:23:35'),(2,'generateContent','gemini-flash-latest',124,2,466,200,1,'2026-05-05 21:23:50'),(3,'generateContent','gemini-flash-latest',111,2,466,200,1,'2026-05-05 21:23:53'),(4,'generateContent','gemini-flash-latest',111,2,345,200,1,'2026-05-05 21:23:54'),(5,'generateContent','gemini-flash-latest',119,2,385,200,1,'2026-05-05 21:23:59'),(6,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:24:01'),(7,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:24:03'),(8,'generateContent','gemini-flash-latest',119,2,466,200,1,'2026-05-05 21:24:03'),(9,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:24:05'),(10,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:24:05'),(11,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-05 21:24:07'),(12,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:24:07'),(13,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:24:09'),(14,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-05 21:24:10'),(15,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:24:11'),(16,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:24:12'),(17,'generateContent','gemini-flash-latest',118,0,118,429,0,'2026-05-05 21:24:15'),(18,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:24:15'),(19,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:24:16'),(20,'generateContent','gemini-flash-latest',127,0,127,429,0,'2026-05-05 21:24:16'),(21,'generateContent','gemini-flash-latest',118,0,118,429,0,'2026-05-05 21:24:18'),(22,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:24:18'),(23,'generateContent','gemini-flash-latest',120,1,253,200,1,'2026-05-05 21:24:22'),(24,'generateContent','gemini-flash-latest',112,2,391,200,1,'2026-05-05 21:24:23'),(25,'generateContent','gemini-flash-latest',113,2,280,200,1,'2026-05-05 21:24:27'),(26,'generateContent','gemini-flash-latest',119,2,439,200,1,'2026-05-05 21:24:41'),(27,'generateContent','gemini-flash-latest',124,2,424,200,1,'2026-05-05 21:24:52'),(28,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:24:55'),(29,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:24:57'),(30,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:24:59'),(31,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:25:01'),(32,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:25:03'),(33,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:25:05'),(34,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:25:09'),(35,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:25:11'),(36,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:25:12'),(37,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:25:14'),(38,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:25:16'),(39,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:25:18'),(40,'generateContent','gemini-flash-latest',118,2,537,200,1,'2026-05-05 21:25:24'),(41,'generateContent','gemini-flash-latest',124,2,419,200,1,'2026-05-05 21:25:32'),(42,'generateContent','gemini-flash-latest',111,2,444,200,1,'2026-05-05 21:25:42'),(43,'generateContent','gemini-flash-latest',119,2,477,200,1,'2026-05-05 21:25:56'),(44,'generateContent','gemini-flash-latest',119,2,502,200,1,'2026-05-05 21:26:13'),(45,'generateContent','gemini-flash-latest',112,2,249,200,1,'2026-05-05 21:26:17'),(46,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:26:19'),(47,'generateContent','gemini-flash-latest',118,2,519,200,1,'2026-05-05 21:26:24'),(48,'generateContent','gemini-flash-latest',125,2,409,200,1,'2026-05-05 21:26:31'),(49,'generateContent','gemini-flash-latest',112,2,364,200,1,'2026-05-05 21:26:42'),(50,'generateContent','gemini-flash-latest',118,0,118,429,0,'2026-05-05 21:26:44'),(51,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:26:46'),(52,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:26:48'),(53,'generateContent','gemini-flash-latest',117,0,117,429,0,'2026-05-05 21:26:50'),(54,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:26:52'),(55,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:26:55'),(56,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:26:57'),(57,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:26:59'),(58,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:27:01'),(59,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:27:03'),(60,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:27:05'),(61,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-05 21:27:07'),(62,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:27:11'),(63,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:27:13'),(64,'generateContent','gemini-flash-latest',118,0,118,429,0,'2026-05-05 21:27:14'),(65,'generateContent','gemini-flash-latest',127,0,127,429,0,'2026-05-05 21:27:16'),(66,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:27:18'),(67,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:27:20'),(68,'generateContent','gemini-flash-latest',124,0,124,429,0,'2026-05-05 21:27:22'),(69,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:27:26'),(70,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:27:28'),(71,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:27:30'),(72,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:27:31'),(73,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:27:33'),(74,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:27:35'),(75,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:27:37'),(76,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:27:41'),(77,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:27:43'),(78,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:27:45'),(79,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:27:46'),(80,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:27:48'),(81,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:27:50'),(82,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:27:52'),(83,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:27:56'),(84,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:27:58'),(85,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:27:59'),(86,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:28:01'),(87,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:28:03'),(88,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:28:05'),(89,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:28:07'),(90,'generateContent','gemini-flash-latest',127,0,127,429,0,'2026-05-05 21:28:11'),(91,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:28:13'),(92,'generateContent','gemini-flash-latest',117,0,117,429,0,'2026-05-05 21:28:14'),(93,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:28:16'),(94,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:28:18'),(95,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:28:20'),(96,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-05 21:28:22'),(97,'generateContent','gemini-flash-latest',112,2,382,200,1,'2026-05-05 21:28:38'),(98,'generateContent','gemini-flash-latest',124,0,124,429,0,'2026-05-05 21:28:40'),(99,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:28:44'),(100,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:28:46'),(101,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:28:47'),(102,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:28:49'),(103,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:28:51'),(104,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:28:52'),(105,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:28:54'),(106,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:28:58'),(107,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:28:59'),(108,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:29:01'),(109,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:29:03'),(110,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:29:05'),(111,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:29:06'),(112,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:29:08'),(113,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:29:12'),(114,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:29:14'),(115,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:29:15'),(116,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:29:17'),(117,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:29:19'),(118,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:29:20'),(119,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:29:22'),(120,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:29:26'),(121,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:29:28'),(122,'generateContent','gemini-flash-latest',118,0,118,429,0,'2026-05-05 21:29:29'),(123,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:29:31'),(124,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:29:33'),(125,'generateContent','gemini-flash-latest',117,0,117,429,0,'2026-05-05 21:29:35'),(126,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:29:36'),(127,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:29:40'),(128,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:29:42'),(129,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:29:43'),(130,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:29:45'),(131,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:29:46'),(132,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:29:48'),(133,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-05 21:29:50'),(134,'generateContent','gemini-flash-latest',128,0,128,429,0,'2026-05-05 21:29:54'),(135,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:29:55'),(136,'generateContent','gemini-flash-latest',118,0,118,429,0,'2026-05-05 21:29:57'),(137,'generateContent','gemini-flash-latest',127,0,127,429,0,'2026-05-05 21:29:59'),(138,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:30:01'),(139,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:30:02'),(140,'generateContent','gemini-flash-latest',124,0,124,429,0,'2026-05-05 21:30:04'),(141,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:30:08'),(142,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:09'),(143,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:30:11'),(144,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:30:13'),(145,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:30:15'),(146,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:17'),(147,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:30:19'),(148,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:30:23'),(149,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:24'),(150,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:30:26'),(151,'generateContent','gemini-flash-latest',125,0,125,429,0,'2026-05-05 21:30:28'),(152,'generateContent','gemini-flash-latest',113,0,113,429,0,'2026-05-05 21:30:29'),(153,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:32'),(154,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:30:33'),(155,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:30:37'),(156,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:39'),(157,'generateContent','gemini-flash-latest',116,0,116,429,0,'2026-05-05 21:30:41'),(158,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:30:43'),(159,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:44'),(160,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:46'),(161,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-05 21:30:48'),(162,'generateContent','gemini-flash-latest',127,0,127,429,0,'2026-05-05 21:30:51'),(163,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:30:53'),(164,'generateContent','gemini-flash-latest',117,0,117,429,0,'2026-05-05 21:30:55'),(165,'generateContent','gemini-flash-latest',126,0,126,429,0,'2026-05-05 21:30:57'),(166,'generateContent','gemini-flash-latest',114,0,114,429,0,'2026-05-05 21:30:58'),(167,'generateContent','gemini-flash-latest',115,0,115,429,0,'2026-05-05 21:31:00'),(168,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-05 21:31:02'),(169,'generateContent','gemini-flash-latest',147,2,422,200,1,'2026-05-06 08:55:22'),(170,'generateContent','gemini-flash-latest',144,0,144,429,0,'2026-05-06 08:55:24'),(171,'generateContent','gemini-flash-latest',146,0,146,429,0,'2026-05-06 08:55:26'),(172,'generateContent','gemini-flash-latest',156,0,156,429,0,'2026-05-06 08:55:28'),(173,'generateContent','gemini-flash-latest',144,0,144,429,0,'2026-05-06 08:55:29'),(174,'generateContent','gemini-flash-latest',144,0,144,429,0,'2026-05-06 08:55:31'),(175,'generateContent','gemini-flash-latest',152,0,152,429,0,'2026-05-06 08:55:33'),(176,'generateContent','gemini-flash-latest',129,2,396,200,1,'2026-05-06 09:57:51'),(177,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-06 09:57:52'),(178,'generateContent','gemini-flash-latest',124,0,124,429,0,'2026-05-06 09:57:54'),(179,'generateContent','gemini-flash-latest',134,0,134,429,0,'2026-05-06 09:57:56'),(180,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-06 09:57:58'),(181,'generateContent','gemini-flash-latest',122,0,122,429,0,'2026-05-06 09:58:00'),(182,'generateContent','gemini-flash-latest',130,0,130,429,0,'2026-05-06 09:58:01'),(183,'generateContent','gemini-flash-latest',143,83,674,200,1,'2026-05-06 14:16:38'),(184,'generateContent','gemini-flash-latest',123,2,713,200,1,'2026-05-11 10:58:26'),(185,'generateContent','gemini-flash-latest',110,2,325,200,1,'2026-05-11 10:58:31'),(186,'generateContent','gemini-flash-latest',118,2,418,200,1,'2026-05-11 10:58:36'),(187,'generateContent','gemini-flash-latest',118,2,326,200,1,'2026-05-11 10:58:40'),(188,'generateContent','gemini-flash-latest',111,2,334,200,1,'2026-05-11 10:58:44'),(189,'generateContent','gemini-flash-latest',110,2,383,200,1,'2026-05-11 10:58:48'),(190,'generateContent','gemini-flash-latest',123,0,123,429,0,'2026-05-11 10:58:51');
/*!40000 ALTER TABLE `ai_usage_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `actor_id` int DEFAULT NULL,
  `actor_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` int DEFAULT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_actor` (`actor_id`),
  KEY `idx_audit_target` (`target_type`,`target_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,17,'youth','Youth self-registered with full profile for approval','OSYProfile',15,'{\"barangay\":\"Baga\",\"id_type\":\"\"}','2026-05-11 10:58:20'),(2,1,'lydo','Approved provider account','User',18,'{\"status\":\"Active\",\"remark\":\"\"}','2026-05-13 00:13:50'),(3,1,'lydo','Approved provider account','User',19,'{\"status\":\"Active\",\"remark\":\"\"}','2026-05-13 00:15:06'),(4,1,'lydo','Approved provider account','User',34,'{\"status\":\"Active\",\"remark\":\"\"}','2026-05-14 22:50:48'),(5,1,'lydo','Approved provider account','User',35,'{\"status\":\"Active\",\"remark\":\"\"}','2026-05-14 22:50:50'),(6,26,'sk_chairman','Approved youth verification','OSYProfile',15,'{\"remark\":\"\"}','2026-05-30 10:22:35'),(7,1,'lydo','Approved provider account','User',36,'{\"status\":\"Active\",\"remark\":\"Approved for testing\"}','2026-07-06 02:49:15'),(8,1,'lydo','Approved provider account','User',37,'{\"status\":\"Active\",\"remark\":\"\"}','2026-08-13 03:05:56'),(9,1,'lydo','Approved provider account','User',37,'{\"status\":\"Active\",\"remark\":\"\"}','2026-08-13 03:05:56'),(10,1,'lydo','Created SK Chairman account','User',38,'{\"username\":\"rada\",\"barangay\":\"Punta\"}','2026-08-13 03:33:39'),(11,39,'youth','Youth self-registered with full profile for approval','OSYProfile',16,'{\"barangay\":\"Purok 2\",\"id_type\":\"Passport\"}','2026-08-13 03:40:18'),(12,1,'lydo','Deleted SK Chairman account','User',28,'','2026-08-19 10:11:19'),(13,1,'lydo','Created SK Chairman account','User',40,'{\"username\":\"skmagasaysay\",\"barangay\":\"Magsaysay\"}','2026-09-01 05:28:35'),(14,40,'sk_chairman','Flagged youth for action from barangay portal','OSYProfile',16,'{\"remark\":\"\"}','2026-09-01 05:34:41'),(15,41,'youth','Youth self-registered with full profile for approval','OSYProfile',17,'{\"barangay\":\"Magsaysay\",\"id_type\":\"Driver\'s License\"}','2026-09-01 05:46:32'),(16,40,'sk_chairman','Flagged youth for action from barangay portal','OSYProfile',17,'{\"remark\":\"atay bataa ni\"}','2026-09-01 05:47:00'),(17,40,'sk_chairman','Returned youth for correction','OSYProfile',17,'{\"remark\":\"\"}','2026-09-01 07:11:30'),(18,40,'sk_chairman','Approved youth verification','OSYProfile',17,'{\"remark\":\"\"}','2026-09-01 07:11:32'),(19,40,'sk_chairman','Approved youth verification','OSYProfile',16,'{\"remark\":\"\"}','2026-09-01 07:11:35'),(20,10,'youth','applied_to_opportunity','opportunity',19,'Array','2026-09-04 12:42:16'),(21,10,'youth','applied_to_opportunity','opportunity',18,'Array','2026-09-04 12:55:44'),(22,10,'youth','applied_to_opportunity','opportunity',17,'Array','2026-09-04 12:55:52'),(23,10,'youth','applied_to_opportunity','opportunity',16,'Array','2026-09-04 12:55:59'),(24,10,'youth','applied_to_opportunity','opportunity',9,'Array','2026-09-04 12:56:03'),(25,10,'youth','cancelled_application','opportunity',19,'Array','2026-09-04 12:56:10'),(26,10,'youth','cancelled_application','opportunity',18,'Array','2026-09-04 12:56:11'),(27,10,'youth','cancelled_application','opportunity',17,'Array','2026-09-04 12:56:15'),(28,10,'youth','cancelled_application','opportunity',9,'Array','2026-09-04 12:56:16'),(29,10,'youth','cancelled_application','opportunity',16,'Array','2026-09-04 12:56:50'),(30,10,'youth','applied_to_opportunity','opportunity',19,'Array','2026-09-04 12:59:50'),(31,10,'youth','cancelled_application','opportunity',19,'Array','2026-09-04 12:59:56');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_members` (
  `group_id` int NOT NULL,
  `osy_id` int NOT NULL,
  PRIMARY KEY (`group_id`,`osy_id`),
  CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `notification_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES (1,14),(1,16);
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_type` enum('admin','osy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` int NOT NULL,
  `recipient_type` enum('admin','osy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms_status` enum('none','pending','success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `email_status` enum('none','pending','success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `sms_error` text COLLATE utf8mb4_unicode_ci,
  `email_error` text COLLATE utf8mb4_unicode_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_recipient` (`recipient_type`,`recipient_id`),
  KEY `idx_messages_sender` (`sender_type`,`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_groups`
--

DROP TABLE IF EXISTS `notification_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_groups`
--

LOCK TABLES `notification_groups` WRITE;
/*!40000 ALTER TABLE `notification_groups` DISABLE KEYS */;
INSERT INTO `notification_groups` VALUES (1,'hey',1,'2026-08-19 10:02:54');
/*!40000 ALTER TABLE `notification_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_reads`
--

DROP TABLE IF EXISTS `notification_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_reads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `notification_id` int NOT NULL,
  `user_id` int NOT NULL,
  `read_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_notification_user` (`notification_id`,`user_id`),
  KEY `idx_notification` (`notification_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_reads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_reads`
--

LOCK TABLES `notification_reads` WRITE;
/*!40000 ALTER TABLE `notification_reads` DISABLE KEYS */;
INSERT INTO `notification_reads` VALUES (2,6,1,'2026-08-13 02:52:26'),(4,5,1,'2026-08-13 02:52:29'),(6,5,31,'2026-08-13 03:19:43'),(8,1,32,'2026-08-13 03:24:17'),(10,24,41,'2026-09-01 07:36:08'),(12,1,10,'2026-09-04 12:47:15'),(15,4,10,'2026-09-04 12:47:16');
/*!40000 ALTER TABLE `notification_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_templates`
--

DROP TABLE IF EXISTS `notification_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('SMS','Email','SMS/Email') COLLATE utf8mb4_unicode_ci DEFAULT 'SMS',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `notification_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_templates`
--

LOCK TABLES `notification_templates` WRITE;
/*!40000 ALTER TABLE `notification_templates` DISABLE KEYS */;
INSERT INTO `notification_templates` VALUES (1,'Training Invitation','New Training Opportunity','Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.','SMS/Email',1,'2026-05-05 21:20:51','2026-05-05 21:20:51'),(2,'Job Match Alert','Job Opportunity Found','Hello {{name}}, a new job opportunity at {{company}} matches your skills. Apply now through the Opportunity Hub!','Email',1,'2026-05-05 21:20:51','2026-05-05 21:20:51'),(3,'Registration Confirmation','Welcome to Barangay OSY','Hi {{name}}, your profile has been successfully registered. You are now part of our skills matching program.','SMS',1,'2026-05-05 21:20:51','2026-05-05 21:20:51'),(4,'Skill Upgrade Suggestion','Upskill Recommendation','Hi {{name}}, completing the {{course}} course can increase your matching score by {{percentage}}%. Check it out!','Email',1,'2026-05-05 21:20:51','2026-05-05 21:20:51');
/*!40000 ALTER TABLE `notification_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Opportunity','Match','System','Reminder') COLLATE utf8mb4_unicode_ci DEFAULT 'System',
  `recipient_type` enum('All','OSY','Specific') COLLATE utf8mb4_unicode_ci DEFAULT 'All',
  `recipient_id` int DEFAULT NULL,
  `status` enum('Sent','Read','Failed') COLLATE utf8mb4_unicode_ci DEFAULT 'Sent',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_notification_type` (`type`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'New Opportunity Posted','TESDA NCII Welding training is now available with 15 slots. Qualified candidates will be notified.','Opportunity','OSY',NULL,'Sent',1,'2026-05-05 21:20:51'),(2,'Skills Match Found','You have been matched with a job opportunity in Logistics!','Match','Specific',NULL,'Sent',2,'2026-05-05 21:20:51'),(3,'Training Started','Congratulations! You have been enrolled in the Basic Web Design course.','System','Specific',NULL,'Read',1,'2026-05-05 21:20:51'),(4,'Application Deadline Reminder','STEM University Grant applications close in 5 days. Apply now!','Reminder','OSY',NULL,'Sent',1,'2026-05-05 21:20:51'),(5,'Employment Success','Maria Elena Dela Cruz has been successfully employed through the system!','System','All',NULL,'Sent',2,'2026-05-05 21:20:51'),(6,'New Admin Registered','A new admin account has been created: admin4','System','All',NULL,'Sent',1,'2026-05-05 21:20:51'),(7,'training','Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.','System','Specific',NULL,'Sent',1,'2026-05-06 09:59:31'),(8,'haha','Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.','System','Specific',NULL,'Sent',1,'2026-05-06 10:01:05'),(9,'job aler','Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.','System','Specific',NULL,'Sent',1,'2026-05-06 10:14:15'),(10,'Provider Account Approved','Your provider registration has been active','System','Specific',18,'Sent',1,'2026-05-13 00:13:50'),(11,'Provider Account Approved','Your provider registration has been active','System','Specific',19,'Sent',1,'2026-05-13 00:15:06'),(12,'Provider Account Approved','Your provider registration has been active','System','Specific',34,'Sent',1,'2026-05-14 22:50:48'),(13,'Provider Account Approved','Your provider registration has been active','System','Specific',35,'Sent',1,'2026-05-14 22:50:50'),(14,'Profile Verification Approved','Your youth registration has been verified.','System','Specific',17,'Sent',26,'2026-05-30 10:22:35'),(15,'Provider Account Approved','Your provider registration has been active: Approved for testing','System','Specific',36,'Sent',1,'2026-07-06 02:49:15'),(16,'Provider Account Approved','Your provider registration has been active','System','Specific',37,'Sent',1,'2026-08-13 03:05:56'),(17,'Provider Account Approved','Your provider registration has been active','System','Specific',37,'Sent',1,'2026-08-13 03:05:56'),(18,'Account Created','Welcome to the Youth Profiling System! Your SK Chairman account has been created by the LYDO. Your temporary password is: 498604b118d6. Please change it on your first login.','System','Specific',38,'Sent',1,'2026-08-13 03:33:34'),(19,'basta','Hello, this is a new update.','System','Specific',39,'Sent',1,'2026-09-01 05:22:11'),(20,'basta gud','Hello, this is a new update.','System','Specific',39,'Sent',1,'2026-09-01 05:22:48'),(21,'Account Created','Welcome to the Youth Profiling System! Your SK Chairman account has been created by the LYDO. Your temporary password is: 7#s8iYyT9Z^hYZ. Please change it on your first login.','System','Specific',40,'Sent',1,'2026-09-01 05:28:31'),(22,'New Youth Registration awaiting review','A new youth member (Fredy Lusing) has self-registered in barangay Magsaysay Panaon Misamis Occidental and is awaiting verification.','System','Specific',40,'Sent',41,'2026-09-01 05:46:32'),(23,'Profile Verification Needs Action ⚠️','Your youth registration needs attention. Please review and update your profile.','System','Specific',41,'Sent',40,'2026-09-01 07:11:30'),(24,'Profile Verification Approved ✅','Your youth registration has been approved. You can now log in to the system.','System','Specific',41,'Sent',40,'2026-09-01 07:11:32'),(25,'Profile Verification Approved ✅','Your youth registration has been approved. You can now log in to the system.','System','Specific',39,'Sent',40,'2026-09-01 07:11:35'),(26,'New Application Received','Test Youth has applied to your opportunity: welder','Opportunity','Specific',31,'Sent',10,'2026-09-04 12:42:16'),(27,'New Application Received','Test Youth has applied to your opportunity: welder','Opportunity','Specific',31,'Sent',10,'2026-09-04 12:55:44'),(28,'New Application Received','Test Youth has applied to your opportunity: welder','Opportunity','Specific',31,'Sent',10,'2026-09-04 12:55:52'),(29,'New Application Received','Test Youth has applied to your opportunity: welder','Opportunity','Specific',31,'Sent',10,'2026-09-04 12:55:59'),(30,'New Application Received','Test Youth has applied to your opportunity: upskill','Opportunity','Specific',31,'Sent',10,'2026-09-04 12:56:03'),(31,'New Application Received','Test Youth has applied to your opportunity: welder','Opportunity','Specific',31,'Sent',10,'2026-09-04 12:59:50');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities`
--

DROP TABLE IF EXISTS `opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opportunities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Job Opening','Vocational Training','Scholarship') COLLATE utf8mb4_unicode_ci NOT NULL,
  `employment_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_schedule` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_req` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `training_provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modality` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compensation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `benefits` text COLLATE utf8mb4_unicode_ci,
  `certification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_min` int DEFAULT NULL,
  `age_max` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `total_slots` int DEFAULT '10',
  `deadline` date DEFAULT NULL,
  `status` enum('Open','Closed','Pending') COLLATE utf8mb4_unicode_ci DEFAULT 'Open',
  `created_by` int DEFAULT NULL,
  `provider_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_opportunity_type` (`type`),
  KEY `idx_opportunity_status` (`status`),
  CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities`
--

LOCK TABLES `opportunities` WRITE;
/*!40000 ALTER TABLE `opportunities` DISABLE KEYS */;
INSERT INTO `opportunities` VALUES (1,'TESDA NCII Cookery','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'TESDA-MisOr Hub',NULL,'Free training materials','National Certificate II (NCII)',NULL,NULL,'Comprehensive culinary arts and food safety training program',20,'2024-10-24','Closed',1,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(2,'Logistics Assistant','Job Opening',NULL,NULL,NULL,NULL,NULL,NULL,'Port Logistics Corp.','₱14,500 - ₱16,000','Full HMO',NULL,NULL,NULL,'Inventory management and logistics support',5,'2024-11-05','Closed',2,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(3,'STEM University Grant','Scholarship',NULL,NULL,NULL,NULL,NULL,NULL,'City Education Board',NULL,'Full tuition coverage',NULL,NULL,NULL,'For students with grade 85+ and indigent status',50,'2024-11-15','Closed',1,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(4,'Basic Web Design','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'Digital Arts Institute',NULL,'Certificate of Completion','Certificate of Completion',NULL,NULL,'UI/UX principles and Figma training',12,'2024-10-01','Closed',2,NULL,'2026-05-05 21:20:51','2026-05-05 21:20:51'),(5,'Welding Specialist - NC II','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'TESDA-MisOr Hub',NULL,'Tools provided','NC II Certification',NULL,NULL,'Industrial-grade welding expertise for infrastructure projects',15,'2024-11-30','Closed',1,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(6,'BPO Customer Service','Job Opening',NULL,NULL,NULL,NULL,NULL,NULL,'TechCorp Solutions','₱18,000 - ₱22,000','Medical benefits, Meal allowance',NULL,NULL,NULL,'Virtual customer support representative',10,'2024-11-20','Closed',2,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(7,'Automotive Technician','Job Opening',NULL,NULL,NULL,NULL,NULL,NULL,'AutoWorks Ltd.','₱16,000 - ₱20,000','HMO, Hazard pay',NULL,NULL,NULL,'Vehicle maintenance and repair technician',8,'2024-11-25','Closed',1,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(8,'Computer Literacy & Basic IT','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'Barangay Tech Center',NULL,'Free','Completion Certificate',NULL,NULL,'MS Office, Internet basics, Email management',25,'2024-12-15','Closed',2,NULL,'2026-05-05 21:20:51','2026-09-10 11:50:41'),(9,'upskill','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'baga, panaon',NULL,NULL,'tesda nc2',NULL,NULL,'naay allowanace 500 per day',100,'2026-06-17','Closed',31,31,'2026-06-17 23:22:16','2026-09-10 11:50:41'),(10,'welding machine','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'Digital Arts Institute','10,000','philhealth ',NULL,4,1,'ambot',5,'2026-08-11','Closed',37,37,'2026-08-13 03:09:03','2026-09-10 11:50:41'),(11,'welding machine','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'Digital Arts Institute','10,000','philhealth ',NULL,4,1,'ambot',5,'2026-08-11','Closed',37,37,'2026-08-13 03:09:03','2026-09-10 11:50:41'),(12,'WELDER ','Job Opening',NULL,NULL,NULL,NULL,NULL,NULL,'MAGSAYSAY','','13month pay with SRI ',NULL,24,60,'WALAY DETAILS ',6,'2026-08-10','Closed',37,37,'2026-08-13 03:13:16','2026-09-10 11:50:41'),(13,'WELDER ','Job Opening',NULL,NULL,NULL,NULL,NULL,NULL,'MAGSAYSAY','','13month pay with SRI ',NULL,24,60,'WALAY DETAILS ',6,'2026-08-10','Closed',37,37,'2026-08-13 03:13:17','2026-09-10 11:50:41'),(14,'welder','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'panaon',NULL,NULL,'tesda nc2',NULL,NULL,'hsashau',6,'2026-08-14','Closed',31,31,'2026-08-13 03:17:52','2026-09-10 11:50:41'),(16,'welder','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'panaon',NULL,NULL,'tesda nc2',NULL,NULL,'hsashau',6,'2026-08-14','Closed',31,31,'2026-08-13 03:17:52','2026-09-10 11:50:41'),(17,'welder','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'panaon',NULL,NULL,'tesda nc2',NULL,NULL,'hsashau',6,'2026-08-14','Closed',31,31,'2026-08-13 03:17:52','2026-09-10 11:50:41'),(18,'welder','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'panaon',NULL,NULL,'tesda nc2',NULL,NULL,'hsashau',6,'2026-08-14','Closed',31,31,'2026-08-13 03:17:52','2026-09-10 11:50:41'),(19,'welder','Vocational Training',NULL,NULL,NULL,NULL,NULL,NULL,'panaon',NULL,NULL,'tesda nc2',NULL,NULL,'hsashau',6,'2026-08-14','Closed',31,31,'2026-08-13 03:17:52','2026-09-10 11:50:41'),(20,'Pharmacy Assistant','Job Opening','Full-time','Monday to Saturday, 8AM–5PM','No experience required',NULL,NULL,NULL,'Baga','₱500/day','SSS, PhilHealth, 13th Month Pay',NULL,18,30,'We are looking for a responsible and detail-oriented Pharmacy Assistant to help our licensed pharmacist in dispensing medications, assisting customers, and maintaining inventory. No prior experience needed — we will train the right candidate.',3,'2026-10-20','Open',18,18,'2026-09-05 05:53:07','2026-09-05 05:53:07'),(21,'Cashier / Sales Clerk','Job Opening','Full-time','Monday to Saturday, 8AM–5PM','No experience required',NULL,NULL,NULL,'Poblacion','₱480/day','SSS, PhilHealth, Meal Allowance',NULL,18,28,'Nhem Pharmacy is hiring a friendly and trustworthy Cashier / Sales Clerk. Duties include handling cash transactions, assisting customers with over-the-counter products, and maintaining a clean and organized store. Fresh graduates are welcome.',2,'2026-10-05','Open',18,18,'2026-09-05 05:53:07','2026-09-05 05:53:07'),(22,'Inventory Clerk','Job Opening','Part-time','Monday, Wednesday, Friday, 8AM–12PM','No experience required',NULL,NULL,NULL,'Baga','₱300/day','PhilHealth',NULL,18,32,'We need an organized and hardworking Inventory Clerk to assist in receiving deliveries, counting stocks, and encoding product data. Ideal for students or those looking for part-time work. Computer literacy is a plus.',1,'2026-11-04','Open',18,18,'2026-09-05 05:53:07','2026-09-05 05:53:07'),(23,'Delivery Rider / Messenger','Job Opening','Full-time','Monday to Saturday, 8AM–5PM','6 months experience preferred',NULL,NULL,NULL,'Any','₱520/day + delivery incentives','SSS, PhilHealth, Gasoline Allowance',NULL,18,35,'Nhem Pharmacy is looking for a reliable Delivery Rider to deliver medicines and health products to our customers. Applicant must have a valid driver\'s license (Restriction Code 1) and own motorcycle. Knowledge of local barangays is an advantage.',2,'2026-10-05','Open',18,18,'2026-09-05 05:53:07','2026-09-05 05:53:07'),(24,'Store Helper / Utility Worker','Job Opening','Full-time','Monday to Saturday, 7AM–4PM','No experience required',NULL,NULL,NULL,'Baga','₱450/day','SSS, PhilHealth, 13th Month Pay',NULL,17,30,'We are looking for an energetic and willing Store Helper to assist with general maintenance, stocking shelves, cleaning, and supporting daily pharmacy operations. No experience necessary — positive attitude and willingness to learn are what we value most.',2,'2026-10-20','Open',18,18,'2026-09-05 05:53:07','2026-09-05 05:53:07');
/*!40000 ALTER TABLE `opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunity_required_skills`
--

DROP TABLE IF EXISTS `opportunity_required_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opportunity_required_skills` (
  `id` int NOT NULL AUTO_INCREMENT,
  `opportunity_id` int NOT NULL,
  `skill` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Skill name or description',
  `importance_level` enum('Required','Preferred','Nice to have') COLLATE utf8mb4_unicode_ci DEFAULT 'Required',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_opportunity_skills` (`opportunity_id`),
  CONSTRAINT `opportunity_required_skills_ibfk_1` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunity_required_skills`
--

LOCK TABLES `opportunity_required_skills` WRITE;
/*!40000 ALTER TABLE `opportunity_required_skills` DISABLE KEYS */;
INSERT INTO `opportunity_required_skills` VALUES (1,10,'welding','Required','2026-08-13 03:09:03'),(2,11,'welding','Required','2026-08-13 03:09:03'),(3,12,'welding','Required','2026-08-13 03:13:17'),(4,13,'welding','Required','2026-08-13 03:13:17');
/*!40000 ALTER TABLE `opportunity_required_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunity_skills`
--

DROP TABLE IF EXISTS `opportunity_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opportunity_skills` (
  `opportunity_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `importance_level` enum('Required','Preferred','Nice to have') COLLATE utf8mb4_unicode_ci DEFAULT 'Required',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`opportunity_id`,`skill_id`),
  CONSTRAINT `opportunity_skills_ibfk_1` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunity_skills`
--

LOCK TABLES `opportunity_skills` WRITE;
/*!40000 ALTER TABLE `opportunity_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunity_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osy_matches`
--

DROP TABLE IF EXISTS `osy_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `osy_matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `osy_id` int NOT NULL,
  `opportunity_id` int NOT NULL,
  `match_score` int DEFAULT '0',
  `status` enum('Pending','Accepted','Rejected','In Progress','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `ai_insight` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match` (`osy_id`,`opportunity_id`),
  KEY `idx_matches_osy` (`osy_id`),
  KEY `idx_matches_opportunity` (`opportunity_id`),
  KEY `idx_matches_score` (`match_score`),
  KEY `idx_matches_status` (`status`),
  CONSTRAINT `osy_matches_ibfk_1` FOREIGN KEY (`osy_id`) REFERENCES `osy_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `osy_matches_ibfk_2` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osy_matches`
--

LOCK TABLES `osy_matches` WRITE;
/*!40000 ALTER TABLE `osy_matches` DISABLE KEYS */;
INSERT INTO `osy_matches` VALUES (1,1,2,55,'Accepted',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:23:54'),(2,2,1,45,'Accepted',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:30:23'),(3,3,8,55,'Accepted',NULL,NULL,'2026-05-05 21:20:51','2026-05-06 13:37:14'),(4,4,5,35,'Accepted',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:30:57'),(5,5,5,45,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:28:49'),(6,6,5,45,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:30:13'),(7,7,5,45,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:29:03'),(8,8,6,35,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:29:19'),(9,9,8,45,'Accepted',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:29:36'),(10,10,7,35,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:29:48'),(11,11,1,45,'Accepted',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:30:37'),(12,12,5,35,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:29:59'),(13,1,7,45,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:24:09'),(14,2,6,35,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:30:30'),(15,3,1,35,'Pending',NULL,NULL,'2026-05-05 21:20:51','2026-05-05 21:24:15'),(16,1,1,30,'Pending',NULL,NULL,'2026-05-05 21:23:35','2026-05-05 21:23:50'),(17,1,3,35,'Pending',NULL,NULL,'2026-05-05 21:23:59','2026-05-05 21:23:59'),(18,1,5,35,'Pending',NULL,NULL,'2026-05-05 21:24:01','2026-05-05 21:24:05'),(19,1,6,35,'Pending',NULL,NULL,'2026-05-05 21:24:03','2026-05-05 21:24:07'),(21,1,8,35,'Pending',NULL,NULL,'2026-05-05 21:24:07','2026-05-05 21:24:10'),(22,3,2,35,'Pending',NULL,NULL,'2026-05-05 21:24:12','2026-05-05 21:24:16'),(23,3,3,35,'Pending',NULL,NULL,'2026-05-05 21:24:15','2026-05-05 21:24:18'),(24,3,5,5,'Pending',NULL,NULL,'2026-05-05 21:24:16','2026-05-05 21:24:22'),(25,3,6,80,'Pending',NULL,NULL,'2026-05-05 21:24:18','2026-05-05 21:24:27'),(26,3,7,25,'Pending',NULL,NULL,'2026-05-05 21:24:23','2026-05-05 21:28:38'),(27,5,1,35,'Pending',NULL,NULL,'2026-05-05 21:24:52','2026-05-05 21:28:44'),(28,5,2,35,'Pending',NULL,NULL,'2026-05-05 21:24:55','2026-05-05 21:28:46'),(29,5,3,35,'Pending',NULL,NULL,'2026-05-05 21:24:57','2026-05-05 21:28:47'),(30,5,6,35,'Pending',NULL,NULL,'2026-05-05 21:25:01','2026-05-05 21:28:51'),(31,5,7,35,'Pending',NULL,NULL,'2026-05-05 21:25:03','2026-05-05 21:28:52'),(32,5,8,35,'Pending',NULL,NULL,'2026-05-05 21:25:05','2026-05-05 21:28:54'),(33,7,1,35,'Pending',NULL,NULL,'2026-05-05 21:25:09','2026-05-05 21:28:58'),(34,7,2,35,'Pending',NULL,NULL,'2026-05-05 21:25:11','2026-05-05 21:28:59'),(35,7,3,35,'Pending',NULL,NULL,'2026-05-05 21:25:12','2026-05-05 21:29:01'),(36,7,6,35,'Pending',NULL,NULL,'2026-05-05 21:25:16','2026-05-05 21:29:05'),(37,7,7,35,'Pending',NULL,NULL,'2026-05-05 21:25:18','2026-05-05 21:29:06'),(38,7,8,35,'Pending',NULL,NULL,'2026-05-05 21:25:24','2026-05-05 21:29:08'),(39,8,1,35,'Pending',NULL,NULL,'2026-05-05 21:25:32','2026-05-05 21:29:12'),(40,8,2,35,'Pending',NULL,NULL,'2026-05-05 21:25:42','2026-05-05 21:29:14'),(41,8,3,35,'Pending',NULL,NULL,'2026-05-05 21:25:56','2026-05-05 21:29:15'),(42,8,5,45,'Pending',NULL,NULL,'2026-05-05 21:26:13','2026-05-05 21:29:17'),(43,8,7,35,'Pending',NULL,NULL,'2026-05-05 21:26:19','2026-05-05 21:29:20'),(44,8,8,35,'Pending',NULL,NULL,'2026-05-05 21:26:24','2026-05-05 21:29:22'),(45,9,1,35,'Pending',NULL,NULL,'2026-05-05 21:26:31','2026-05-05 21:29:26'),(46,9,2,35,'Pending',NULL,NULL,'2026-05-05 21:26:42','2026-05-05 21:29:28'),(47,9,3,35,'Pending',NULL,NULL,'2026-05-05 21:26:44','2026-05-05 21:29:29'),(48,9,5,35,'Pending',NULL,NULL,'2026-05-05 21:26:46','2026-05-05 21:29:31'),(49,9,6,35,'Pending',NULL,NULL,'2026-05-05 21:26:48','2026-05-05 21:29:33'),(50,9,7,55,'Pending',NULL,NULL,'2026-05-05 21:26:50','2026-05-05 21:29:35'),(51,10,1,35,'Pending',NULL,NULL,'2026-05-05 21:26:55','2026-05-05 21:29:40'),(52,10,2,35,'Pending',NULL,NULL,'2026-05-05 21:26:57','2026-05-05 21:29:42'),(53,10,3,35,'Pending',NULL,NULL,'2026-05-05 21:26:59','2026-05-05 21:29:43'),(54,10,5,35,'Pending',NULL,NULL,'2026-05-05 21:27:01','2026-05-05 21:29:45'),(55,10,6,35,'Pending',NULL,NULL,'2026-05-05 21:27:03','2026-05-05 21:29:46'),(56,10,8,35,'Pending',NULL,NULL,'2026-05-05 21:27:07','2026-05-05 21:29:50'),(57,12,1,35,'Pending',NULL,NULL,'2026-05-05 21:27:11','2026-05-05 21:29:54'),(58,12,2,35,'Pending',NULL,NULL,'2026-05-05 21:27:13','2026-05-05 21:29:55'),(59,12,3,35,'Pending',NULL,NULL,'2026-05-05 21:27:14','2026-05-05 21:29:57'),(60,12,6,35,'Pending',NULL,NULL,'2026-05-05 21:27:18','2026-05-05 21:30:01'),(61,12,7,35,'Pending',NULL,NULL,'2026-05-05 21:27:20','2026-05-05 21:30:02'),(62,12,8,35,'Pending',NULL,NULL,'2026-05-05 21:27:22','2026-05-05 21:30:04'),(63,6,1,35,'Pending',NULL,NULL,'2026-05-05 21:27:26','2026-05-05 21:30:08'),(64,6,2,35,'Pending',NULL,NULL,'2026-05-05 21:27:28','2026-05-05 21:30:09'),(65,6,3,35,'Pending',NULL,NULL,'2026-05-05 21:27:30','2026-05-05 21:30:11'),(66,6,6,35,'Pending',NULL,NULL,'2026-05-05 21:27:33','2026-05-05 21:30:15'),(67,6,7,35,'Pending',NULL,NULL,'2026-05-05 21:27:35','2026-05-05 21:30:17'),(68,6,8,35,'Pending',NULL,NULL,'2026-05-05 21:27:37','2026-05-05 21:30:19'),(69,2,2,35,'Pending',NULL,NULL,'2026-05-05 21:27:43','2026-05-05 21:30:24'),(70,2,3,35,'Pending',NULL,NULL,'2026-05-05 21:27:45','2026-05-05 21:30:26'),(71,2,5,35,'Pending',NULL,NULL,'2026-05-05 21:27:46','2026-05-05 21:30:28'),(72,2,7,35,'Pending',NULL,NULL,'2026-05-05 21:27:50','2026-05-05 21:30:32'),(73,2,8,35,'Pending',NULL,NULL,'2026-05-05 21:27:52','2026-05-05 21:30:33'),(74,11,2,35,'Pending',NULL,NULL,'2026-05-05 21:27:58','2026-05-05 21:30:39'),(75,11,3,35,'Pending',NULL,NULL,'2026-05-05 21:27:59','2026-05-05 21:30:41'),(76,11,5,35,'Pending',NULL,NULL,'2026-05-05 21:28:01','2026-05-05 21:30:43'),(77,11,6,35,'Pending',NULL,NULL,'2026-05-05 21:28:03','2026-05-05 21:30:44'),(78,11,7,35,'Pending',NULL,NULL,'2026-05-05 21:28:05','2026-05-05 21:30:46'),(79,11,8,35,'Pending',NULL,NULL,'2026-05-05 21:28:07','2026-05-05 21:30:48'),(80,4,1,35,'Pending',NULL,NULL,'2026-05-05 21:28:11','2026-05-05 21:30:51'),(81,4,2,35,'Pending',NULL,NULL,'2026-05-05 21:28:13','2026-05-05 21:30:53'),(82,4,3,35,'Pending',NULL,NULL,'2026-05-05 21:28:14','2026-05-05 21:30:55'),(83,4,6,35,'Pending',NULL,NULL,'2026-05-05 21:28:18','2026-05-05 21:30:58'),(84,4,7,55,'Pending',NULL,NULL,'2026-05-05 21:28:20','2026-05-05 21:31:00'),(85,4,8,55,'Pending',NULL,NULL,'2026-05-05 21:28:22','2026-05-05 21:31:02'),(93,14,1,92,'Accepted',NULL,'Your existing skills in cooking align perfectly with this program, allowing you to transform your practical experience into a professional culinary qualification. As a college undergraduate, you possess the strong educational foundation necessary to excel in the technical training and food safety standards required for this certification.\n\n**Specific Benefit:** This NCII certification provides you with a nationally recognized credential that significantly increases your employability for high-paying roles in professional kitchens and hotels.','2026-05-06 09:57:51','2026-05-06 14:16:45'),(94,14,2,55,'Pending',NULL,NULL,'2026-05-06 09:57:52','2026-05-06 09:57:52'),(95,14,3,35,'Pending',NULL,NULL,'2026-05-06 09:57:54','2026-05-06 09:57:54'),(96,14,5,35,'Pending',NULL,NULL,'2026-05-06 09:57:56','2026-05-06 09:57:56'),(97,14,6,35,'Pending',NULL,NULL,'2026-05-06 09:57:58','2026-05-06 09:57:58'),(98,14,7,55,'Pending',NULL,NULL,'2026-05-06 09:58:00','2026-05-06 09:58:00'),(99,14,8,35,'Pending',NULL,NULL,'2026-05-06 09:58:01','2026-05-06 09:58:01'),(100,15,1,35,'Pending',NULL,NULL,'2026-05-11 10:58:26','2026-05-11 10:58:26'),(101,15,2,60,'Pending',NULL,NULL,'2026-05-11 10:58:31','2026-05-11 10:58:31'),(102,15,3,45,'Pending',NULL,NULL,'2026-05-11 10:58:36','2026-05-11 10:58:36'),(103,15,5,10,'Pending',NULL,NULL,'2026-05-11 10:58:40','2026-05-11 10:58:40'),(104,15,6,65,'Pending',NULL,NULL,'2026-05-11 10:58:44','2026-05-11 10:58:44'),(105,15,7,35,'Pending',NULL,NULL,'2026-05-11 10:58:48','2026-05-11 10:58:48'),(106,15,8,45,'Pending',NULL,NULL,'2026-05-11 10:58:51','2026-05-11 10:58:51'),(107,15,9,60,'Pending',NULL,NULL,'2026-06-17 23:22:16','2026-06-17 23:22:16'),(108,15,10,45,'Pending',NULL,NULL,'2026-08-13 03:09:03','2026-08-13 03:09:03'),(109,15,11,45,'Pending',NULL,NULL,'2026-08-13 03:09:03','2026-08-13 03:09:03'),(110,15,12,45,'Pending',NULL,NULL,'2026-08-13 03:13:17','2026-08-13 03:13:17'),(111,15,13,45,'Pending',NULL,NULL,'2026-08-13 03:13:17','2026-08-13 03:13:17'),(112,15,14,45,'Pending',NULL,NULL,'2026-08-13 03:17:52','2026-08-13 03:17:52'),(114,15,16,45,'Pending',NULL,NULL,'2026-08-13 03:17:52','2026-08-13 03:17:52'),(115,15,17,45,'Pending',NULL,NULL,'2026-08-13 03:17:52','2026-08-13 03:17:52'),(116,15,18,45,'Pending',NULL,NULL,'2026-08-13 03:17:52','2026-08-13 03:17:52'),(117,15,19,45,'Pending',NULL,NULL,'2026-08-13 03:17:52','2026-08-13 03:17:52');
/*!40000 ALTER TABLE `osy_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osy_profile_contact_archive`
--

DROP TABLE IF EXISTS `osy_profile_contact_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `osy_profile_contact_archive` (
  `profile_id` int NOT NULL,
  `created_by` int DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archived_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osy_profile_contact_archive`
--

LOCK TABLES `osy_profile_contact_archive` WRITE;
/*!40000 ALTER TABLE `osy_profile_contact_archive` DISABLE KEYS */;
INSERT INTO `osy_profile_contact_archive` VALUES (1,1,'rsantos.osy@email.com','09171234567','2026-09-10 12:22:29'),(2,1,'m.delacruz@email.com','09175678901','2026-09-10 12:22:29'),(3,2,'garcia.rob@email.com','09179876543','2026-09-10 12:22:29'),(4,1,'p.lozano@email.com','09178765432','2026-09-10 12:22:29'),(5,2,'jdelacruz@email.com','09177654321','2026-09-10 12:22:29'),(6,2,'arivera@email.com','09176543210','2026-09-10 12:22:29'),(7,1,'msantos@email.com','09175432109','2026-09-10 12:22:29'),(8,2,'rbautista@email.com','09174321098','2026-09-10 12:22:29'),(9,1,'sreyes@email.com','09173210987','2026-09-10 12:22:29'),(10,2,'cmendoza@email.com','09172109876','2026-09-10 12:22:29'),(11,1,'rfernandez@email.com','09171098765','2026-09-10 12:22:29'),(12,2,'mtorres@email.com','09170987654','2026-09-10 12:22:29'),(14,1,'jovilynmiers@gmail.com','9466898063','2026-09-10 12:22:29'),(15,17,'test8@example.com','09123456789','2026-09-10 12:22:29'),(16,39,'lusing@gmail.com','09674185880','2026-09-10 12:22:29'),(17,41,'lusingfredy09@gmail.com','09615264572','2026-09-10 12:22:29'),(18,NULL,'dummyseeker22+testyouth10@gmail.com','09123456789','2026-09-10 12:22:29'),(19,10,'dummyseeker22+testyouth10@gmail.com','09123456789','2026-09-10 12:22:29');
/*!40000 ALTER TABLE `osy_profile_contact_archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osy_profiles`
--

DROP TABLE IF EXISTS `osy_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `osy_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `profile_type` enum('OSY','Regular') COLLATE utf8mb4_unicode_ci DEFAULT 'Regular',
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Solo Parent') COLLATE utf8mb4_unicode_ci DEFAULT 'Single',
  `education_level` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipality` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purok` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay_id` int DEFAULT NULL,
  `primary_skill` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills` text COLLATE utf8mb4_unicode_ci,
  `interests` text COLLATE utf8mb4_unicode_ci,
  `govt_id_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `govt_id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `govt_id_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason_for_not_in_school` text COLLATE utf8mb4_unicode_ci,
  `engagement_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','In Training','Employed','Inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `date_of_birth` date DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_status` enum('Drafting','Submitted','Approved') COLLATE utf8mb4_unicode_ci DEFAULT 'Drafting',
  `verification_status` enum('Drafting','Pending','Verified','Rejected','Action Required','Declined') COLLATE utf8mb4_unicode_ci DEFAULT 'Drafting',
  `verification_remark` text COLLATE utf8mb4_unicode_ci,
  `consent_accepted` tinyint(1) DEFAULT '0',
  `identity_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_osy_status` (`status`),
  KEY `idx_osy_skill` (`primary_skill`),
  KEY `idx_osy_barangay` (`barangay`),
  CONSTRAINT `osy_profiles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osy_profiles`
--

LOCK TABLES `osy_profiles` WRITE;
/*!40000 ALTER TABLE `osy_profiles` DISABLE KEYS */;
INSERT INTO `osy_profiles` VALUES (1,'OSY','Ricardo',NULL,'Santos',NULL,21,'Male','Single','High School Graduate','Barangay 1','Misamis Occidental','Panaon',NULL,NULL,NULL,'Automotive',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2003-05-15',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(2,'OSY','Maria Elena','','Dela Cruz',NULL,19,'Female','Single','Elementary Graduate','Map-an','Misamis Occidental','Panaon',NULL,NULL,NULL,'Culinary','','','','',NULL,NULL,'',NULL,'Employed','2005-08-22',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(3,'OSY','Roberto','','Garcia',NULL,17,'Male','Single','High School Undergraduate','Sumasap','Misamis Occidental','Panaon',NULL,NULL,NULL,'IT Support','','','','',NULL,NULL,'',NULL,'Active','2006-11-30',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,2,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(4,'OSY','Patricia','','Lozano',NULL,22,'Female','Single','High School Graduate','Villalin','Misamis Occidental','Panaon',NULL,NULL,NULL,'Hospitality','','','','',NULL,NULL,'',NULL,'Inactive','2002-03-18',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(5,'OSY','Juan',NULL,'Dela Cruz',NULL,20,'Male','Single','High School Graduate','Barangay 4','Misamis Occidental','Panaon',NULL,NULL,NULL,'Welding',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2004-01-10',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,2,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(6,'OSY','Ana',NULL,'Rivera',NULL,18,'Female','Single','High School Graduate','Barangay 2','Misamis Occidental','Panaon',NULL,NULL,NULL,'Welding',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'In Training','2006-07-25',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,2,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(7,'OSY','Maria',NULL,'Santos',NULL,19,'Female','Single','High School Graduate','Barangay 1','Misamis Occidental','Panaon',NULL,NULL,NULL,'Welding',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2005-09-12',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(8,'OSY','Ricardo',NULL,'Bautista',NULL,21,'Male','Single','High School Graduate','Barangay 7','Misamis Occidental','Panaon',NULL,NULL,NULL,'Welding',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2003-04-08',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,2,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(9,'OSY','Sofia',NULL,'Reyes',NULL,20,'Female','Single','High School Graduate','Barangay 3','Misamis Occidental','Panaon',NULL,NULL,NULL,'Computer Literacy',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2004-06-20',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(10,'OSY','Carlos',NULL,'Mendoza',NULL,19,'Male','Single','High School Graduate','Barangay 5','Misamis Occidental','Panaon',NULL,NULL,NULL,'Carpentry',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2005-02-14',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,2,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(11,'OSY','Rosa',NULL,'Fernandez',NULL,22,'Female','Single','High School Graduate','Barangay 2','Misamis Occidental','Panaon',NULL,NULL,NULL,'Culinary',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Employed','2002-11-28',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(12,'OSY','Miguel',NULL,'Torres',NULL,20,'Male','Single','High School Undergraduate','Barangay 4','Misamis Occidental','Panaon',NULL,NULL,NULL,'Electrical',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active','2004-12-05',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,2,'2026-05-05 21:20:51','2026-09-08 16:40:42'),(14,'Regular','Jovilyn Mie ','','Rada',NULL,21,'','Single','College Undergraduate','Baga','Misamis Occidental','Panaon',NULL,NULL,NULL,'Driving, Cooking','Driving, Cooking','Make-up','Driver\'s License',NULL,NULL,'','',NULL,'Active','2004-11-25',NULL,'Submitted','Drafting',NULL,0,NULL,NULL,NULL,1,'2026-05-06 09:57:47','2026-09-08 16:40:42'),(15,'OSY','Test','M','Youth',NULL,26,'Male','Single','High School','Baga','Misamis Occidental','Panaon',NULL,NULL,NULL,'','Test certification','','','',NULL,'','',NULL,'Active','2000-01-01','/uploads/profiles/profile_1778468300_6a0145cc7129e.jpg','Approved','Verified','',1,NULL,26,'2026-05-30 10:22:35',17,'2026-05-11 10:58:20','2026-09-08 16:40:42'),(16,'OSY','fredy','P.','Lusing',NULL,35,'Male','Solo Parent','College Undergraduate','Magsaysay','Misamis Occidental','Panaon',NULL,NULL,NULL,'welder',NULL,NULL,NULL,NULL,'/uploads/govt_ids/govt_id_1786592418_6a7d3ca2835bc.jpg','','Self-Employed',NULL,'Employed','1999-02-01','/uploads/profiles/profile_1786592418_6a7d3ca272d10.jpg','Approved','Verified','',1,'/uploads/certifications/certification_1786592418_6a7d3ca28fd96.jpg',40,'2026-09-01 07:11:35',39,'2026-08-13 03:40:18','2026-09-08 16:40:42'),(17,'OSY','Fredy','P.','Lusing',NULL,27,'Male','Single','Elementary Undergraduate','Magsaysay','Misamis Occidental','Panaon',NULL,NULL,NULL,'IT Support, cooking',NULL,NULL,NULL,NULL,'/uploads/govt_ids/govt_id_1788241592_6a9666b886b50.jpg','','Self-Employed',NULL,'Inactive','1999-02-01',NULL,'Approved','Verified','',1,NULL,40,'2026-09-01 07:11:32',41,'2026-09-01 05:46:32','2026-09-08 16:40:42'),(18,'Regular','Test','Demo','Youth',NULL,22,'Male','Single','High School Graduate','Baga','Misamis Occidental','Panaon',NULL,NULL,NULL,'Communication','Problem Solving, Leadership','Technology, Business','National ID','12-3456789-0',NULL,'Seeking Employment','Seeking Employment',NULL,'Active','2004-01-15',NULL,'Drafting','Drafting',NULL,0,NULL,NULL,NULL,NULL,'2026-09-02 01:04:02','2026-09-10 12:02:49'),(19,'OSY','Test','Demo','Youth','',22,'Male','Single','High School Graduate','Baga','Misamis Occidental','Panaon','baga, panaon, misamis occidental','baga, panaon, misamis occidental',NULL,'Communication','Problem Solving, Leadership','Technology, Business','National ID','12-3456789-0',NULL,'Seeking Employment','Seeking Employment','','Active','2004-01-15',NULL,'Approved','Verified','Test profile created',1,NULL,NULL,'2026-09-02 01:06:26',10,'2026-09-02 01:06:26','2026-09-10 12:26:41');
/*!40000 ALTER TABLE `osy_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_references`
--

DROP TABLE IF EXISTS `system_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_references` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_references`
--

LOCK TABLES `system_references` WRITE;
/*!40000 ALTER TABLE `system_references` DISABLE KEYS */;
INSERT INTO `system_references` VALUES (1,'govt_id_type','SSS',1,'2026-05-06 08:44:39'),(2,'govt_id_type','GSIS',1,'2026-05-06 08:44:39'),(3,'govt_id_type','PhilHealth',1,'2026-05-06 08:44:39'),(4,'govt_id_type','Pag-IBIG',1,'2026-05-06 08:44:39'),(5,'govt_id_type','Passport',1,'2026-05-06 08:44:39'),(6,'govt_id_type','Driver\'s License',1,'2026-05-06 08:44:39'),(7,'govt_id_type','Postal ID',1,'2026-05-06 08:44:39'),(8,'govt_id_type','Voter\'s ID',1,'2026-05-06 08:44:39'),(9,'govt_id_type','National ID',1,'2026-05-06 08:44:39'),(10,'barangay','Baga',1,'2026-05-06 08:44:39'),(11,'barangay','Bangko',1,'2026-05-06 08:44:39'),(12,'barangay','Camanucan',1,'2026-05-06 08:44:39'),(13,'barangay','Dela Paz',1,'2026-05-06 08:44:39'),(14,'barangay','Lutao',1,'2026-05-06 08:44:39'),(15,'barangay','Magsaysay',1,'2026-05-06 08:44:39'),(16,'barangay','Map-an',1,'2026-05-06 08:44:39'),(17,'barangay','Mohon',1,'2026-05-06 08:44:39'),(18,'barangay','Poblacion',1,'2026-05-06 08:44:39'),(19,'barangay','Punta',1,'2026-05-06 08:44:39'),(20,'barangay','Salimpuno',1,'2026-05-06 08:44:39'),(21,'barangay','San Andres',1,'2026-05-06 08:44:39'),(22,'barangay','San Juan',1,'2026-05-06 08:44:39'),(23,'barangay','San Roque',1,'2026-05-06 08:44:39'),(24,'barangay','Sumasap',1,'2026-05-06 08:44:39'),(25,'barangay','Villalin',1,'2026-05-06 08:44:39'),(26,'education_level','Elementary Undergraduate',1,'2026-05-06 08:44:39'),(27,'education_level','Elementary Graduate',1,'2026-05-06 08:44:39'),(28,'education_level','High School Undergraduate',1,'2026-05-06 08:44:39'),(29,'education_level','High School Graduate',1,'2026-05-06 08:44:39'),(30,'education_level','College Undergraduate',1,'2026-05-06 08:44:39'),(31,'education_level','College Graduate',1,'2026-05-06 08:44:39'),(32,'education_level','Vocational',1,'2026-05-06 08:44:39'),(33,'education_level','No Formal Education',1,'2026-05-06 08:44:39'),(34,'reason','Financial Problem',1,'2026-05-06 08:44:39'),(35,'reason','Lack of Interest',1,'2026-05-06 08:44:39'),(36,'reason','Family Problem',1,'2026-05-06 08:44:39'),(37,'reason','Illness/Disability',1,'2026-05-06 08:44:39'),(38,'reason','Employment',1,'2026-05-06 08:44:39'),(39,'reason','Marriage/Pregnancy',1,'2026-05-06 08:44:39'),(40,'reason','Distance of School',1,'2026-05-06 08:44:39'),(41,'reason','Others',1,'2026-05-06 08:44:39');
/*!40000 ALTER TABLE `system_references` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13577 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'traccar_token','e-pBwcCzQB2rAEMGJ4tOBG:APA91bE1IydlYT8tukOUGYA5iRN1V-ojcRJVsag0R8W78I6gvOYrpFosNoYkkL1zYgDQNvwrCttCxC-X0j9UOBG1nvhPaI7g0UZHGgrevk0F1yDTSB74ZP4','2026-09-01 03:34:07'),(2,'gmail_user','aclonhemday@gmail.com','2026-05-06 10:11:59'),(3,'gmail_app_password','jsit bytp oppd jcxo','2026-05-06 10:11:59'),(2288,'traccar_api_url','http://192.168.100.41:8082','2026-08-02 12:12:14'),(3761,'traccar_mode','cloud','2026-08-02 14:14:03'),(4218,'traccar_username','nhemday','2026-08-02 13:54:00'),(4219,'traccar_password','nhemday123','2026-08-02 13:54:00');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traccar_notifications`
--

DROP TABLE IF EXISTS `traccar_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traccar_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `sender_role` varchar(50) NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `recipient_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `notification_type` varchar(50) DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recipient` (`recipient_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_read_status` (`is_read`),
  CONSTRAINT `traccar_notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `osy_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traccar_notifications`
--

LOCK TABLES `traccar_notifications` WRITE;
/*!40000 ALTER TABLE `traccar_notifications` DISABLE KEYS */;
INSERT INTO `traccar_notifications` VALUES (1,1,'lydo','Senior Administrator',16,'basta','Hello, this is a new update.','System',0,NULL,'2026-09-01 05:22:11'),(2,1,'lydo','Senior Administrator',16,'basta gud','Hello, this is a new update.','System',0,NULL,'2026-09-01 05:22:48');
/*!40000 ALTER TABLE `traccar_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_2fa_codes`
--

DROP TABLE IF EXISTS `user_2fa_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_2fa_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `channel` enum('email','phone') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `purpose` enum('login','signup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login',
  `otp_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `consumed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_2fa_user` (`user_id`),
  KEY `idx_user_2fa_active` (`user_id`,`consumed_at`,`expires_at`),
  CONSTRAINT `fk_user_2fa_codes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_2fa_codes`
--

LOCK TABLES `user_2fa_codes` WRITE;
/*!40000 ALTER TABLE `user_2fa_codes` DISABLE KEYS */;
INSERT INTO `user_2fa_codes` VALUES (1,1,'email','login','$2y$10$Efq7BGLn80ikHpafreZ0Iuff4aE7DPreA5cIXpjWW2rjat0ULQ0AC','2026-08-28 12:18:40',0,'2026-08-28 12:08:40','2026-08-28 12:17:00'),(2,1,'email','login','$2y$10$UHaygScfKwMIRpwU4QqaOueMblSzen/3fvJ2Nx.6VnyCnQHtGcNhS','2026-08-28 12:27:00',0,'2026-08-28 12:17:00','2026-08-28 12:20:38'),(3,1,'email','login','$2y$10$5MmmQUO8Us0zTXC0zJigtex7rKOmYT2lMJb63DRWlrFyTuQWUBGB6','2026-08-28 12:30:38',0,'2026-08-28 12:20:38','2026-08-28 12:22:42'),(4,1,'email','login','$2y$10$EhPbR0Mk3ml2n/1tbtKhze8BlaMRh5MAqDAMaRk7rg8VcRYl7p9qG','2026-08-28 12:32:42',0,'2026-08-28 12:22:42','2026-08-28 12:23:10'),(5,1,'email','login','$2y$10$tnY2ibB2UAoXFfih9cx1zu.oWqvqw9TrpWJhv2CwmaC13J4hoDepm','2026-08-28 13:18:35',0,'2026-08-28 13:08:35','2026-08-29 08:16:31'),(6,31,'email','login','$2y$10$.K20Oh3ZLCh6BfPAzzsnheVbfCJYvC5Y/5kzxa16x3rj.MjR2E2/2','2026-08-29 08:22:49',0,'2026-08-29 08:12:49',NULL),(7,1,'email','login','$2y$10$17xeOvqZvOFPLR4Cg12mcethXIAZavjPohWHTtiMvSmOCMZ9p.Nna','2026-08-29 08:26:31',0,'2026-08-29 08:16:31','2026-08-29 08:17:19'),(8,1,'email','login','$2y$10$riG2kks2xzTnN6JKNe0ZQ.EgJi4bMkAa1zm5nX6YDrExdsQ0HUypu','2026-08-31 14:22:50',0,'2026-08-31 14:12:50','2026-08-31 14:46:01'),(9,1,'email','login','$2y$10$O11ieqj36e4mrPwq2oBSE.QiKhlay3yhlw6bQVNtjqYF3kNVZKTum','2026-08-31 14:56:01',0,'2026-08-31 14:46:01','2026-08-31 14:46:31'),(10,1,'email','login','$2y$10$rKUIkZh6mEMgtF/VJAFc3e9iSfaESzKgcWx2l6.PcCvnvUBgPm356','2026-08-31 14:57:25',0,'2026-08-31 14:47:25','2026-08-31 14:47:47'),(11,1,'email','login','$2y$10$bQXzt5ngCYSvjSVxLgo51OA20OcvmssVsSvDrMjBlmnobkse4Jepu','2026-08-31 15:06:39',0,'2026-08-31 14:56:39','2026-08-31 14:56:55'),(12,40,'email','login','$2y$10$PVPSGxdWcKJYMSp6sn7.kebyU1bcUGzGViv5PZ1HFVUB7.ZL8Duoy','2026-09-01 05:40:29',0,'2026-09-01 05:30:29','2026-09-01 05:30:59'),(13,41,'email','login','$2y$10$hj45Aj4BnTDnNIp/jjtekutO7qBgGcXXlqRl8Xwo41KBdVKg63mNa','2026-09-01 07:21:57',0,'2026-09-01 07:11:57','2026-09-01 07:14:55'),(14,41,'email','login','$2y$10$P4lvWaVMrmIoIGy9YmWoNObeYNxqWcmq171FBIiEgIbDs9gjzpeGW','2026-09-01 07:24:55',0,'2026-09-01 07:14:55','2026-09-01 07:15:34'),(15,41,'email','login','$2y$10$1Jl5L2Vi1zHz4WCFqO9Ceuj/Mo/ir7TECvDMRKYBDwwy2x7CHqPfW','2026-09-01 07:25:34',0,'2026-09-01 07:15:34','2026-09-01 07:17:48'),(16,41,'email','login','$2y$10$lYRm4O7KB7I39zNInia6ROnYhrz3wRUj6oNpWkJJc5VqoKe.0WgDm','2026-09-01 07:27:48',0,'2026-09-01 07:17:48','2026-09-01 07:19:07'),(17,41,'email','login','$2y$10$EtyU/jz0ZQQZCNnWR5Dxh.57FFLctgzClirgiS0UmhbT9p/s2X/E6','2026-09-01 07:29:07',0,'2026-09-01 07:19:07','2026-09-01 07:20:04'),(18,41,'email','login','$2y$10$i6bSLb7ka1jmjvhdnEr1defmMbTSmwmbLnHXvV/q01wx.zybqkv32','2026-09-01 07:30:04',0,'2026-09-01 07:20:04','2026-09-01 07:22:16'),(19,41,'email','login','$2y$10$Rg/xsDcZZ5EKkel7AJOymOIDDQ8cFUq.X4hoP3QyzrBXezJlRio42','2026-09-01 07:32:16',0,'2026-09-01 07:22:16','2026-09-01 07:26:46'),(20,41,'email','login','$2y$10$jZhw.DMVBb1uL5oGFZSvQuDvcoURCi1aEeJ8x5g20hoeQdUyxv166','2026-09-01 07:36:46',0,'2026-09-01 07:26:46','2026-09-01 07:34:33'),(21,41,'email','login','$2y$10$byjzZFUCXr.RrjYLcqu8t.cn/QIn5jfKXwBAgxbLdp.HQY048/HkW','2026-09-01 07:44:33',0,'2026-09-01 07:34:33','2026-09-01 07:35:08'),(22,41,'email','login','$2y$10$QbtxNegZHsEalWv3PdHTf.lTVXXKAmtL8e6xOAbZLSWCihpzgQXQG','2026-09-01 07:45:08',0,'2026-09-01 07:35:08','2026-09-01 07:35:41'),(23,41,'email','login','$2y$10$ahSpz1glFNijJvIZFl2Fq.kcsz6Ar6lE31XhezuxRGrhMeMWdeVC.','2026-09-01 15:35:58',0,'2026-09-01 15:25:58','2026-09-01 15:26:07'),(24,41,'email','login','$2y$10$/v.IXyHWgna3jV4yOEHuweL7abHvtFP22kVPnI0y7pHeuavAl6BKC','2026-09-01 15:36:07',0,'2026-09-01 15:26:07','2026-09-01 15:27:07'),(25,41,'email','login','$2y$10$EiG3lF9l.52j6CBlljSFbeSLkXaeXMUqOJKm/0eDS8V/ConORes9S','2026-09-01 15:37:07',2,'2026-09-01 15:27:07','2026-09-01 15:29:15'),(26,10,'email','login','$2y$10$E1ibtOC9STupxJUDlbKER.7NlhgKuRBDLj.G75oBLlYyAeA1YFd2q','2026-09-01 16:38:28',0,'2026-09-01 16:28:28','2026-09-02 00:48:43'),(27,10,'email','login','$2y$10$QkKOS5YOw4QkfWBO5zDA4ebC67JjxibmiH6s/t5wU3QdrqCwuHpjS','2026-09-02 00:58:43',0,'2026-09-02 00:48:43','2026-09-02 00:59:41'),(28,41,'email','login','$2y$10$zgahA8cZ5ub6oQ3OghUUBOJn8SNIt/ygGmnfavwqbV0bDr.ZPbrlW','2026-09-02 01:01:06',0,'2026-09-02 00:51:06',NULL),(29,10,'email','login','$2y$10$1eem42G4SnTIV2I2Dr9KaOfboAZHMQlKgBRWlfzfW1zRu6dl1mXQi','2026-09-02 01:09:41',0,'2026-09-02 00:59:41','2026-09-02 01:02:37'),(30,10,'email','login','$2y$10$F/oLOFW515kZcf0NIe.oses/UWzp0g/pQ6sbPcOnrkdjVOUUqqJv.','2026-09-02 01:43:44',0,'2026-09-02 01:33:44','2026-09-02 01:34:17'),(31,10,'email','login','$2y$10$q2OK3m/adhxKNecFDFAyCeCM5TF3XBpBT7r4V6jKE/QmDtVo3mdYa','2026-09-04 12:30:54',0,'2026-09-04 12:20:54','2026-09-10 12:41:00'),(32,1,'email','login','$2y$10$/wLevGzFQpnyxMfg4AbySOhEDPgydX1/nltqzJGvcP0xWy2vz3V4m','2026-09-10 12:43:28',0,'2026-09-10 12:33:28','2026-09-10 12:33:34'),(33,1,'email','login','$2y$10$3dmVlpNbrSa/5AvuxAnROOyFgnXbOx7VO/I39A41/15U8uTjUnXAq','2026-09-10 12:43:34',0,'2026-09-10 12:33:34','2026-09-10 12:33:38'),(34,1,'email','login','$2y$10$G2fGAUxPn/9AW//E67kQCes849Ml0YoXQLgiu812JQUhL9JNu.sCW','2026-09-10 12:43:38',0,'2026-09-10 12:33:38','2026-09-10 12:33:39'),(35,1,'email','login','$2y$10$bXpcyx8H7sAgDMLphh4cd.onMXRMpT6KypPOv8PVeJxwdrJgCqc7e','2026-09-10 12:43:39',0,'2026-09-10 12:33:39','2026-09-10 12:34:01'),(36,1,'email','login','$2y$10$TwUOPqPeDgW15vQT0hi2oevQE/oUgFE.sBSByKU19SreoqlSrUoy6','2026-09-10 12:44:01',0,'2026-09-10 12:34:01','2026-09-10 12:34:28'),(37,1,'email','login','$2y$10$nKqi.kZ7okXrYc2.jI1kHeYBZbqnFBR8S1jurA/sv0v6qjJosJUS6','2026-09-10 12:44:28',0,'2026-09-10 12:34:28','2026-09-10 12:34:40'),(38,1,'email','login','$2y$10$xDZv..fzLbA73MQYT02kB.zT1ib9GkqP2DF9LKL7dmOMYnkwlSxpm','2026-09-10 12:44:40',0,'2026-09-10 12:34:40','2026-09-10 12:34:51'),(39,1,'email','login','$2y$10$Z8vM1of70FBcsGAFIibbDex7Bc24A25QulshyAXEDWQ9jSa2kYBAu','2026-09-10 12:44:51',0,'2026-09-10 12:34:51','2026-09-10 12:35:13'),(40,1,'email','login','$2y$10$nkzEa0cjtqTgFtAHYg9E2uYzbnyomzDyvHS2fe6GS9UsBRpEc7I/e','2026-09-10 12:45:13',0,'2026-09-10 12:35:13','2026-09-10 12:35:48'),(41,1,'email','login','$2y$10$p89pGDK6TbeN/S25z1o.HOGx0TXsOdORgLSo/Z7KXoHOoRpN0Hnka','2026-09-10 12:45:48',0,'2026-09-10 12:35:48','2026-09-10 12:35:58'),(42,1,'email','login','$2y$10$He6tVKLJnNiiKlPZt8H9r.7fsVOEXvods9kAvMwF.acUj1T11cCIq','2026-09-10 12:45:58',0,'2026-09-10 12:35:58','2026-09-10 12:37:05'),(43,1,'email','login','$2y$10$XjBeKm2dmwcfbh5sDa02Ne/B0NqEITGFAP9i2sfT02BN8p5RY0K4u','2026-09-10 12:47:05',0,'2026-09-10 12:37:05','2026-09-10 12:38:16'),(44,1,'email','login','$2y$10$nMZyW9M0BDhiCfJ94cQYge5Hz4Klmb/TpH/FFqYIM6IPY31Wvd1HK','2026-09-10 12:48:16',0,'2026-09-10 12:38:16','2026-09-10 12:38:55'),(45,1,'email','login','$2y$10$0SU/64gaCbz8lmPvvG3X7OzhOE58EBdaIGPvwiWmHKx08k/bB5NlO','2026-09-10 12:48:55',0,'2026-09-10 12:38:55','2026-09-10 12:38:55'),(46,1,'email','login','$2y$10$YvNIqul79kFvm9hlfyTrouTikLMGF9cL3UdTaOfn6WuCgJ/28a.9W','2026-09-10 12:48:55',0,'2026-09-10 12:38:55','2026-09-10 12:39:02'),(47,1,'email','login','$2y$10$bRYOretxuitjeBI6L6vfVO7i866lshO2TrSo5aTzSI/888C5HySXm','2026-09-10 12:49:02',0,'2026-09-10 12:39:02','2026-09-10 12:39:05'),(48,1,'email','login','$2y$10$zAZgP55zMRlHX1TQmkD/meTyWo.P.1gnLqZzO893VAha/G/wx/wju','2026-09-10 12:49:05',0,'2026-09-10 12:39:05','2026-09-10 12:39:18'),(49,1,'email','login','$2y$10$AESdhmjh70gH5F5N3E6UK.vZTcoYpkUSQcuJshr5jF6GR4dp8f97K','2026-09-10 12:49:18',0,'2026-09-10 12:39:18','2026-09-10 12:39:41'),(50,1,'email','login','$2y$10$ESkHjohq45uBVJqMV6V5deJcky11bPmq8HkFUt88ErXi5KVPGFyDy','2026-09-10 12:49:41',0,'2026-09-10 12:39:41','2026-09-10 12:39:59'),(51,1,'email','login','$2y$10$vqHMgmn6g47.byK52hC1zu0Es.rk43F2E9H4wI5dKIPjI3CRpBtoa','2026-09-10 12:49:59',0,'2026-09-10 12:39:59','2026-09-10 12:43:03'),(52,10,'email','login','$2y$10$MgnUcEkCMFqmK8JyizZglOc9Gx062YLtch1lcu0qrLf9XL8olwV/C','2026-09-10 12:51:00',0,'2026-09-10 12:41:00',NULL),(53,1,'email','login','$2y$10$QUXK1Uexa5JQOSmhqc1Ecu1fg69ueKUk1HgZ2RKxQ9rz1L.FAzPFS','2026-09-10 12:53:03',0,'2026-09-10 12:43:03','2026-09-10 13:55:46'),(54,1,'email','login','$2y$10$zKuk5JB.XDPxaU.FRQL5Fuu4v4nR46lxp4qOEo4yHiK74HlHop/KS','2026-09-10 14:05:46',0,'2026-09-10 13:55:46','2026-09-10 13:56:21');
/*!40000 ALTER TABLE `user_2fa_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_login_events`
--

DROP TABLE IF EXISTS `user_login_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_login_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `login_result` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_login_events_user` (`user_id`),
  KEY `idx_user_login_events_result` (`login_result`),
  CONSTRAINT `fk_user_login_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_login_events`
--

LOCK TABLES `user_login_events` WRITE;
/*!40000 ALTER TABLE `user_login_events` DISABLE KEYS */;
INSERT INTO `user_login_events` VALUES (1,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-08-28 12:23:10','success','otp_verified'),(2,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-08-29 08:17:19','success','otp_verified'),(3,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-08-31 14:47:47','success','otp_verified'),(4,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-08-31 14:56:55','success','otp_verified'),(5,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-08-31 15:01:12','success','direct_login'),(6,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 03:33:05','success','direct_login'),(7,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 03:53:20','failed','invalid_password'),(8,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 03:53:33','success','direct_login'),(9,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 04:27:10','success','direct_login'),(10,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:25:38','success','direct_login'),(11,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:29:59','success','direct_login'),(12,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:30:59','success','otp_verified'),(13,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:35:56','failed','invalid_password'),(14,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:36:27','failed','invalid_password'),(15,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:36:36','success','direct_login'),(16,37,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:37:55','failed','invalid_password'),(17,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:38:10','success','direct_login'),(18,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:47:01','success','direct_login'),(19,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 05:47:35','success','direct_login'),(20,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 06:02:19','success','direct_login'),(21,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 07:20:33','success','direct_login'),(22,41,'172.21.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/152.0.7977.64 Mobile/15E148 Safari/604.1','2026-09-01 07:35:41','success','otp_verified'),(23,40,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-01 15:26:32','success','direct_login'),(24,41,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 OPR/134.0.0.0 (Edition std-2)','2026-09-01 15:29:15','success','otp_verified'),(25,41,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 OPR/134.0.0.0 (Edition std-2)','2026-09-01 15:29:27','success','direct_login'),(26,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.135.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-09-02 01:02:37','success','otp_verified'),(27,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-02 01:34:17','success','otp_verified'),(28,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-02 01:34:21','success','direct_login'),(29,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-02 02:31:04','success','direct_login'),(30,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-02 02:51:08','success','direct_login'),(31,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 06:04:21','success','direct_login'),(32,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 11:50:35','success','direct_login'),(33,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 12:00:26','success','direct_login'),(34,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 12:26:10','success','direct_login'),(35,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 12:28:30','success','direct_login'),(36,10,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 12:42:46','success','direct_login'),(37,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 13:56:21','success','otp_verified'),(38,1,'172.21.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-10 14:56:24','success','direct_login');
/*!40000 ALTER TABLE `user_login_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_password_history`
--

DROP TABLE IF EXISTS `user_password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_password_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_password_history_user` (`user_id`),
  CONSTRAINT `fk_password_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_password_history`
--

LOCK TABLES `user_password_history` WRITE;
/*!40000 ALTER TABLE `user_password_history` DISABLE KEYS */;
INSERT INTO `user_password_history` VALUES (1,1,'$2y$10$TG43P5/sT628EvnF.agoeufWjP/AzrWhROwZoAaoeyO51Z2lcb46G','2026-08-28 12:07:53'),(2,2,'$2y$10$a3QPnmKLlwC/4k0q6clwju7aEZCkB/xpnZgy0ILElzfzCDyPYYw8W','2026-08-28 12:07:53'),(3,3,'$2y$10$mQ9PL5gA5MesA6AEBX5my.m8YUg/1MtLURUN55AygeKfew3zUmUUi','2026-08-28 12:07:53'),(4,4,'$2y$10$8rPiBNciUDFcyVe7kxlWxekm0dyomVQc5T.JglhAI2STl6KD9G5ve','2026-08-28 12:07:53'),(5,5,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','2026-08-28 12:07:53'),(6,6,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','2026-08-28 12:07:53'),(7,7,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','2026-08-28 12:07:53'),(8,8,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','2026-08-28 12:07:53'),(9,9,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','2026-08-28 12:07:53'),(10,10,'$2y$10$i7oI47MUllGyWkJEEd7IguSV/NbS4PeeueMFwmonO0AOH13zUYCzW','2026-08-28 12:07:53'),(11,11,'$2y$10$dtiJ57js4oGGaiREUBicwe0qvFKoPgGiUjXNfsSe4wZTU2Z/Zwmay','2026-08-28 12:07:53'),(12,12,'$2y$10$p8ntbwd5o5dvaO2tv2mlhuGTjMerNYX/4es.twVnVkXpW.S2os5DG','2026-08-28 12:07:53'),(13,13,'$2y$10$3HJbTE8I.KKuuxyp7838W.vVlHcDP7W6jsbV/Vu1pxJUiNnhAri9e','2026-08-28 12:07:53'),(14,14,'$2y$10$kHHalKrdPbwXXTL3KhtxwOYqVl22tLSCLb4LNgNIEyeSK5D95BuZe','2026-08-28 12:07:53'),(15,15,'$2y$10$PsAseWJWteN4Tc0GfybReONMrPn.V8hvHlPJs2jH5Lxfb/lyc45r.','2026-08-28 12:07:53'),(16,16,'$2y$10$l1og5iTIYTy3wVKXcnhNLu7RbSbuUO7sbquAbFe9HwflJ6SRlzgv6','2026-08-28 12:07:53'),(17,17,'$2y$10$CiGWVMTrt3GQSpbgtMJGY.pDASGd6JgVJUfczXqW9xDLTrHYU812i','2026-08-28 12:07:53'),(18,18,'$2y$10$UOGNLaPEZOPoUJKqTnnow.MKfZ.zvHdDEpY043W12BTFWKfqfGEey','2026-08-28 12:07:53'),(19,19,'$2y$10$DM6uD/IlNGnTVzI7YYyA7.iELHI9fA83X6hFVbg0GQZlfr0gyoKAO','2026-08-28 12:07:53'),(20,25,'$2y$10$3KdeE/FRF.VlY/KbjRAIL.8puPSJdchyksD04hnoXxmDQ74u/JCzq','2026-08-28 12:07:53'),(21,26,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','2026-08-28 12:07:53'),(22,27,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','2026-08-28 12:07:53'),(23,29,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','2026-08-28 12:07:53'),(24,30,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','2026-08-28 12:07:53'),(25,31,'$2y$10$kg3vl5h7ehBf5G74aA0Puu4CiGj.nrIHSHda9nVJ.tIDtzbO3SyzS','2026-08-28 12:07:53'),(26,32,'$2y$10$DPWDG.NCsUyecaxusU8DRuJOnfqDXNL3b2INFcilYPWbiCkZh53Vi','2026-08-28 12:07:53'),(27,33,'$2y$10$1YSDEG/m5ozh7oRL8ZRqU.VT4cJ9aV7QbSbt2QC44rCEKlWv26k5y','2026-08-28 12:07:53'),(28,34,'$2y$10$kAwM/RYnAhBBC/ZehYI/kObEUCCHDstjRrxf6IzcrYlvsnA5UksZG','2026-08-28 12:07:53'),(29,35,'$2y$10$iz/VulKpaspxifk9YPmmUOTu3AZCdDVObpjwcOzMBjFzpjz7Crvny','2026-08-28 12:07:53'),(30,36,'$2y$10$4yW8t6.7ex4NUcA8Kur5i.2mhZhbnJE8FVWmgufmBGF7z/O7YlMvm','2026-08-28 12:07:53'),(31,37,'$2y$10$Hjkk9xzDZ5nGCO/ogPBQxeXlEwBHi2mCR29oZVgUKNXixSW5hjX9.','2026-08-28 12:07:53'),(32,38,'$2y$10$kUhMZEpEzS9NpEeOal0sqetUATKUOTsBJhlmYRLCGG6ODJRABWuhO','2026-08-28 12:07:53'),(33,39,'$2y$10$bI2nxf4bJ8JN6wdc.nfm.u973KGUtIWB3qnL.sPnmSdZAn5ydE0Lm','2026-08-28 12:07:53'),(34,40,'$2y$10$2Emh04ZF.Soh.ewyJf9XAu50phicoIAlf6Wk9lnGx/B6dW3c.3jiK','2026-09-01 05:28:31'),(35,40,'$2y$10$8Hmlg0IKpZnaTBttzESAceuArBhKhLgkVRKR.vWbmicAHbIs64Utu','2026-09-01 05:33:50'),(36,41,'$2y$10$0ZwkONxaNvgdFxtjmI3vaeWqRZRkQDwP3FnE3c.90ObKblISuntAm','2026-09-01 05:46:32');
/*!40000 ALTER TABLE `user_password_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_security_settings`
--

DROP TABLE IF EXISTS `user_security_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_security_settings` (
  `user_id` int NOT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `alert_email_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_password_changed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_security_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_security_settings`
--

LOCK TABLES `user_security_settings` WRITE;
/*!40000 ALTER TABLE `user_security_settings` DISABLE KEYS */;
INSERT INTO `user_security_settings` VALUES (1,1,1,NULL,'2026-08-28 12:07:53'),(2,1,1,NULL,'2026-08-28 12:07:53'),(3,1,1,NULL,'2026-08-28 12:07:53'),(4,1,1,NULL,'2026-08-28 12:07:53'),(5,1,1,NULL,'2026-08-28 12:07:53'),(6,1,1,NULL,'2026-08-28 12:07:53'),(7,1,1,NULL,'2026-08-28 12:07:53'),(8,1,1,NULL,'2026-08-28 12:07:53'),(9,1,1,NULL,'2026-08-28 12:07:53'),(10,1,1,NULL,'2026-08-28 12:07:53'),(11,1,1,NULL,'2026-08-28 12:07:53'),(12,1,1,NULL,'2026-08-28 12:07:53'),(13,1,1,NULL,'2026-08-28 12:07:53'),(14,1,1,NULL,'2026-08-28 12:07:53'),(15,1,1,NULL,'2026-08-28 12:07:53'),(16,1,1,NULL,'2026-08-28 12:07:53'),(17,1,1,NULL,'2026-08-28 12:07:53'),(18,1,1,NULL,'2026-08-28 12:07:53'),(19,1,1,NULL,'2026-08-28 12:07:53'),(25,1,1,NULL,'2026-08-28 12:07:53'),(26,1,1,NULL,'2026-08-28 12:07:53'),(27,1,1,NULL,'2026-08-28 12:07:53'),(29,1,1,NULL,'2026-08-28 12:07:53'),(30,1,1,NULL,'2026-08-28 12:07:53'),(31,1,1,NULL,'2026-08-28 12:07:53'),(32,1,1,NULL,'2026-08-28 12:07:53'),(33,1,1,NULL,'2026-08-28 12:07:53'),(34,1,1,NULL,'2026-08-28 12:07:53'),(35,1,1,NULL,'2026-08-28 12:07:53'),(36,1,1,NULL,'2026-08-28 12:07:53'),(37,1,1,NULL,'2026-08-28 12:07:53'),(38,1,1,NULL,'2026-08-28 12:07:53'),(39,1,1,NULL,'2026-08-28 12:07:53'),(40,1,1,'2026-09-01 05:33:50','2026-09-01 05:33:50'),(41,1,1,NULL,'2026-09-01 05:46:41');
/*!40000 ALTER TABLE `user_security_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `phone_verified_at` datetime DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','lydo','sk_chairman','youth','employer','training_provider') COLLATE utf8mb4_unicode_ci DEFAULT 'lydo',
  `is_active` tinyint(1) DEFAULT '1',
  `status` enum('Active','Pending','Declined','Suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_type` enum('employer','training_provider') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temp_password_required` tinyint(1) DEFAULT '0',
  `approval_remark` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin1','dummyseeker22+admin11@gmail.com','09171234567',NULL,NULL,'$2y$10$vcr4m4f/mhYHVR8i1Sj.jOnTMVcNixL8QvVdNyxyjqtMV4tswQS3W','Senior Administrator','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 12:22:29'),(2,'admin2','dummyseeker22+admin22@gmail.com','09179876543',NULL,NULL,'$2y$10$a3QPnmKLlwC/4k0q6clwju7aEZCkB/xpnZgy0ILElzfzCDyPYYw8W','System Manager','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 12:22:29'),(3,'admin3','dummyseeker22+admin33@gmail.com',NULL,NULL,NULL,'$2y$10$mQ9PL5gA5MesA6AEBX5my.m8YUg/1MtLURUN55AygeKfew3zUmUUi','Database Administrator','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(4,'admin4','dummyseeker22+admin44@gmail.com',NULL,NULL,NULL,'$2y$10$8rPiBNciUDFcyVe7kxlWxekm0dyomVQc5T.JglhAI2STl6KD9G5ve','Operations Lead','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(5,'jsmith','dummyseeker22+jsmith5@gmail.com',NULL,NULL,NULL,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','John Smith','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(6,'mgarcia','dummyseeker22+mgarcia6@gmail.com',NULL,NULL,NULL,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','Maria Garcia','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(7,'rsantos','dummyseeker22+rsantos7@gmail.com',NULL,NULL,NULL,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','Ricardo Santos','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(8,'acruz','dummyseeker22+acruz8@gmail.com',NULL,NULL,NULL,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','Angela Cruz','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(9,'blopez','dummyseeker22+blopez9@gmail.com',NULL,NULL,NULL,'$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G','Benjamin Lopez','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-05 21:20:51','2026-09-10 11:35:04'),(10,'testyouth','dummyseeker22+testyouth10@gmail.com','09123456789',NULL,NULL,'$2y$10$nNOZUzkggqNeYE3OOtZWa.FlF3UJSw5vTw6Vg3rhRlJAj0eHMS7Ru','Test Youth','youth',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:49:52','2026-09-10 12:26:41'),(11,'testyouth2','dummyseeker22+testyouth211@gmail.com',NULL,NULL,NULL,'$2y$10$dtiJ57js4oGGaiREUBicwe0qvFKoPgGiUjXNfsSe4wZTU2Z/Zwmay','Test Youth','youth',1,'Pending',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:54:38','2026-09-10 11:35:04'),(12,'testyouth3','dummyseeker22+testyouth312@gmail.com',NULL,NULL,NULL,'$2y$10$p8ntbwd5o5dvaO2tv2mlhuGTjMerNYX/4es.twVnVkXpW.S2os5DG','Test Youth','youth',1,'Pending',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:55:38','2026-09-10 11:35:04'),(13,'testyouth4','dummyseeker22+testyouth413@gmail.com',NULL,NULL,NULL,'$2y$10$3HJbTE8I.KKuuxyp7838W.vVlHcDP7W6jsbV/Vu1pxJUiNnhAri9e','Test Youth','youth',1,'Pending',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:56:10','2026-09-10 11:35:04'),(14,'testyouth5','dummyseeker22+testyouth514@gmail.com',NULL,NULL,NULL,'$2y$10$kHHalKrdPbwXXTL3KhtxwOYqVl22tLSCLb4LNgNIEyeSK5D95BuZe','Test Youth','youth',1,'Pending',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:56:40','2026-09-10 11:35:04'),(15,'testyouth6','dummyseeker22+testyouth615@gmail.com',NULL,NULL,NULL,'$2y$10$PsAseWJWteN4Tc0GfybReONMrPn.V8hvHlPJs2jH5Lxfb/lyc45r.','Test Youth','youth',1,'Pending',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:57:07','2026-09-10 11:35:04'),(16,'testyouth7','dummyseeker22+testyouth716@gmail.com',NULL,NULL,NULL,'$2y$10$l1og5iTIYTy3wVKXcnhNLu7RbSbuUO7sbquAbFe9HwflJ6SRlzgv6','Test Youth','youth',1,'Pending',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:57:38','2026-09-10 11:35:04'),(17,'testyouth8','dummyseeker22+testyouth817@gmail.com','09123456789',NULL,NULL,'$2y$10$CiGWVMTrt3GQSpbgtMJGY.pDASGd6JgVJUfczXqW9xDLTrHYU812i','Test Youth','youth',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-11 10:58:20','2026-09-10 12:22:29'),(18,'nhempharmacy','aclonhemday+employer@gmail.com',NULL,NULL,NULL,'$2y$10$gzXPdx5oOtBdpWAxd/C12.vM/BCTDP.HT7VCQMdgYPNLDUP5.QK66','Nhem pharmacy','employer',1,'Active','Baga','employer','/uploads/providers/provider_doc_1778587396_6a0317040f4bb.png',0,'',NULL,NULL,'2026-05-12 20:03:16','2026-09-01 03:48:47'),(19,'nhemdaygaclo','aclonhem@gmail.com',NULL,NULL,NULL,'$2y$10$DM6uD/IlNGnTVzI7YYyA7.iELHI9fA83X6hFVbg0GQZlfr0gyoKAO','Nhem Day G. Aclo','employer',1,'Active','Baga','employer','/uploads/providers/provider_doc_1778591593_6a0327692d2c4.png',1,'',NULL,NULL,'2026-05-12 21:13:13','2026-05-13 00:15:06'),(25,'lydo_admin','dummyseeker22+lydoadmin25@gmail.com',NULL,NULL,NULL,'$2y$10$3KdeE/FRF.VlY/KbjRAIL.8puPSJdchyksD04hnoXxmDQ74u/JCzq','Maria Teresa Lim','lydo',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-12 23:12:56','2026-09-10 11:35:04'),(26,'sk_baga','dummyseeker22+skbaga26@gmail.com',NULL,NULL,NULL,'$2y$10$yc5WF9.yUnYHawUVGb8KZe.BboofwkhFNerV6ORTp5oy1L2sD/kHq','Carlos Reyes','sk_chairman',1,'Active','Baga',NULL,NULL,0,NULL,NULL,NULL,'2026-05-12 23:12:56','2026-09-10 11:55:47'),(27,'sk_bangko','dummyseeker22+skbangko27@gmail.com',NULL,NULL,NULL,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','Angela Mendoza','sk_chairman',1,'Active','Bangko',NULL,NULL,0,NULL,NULL,NULL,'2026-05-12 23:12:56','2026-09-10 11:55:47'),(29,'sk_delapaz','dummyseeker22+skdelapaz29@gmail.com',NULL,NULL,NULL,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','Patricia Santos','sk_chairman',1,'Active','Dela Paz',NULL,NULL,0,NULL,NULL,NULL,'2026-05-12 23:12:56','2026-09-10 11:55:47'),(30,'sk_poblacion','dummyseeker22+skpoblacion30@gmail.com',NULL,NULL,NULL,'$2y$10$WQoHMMCPO00GjFO0jtE83OXvj16Wswx0bG6.MVnWBQsSYjsUFH59G','Marco Dela Cruz','sk_chairman',1,'Active','Poblacion',NULL,NULL,0,NULL,NULL,NULL,'2026-05-12 23:12:56','2026-09-10 11:55:47'),(31,'test_provider','dummyseeker22+testprovider31@gmail.com',NULL,NULL,NULL,'$2y$10$VI69gz40/RuNqnXw4iEzWe2wnnfP5nJsz40Cg/D1ATUkp3gV/d1RO','Test Training Provider','training_provider',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-13 08:18:46','2026-09-10 11:35:04'),(32,'test_youth','dummyseeker22+testyouth32@gmail.com',NULL,NULL,NULL,'$2y$10$DPWDG.NCsUyecaxusU8DRuJOnfqDXNL3b2INFcilYPWbiCkZh53Vi','Test Youth User','youth',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-13 08:18:46','2026-09-10 11:35:04'),(33,'youth_dev','dummyseeker22+youthdev33@gmail.com',NULL,NULL,NULL,'$2y$10$1YSDEG/m5ozh7oRL8ZRqU.VT4cJ9aV7QbSbt2QC44rCEKlWv26k5y','Dev Youth','youth',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-05-14 18:39:50','2026-09-10 11:35:04'),(34,'employer_dev','dummyseeker22+employerdev34@gmail.com',NULL,NULL,NULL,'$2y$10$kAwM/RYnAhBBC/ZehYI/kObEUCCHDstjRrxf6IzcrYlvsnA5UksZG','Dev Employer','employer',1,'Active',NULL,NULL,NULL,0,'',NULL,NULL,'2026-05-14 18:39:51','2026-09-10 11:35:04'),(35,'training_provider_dev','dummyseeker22+trainingproviderdev35@gmail.com',NULL,NULL,NULL,'$2y$10$iz/VulKpaspxifk9YPmmUOTu3AZCdDVObpjwcOzMBjFzpjz7Crvny','Dev Training_provider','training_provider',1,'Active',NULL,NULL,NULL,0,'',NULL,NULL,'2026-05-14 18:39:51','2026-09-10 11:35:04'),(36,'testprovider20260706','dummyseeker22+testprovider2026070636@gmail.com',NULL,NULL,NULL,'$2y$10$4yW8t6.7ex4NUcA8Kur5i.2mhZhbnJE8FVWmgufmBGF7z/O7YlMvm','Test Employer Co','employer',1,'Active','Barangay Test','employer','/uploads/providers/provider_doc_1783306036_6a4b1734927be.pdf',1,'Approved for testing',NULL,NULL,'2026-07-06 02:47:16','2026-09-10 11:35:05'),(37,'fredylusing','fredylusing@gmail.com',NULL,NULL,NULL,'$2y$10$Hjkk9xzDZ5nGCO/ogPBQxeXlEwBHi2mCR29oZVgUKNXixSW5hjX9.','fredy lusing','employer',1,'Active','magsaysay, panaon','employer','/uploads/providers/provider_doc_1786590270_6a7d343e341b5.jpg',0,'',NULL,NULL,'2026-08-13 03:04:30','2026-08-13 03:06:49'),(38,'rada','rada@gmail.com',NULL,NULL,NULL,'$2y$10$kUhMZEpEzS9NpEeOal0sqetUATKUOTsBJhlmYRLCGG6ODJRABWuhO','Jovilyn Mie H. Rada','sk_chairman',1,'Active','Punta',NULL,NULL,0,NULL,1,NULL,'2026-08-13 03:33:34','2026-08-13 03:34:42'),(39,'lusing','lusing@gmail.com','09674185880',NULL,NULL,'$2y$10$bI2nxf4bJ8JN6wdc.nfm.u973KGUtIWB3qnL.sPnmSdZAn5ydE0Lm','fredy Lusing','youth',1,'Active',NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-08-13 03:40:18','2026-09-10 12:22:29'),(40,'skmagasaysay','dajaokc7@gmail.com',NULL,NULL,NULL,'$2y$10$8Hmlg0IKpZnaTBttzESAceuArBhKhLgkVRKR.vWbmicAHbIs64Utu','kisey dajao','sk_chairman',1,'Active','Magsaysay',NULL,NULL,0,NULL,1,NULL,'2026-09-01 05:28:31','2026-09-01 05:33:50'),(41,'lusingni','lusingfredy09@gmail.com','09615264572',NULL,NULL,'$2y$10$0ZwkONxaNvgdFxtjmI3vaeWqRZRkQDwP3FnE3c.90ObKblISuntAm','Fredy Lusing','youth',1,'Active','Magsaysay',NULL,NULL,0,NULL,NULL,NULL,'2026-09-01 05:46:32','2026-09-10 12:22:29');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `youth_barangay_transfers`
--

DROP TABLE IF EXISTS `youth_barangay_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `youth_barangay_transfers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `profile_id` int NOT NULL,
  `from_barangay` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_barangay` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `request_remark` text COLLATE utf8mb4_unicode_ci,
  `review_remark` text COLLATE utf8mb4_unicode_ci,
  `requested_by` int NOT NULL,
  `reviewed_by` int DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transfer_destination` (`to_barangay`,`status`),
  KEY `idx_transfer_profile` (`profile_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `youth_barangay_transfers`
--

LOCK TABLES `youth_barangay_transfers` WRITE;
/*!40000 ALTER TABLE `youth_barangay_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `youth_barangay_transfers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-11  2:50:49
