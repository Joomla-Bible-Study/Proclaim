ALTER TABLE `#__bsms_teachers`
    ADD COLUMN `user_id` INT(10) UNSIGNED DEFAULT NULL AFTER `contact`,
    ADD KEY `idx_teacher_user` (`user_id`);
