INSERT INTO `#__bsms_update` (id, version) VALUES (8, '7.1.1')
ON DUPLICATE KEY UPDATE version= '7.1.2';

-- Remove old table
DROP TABLE IF EXISTS `#__bsms_search`;
