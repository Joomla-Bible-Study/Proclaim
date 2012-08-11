<?php

/**
 * Controller for MediaFiles
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * MediaFiles list controller class
 *
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerMediafiles extends JControllerAdmin {

    /**
     * Constructor.
     *
     * @param	array	$config	An optional associative array of configuration settings.

     * @return	ContentControllerArticles
     * @see		JController
     * @since	1.6
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Proxy for getModel
     *
     * @param string $name    The name of the model
     * @param string $prefix  The prefix for the PHP class name
     * @param array $config Set ignore request
     *
     * @return JModel
     * @since 7.0
     */
    public function &getModel($name = 'Mediafile', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

}
