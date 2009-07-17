<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewseriesedit extends JView
{
	
	function display($tpl = null)
	{
		
		$seriesedit		=& $this->get('Data');
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$isNew		= ($seriesedit->id < 1);
		$lists = array();
		$teachers =& $this->get('Teacher');
		//dump ($teachers, 'Teachers: ');
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Teacher' ) .' -' );
		$types 			= array_merge( $types, $teachers );
		$lists['teacher'] = JHTML::_('select.genericlist', $types, 'teacher', 'class="inputbox" size="1" ', 'value', 'text',  $seriesedit->teacher );
		
		$javascript			= 'onchange="changeDisplayImage();"';
		$directory = DS.'images'.DS.$admin_params->get('series_imagefolder', 'stories');
		$lists['series_thumbnail']	= JHTML::_('list.images',  'series_thumbnail', $seriesedit->series_thumbnail, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		
	
		
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Series Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.series', true );
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('lists', $lists);
		$this->assignRef('seriesedit',		$seriesedit);

		parent::display($tpl);
	}
}
?>