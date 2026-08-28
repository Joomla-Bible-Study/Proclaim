DROP TABLE IF EXISTS `#__bsms_install`;
DROP TABLE IF EXISTS `#__bsms_storage`;
DROP TABLE IF EXISTS `#__bsms_studies`;
DROP TABLE IF EXISTS `#__bsms_teachers`;
DROP TABLE IF EXISTS `#__bsms_topics`;
DROP TABLE IF EXISTS `#__bsms_servers`;
DROP TABLE IF EXISTS `#__bsms_series`;
DROP TABLE IF EXISTS `#__bsms_message_type`;
DROP TABLE IF EXISTS `#__bsms_folders`;
DROP TABLE IF EXISTS `#__bsms_media`;
DROP TABLE IF EXISTS `#__bsms_order`;
DROP TABLE IF EXISTS `#__bsms_podcast`;
DROP TABLE IF EXISTS `#__bsms_mimetype`;
DROP TABLE IF EXISTS `#__bsms_mediafiles`;
DROP TABLE IF EXISTS `#__bsms_templates`;
DROP TABLE IF EXISTS `#__bsms_templatecode`;
DROP TABLE IF EXISTS `#__bsms_comments`;
DROP TABLE IF EXISTS `#__bsms_admin`;
DROP TABLE IF EXISTS `#__bsms_studytopics`;
DROP TABLE IF EXISTS `#__bsms_version`;
DROP TABLE IF EXISTS `#__bsms_locations`;
DROP TABLE IF EXISTS `#__bsms_playlist_items`;
DROP TABLE IF EXISTS `#__bsms_playlists`;
DROP TABLE IF EXISTS `#__bsms_timeset`;
DROP TABLE IF EXISTS `#__bsms_install`;
DROP TABLE IF EXISTS `#__bsms_search`;
DROP TABLE IF EXISTS `#__bsms_share`;
DROP TABLE IF EXISTS `#__bsms_storage`;
DROP TABLE IF EXISTS `#__bsms_styles`;
DROP TABLE IF EXISTS `#__bsms_platform_stats`;
DROP TABLE IF EXISTS `#__bsms_analytics_events`;
DROP TABLE IF EXISTS `#__bsms_analytics_monthly`;

-- Created by install.mysql.utf8.sql and never dropped here. Found by the
-- uninstall test (#1983): an uninstall with drop_tables enabled left these
-- three behind, so a reinstall met tables it did not create.
--
-- The #__bsms_bible_* and #__bsms_scripture_* tables are deliberately NOT here:
-- lib_cwmscripture owns them and other extensions may still be reading them.
DROP TABLE IF EXISTS `#__bsms_study_teachers`;
DROP TABLE IF EXISTS `#__bsms_study_scriptures`;
DROP TABLE IF EXISTS `#__bsms_podcast_download_log`;
