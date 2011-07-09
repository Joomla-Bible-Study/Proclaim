<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

/**
 * @package     BibleStudy.Administrator
 * @since       7.0
 */
class biblestudyViewMessagetypelist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->canDo	= BibleStudyHelper::getActions('', 'messagetypeedit');
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
        JToolBarHelper::title(JText::_('JBS_CMN_MESSAGE_TYPES'), 'messagetype.png');
        if ($this->canDo->get('core.create')) 
        { JToolBarHelper::addNew('messagetypeedit.add'); }
        if ($this->canDo->get('core.edit')) 
        {JToolBarHelper::editList('messagetypeedit.edit');}
        if ($this->canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('messagetypelist.publish');
        JToolBarHelper::unpublishList('messagetypelist.unpublish');
         JToolBarHelper::archiveList('messagetypelist.archive','JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) 
        {JToolBarHelper::trash('messagetypelist.trash');}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
        JToolBarHelper::deleteList('', 'messagetypelist.delete','JTOOLBAR_EMPTY_TRASH');}
    }

}
?>