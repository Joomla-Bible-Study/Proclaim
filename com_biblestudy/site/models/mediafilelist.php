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

// Base this model on the backend version.
JLoader::register('BiblestudyModelMediafiles', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/mediafiles.php');

/**
 * Model class for MediaFiles
 *
 * @property mixed _data
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelMediafilelist extends BiblestudyModelMediafiles
{

	/**
	 * Build Query
	 *
	 * @return string
	 *
	 * @todo may not be needed.
	 */
	public function _buildQuery()
	{
		$where   = $this->_buildContentWhere();
		$orderby = $this->_buildContentOrderBy();
		$query   = ' SELECT m.*, s.id AS sid, s.studytitle, md.media_image_name, md.id AS mid'
			. ' FROM #__bsms_mediafiles AS m'
			. ' LEFT JOIN #__bsms_studies AS s ON (s.id = m.study_id)'
			. ' LEFT JOIN #__bsms_media AS md ON (md.id = m.media_image)'
			. $where
			. $orderby;

		return $query;
	}

	/**
	 * Retrieves the data
	 *
	 * @return array Array of objects containing the data from the database
	 *
	 * @todo may not be needed.
	 */
	public function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query, (int) $this->getState('limitstart'), (int) $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Build Content Where
	 *
	 * @return string
	 *
	 * @todo may not be needed.
	 */
	public function _buildContentWhere()
	{
		$mainframe      = JFactory::getApplication();
		$input          = new JInput;
		$option         = $input->get('option', '', 'cmd');
		$where          = array();
		$filter_studyid = $mainframe->getUserStateFromRequest($option . 'filter_studyid', 'filter_studyid', 0, 'int');

		if ($filter_studyid > 0)
		{
			$where[] = 'm.study_id = ' . (int) $filter_studyid;
		}
		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		return $where;
	}

	/**
	 * Build Content Order By
	 *
	 * @return string
	 *
	 * @todo may not be needed.
	 */
	public function _buildContentOrderBy()
	{
		$mainframe        = JFactory::getApplication();
		$input            = new JInput;
		$option           = $input->get('option', '', 'cmd');
		$orders           = array(
			'id',
			'published',
			'studytitle',
			'ordering',
			'media_image_name',
			'createdate',
			'filename'
		);
		$filter_order     = $mainframe->getUserStateFromRequest(
			$option . 'filter_order',
			'filter_order',
			'ordering',
			'cmd'
		);
		$filter_order_Dir = strtoupper(
			$mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'ASC')
		);

		if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC')
		{
			$filter_order_Dir = 'ASC';
		}
		if (!in_array($filter_order, $orders))
		{
			$filter_order = 'ordering';
		}

		if ($filter_order == 'ordering')
		{
			$orderby = ' ORDER BY study_id, ordering ' . $filter_order_Dir;
		}
		else
		{
			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , study_id, ordering ';
		}

		return $orderby;
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
	 * @since   11.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		// Load the parameters.
		$params = JFactory::getApplication('site')->getParams();
		$this->setState('params', $params);

		$filename = $this->getUserStateFromRequest($this->context . '.filter.filename', 'filter_filename');
		$this->setState('filter.filename', $filename);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
		$this->setState('filter.state', $state);

		$study = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
		$this->setState('filter.studytitle', $study);

		$mediaTypeId = $this->getUserStateFromRequest($this->context . '.filter.mediatype', 'filter_mediatypeId');
		$this->setState('filter.mediatypeId', $mediaTypeId);

		parent::populateState('mediafile.createdate', 'DESC');
	}

}
