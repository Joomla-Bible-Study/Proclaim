<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewteacheredit extends JView
{
	
	function display($tpl = null)
	{
		
		$teacheredit		=& $this->get('Data');
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$isNew		= ($teacheredit->id < 1);
		$editor =& JFactory::getEditor();
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Teacher Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.teachers', true );
		$javascript			= 'onchange="changeDisplayImage();"';
		$directory = DS.'images'.DS.$admin_params->get('teachers_imagefolder', 'stories');
		$lists['teacher_thumbnail']	= JHTML::_('list.images',  'teacher_thumbnail', $teacheredit->teacher_thumbnail, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		$lists['teacher_image']	= JHTML::_('list.images',  'teacher_image', $teacheredit->teacher_image, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
// build the html select list for ordering
	$query = 'SELECT ordering AS value, ordering AS text'
	. ' FROM #__bsms_teachers'
	. ' WHERE catid = '. (int) $teacheredit->catid	. ' ORDER BY ordering'
	;
	$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $teacheredit->published);
	$lists['list_show'] = JHTML::_('select.booleanlist', 'list_show', 'class="inputbox"', $teacheredit->list_show);
	$lists['ordering'] 			= JHTML::_('list.specificordering',  $teacheredit, $teacheredit->id, $query, 1 );	
		$this->assignRef( 'editor', $editor );
		$this->assignRef('teacheredit',		$teacheredit);
		$this->assignRef('lists', $lists);
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('directory', $directory);
		parent::display($tpl);
	}
}
?>