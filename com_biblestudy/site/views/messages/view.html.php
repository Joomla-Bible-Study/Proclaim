<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
jimport('joomla.application.component.view');

class biblestudyViewmessages extends JView {
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
         $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'studiesedit');
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->books = $this->get('Books');
        $this->teachers = $this->get('Teachers');
        $this->series = $this->get('Series');
        $this->messageTypes = $this->get('MessageTypes');
        $this->years = $this->get('Years');
        $this->topics = $this->get('Topics');
      //  $this->addToolbar();
        
        
    //check permissions to enter studies
    $admin = new JBSAdmin();
    $params = $admin->getAdminsettings();
    $entry_access = $params->get('entry_access');
    $allow_entry = $params->get('allow_entry_study', 0);
    
        if (!$allow_entry){
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
            }
        
        $user = JFactory::getUser();
      
      $permission = false; 
      $groups = JAccess::getGroupsByUser($user->id);
      
           foreach ($groups as $group)
           {
                if ($entry_access <= $group){$permission = true;}
           }
           if (!$permission)
           {
                JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
                return false; 
           }

     //Puts a new record link at the top of the form
     if ($this->canDo->get('core.create')) 
     { 
      echo '<a href="index.php?option=com_biblestudy&view=message&layout=form">'.JText::_('JBS_CMN_NEW').'</a>';
     }  
        parent::display($tpl);

    }
/*
    protected function addToolbar() {
        JToolBarHelper::title(JText::_('JBS_STY_STUDIES_MANAGER'), 'studies.png');
        JToolBarHelper::addNew('studiesedit.add');
        JToolBarHelper::editList('studiesedit.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publishList('studieslist.publish');
        JToolBarHelper::unpublishList('studieslist.unpublish');
        JToolBarHelper::divider();
        if($this->state->get('filter.state') == -2)
            JToolBarHelper::deleteList('', 'artistudieslist.delete','JTOOLBAR_EMPTY_TRASH');
        else
            JToolBarHelper::trash('studieslist.trash');
    }
*/
}

?>