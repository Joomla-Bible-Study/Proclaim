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
 * Styles list model class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BiblestudyModelstyles extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param   array $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'style.id',
				'published', 'style.published',
				'ordering', 'style.ordering',
				'access', 'style.access',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Populate State.
	 *
	 * @param   string $ordering   An optional ordering field.
	 * @param   string $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since 7.1.0
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

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		parent::populateState('style.filename', 'ASC');
	}

	/**
	 * Get List Query
	 *
	 * @return string
	 */
	protected function getListQuery()
	{

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'style.id, style.published, style.filename, style.stylecode')
		);
		$query->from('`#__bsms_styles` AS style');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('style.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(style.published = 0 OR style.published = 1)');
		}

		return $query;
	}

}
