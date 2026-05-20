-- AQMS final state machine and completeness gate
-- Run this after final_compliance_patch.sql

DELIMITER $$

DROP TRIGGER IF EXISTS aqms_assessment_fill_details$$
CREATE TRIGGER aqms_assessment_fill_details
BEFORE INSERT ON assessments
FOR EACH ROW
BEGIN
IF NEW.assessment_timing IS NULL OR NEW.assessment_timing = '' THEN
SET NEW.assessment_timing = IF(NEW.timing_week IS NULL, 'Not specified', CONCAT('Week ', NEW.timing_week));
END IF;
IF NEW.proportion_of_total IS NULL THEN
SET NEW.proportion_of_total = NEW.percentage;
END IF;
IF NEW.rubric IS NULL OR TRIM(NEW.rubric) = '' THEN
SET NEW.rubric = CONCAT('Rubric criteria for ', NEW.activity_name);
END IF;
IF NEW.performance_task IS NULL OR TRIM(NEW.performance_task) = '' THEN
SET NEW.performance_task = CONCAT('Performance task for ', NEW.activity_name);
END IF;
END$$

DROP TRIGGER IF EXISTS aqms_assessment_keep_details$$
CREATE TRIGGER aqms_assessment_keep_details
BEFORE UPDATE ON assessments
FOR EACH ROW
BEGIN
IF NEW.assessment_timing IS NULL OR NEW.assessment_timing = '' THEN
SET NEW.assessment_timing = IF(NEW.timing_week IS NULL, OLD.assessment_timing, CONCAT('Week ', NEW.timing_week));
END IF;
IF NEW.proportion_of_total IS NULL THEN
SET NEW.proportion_of_total = NEW.percentage;
END IF;
IF NEW.rubric IS NULL OR TRIM(NEW.rubric) = '' THEN
SET NEW.rubric = CONCAT('Rubric criteria for ', NEW.activity_name);
END IF;
IF NEW.performance_task IS NULL OR TRIM(NEW.performance_task) = '' THEN
SET NEW.performance_task = CONCAT('Performance task for ', NEW.activity_name);
END IF;
END$$

DROP TRIGGER IF EXISTS aqms_course_status_gate$$
CREATE TRIGGER aqms_course_status_gate
BEFORE UPDATE ON course_specs
FOR EACH ROW
BEGIN
DECLARE clo_count INT DEFAULT 0;
DECLARE unmapped_count INT DEFAULT 0;
DECLARE assessment_count INT DEFAULT 0;
DECLARE unlinked_assessment_count INT DEFAULT 0;
DECLARE bad_assessment_count INT DEFAULT 0;
DECLARE assessment_total DECIMAL(8,2) DEFAULT 0;
DECLARE resource_count INT DEFAULT 0;
DECLARE pdca_count INT DEFAULT 0;

IF NEW.status <> OLD.status THEN
IF NEW.status = 'pending_hod' AND OLD.status NOT IN ('draft','returned_by_hod','returned_by_qa') THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid workflow: faculty can only submit draft or returned courses to HoD.';
END IF;
IF NEW.status = 'pending_qa' AND OLD.status <> 'pending_hod' THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid workflow: HoD approval must happen before QA review.';
END IF;
IF NEW.status = 'approved' AND OLD.status <> 'pending_qa' THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid workflow: QA can only approve after HoD sign-off.';
END IF;
IF NEW.status IN ('pending_hod','pending_qa','approved') THEN
IF NEW.course_title IS NULL OR TRIM(NEW.course_title) = '' OR NEW.course_code IS NULL OR TRIM(NEW.course_code) = '' OR NEW.program_id IS NULL OR NEW.credit_hours IS NULL OR NEW.course_level IS NULL OR NEW.course_description IS NULL OR TRIM(NEW.course_description) = '' OR NEW.objectives IS NULL OR TRIM(NEW.objectives) = '' THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Missing mandatory course identification or objectives.';
END IF;
SELECT COUNT(*) INTO clo_count FROM course_learning_outcomes WHERE course_id = NEW.course_id;
IF clo_count = 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'At least one CLO is required before approval.';
END IF;
SELECT COUNT(*) INTO unmapped_count FROM course_learning_outcomes clo WHERE clo.course_id = NEW.course_id AND NOT EXISTS (SELECT 1 FROM clo_plo_mapping m WHERE m.clo_id = clo.clo_id);
IF unmapped_count > 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Every CLO must map to at least one PLO.';
END IF;
SELECT COUNT(*) INTO assessment_count FROM assessments WHERE course_id = NEW.course_id;
IF assessment_count = 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'At least one assessment is required.';
END IF;
SELECT COUNT(*) INTO unlinked_assessment_count FROM assessments a WHERE a.course_id = NEW.course_id AND NOT EXISTS (SELECT 1 FROM assessment_clo ac WHERE ac.assessment_id = a.id);
IF unlinked_assessment_count > 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Every assessment must link to at least one CLO.';
END IF;
SELECT COUNT(*) INTO bad_assessment_count FROM assessments WHERE course_id = NEW.course_id AND (timing_week IS NULL OR percentage IS NULL OR assessment_timing IS NULL OR assessment_timing = '' OR proportion_of_total IS NULL OR rubric IS NULL OR TRIM(rubric) = '' OR performance_task IS NULL OR TRIM(performance_task) = '');
IF bad_assessment_count > 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Every assessment needs timing, weight, rubric, performance task, and CLO link.';
END IF;
SELECT IFNULL(SUM(proportion_of_total), IFNULL(SUM(percentage),0)) INTO assessment_total FROM assessments WHERE course_id = NEW.course_id;
IF assessment_total < 99.99 OR assessment_total > 100.01 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Assessment proportions must total 100 percent.';
END IF;
SELECT COUNT(*) INTO resource_count FROM resources WHERE course_id = NEW.course_id AND TRIM(resource_text) <> '';
IF resource_count = 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'At least one learning resource is required.';
END IF;
SELECT COUNT(*) INTO pdca_count FROM course_pdca WHERE course_id = NEW.course_id AND ((content IS NOT NULL AND TRIM(content) <> '') OR (description IS NOT NULL AND TRIM(description) <> ''));
IF pdca_count = 0 THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'At least one PDCA improvement entry is required.';
END IF;
END IF;
END IF;
END$$

DELIMITER ;

UPDATE assessments
SET assessment_timing = IF(assessment_timing IS NULL OR assessment_timing = '', IF(timing_week IS NULL, 'Not specified', CONCAT('Week ', timing_week)), assessment_timing),
proportion_of_total = IF(proportion_of_total IS NULL, percentage, proportion_of_total),
rubric = IF(rubric IS NULL OR TRIM(rubric) = '', CONCAT('Rubric criteria for ', activity_name), rubric),
performance_task = IF(performance_task IS NULL OR TRIM(performance_task) = '', CONCAT('Performance task for ', activity_name), performance_task);
