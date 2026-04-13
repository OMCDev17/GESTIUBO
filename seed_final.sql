
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mayhem_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `mayhem_db`;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text NOT NULL,
  `apellidos` text NOT NULL,
  `dni_pasaporte` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` text NOT NULL,
  `foto_url` text DEFAULT NULL,
  `rol` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni_pasaporte` (`dni_pasaporte`) USING HASH,
  UNIQUE KEY `username` (`username`) USING HASH,
  UNIQUE KEY `email` (`email`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `employees` VALUES
(1,'Francisco','García Martínez','12345678X','francisco.garcia','franco123','1990-05-14','francisco.garcia@universidad.es','https://i.pravatar.cc/160?img=12','coordinador'),
(2,'María','López','87654321L','maria.lopez','maria123','1993-08-09','marialopez@universidad.es','https://i.pravatar.cc/160?img=15','empleado'),
(3,'Luis','Martínez','34567890P','luis.martinez','luis123','1988-11-22','luismartinez@universidad.es','https://i.pravatar.cc/160?img=48','empleado'),
(4,'Ana','Gómez','56789012Q','ana.gomez','ana123','1995-03-30','anagomez@universidad.es','https://i.pravatar.cc/160?img=33','empleado'),
(5,'Roberto','Sánchez','23456789M','roberto.sanchez','coord123','1982-10-17','roberto.sanchez@universidad.es','https://i.pravatar.cc/160?img=5','coordinador'),
(6,'Laura','Pérez','45678901N','laura.perez','seguridad123','1986-07-08','laura.perez@universidad.es','https://i.pravatar.cc/160?img=30','seguridad'),
(7,'Admin','Principal','00000000T','admin','admin123','1980-01-01','admin@universidad.es','https://i.pravatar.cc/160?img=1','admin'),
(8,'Jairo','Afonso','78787878J','j.afonso','$2y$10$kw4emAelDLZDjTnLDiZR5eRxpU5PSlOgVEWaVqpztAE6GyO6VyMwS','1991-11-14','jairito@gmail.com','https://i.pravatar.cc/160?u=jairilicious%40gmail.com','coordinador'),
(9,'Carla','Padilla','79797979X','carlita','$2y$10$8syN.mbLZW8S32sn9ema5Ogy3lfzr3p2hMCE69g3J6fPNLT9ZFzo6','2003-10-31','carlita@gmail.com','https://i.pravatar.cc/160?u=carlita%40gmail.com','empleado'),
(10,'Alexander','Piña','80808080F','alefriki','$2y$10$BFFBADkV1kEX7dVhju47N.4RUJ/8NGBSFtEXXtce6cjxl1wD4J/5i','2002-02-12','Alexito@gmail.com','https://i.pravatar.cc/160?u=Alexito%40gmail.com','empleado'),
(11,'Petra','Ioana','80808080L','petra.ioana','$2y$10$YsOU1.l3ehg2oZoVgRtSHu9/xBsqRoknmJvGxjfot8buhdrlQl1I.','1998-01-11','jairilicious@gmail.com','https://i.pravatar.cc/160?u=jairilicious%40gmail.com','empleado'),
(12,'Tere','Teresita','77447744T','tere.sita','$2y$10$LeX6W/kIjj.xKxlz3v7I6egLhjbNyRG7t3JkQOeSYw8lrhEaH10Q.','2026-04-08','teresita@gmail.com','https://i.pravatar.cc/160?u=teresita%40gmail.com','empleado'),
(14,'Nayara','Ramirez','90909090X','nayi.nayi','$2y$10$Fvax4CQc4DLnqj5cP0UcLOV8VheXeMEXAiOUL1k2mdbtok7fG8M9K','2002-02-15','nayita@gmail.com','https://i.pravatar.cc/160?u=nayita%40gmail.com','empleado'),
(15,'Octavio','Manrique','46464646J','octavio','$2y$10$gYA37ESnTtmle8.2u3qOXeL2E0O13B7nRv27VsJk.By2qTVNicy.y','1991-05-15','octavio@inventado.com','https://i.pravatar.cc/160?u=octavio%40inventado.com','empleado'),
(16,'Pikachu','Pokemon','78785252K','pikachu','$2y$10$3v68APhJIEwB7rEtfc5mSuglGz4/YOXWlLa2QpVmEjyCKC96c55fW','2026-04-02','pikachu@inventadellas.com','https://i.pravatar.cc/160?u=pikachu%40inventadellas.com','empleado'),
(17,'Fer','Suarez','12121212M','fersito','$2y$10$o0j5IPWdZYZ1OL1VmuC4Y.m5J9YDzxurZA7UZQpEnbUsQ2X13Q02u','1991-06-06','fersito@inventado.com','https://i.pravatar.cc/160?u=fersito%40inventado.com','empleado'),
(18,'Adri','Suarez','12131212N','adri','$2y$10$Mrh.hD.SyV/IGGt91xcDVO6dkbJns7IreC9knZ2eP/q8zelJhpw.O','2025-11-05','omcdentaltrainer@gmail.com','https://i.pravatar.cc/160?u=omcdentaltrainer%40gmail.com','empleado');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;
commit;
DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name_ci` varchar(100) GENERATED ALWAYS AS (lcase(`name`)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `ux_groups_name_ci` (`name_ci`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `groups` VALUES
(1,'AFM-NANO',NULL,'2026-04-07 11:49:47','2026-04-07 14:48:27','afm-nano'),
(2,'Otros usuarios',NULL,'2026-04-07 11:49:47','2026-04-07 11:49:47','otros usuarios'),
(4,'BIOLAB',NULL,'2026-04-07 12:26:02','2026-04-07 14:08:06','biolab'),
(5,'AMBILAB',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','ambilab'),
(6,'GEO-GLOBAL',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','geo-global'),
(7,'PRODMAR',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','prodmar'),
(8,'QUIBIONAT',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','quibionat'),
(9,'QUIMIOPLAN',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','quimioplan'),
(10,'SINTESTER',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','sintester'),
(11,'PTGAS',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','ptgas'),
(12,'ECOBERTURA',NULL,'2026-04-07 12:29:39','2026-04-07 12:29:39','ecobertura'),
(67,'DIRECCIONES','2026-04-08 14:21:00','2026-04-08 13:52:36','2026-04-08 14:21:00','direcciones'),
(71,'DIRECCION','2026-04-09 09:19:45','2026-04-09 09:18:23','2026-04-09 09:19:45','direccion');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` varchar(4) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `code` (`code`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
commit;
DROP TABLE IF EXISTS `stays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `motivo` varchar(150) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `horario` tinyint(1) NOT NULL DEFAULT 1,
  `institucion` varchar(255) DEFAULT NULL,
  `pais` varchar(255) DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stays_employee` (`employee_id`),
  KEY `idx_stays_status` (`status`),
  KEY `fk_stay_group` (`group_id`),
  CONSTRAINT `fk_stay_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stay_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stays` WRITE;
/*!40000 ALTER TABLE `stays` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stays` VALUES
(1,1,'2026-01-15','2027-01-14','Postdoctoral',12,0,'Universidad de Ejemplo','España','active',NULL,'2026-04-09 09:58:16','2026-04-09 13:20:16'),
(2,2,'2025-09-01','2026-04-07','Predoctoral',9,0,'Universidad de Ejemplo','España','archived','2026-04-09 12:17:35','2026-04-09 09:58:16','2026-04-09 13:20:06'),
(3,3,'2025-02-10','2026-04-07','PDI',1,1,'Universidad de Ejemplo','España','archived','2026-04-09 10:14:29','2026-04-09 09:58:16','2026-04-09 10:14:29'),
(4,4,'2026-04-07','2026-04-08','Vistante',6,1,'Universidad de Minnesota','EEUU','archived','2026-04-09 13:58:36','2026-04-09 09:58:16','2026-04-09 13:19:31'),
(5,5,'2024-09-01','2026-04-30','Coordinador de grupo',4,0,'Universidad de Ejemplo','España','active',NULL,'2026-04-09 09:58:16','2026-04-09 12:15:33'),
(6,6,'2024-01-15','2026-09-16','Seguridad',11,0,'Universidad de Ejemplo','España','active',NULL,'2026-04-09 09:58:16','2026-04-10 09:30:32'),
(7,7,'2023-01-01','2030-01-01','Administración',2,1,'Universidad de Ejemplo','España','active',NULL,'2026-04-09 09:58:16','2026-04-09 09:58:16'),
(8,8,'2026-04-01','2026-06-30','TFG',11,1,'IES César Manrique','España','active',NULL,'2026-04-09 09:58:16','2026-04-10 09:16:48'),
(9,9,'2026-04-02','2026-04-29','PDI',8,0,'Mc University','España','active',NULL,'2026-04-09 09:58:16','2026-04-09 13:17:47'),
(10,10,'2026-01-01','2026-04-01','PDI',12,1,'IES BOBOLIA','Alemania','archived','2026-04-10 11:01:58','2026-04-09 09:58:16','2026-04-10 10:01:58'),
(11,11,'2026-04-08','2026-04-30','Otros',11,0,'IES PORTEZUELO','España','active',NULL,'2026-04-09 09:58:16','2026-04-10 09:31:17'),
(12,12,'2026-04-01','2026-04-30','ERASMUS',4,0,'IES OFRA','España','active',NULL,'2026-04-09 09:58:16','2026-04-09 13:18:33'),
(13,14,'2026-04-08','2026-04-30','Predoctoral',7,1,'IES OROTAVA','España','active',NULL,'2026-04-09 09:58:16','2026-04-09 13:18:48'),
(23,4,'2026-04-09','2026-04-30','Postdoctoral',6,1,'Universidad de Minnesota','EEUU','active',NULL,'2026-04-09 12:03:48','2026-04-09 13:18:59'),
(24,2,'2026-04-09','2026-04-30','Visitante',10,1,'Universidad de Pamplona','España','active',NULL,'2026-04-09 12:19:16','2026-04-09 13:19:04'),
(25,15,'2026-04-09','2100-01-01','Predoctoral',1,1,'Universidad de Massachusetts','EEUU','active',NULL,'2026-04-09 12:34:55','2026-04-09 13:19:10'),
(26,16,'2026-04-09','2026-04-30','Predoctoral',8,1,'Universidad Pokemon','Kanto','active',NULL,'2026-04-09 12:37:31','2026-04-09 13:19:16'),
(27,10,'2026-04-10','2026-04-30','TFM',2,1,'Universidad Madrid','España','active',NULL,'2026-04-10 09:26:07','2026-04-10 09:26:07'),
(28,17,'2026-04-10','2026-04-30','TFG',7,1,'Universidad de La Laguna','España','active',NULL,'2026-04-10 09:46:49','2026-04-10 09:46:49'),
(29,18,'2026-04-10','2026-04-30','Predoctoral',8,1,'ULL','España','active',NULL,'2026-04-10 09:48:56','2026-04-10 09:48:56');
/*!40000 ALTER TABLE `stays` ENABLE KEYS */;
UNLOCK TABLES;
commit;
DROP TABLE IF EXISTS `vista_usuario_estancias`;
/*!50001 DROP VIEW IF EXISTS `vista_usuario_estancias`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vista_usuario_estancias` AS SELECT
 1 AS `id_usuario`,
  1 AS `nombre_usuario`,
  1 AS `fecha_inicio`,
  1 AS `fecha_fin`,
  1 AS `estado` */;
SET character_set_client = @saved_cs_client;

USE `mayhem_db`;
/*!50001 DROP VIEW IF EXISTS `vista_usuario_estancias`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_usuario_estancias` AS select `e`.`id` AS `id_usuario`,`e`.`nombre` AS `nombre_usuario`,`s`.`fecha_inicio` AS `fecha_inicio`,`s`.`fecha_fin` AS `fecha_fin`,`s`.`status` AS `estado` from (`employees` `e` left join `stays` `s` on(`e`.`id` = `s`.`employee_id`)) order by `e`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

