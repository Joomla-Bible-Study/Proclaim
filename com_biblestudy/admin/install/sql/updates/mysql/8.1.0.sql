INSERT INTO `#__bsms_update` (id,version) VALUES (10,'8.1.0')
ON DUPLICATE KEY UPDATE version= '8.1.0';

ALTER TABLE `#__bsms_topics` ADD `language` char(70) DEFAULT '*'