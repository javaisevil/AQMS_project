-- AQMS TP-151 completion patch
-- Import this once after your current database, or add this table to aqms_final_database.sql.

USE aqms;

CREATE TABLE IF NOT EXISTS program_plo_course_mapping (
id int AUTO_INCREMENT PRIMARY KEY,
program_id int NOT NULL,
course_plan_id int NOT NULL,
plo_id int NOT NULL,
performance_level enum('I','P','M') NOT NULL,
created_at timestamp DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY unique_program_course_plo (program_id, course_plan_id, plo_id),
FOREIGN KEY (program_id) REFERENCES program_specs(program_id) ON DELETE CASCADE,
FOREIGN KEY (course_plan_id) REFERENCES program_course_plan(id) ON DELETE CASCADE,
FOREIGN KEY (plo_id) REFERENCES program_learning_outcomes(plo_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
