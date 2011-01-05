<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport ('joomla.application.component.helper');
jimport( 'joomla.i18n.help' );

class biblestudyViewmediafilesedit extends JView {
	protected $form;
	protected $item;
	protected $state;
	
	
	function display($tpl = null) {

		$this->form = $this->get("Form");
		$this->item = $this->get("Item");
		$this->state = $this->get("State");
		$this->setLayout('form');
        	
		//Get the js and css files
		$document =& JFactory::getDocument();	
		$document->addScript(JURI::base().'components/com_biblestudy/js/views/mediafilesedit.js');
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		$isNew		= ($this->item->id < 1);
		$title = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_EDIT' );
		JToolBarHelper::title(JText::_( 'JBS_MED_EDIT_MEDIA' ).': <small><small>['. $title.']</small></small>', 'mp3.png' );
                JToolBarHelper::apply('mediafilesedit.apply');
                JToolBarHelper::save('mediafilesedit.save');

		if(!$isNew){
                    JToolBarHelper::custom( 'resetDownloads', 'download.png', 'Reset Download Hits', 'JBS_MED_RESET_DOWNLOAD_HITS', false, false );
                    JToolBarHelper::custom( 'resetPlays', 'play.png', 'Reset Plays', 'JBS_MED_RESET_PLAYS', false, false );
		}
                JToolBarHelper::cancel( 'mediafilesedit.cancel', 'Cancel' );
		
		// Add an upload button and view a popup screen width 550 and height 400
                JToolBarHelper::media_manager();
                JToolBarHelper::divider();
		JToolBarHelper::help( 'biblestudy', true );		
	}
}
?>