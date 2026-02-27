-- Remove unused 'text' (Details Link Image) and 'pdf' columns from templates table
-- Neither field was rendered on the frontend
-- @since 10.1.0

ALTER TABLE `#__bsms_templates` DROP COLUMN `text`;
ALTER TABLE `#__bsms_templates` DROP COLUMN `pdf`;