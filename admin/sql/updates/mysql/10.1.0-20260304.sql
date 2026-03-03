-- Add iTunes configuration columns to podcast table
ALTER TABLE `#__bsms_podcast`
    ADD COLUMN `itunes_category` VARCHAR(100) NOT NULL DEFAULT 'Religion & Spirituality' AFTER `linktype`,
    ADD COLUMN `itunes_subcategory` VARCHAR(100) NOT NULL DEFAULT 'Christianity' AFTER `itunes_category`,
    ADD COLUMN `itunes_explicit` VARCHAR(5) NOT NULL DEFAULT 'false' AFTER `itunes_subcategory`,
    ADD COLUMN `itunes_type` VARCHAR(10) NOT NULL DEFAULT 'episodic' AFTER `itunes_explicit`;
