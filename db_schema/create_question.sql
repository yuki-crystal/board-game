CREATE TABLE `question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `classification` varchar(45) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `asking_time` datetime DEFAULT NULL,
  `title` varchar(45) DEFAULT NULL,
  `content` varchar(500) DEFAULT NULL,
  `reply_time` datetime DEFAULT NULL,
  `reply` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
