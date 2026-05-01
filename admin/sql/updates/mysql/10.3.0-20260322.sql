-- Migrate Font Awesome 5 shorthand prefixes to FA6 canonical in stored params
-- Covers: #__bsms_mediafiles.params, #__bsms_servers.media, #__bsms_admin.params

-- Media files: media_icon_type stored in params JSON
UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fas ', '"fa-solid ')
WHERE `params` LIKE '%"fas %';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fab ', '"fa-brands ')
WHERE `params` LIKE '%"fab %';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"far ', '"fa-regular ')
WHERE `params` LIKE '%"far %';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa fa-', '"fa-solid fa-')
WHERE `params` LIKE '%"fa fa-%';

-- Server media defaults
UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fas ', '"fa-solid ')
WHERE `media` LIKE '%"fas %';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fab ', '"fa-brands ')
WHERE `media` LIKE '%"fab %';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"far ', '"fa-regular ')
WHERE `media` LIKE '%"far %';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fa fa-', '"fa-solid fa-')
WHERE `media` LIKE '%"fa fa-%';

-- Admin params
UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fas ', '"fa-solid ')
WHERE `params` LIKE '%"fas %';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fab ', '"fa-brands ')
WHERE `params` LIKE '%"fab %';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"far ', '"fa-regular ')
WHERE `params` LIKE '%"far %';

-- Rename deprecated FA5 icon names to FA6 canonical in all three tables
-- media_icon_type values in mediafiles
UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-play-circle', 'fa-circle-play')
WHERE `params` LIKE '%fa-play-circle%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-file-alt', 'fa-file-lines')
WHERE `params` LIKE '%fa-file-alt%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-shopping-cart', 'fa-cart-shopping')
WHERE `params` LIKE '%fa-shopping-cart%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-external-link-alt', 'fa-up-right-from-square')
WHERE `params` LIKE '%fa-external-link-alt%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-check-circle', 'fa-circle-check')
WHERE `params` LIKE '%fa-check-circle%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-info-circle', 'fa-circle-info')
WHERE `params` LIKE '%fa-info-circle%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-exclamation-triangle', 'fa-triangle-exclamation')
WHERE `params` LIKE '%fa-exclamation-triangle%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-map-marker-alt', 'fa-location-dot')
WHERE `params` LIKE '%fa-map-marker-alt%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, 'fa-bible', 'fa-book-bible')
WHERE `params` LIKE '%fa-bible%';

-- Server media defaults (same renames)
UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, 'fa-play-circle', 'fa-circle-play')
WHERE `media` LIKE '%fa-play-circle%';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, 'fa-file-alt', 'fa-file-lines')
WHERE `media` LIKE '%fa-file-alt%';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, 'fa-shopping-cart', 'fa-cart-shopping')
WHERE `media` LIKE '%fa-shopping-cart%';

-- Template params may also contain icon references
UPDATE `#__bsms_templates`
SET `params` = REPLACE(`params`, '"fas ', '"fa-solid ')
WHERE `params` LIKE '%"fas %';

UPDATE `#__bsms_templates`
SET `params` = REPLACE(`params`, '"fab ', '"fa-brands ')
WHERE `params` LIKE '%"fab %';

UPDATE `#__bsms_templates`
SET `params` = REPLACE(`params`, '"far ', '"fa-regular ')
WHERE `params` LIKE '%"far %';

-- Add organization name column to teachers table (issue #1198)
ALTER TABLE `#__bsms_teachers` ADD COLUMN `org_name` VARCHAR(255) DEFAULT NULL AFTER `title`;
