-- Retire the CD/DVD production set and studytext2 from #__bsms_studies (#1690).
--
-- Seven columns that are declared and never used: no form field, no model, no
-- query, no importer, no view. A production set from when messages shipped on
-- physical media, plus a second study-text field, carried on every row since.
-- Empty on every database available, including one with 827 imported studies.
--
-- ⚠️ One statement per column, deliberately. Joomla's MysqlChangeItem reads only
-- words 3 and 4 of a statement, so a compound
-- `ALTER TABLE x DROP COLUMN a, DROP COLUMN b` registers as a single
-- "DROP COLUMN a" — every clause after the first is neither checked by Database
-- Maintenance nor repairable from it. Same trap as #1664.

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `prod_dvd`;

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `prod_cd`;

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `server_cd`;

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `server_dvd`;

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `image_cd`;

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `image_dvd`;

ALTER TABLE `#__bsms_studies`
    DROP COLUMN `studytext2`;
