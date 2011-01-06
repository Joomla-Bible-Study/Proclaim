<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class biblestudyViewstudiesedit extends JView {

    protected $form;
    protected $item;
    protected $state;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->mediafiles = $this->get('MediaFiles');

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_STY_EDIT_STUDY') . ': <small><small>[ ' . $text . ' ]</small></small>', 'studies.png');
        JToolBarHelper::apply('studiesedit.apply');
        JToolBarHelper::save('studiesedit.save');
        JToolBarHelper::divider();
        JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
        JToolBarHelper::divider();
        JToolBarHelper::cancel('studiesedit.cancel');
    }

}