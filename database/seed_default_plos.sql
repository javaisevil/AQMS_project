USE `AQMS_db`;

CREATE TABLE IF NOT EXISTS `program_learning_outcomes` (
  `plo_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `plo_code` varchar(20) NOT NULL,
  `description` text,
  `category` varchar(100) NOT NULL,
  PRIMARY KEY (`plo_id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO program_learning_outcomes (program_id, plo_code, description, category)
SELECT p.program_id, x.plo_code, x.description, x.category
FROM program_specs p
JOIN (
  SELECT 'K1' AS plo_code, 'Program learning outcome K1' AS description, 'Knowledge and Understanding' AS category
  UNION ALL SELECT 'K2', 'Program learning outcome K2', 'Knowledge and Understanding'
  UNION ALL SELECT 'K3', 'Program learning outcome K3', 'Knowledge and Understanding'
  UNION ALL SELECT 'S1', 'Program learning outcome S1', 'Skills'
  UNION ALL SELECT 'S2', 'Program learning outcome S2', 'Skills'
  UNION ALL SELECT 'S3', 'Program learning outcome S3', 'Skills'
  UNION ALL SELECT 'V1', 'Program learning outcome V1', 'Values, Autonomy, and Responsibility'
  UNION ALL SELECT 'V2', 'Program learning outcome V2', 'Values, Autonomy, and Responsibility'
) x
WHERE NOT EXISTS (
  SELECT 1 FROM program_learning_outcomes existing
  WHERE existing.program_id = p.program_id AND existing.plo_code = x.plo_code
);
