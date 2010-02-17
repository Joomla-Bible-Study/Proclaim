<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewmediaedit extends JView
{
	
	function display($tpl = null)
	{
		
		$database = & JFactory::getDBO();
		//$database->setQuery("SELECT * FROM #__bsms_media;");
		//$database->query();
		//$numrows = $database->getNumRows();
		//dump ($numrows, 'numrows1: ');
		//$numrows = $numrows + 1;
		//dump ($numrows, 'numrows2: ');
		//$query = "INSERT INTO #__bsms_media (media_text, media_image_name, media_image_path, path2, media_alttext, published) VALUES ('Download','Download', '', 'download.png', 'Download', '1');";
		
		//$database->setQuery = ("INSERT INTO #__bsms_media VALUES ('".$numrows."','Download','Download', '', 'download.png', 'Download', '1');");
		//$database->query();
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');										 
		$mediaedit		=& $this->get('Data');
		$isNew		= ($mediaedit->id < 1);
		$lists = array();
		$admin = $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$directory = ($admin_params->get('media_imagefolder') != '' ? '/images/'.$admin_params->get('media_imagefolder') : '/components/com_biblestudy/images');
		//dump ($directory, 'directory: ');
		$javascript			= 'onchange="changeDisplayImage();"';
           // $fullpath = JURI::root() . $path;
           // $javascript = 'onchange="javascript:if (document.forms.adminForm.thumbnailm.options[selectedIndex].value!=\'\'){document.imagelib.src=\''.$directory.'\' + document.forms.adminForm.thumbnailm.options[selectedIndex].value}else{document.imagelib.src=\'../images/blank.png\'}" size="1" name="image">}"';
            //echo JHTML::_( 'list.images', 'originalimg', $mediaedit->thumbnailm , $customJS, $directory );
		//array_unshift($folderfinal2, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['media']	= JHTML::_('list.images',  'path2', $mediaedit->path2, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $mediaedit->published);
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Edit Media' ).': <small><small>[ ' . $text.' ]</small></small>', 'mediaimages.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		// Add an upload button and view a popup screen width 550 and height 400
		$alt = "Upload";
		$bar=& JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&directory=", 650, 400 );
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.media', true );
		$this->assignRef('directory', $directory);
		$this->assignRef('mediaedit',		$mediaedit);
		$this->assignRef('lists', $lists);

		parent::display($tpl);
	}
}
?>