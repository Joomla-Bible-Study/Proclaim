<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Teacher
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerTeacher extends JControllerForm
{
	/**
	 * constructor (registers additional tasks to methods)
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since 1.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   JModelLegacy  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		// Set the model
		$model = $this->getModel('Teacher', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=teachers' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return BiblestudyModelTeacher
	 *
	 * @since 7.0
	 */
	public function getModel($name = 'Teacher', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
