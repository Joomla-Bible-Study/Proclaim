<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class biblestudyViewtemplateslist extends JView {

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
        JToolBarHelper::title(JText::_('JBS_CMN_TEMPLATES'), 'templates.png');
        JToolBarHelper::addNew('templateedit.add');
        JToolBarHelper::editList('templateedit.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publishList('templateslist.publish');
        JToolBarHelper::unpublishList('templateslist.unpublish');
        JToolBarHelper::divider();
        JToolBarHelper::trash('templateslist.trach');
    }

}