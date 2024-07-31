alter table `#__bsms_podcast`
    modify podcast_image_subscribe varchar(255) null;
alter table `#__bsms_podcast`
    modify podcastimage varchar(255) null;
alter table `#__bsms_podcast`
    modify image VARCHAR(255) null;
alter table `#__bsms_series`
    modify series_thumbnail VARCHAR(255) null;
DROP PROCEDURE IF EXISTS `addit`;
DELIMITER //
CREATE PROCEDURE `addit`()
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    ALTER TABLE `#__bsms_series` ADD COLUMN `subtitle` TEXT;
END //
DELIMITER ;
CALL `addit`();
DROP PROCEDURE `addit`;