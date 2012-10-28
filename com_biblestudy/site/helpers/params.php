<?php

/**
 * Params Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * //Eugen
 * This class may not be required
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BsmHelper extends JComponentHelper {

    /**
     * Gets the settings from Admin
     *
     * @param   $isSite   Boolean   True if this is called from the frontend
     * @since   7.0
     */
    public static function getAdmin($isSite = false) {
        if ($isSite)
            JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models');
        $admin = JModel::getInstance('Admin', 'biblestudyModel');
        $admin = $admin->getItem(1);

        //Add the current user id
        $user = JFactory::getUser();
        $admin->user_id = $user->id;
        return $admin;
    }

    /**
     * Get Template Params
     * @return Object
     */
    public static function getTemplateparams() {
        $pk = JRequest::getInt('t', 'get', '1');
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__bsms_templates WHERE id = ' . $pk;
        $db->setQuery($query);
        return $db->loadObject();
    }

}