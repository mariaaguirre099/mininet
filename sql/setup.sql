-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 24, 2025 at 12:57 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `mininet`
--

-- --------------------------------------------------------

--
-- Table structure for table `MessageParticipants`
--

CREATE TABLE `MessageParticipants` (
  `UserSenderID` int UNSIGNED NOT NULL,
  `UserReceiverID` int UNSIGNED NOT NULL,
  `MessageID` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `MessageParticipants`
--

INSERT INTO `MessageParticipants` (`UserSenderID`, `UserReceiverID`, `MessageID`) VALUES
(5, 4, 4),
(13, 8, 14),
(13, 8, 15);

-- --------------------------------------------------------

--
-- Table structure for table `Messages`
--

CREATE TABLE `Messages` (
  `MessageID` int UNSIGNED NOT NULL,
  `MessageContent` varchar(240) NOT NULL,
  `MessageTimestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Messages`
--

INSERT INTO `Messages` (`MessageID`, `MessageContent`, `MessageTimestamp`) VALUES
(1, 'this is a new message! sent by SendMessage script', '2025-10-28 16:57:13'),
(4, 'this is a new message! sent by SendMessage script', '2025-10-28 18:35:26'),
(6, 'hi adam', '2025-11-23 19:52:19'),
(7, 'Hiii', '2025-11-23 19:55:52'),
(8, 'are you here', '2025-11-23 21:01:30'),
(14, 'hi', '2025-11-24 00:34:21'),
(15, 'hi hailey!', '2025-11-24 00:41:51');

-- --------------------------------------------------------

--
-- Table structure for table `Posts`
--

CREATE TABLE `Posts` (
  `PostID` int UNSIGNED NOT NULL,
  `PostContent` varchar(240) NOT NULL,
  `PostTimestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Posts`
--

INSERT INTO `Posts` (`PostID`, `PostContent`, `PostTimestamp`) VALUES
(2, 'New post', '2025-11-18 20:48:28'),
(3, 'Hello!', '2025-11-18 20:49:40'),
(12, 'Hello', '2025-11-22 21:17:59'),
(13, 'Hi', '2025-11-22 21:18:47'),
(14, 'hi', '2025-11-22 21:23:20'),
(15, 'hi', '2025-11-22 21:23:36'),
(16, 'hi', '2025-11-22 21:23:41'),
(17, 'hi', '2025-11-22 21:23:57'),
(18, 'hello', '2025-11-22 21:24:40'),
(19, 'hello', '2025-11-22 21:24:58'),
(20, 'hello', '2025-11-22 21:25:12'),
(21, 'aa', '2025-11-22 21:26:04'),
(22, 'aa', '2025-11-22 21:26:14'),
(31, 'Hi', '2025-11-23 01:48:38'),
(33, 'hi!', '2025-11-23 18:48:02'),
(35, 'Hi', '2025-11-24 00:33:59'),
(36, 'hi', '2025-11-24 00:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `UserFriends`
--

CREATE TABLE `UserFriends` (
  `User1ID` int UNSIGNED NOT NULL,
  `User2ID` int UNSIGNED NOT NULL
) ;

--
-- Dumping data for table `UserFriends`
--

INSERT INTO `UserFriends` (`User1ID`, `User2ID`) VALUES
(4, 8),
(2, 11),
(3, 11),
(8, 13);

-- --------------------------------------------------------

--
-- Table structure for table `UserPosts`
--

CREATE TABLE `UserPosts` (
  `UserID` int UNSIGNED NOT NULL,
  `PostID` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `UserPosts`
--

INSERT INTO `UserPosts` (`UserID`, `PostID`) VALUES
(11, 33),
(13, 35),
(13, 36);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `UserID` int UNSIGNED NOT NULL,
  `UserUsername` varchar(15) NOT NULL,
  `UserStatus` enum('Active','Restricted','Banned') DEFAULT 'Active',
  `UserType` enum('Regular','Admin') DEFAULT NULL,
  `UserDateOfBirth` date NOT NULL,
  `UserPassword` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UserID`, `UserUsername`, `UserStatus`, `UserType`, `UserDateOfBirth`, `UserPassword`) VALUES
(2, 'bertha2', 'Active', 'Regular', '1999-02-02', '$2y$10$aLISMhD3xl9vBFnul9J1H.OlbrkqkAfutb9nWBcx.c7ciuOqvHT.y'),
(3, 'carl3', 'Active', 'Regular', '1999-03-03', '$2y$10$roeOSRnAai5pbfnkfAwEAuLSIlLs1aouVnI.9uq07iANwqv3OKSsy'),
(4, 'denise4', 'Active', 'Regular', '1999-04-04', '$2y$10$A.oevNWSlKspfi5qNAwcV.P9sugrQOKiv3ClddX0yVKV/f3DR.PRy'),
(5, 'ethan5', 'Active', 'Regular', '1999-05-05', '$2y$10$2SBvFaTFftcsdW/UZ823JehFKRWWkIzki7S8003.Zaoy97jp5sp1G'),
(6, 'freya6', 'Active', 'Regular', '1999-06-06', '$2y$10$whhC.MBgGzv9IpGRalQ9bOuzIPIa2bU8pBr3vf0tSBJxkH3cGDml6'),
(7, 'george7', 'Active', 'Regular', '1999-07-07', '$2y$10$QGlCiQAYOmcme6icZm9qZe/VZTRWpnbfaxGu.LC6YB3rOIUkA5C9.'),
(8, 'hailey8', 'Active', 'Admin', '1999-08-08', '$2y$10$ZIdmBT.BNPFzZtlUGoT8ceUIqqyF/Vmu.UgiRqpARk0AwdxyP1gsG'),
(9, 'ivan9', 'Active', 'Regular', '1999-09-09', '$2y$10$hel05Gw3UBW6zSw86KWlX.26lTD0/LBZFMYONFYKRhqM8HmzESlfa'),
(10, 'jessica10', 'Active', 'Regular', '1999-10-10', '$2y$10$75VApKQ6RQh4gAMxjGWjauJZo17kwMTxPk.HNwrDdklHWQTBrN5Si'),
(11, 'genericuser', 'Active', 'Regular', '1999-06-16', '$2y$10$EgiO26Tu2axY5Gl2CF00RevKGlpzczfNEpsuJVlL/8j.87Tejjpz6'),
(12, 'newuser1', 'Active', 'Regular', '1959-01-01', '$2y$10$0CLiNasWEksAMLCv21sD1ucTQ1cEGVOHWZMQ8kJPZramF0flQEu82'),
(13, 'passwordisa', 'Active', 'Admin', '2000-02-26', '$2y$10$swvvXRus8AlZvXG4gz1zOegWaOOHetEasSZQenD9Noc8tSmFqiSFe');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `MessageParticipants`
--
ALTER TABLE `MessageParticipants`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `UserSenderID` (`UserSenderID`),
  ADD KEY `UserReceiverID` (`UserReceiverID`);

--
-- Indexes for table `Messages`
--
ALTER TABLE `Messages`
  ADD PRIMARY KEY (`MessageID`);

--
-- Indexes for table `Posts`
--
ALTER TABLE `Posts`
  ADD PRIMARY KEY (`PostID`);

--
-- Indexes for table `UserFriends`
--
ALTER TABLE `UserFriends`
  ADD PRIMARY KEY (`User1ID`,`User2ID`),
  ADD KEY `User2ID` (`User2ID`);

--
-- Indexes for table `UserPosts`
--
ALTER TABLE `UserPosts`
  ADD PRIMARY KEY (`UserID`,`PostID`),
  ADD KEY `PostID` (`PostID`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `UserUsername` (`UserUsername`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Messages`
--
ALTER TABLE `Messages`
  MODIFY `MessageID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Posts`
--
ALTER TABLE `Posts`
  MODIFY `PostID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `UserID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `MessageParticipants`
--
ALTER TABLE `MessageParticipants`
  ADD CONSTRAINT `messageparticipants_ibfk_1` FOREIGN KEY (`UserSenderID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `messageparticipants_ibfk_2` FOREIGN KEY (`UserReceiverID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `messageparticipants_ibfk_3` FOREIGN KEY (`MessageID`) REFERENCES `Messages` (`MessageID`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `UserFriends`
--
ALTER TABLE `UserFriends`
  ADD CONSTRAINT `userfriends_ibfk_1` FOREIGN KEY (`User1ID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `userfriends_ibfk_2` FOREIGN KEY (`User2ID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `UserPosts`
--
ALTER TABLE `UserPosts`
  ADD CONSTRAINT `userposts_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `userposts_ibfk_2` FOREIGN KEY (`PostID`) REFERENCES `Posts` (`PostID`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;
