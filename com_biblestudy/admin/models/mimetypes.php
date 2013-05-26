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
 * MimeTypes model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelMimetypes extends JModelList
{
	/**
	 * Deletes
	 *
	 * @var string
	 */
	private $_deletes;

	/**
	 * Get Deleted
	 *
	 * @return object
	 */
	public function getDeletes()
	{
		if (empty($this->_deletes))
		{
			$query          = 'SELECT allow_deletes'
				. ' FROM #__bsms_admin'
				. ' WHERE id = 1';
			$this->_deletes = $this->_getList($query);
		}

		return $this->_deletes;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering   An optional ordering field.
	 * @param   string $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   7.0
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

		parent::populateState('mimetype.mimetext', 'ASC');
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 */
	protected function getListQuery()
	{

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select', 'mimetype.id, mimetype.mimetype, mimetype.mimetext, mimetype.published')
		);
		$query->from('`#__bsms_mimetype` AS mimetype');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('mimetype.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(mimetype.published = 0 OR mimetype.published = 1)');
		}

		return $query;
	}

}
