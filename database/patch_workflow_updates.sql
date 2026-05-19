USE `AQMS_db`;

ALTER TABLE `course_specs`
  ADD COLUMN `department` varchar(150) DEFAULT NULL AFTER `course_code`,
  ADD COLUMN `college` varchar(150) DEFAULT NULL AFTER `department`,
  ADD COLUMN `institution` varchar(150) DEFAULT 'Al Yamamah University' AFTER `college`,
  ADD COLUMN `last_revision_date` date DEFAULT NULL AFTER `version`,
  ADD COLUMN `required_elective` enum('Required','Elective') DEFAULT NULL AFTER `course_type`,
  ADD COLUMN `due_date` date DEFAULT NULL AFTER `objectives`,
  ADD COLUMN `submitted_at` datetime DEFAULT NULL AFTER `due_date`,
  ADD COLUMN `deadline_status` enum('not_due','on_time','late','overdue') DEFAULT 'not_due' AFTER `submitted_at`;

ALTER TABLE `course_specs`
  MODIFY `status` enum('draft','pending_hod','returned_by_hod','pending_qa','returned_by_qa','approved','archived') DEFAULT 'draft';

ALTER TABLE `course_learning_outcomes`
  MODIFY `category` enum('Knowledge and Understanding','Skills','Values, Autonomy, and Responsibility') NOT NULL;

ALTER TABLE `program_learning_outcomes`
  MODIFY `category` enum('Knowledge and Understanding','Skills','Values, Autonomy, and Responsibility') NOT NULL;

DROP TABLE IF EXISTS `course_pdca`;
CREATE TABLE `course_pdca` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `phase` enum('Plan','Do','Check','Act') NOT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_pdca_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `course_facilities`;
CREATE TABLE `course_facilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `item` enum('facilities','Technology equipment','Other equipment') NOT NULL,
  `resources` text,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_facilities_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `course_quality`;
CREATE TABLE `course_quality` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `assessment_area` enum('Effectiveness of teaching','Effectiveness of Students assessment','Quality of learning resources','The extent to which CLOs have been achieved','Other') NOT NULL,
  `assessor` varchar(200) DEFAULT NULL,
  `assessment_method` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_quality_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `course_approval`;
CREATE TABLE `course_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `council_committee` varchar(200) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`),
  CONSTRAINT `course_approval_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_specs` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `resources`
  MODIFY `category` enum('Essential References','Supportive References','Electronic Materials','Other Learning Materials') NOT NULL;

ALTER TABLE `teaching_modes`
  MODIFY `mode_type` enum('Traditional classroom','E-learning','Hybrid','Distance learning') NOT NULL;

ALTER TABLE `contact_hours`
  MODIFY `activity_type` enum('Lectures','Laboratory/Studio','Field','Tutorial','Others') NOT NULL;

UPDATE `program_learning_outcomes` SET `category` = 'Knowledge and Understanding' WHERE `category` = 'Knowledge';
UPDATE `program_learning_outcomes` SET `category` = 'Values, Autonomy, and Responsibility' WHERE `category` = 'Values';
UPDATE `course_learning_outcomes` SET `category` = 'Knowledge and Understanding' WHERE `category` = 'Knowledge';
UPDATE `course_learning_outcomes` SET `category` = 'Values, Autonomy, and Responsibility' WHERE `category` = 'Values';
