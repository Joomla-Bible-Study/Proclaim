<?php
defined('_JEXEC') or die();
jimport('joomla.html.toolbar');
 
 class biblestudyHelperToolbar extends JObject
 {        
        function getToolbar() {
 
 
                $bar =& new JToolBar( 'Toolbar' );
                $bar->appendButton( 'Standard', 'save', 'Save', "index.php?option=com_biblestudy&controller=studiesedit&task=save", false );
                $bar->appendButton( 'Separator' );
                $bar->appendButton( 'Standard', 'delete', 'Delete', 'delete', false );
 				$bar->appendButton( 'Popup', 'upload', 'Upload', "index.php?option=com_media&tmpl=component&task=popupUpload&directory=$directory", 800, 700 );
 
                return $bar->render();
 
        }
 
 }
