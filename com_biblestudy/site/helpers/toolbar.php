<?php

/**
 * @version $Id: toolbar.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();
jimport('joomla.html.toolbar');
 
 class biblestudyHelperToolbar extends JObject
 {        
        function getToolbar() {
 
 				$directory='images';
                $bar = new JToolBar( 'Toolbar' );
 				$toolview = JRequest::getVar('view');
 				if ($toolview == 'mediafile')
				 {$bar->appendButton( 'Popup', 'upload', 'JBS_MED_UPLOAD', "index.php?option=com_media&tmpl=component&task=popupUpload&folder=", 600, 400 );}
 
                return $bar->render();
 
        }
 
 }
