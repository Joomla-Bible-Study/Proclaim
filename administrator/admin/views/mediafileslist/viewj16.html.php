<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * @package     BibleStudy.Administrator
 * @since       7.0
 */
class biblestudyViewmediafileslist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->state = $this->get('State');

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        //Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @since 7.0
     */
    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_MED_MEDIA_FILES_MANAGER'), 'mp3.png');
        JToolBarHelper::addNew('mediafilesedit.add', 'JTOOLBAR_NEW');
        JToolBarHelper::editList('mediafilesedit.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::divider();
        JToolBarHelper::publishList('mediafileslist.publish');
        JToolBarHelper::unpublishList('mediafileslist.unpublish');
        JToolBarHelper::trash('mediafileslist.trash');
    }

}

?>
