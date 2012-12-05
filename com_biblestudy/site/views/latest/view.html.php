<?php

/**
 * Latest JViewLegacy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Latest
 * @package BibleStudy.Site
 * @since 7.1.0
 */
class BiblestudyViewLatest extends JViewLegacy {

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @see     fetch()
     * @since   11.1
     */
    public function display($tpl = null) {

        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('id');
        $query->from('#__bsms_studies');
        $query->where('published = 1');
        $query->order('studydate DESC LIMIT 1');
        $db->setQuery($query);
        $id = $db->loadResult();
        $t = JRequest::getInt('t', '1', '');

        $link = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $id . '&t=' . $t);
        $app = JFactory::getApplication();
        $app->redirect($link);
    }

}