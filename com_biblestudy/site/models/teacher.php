<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * Model class for Teacher
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelTeacher extends JModelItem
{

	/**
	 *  Model context string.
	 *
	 * @var  string
	 */
	protected $_context = 'com_biblestudy.teacher';

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

		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

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
			catch (JException $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go through the error handler to allow Redirect to work.
					$app->enqueueMessage($e->getMessage(), 'error');
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

// End class
}
