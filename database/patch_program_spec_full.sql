USE `AQMS_db`;

CREATE TABLE IF NOT EXISTS `program_tp151_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `section_key` varchar(100) NOT NULL,
  `section_title` varchar(200) NOT NULL,
  `section_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_section` (`program_id`, `section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_curriculum_structure` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `requirement_type` varchar(100) NOT NULL,
  `required_hours` decimal(6,1) DEFAULT NULL,
  `elective_hours` decimal(6,1) DEFAULT NULL,
  `total_hours` decimal(6,1) DEFAULT NULL,
  `percentage` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_course_plan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `level_no` int DEFAULT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `course_title` varchar(200) DEFAULT NULL,
  `required_elective` varchar(50) DEFAULT NULL,
  `prerequisites` varchar(200) DEFAULT NULL,
  `credit_hours` decimal(5,1) DEFAULT NULL,
  `course_spec_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_plo_course_matrix` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `plo_id` int NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `level_mark` enum('I','P','M') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_plo_course` (`program_id`, `plo_id`, `course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_plo_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `plo_id` int NOT NULL,
  `teaching_strategies` text,
  `assessment_methods` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_plo` (`program_id`, `plo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_staffing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `academic_rank` varchar(100) DEFAULT NULL,
  `specialty` varchar(150) DEFAULT NULL,
  `special_requirements` text,
  `male_count` int DEFAULT NULL,
  `female_count` int DEFAULT NULL,
  `total_count` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_evaluation_matrix` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `evaluation_area` varchar(200) DEFAULT NULL,
  `evaluation_sources` text,
  `evaluation_methods` text,
  `evaluation_time` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `program_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `council_committee` varchar(200) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
