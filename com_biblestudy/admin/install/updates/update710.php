<?php

/**
 * @author Tom Fuller
 * @copyright 2012
 * @since 7.1.0
 * @desc copies existing css file to new location
 * @package BibleStudy
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
defined('_JEXEC') or die;
class JBS710Update
{
    function update710()
    {
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
        if (!JFile::exists($dest))
        {
            //if there is no new css file in the media folder, check to see if there is one in the old assets or in the backup folder
            if (JFile::exists($src))
            {
                $oldcss = JFile::read($src);
            }
            //There is no existing css so let us check for a backup
            $backup = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'biblestudy.css';
            if (JFile::exists($backup))
            {$oldcss = JFile::read($backup);}
            if ($oldcss)
            {

                $query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
                $db->setQuery($query);
                $db->query();
                $result = $db->loadObject();
                if ($result)
                {
                    $query = 'UPDATE #__bsms_styles SET `stylecode` = "'.$oldcss.'" WHERE `id` = '.$result->id;
                    $db->setQuery($query);
                    $db->query();
                    if (!JFile::write($dest,$oldcss)){return false;}
                }
            }
            else
            {
                //No css or backup found so we get the default and write a file with it to the new location
                $query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
                $db->setQuery($query);
                $db->query();
                $result = $db->loadObject();
                $newcss = $result->stylecode;
                if ($result)
                {
                    if (!JFile::write($dest,$newcss)){return false;}
                }
            }
        } //end if no new css file
    }
    
}
?>
