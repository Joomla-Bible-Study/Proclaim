<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Model;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;

// No Direct Access
defined('_JEXEC') or die;

/**
 * Model class for Teachers
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMTeachersModel extends ListModel
{
	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0.0
	 */
	protected function getListQuery()
	{
		$db = Factory::getApplication()->getDbo();

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
		$query->select('teachers.*,CASE WHEN CHAR_LENGTH(teachers.alias) THEN CONCAT_WS(\':\', teachers.id, teachers.alias) ELSE teachers.id END as slug');
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
	 * @since 7.0
	 */
	protected function populateState($ordering = 'teachers.ordering', $direction = 'asc')
	{
		/** @type JApplicationSite $app */
		$app = Factory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id', '');
		$this->setState('sermon.id', $pk);

		$offset = $app->input->getUInt('limitstart', '');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = $params->get('teachersid');

		if (!$t)
		{
			$input = Factory::getApplication();
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$user = Factory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy')))
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
	 * @since   9.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (Factory::getApplication()->isClient('site'))
		{
			$user   = Factory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}
}
