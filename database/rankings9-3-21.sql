-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.7.33 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table ffb.preseason_rankings: ~261 rows (approximately)
/*!40000 ALTER TABLE `preseason_rankings` DISABLE KEYS */;
INSERT INTO `preseason_rankings` (`id`, `player`, `position`, `adp`, `my_rank`, `team`, `bye`, `sos`, `line`, `depth`, `tier`, `points`, `proj_points`, `designation`, `data_url`, `notes`) VALUES
	(1, 'Josh Allen', 'QB', 4, 8, 'Buf', 7, 13, 14, 1, 1, 390, 381, NULL, 'josh-allen-allenjo06', NULL),
	(2, 'Kyler Murray', 'QB', 7, 7, 'Ari', 12, 18, 11, 1, 1, 375, 416, NULL, 'kyler-murray-murraky01', NULL),
	(3, 'Aaron Rodgers', 'QB', 18, 21, 'GB', 13, 29, 16, 1, 2, 381, 350, NULL, 'aaron-rodgers-rodgeaa01', NULL),
	(4, 'Patrick Mahomes', 'QB', 2, 3, 'KC', 12, 17, 7, 1, 1, 372, 399, NULL, 'patrick-mahomes-mahompa01', NULL),
	(5, 'Deshaun Watson', 'QB', 149, 180, 'Hou', 10, 7, 20, 2, 8, 366, 0, NULL, 'deshaun-watson-watsode03', NULL),
	(6, 'Russell Wilson', 'QB', 22, 26, 'Sea', 9, 32, 19, 1, 2, 356, 350, NULL, 'russell-wilson-wilsoru01', NULL),
	(7, 'Ryan Tannehill', 'QB', 39, 42, 'Ten', 13, 24, 15, 1, 3, 343, 333, 'value', 'ryan-tannehill-tannery01', NULL),
	(8, 'Tom Brady', 'QB', 34, 28, 'TB', 9, 25, 5, 1, 2, 337, 350, 'value', 'tom-brady-bradyto01', NULL),
	(9, 'Justin Herbert', 'QB', 28, 31, 'LAC', 7, 20, 18, 1, 3, 332, 338, NULL, 'justin-herbert-herbeju01', NULL),
	(10, 'Lamar Jackson', 'QB', 8, 17, 'Bal', 8, 30, 12, 1, 2, 329, 381, NULL, 'lamar-jackson-jacksla06', NULL),
	(11, 'Alvin Kamara', 'RB', 6, 4, 'NO', 6, 29, 4, 1, 1, 339, 318, NULL, 'alvin-kamara-kamaral01', NULL),
	(12, 'Derrick Henry', 'RB', 5, 5, 'Ten', 13, 10, 15, 1, 1, 322, 282, NULL, 'derrick-henry-henryde01', NULL),
	(13, 'Kirk Cousins', 'QB', 71, 75, 'Min', 7, 22, 27, 1, 4, 301, 300, 'value', 'kirk-cousins-cousiki01', NULL),
	(14, 'Dalvin Cook', 'RB', 3, 2, 'Min', 7, 12, 27, 1, 1, 313, 335, NULL, 'dalvin-cook-cookda04', NULL),
	(15, 'Davante Adams', 'WR', 11, 11, 'GB', 13, 23, 16, 1, 1, 300, 300, NULL, 'davante-adams-adamsda04', NULL),
	(16, 'Matt Ryan', 'QB', 58, 82, 'Atl', 6, 19, 24, 1, 4, 279, 301, NULL, 'matt-ryan-ryanma01', NULL),
	(17, 'Tyreek Hill', 'WR', 14, 15, 'KC', 12, 25, 7, 1, 1, 285, 298, NULL, 'tyreek-hill-hillty02', NULL),
	(18, 'Derek Carr', 'QB', 134, 104, 'LV', 8, 26, 26, 1, 5, 264, 292, NULL, 'derek-carr-carrde03', NULL),
	(19, 'Ben Roethlisberger', 'QB', 104, 96, 'Pit', 7, 4, 31, 1, 5, 266, 273, NULL, 'ben-roethlisberger-roethbe01', NULL),
	(20, 'Matthew Stafford', 'QB', 44, 54, 'LAR', 11, 14, 8, 1, 3, 260, 299, NULL, 'matthew-stafford-staffma01', NULL),
	(21, 'Cam Newton', 'QB', 201, 201, NULL, NULL, NULL, NULL, NULL, 8, 259, NULL, NULL, 'cam-newton-newtoca02', NULL),
	(22, 'Stefon Diggs', 'WR', 19, 16, 'Buf', 7, 8, 14, 1, 1, 265, 275, NULL, 'stefon-diggs-diggsst01', ''),
	(23, 'Travis Kelce', 'TE', 17, 10, 'KC', 12, 22, 7, 1, 1, 259, 278, NULL, 'travis-kelce-kelcetr01', 'round 2 target'),
	(24, 'Baker Mayfield', 'QB', 73, 91, 'Cle', 13, 27, 1, 1, 5, 244, 284, NULL, 'baker-mayfield-mayfiba01', NULL),
	(25, 'Jared Goff', 'QB', 189, 139, 'Det', 9, 31, 10, 1, 7, 236, 257, NULL, 'jared-goff-goffja01', NULL),
	(26, 'Teddy Bridgewater', 'QB', 188, 125, 'Den', 11, 2, 21, 1, 6, 238, 274, NULL, 'teddy-bridgewater-bridgte01', NULL),
	(27, 'David Montgomery', 'RB', 35, 40, 'Chi', 10, 3, 28, 1, 3, 237, 221, NULL, 'david-montgomery-montgda01', NULL),
	(28, 'Calvin Ridley', 'WR', 24, 18, 'Atl', 6, 13, 24, 1, 2, 236, 267, NULL, 'calvin-ridley-ridleca01', 'round 3 target'),
	(29, 'Aaron Jones', 'RB', 12, 9, 'GB', 13, 20, 16, 1, 2, 235, 262, NULL, 'aaron-jones-jonesaa02', NULL),
	(30, 'Jonathan Taylor', 'RB', 13, 14, 'Ind', 14, 1, 2, 1, 2, 234, 244, NULL, 'jonathan-taylor-taylojo07', NULL),
	(31, 'DeAndre Hopkins', 'WR', 27, 24, 'Ari', 12, 11, 11, 1, 2, 228, 257, NULL, 'deandre-hopkins-hopkide01', NULL),
	(32, 'Justin Jefferson', 'WR', 30, 29, 'Min', 7, 29, 27, 1, 2, 230, 245, NULL, 'justin-jefferson-jeffeju01', NULL),
	(33, 'DK Metcalf', 'WR', 26, 27, 'Sea', 9, 31, 19, 1, 2, 229, 238, NULL, 'dk-metcalf-metcadk01', NULL),
	(34, 'James Robinson', 'RB', 47, 49, 'Jax', 7, 11, 23, 1, 4, 225, 186, NULL, 'james-robinson-robinja09', NULL),
	(35, 'Darren Waller', 'TE', 32, 34, 'LV', 8, 16, 26, 1, 1, 223, 238, NULL, 'darren-waller-walleda01', NULL),
	(36, 'Adam Thielen', 'WR', 55, 64, 'Min', 7, 29, 27, 2, 5, 217, 205, 'bust', 'adam-thielen-thielad01', NULL),
	(37, 'Tyler Lockett', 'WR', 61, 48, 'Sea', 9, 31, 19, 2, 4, 216, 210, NULL, 'tyler-lockett-lockety01', NULL),
	(38, 'Josh Jacobs', 'RB', 40, 50, 'LV', 8, 28, 26, 1, 4, 213, 205, NULL, 'josh-jacobs-jacobjo05', NULL),
	(39, 'Mike Evans', 'WR', 42, 43, 'TB', 9, 21, 5, 1, 3, 214, 217, NULL, 'mike-evans-evansmi03', NULL),
	(40, 'Carson Wentz', 'QB', 157, 107, 'Ind', 14, 3, 2, 1, 6, 194, 299, NULL, 'carson-wentz-wentzca02', 'Kinda injured, should start week 1'),
	(41, 'A.J. Brown', 'WR', 29, 33, 'Ten', 13, 26, 15, 2, 3, 214, 224, NULL, 'aj-brown-brownaj01', NULL),
	(42, 'Allen Robinson II', 'WR', 43, 30, 'Chi', 10, 15, 28, 1, 2, 212, 229, NULL, 'allen-robinson-robinal04', NULL),
	(43, 'Robert Woods', 'WR', 52, 51, 'LAR', 11, 9, 8, 1, 4, 199, 205, NULL, 'robert-woods-woodsro06', NULL),
	(44, 'Nick Chubb', 'RB', 10, 12, 'Cle', 13, 2, 1, 1, 2, 199, 256, NULL, 'nick-chubb-chubbni01', NULL),
	(45, 'Kareem Hunt', 'RB', 68, 67, 'Cle', 13, 2, 1, 2, 5, 200, 176, NULL, 'kareem-hunt-huntka01', NULL),
	(46, 'Ezekiel Elliott', 'RB', 9, 6, 'Dal', 7, 27, 6, 1, 2, 193, 270, NULL, 'ezekiel-elliott-ellioez01', NULL),
	(47, 'Drew Lock', 'QB', 224, 208, 'Den', 11, 2, 21, 2, 8, 178, NULL, NULL, 'drew-lock-lockdr01', NULL),
	(48, 'Keenan Allen', 'WR', 36, 36, 'LAC', 7, 19, 18, 1, 3, 193, 228, NULL, 'keenan-allen-allenke03', NULL),
	(49, 'Brandin Cooks', 'WR', 100, 97, 'Hou', 10, 17, 20, 1, 6, 192, 185, 'value', 'brandin-cooks-cooksbr01', NULL),
	(50, 'Amari Cooper', 'WR', 53, 46, 'Dal', 7, 28, 6, 1, 4, 191, 208, 'value', 'amari-cooper-coopeam01', NULL),
	(51, 'Daniel Jones', 'QB', 153, 114, 'NYG', 10, 21, 32, 1, 6, 174, 275, 'bust', 'daniel-jones-jonesda20', NULL),
	(52, 'Marvin Jones Jr.', 'WR', 144, 135, 'Jax', 7, 5, 23, 2, 8, 190, 167, NULL, 'marvin-jones-jonesma08', NULL),
	(53, 'JuJu Smith-Schuster', 'WR', 87, 98, 'Pit', 7, 16, 31, 2, 6, 185, 179, NULL, 'juju-smithschuster-smithju03', NULL),
	(54, 'Antonio Gibson', 'RB', 21, 22, 'Was', 9, 22, 17, 1, 3, 182, 231, NULL, 'antonio-gibson-gibsoan02', NULL),
	(55, 'Chase Claypool', 'WR', 76, 58, 'Pit', 7, 16, 31, 3, 4, 183, 190, 'breakout', 'chase-claypool-claypch01', NULL),
	(56, 'Melvin Gordon III', 'RB', 82, 77, 'Den', 11, 6, 21, 1, 5, 178, 151, 'bust', 'melvin-gordon-gordome01', NULL),
	(57, 'CeeDee Lamb', 'WR', 41, 35, 'Dal', 7, 28, 6, 2, 3, 191, 215, 'breakout', 'ceedee-lamb-lambce01', NULL),
	(58, 'Terry McLaurin', 'WR', 37, 37, 'Was', 9, 7, 17, 1, 3, 179, 220, 'breakout', 'terry-mclaurin-mclaute01', NULL),
	(59, 'Kenyan Drake', 'RB', 102, 154, 'LV', 8, 28, 26, 2, 7, 179, 126, NULL, 'kenyan-drake-drakeke02', NULL),
	(60, 'Joe Burrow', 'QB', 51, 71, 'Cin', 10, 15, 25, 1, 4, 170, 290, 'bust', 'joe-burrow-burrojo02', ''),
	(61, 'DJ Moore', 'WR', 54, 57, 'Car', 13, 2, 30, 1, 4, 179, 206, NULL, 'dj-moore-mooredj02', NULL),
	(62, 'Diontae Johnson', 'WR', 67, 60, 'Pit', 7, 16, 31, 1, 4, 181, 199, NULL, 'diontae-johnson-johnsdi05', NULL),
	(63, 'Mike Davis', 'RB', 63, 62, 'Atl', 6, 26, 24, 1, 4, 176, 186, 'value', 'mike-davis-davismi08', NULL),
	(64, 'Robby Anderson', 'WR', 94, 84, 'Car', 13, 2, 30, 2, 5, 176, 179, 'value', 'robby-anderson-anderro05', NULL),
	(65, 'Curtis Samuel', 'WR', 143, 95, 'Was', 9, 7, 17, 2, 6, 174, 173, NULL, 'curtis-samuel-samuecu01', 'Should play week 1 (hasn\'t been practicing)'),
	(66, 'Ronald Jones II', 'RB', 88, 86, 'TB', 9, 32, 5, 2, 5, 170, 164, NULL, 'ronald-jones-jonesro13', NULL),
	(67, 'Chris Carson', 'RB', 38, 39, 'Sea', 9, 16, 19, 1, 3, 169, 219, NULL, 'chris-carson-carsoch01', NULL),
	(68, 'D\'Andre Swift', 'RB', 46, 63, 'Det', 9, 25, 10, 1, 4, 165, 214, NULL, 'dandre-swift-swiftda01', 'Has been missing practice with groin injury'),
	(69, 'Cole Beasley', 'WR', 173, 119, 'Buf', 7, 8, 14, 3, 7, 167, 164, NULL, 'cole-beasley-beaslco01', NULL),
	(71, 'David Johnson', 'RB', 107, 131, 'Hou', 10, 19, 20, 2, 7, 162, 121, 'bust', 'david-johnson-johnsda16', NULL),
	(72, 'Cooper Kupp', 'WR', 57, 52, 'LAR', 11, 9, 8, 2, 4, 164, 206, NULL, 'cooper-kupp-kuppco01', NULL),
	(73, 'Will Fuller V', 'WR', 117, 112, 'Mia', 14, 14, 29, 2, 7, 163, 171, NULL, 'will-fuller-fullewi02', 'Suspended for 1 game'),
	(75, 'Nyheim Hines', 'RB', 125, 140, 'Ind', 14, 1, 2, 3, 7, 180, 144, 'bust', 'nyheim-hines-hinesny01', NULL),
	(76, 'Nelson Agholor', 'WR', 194, 133, 'NE', 14, 1, 3, 1, 7, 162, 153, 'sleeper', 'nelson-agholor-agholne01', NULL),
	(77, 'Ryan Fitzpatrick', 'QB', 108, 90, 'Was', 9, 11, 17, 1, 5, 153, 286, 'sleeper', 'ryan-fitzpatrick-fitzpry01', NULL),
	(78, 'Tee Higgins', 'WR', 70, 65, 'Cin', 10, 24, 25, 2, 5, 160, 183, NULL, 'tee-higgins-higgite01', NULL),
	(79, 'J.K. Dobbins', 'RB', NULL, 261, 'Bal', 8, 17, 12, 4, NULL, 160, 0, NULL, 'jk-dobbins-dobbijk01', 'Out for season'),
	(80, 'Corey Davis', 'WR', 113, 89, 'NYJ', 6, 4, 22, 1, 5, 158, 183, NULL, 'corey-davis-davisco05', NULL),
	(81, 'Chris Godwin', 'WR', 48, 44, 'TB', 9, 21, 5, 2, 4, 159, 206, NULL, 'chris-godwin-godwich01', NULL),
	(82, 'Clyde Edwards-Helaire', 'RB', 31, 32, 'KC', 12, 14, 7, 1, 3, 158, 222, 'value', 'clyde-edwardshelaire-edwarcl02', NULL),
	(83, 'Miles Sanders', 'RB', 50, 56, 'Phi', 14, 23, 13, 1, 4, 154, 193, NULL, 'miles-sanders-sandemi01', NULL),
	(84, 'Taysom Hill', 'QB', 183, 174, 'NO', 6, 6, 4, 2, 7, 148, NULL, NULL, 'taysom-hill-hillta01', ''),
	(85, 'Brandon Aiyuk', 'WR', 66, 59, 'SF', 6, 6, 9, 1, 4, 156, 195, NULL, 'brandon-aiyuk-aiyukbr01', NULL),
	(86, 'Marquise Brown', 'WR', 160, 155, 'Bal', 8, 22, 12, 1, 8, 156, 142, 'bust', 'marquise-brown-brownma18', NULL),
	(87, 'Tyler Boyd', 'WR', 98, 103, 'Cin', 10, 24, 25, 1, 6, 153, 168, NULL, 'tyler-boyd-boydty01', NULL),
	(88, 'Jarvis Landry', 'WR', 115, 136, 'Cle', 13, 30, 1, 2, 8, 152, 148, NULL, 'jarvis-landry-landrja02', NULL),
	(89, 'J.D. McKissic', 'RB', 136, 200, 'Was', 9, 22, 17, 2, 8, 150, 116, NULL, 'jd-mckissic-mckisjd01', NULL),
	(90, 'Todd Gurley II', 'RB', 260, 254, NULL, NULL, NULL, NULL, NULL, NULL, 151, NULL, NULL, 'todd-gurley-gurleto02', NULL),
	(91, 'Robert Tonyan', 'TE', 123, 101, 'GB', 13, 21, 16, 1, 3, 151, 148, NULL, 'robert-tonyan-tonyaro01', NULL),
	(92, 'James Conner', 'RB', 92, 93, 'Ari', 12, 9, 11, 2, 6, 147, 158, 'value', 'james-conner-conneja02', NULL),
	(93, 'Russell Gage', 'WR', 192, 127, 'Atl', 6, 13, 24, 2, 7, 145, 155, 'sleeper', 'russell-gage-gageru01', NULL),
	(94, 'Sam Darnold', 'QB', 163, 129, 'Car', 13, 16, 30, 1, 7, 132, 263, NULL, 'sam-darnold-darnosa01', NULL),
	(95, 'Andy Dalton', 'QB', 252, 232, 'Chi', 10, 28, 28, 1, 8, 135, 60, NULL, 'andy-dalton-daltoan02', NULL),
	(96, 'Michael Gallup', 'WR', 139, 152, 'Dal', 7, 28, 6, 3, 8, 144, 148, NULL, 'michael-gallup-gallumi01', NULL),
	(97, 'Myles Gaskin', 'RB', 64, 55, 'Mia', 14, 13, 29, 1, 4, 142, 175, NULL, 'myles-gaskin-gaskimy01', NULL),
	(98, 'Jamison Crowder', 'WR', 223, 207, 'NYJ', 6, 4, 22, 2, NULL, 143, 106, NULL, 'jamison-crowder-crowdja01', 'Tweaked groin'),
	(99, 'T.J. Hockenson', 'TE', 80, 80, 'Det', 9, 25, 10, 1, 2, 141, 173, 'breakout', 'tj-hockenson-hocketj01', 'Slightly injured'),
	(100, 'Chase Edmonds', 'RB', 74, 79, 'Ari', 12, 9, 11, 1, 5, 162, 185, NULL, 'chase-edmonds-edmonch02', NULL),
	(101, 'Mark Andrews', 'TE', 69, 70, 'Bal', 8, 20, 12, 1, 2, 141, 173, NULL, 'mark-andrews-andrema01', NULL),
	(102, 'Logan Thomas', 'TE', 110, 158, 'Was', 9, 11, 17, 1, 4, 141, 130, 'bust', 'logan-thomas-thomalo01', NULL),
	(103, 'Tua Tagovailoa', 'QB', 84, 88, 'Mia', 14, 5, 29, 1, 5, 134, 284, NULL, 'tua-tagovailoa-tagovtu01', NULL),
	(104, 'Dak Prescott', 'QB', 16, 25, 'Dal', 7, 23, 6, 1, 2, 132, 350, NULL, 'dak-prescott-prescda01', NULL),
	(105, 'Austin Ekeler', 'RB', 23, 13, 'LAC', 7, 8, 18, 1, 2, 138, 246, NULL, 'austin-ekeler-ekeleau01', NULL),
	(106, 'T.Y. Hilton', 'WR', 203, 198, 'Ind', 14, 10, 2, 3, NULL, 136, 118, NULL, 'ty-hilton-hiltoty01', 'IR to start season'),
	(107, 'Jeff Wilson Jr.', 'RB', 279, 215, 'SF', 6, 4, 9, 3, NULL, 134, 84, NULL, 'jeffery-wilson-wilsoje05', 'Will miss most of season with injury'),
	(108, 'Tim Patrick', 'WR', 314, 216, 'Den', 11, 18, 21, 4, NULL, 136, 118, NULL, 'tim-patrick-patriti01', NULL),
	(109, 'DeVante Parker', 'WR', 174, 156, 'Mia', 14, 14, 29, 1, 8, 135, 131, NULL, 'devante-parker-parkede02', NULL),
	(110, 'Giovani Bernard', 'RB', 152, 222, 'TB', 9, 32, 5, 3, NULL, 136, 77, NULL, 'giovani-bernard-bernagi01', NULL),
	(111, 'Emmanuel Sanders', 'WR', 236, 161, 'Buf', 7, 8, 14, 2, 8, 134, 127, NULL, 'emmanuel-sanders-sandeem01', NULL),
	(112, 'Mike Gesicki', 'TE', 176, 122, 'Mia', 14, 2, 29, 1, 3, 133, 145, NULL, 'mike-gesicki-gesicmi01', NULL),
	(113, 'Jerry Jeudy', 'WR', 81, 92, 'Den', 11, 18, 21, 2, 6, 132, 182, NULL, 'jerry-jeudy-jeudyje01', NULL),
	(114, 'Mike Williams', 'WR', 122, 111, 'LAC', 7, 19, 18, 2, 7, 130, 161, NULL, 'mike-williams-willimi21', NULL),
	(115, 'Sterling Shepard', 'WR', 213, 137, 'NYG', 10, 20, 32, 2, 8, 130, 144, NULL, 'sterling-shepard-shepast01', NULL),
	(116, 'Laviska Shenault Jr.', 'WR', 121, 110, 'Jax', 7, 5, 23, 3, 7, 128, 174, NULL, 'laviska-shenault-shenala01', NULL),
	(117, 'Keelan Cole Sr.', 'WR', 334, 250, 'NYJ', 6, 4, 22, 4, NULL, 139, NULL, NULL, 'keelan-cole-coleke01', NULL),
	(118, 'DJ Chark Jr.', 'WR', 101, 116, 'Jax', 7, 5, 23, 1, 7, 127, 131, 'bust', 'dj-chark-charkdj01', NULL),
	(119, 'Rob Gronkowski', 'TE', 170, 206, 'TB', 9, 5, 5, 1, 5, 127, 127, NULL, 'rob-gronkowski-gronkro01', NULL),
	(120, 'Wayne Gallman Jr.', 'RB', 328, 251, NULL, NULL, NULL, NULL, NULL, NULL, 126, NULL, NULL, 'wayne-gallman-gallmwa01', NULL),
	(121, 'Latavius Murray', 'RB', 130, 147, 'NO', 6, 29, 4, 2, 7, 125, 135, NULL, 'latavius-murray-murrala01', NULL),
	(122, 'Devin Singletary', 'RB', 105, 126, 'Buf', 7, 24, 14, 1, 6, 124, 137, NULL, 'devin-singletary-singlde01', NULL),
	(123, 'Gus Edwards', 'RB', 56, 45, 'Bal', 8, 17, 12, 1, 4, 123, 191, 'value', 'gus-edwards-edwargu01', ''),
	(124, 'Christian Kirk', 'WR', 273, 159, 'Ari', 12, 11, 11, 3, 8, 130, 124, NULL, 'christian-kirk-kirkch01', NULL),
	(125, 'Darrell Henderson Jr.', 'RB', 59, 61, 'LAR', 11, 5, 8, 1, 4, 122, 187, NULL, 'darrell-henderson-hendeda01', NULL),
	(126, 'Darnell Mooney', 'WR', 168, 113, 'Chi', 10, 15, 28, 2, 7, 122, 160, 'sleeper', 'darnell-mooney-mooneda01', NULL),
	(127, 'Hayden Hurst', 'TE', 280, 226, 'Atl', 6, 10, 24, 2, NULL, 121, 70, NULL, 'hayden-hurst-hurstha01', NULL),
	(128, 'Marquez Valdes-Scantling', 'WR', 264, 189, 'GB', 13, 23, 16, 2, NULL, 120, 89, NULL, 'marquez-valdesscantling-valdema01', NULL),
	(129, 'Julio Jones', 'WR', 49, 47, 'Ten', 13, 26, 15, 1, 4, 121, 207, NULL, 'julio-jones-jonesju05', NULL),
	(130, 'Jonnu Smith', 'TE', 171, 221, 'NE', 14, 1, 3, 1, NULL, 120, 122, NULL, 'jonnu-smith-smithjo13', NULL),
	(131, 'Gabriel Davis', 'WR', 210, 170, 'Buf', 7, 8, 14, 4, 8, 119, 137, NULL, 'gabriel-davis-davisga05', NULL),
	(132, 'Jimmy Graham', 'TE', 315, 227, 'Chi', 10, 30, 28, 1, NULL, 119, 73, NULL, 'jimmy-graham-grahaji01', NULL),
	(133, 'Adrian Peterson', 'RB', NULL, 255, NULL, NULL, NULL, NULL, NULL, NULL, 119, NULL, NULL, 'adrian-peterson-peterad02', NULL),
	(134, 'Noah Fant', 'TE', 119, 142, 'Den', 11, 3, 21, 1, 4, 118, 133, 'value', 'noah-fant-fantno01', NULL),
	(135, 'Darius Slayton', 'WR', 305, 224, 'NYG', 10, 20, 32, 3, NULL, 115, 105, NULL, 'darius-slayton-slaytda01', NULL),
	(136, 'Hunter Henry', 'TE', 197, 223, 'NE', 14, 1, 3, 2, NULL, 115, 99, 'bust', 'hunter-henry-henryhu01', NULL),
	(137, 'Dalton Schultz', 'TE', 368, 247, 'Dal', 7, 18, 6, 2, NULL, 114, 60, NULL, 'dalton-schultz-schulda01', NULL),
	(138, 'Zach Pascal', 'WR', 326, 231, 'Ind', 14, 10, 2, 2, NULL, 116, NULL, NULL, 'zach-pascal-pascaza01', NULL),
	(139, 'Leonard Fournette', 'RB', 90, 115, 'TB', 9, 32, 5, 1, 6, 114, 136, NULL, 'leonard-fournette-fournle01', NULL),
	(140, 'Eric Ebron', 'TE', 282, 244, 'Pit', 7, 4, 31, 1, NULL, 113, 104, NULL, 'eric-ebron-ebroner01', NULL),
	(141, 'Jakobi Meyers', 'WR', 175, 123, 'NE', 14, 1, 3, 2, 7, 112, 157, NULL, 'jakobi-meyers-meyerja01', NULL),
	(142, 'Jalen Hurts', 'QB', 45, 38, 'Phi', 14, 9, 13, 1, 3, 107, 343, 'breakout', 'jalen-hurts-hurtsja01', NULL),
	(143, 'Nick Foles', 'QB', NULL, 258, 'Chi', 10, 28, 28, 3, 8, 104, NULL, NULL, 'nick-foles-folesni01', NULL),
	(144, 'Jamaal Williams', 'RB', 109, 146, 'Det', 9, 25, 10, 2, 7, 113, 140, NULL, 'jamaal-williams-willija32', NULL),
	(145, 'Jerick McKinnon', 'RB', 256, 248, 'KC', 12, 14, 7, 3, NULL, 122, NULL, NULL, 'jerick-mckinnon-mckinje02', NULL),
	(146, 'Evan Engram', 'TE', 196, 163, 'NYG', 10, 9, 32, 1, 5, 109, 125, 'value', 'evan-engram-engraev01', NULL),
	(147, 'Jared Cook', 'TE', 199, 214, 'LAC', 7, 23, 18, 1, 5, 108, 93, NULL, 'jared-cook-cookja02', NULL),
	(148, 'Mecole Hardman', 'WR', 161, 153, 'KC', 12, 25, 7, 2, 8, 124, 134, NULL, 'mecole-hardman-hardmme01', NULL),
	(149, 'Tony Pollard', 'RB', 116, 164, 'Dal', 7, 27, 6, 2, 7, 145, 121, NULL, 'tony-pollard-pollato02', NULL),
	(150, 'Greg Ward', 'WR', NULL, 242, 'Phi', 14, 12, 13, 4, NULL, 113, NULL, NULL, 'greg-ward-wardgr01', NULL),
	(151, 'Tyler Higbee', 'TE', 142, 99, 'LAR', 11, 31, 8, 1, 3, 106, 149, 'value', 'tyler-higbee-higbety01', NULL),
	(152, 'Kendrick Bourne', 'WR', 333, 233, 'NE', 14, 1, 3, 3, NULL, 105, 82, NULL, 'kendrick-bourne-bournke01', NULL),
	(153, 'Hunter Renfrow', 'WR', 287, 217, 'LV', 8, 32, 26, 2, NULL, 116, 106, NULL, 'hunter-renfrow-renfrhu01', NULL),
	(156, 'George Kittle', 'TE', 33, 41, 'SF', 6, 19, 9, 1, 1, 101, 223, NULL, 'george-kittle-kittlge01', NULL),
	(157, 'Josh Reynolds', 'WR', 308, 236, 'Ten', 13, 26, 15, 3, NULL, 97, 104, NULL, 'josh-reynolds-reynojo03', NULL),
	(158, 'Malcolm Brown', 'RB', 169, 148, 'Mia', 14, 13, 29, 2, 7, 97, 118, NULL, 'malcolm-brown-brownma16', NULL),
	(160, 'Cam Akers', 'RB', NULL, 263, 'LAR', 11, 5, 8, NULL, NULL, 95, 0, NULL, 'cam-akers-akersca01', 'Out for season'),
	(162, 'Zack Moss', 'RB', 93, 128, 'Buf', 7, 24, 14, 2, 7, 95, 137, NULL, 'zack-moss-mossza01', NULL),
	(163, 'Antonio Brown', 'WR', 103, 83, 'TB', 9, 21, 5, 3, 5, 95, 175, 'value', 'antonio-brown-brownan05', NULL),
	(164, 'Dallas Goedert', 'TE', 133, 130, 'Phi', 14, 13, 13, 1, 4, 93, 139, NULL, 'dallas-goedert-goededa01', NULL),
	(165, 'James White', 'RB', 148, 166, 'NE', 14, 21, 3, 2, 8, 92, 112, NULL, 'james-white-whiteja06', NULL),
	(166, 'Raheem Mostert', 'RB', 79, 66, 'SF', 6, 4, 9, 1, 5, 91, 179, NULL, 'raheem-mostert-mostera01', NULL),
	(167, 'Austin Hooper', 'TE', 231, 219, 'Cle', 13, 28, 1, 1, NULL, 91, 112, NULL, 'austin-hooper-hoopeau01', NULL),
	(168, 'Joe Mixon', 'RB', 25, 23, 'Cin', 10, 15, 25, 1, 3, 88, 244, 'breakout', 'joe-mixon-mixonjo01', NULL),
	(169, 'Damien Harris', 'RB', 62, 53, 'NE', 14, 21, 3, 1, 4, 89, 164, 'sleeper', 'damien-harris-harrida11', NULL),
	(170, 'A.J. Green', 'WR', 221, 212, 'Ari', 12, 11, 11, 2, NULL, 88, 143, NULL, 'aj-green-greenaj02', NULL),
	(171, 'Tre\'Quan Smith', 'WR', 234, 187, 'NO', 6, 3, 4, 2, NULL, 86, 119, NULL, 'trequan-smith-smithtr09', NULL),
	(175, 'Anthony Miller', 'WR', 318, 238, 'Chi', 10, 15, 28, 3, NULL, 87, 77, NULL, 'anthony-miller-millean04', NULL),
	(177, 'Breshad Perriman', 'WR', 266, 211, 'Chi', 10, 15, 28, 3, NULL, 84, NULL, NULL, 'breshad-perriman-perribr02', ''),
	(178, 'Irv Smith Jr.', 'TE', 242, 260, 'Min', 7, 24, 27, 1, NULL, 84, 0, NULL, 'irv-smith-smithir03', 'Out for the season'),
	(180, 'Boston Scott', 'RB', 232, 225, 'Phi', 14, 23, 13, 2, NULL, 113, 78, NULL, 'boston-scott-scottbo04', NULL),
	(181, 'Jalen Guyton', 'WR', NULL, 245, 'LAC', 7, 19, 18, 4, NULL, 83, NULL, NULL, 'jalen-guyton-guytoja01', 'Possibly injured'),
	(183, 'Christian McCaffrey', 'RB', 1, 1, 'Car', 13, 30, 30, 1, 1, 82, 349, NULL, 'christian-mccaffrey-mccafch01', NULL),
	(184, 'Dan Arnold', 'TE', 332, 246, 'Car', 13, 7, 30, 1, NULL, 80, 66, NULL, 'dan-arnold-arnolda03', NULL),
	(185, 'Allen Lazard', 'WR', 283, 175, 'GB', 13, 23, 16, 3, 8, 81, 88, NULL, 'allen-lazard-lazaral01', NULL),
	(186, 'Randall Cobb', 'WR', 249, 210, 'GB', 13, 23, 16, 4, NULL, 81, 127, NULL, 'randall-cobb-cobbra01', NULL),
	(187, 'Alexander Mattison', 'RB', 131, 176, 'Min', 7, 12, 27, 2, 8, 80, 79, NULL, 'alexander-mattison-mattial01', NULL),
	(188, 'John Brown', 'WR', 255, 237, NULL, NULL, NULL, NULL, NULL, NULL, 80, NULL, NULL, 'john-brown-brownjo10', NULL),
	(189, 'Anthony McFarland', 'RB', NULL, 239, 'Pit', 7, 7, 31, 3, NULL, NULL, NULL, NULL, NULL, 'IR'),
	(190, 'Michael Pittman Jr.', 'WR', 127, 102, 'Ind', 14, 10, 2, 1, 6, 79, 172, NULL, 'michael-pittman-pittmmi02', NULL),
	(191, 'Braxton Berrios', 'WR', NULL, 252, 'NYJ', 6, 4, 22, 5, NULL, 91, NULL, NULL, 'braxton-berrios-berribr01', NULL),
	(192, 'Willie Snead IV', 'WR', NULL, 253, 'LV', 8, 32, 26, 4, NULL, 78, NULL, NULL, 'willie-snead-sneadwi02', NULL),
	(193, 'Devontae Booker', 'RB', 208, 241, 'NYG', 10, 31, 32, 2, NULL, 77, 54, NULL, 'devontae-booker-bookede02', NULL),
	(195, 'Carlos Hyde', 'RB', 172, 202, 'Jax', 7, 11, 23, 2, 8, 77, 84, NULL, 'carlos-hyde-hydeca01', NULL),
	(196, 'Odell Beckham Jr.', 'WR', 72, 73, 'Cle', 13, 30, 1, 1, 5, 75, 190, NULL, 'odell-beckham-beckhod01', NULL),
	(198, 'KJ Hamler', 'WR', 289, 234, 'Den', 11, 18, 21, 3, NULL, 78, 86, NULL, 'kj-hamler-hamlekj01', NULL),
	(199, 'Larry Fitzgerald', 'WR', NULL, 259, NULL, NULL, NULL, NULL, NULL, NULL, 74, NULL, NULL, 'larry-fitzgerald-fitzgla01', NULL),
	(200, 'Gerald Everett', 'TE', 207, 191, 'Sea', 9, 32, 19, 1, 5, 71, 113, 'sleeper', 'gerald-everett-everege01', NULL),
	(256, 'Saquon Barkley', 'RB', 15, 20, 'NYG', 10, 31, 32, 1, 2, 12, 256, NULL, 'saquon-barkley-barklsa01', 'Might miss week 1'),
	(257, 'Michael Thomas', 'WR', 85, 105, 'NO', 6, 3, 4, 1, 7, 64, 154, NULL, 'michael-thomas-thomami05', 'IR for first 8 weeks'),
	(258, 'Trevor Lawrence', 'QB', 60, 72, 'Jax', 7, 8, 23, 1, 4, NULL, 295, 'sleeper', NULL, NULL),
	(259, 'Kyle Pitts', 'TE', 65, 76, 'Atl', 6, 10, 24, 1, 2, NULL, 171, 'breakout', 'kyle-pitts-pittsky01', NULL),
	(260, 'Najee Harris', 'RB', 20, 19, 'Pit', 7, 7, 31, 1, 2, NULL, 241, 'breakout', 'najee-harris-harrina05', NULL),
	(261, 'Ja\'marr Chase', 'WR', 77, 69, 'Cin', 10, 24, 25, 3, 5, NULL, 172, NULL, 'jamarr-chase-chaseja01', NULL),
	(262, 'Zach Wilson', 'QB', 155, 106, 'NYJ', 6, 12, 22, 1, 6, NULL, 269, NULL, NULL, NULL),
	(263, 'Justin Fields', 'QB', 86, 108, 'Chi', 10, 28, 28, 2, 6, NULL, 247, NULL, 'justin-fields-fieldju01', NULL),
	(264, 'Trey Lance', 'QB', 95, 74, 'SF', 6, 10, 9, 2, 4, NULL, 225, 'breakout', NULL, 'Questionable tag to start season'),
	(265, 'Mac Jones', 'QB', 158, 117, 'NE', 14, 1, 3, 1, 6, NULL, 249, NULL, NULL, NULL),
	(266, 'Travis Etienne', 'RB', NULL, 262, 'Jax', 7, 11, 23, NULL, NULL, NULL, 0, NULL, NULL, 'OUT FOR SEASON (foot injury)'),
	(267, 'Jaylen Waddle', 'WR', 129, 118, 'Mia', 14, 14, 29, 3, 7, NULL, 126, NULL, 'jaylen-waddle-waddlja01', NULL),
	(268, 'DeVonta Smith', 'WR', 91, 87, 'Phi', 14, 12, 13, 1, 5, NULL, 172, 'breakout', 'devonta-smith-smithde16', NULL),
	(269, 'Harrison Butker', 'K', 140, 168, 'KC', 12, 3, 7, 1, NULL, 134, 142, NULL, NULL, NULL),
	(270, 'Justin Tucker', 'K', 138, 162, 'Bal', 8, 14, 12, 1, NULL, 143, 152, NULL, NULL, NULL),
	(271, 'Ryan Succop', 'K', 164, 182, 'TB', 9, 27, 5, 1, NULL, 145, 150, NULL, NULL, NULL),
	(272, 'Greg Zuerlein', 'K', 145, 177, 'Dal', 7, 2, 6, 1, NULL, 153, 160, NULL, NULL, NULL),
	(273, 'Wil Lutz', 'K', 165, 257, 'NO', 6, 28, 4, 1, NULL, 131, 100, NULL, NULL, NULL),
	(274, 'Matt Gay', 'K', 218, 190, 'LAR', 11, 10, 8, 1, NULL, 64, 135, NULL, NULL, NULL),
	(275, 'Tyler Bass', 'K', 147, 185, 'Buf', 7, 32, 14, 1, NULL, 151, 136, NULL, NULL, NULL),
	(276, 'Jason Sanders', 'K', 146, 181, 'Mia', 14, 24, 29, 1, NULL, 172, 148, NULL, NULL, NULL),
	(277, 'Mason Crosby', 'K', 220, 192, 'GB', 13, 29, 16, 1, NULL, 120, 135, NULL, NULL, NULL),
	(278, 'Younghoe Koo', 'K', 141, 173, 'Atl', 6, 13, 24, 1, NULL, 166, 155, NULL, NULL, NULL),
	(279, 'Jason Myers', 'K', 198, 188, 'Sea', 9, 15, 19, 1, NULL, 138, 142, NULL, NULL, NULL),
	(281, 'LA Rams', 'DEF', 111, 121, 'LAR', 11, 18, 8, NULL, NULL, 208, 195, NULL, NULL, ''),
	(282, 'Washington', 'DEF', 126, 134, 'Was', 9, 7, 17, NULL, NULL, 159, 180, NULL, NULL, NULL),
	(283, 'Pittsburgh', 'DEF', 112, 124, 'Pit', 7, 3, 31, NULL, NULL, 176, 190, 'value', NULL, NULL),
	(284, 'Baltimore', 'DEF', 124, 138, 'Bal', 8, 17, 12, NULL, NULL, 170, 170, NULL, NULL, NULL),
	(285, 'San Francisco', 'DEF', 128, 145, 'SF', 6, 13, 9, NULL, NULL, 120, 165, NULL, NULL, NULL),
	(286, 'Tampa Bay', 'DEF', 114, 197, 'TB', 9, 32, 5, NULL, NULL, 132, 120, 'bust', NULL, NULL),
	(287, 'Indianapolis', 'DEF', 132, 149, 'Ind', 14, 6, 2, NULL, NULL, 156, 160, NULL, NULL, NULL),
	(288, 'New England', 'DEF', 154, 151, 'NE', 14, 2, 3, NULL, NULL, 118, 140, NULL, NULL, NULL),
	(289, 'New Orleans', 'DEF', 181, 195, 'NO', 6, 21, 4, NULL, NULL, 144, 135, NULL, NULL, NULL),
	(290, 'Miami', 'DEF', 195, 160, 'Mia', 14, 11, 29, NULL, NULL, 140, 140, NULL, NULL, NULL),
	(291, 'Buffalo', 'DEF', 135, 209, 'Buf', 7, 26, 14, NULL, NULL, 106, 115, NULL, NULL, NULL),
	(292, 'LA Chargers', 'DEF', 206, 167, 'LAC', 7, 9, 18, NULL, NULL, 70, 105, NULL, NULL, NULL),
	(293, 'Denver', 'DEF', 191, 165, 'Den', 11, 1, 21, NULL, NULL, 59, 100, NULL, NULL, NULL),
	(294, 'Kenny Golladay', 'WR', 78, 68, 'NYG', 10, 20, 32, 1, 5, 56, 186, 'bust', 'kenny-golladay-gollake01', NULL),
	(295, 'Courtland Sutton', 'WR', 89, 78, 'Den', 11, 18, 21, 1, 5, 8, 175, NULL, 'courtland-sutton-suttoco01', NULL),
	(296, 'Deebo Samuel', 'WR', 99, 85, 'SF', 6, 6, 9, 2, 5, 64, 185, NULL, 'deebo-samuel-samuede01', NULL),
	(297, 'Michael Carter', 'RB', 97, 109, 'NYJ', 6, 18, 22, 3, 6, NULL, 126, NULL, 'michael-carter-cartemi06', NULL),
	(298, 'Denzel Mims', 'WR', 313, 203, 'NYJ', 6, 4, 22, 6, 0, 49, 50, NULL, 'denzel-mims-mimsde01', NULL),
	(299, 'Henry Ruggs', 'WR', 156, 132, 'LV', 8, 32, 26, 1, 7, 76, 145, 'sleeper', 'henry-ruggs-ruggshe01', NULL),
	(300, 'Tyrell Williams', 'WR', 235, 157, 'Det', 9, 27, 10, 1, 8, NULL, 143, NULL, 'tyrell-williams-willity04', NULL),
	(301, 'AJ Dillon', 'RB', 96, 120, 'GB', 13, 20, 16, 2, 6, 39, 141, 'sleeper', 'aj-dillon-dilloaj01', NULL),
	(302, 'Javonte Williams', 'RB', 75, 81, 'Den', 11, 6, 21, 2, 5, NULL, 146, 'sleeper', NULL, NULL),
	(303, 'Jalen Reagor', 'WR', 211, 178, 'Phi', 14, 12, 13, 2, 8, 76, 125, NULL, 'jalen-reagor-reagoja01', NULL),
	(304, 'Rondale Moore', 'WR', 217, 213, 'Ari', 12, 11, 11, 4, NULL, NULL, 115, NULL, 'rondale-moore-moorero09', NULL),
	(305, 'Blake Jarwin', 'TE', 214, 183, 'Dal', 7, 18, 6, 1, 5, 2, 113, 'sleeper', 'blake-jarwin-jarwibl01', NULL),
	(306, 'Bryan Edwards', 'WR', 237, 235, 'LV', 8, 32, 26, 3, NULL, 31, 120, NULL, 'bryan-edwards-edwarbr05', NULL),
	(307, 'Rashod Bateman', 'WR', 216, 194, 'Bal', 8, 22, 12, 2, 0, NULL, 103, NULL, 'rashod-bateman-batemra01', 'IR to start season (3 weeks)'),
	(308, 'Terrace Marshall', 'WR', 241, 143, 'Car', 13, 2, 30, 3, 8, NULL, 114, NULL, 'terrace-marshall-marshte01', NULL),
	(309, 'Trey Sermon', 'RB', 83, 100, 'SF', 6, 4, 9, 2, 6, NULL, 136, NULL, NULL, NULL),
	(310, 'Elijah Moore', 'WR', 184, 144, 'NYJ', 6, 4, 22, 3, 8, NULL, 124, NULL, NULL, NULL),
	(311, 'Damien Williams', 'RB', 186, 186, 'Chi', 10, 3, 28, 2, 8, NULL, 103, NULL, 'damien-williams-willida20', NULL),
	(312, 'Adam Trautman', 'TE', 244, 196, 'NO', 6, 6, 4, 1, 5, NULL, 79, NULL, 'adam-trautman-trautad01', NULL),
	(313, 'Justin Jackson', 'RB', 212, 179, 'LAC', 7, 8, 18, 2, 8, 56, 108, NULL, 'justin-jackson-jacksju03', NULL),
	(315, 'Jameis Winston', 'QB', 120, 94, 'NO', 6, 6, 4, 1, 5, NULL, 274, NULL, 'jameis-winston-winstja01', NULL),
	(316, 'Chuba Hubbard', 'RB', 166, 218, 'Car', 13, 30, 30, 2, NULL, NULL, 68, NULL, 'chuba-hubbard-hubbach02', NULL),
	(317, 'Tyrod Taylor', 'QB', 226, 141, 'Hou', 10, 7, 20, 1, 7, NULL, 255, NULL, 'tyrod-taylor-tayloty02', NULL),
	(318, 'Phillip Lindsay', 'RB', 118, 171, 'Hou', 10, 19, 20, 3, 8, NULL, 107, NULL, NULL, NULL),
	(319, 'Tarik Cohen', 'RB', 200, 243, 'Chi', 10, 3, 28, 3, NULL, NULL, 56, NULL, NULL, 'IR to start season (6 weeks)'),
	(320, 'Le\'Veon Bell', 'RB', NULL, 256, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(321, 'Tennessee', 'DEF', 204, 240, 'Ten', 13, 27, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(322, 'Nick Folk', 'K', 276, 205, 'NE', 14, 1, 3, 1, NULL, 126, 125, NULL, NULL, NULL),
	(323, 'Rodrigo Blankenship', 'K', 150, 199, 'Ind', 14, 3, 2, 1, NULL, 151, 142, NULL, NULL, NULL),
	(324, 'Jimmy Garoppolo', 'QB', 215, 184, 'SF', 6, 10, 9, 1, 8, NULL, 109, NULL, NULL, NULL),
	(325, 'Rashaad Penny', 'RB', 151, 204, 'Sea', 9, 16, 19, 2, NULL, NULL, 80, NULL, NULL, NULL),
	(326, 'Lamical Perine', 'RB', NULL, 249, 'NYJ', 6, 18, 22, 2, NULL, NULL, NULL, NULL, NULL, NULL),
	(327, 'Tevin Coleman', 'RB', 137, 169, 'NYJ', 6, 18, 22, 1, 8, NULL, 109, NULL, NULL, NULL),
	(329, 'Sony Michel', 'RB', 106, 150, 'LAR', 11, 5, 8, 2, 7, NULL, 118, NULL, NULL, NULL),
	(330, 'Mark Ingram', 'RB', 190, 172, 'Hou', 10, 19, 20, 1, 8, NULL, 70, NULL, NULL, NULL),
	(331, 'Cleveland', 'DEF', 180, 193, 'Cle', 13, NULL, NULL, NULL, NULL, NULL, 135, NULL, NULL, NULL),
	(332, 'Marquez Callaway', 'WR', 155, 155, 'NO', 6, 3, 4, 3, NULL, NULL, NULL, NULL, NULL, 'draft stock rising, taken #94 overall?? what the heck?'),
	(333, 'Marlon Mack', 'RB', 220, 228, 'Ind', 14, 1, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL),
	(334, 'Kenneth Gainwell', 'RB', 225, 229, 'Phi', 14, 23, 13, 2, NULL, NULL, NULL, NULL, NULL, NULL),
	(335, 'Benny Snell', 'RB', 230, 230, 'Pit', 7, 7, 31, 2, NULL, NULL, NULL, NULL, NULL, NULL),
	(336, 'Rhamondre Stevenson', 'RB', 158, 189, 'NE', 14, 21, 3, 3, NULL, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `preseason_rankings` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;