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

-- Retire #__bsms_books (#1687).
--
-- 73 rows holding `JBS_BBK_*` language keys, never written by anything, and the
-- same 101-173 -> key map the scripture library already carries in
-- ScriptureHelper::BOOK_KEYS. Every consumer JOINed it and then called Text::_()
-- on the result, so the round trip to the database existed to fetch a
-- translation key. Its `published` column was read only by the two type="SQL"
-- form fields that are now BookList fields.

DROP TABLE IF EXISTS `#__bsms_books`;

-- The flat scripture columns are retired too (#1623), but not from here.
--
-- ⚠️ Joomla's ChangeSet builds a verification check only for ALTER TABLE,
-- CREATE TABLE and RENAME TABLE. An INSERT has no check, so it is skipped and
-- never replayed -- while every DROP COLUMN beside it is checked and IS
-- replayed. Database Maintenance -> Fix, and the 9.x upgrade wizard through
-- CwmupgradeHelper::runSchemaMigration(), would therefore run the drops without
-- the backfill and take every scripture reference with them.
--
-- proclaim.script.php does the whole retirement in postflight instead, where
-- the backfill and the drops cannot be separated:
-- CwmscriptureMigration::retireLegacyColumns().
