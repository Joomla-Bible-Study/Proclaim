<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// No Direct Access
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Teacher
 *
 * @since  7.0.0
 */
class CWMTeacherModel extends ItemModel
{
	/**
	 *  Model context string.
	 *
	 * @var  string
	 *
	 * @since 7.0
	 */
	protected $context = 'com_proclaim.teacher';

	/**
	 * Method to get study data.
	 *
	 * @param   int  $pk  The id of the study.
	 *
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @throws \Exception
	 * @since 7.1.0
	 */
	public function &getItem($pk = null)
	{
		$app = Factory::getApplication();

		// Initialise variables.
		if ($pk === null)
		{
			$pk = $app->input->getInt('id');
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db    = Factory::getContainer()->get('DatabaseDriver');
				$query = $db->getQuery(true);
				$query->select(
					$this->getState('item.select',
						't.*,CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\', t.id, t.alias) ELSE t.id END as slug'
					)
				);
				$query->from('#__bsms_teachers AS t');
				$query->where('t.id = ' . (int) $pk);
				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					$app->enqueueMessage(Text::_('JBS_CMN_TEACHER_NOT_FOUND'), 'error');
				}

				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				if ($e->getCode() !== 404)
				{
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
	 * @throws \Exception
	 * @since    1.6
	 */
	protected function populateState(): void
	{
		$app = Factory::getApplication();

		// Load state from the request.
		// $input = new JInput;
		$pk = $app->input->get('id', '', 'int');
		$this->setState('teacher.id', $pk);

		$offset = $app->input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = (int) $params->get('teachertemplateid');

		if (!$t)
		{
			$t = $app->input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);
		$this->setState('params', $params);
		$user = $app->getSession()->get('user');

		if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}
	// End class
}
