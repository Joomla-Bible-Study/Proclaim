-- Add organization name field to teachers for Schema.org worksFor override
ALTER TABLE `#__bsms_teachers` ADD COLUMN `org_name` VARCHAR(255) DEFAULT NULL AFTER `title`;
