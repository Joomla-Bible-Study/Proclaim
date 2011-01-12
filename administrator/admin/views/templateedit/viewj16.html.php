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

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_TPL_CREATE_TEMPLATE'), 'templates.png');
        JToolbarHelper::save('templateedit.save');
        JToolbarHelper::apply('templateedit.apply');
        JToolbarHelper::cancel('templateedit.cancel');
    }

}