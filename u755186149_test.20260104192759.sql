/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: u755186149_test
-- ------------------------------------------------------
-- Server version	11.8.3-MariaDB-log

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

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `vkey` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `allowed` tinyint(1) NOT NULL,
  `memberSince` date NOT NULL,
  `lastLogin` date NOT NULL,
  `location` varchar(180) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `LastSubscription` date DEFAULT NULL,
  `isAdmin` tinyint(1) NOT NULL DEFAULT 0,
  `accountType` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=383 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(3,'Urkey','$2y$10$JocuGXg2TKvBnGqjQiFjFufkeKHlnI71UwgVOIxW4TgKUs2kqI0Pa','ukikuki37@gmail.com','e6664b4290bd07ae24a6493252178db475e88f10223a98e2f64cc4672bdc4fc8',1,1,'2024-07-03','2024-10-10','\0Kragujevac, 34000, Central Serbia, RS',NULL,1,1),
(4,'Sukant','$2y$10$PiZEeRR30oxXh4BXDO9Pe.EzJKK4Qmfv8B4a70E.nguBp1njLeJS2','sukantratnakar@gmail.com','98a7923df546bb92e4f27e7928dfb5dae51786a291e307ddfbc94e5bac4805e6',1,1,'2024-07-03','2025-05-23','Saskatoon, S7W, Saskatchewan, CA',NULL,1,1),
(5,'sd','$2y$10$q0FbNRdRrRtuOnGCUzpC0.eAbqkjArWrEi/O6LAjryWbyyllkfyKG','kathiriyameghana91@gmail.com','27ddcc8bc270c861e9ba94d919ae5fe10457c6655458300e15dd1c18bb66fd76',0,1,'2024-07-09','0000-00-00','Surat, 395005, Gujarat, IN',NULL,0,1),
(14,'Sukant Sukant','$2y$10$GLMLeg7TzKRBrv.evL1S7eJ5Qx.H55VdIUH3zjASCBOSxjZBEE/mS','sukantcanada@gmail.com','ba8b16f65a028e52661d18b5a05b1e94662c3b233f74557c07abf6533e7fbb39',1,1,'2024-07-13','2024-07-13','Saskatoon, S7W, Saskatchewan, CA',NULL,1,1),
(15,'Nidhi Ratnakar','$2y$10$qX.lPBxjE7xZ8L0R3yYT5Omx7kkec.zz1iAx0Oi6pIdUD.FgUdJ.y','nidhi.k.ratnakar@gmail.com','94b67d6fe23fbefc6d0975f8b4c8ccf0c32858632f4a21f9d9076e67d1d31553',0,1,'2024-07-13','2024-07-13','Saskatoon, S7W, Saskatchewan, CA',NULL,0,1),
(16,'Uros Markovic','$2y$10$m7rLaYccVgImeNqYEAxu..UP3rcH7vxcrnWJwmZdedefIHjHYYHFW','ureumvampire@gmail.com','db8068eafa54ff4ea7db91edc20f69bc378d976d9718114008b83073e45f0042',0,1,'2024-07-13','2024-07-13','\0Kragujevac, 34000, Central Serbia, RS',NULL,0,1),
(17,'Anshu','$2y$10$5WluBvBUPYATlwAk43p9O.cYWEa75.nuDjoqaFlCxeJHDxC5ZBo26','anshu49@gmail.com','65dfc7665fadf8f9943c0b969533dd1fd375e72dfa7615f88a742783bcb771e9',1,1,'2024-07-22','2024-07-22','Dehra D?n, 248001, Uttarakhand, IN',NULL,0,1),
(18,'test2','$2y$10$3RL1/TwYA7JB/r8oaEwWd.Tbrh8jHPnfeielG0WZAgjJOLGnSvtGC','matijamp22.sp@gmail.com','627b1271017022664b84421eb2cbdfe0f74e76d1d82fa8ec2c8a2d737d43f23e',1,1,'2024-09-07','2024-09-07','\0Kragujevac, 34000, Central Serbia, RS',NULL,0,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
commit;

--
-- Dumping routines for database 'u755186149_test'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-01-04 19:28:22
