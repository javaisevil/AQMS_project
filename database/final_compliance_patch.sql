-- AQMS final compliance patch

ALTER TABLE `user`
MODIFY `role` varchar(50) NOT NULL;

ALTER TABLE `course_specs`
MODIFY `status` enum('draft','pending_hod','pending_qa','approved','returned_by_hod','returned_by_qa') DEFAULT 'draft';

ALTER TABLE `course_specs`
ADD COLUMN `department` varchar(150) DEFAULT NULL,
ADD COLUMN `college` varchar(150) DEFAULT NULL,
ADD COLUMN `institution` varchar(150) DEFAULT NULL,
ADD COLUMN `required_elective` varchar(50) DEFAULT NULL,
ADD COLUMN `last_revision_date` date DEFAULT NULL,
ADD COLUMN `submitted_at` datetime DEFAULT NULL,
ADD COLUMN `deadline_status` varchar(50) DEFAULT NULL;

ALTER TABLE `course_learning_outcomes`
MODIFY `category` varchar(100) NOT NULL;

ALTER TABLE `resources`
MODIFY `category` varchar(100) NOT NULL;

ALTER TABLE `assessments`
ADD COLUMN `assessment_timing` varchar(100) DEFAULT NULL,
ADD COLUMN `proportion_of_total` decimal(5,2) DEFAULT NULL,
ADD COLUMN `rubric` text,
ADD COLUMN `performance_task` text;

UPDATE `assessments`
SET `assessment_timing` = IF(`timing_week` IS NULL, NULL, CONCAT('Week ', `timing_week`)),
`proportion_of_total` = `percentage`
WHERE `id` IS NOT NULL;

CREATE TABLE IF NOT EXISTS `course_facilities` (
`id` int NOT NULL AUTO_INCREMENT,
`course_id` int NOT NULL,
`item` varchar(150) NOT NULL,
`resources` text,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
CONSTRAINT `course_facilities_course_fk` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_quality` (
`id` int NOT NULL AUTO_INCREMENT,
`course_id` int NOT NULL,
`assessment_area` varchar(200) NOT NULL,
`assessor` varchar(200) DEFAULT NULL,
`assessment_method` varchar(200) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
CONSTRAINT `course_quality_course_fk` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_approval` (
`id` int NOT NULL AUTO_INCREMENT,
`course_id` int NOT NULL,
`council_committee` varchar(200) DEFAULT NULL,
`reference_no` varchar(100) DEFAULT NULL,
`approval_date` date DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
CONSTRAINT `course_approval_course_fk` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `course_pdca`
ADD COLUMN `phase` enum('Plan','Do','Check','Act') DEFAULT NULL,
ADD COLUMN `content` text,
ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP;

UPDATE `course_pdca`
SET `phase` = `stage`,
`content` = `description`
WHERE `id` IS NOT NULL;

CREATE TABLE IF NOT EXISTS `program_tp151_sections` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`section_key` varchar(100) NOT NULL,
`section_title` varchar(200) NOT NULL,
`section_value` text,
PRIMARY KEY (`id`),
UNIQUE KEY `uniq_program_section` (`program_id`,`section_key`),
CONSTRAINT `program_tp151_sections_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_curriculum_structure` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`requirement_type` varchar(150) NOT NULL,
`required_hours` decimal(5,1) DEFAULT NULL,
`elective_hours` decimal(5,1) DEFAULT NULL,
`total_hours` decimal(5,1) DEFAULT NULL,
`percentage` decimal(5,2) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `program_id` (`program_id`),
CONSTRAINT `program_curriculum_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_course_plan` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`level_no` int DEFAULT NULL,
`course_code` varchar(50) DEFAULT NULL,
`course_title` varchar(200) DEFAULT NULL,
`required_elective` varchar(50) DEFAULT NULL,
`prerequisites` varchar(200) DEFAULT NULL,
`credit_hours` decimal(4,1) DEFAULT NULL,
`course_spec_url` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `program_id` (`program_id`),
CONSTRAINT `program_course_plan_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_plo_methods` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`plo_id` int NOT NULL,
`teaching_strategies` text,
`assessment_methods` text,
PRIMARY KEY (`id`),
KEY `program_id` (`program_id`),
KEY `plo_id` (`plo_id`),
CONSTRAINT `program_plo_methods_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE,
CONSTRAINT `program_plo_methods_plo_fk` FOREIGN KEY (`plo_id`) REFERENCES `program_learning_outcomes` (`plo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_staffing` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`academic_rank` varchar(100) NOT NULL,
`specialty` varchar(200) DEFAULT NULL,
`special_requirements` varchar(200) DEFAULT NULL,
`male_count` int DEFAULT NULL,
`female_count` int DEFAULT NULL,
`total_count` int DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `program_id` (`program_id`),
CONSTRAINT `program_staffing_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_evaluation_matrix` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`evaluation_area` varchar(200) NOT NULL,
`evaluation_sources` varchar(200) DEFAULT NULL,
`evaluation_methods` varchar(200) DEFAULT NULL,
`evaluation_time` varchar(200) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `program_id` (`program_id`),
CONSTRAINT `program_evaluation_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_approval` (
`id` int NOT NULL AUTO_INCREMENT,
`program_id` int NOT NULL,
`council_committee` varchar(200) DEFAULT NULL,
`reference_no` varchar(100) DEFAULT NULL,
`approval_date` date DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `program_id` (`program_id`),
CONSTRAINT `program_approval_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program_specs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
