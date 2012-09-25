<?php

/**
 * Controller for Template
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.backup.php');
jimport('joomla.application.component.controllerform');

/**
 * Template controller class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerTemplate extends JControllerForm {

    /**
     * Class constructor.
     *
     * @param   array  $config  A named array of configuration variables.
     *
     * @since	7.0.0
     */
    function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Copy Template
     */
    function copy() {
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        $model = & $this->getModel('template');

        if ($model->copy($cid)) {
            $msg = JText::_('JBS_TPL_TEMPLATE_COPIED');
        } else {
            $msg = $model->getError();
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=templates', $msg);
    }

    /**
     * Make Template Default
     *
     */
    function makeDefault() {
        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid', array(0), 'post', 'array');

        if (!is_array($cid) || count($cid) < 1) {
            JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'));
        }

        $model = $this->getModel('template');
        if (!$model->makeDefault($cid, 0)) {
            echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=templates');
    }


    /**
     * Get Template Settings
     *
     * @param array $template
     * @return boolean|string
     */
    function getTemplate($template) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('tc.id, tc.templatecode,tc.type,tc.filename');
        $query->from('#__bsms_templatecode as tc');
        $query->where('tc.filename ="' . $template . '"');
        $db->setQuery($query);
        if (!$object = $db->loadObject()) {
            return false;
        }
        $templatereturn = '
                        INSERT INTO #__bsms_templatecode SET `type` = "' . $db->getEscaped($object->type) . '",
                        `templatecode` = "' . $db->getEscaped($object->templatecode) . '",
                        `filename`="' . $db->getEscaped($template) . '",
                        `published` = "1";
                        ';
        return $templatereturn;
    }



}