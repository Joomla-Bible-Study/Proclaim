alter table `#__bsms_podcast`
    modify podcast_image_subscribe varchar(255) DEFAULT NULL;
alter table `#__bsms_podcast`
    modify podcastimage varchar(255) DEFAULT NULL;
alter table `#__bsms_podcast`
    modify image VARCHAR(255) DEFAULT NULL;
alter table `#__bsms_series`
    modify series_thumbnail VARCHAR(255) DEFAULT NULL;
alter table `#__bsms_podcast`
    add column `podcastlink` VARCHAR(100) DEFAULT NULL AFTER `website`;