USE `AQMS_db`;

-- MySQL-compatible final patch bundle.
-- This avoids ADD COLUMN IF NOT EXISTS because some MySQL/phpMyAdmin versions reject that syntax.

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'department') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `department` varchar(150) DEFAULT NULL', 'SELECT "department exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'college') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `college` varchar(150) DEFAULT NULL', 'SELECT "college exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'institution') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `institution` varchar(150) DEFAULT "Al Yamamah University"', 'SELECT "institution exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'last_revision_date') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `last_revision_date` date DEFAULT NULL', 'SELECT "last_revision_date exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'required_elective') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `required_elective` varchar(50) DEFAULT NULL', 'SELECT "required_elective exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'due_date') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `due_date` date DEFAULT NULL', 'SELECT "due_date exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'submitted_at') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `submitted_at` datetime DEFAULT NULL', 'SELECT "submitted_at exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'deadline_status') = 0, 'ALTER TABLE `course_specs` ADD COLUMN `deadline_status` varchar(50) DEFAULT "not_due"', 'SELECT "deadline_status exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

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

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assessments' AND COLUMN_NAME = 'rubric') = 0, 'ALTER TABLE `assessments` ADD COLUMN `rubric` text NULL', 'SELECT "rubric exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assessments' AND COLUMN_NAME = 'performance_task') = 0, 'ALTER TABLE `assessments` ADD COLUMN `performance_task` text NULL', 'SELECT "performance_task exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `program_learning_outcomes` SET `category` = 'Knowledge and Understanding' WHERE `category` = 'Knowledge';
UPDATE `program_learning_outcomes` SET `category` = 'Values, Autonomy, and Responsibility' WHERE `category` = 'Values';
UPDATE `course_learning_outcomes` SET `category` = 'Knowledge and Understanding' WHERE `category` = 'Knowledge';
UPDATE `course_learning_outcomes` SET `category` = 'Values, Autonomy, and Responsibility' WHERE `category` = 'Values';
