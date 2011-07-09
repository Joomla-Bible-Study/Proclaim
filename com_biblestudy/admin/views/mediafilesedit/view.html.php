<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewmediafilesedit extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'mediafilesedit');
        //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin();

        //Needed to load the article field type for the article selector
        JFormHelper::addFieldPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'models'.DS.'fields'.DS.'modal');

        $this->setLayout('form');
        
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>', 'mp3.png');
        
        if ($this->canDo->get('core.edit','com_biblestudy'))
        {
          JToolBarHelper::save('mediafilesedit.save');
          JToolBarHelper::apply('mediafilesedit.apply');
        }
        JToolBarHelper::cancel('mediafilesedit.cancel', 'JTOOLBAR_CANCEL');
		if ($this->canDo->get('core.edit','com_biblestudy') && !$isNew)
        {
            JToolBarHelper::divider();
            JToolBarHelper::custom('resetDownloads', 'download.png', 'Reset Download Hits', 'JBS_MED_RESET_DOWNLOAD_HITS', false, false);
            JToolBarHelper::custom('resetPlays', 'play.png', 'Reset Plays', 'JBS_MED_RESET_PLAYS', false, false);
        }

        // Add an upload button and view a popup screen width 550 and height 400
        JToolBarHelper::divider();
        JToolBarHelper::media_manager();
        JToolBarHelper::divider();
        JToolBarHelper::help('biblestudy', true);
    }

}

?>