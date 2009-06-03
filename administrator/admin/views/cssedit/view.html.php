<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewcssedit extends JView
{
	
	function display($tpl = null)
	{
		
		//$filename=dirname(__FILE__)."/../../../administrator/components/com_biblestudy/assests/css/biblestudy.css";
		$filename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$csscontents=fopen($filename,"rb");
		$lists['initstring'] = fread($csscontents,filesize($filename));
		fclose($csscontents);
		//$seriesedit		=& $this->get('Data');
		//$isNew		= ($seriesedit->id < 1);

		$text = JText::_( 'Edit CSS' );
		JToolBarHelper::title(   JText::_( 'CSS Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.series', true );
//dump ($initstring, 'initstring: ');
		$this->assignRef('lists',		$lists);

		parent::display($tpl);
	}
}
?>