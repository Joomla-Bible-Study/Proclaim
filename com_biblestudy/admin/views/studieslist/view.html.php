<?php

/**
 * @version     $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');
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
        $this->addToolbar();

        parent::display($tpl);

    }

    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_CMN_STUDIES'), 'studies.png');

        if ($this->canDo->get('core.create')) {
        	JToolBarHelper::addNew('studiesedit.add');
        }

        if ($this->canDo->get('core.edit')) {
        	JToolBarHelper::editList('studiesedit.edit');
        }

        if ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publishList('studieslist.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublishList('studieslist.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('studieslist.archive','JTOOLBAR_ARCHIVE');
        }

        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'studieslist.delete','JTOOLBAR_EMPTY_TRASH');

        }
        elseif ($this->canDo->get('core.edit.state')) {
            JToolBarHelper::trash('studieslist.trash');
                JToolBarHelper::divider();
        }
    }

}
