<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

JLoader::register('JBSMParams', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helper/params.php');

/**
 * Model class for SeriesDisplays
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelSeriesdisplays extends JModelList
{

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Adjust the context to support modal layouts.
		$input  = new JInput;
		$layout = $input->get('layout');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}
		$params = JComponentHelper::getParams('com_biblestudy');
		$this->setState('params', $params);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
		$series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
		$this->setState('filter.series', $series);
		$this->setState('filter.language', JLanguageHelper::getLanguages());

		// Process show_noauth parameter
		if (!$params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}
		$input = new JInput;
		$this->setState('layout', $input->get('layout', '', 'cmd'));
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   7.0
	 */
	protected function getListQuery()
	{
		$db              = $this->getDbo();
		$template_params = JBSMParams::getTemplateparams();
		$t_params        = $template_params->params;
		$app             = JFactory::getApplication('site');
		$params          = JComponentHelper::getParams('com_biblestudy');
		$menuparams      = new JRegistry;
		$menu            = $app->getMenu()->getActive();

		if ($menu)
		{
			$menuparams->loadString($menu->params);
		}
		$query = $db->getQuery(true);
		$query->select('se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug');
		$query->from('#__bsms_series as se');
		$query->select('t.id as tid, t.teachername, t.title as teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail');
		$query->join('LEFT', '#__bsms_teachers as t on se.teacher = t.id');
		$where = $this->_buildContentWhere();
		$query->where($where);

		// Filter by language
		$language = $params->get('language', '*');

		if ($this->getState('filter.language') || $language != '*')
		{
			$query->where('se.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}
		$orderparam = $params->get('default_order');

		if (empty($orderparam))
		{
			$orderparam = $t_params->get('default_order', '1');
		}
		if ($orderparam == 2)
		{
			$order = "ASC";
		}
		else
		{
			$order = "DESC";
		}
		$orderstate = $this->getState('filter.order');

		if (!empty($orderstate))
		{
			$order = $orderstate;
		}

		$query->order('series_text ' . $order);

		return $query;
	}

	/**
	 * Build Content of series
	 *
	 * @return string
	 */
	public function _buildContentWhere()
	{
		$mainframe     = JFactory::getApplication();
		$input         = new JInput;
		$option        = $input->get('option', '', 'cmd');
		$params        = JComponentHelper::getParams($option);
		$filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$where         = array();
		$where[]       = ' se.published = 1';

		if ($filter_series > 0)
		{
			$where[] = ' se.id = ' . (int) $filter_series;
		}

		$where = (count($where) ? implode(' AND ', $where) : '');

		$where2   = array();
		$continue = 0;

		if ($params->get('series_id') && !$filter_series)
		{

			$filters = $params->get('series_id');

			switch ($filters)
			{
				case is_array($filters) :
					foreach ($filters AS $filter)
					{
						if ($filter == -1)
						{
							break;
						}
						{
							$continue = 1;
							$where2[] = 'se.id = ' . (int) $filter;
						}
					}
					break;

				case -1:
					break;

				default:
					$continue = 1;
					$where2[] = 'se.id = ' . (int) $filters;
					break;
			}
		}
		$where2 = (count($where2) ? ' ' . implode(' OR ', $where2) : '');

		if ($continue > 0)
		{
			$where = $where . ' AND ( ' . $where2 . ')';
		}

		return $where;
	}

	/**
	 * Get a list of all used series
	 *
	 * @since 7.0
	 * @return Object
	 */
	public function getSeries()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('series.id AS value, series.series_text AS text, series.access');
		$query->from('#__bsms_series AS series');
		$query->join('INNER', '#__bsms_studies AS study ON study.series_id = series.id');
		$query->group('series.id');
		$query->order('series.series_text');

		$db->setQuery($query->__toString());
		$items = $db->loadObjectList();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($items);

		if ($count > 0)
		{
			for ($i = 0; $i < $count; $i++)
			{

				if ($items[$i]->access > 1)
				{
					if (!in_array($items[$i]->access, $groups))
					{
						unset($items[$i]);
					}
				}
			}
		}

		return $items;
	}

}
