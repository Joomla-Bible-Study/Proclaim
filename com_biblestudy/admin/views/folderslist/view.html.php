<?php
/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
//require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'helper.php');
jimport('joomla.application.component.view');

/**
 * @package     BibleStudy.Administrator
 * @since       7.0
 */
class BibleStudyViewFolderslist extends JView {

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
      
      
      
        $canDo = BibleStudyHelper::getActions();

        JToolBarHelper::title(JText::_('JBS_FLD_FOLDERS_MANAGER'), 'servers.png');
        if ($canDo->get('core.create')) 
        { JToolBarHelper::addNew('foldersedit.add'); }
        if ($canDo->get('core.edit')) 
        {JToolBarHelper::editList('foldersedit.edit');}
        if ($canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('folderslist.publish');
        JToolBarHelper::unpublishList('folderslist.unpublish');
        }
        if ($canDo->get('core.delete')) 
        {JToolBarHelper::trash('folderslist.trash');}
    }

}
?>