ALTER TABLE `#__bsms_mediafiles` MODIFY `docMan_id` varchar(250) NULL;

ALTER TABLE `#__bsms_folders` ADD `server_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER 'id';

ALTER TABLE `#__bsms_folders` MODIFY `server_type`  CHAR(255) NOT NULL DEFAULT 'local';

ALTER TABLE `#__bsms_folders` ADD `params` TEXT NOT NULL AFTER 'aws_secret';
