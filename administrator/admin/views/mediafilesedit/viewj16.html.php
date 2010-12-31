<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport ('joomla.application.component.helper');

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
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/css/mediafilesedit.css');
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/views/mediafilesedit.js');
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		$isNew		= ($mediafilesedit->id < 1);
		$title = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_EDIT' );
		JToolBarHelper::title(JText::_( 'JBS_MED_EDIT_MEDIA' ).': <small><small>['. $title.']</small></small>', 'mp3.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();

		} else {
			JToolBarHelper::apply();
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		JToolBarHelper::custom( 'resetDownloads', 'download.png', 'Reset Download Hits', 'JBS_MED_RESET_DOWNLOAD_HITS', false, false );
		JToolBarHelper::custom( 'resetPlays', 'play.png', 'Reset Plays', 'JBS_MED_RESET_PLAYS', false, false );
		}
		
		// Add an upload button and view a popup screen width 550 and height 400
		$alt = "Upload";
		$bar=& JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&directory=", 650, 400 );
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );		
	}
}
?>