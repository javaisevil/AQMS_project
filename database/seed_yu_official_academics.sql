USE `AQMS_db`;

SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'program_specs' AND COLUMN_NAME = 'institution') = 0,
  'ALTER TABLE program_specs ADD COLUMN institution varchar(150) DEFAULT ''Al Yamamah University'' AFTER college',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'program_specs' AND COLUMN_NAME = 'qualification_level') = 0,
  'ALTER TABLE program_specs ADD COLUMN qualification_level varchar(100) DEFAULT NULL AFTER program_code',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE program_specs SET
  program_code = 'SWE',
  program_name = 'Software Engineering',
  department = 'Computer Engineering Department',
  college = 'College of Engineering',
  institution = 'Al Yamamah University',
  qualification_level = 'Undergraduate'
WHERE program_id = 1;

INSERT INTO program_specs (program_id, program_code, program_name, department, college, institution, qualification_level) VALUES
(2, 'ACC', 'Accounting', 'Accounting & Finance Department', 'College of Business', 'Al Yamamah University', 'Undergraduate'),
(3, 'FIN', 'Finance', 'Accounting & Finance Department', 'College of Business', 'Al Yamamah University', 'Undergraduate'),
(4, 'MGT', 'Management', 'Management and Marketing Department', 'College of Business', 'Al Yamamah University', 'Undergraduate'),
(5, 'MKT', 'Marketing', 'Management and Marketing Department', 'College of Business', 'Al Yamamah University', 'Undergraduate'),
(6, 'MIS', 'Management Information Systems', 'Management Information Systems', 'College of Business', 'Al Yamamah University', 'Undergraduate'),
(7, 'AIA', 'Architecture', 'Architecture Department', 'College of Engineering', 'Al Yamamah University', 'Undergraduate'),
(8, 'CNE', 'Computer Network Engineering', 'Computer Engineering Department', 'College of Engineering', 'Al Yamamah University', 'Undergraduate'),
(9, 'IE', 'Industrial Engineering', 'Industrial Engineering Department', 'College of Engineering', 'Al Yamamah University', 'Undergraduate'),
(10, 'LL.B', 'Bachelor of Law', 'Law Department', 'College of Law', 'Al Yamamah University', 'Undergraduate'),
(11, 'MCS', 'Master in Cyber Security', 'Computer Engineering Department', 'College of Engineering', 'Al Yamamah University', 'Postgraduate'),
(12, 'MBA', 'Masters of Business Administration (MBA)', 'Management and Marketing Department', 'College of Business', 'Al Yamamah University', 'Postgraduate'),
(13, 'EMBA', 'Executive Masters of Business Administration (EMBA)', 'Management and Marketing Department', 'College of Business', 'Al Yamamah University', 'Postgraduate'),
(14, 'MBL', 'Masters of Business Law', 'Law Department', 'College of Law', 'Al Yamamah University', 'Postgraduate')
ON DUPLICATE KEY UPDATE
  program_code = VALUES(program_code),
  program_name = VALUES(program_name),
  department = VALUES(department),
  college = VALUES(college),
  institution = VALUES(institution),
  qualification_level = VALUES(qualification_level);
