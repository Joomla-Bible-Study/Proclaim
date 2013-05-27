<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Model class for Teachers
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelTeachers extends JModelList
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
		$db = $this->getDbo();

		// See if this view is being filtered by language in the menu
		$app  = JFactory::getApplication();
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
	 * @param   string $ordering   ?
	 * @param   string $direction  ?
	 *
	 * @return void
	 */
	protected function populateState($ordering = 'teachers.ordering', $direction = 'asc')
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('sermon.id', $pk);

		$offset = $app->input->getUInt('limitstart');
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

		$this->setState('filter.language', $app->getLanguageFilter());
	}
}
