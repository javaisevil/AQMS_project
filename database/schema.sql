-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 15, 2026 at 01:38 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
CREATE DATABASE IF NOT EXISTS `AQMS_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `AQMS_db`;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `AQMS_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `approval_log`
--

CREATE TABLE `approval_log` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `user_id` int NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `activity_name` varchar(200) NOT NULL,
  `timing_week` int DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_clo`
--

CREATE TABLE `assessment_clo` (
  `id` int NOT NULL,
  `assessment_id` int NOT NULL,
  `clo_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clo_plo_mapping`
--

CREATE TABLE `clo_plo_mapping` (
  `id` int NOT NULL,
  `clo_id` int NOT NULL,
  `plo_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_hours`
--

CREATE TABLE `contact_hours` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `hours` decimal(5,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_learning_outcomes`
--

CREATE TABLE `course_learning_outcomes` (
  `clo_id` int NOT NULL,
  `course_id` int NOT NULL,
  `clo_code` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `category` enum('Knowledge','Skills','Values') NOT NULL,
  `teaching_strategies` text,
  `assessment_methods` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_specs`
--

CREATE TABLE `course_specs` (
  `course_id` int NOT NULL,
  `program_id` int DEFAULT NULL,
  `faculty_id` int DEFAULT NULL,
  `course_title` varchar(200) NOT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `credit_hours` decimal(4,1) DEFAULT NULL,
  `course_type` varchar(100) DEFAULT NULL,
  `teaching_mode` varchar(100) DEFAULT NULL,
  `course_level` int DEFAULT NULL,
  `prerequisites` varchar(200) DEFAULT NULL,
  `corequisites` varchar(200) DEFAULT NULL,
  `course_description` text,
  `objectives` text,
  `learning_resources` text,
  `version` varchar(20) DEFAULT '1.0',
  `status` enum('draft','pending_hod','pending_qa','approved') DEFAULT 'draft',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_topics`
--

CREATE TABLE `course_topics` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `topic_text` text NOT NULL,
  `contact_hours` decimal(5,1) DEFAULT NULL,
  `sort_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jahiziah_skills`
--

CREATE TABLE `jahiziah_skills` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `clo_id` int DEFAULT NULL,
  `skill_type` enum('Digital','Communication','Teamwork','Ethics') NOT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_kpis`
--

CREATE TABLE `program_kpis` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `kpi_code` varchar(50) DEFAULT NULL,
  `kpi_text` text NOT NULL,
  `target_level` varchar(200) DEFAULT NULL,
  `measurement_method` text,
  `measurement_time` varchar(200) DEFAULT NULL,
  `years_to_achieve` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_learning_outcomes`
--

CREATE TABLE `program_learning_outcomes` (
  `plo_id` int NOT NULL,
  `program_id` int NOT NULL,
  `plo_code` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `category` enum('Knowledge','Skills','Values') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `program_learning_outcomes`
--

INSERT INTO `program_learning_outcomes` (`plo_id`, `program_id`, `plo_code`, `description`, `category`) VALUES
(1, 1, 'K1', 'Demonstrate foundational knowledge in core computing concepts, algorithms and data structures', 'Knowledge'),
(2, 1, 'K2', 'Understand software development methodologies, design patterns, and engineering best practices', 'Knowledge'),
(3, 1, 'K3', 'Recognize the role of ethics, privacy and security in computing systems', 'Knowledge'),
(4, 1, 'S1', 'Apply programming and problem-solving skills to design and implement software solutions', 'Skills'),
(5, 1, 'S2', 'Analyze complex technical problems and propose well-justified solutions using appropriate tools', 'Skills'),
(6, 1, 'S3', 'Communicate technical ideas effectively in written, oral and visual forms', 'Skills'),
(7, 1, 'V1', 'Commit to professional, ethical and legal standards in computing practice', 'Values'),
(8, 1, 'V2', 'Work effectively in diverse team environments and demonstrate leadership in projects', 'Values');

-- --------------------------------------------------------

--
-- Table structure for table `program_specs`
--

CREATE TABLE `program_specs` (
  `program_id` int NOT NULL,
  `program_name` varchar(200) NOT NULL,
  `program_code` varchar(50) DEFAULT NULL,
  `college` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `credit_hours` int DEFAULT NULL,
  `qualification_level` varchar(100) DEFAULT NULL,
  `mission` text,
  `goals` text,
  `program_aims` text,
  `program_structure` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `program_specs`
--

INSERT INTO `program_specs` (`program_id`, `program_name`, `program_code`, `college`, `department`, `credit_hours`, `qualification_level`, `mission`, `goals`, `program_aims`, `program_structure`) VALUES
(1, 'Bachelor of Science in Software Engineering', 'BSE', 'College of Engineering and Architecture', 'Software Engineering', 141, 'NQF Level 6 — Bachelor', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quality_improvements`
--

CREATE TABLE `quality_improvements` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `stage` enum('Plan','Do','Check','Act') NOT NULL,
  `description` text,
  `cycle_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `category` enum('Essential','Supportive','Electronic','Other') NOT NULL,
  `resource_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teaching_modes`
--

CREATE TABLE `teaching_modes` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `mode_type` varchar(100) DEFAULT NULL,
  `contact_hours` decimal(5,1) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('faculty','hod','qa') NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `role`, `full_name`, `department`, `created_at`) VALUES
(1, 'faculty1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'Dr. Conrado Vizcarra', 'Engineering', '2026-05-15 14:24:04'),
(2, 'hod1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hod', 'Dr. Roxane Mallouhy', 'Computer Science', '2026-05-15 14:24:04'),
(3, 'qa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'qa', 'Mr. Khalid Al-Qahtani', 'Quality Assurance Office', '2026-05-15 14:24:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approval_log`
--
ALTER TABLE `approval_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `assessment_clo`
--
ALTER TABLE `assessment_clo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `clo_id` (`clo_id`);

--
-- Indexes for table `clo_plo_mapping`
--
ALTER TABLE `clo_plo_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_clo_plo` (`clo_id`,`plo_id`),
  ADD KEY `plo_id` (`plo_id`);

--
-- Indexes for table `contact_hours`
--
ALTER TABLE `contact_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_learning_outcomes`
--
ALTER TABLE `course_learning_outcomes`
  ADD PRIMARY KEY (`clo_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_specs`
--
ALTER TABLE `course_specs`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `course_topics`
--
ALTER TABLE `course_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `jahiziah_skills`
--
ALTER TABLE `jahiziah_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `clo_id` (`clo_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `program_kpis`
--
ALTER TABLE `program_kpis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `program_learning_outcomes`
--
ALTER TABLE `program_learning_outcomes`
  ADD PRIMARY KEY (`plo_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `program_specs`
--
ALTER TABLE `program_specs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `quality_improvements`
--
ALTER TABLE `quality_improvements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `teaching_modes`
--
ALTER TABLE `teaching_modes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approval_log`
--
ALTER TABLE `approval_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessment_clo`
--
ALTER TABLE `assessment_clo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clo_plo_mapping`
--
ALTER TABLE `clo_plo_mapping`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_hours`
--
ALTER TABLE `contact_hours`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_learning_outcomes`
--
ALTER TABLE `course_learning_outcomes`
  MODIFY `clo_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_specs`
--
ALTER TABLE `course_specs`
  MODIFY `course_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_topics`
--
ALTER TABLE `course_topics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jahiziah_skills`
--
ALTER TABLE `jahiziah_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_kpis`
--
ALTER TABLE `program_kpis`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_learning_outcomes`
--
ALTER TABLE `program_learning_outcomes`
  MODIFY `plo_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `program_specs`
--
ALTER TABLE `program_specs`
  MODIFY `program_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quality_improvements`
--
ALTER TABLE `quality_improvements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teaching_modes`
--
ALTER TABLE `teaching_modes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approval_log`
--
ALTER TABLE `approval_log`
  ADD CONSTRAINT `approval_log_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approval_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_clo`
--
ALTER TABLE `assessment_clo`
  ADD CONSTRAINT `assessment_clo_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_clo_ibfk_2` FOREIGN KEY (`clo_id`) REFERENCES `course_learning_outcomes` (`clo_id`) ON DELETE CASCADE;

--
-- Constraints for table `clo_plo_mapping`
--
ALTER TABLE `clo_plo_mapping`
  ADD CONSTRAINT `clo_plo_mapping_ibfk_1` FOREIGN KEY (`clo_id`) REFERENCES `course_learning_outcomes` (`clo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clo_plo_mapping_ibfk_2` FOREIGN KEY (`plo_id`) REFERENCES `program_learning_outcomes` (`plo_id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_hours`
--
ALTER TABLE `contact_hours`
  ADD CONSTRAINT `contact_hours_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_learning_outcomes`
--
ALTER TABLE `course_learning_outcomes`
  ADD CONSTRAINT `course_learning_outcomes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_specs`
--
ALTER TABLE `course_specs`
  ADD CONSTRAINT `course_specs_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `course_specs_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `course_topics`
--
ALTER TABLE `course_topics`
  ADD CONSTRAINT `course_topics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `jahiziah_skills`
--
ALTER TABLE `jahiziah_skills`
  ADD CONSTRAINT `jahiziah_skills_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jahiziah_skills_ibfk_2` FOREIGN KEY (`clo_id`) REFERENCES `course_learning_outcomes` (`clo_id`) ON DELETE SET NULL;

--
-- Constraints for table `program_kpis`
--
ALTER TABLE `program_kpis`
  ADD CONSTRAINT `program_kpis_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE;

--
-- Constraints for table `program_learning_outcomes`
--
ALTER TABLE `program_learning_outcomes`
  ADD CONSTRAINT `program_learning_outcomes_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE;

--
-- Constraints for table `quality_improvements`
--
ALTER TABLE `quality_improvements`
  ADD CONSTRAINT `quality_improvements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `teaching_modes`
--
ALTER TABLE `teaching_modes`
  ADD CONSTRAINT `teaching_modes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
