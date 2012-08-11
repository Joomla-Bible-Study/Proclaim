<?php

/**
 * Controller for Servers
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Servers list controller class.
 *
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerServers extends JControllerAdmin {

    /**
     * Proxy for getModel
     *
     * @param string $name    The name of the model
     * @param string $prefix  The prefix for the PHP class name
     * @param array $config
     * @return JModel
     *
     * @since 7.0.0
     */
    public function getModel($name = 'Server', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

}