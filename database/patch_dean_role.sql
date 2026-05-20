USE `AQMS_db`;

ALTER TABLE `user`
  MODIFY `role` enum('faculty','hod','dean','qa') NOT NULL;

INSERT INTO `user` (`username`, `password`, `role`, `full_name`, `department`)
SELECT 'dean1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3V6G3FvL3R8Q0Sg0g0mFQpO7dVQK', 'dean', 'Department Dean', 'Software Engineering'
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE `username` = 'dean1');
