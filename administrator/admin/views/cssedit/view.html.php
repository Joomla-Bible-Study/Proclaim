<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewcssedit extends JView
{
	
	function display($tpl = null)
	{
		
		//$filename=dirname(__FILE__)."/../../../administrator/components/com_biblestudy/assests/css/biblestudy.css";
		//$filename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		//$csscontents=fopen($filename,"rb");
		//$lists['initstring'] = fread($csscontents,filesize($filename));
		//fclose($csscontents);
		//$seriesedit		=& $this->get('Data');
		//$isNew		= ($seriesedit->id < 1);
	$lists		=& $this->get('Data');
		$text = JText::_( 'Edit CSS' );
		JToolBarHelper::title(   JText::_( 'CSS Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		JToolBarHelper::custom( 'reset', 'save', 'Reset CSS', 'Reset CSS', false, false );
		//$alt = "Save CSS";
		//$bar=& JToolBar::getInstance( 'toolbar' );

		//$bar->appendButton( 'Standard', 'save', 'Reset CSS', "index.php?option=com_biblestudy&view=cssedit&task=reset",'' ,'' );
//$bar->appendButton( 'Standard', 'save', $alt, "",'' ,'' );

			// for existing items the button is renamed `close`
			//JToolBarHelper::custom( 'save','','', 'Save CSS', 'false', 'false' );
		
		//jimport( 'joomla.i18n.help' );
		//JToolBarHelper::help( 'biblestudy.series', true );
//dump ($filename, 'initstring: ');
		$this->assignRef('lists',		$lists);

		parent::display($tpl);
	}
}
?>