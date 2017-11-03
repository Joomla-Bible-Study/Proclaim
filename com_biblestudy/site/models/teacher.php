<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Model class for Teacher
 *
 * @since  7.0.0
 */
class BiblestudyModelTeacher extends JModelItem
{
	/**
	 *  Model context string.
	 *
	 * @var  string
	 *
	 * @since 7.0
	 */
	protected $context = 'com_biblestudy.teacher';

	/**
	 * Method to get study data.
	 *
	 * @param   int  $pk  The id of the study.
	 *
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @since 7.1.0
	 */
	public function &getItem($pk = null)
	{
		$app = JFactory::getApplication();

		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('teacher.id');

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select($this->getState('item.select', 't.*,CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug'));
				$query->from('#__bsms_teachers AS t');
				$query->where('t.id = ' . (int) $pk);
				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					$app->enqueueMessage(JText::_('JBS_CMN_TEACHER_NOT_FOUND'), 'error');

					return false;
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go through the error handler to allow Redirect to work.
					$app->enqueueMessage($e->getMessage(), 'error');
				}
				else
				{
					$app->enqueueMessage($e->getMessage(), 'error');
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		/** @type JApplicationSite $app */
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$input = new JInput;
		$pk    = $input->get('id', '', 'int');
		$this->setState('teacher.id', $pk);

		$offset = $input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		$template = JBSMParams::getTemplateparams();
		$admin    = JBSMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = $params->get('teachertemplateid');

		if (!$t)
		{
			$input = new JInput;
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('admin', $admin);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}
// End class
}
