<?php
defined('_JEXEC') or die();
jimport('joomla.html.toolbar');
 
 class biblestudyHelperToolbar extends JObject
 {        
        function getToolbar() {
 
 				$directory='images';
                $bar = new JToolBar( 'Toolbar' );
          //      $bar->appendButton( 'Standard', 'new', 'New Record', 'new', false );
          //      $bar->appendButton( 'Separator' );
          //      $bar->appendButton( 'Standard', 'delete', 'Delete Record', 'delete', false );
          //      $bar->appendButton( 'Standard', 'publish', 'Publish Record', 'delete', false );
          //      $bar->appendButton( 'Standard', 'unpublish', 'Unpublish Record', 'delete', false );
              //  $bar->appendButton( 'Standard', 'save', 'Save', 'save', false );
              //  $bar->appendButton( 'Standard', 'cancel', 'Cancel', 'cancel', false );
 				$toolview = JRequest::getVar('view');
 				if ($toolview == 'mediafile')
				 {$bar->appendButton( 'Popup', 'upload', 'JBS_MED_UPLOAD', "index.php?option=com_media&tmpl=component&task=popupUpload&folder=", 600, 400 );}
 
                return $bar->render();
 
        }
 
 }
