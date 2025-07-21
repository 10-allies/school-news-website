-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 11:00 AM
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
-- Database: `newsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_display_name` varchar(100) DEFAULT NULL,
  `email_address` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'author',
  `secret_code` varchar(100) NOT NULL,
  `nickname_updated_at` datetime DEFAULT NULL,
  `password_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `author_name`, `author_display_name`, `email_address`, `password`, `role`, `secret_code`, `nickname_updated_at`, `password_updated_at`) VALUES
(4, 'Frank IRABARUTA', 'Frank_c', 'frankirabaruta@gmail.com', '$2y$10$kbKjM9z7FN9dd.rMI/cdYenXDF1CitcBLEfhahnL4lTHjUwOGWQ16', 'author', 'author1234', '2025-04-19 22:29:58', '2025-04-19 22:29:58'),
(5, 'JOHN DOE', NULL, 'doe.john@gmail.com', '', 'author', 'author123', NULL, NULL),
(6, 'ALLIANCE', NULL, 'alliance@gmail.com', '', 'Central-admin', 'admin123', NULL, NULL),
(7, 'Admin Name', 'AdminUser', 'admin@hopehaven.com', 'admin123', 'author', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `goal_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `goal_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`goal_id`, `player_id`, `match_id`, `goal_time`) VALUES
(1, 1, 1, '00:00:34'),
(2, 1, 1, '00:00:00'),
(3, 2, 1, '00:00:52'),
(4, 3, 2, '00:00:22'),
(5, 4, 2, '00:00:00'),
(6, 4, 2, '00:00:00'),
(7, 5, 2, '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `home_team_id` int(11) DEFAULT NULL,
  `away_team_id` int(11) DEFAULT NULL,
  `match_date` datetime DEFAULT NULL,
  `home_score` int(11) DEFAULT 0,
  `away_score` int(11) DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('upcoming','completed') DEFAULT 'upcoming',
  `news_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`match_id`, `home_team_id`, `away_team_id`, `match_date`, `home_score`, `away_score`, `location`, `status`, `news_id`) VALUES
(1, 1, 2, '2025-04-20 00:00:00', 2, 1, NULL, 'completed', NULL),
(2, 3, 4, '2025-04-21 00:00:00', 3, 3, NULL, 'completed', NULL),
(3, 1, 3, '2025-04-27 00:00:00', 0, 0, NULL, 'upcoming', NULL),
(4, 2, 4, '2025-04-28 00:00:00', 0, 0, NULL, 'upcoming', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `media_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `media_url` varchar(255) NOT NULL DEFAULT 'uploads/default.jpg',
  `media_caption` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`media_id`, `news_id`, `media_type`, `media_url`, `media_caption`, `uploaded_at`) VALUES
(6, 8, 'image', '../uploads/sport_news_6808f6fd5ea5b3.34187138.png', 'Sport news media', '2025-04-23 14:19:41'),
(7, 16, 'image', 'uploads/Screenshot 2025-04-14 215326.png', NULL, '2025-04-23 19:40:05'),
(8, 16, 'image', 'uploads/Screenshot 2025-04-14 215326.png', NULL, '2025-04-23 19:40:13');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `news_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `news_title` varchar(255) NOT NULL,
  `news_content` text NOT NULL,
  `status` enum('published','archived') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_posted_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`news_id`, `category_id`, `section_id`, `author_id`, `news_title`, `news_content`, `status`, `created_at`, `updated_at`, `admin_posted_by`) VALUES
(8, 2, 1, NULL, 'Chelsea', 'nsdjhjhsa', 'published', '2025-04-23 14:19:41', '2025-04-23 14:19:41', NULL),
(13, 1, 1, 7, 'Hope Haven Wins Inter-school Football Cup', 'Hope Haven’s football team secured victory in the Inter-school Cup final with a score of 3-1.', 'published', '2025-04-23 19:26:48', '2025-04-23 19:26:48', 'AdminUser'),
(14, 1, 2, 7, 'Basketball Team Advances to Semifinals', 'Hope Haven’s basketball team defeated Green Hills School to qualify for the semifinals of the regional league.', 'published', '2025-04-23 19:27:05', '2025-04-23 19:27:05', 'AdminUser'),
(15, 1, 3, 7, 'Volleyball Girls Shine in District Match', 'The senior girls volleyball team displayed impressive teamwork and won 2-0 against St. Mary’s School.', 'published', '2025-04-23 19:27:21', '2025-04-23 19:27:21', 'AdminUser'),
(16, 1, 4, 7, 'Hope Haven Premier League Kicks Off', 'The annual Hope Haven Premier League has begun, featuring inter-class teams in a month-long football tournament.', 'published', '2025-04-23 19:27:37', '2025-04-23 19:27:37', 'AdminUser');

-- --------------------------------------------------------

--
-- Table structure for table `news_category`
--

CREATE TABLE `news_category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_category`
--

INSERT INTO `news_category` (`category_id`, `category_name`) VALUES
(1, 'All'),
(2, 'Sports'),
(3, 'Entertainment'),
(4, 'Announcement'),
(5, 'Local news');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `not_id` int(11) NOT NULL,
  `news_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`player_id`, `player_name`, `team_id`, `position`) VALUES
(1, 'John Doe', 1, NULL),
(2, 'Michael Smith', 2, NULL),
(3, 'David Johnson', 1, NULL),
(4, 'Chris Lee', 3, NULL),
(5, 'James Brown', 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `section_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `category_id`, `section_name`) VALUES
(1, 2, 'Football'),
(2, 2, 'Basketball'),
(3, 2, 'Volleyball'),
(4, 2, 'Hope Premier League');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT 'uploads/team_default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `team_name`, `logo_url`) VALUES
(1, 'Hope Lions', './images/hope_lions.png'),
(2, 'Haven Hawks', './images/haven_hawks.png'),
(3, 'Victory Eagles', './images/victory_eagles.png'),
(4, 'Faith Falcons', './images/faith_falcons.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `idx_match_id` (`match_id`),
  ADD KEY `idx_player_id` (`player_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `fk_match_news` (`news_id`),
  ADD KEY `idx_team_home_id` (`home_team_id`),
  ADD KEY `idx_team_away_id` (`away_team_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`news_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `news_category`
--
ALTER TABLE `news_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`not_id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `idx_team_id` (`team_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `news_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `news_category`
--
ALTER TABLE `news_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `not_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goals_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_match_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `news_category` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `news_ibfk_3` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE SET NULL;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `news_category` (`category_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE `user`(`id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(255) NOT NULL,`password` VARCHAR(255) NOT NULL);
