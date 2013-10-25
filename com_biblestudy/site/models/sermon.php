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

if (!BIBLESTUDY_CHECKREL)
{
	jimport('joomla.application.component.modelitem');
}

/**
 * Model class for Sermon
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyModelSermon extends JModelItem
{

	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_biblestudy.sermon';

	/**
	 * Constructor
	 *
	 * @param   array $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to increment the hit counter for the study
	 *
	 * @param   int $pk  ID
	 *
	 * @access    public
	 * @return    boolean    True on success
	 *
	 * @todo      this look like it could be moved to a helper.
	 * @since     1.5
	 */
	public function hit($pk = null)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState('study.id');
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_studies')->set('hits = hits  + 1')->where('id = ' . (int) $pk);
		$db->setQuery($query);
		$db->query();

		return true;
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
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->get('id', '', 'int');
		$this->setState('study.id', $pk);

		$offset = $app->input->get('limitstart', '', 'int');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
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
	 * @param   int $pk  The id of the study.
	 *
	 * @since 7.1.0
	 * @return    mixed    Menu item data object on success, false on failure.
	 *
	 * @todo  this look like it is not needed. bcc
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('study.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select($this->getState('item.select', 's.*,CASE WHEN CHAR_LENGTH(s.alias) THEN CONCAT_WS(\':\', s.id, s.alias) ELSE s.id END as slug'));
			$query->from('#__bsms_studies AS s');

			// Join over teachers
			$query->select('t.id AS tid, t.teachername AS teachername, t.title AS teachertitle, t.image, t.imagew, t.imageh, t.teacher_thumbnail as thumb, t.thumbw, t.thumbh');

			$query->join('LEFT', '#__bsms_teachers as t on s.teacher_id = t.id');

			// Join over series
			$query->select('se.id AS sid, se.series_text, se.series_thumbnail, se.description as sdescription');
			$query->join('LEFT', '#__bsms_series as se on s.series_id = se.id');

			// Join over message type
			$query->select('mt.id as mid, mt.message_type');
			$query->join('LEFT', '#__bsms_message_type as mt on s.messagetype = mt.id');

			// Join over books
			$query->select('b.bookname as bookname');
			$query->join('LEFT', '#__bsms_books as b on s.booknumber = b.booknumber');

			// Join over locations
			$query->select('l.id as lid, l.location_text');
			$query->join('LEFT', '#__bsms_locations as l on s.location_id = l.id');

			// Join over topics
			$query->select('group_concat(stp.id separator ", ") AS tp_id, group_concat(stp.topic_text separator ", ")
				 as topic_text, group_concat(stp.params separator ", ") as topic_params');
			$query->join('LEFT', '#__bsms_studytopics as tp on s.id = tp.study_id');
			$query->join('LEFT', '#__bsms_topics as stp on stp.id = tp.topic_id');

			// Join over media files
			$query->select('sum(m.plays) AS totalplays, sum(m.downloads) AS totaldownloads, m.id');
			$query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
			$query->join('LEFT', '#__bsms_mediafiles AS m on s.id = m.study_id');

			$query->group('s.id');
			$query->where('s.id = ' . (int) $pk);
			$db->setQuery($query);
			$data = $db->loadObject();

			if (empty($data))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_STUDY_NOT_FOUND', 'error'));

				return false;
			}

			// Concat topic_text and concat topic_params do not fit, so translate individually
			$topic_text       = JBSMTranslated::getTopicItemTranslated($data);
			$data->id         = $pk;
			$data->topic_text = $topic_text;
			$data->bookname      = JText::_($data->bookname);

            $registry = new JRegistry;
            $registry->loadString($data->params);
            $data->params = $registry;
            $template = JBSMParams::getTemplateparams();

            $data->params->merge($template->params);
            $mparams = clone $this->getState('params');
            $data->params->merge($mparams);

			$a_params           = JBSMParams::getAdmin();
			$data->admin_params = $a_params->params;

			// Compute selected asset permissions.
			$user = JFactory::getUser();

			// Technically guest could edit an article, but lets not check that to improve performance a little.
			if (!$user->get('guest'))
			{
				$userId = $user->get('id');
				$asset  = 'com_biblestudy.message.' . $data->id;

				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$data->params->set('access-edit', true);
				}
				// Now check if edit.own is available.
				elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					$data->params->set('access-edit', true);
				}
			}

			// Compute view access permissions.
			$access = $this->getState('filter.access');

			if ($access)
			{
				// If the access filter has been set, we already know this user can view.
				$data->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				$user   = JFactory::getUser();
				$groups = $user->getAuthorisedViewLevels();

				$data->params->set('access-view', in_array($data->access, $groups));
			}

			$this->_item[$pk] = $data;
		}

		return $this->_item[$pk];
	}

	/**
	 * Method to retrieve comments for a study
	 *
	 * @access  public
	 * @return object
	 */
	public function getComments()
	{
		$app = JFactory::getApplication('site');
		$id  = $app->input->get('id', '', 'int');

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('c.*')->from('#__bsms_comments AS c')->where('c.published = 1')->where('c.study_id = ' . $id)->order('c.comment_date asc');
		$db->setQuery($query);
		$comments = $db->loadObjectList();

		return $comments;
	}

	/**
	 * Method to store a record
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function storecomment()
	{
		$row                  = $this->getTable('comment');
		$input                = new JInput;
		$data                 = $_POST;
		$data['comment_text'] = $input->get('comment_text', '', 'string');

		// Bind the form fields to the table
		$row->bind($data);

		// Make sure the record is valid
		if (!$row->check())
		{
			return false;
		}

		// Store the table to the database
		if (!$row->store())
		{
			return false;
		}

		return true;
	}


// End class
}
