<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;

defined('_JEXEC') or die;

/**
 * Comments model class
 *
 * @since  7.0.0
 */
class CWMCommentsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'comment.id',
				'published', 'comment.published',
				'ordering', 'comment.ordering',
				'studytitle', 'study.studytitle',
				'bookname', 'comment.bookname',
				'createdate', 'comment.createdate',
				'full_name', 'comment.full_name',
				'language', 'comment.language'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Populate State
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since 7.0
	 * @throws \Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app    = Factory::getApplication();
		$layout = $app->input->get('layout');

		// Adjust the context to support modal layouts.
		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState('comment.comment_date', 'desc');
	}

	/**
	 * Get Stored ID
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since 7.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * List Query
	 *
	 * @return  \Joomla\Database\QueryInterface   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = Factory::getContainer()->get('DatabaseDriver');

		// Select the required fields from the table.
		$query = $db->getQuery(true);
		$query->select(
			$this->getState(
				'list.select', 'comment.id, comment.published, comment.user_id, comment.full_name, comment.user_email, '
				. 'comment.comment_date, comment.comment_text, comment.access, comment.language, comment.asset_id')
		);
		$query->from('#__bsms_comments AS comment');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = comment.language');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('comment.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(comment.published = 0 OR comment.published = 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('comment.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(study.studytitle LIKE ' . $search . ' OR book.bookname LIKE ' . $search . ')');
			}
		}

		// Join over Studies
		$query->select('study.studytitle AS studytitle, study.chapter_begin, study.studydate, study.booknumber');
		$query->join('LEFT', '#__bsms_studies AS study ON study.id = comment.study_id');

		// Join over books
		$query->select('book.bookname as bookname');
		$query->join('LEFT', '#__bsms_books as book ON book.booknumber = study.booknumber');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = comment.access');

		// Add the list ordering clause
		$orderCol  = $this->state->get('list.ordering', 'study.studytitle');
		$orderDirn = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
