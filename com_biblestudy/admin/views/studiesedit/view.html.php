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

class biblestudyViewstudiesedit extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');

        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin();
       	$this->canDo	= BibleStudyHelper::getActions($type = 'studiesedit', $Itemid = $this->item->id);
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        $canDo = BibleStudyHelper::getActions($this->item->id, 'studiesedit');
        $isNew = $this->item->id == 0;
        if($isNew)
            $text = JText::_('JBS_CMN_NEW');
        else
            $text = JText::_('JBS_CMN_EDIT');

        JToolBarHelper::title(JText::_('JBS_STY_EDIT_STUDY') . ': <small><small>[ ' . $text . ' ]</small></small>', 'studies.png');
        JToolBarHelper::apply('studiesedit.apply');
        JToolBarHelper::save('studiesedit.save');
        JToolBarHelper::divider();
        JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
        JToolBarHelper::divider();
        JToolBarHelper::cancel('studiesedit.cancel');
    }

}
?>