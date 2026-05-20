USE `AQMS_db`;

ALTER TABLE `course_specs`
  ADD COLUMN IF NOT EXISTS `department` varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `college` varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `institution` varchar(150) DEFAULT 'Al Yamamah University',
  ADD COLUMN IF NOT EXISTS `last_revision_date` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `required_elective` varchar(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `due_date` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `submitted_at` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `deadline_status` varchar(50) DEFAULT 'not_due';

ALTER TABLE `course_specs`
  MODIFY `status` enum('draft','pending_hod','returned_by_hod','pending_qa','returned_by_qa','approved','archived') DEFAULT 'draft';

CREATE TABLE IF NOT EXISTS `course_facilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `item` varchar(100) NOT NULL,
  `resources` text,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_quality` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `assessment_area` varchar(200) NOT NULL,
  `assessor` varchar(200) DEFAULT NULL,
  `assessment_method` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `council_committee` varchar(200) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `course_pdca` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `phase` varchar(30) NOT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `assessments`
  ADD COLUMN IF NOT EXISTS `rubric` text NULL,
  ADD COLUMN IF NOT EXISTS `performance_task` text NULL;

UPDATE `program_learning_outcomes` SET `category` = 'Knowledge and Understanding' WHERE `category` = 'Knowledge';
UPDATE `program_learning_outcomes` SET `category` = 'Values, Autonomy, and Responsibility' WHERE `category` = 'Values';
UPDATE `course_learning_outcomes` SET `category` = 'Knowledge and Understanding' WHERE `category` = 'Knowledge';
UPDATE `course_learning_outcomes` SET `category` = 'Values, Autonomy, and Responsibility' WHERE `category` = 'Values';
