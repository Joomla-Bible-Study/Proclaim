<?php

/*
 *
 */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class BiblestudyViewLatest extends JView {

    function display($tpl = null) {

        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('id');
        $query->from('#__bsms_studies');
        $query->where('published = 1');
        $query->order('studydate DESC LIMIT 1');
        $db->setQuery($query);
        $db->query();
        $id = $db->loadResult();
        $t = JRequest::getInt('t','1','');

        $link = JRoute::_('index.php?option=com_biblestudy&view=sermon&id='.$id.'&t='.$t);
        $app = JFactory::getApplication();
        $app->redirect($link);
    }
}