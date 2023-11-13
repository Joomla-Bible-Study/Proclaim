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

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use JApplicationSite;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Teachers
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmteachersModel extends ListModel
{
	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  DatabaseQuery A DatabaseQuery object to retrieve the data set.
	 *
	 * @throws \Exception
	 * @since   7.0.0
	 */
	protected function getListQuery(): DatabaseQuery
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		// See if this view is being filtered by language in the menu
		$app  = Factory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getActive();

		if (isset($item->language))
		{
			$language = $db->quote($item->language) . ',' . $db->quote('*');
		}
		else
		{
			$language = $db->quote('*');
		}

		$query = $db->getQuery(true);
		$query->select('teachers.*,CASE WHEN CHAR_LENGTH(teachers.alias) THEN CONCAT_WS(\':\', teachers.id, teachers.alias)'
			. 'ELSE teachers.id END as slug'
		);
		$query->from('#__bsms_teachers as teachers');
		$query->select('s.id as sid');
		$query->join('LEFT', '#__bsms_studies as s on teachers.id = s.teacher_id');
		$query->where('teachers.language in (' . $language . ')');
		$query->where('teachers.published = 1 AND teachers.list_show = 1');
		$query->order('teachers.ordering, teachers.teachername ASC');
		$query->group('teachers.id');

		return $query;
	}

	/**
	 * Populate the State
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function populateState($ordering = 'teachers.ordering', $direction = 'asc'): void
	{
		/** @type JApplicationSite $app */
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id', '');
		$this->setState('sermon.id', $pk);

		$offset = $app->input->getInt('limitstart', '');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		$template = Cwmparams::getTemplateparams();
		$admin    = Cwmparams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = (int) $params->get('teachersid');

		if (!$t)
		{
			$t = $app->input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$user = $app->getSession()->get('user');

		if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		$this->setState('filter.language', $app->getLanguageFilter());
	}

	/**
	 * Method to get a list of sermons.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @throws \Exception
	 * @since   9.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (Factory::getApplication()->isClient('site'))
		{
			$user   = Factory::getApplication()->getIdentity();
			$groups = $user->getAuthorisedViewLevels();

			foreach ($items as $x => $xValue)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($xValue->access, $groups, true))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}
}
