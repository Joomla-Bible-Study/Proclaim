--
-- Menu Icon Corections
--
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbscmncombiblestudy;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnucontrolpanel;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnumediaimages;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnutemplatedisplay;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnusocialnetworklinks;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnupodcasts;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnuserverfolders;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnuservers;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnustudycomments;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnutopics;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnulocations;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnumessagetypes;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnuseries;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnuteachers;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnumediafiles;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnustudies;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnuadministration;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnucssedit;
UPDATE `#__menu` SET `img` = '../components/com_biblestudy/images/menu/icon-16-biblemenu.png' WHERE `#__menu`.`alias` =jbsmnumimetypes;

--
-- Table Index Addtions
--

-- MimeType
ALTER TABLE `#__bsms_mimetype` ADD INDEX `idx_state` ( `published` ),
ADD INDEX `idx_access` ( `access` ),
ADD `ordering` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `access` `access` INT( 10 ) UNSIGNED NULL DEFAULT '0';
CHANGE `published` `published` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';

--Messege Type
ALTER TABLE `#__bsms_message_type` ADD INDEX `idx_state` ( `published` ),
ADD INDEX `idx_access` ( `access` ),
ADD `ordering` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `access` `access` INT( 10 ) UNSIGNED NULL DEFAULT '0';

--Locations Type
ALTER TABLE `#__bsms_locations` ADD INDEX `idx_state` ( `published` ),
ADD INDEX `idx_access` ( `access` ),
ADD `ordering` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `access` `access` INT( 10 ) UNSIGNED NULL DEFAULT '0';

