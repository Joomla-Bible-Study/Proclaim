<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

/**
 * Update for 7.1.0 class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class JBS710Update {

    /**
     * Method to Update to 7.1.0
     * @return boolean
     */
    function update710() {
        $db = JFactory::getDBO();
        //fix some css from 701 to 702
        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update702.php');
        $update702 = new JBS702Update();
        $update702css = $update702->css702();
        $oldcss = '';
        jimport('joomla.filesystem.file');
        //Check to see if there is an existing css
        $src = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'biblestudy.css';
        $dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'biblestudy.css';
        if (!JFile::exists($dest)) {
            //if there is no new css file in the media folder, check to see if there is one in the old assets or in the backup folder
            if (JFile::exists($src)) {
                $oldcss = JFile::read($src);
            }
            //There is no existing css so let us check for a backup
            $backup = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'biblestudy.css';
            if (JFile::exists($backup)) {
                $oldcss = JFile::read($backup);
            }
            if ($oldcss) {

                $query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
                $db->setQuery($query);
                $db->query();
                $result = $db->loadObject();
                if ($result) {
                    $query = 'UPDATE #__bsms_styles SET `stylecode` = "' . $oldcss . '" WHERE `id` = ' . $result->id;
                    $db->setQuery($query);
                    $db->query();
                    if (!JFile::write($dest, $oldcss)) {
                        return false;
                    }
                }
            } else {
                //No css or backup found so we get the default and write a file with it to the new location
                $query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
                $db->setQuery($query);
                $db->query();
                $result = $db->loadObject();
                $newcss = $result->stylecode;
                if ($result) {
                    if (!JFile::write($dest, $newcss)) {
                        return false;
                    }
                }
            }
        } //end if no new css file
        //Add CSS to the file
        $new710css = '
/* Terms of use or donate display settings */
.termstext {
}

.termslink{
}
/* Podcast Subscription Display Settings */

.podcastsubscribe{
clear:both;
display:table;
width:auto;
background-color:#eee;
border: 1px solid grey;
padding: 1em;
}
.image {
display: inline;
}
.image .text {
display:inline;
position:relative;
right:50px;
bottom:-10px;
}
.prow {
  display: table-row;
 width:auto;
  clear:both;
}
.pcell {
  display: table-cell;
  float:left;/*fix for  buggy browsers*/
    }
.podcastheader h3{
display:table-header;
text-align:center;
}/* Listing Page Items */
#subscribelinks {

}
.podcastheader{
font-weight: bold;
}


.podcastlinks{
  display: inline;

}

';
        $query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
        $db->setQuery($query);
        $db->query();
        $result = $db->loadObject();
        $oldcss = $result->stylecode;
        $newcss = $new710css . ' ' . $oldcss;
        $query = 'UPDATE #__bsms_styles SET stylecode=' . $newcss . ' where `filename` = "biblestudy"';
        $db->setQuery($query);
        $db->query();
        if (!JFile::write($dest, $newcss)) {
            return false;
        }
    }

}