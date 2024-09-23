INSERT INTO `#__bsms_update` (id, version)
VALUES ('42', '10.0.0-20220921')
ON DUPLICATE KEY UPDATE version = '10.0.0-20220921';

alter table `#__bsms_podcast`
    modify podcast_image_subscribe varchar(255) null;
alter table `#__bsms_podcast`
    modify podcastimage varchar(255) null;
alter table `#__bsms_podcast`
    modify image VARCHAR(255) null;
alter table `#__bsms_series`
    modify series_thumbnail VARCHAR(255) null;
