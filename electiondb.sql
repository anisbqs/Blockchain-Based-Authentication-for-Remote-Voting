-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2024 at 03:07 PM
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
-- Database: `electiondb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activestudents`
--

CREATE TABLE `activestudents` (
  `student_id` varchar(10) NOT NULL,
  `student_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activestudents`
--

INSERT INTO `activestudents` (`student_id`, `student_email`) VALUES
('1201101801', 'adam.harith@mmu.student.my'),
('1201101821', 'arthur.curry@mmu.student.my'),
('1201101810', 'ava.thompson@mmu.student.my'),
('1201101819', 'barry.allen@mmu.student.my'),
('1201101817', 'bruce.wayne@mmu.student.my'),
('1201101803', 'carson.johnson@mmu.student.my'),
('1201101816', 'clark.kent@mmu.student.my'),
('1201101818', 'diana.prince@mmu.student.my'),
('1201101805', 'ethan.smith@mmu.student.my'),
('1201101820', 'hal.jordan@mmu.student.my'),
('1201101811', 'jake.doe@mmu.student.my'),
('1201101807', 'james.white@mmu.student.my'),
('1201101813', 'john.smith@mmu.student.my'),
('1201101804', 'lily.chen@mmu.student.my'),
('1201101809', 'lucas.martin@mmu.student.my'),
('1201101802', 'maria.elena@mmu.student.my'),
('1201101814', 'nancy.drew@mmu.student.my'),
('1201101808', 'olivia.harris@mmu.student.my'),
('1201101815', 'sam.wilson@mmu.student.my'),
('1201101812', 'sara.connor@mmu.student.my'),
('1201101806', 'sophia.brown@mmu.student.my');

-- --------------------------------------------------------

--
-- Table structure for table `election`
--

CREATE TABLE `election` (
  `election_id` int(11) NOT NULL,
  `election_name` varchar(100) DEFAULT NULL,
  `candidate_name` varchar(100) DEFAULT NULL,
  `candidate_student_id` varchar(10) DEFAULT NULL,
  `election_start_time` datetime DEFAULT NULL,
  `election_end_time` datetime DEFAULT NULL,
  `contract_address` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election`
--

INSERT INTO `election` (`election_id`, `election_name`, `candidate_name`, `candidate_student_id`, `election_start_time`, `election_end_time`, `contract_address`) VALUES
(132, 'Hostel Head Secretary', 'Sophia Brown', '1201101806', '2024-06-30 02:10:00', '2024-07-09 02:10:00', '0x05ec0D8811A48460904966148486D84428472913'),
(133, 'Hostel Head Secretary', 'Lucas Martin', '1201101809', '2024-06-30 02:10:00', '2024-07-09 02:10:00', '0x05ec0D8811A48460904966148486D84428472913'),
(136, 'MMU Director', 'Adam Harith Anuar', '1201101801', '2024-06-30 03:15:00', '2024-07-08 03:15:00', '0x43158BE8E47De940cc1f74E1FFBC0793906E9FA0'),
(138, 'MMU Director', 'Olivia Harris', '1201101808', '2024-06-30 03:15:00', '2024-07-08 03:15:00', '0x43158BE8E47De940cc1f74E1FFBC0793906E9FA0'),
(139, 'General Election MMU Treasurer', 'James White', '1201101807', '2024-06-30 18:07:00', '2024-07-02 18:07:00', '0x684d18a56f439767e272a027bdc2722ee6467bbab5636efe1e93dd9ebcd420c0'),
(140, 'General Election MMU Treasurer', 'Ava Thompson', '1201101810', '2024-06-30 18:07:00', '2024-07-02 18:07:00', '0x684d18a56f439767e272a027bdc2722ee6467bbab5636efe1e93dd9ebcd420c0'),
(144, 'SRC Secretary 2024', 'Maria Elena', '1201101802', '2024-06-30 02:09:00', '2024-07-08 02:09:00', '0xeFad170B9B9E38101E4B8DFAFe335283Ee5b7eCD'),
(145, 'SRC Secretary 2024', 'Lily Chen', '1201101804', '2024-06-30 02:09:00', '2024-07-08 02:09:00', '0xeFad170B9B9E38101E4B8DFAFe335283Ee5b7eCD'),
(146, 'SRC Head 2024', 'Carson Johnson', '1201101803', '2024-06-30 18:10:00', '2024-07-17 18:10:00', '0x75574af58DCE4231eDb9aCB0295Ea28B5b80506b'),
(147, 'SRC Head 2024', 'Ethan Smith', '1201101805', '2024-06-30 18:10:00', '2024-07-17 18:10:00', '0x75574af58DCE4231eDb9aCB0295Ea28B5b80506b');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `student_password` varchar(100) DEFAULT NULL,
  `student_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `student_password`, `student_email`) VALUES
('1201101801', 'Adam Harith Anuar', 'P@ssw0rd!', 'adam.harith@mmu.student.my'),
('1201101802', 'Maria Elena', 'maria', 'maria.elena@mmu.student.my'),
('1201101803', 'Carson Johnson', 'carson', 'carson.johnson@mmu.student.my'),
('1201101804', 'Lily Chen', 'lily', 'lily.chen@mmu.student.my'),
('1201101805', 'Ethan Smith', 'ethan', 'ethan.smith@mmu.student.my'),
('1201101806', 'Sophia Brown', 'sophia', 'sophia.brown@mmu.student.my'),
('1201101807', 'James White', 'james', 'james.white@mmu.student.my'),
('1201101808', 'Olivia Harris', 'olivia', 'olivia.harris@mmu.student.my'),
('1201101809', 'Lucas Martin', 'lucas', 'lucas.martin@mmu.student.my'),
('1201101810', 'Ava Thompson', 'ava', 'ava.thompson@mmu.student.my'),
('1201101811', 'anis', '$afety1!', 'jake.doe@mmu.student.my');

-- --------------------------------------------------------

--
-- Table structure for table `votinghub`
--

CREATE TABLE `votinghub` (
  `admin_id` varchar(10) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `admin_passphrase` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votinghub`
--

INSERT INTO `votinghub` (`admin_id`, `password`, `admin_passphrase`) VALUES
('101', 'admin1', 'mmuvote'),
('102', 'admin2', 'mmuvote'),
('103', '$afety1!', 'mmuvote');

-- --------------------------------------------------------

--
-- Table structure for table `votingrecord`
--

CREATE TABLE `votingrecord` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `election_name` varchar(255) NOT NULL,
  `has_voted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votingrecord`
--

INSERT INTO `votingrecord` (`id`, `student_id`, `election_name`, `has_voted`) VALUES
(20, '1201101811', 'Hostel Head Secretary', 1),
(21, '1201101810', 'SRC Secretary 2024', 1),
(22, '1201101810', 'Hostel Head Secretary', 1),
(23, '1201101807', 'MMU Director', 1),
(24, '1201101811', 'MMU Director', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activestudents`
--
ALTER TABLE `activestudents`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_email` (`student_email`);

--
-- Indexes for table `election`
--
ALTER TABLE `election`
  ADD PRIMARY KEY (`election_id`),
  ADD KEY `candidate_student_id` (`candidate_student_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `votinghub`
--
ALTER TABLE `votinghub`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `votingrecord`
--
ALTER TABLE `votingrecord`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`election_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `election`
--
ALTER TABLE `election`
  MODIFY `election_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `votingrecord`
--
ALTER TABLE `votingrecord`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `election`
--
ALTER TABLE `election`
  ADD CONSTRAINT `election_ibfk_1` FOREIGN KEY (`candidate_student_id`) REFERENCES `student` (`student_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
