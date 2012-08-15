-- 7.0.1
INSERT INTO #__bsms_update (id,version) VALUES (2, '7.0.1')
ON DUPLICATE KEY UPDATE version= '7.0.1';

ALTER TABLE `#__bsms_studytopics` DROP INDEX id;
ALTER TABLE `#__bsms_studytopics` DROP INDEX id_2;