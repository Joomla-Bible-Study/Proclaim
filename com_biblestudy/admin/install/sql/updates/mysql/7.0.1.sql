-- 7.0.1
INSERT INTO #__bsms_update (id,version) VALUES (2, '7.0.1')
ON DUPLICATE KEY UPDATE version= '7.0.1';

-- removed bad index that came from 6.2.4
ALTER TABLE `r9s7a_bsms_studytopics` DROP INDEX id;
ALTER TABLE `r9s7a_bsms_studytopics` DROP INDEX id_2;