<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport( 'joomla.application.component.view' );

class biblestudyViewserieslist extends JView
{


    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->canDo	= BibleStudyHelper::getActions('', 'seriesedit');
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
        JToolBarHelper::title(JText::_('JBS_CMN_SERIES'), 'series.png');
         if ($this->canDo->get('core.create')) 
        { JToolBarHelper::addNew('seriesedit.add'); }
        if ($this->canDo->get('core.edit')) 
        {JToolBarHelper::editList('seriesedit.edit');}
        if ($this->canDo->get('core.edit.state')) {
        JToolBarHelper::divider();
        JToolBarHelper::publishList('serieslist.publish');
        JToolBarHelper::unpublishList('serieslist.unpublish');
         JToolBarHelper::archiveList('serieslist.archive','JTOOLBAR_ARCHIVE');
        }
        if ($this->canDo->get('core.delete')) 
        {JToolBarHelper::trash('serieslist.trash');}
        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
        JToolBarHelper::deleteList('', 'serieslist.delete','JTOOLBAR_EMPTY_TRASH');}
    }

}

?>
