<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

/**
 * @package     BibleStudy.Administrator
 * @since       7.0
 */
class biblestudyViewServerslist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'serversedit');
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
        
        JToolBarHelper::title(JText::_('JBS_SVR_SERVERS_MANAGER'), 'servers.png');
        if ($this->canDo->get('core.create')) 
        { JToolBarHelper::addNew('serversedit.add'); }
        if ($this->canDo->get('core.edit')) 
        {JToolBarHelper::editList('serversedit.edit');}
        if ($this->canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('serverslist.publish');
        JToolBarHelper::unpublishList('serverslist.unpublish');
        }
        if ($this->canDo->get('core.delete')) 
        {JToolBarHelper::trash('serverslist.trash');}
    }

}
?>