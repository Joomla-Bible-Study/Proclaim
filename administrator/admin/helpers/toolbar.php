<?php
defined('_JEXEC') or die();
jimport('joomla.html.toolbar');
 
 class biblestudyHelperToolbar extends JObject
 {        
        function getToolbar() {
 
 
                $bar =& new JToolBar( 'Toolbar' );
                $bar->appendButton( 'Standard', 'save', 'Save', 'save', false );
                $bar->appendButton( 'Standard', 'cancel', 'Cancel', 'cancel', false );
 				$toolview = JRequest::getVar('view');
 				if ($toolview == 'mediafilesedit')
				 {$bar->appendButton( 'Popup', 'upload', 'Upload', "index.php?option=com_media&tmpl=component&task=popupUpload&directory=$directory", 600, 400 );}
 
                return $bar->render();
 
        }
 
 }
