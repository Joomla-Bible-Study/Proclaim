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

class biblestudyViewstudieslist extends JView {
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->canDo	= BibleStudyHelper::getActions('', 'studiesedit');
        $this->state = $this->get('State');
        $items = $this->get('Items');
        $modelView = $this->getModel();
        $this->items = $modelView->getTranslated($items);
        $this->pagination = $this->get('Pagination');
        $this->books = $this->get('Books');
        $this->teachers = $this->get('Teachers');
        $this->series = $this->get('Series');
        $this->messageTypes = $this->get('MessageTypes');
        $this->years = $this->get('Years');
        $this->topics = $this->get('Topics');
        $this->addToolbar();
        
        
        parent::display($tpl);

    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_STUDIES'), 'studies.png');
        if ($this->canDo->get('core.create')) 
        { JToolBarHelper::addNew('studiesedit.add'); }
        if ($this->canDo->get('core.edit')) 
        {JToolBarHelper::editList('studiesedit.edit');}
        if ($this->canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('studieslist.publish');
        JToolBarHelper::unpublishList('studieslist.unpublish');
        JToolBarHelper::archiveList('studieslist.archive','JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) 
        {JToolBarHelper::trash('studieslist.trash');}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
        JToolBarHelper::deleteList('', 'studieslist.delete','JTOOLBAR_EMPTY_TRASH');}
    }

}

?>