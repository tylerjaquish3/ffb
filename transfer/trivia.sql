-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.7.19 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for trivia
CREATE DATABASE IF NOT EXISTS `trivia` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `trivia`;

-- Dumping structure for table trivia.answers
CREATE TABLE IF NOT EXISTS `answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `answer` varchar(100) DEFAULT NULL,
  `correct` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `max_points` int(11) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=latin1;

-- Dumping data for table trivia.answers: ~4 rows (approximately)
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` (`id`, `question_id`, `user_id`, `answer`, `correct`, `max_points`, `submitted_at`) VALUES
	(164, 1, 24, 'green', 1, 87, '2020-05-07 11:37:32'),
	(165, 2, 24, '34343', 1, 93, '2020-05-07 11:37:52'),
	(166, 3, 24, 'mayo', 0, 87, '2020-05-07 11:38:12'),
	(167, 6, 26, 'china', 1, 87, '2020-05-07 11:45:56'),
	(168, 7, 26, 'georgia', 0, 44, '2020-05-07 11:46:35');
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;

-- Dumping structure for table trivia.points
CREATE TABLE IF NOT EXISTS `points` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `question_id` int(11) unsigned DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=latin1;

-- Dumping data for table trivia.points: ~8 rows (approximately)
/*!40000 ALTER TABLE `points` DISABLE KEYS */;
INSERT INTO `points` (`id`, `user_id`, `question_id`, `points`) VALUES
	(113, 24, 1, 87),
	(114, 24, 2, 93),
	(115, 24, 3, 0),
	(116, 25, 0, 0),
	(117, 26, 0, 0),
	(118, 26, 6, 87),
	(119, 26, 7, 0),
	(120, 0, 0, 0);
/*!40000 ALTER TABLE `points` ENABLE KEYS */;

-- Dumping structure for table trivia.questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL,
  `answer` varchar(100) DEFAULT NULL,
  `round_id` smallint(5) unsigned DEFAULT NULL,
  `sort_order` smallint(6) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reveal` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Dumping data for table trivia.questions: ~5 rows (approximately)
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` (`id`, `question`, `answer`, `round_id`, `sort_order`, `start_time`, `active`, `reveal`) VALUES
	(1, 'What is Tyler\'s favorite color?', 'Green', 1, 1, '2020-05-07 11:37:26', 1, 1),
	(2, 'How high is the tallest mountain?', '32,054 feet', 1, 2, '2020-05-07 11:37:49', 1, 1),
	(3, 'What is the most popular topping on a Whopper?', 'Pickles', 1, 3, '2020-05-07 11:38:06', 1, 1),
	(6, 'Where is the great wall of china?', 'china', 2, 1, '2020-05-07 11:45:50', 1, 1),
	(7, 'Who was the first woman on the moon?', 'Harriet Tubman', 2, 2, '2020-05-07 11:46:10', 1, 1),
	(8, 'What island do lemurs come from?', 'Madagascar', 1, 1, NULL, 0, 0);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;

-- Dumping structure for table trivia.rounds
CREATE TABLE IF NOT EXISTS `rounds` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` smallint(5) unsigned DEFAULT NULL,
  `trivia_date` datetime DEFAULT NULL,
  `active` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table trivia.rounds: ~2 rows (approximately)
/*!40000 ALTER TABLE `rounds` DISABLE KEYS */;
INSERT INTO `rounds` (`id`, `season_id`, `trivia_date`, `active`) VALUES
	(1, 1, '2020-05-24 21:33:32', 0),
	(2, 1, '2020-05-29 21:33:32', 1);
/*!40000 ALTER TABLE `rounds` ENABLE KEYS */;

-- Dumping structure for table trivia.seasons
CREATE TABLE IF NOT EXISTS `seasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table trivia.seasons: ~2 rows (approximately)
/*!40000 ALTER TABLE `seasons` DISABLE KEYS */;
INSERT INTO `seasons` (`id`, `name`, `active`) VALUES
	(1, 'Season 1', 1),
	(2, 'Season 2', 0);
/*!40000 ALTER TABLE `seasons` ENABLE KEYS */;

-- Dumping structure for table trivia.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `round_id` int(10) unsigned DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table trivia.users: ~0 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
