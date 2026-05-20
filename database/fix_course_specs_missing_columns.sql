USE `AQMS_db`;

SET @db_name = DATABASE();

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'department') = 0,
  'ALTER TABLE course_specs ADD COLUMN department varchar(150) DEFAULT NULL AFTER course_code',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'college') = 0,
  'ALTER TABLE course_specs ADD COLUMN college varchar(150) DEFAULT NULL AFTER department',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'institution') = 0,
  'ALTER TABLE course_specs ADD COLUMN institution varchar(150) DEFAULT ''Al Yamamah University'' AFTER college',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'last_revision_date') = 0,
  'ALTER TABLE course_specs ADD COLUMN last_revision_date date DEFAULT NULL AFTER version',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'required_elective') = 0,
  'ALTER TABLE course_specs ADD COLUMN required_elective enum(''Required'',''Elective'') DEFAULT NULL AFTER course_type',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'due_date') = 0,
  'ALTER TABLE course_specs ADD COLUMN due_date date DEFAULT NULL AFTER objectives',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'submitted_at') = 0,
  'ALTER TABLE course_specs ADD COLUMN submitted_at datetime DEFAULT NULL AFTER due_date',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'course_specs' AND COLUMN_NAME = 'deadline_status') = 0,
  'ALTER TABLE course_specs ADD COLUMN deadline_status enum(''not_due'',''on_time'',''late'',''overdue'') DEFAULT ''not_due'' AFTER submitted_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE course_specs
  MODIFY status enum('draft','pending_hod','returned_by_hod','pending_qa','returned_by_qa','approved','archived') DEFAULT 'draft';

UPDATE course_specs cs
LEFT JOIN program_specs ps ON cs.program_id = ps.program_id
SET
  cs.department = COALESCE(NULLIF(cs.department, ''), ps.department),
  cs.college = COALESCE(NULLIF(cs.college, ''), ps.college),
  cs.institution = 'Al Yamamah University',
  cs.deadline_status = COALESCE(cs.deadline_status, 'not_due');
