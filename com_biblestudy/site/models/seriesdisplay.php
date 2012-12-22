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
 * Model class for SeriesDisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelSeriesdisplay extends JModelItem
{

	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_biblestudy.seriesdisplay';

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{

		// Load state from the request.
		$input = new JInput;
		$pk    = $input->get('id', '', 'int');
		$this->setState('series.id', $pk);
		$input  = new JInput;
		$offset = $input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = JFactory::getApplication('site')->getParams;
		$this->setState('params', $params);

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
	 * @since 7.1.0
	 * @throws Exception
	 *
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @todo  look are removing this may not used. bcc
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('series.id');

		if (!isset($this->_item[$pk]))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select(
				$this->getState(
					'item.select',
					'se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug'
				)
			);
			$query->from('#__bsms_series AS se');

			// Join over teachers
			$query->select(
				't.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail'
			);
			$query->join('LEFT', '#__bsms_teachers as t on se.teacher = t.id');
			$query->where('se.id = ' . (int) $pk);
			$db->setQuery($query);
			$data = $db->loadObject();

			if (empty($data))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_SERIES_NOT_FOUND'), 'message');

				return false;
			}

			$this->_item[$pk] = $data;
		}

		return $this->_item[$pk];
	}

//end class
}
