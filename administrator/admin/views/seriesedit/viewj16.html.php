<?php

/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class biblestudyViewSeriesedit extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");

 //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin();
        
        $this->setLayout("form");
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        $isNew = ($this->item->id < 1);
        $title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
        JToolBarHelper::title(JText::_('JBS_SER_SERIES_EDIT') . ': <small><small>[' . $title . ']</small></small>', 'series.png');
        JToolBarHelper::save('seriesedit.save');
        if ($isNew)
            JToolBarHelper::cancel();
        else {
            JToolBarHelper::apply('seriesedit.apply');
            JToolBarHelper::cancel('seriesedit.cancel', 'JTOOLBAR_CLOSE');
        }
    }

}