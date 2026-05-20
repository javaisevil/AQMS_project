USE `AQMS_db`;

ALTER TABLE `assessments`
  ADD COLUMN `rubric` TEXT NULL AFTER `percentage`,
  ADD COLUMN `performance_task` TEXT NULL AFTER `rubric`;
