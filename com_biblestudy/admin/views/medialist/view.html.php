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

class biblestudyViewmedialist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        
//        $admin = $this->get('Admin');
//		$admin_params = new JParameter($admin[0]->params);
//		$directory = ($admin_params->get('media_imagefolder') != '' ? '/images/'.$admin_params->get('media_imagefolder') : '/components/com_biblestudy/images'); // santon 2011-04-20 no more available
		$directory = '/components/com_biblestudy/images';
        $this->assignRef('directory', $directory);
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->canDo	= BibleStudyHelper::getActions($this->items[0]->id, 'medialist' );
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
        JToolBarHelper::title(JText::_('JBS_CMN_MEDIAIMAGES'), 'mediaimages.png');
        if ($this->canDo->get('core.create')) 
        { JToolBarHelper::addNew('mediaedit.add'); }
        if ($this->canDo->get('core.edit')) 
        {JToolBarHelper::editList('mediaedit.edit');}
        if ($this->canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('medialist.publish');
        JToolBarHelper::unpublishList('medialist.unpublish');
         JToolBarHelper::archiveList('medialist.archive','JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) 
        {JToolBarHelper::trash('medialist.trash');}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
        JToolBarHelper::deleteList('', 'medialist.delete','JTOOLBAR_EMPTY_TRASH');}
    }

}

?>
