<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.helper');

/**
 * //Eugen
 * This class may not be required
 */
class BsmHelper extends JComponentHelper {

    /**
     * Gets the settings from Admin
     *
     * @param   $isSite   Boolean   True if this is called from the frontend
     * @since   7.0
     */
    public function getAdmin($isSite = false) {
        if($isSite)
            JModel::addIncludePath (JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $admin = JModel::getInstance('Admin', 'biblestudyModel');
        $admin = $admin->getItem(1);

        //Add the current user id
        $user = JFactory::getUser();
        $admin->user_id = $user->id;
        return $admin;
    }
    
    public function getTemplateparams($isSite = false){
        if ($isSite)
            JModel::addIncludePath (JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
        $pk = JRequest::getInt('t','get','1');
        $template = JModel::getInstance('Templateedit', 'biblestudyModel');
        $template = $template->getItem($pk);
        return $template;
    }
}
?>