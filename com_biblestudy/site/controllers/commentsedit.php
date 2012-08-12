<?php

/**
 * Comments Edit Controller
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller class for CommentsEdit
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyControllerCommentsEdit extends JControllerForm {

    /**
     * View List
     * @var type
     */
    protected $view_list = 'commentslist';

    /**
     * View Item
     * @since	1.6
     */
    protected $view_item = 'commentsedit';

    /**
     * Method to cancel an edit.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     *
     * @return	Boolean	True if access level checks pass, false otherwise.
     * @since	1.6
     */
    public function cancel($key = 'a_id') {
        parent::cancel($key);
    }

    /**
     * Method to edit an existing record.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return	Boolean	True if access level check and checkout passes, false otherwise.
     * @since	1.6
     */
    public function edit($key = null, $urlVar = 'a_id') {
        $result = parent::edit($key, $urlVar);
        return $result;
    }

    /**
     * Method to save a record.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return	Boolean	True if successful, false otherwise.
     * @since	1.6
     */
    public function save($key = null, $urlVar = 'a_id') {

        $result = parent::save($key, $urlVar);
        return $result;
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	object	The model.
     *
     * @since	1.5
     */
    public function getModel($name = 'CommentsEdit', $prefix = 'biblestudyModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

}