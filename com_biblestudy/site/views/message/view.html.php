<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_SITE  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
jimport('joomla.application.component.view');

class biblestudyViewmessage extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        
        // Access check.
if (!JFactory::getUser()->authorise('core.edit', 'com_biblestudy')) 
{
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');

        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($isSite = true);
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
      //  $this->addToolbar();
        parent::display($tpl);
    }
/*
    protected function addToolbar() {
        $isNew = $this->item->id == 0;
        if($isNew)
            $text = JText::_('JBS_CMN_NEW');
        else
            $text = JText::_('JBS_CMN_EDIT');

        JToolBarHelper::title(JText::_('JBS_STY_EDIT_STUDY') . ': <small><small>[ ' . $text . ' ]</small></small>', 'studies.png');
        JToolBarHelper::apply('studiesedit.apply');
        JToolBarHelper::save('studiesedit.save');
        JToolBarHelper::divider();
        JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
        JToolBarHelper::divider();
        JToolBarHelper::cancel('studiesedit.cancel');
    }
*/
}
?>