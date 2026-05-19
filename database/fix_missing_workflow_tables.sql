USE `AQMS_db`;

CREATE TABLE IF NOT EXISTS `course_facilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `item` enum('facilities','Technology equipment','Other equipment') NOT NULL,
  `resources` text,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_facilities_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_quality` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `assessment_area` enum('Effectiveness of teaching','Effectiveness of Students assessment','Quality of learning resources','The extent to which CLOs have been achieved','Other') NOT NULL,
  `assessor` varchar(200) DEFAULT NULL,
  `assessment_method` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_quality_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `council_committee` varchar(200) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`),
  CONSTRAINT `course_approval_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_pdca` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `phase` enum('Plan','Do','Check','Act') NOT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_pdca_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
