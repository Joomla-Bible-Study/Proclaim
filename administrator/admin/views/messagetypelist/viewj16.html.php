<?php
/**
 * @version     $Id
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
class biblestudyViewMessagetypelist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

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
        JToolBarHelper::title(JText::_('JBS_MST_MESSAGETYPE_MANAGER'), 'messagetype.png');
        JToolBarHelper::addNew('messagetypeedit.add');
        JToolBarHelper::editList('messagetypeedit.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publishList('messagetypelist.publish');
        JToolBarHelper::unpublishList('messagetypelist.unpublish');
        JToolBarHelper::trash('messagetypelist.trash');
    }

}
?>