-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 14, 2018 at 05:36 AM Darilys Pereira
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `virtual_roll_call`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Category_ID` int(10) UNSIGNED NOT NULL,
  `Category_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `category_shift_access`
--

CREATE TABLE `category_shift_access` (
  `Category_ID` int(10) NOT NULL,
  `Shift_Id` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `Document_ID` int(10) UNSIGNED NOT NULL,
  `Document_Name` varchar(50) NOT NULL,
  `Category_ID` int(10) UNSIGNED NOT NULL,
  `Upload_Date` date NOT NULL,
  `Pinned` tinyint(1) NOT NULL,
  `Uploaded_By` varchar(255) NOT NULL,
  `Upload_Name` varchar(255) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Manual_Archived` tinyint(1) NOT NULL,
  `Has_Quiz` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `document_status`
--

CREATE TABLE `document_status` (
  `Id` int(11) NOT NULL,
  `Description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `Log_id` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime DEFAULT NULL,
  `Lock_count` tinyint(4) NOT NULL,
  `Lock_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `Id` int(11) NOT NULL,
  `DOC` datetime NOT NULL,
  `Userid` int(11) NOT NULL,
  `Documentid` int(11) NOT NULL,
  `Categoryid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `MessageId` int(10) UNSIGNED NOT NULL,
  `Pinned` tinyint(1) NOT NULL DEFAULT '0',
  `Title` varchar(100) NOT NULL,
  `Description` varchar(100) DEFAULT NULL,
  `Message` text NOT NULL,
  `Created_by` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_by` int(11) DEFAULT NULL,
  `Updated_at` datetime DEFAULT NULL,
  `Manual_Archived` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

CREATE TABLE `officers` (
  `UserID` int(10) UNSIGNED NOT NULL,
  `First_Name` varchar(20) NOT NULL,
  `Last_Name` varchar(20) NOT NULL,
  `Username` varchar(20) NOT NULL,
  `Password` varchar(60) NOT NULL,
  `Role` varchar(40) NOT NULL,
  `Shift_id` int(2) NOT NULL,
  `Active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `Id` int(11) NOT NULL,
  `Document_name` varchar(50) NOT NULL,
  `QA` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_logs`
--

CREATE TABLE `quiz_logs` (
  `Id` int(11) NOT NULL,
  `Officer_id` int(11) NOT NULL,
  `Document_id` int(11) NOT NULL,
  `Score` decimal(3,2) NOT NULL,
  `Answers` text NOT NULL,
  `Created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `Application_Name` varchar(255) NOT NULL,
  `Department_Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `Id` int(2) NOT NULL,
  `Name` varchar(10) NOT NULL,
  `From_time` time(5) NOT NULL,
  `To_time` time(5) NOT NULL,
  `Status` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_doc_status`
--

CREATE TABLE `user_doc_status` (
  `OfficerId` int(11) NOT NULL,
  `StatusId` int(11) NOT NULL,
  `DocumentId` int(11) NOT NULL,
  `CategoryId` int(11) NOT NULL,
  `StartDateTime` datetime NOT NULL,
  `EndDateTime` datetime NOT NULL,
  `IsArchived` tinyint(4) NOT NULL,
  `Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `watch_orders`
--

CREATE TABLE `watch_orders` (
  `Id` int(10) NOT NULL AUTO_INCREMENT,
  `Desc` varchar(60) DEFAULT 'No Description',
  `Address` varchar(100) NOT NULL,
  `Lat` float(10,6) NOT NULL,
  `Lng` float(10,6) NOT NULL,
  `AddDate` date NOT NULL,
  `ExpDate` date NOT NULL,
  `StartDate` date DEFAULT NULL,
  `StartTime` time DEFAULT NULL,
  `ExpTime` time DEFAULT NULL,
  `Zone` varchar(60) DEFAULT NULL,
  `BusinessName` varchar(100) DEFAULT NULL,
  `OwnerName` varchar(60) DEFAULT NULL,
  `WORequester` varchar(60) DEFAULT NULL,
  `Phone` varchar(45) DEFAULT NULL,
  `WOInstruction` varchar(250) DEFAULT NULL,
  `EName` varchar(60) DEFAULT NULL,
  `EAddress` varchar(100) DEFAULT NULL,
  `EPhone` varchar(45) DEFAULT NULL,
  `CreatedBy` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `watch_orders_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `watch_orders_id` int(11) NOT NULL,
  `officers_userid` int(11) NOT NULL,
  `is_selected` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `category_name` (`Category_Name`);

--
-- Indexes for table `category_shift_access`
--
ALTER TABLE `category_shift_access`
  ADD PRIMARY KEY (`Category_ID`,`Shift_Id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`Document_ID`),
  ADD UNIQUE KEY `Document_Name` (`Document_Name`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `document_status`
--
ALTER TABLE `document_status`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`Log_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`MessageId`);

--
-- Indexes for table `officers`
--
ALTER TABLE `officers`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Username_2` (`Username`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `document_id` (`Document_name`);

--
-- Indexes for table `quiz_logs`
--
ALTER TABLE `quiz_logs`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `user_doc_status`
--
ALTER TABLE `user_doc_status`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `Category_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `Document_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `document_status`
--
ALTER TABLE `document_status`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `Log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;
--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `MessageId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `UserID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;
--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `quiz_logs`
--
ALTER TABLE `quiz_logs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `Id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `user_doc_status`
--
ALTER TABLE `user_doc_status`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `watch_orders`
--
ALTER TABLE `watch_orders`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `DOCUMENTS_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`) ON DELETE CASCADE;
COMMIT;

--
-- MySQL event called "MoveDocsToArchive", which is going to archive all documents older than 7 days and are marked as "Done"
-- The event will run every day. Modify "Start Date" to the day when this schema will be deploy.
--
DROP EVENT IF EXISTS `MoveDocsToArchive`;
CREATE EVENT MoveDocsToArchive 
	ON SCHEDULE EVERY 1 Day STARTS '2018-02-22' 
    ON COMPLETION NOT PRESERVE ENABLE 
    COMMENT 'Archive documents older than 7 days and that are marked as DONE' 
    DO Update virtual_roll_call.user_doc_status, virtual_roll_call.documents
		  set user_doc_status.IsArchived = 1
		where user_doc_status.StatusId = 3 
		  and documents.Upload_Date < (DATE(NOW()) - INTERVAL 7 DAY) 
		  and user_doc_status.DocumentId = documents.Document_ID;

--
-- Delete Watch Order Tracking records after the watch Order has expired
--
DROP EVENT IF EXISTS `DelWOTrackOnExpDate`;
CREATE EVENT DelWOTrackOnExpDate 
	ON SCHEDULE EVERY 1 Day STARTS '2018-03-24' 
    ON COMPLETION NOT PRESERVE ENABLE 
    COMMENT 'Delete the watch orders tracking records after it has expired' 
    DO Delete From watch_orders_tracking 
			 where watch_orders_id in (select Id from watch_orders where ExpDate < NOW());		  

		  
--
-- ACTIVATE EVENT
--

set global event_scheduler = ON;

--
-- PROCEDURE to create watch order tracking records for all users, on officers table, on watch_orders_tracking table 
--
DROP PROCEDURE IF EXISTS `CreateWatchOrdersForOfficers`;
DELIMITER //
CREATE PROCEDURE `CreateWatchOrdersForOfficers`(IN wo_id INT(10))
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE officerid INT(10);
  DECLARE officers CURSOR FOR SELECT userid FROM officers;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN officers;
  insert_loop: LOOP
    FETCH officers INTO officerid;
    IF done THEN
      LEAVE insert_loop;
    END IF; 
      INSERT INTO watch_orders_tracking (watch_orders_id, officers_userid, is_selected) values (wo_id, officerid, 0);
  END LOOP;

  CLOSE officers;
END ; //
DELIMITER ;

--
-- Create Trigger to create watch order tracking
--
DELIMITER //
CREATE TRIGGER `watch_orders_AFTER_INSERT` AFTER INSERT ON `watch_orders` FOR EACH ROW
BEGIN
	call virtual_roll_call.CreateWatchOrdersForOfficers(new.id);
END;//
DELIMITER ;

--
-- Create trigger to delete watch order tracking when watch order is deleted
--
DELIMITER //
CREATE TRIGGER `watch_orders_AFTER_DELETE` AFTER DELETE ON `watch_orders` FOR EACH ROW
BEGIN
	delete from watch_orders_tracking where watch_orders_id = old.id;
END;//
DELIMITER ;

--
-- Commit Action
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
