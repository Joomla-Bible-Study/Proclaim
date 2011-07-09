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
class biblestudyViewlocationslist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
         $this->canDo	= BibleStudyHelper::getActions('', 'locationsedit');
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
        JToolBarHelper::title(JText::_('JBS_CMN_LOCATIONS'), 'locations.png');
        if ($this->canDo->get('core.create')) 
        { JToolBarHelper::addNew('locationsedit.add');}
        if ($this->canDo->get('core.edit')) 
        {        JToolBarHelper::editList('locationsedit.edit');}
        if ($this->canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('locationslist.publish');
        JToolBarHelper::unpublishList('locationslist.unpublish');
         JToolBarHelper::archiveList('locationslist.archive','JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) 
        {JToolBarHelper::trash('locationslist.trash');}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
        JToolBarHelper::deleteList('', 'locationslist.delete','JTOOLBAR_EMPTY_TRASH');}
    }

}
?>