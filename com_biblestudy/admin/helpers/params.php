<?php

/**
 * Params Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * This is for Retreving Admin and Template db
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BsmHelper
{

    public static $extension = 'com_biblestudy';

    /**
     * Gets the settings from Admin
     *
     * @return object Return Admin table
     */
    public static function getAdmin()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__bsms_admin')
            ->where('id = ' . (int)1);
        $db->setQuery($query);
        $admin = $db->loadObject();
        $registry = new JRegistry();
        $registry->loadString($admin->params);
        $admin->params = $registry;
        //Add the current user id
        $user = JFactory::getUser();
        $admin->user_id = $user->id;
        return $admin;
    }

    /**
     * Get Template Params
     *
     * @return object Retrun active template info
     */
    public static function getTemplateparams()
    {
        $db = JFactory::getDbo();
        $pk = JRequest::getInt('t', 'get', '1');
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__bams_template')
            ->where('id = ' . (int)$db->quote($pk));
        $db->setQuery($query);
        $template = $db->loadObject();
        $registry = new JRegistry();
        $registry->loadString($template->params);
        $template->params = $registry;
        return $template;
    }

}