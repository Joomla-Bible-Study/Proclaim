<?php

/**
 * Teachers Model
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;


// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/teachers.php';

/**
 * Model class for Teachers
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyModelTeacherlist extends BiblestudyModelTeachers {

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_biblestudy.teachers';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_biblestudy';


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('filter.extension', $this->_extension);

		// Get the parent id if defined.
		$parentId = $app->input->getInt('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

}