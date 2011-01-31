<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
jimport('joomla.application.component.view');
class biblestudyViewpodcastlist extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        $this->addToolbar();
        parent::display($tpl);
    }
    
    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_PDC_PODCAST_MANAGER'), 'podcast.png');
        JToolBarHelper::addNew('podcastedit.add');
        JToolBarHelper::editList('podcastedit.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publishList('podcastlist.publish');
        JToolBarHelper::unpublishList('podcastlist.unpublish');
        if($this->state->get('filter.state') == -2)
            JToolBarHelper::deleteList('', 'podcastlist.delete','JTOOLBAR_EMPTY_TRASH');
        else
            JToolBarHelper::trash('podcastlist.trash');
    }

}
?>