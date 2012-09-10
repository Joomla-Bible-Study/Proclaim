<?php

/**
 * Update for 7.1.0
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
 * @since 7.1.0
 */
class JBS710Update {

    /**
     * Method to Update to 7.1.0
     * @return boolean
     */
    public function update710() {
        $db = JFactory::getDBO();
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
/* New Teacher Codes */
#bsm_teachertable_list .bsm_teachername
  {
    font-weight: bold;
    font-size: 14px;
    color: #000000;
    white-space:nowrap;

  }
#bsm_teachertable_list
  {
  margin: 0;
   border-collapse:separate;
  }
#bsm_teachertable_list td {
  text-align:left;
  padding:0 5px 0 5px;
  border:none;
}
#bsm_teachertable_list .titlerow
  {
    border-bottom: thick;
  }
#bsm_teachertable_list .title
  {
    font-size:18px;
    font-weight:bold;
    border-bottom: 3px solid #999999;
    padding: 4px 0px 4px 4px;
  }
#bsm_teachertable_list .bsm_separator
  {
  border-bottom: 1px solid #999999;
  }

.bsm_teacherthumbnail_list
  {

  }
#bsm_teachertable_list .bsm_teacheremail
  {
    font-weight:normal;
    font-size: 11px;
  }
#bsm_teachertable_list .bsm_teacherwebsite
  {
    font-weight:normal;
    font-size: 11px;
  }
#bsm_teachertable_list .bsm_teacherphone
  {
    font-weight:normal;
    font-size: 11px;
  }
#bsm_teachertable_list .bsm_short
  {
    padding: 8px 4px 4px;
  }
#bsm_teachertable .bsm_studiestitlerow {
  background-color: #666;
}
#bsm_teachertable_list .bsm_titletitle
  {
    font-weight:bold;
    color:#FFFFFF;
  }
#bsm_teachertable_list .bsm_titlescripture
  {
    font-weight:bold;
    color:#FFFFFF;
  }
#bsm_teachertable_list .bsm_titledate
  {
    font-weight:bold;
    color:#FFFFFF;
  }
#bsm_teachertable_list .bsm_teacherlong
{
  padding: 8px 4px 4px;
  border-bottom: 1px solid #999999;
}
#bsm_teachertable_list tr.bsodd {
  background-color:#FFFFFF;
  border-bottom: 1px solid #999999;
}
#bsm_teachertable_list tr.bseven {
  background-color:#FFFFF0;
  border-bottom: 1px solid #999999;
}

#bsm_teachertable_list .lastrow td {
  border-bottom:1px solid grey;
  padding-bottom:7px;
  padding-top:7px;
}
#bsm_teachertable_list .bsm_teacherfooter
  {
    border-top: 1px solid #999999;
    padding: 4px 1px 1px 4px;
  }
/* New Teacher Details Codes */

#bsm_teachertable .teacheraddress{
text-align:left;
}

#bsm_teachertable .teacherwebsite{
text-align:left;}

#bsm_teachertable .teacherfacebook{
text-align:left;
}

#bsm_teachertable .bsm_teachertwitter{
text-align:left;
}

#bsm_teachertable .bsm_teacherblog{
text-align:left;
}

#bsm_teachertable .bsm_teacherlink1{
text-align:left;
}


/* New Landing Page CSS */

.landingtable {
clear:both;
width:auto;
display:table;

}

.landingrow {
display:inline;
padding: 1em;
}
.landingcell {
display:table-cell;
}

.landinglink a{
display:inline;
}

/* Terms of use or donate display settings */
.termstext {
}

.termslink{
}
/* Podcast Subscription Display Settings */

.podcastsubscribe{
    clear:both;
    display:table;
    width:100%;
    background-color:#eee;
    border-radius: 15px 15px 15px 15px;
    border: 1px solid grey;
    padding: 1em;
}
.podcastsubscribe .image {
    float: left;
    padding-right: 5px;
    display: inline;
}
.podcastsubscribe .image .text {
    display:inline;
    position:relative;
    right:50px;
    bottom:-10px;
}
.podcastsubscribe .prow {
    display: table-row;
    width:auto;
    clear:both;
}
.podcastsubscribe .pcell {
    display: table-cell;
    float:left;
    background-color:#e3e2e2;
    border-radius: 15px 15px 15px 15px;
    border: 1px solid grey;
    padding: 1em;
    margin-right: 5px;
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

div.listingfooter ul li {
float: left;
list-style: none outside none;
}

';
        $query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
        $db->setQuery($query);
        $result = $db->loadObject();
        $oldcss = $result->stylecode;
        $newcss = $new710css . ' ' . $oldcss;
        $query = 'UPDATE #__bsms_styles SET stylecode="' . $newcss . '" where `filename` = "biblestudy"';
        $db->setQuery($query);
        $db->execute();
        if (!JFile::write($dest, $newcss)) {
            return false;
        }
        return TRUE;
    }



}