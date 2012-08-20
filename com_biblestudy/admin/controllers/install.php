<?php

/**
 * Controller for Admin
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller for Admin
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerInstall extends JControllerForm {

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     * @param array $config
     */
    function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Fix Assets
     */
    function fixAssets() {
        require_once(BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.assets.php');
        $asset = new fixJBSAssets();
        $fixassets = $asset->fixAssets();
        JRequest::setVar('messages', $fixassets, 'get', 'true');
        $jbsname = JRequest::getVar('jbsname');
        $jbstype = JRequest::getVar('jbstype');
        if ($jbsname):
            $this->setRedirect('index.php?option=com_biblestudy&view=install&jbsname=' . $jbsname . '&jbstype=' . $jbstype);
        endif;
    }

}