USE `AQMS_db`;

ALTER TABLE course_learning_outcomes
  MODIFY category varchar(150) NOT NULL;

ALTER TABLE program_learning_outcomes
  MODIFY category varchar(150) NOT NULL;
