<?php
/**
 * Controller for a Comment
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller for a Comment
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerComment extends JControllerForm
{

	/**
	 * Class constructor.
	 *
	 * @param   array $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		// In the absence of better information, revert to the component permissions.
		return parent::allowAdd();
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array  $data  An array of input data.
	 * @param   string $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_biblestudy.comment.' . $recordId))
		{
			return true;
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Comment', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=comments' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

}
