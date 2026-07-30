-- Repair Font Awesome brand icons that 10.3.0-20260322.sql damaged.
--
-- That migration rewrote the FA4 prefix in stored params:
--
--     REPLACE(params, '"fa fa-', '"fa-solid fa-')
--
-- which is wrong for brand icons. The family comes from the style class, not the
-- icon name: .fa-solid selects "Font Awesome 6 Free" at weight 900, and the Free
-- font has no glyph at a brand codepoint. So "fa-solid fa-youtube" renders as
-- tofu even though .fa-youtube exists and carries the right codepoint.
--
-- Worse, the FA4 value was working before the rewrite. Joomla's v4 shim
--
--     .fa.fa-youtube { font-family: "Font Awesome 6 Brands"; font-weight: 400 }
--
-- keys on the bare .fa class that the rewrite strips.
--
-- Separately, some FA4 names exist ONLY as shims. fa-video-camera has no plain
-- rule in joomla-fontawesome.css, only .fa.fa-video-camera, so re-prefixing it
-- was never enough — it has to become the FA6 name fa-video.
--
-- Each pattern below includes the closing quote so it matches a whole JSON value.
-- That matters: fa-vimeo is a prefix of fa-vimeo-v, and an unanchored REPLACE
-- would corrupt the latter while fixing the former.
--
-- This migration deliberately repeats the FA4/FA5 prefix conversions from
-- 10.3.0-20260322.sql instead of assuming they ran. On a site whose database was
-- imported into a fresh install, Joomla stamps #__schemas with the newest update
-- file without executing the intermediate ones — so the recorded version claims
-- the conversion happened while the data shows otherwise. Observed on a real
-- install reporting 10.3.3-20260711 with 1,378 rows still on legacy prefixes and
-- only 2 on FA6. All statements are idempotent, so re-running them is harmless.

-- ---------------------------------------------------------------------------
-- Step 1: FA4/FA5 prefixes -> FA6 style classes (repeat of 10.3.0, see above)
-- ---------------------------------------------------------------------------

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fab ', '"fa-brands ')
WHERE `params` LIKE '%"fab %';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fas ', '"fa-solid ')
WHERE `params` LIKE '%"fas %';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"far ', '"fa-regular ')
WHERE `params` LIKE '%"far %';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa fa-', '"fa-solid fa-')
WHERE `params` LIKE '%"fa fa-%';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fab ', '"fa-brands ')
WHERE `media` LIKE '%"fab %';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fas ', '"fa-solid ')
WHERE `media` LIKE '%"fas %';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fa fa-', '"fa-solid fa-')
WHERE `media` LIKE '%"fa fa-%';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fab ', '"fa-brands ')
WHERE `params` LIKE '%"fab %';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fas ', '"fa-solid ')
WHERE `params` LIKE '%"fas %';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fa fa-', '"fa-solid fa-')
WHERE `params` LIKE '%"fa fa-%';

-- ---------------------------------------------------------------------------
-- Step 2: brand icons wrongly left on a non-brand family by step 1
-- ---------------------------------------------------------------------------

-- Media files: media_icon_type stored in params JSON
UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-youtube"', '"fa-brands fa-youtube"')
WHERE `params` LIKE '%"fa-solid fa-youtube"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-youtube-play"', '"fa-brands fa-youtube"')
WHERE `params` LIKE '%"fa-solid fa-youtube-play"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-youtube-square"', '"fa-brands fa-square-youtube"')
WHERE `params` LIKE '%"fa-solid fa-youtube-square"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-vimeo-v"', '"fa-brands fa-vimeo-v"')
WHERE `params` LIKE '%"fa-solid fa-vimeo-v"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-vimeo"', '"fa-brands fa-vimeo"')
WHERE `params` LIKE '%"fa-solid fa-vimeo"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-vimeo-square"', '"fa-brands fa-square-vimeo"')
WHERE `params` LIKE '%"fa-solid fa-vimeo-square"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa-solid fa-video-camera"', '"fa-solid fa-video"')
WHERE `params` LIKE '%"fa-solid fa-video-camera"%';

-- Any FA4 brand values the 10.3.0 migration never reached (e.g. rows written
-- afterwards by an older form, or an install that skipped that update)
UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa fa-youtube"', '"fa-brands fa-youtube"')
WHERE `params` LIKE '%"fa fa-youtube"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa fa-vimeo"', '"fa-brands fa-vimeo"')
WHERE `params` LIKE '%"fa fa-vimeo"%';

UPDATE `#__bsms_mediafiles`
SET `params` = REPLACE(`params`, '"fa fa-video-camera"', '"fa-solid fa-video"')
WHERE `params` LIKE '%"fa fa-video-camera"%';

-- Server media defaults
UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fa-solid fa-youtube"', '"fa-brands fa-youtube"')
WHERE `media` LIKE '%"fa-solid fa-youtube"%';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fa-solid fa-vimeo-v"', '"fa-brands fa-vimeo-v"')
WHERE `media` LIKE '%"fa-solid fa-vimeo-v"%';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fa-solid fa-vimeo"', '"fa-brands fa-vimeo"')
WHERE `media` LIKE '%"fa-solid fa-vimeo"%';

UPDATE `#__bsms_servers`
SET `media` = REPLACE(`media`, '"fa-solid fa-video-camera"', '"fa-solid fa-video"')
WHERE `media` LIKE '%"fa-solid fa-video-camera"%';

-- Admin params
UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fa-solid fa-youtube"', '"fa-brands fa-youtube"')
WHERE `params` LIKE '%"fa-solid fa-youtube"%';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fa-solid fa-vimeo-v"', '"fa-brands fa-vimeo-v"')
WHERE `params` LIKE '%"fa-solid fa-vimeo-v"%';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fa-solid fa-vimeo"', '"fa-brands fa-vimeo"')
WHERE `params` LIKE '%"fa-solid fa-vimeo"%';

UPDATE `#__bsms_admin`
SET `params` = REPLACE(`params`, '"fa-solid fa-video-camera"', '"fa-solid fa-video"')
WHERE `params` LIKE '%"fa-solid fa-video-camera"%';