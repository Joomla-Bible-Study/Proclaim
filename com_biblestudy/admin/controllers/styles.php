<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Styles list controller class.
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */

class BiblestudyControllerStyles extends JControllerAdmin
{

	/**
	 * Proxy for getModel
	 *
	 * @param   string $name    The model name. Optional.
	 * @param   string $prefix  The class prefix. Optional.
	 * @param   array  $config  Configuration array for model. Optional.
	 *
	 * @return JModel
	 *
	 * @since 7.1.0
	 */
	public function getModel($name = 'Style', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Tries to fix css renaming.
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	public function fixcss()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();

		// Initialise variables.
		$user  = JFactory::getUser();
		$input = new JInput;
		$ids   = $input->get('cid', '', 'array');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_biblestudy.styles.' . (int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
			}
		}

		if (empty($ids))
		{
			$app->enqueueMessage(JText::_('JERROR_NO_ITEMS_SELECTED'), 'warning');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->fixcss($ids))
			{
				$app->enqueueMessage($model->getError(), 'waring');
			}
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=styles');
	}

}
