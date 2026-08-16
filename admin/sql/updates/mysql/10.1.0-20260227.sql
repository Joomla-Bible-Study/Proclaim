-- Phase 10: Add location_id to templates and templatecode for multi-campus support
ALTER TABLE `#__bsms_templates`
    ADD COLUMN `location_id` INT(3) DEFAULT NULL AFTER `access` /** CAN FAIL **/;

ALTER TABLE `#__bsms_templates`
    ADD KEY `idx_template_location` (`location_id`) /** CAN FAIL **/;

ALTER TABLE `#__bsms_templatecode`
    ADD COLUMN `location_id` INT(3) DEFAULT NULL AFTER `published` /** CAN FAIL **/;

ALTER TABLE `#__bsms_templatecode`
    ADD KEY `idx_templatecode_location` (`location_id`) /** CAN FAIL **/;
