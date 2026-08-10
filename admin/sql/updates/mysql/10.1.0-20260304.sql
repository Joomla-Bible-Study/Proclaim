-- Add iTunes configuration columns to podcast table.
--
-- One ALTER per column. As a single four-clause statement, Joomla's schema check
-- built its check query from the first clause only, so itunes_category was
-- verified and the other three were invisible — a database missing them would be
-- reported as up to date, and the schema fix would never add them (#1664).
ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `itunes_category` VARCHAR(100) NOT NULL DEFAULT 'Religion & Spirituality' AFTER `linktype`;

ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `itunes_subcategory` VARCHAR(100) NOT NULL DEFAULT 'Christianity' AFTER `itunes_category`;

ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `itunes_explicit` VARCHAR(5) NOT NULL DEFAULT 'false' AFTER `itunes_subcategory`;

ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `itunes_type` VARCHAR(10) NOT NULL DEFAULT 'episodic' AFTER `itunes_explicit`;
