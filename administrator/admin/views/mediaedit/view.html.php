<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewmediaedit extends JView
{
	
	function display($tpl = null)
	{
		
		$mediaedit		=& $this->get('Data');
		$isNew		= ($mediaedit->id < 1);
		$lists = array();
		$admin = $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$directory = ($admin_params->get('media_imagefolder') != '' ? DS.'images'.DS.$admin_params->get('media_imagefolder') : DS.'components'.DS.'com_biblestudy'.DS.'images');
		//dump ($directory, 'directory: ');
		$javascript			= 'onchange="changeDisplayImage();"';
		//array_unshift($folderfinal2, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['media']	= JHTML::_('list.images',  'media_image_path', $mediaedit->media_image_path, $javascript, $directory, "bmp|gif|jpg|png|swf"  );

		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Edit Media' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.media', true );
		$this->assignRef('mediaedit',		$mediaedit);
		$this->assignRef('lists', $lists);

		parent::display($tpl);
	}
}
?>