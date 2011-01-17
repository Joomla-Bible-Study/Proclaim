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
     * @since   7.0
     */
    public function getAdmin() {
        $admin = JModel::getInstance('Admin', 'biblestudyModel');
        $admin = $admin->getItem(1);

        //Add the current user id
        $user = JFactory::getUser();
        $admin->user_id = $user->id;
        return $admin;
    }
}
?>