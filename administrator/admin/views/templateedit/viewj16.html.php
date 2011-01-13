<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class biblestudyViewTemplateedit extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->types = $this->get('Types');
        $this->form = $this->get("Form");
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
		$isNew = $this->item->id == 0;
       if($isNew){
           $text = JText::_('JBS_NEW');}
       else {
           $text = JText::_('JBS_EDIT');}
        JToolBarHelper::title(JText::_('JBS_TPL_CREATE_TEMPLATE'), 'templates.png');
        JToolbarHelper::save('templateedit.save');
        JToolbarHelper::apply('templateedit.apply');
        JToolbarHelper::cancel('templateedit.cancel');
    }

}