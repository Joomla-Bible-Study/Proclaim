--
-- Teacher table cleanup: drop legacy columns.
-- Data cleanup (merge duplicates, deduplicate aliases, UNIQUE KEY) is handled by the
-- fixTeacherAliases() PHP finish step because:
--   1. Joomla ChangeSet skips UPDATE/DELETE/CREATE TEMPORARY TABLE statements
--   2. Proclaim's migration system uses 2-second AJAX batches; TEMPORARY TABLEs
--      do not survive across the resulting separate database connections
--

-- Drop legacy image/dimension columns superseded by teacher_thumbnail system
ALTER TABLE `#__bsms_teachers` DROP COLUMN `imageh`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `imagew`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `thumb`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `thumbw`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `thumbh`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `address1`;
ALTER TABLE `#__bsms_teachers` DROP COLUMN `catid`;
