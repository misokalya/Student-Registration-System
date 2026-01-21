-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 07:51 AM
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
-- Database: `student_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`) VALUES
(1, 'Electrical & Computer Engineering'),
(2, 'Industrial Automation Engineering'),
(3, 'Telecommunication & Electronics Engineering'),
(4, 'Electrical & Renewable Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `reg_no` varchar(30) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `first_name`, `last_name`, `gender`, `course_id`, `reg_no`, `dob`, `date_registered`, `photo`) VALUES
(1, 2, 'Misokalya', 'Kimiti', 'Male', 1, '123456789', '1995-07-09', '2026-01-19 12:39:49', '1768826389_DSC_7041_Portrait+_1.jpg'),
(2, 3, 'Hudhaifa', 'Ally', 'Male', 1, '012345678', '2006-05-28', '2026-01-19 12:53:25', '1768827205_default.jpg'),
(3, 4, 'Sumaiya', 'Muyabhi', 'Female', 3, '123456', '2010-07-28', '2026-01-19 13:02:02', '1768827722_default.jpg'),
(4, 5, 'Arnold', 'Benjamin', 'Male', 2, '543210', '2002-01-01', '2026-01-19 13:03:43', '1768827823_default.jpg'),
(5, 6, 'Rodgers', 'Eustaki', 'Male', 4, '987654', '2002-01-02', '2026-01-19 13:04:50', '1768827890_default.jpg'),
(6, 7, 'Junior', 'Peter', 'Male', 3, '010101', '2004-02-01', '2026-01-19 13:05:40', '1768827940_default.jpg'),
(8, 9, 'Jovline', 'Epimark', 'Female', 1, '112233', '2003-10-08', '2026-01-21 06:14:33', '1768976325_download (7).jpg'),
(9, 10, 'Mohamed', 'Juma', 'Male', 1, '332211', '2000-01-01', '2026-01-21 06:21:14', '1768976474_images (4).jfif'),
(10, 11, 'Maurus', 'moyo', 'Male', 1, '666666', '2010-12-20', '2026-01-21 06:26:48', '1768976808_KINGWENDU.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$1OFI.YL8xOZNhtDhRw9GOuYSTTDI72fAMdwfC9hPMx.EBC2IprKbW', 'admin'),
(2, '123456789', '$2y$10$FKC.h8zTsheJ/uzUl2bu8OxuO7wFsJiVKn3bixcndxrPpsylIQPKm', 'student'),
(3, '012345678', '$2y$10$3rG7R.nfs8ItgoIOABhH/.FPR/enyg7WOPlNn6iWbl5lbEMHk0e5G', 'student'),
(4, '123456', '$2y$10$MBu7xZLuAaqFssqL.J4R9e4p3PdNehCylJgC7KovFp72t3p9ZP51.', 'student'),
(5, '543210', '$2y$10$NgTLCGd9YM7j/LMdZOQGyOGrK8d9XuctiHqjAsMAif4wUXpRAiZ2u', 'student'),
(6, '987654', '$2y$10$vtNjalIa8ZJ9KJM7J3YcDe0WliQIjY05.QewHcET/2pWZzKGB.rNm', 'student'),
(7, '010101', '$2y$10$lOOd.1BIyYknBqqWe2SffelUkzJmaxr98wp0E32B9ZW3EH/k81g5y', 'student'),
(9, '112233', '$2y$10$xVSXi9Q89td53PvexuWdgOHEmK4fiq7irMMLrRSbwpXamWj5zJDWm', 'student'),
(10, '332211', '$2y$10$5vcrS.S8tEbqN2fjmuN11OlsYOviQjQImgHtl6.6LWlgRtOGqyuvu', 'student'),
(11, '666666', '$2y$10$ILq9dgn3bOq/YlU1JdufoOGHAGEzceQEoUdIBCAuHeEr.kM7IWjPK', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
