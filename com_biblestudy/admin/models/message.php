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

jimport('joomla.application.component.modeladmin');

/**
 * Message model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelMessage extends JModelAdmin
{

	/**
	 * Admin
	 *
	 * @var string
	 */
	private $_admin = null;

	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Method to store a record
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function store()
	{
		// Fix up special html fields

		$row        = & $this->getTable();
		$input      = new JInput;
		$data       = $input->post;
		$scriptures = null;

		// Allows HTML content to come through to the database row
		$data['studytext']           = $input->get('studytext', '', 'string');
		$data['studyintro']          = str_replace('"', "'", $data['studyintro']);
		$data['studynumber']         = str_replace('"', "'", $data['studynumber']);
		$data['secondary_reference'] = str_replace('"', "'", $data['secondary_reference']);

		foreach ($data['scripture'] as $scripture)
		{
			if (!$data['text'][key($data['scripture'])] == '')
			{
				$scriptures[] = $scripture . ' ' . $data['text'][key($data['scripture'])];
			}
			next($data['scripture']);
		}
		$data['scripture'] = implode(';', $scriptures);

		// Bind the form fields to the table
		if (!$row->bind($data))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Make sure the record is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store the table to the database
		// Checks to make sure a valid date field has been entered
		if (!$row->studydate)
		{
			$row->studydate = date('Y-m-d H:i:s');
		}
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Get Tags
		$vTags = $input->get('topic_tags', '', 'string');
		$iTags = explode(",", $vTags);

		JTable::addIncludePath(
			JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'tables'
		);

		foreach ($iTags as $aTag)
		{
			if (is_numeric($aTag))
			{
				// It's an existing tag.  Add it
				if ($aTag != "")
				{

					$tagRow = & JTable::getInstance('studytopics', 'Table');

					$isDup = $this->isDuplicate($row->id, $aTag);

					if (!$isDup)
					{
						$tagRow->study_id = $row->id;
						$tagRow->topic_id = $aTag;

						if (!$tagRow->store())
						{
							$this->setError($this->_db->getErrorMsg());

							return false;
						}
					}
				}
			}
			else
			{
				// It's a new tag.  Gotta insert it into the Topics table.
				if ($aTag != "")
				{
					$topicRow             = & JTable::getInstance('topic', 'Table');
					$tempText             = $aTag;
					$tempText             = str_replace("0_", "", $tempText);
					$topicRow->topic_text = $tempText;
					$topicRow->published  = 1;

					if (!$topicRow->store())
					{
						$this->setError($this->_db->getErrorMsg());

						return false;
					}

					// Gotta somehow make sure this isn't a duplicate...
					$tagRow           = & JTable::getInstance('studytopics', 'Table');
					$tagRow->study_id = $row->id;
					$tagRow->topic_id = $topicRow->id;

					$isDup = $this->isDuplicate($row->id, $aTag);

					if (!$isDup)
					{
						if (!$tagRow->store())
						{
							$this->setError($this->_db->getErrorMsg());

							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Duplicate Check
	 *
	 * @param   int $study_id  Study ID
	 * @param   int $topic_id  Topic ID
	 *
	 * @return boolean
	 *
	 * @todo look like this was not implemented. Need to look into this and see if it is still needed, (I didn't write this one)
     * I don't know where this came from. Tom didn't write it either
	 */
	public function isDuplicate($study_id, $topic_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__bsms_studytopics')->where('study_id = ' . (int) $study_id)->where('topic_id = ' . (int) $topic_id);
		$db->setQuery($query);
		$tresult = $db->loadObject();

		if (empty($tresult))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Gets all the topics associated with a particular study
	 *
	 * @return object JSON Object containing the topics
	 *
	 * @since 7.0.1
	 */
	public function getTopics()
	{
		// Do search in case of present study only, suppress otherwise
		$input          = new JInput;
		$translatedList = array();

		if ($input->get('id', 0, 'int') > 0)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			$query->select('topic.id, topic.topic_text, topic.params AS topic_params');
			$query->from('#__bsms_studytopics AS studytopics');

			$query->join('LEFT', '#__bsms_topics AS topic ON topic.id = studytopics.topic_id');
			$query->where('studytopics.study_id = ' . $input->get('id', 0, 'int'));

			$db->setQuery($query->__toString());
			$topics = $db->loadObjectList();

			if ($topics)
			{
				foreach ($topics as $topic)
				{
					$text             = JBSMTranslated::getTopicItemTranslated($topic);
					$translatedList[] = array(
						'id'   => $topic->id,
						'name' => $text
					);
				}
			}
		}

		return json_encode($translatedList);
	}

	/**
	 * Gets all topics available
	 *
	 * @return object JSON Object containing the topics
	 *
	 * @since 7.0.1
	 */
	public function getAlltopics()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('topic.id, topic.topic_text, topic.params AS topic_params');
		$query->from('#__bsms_topics AS topic');

		$db->setQuery($query->__toString());
		$topics         = $db->loadObjectList();
		$translatedList = array();

		if ($topics)
		{

			foreach ($topics as $topic)
			{
				$text             = JBSMTranslated::getTopicItemTranslated($topic);
				$translatedList[] = array(
					'id'   => $topic->id,
					'name' => $text
				);
			}
		}

		return json_encode($translatedList);
	}

	/**
	 * Returns a list of media files associated with this study
	 *
	 * @since   7.0
	 * @return object
	 */
	public function getMediaFiles()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('mediafile.id, mediafile.filename, mediafile.createdate, media.media_image_name');
		$query->from('#__bsms_mediafiles AS mediafile');
        $query->join('LEFT','#__bsms_media as media ON media.id = mediafile.media_image');
		$query->where('mediafile.study_id = ' . (int) $this->getItem()->id);
		$query->order('mediafile.createdate DESC');

		$db->setQuery($query->__toString());

		return $db->loadObjectList();
	}

	/**
	 * Overrides the JModelAdmin save routine to save the topics(tags)
	 *
	 * @param   string $data  The form data.
	 *
	 * @return boolean
	 *
	 * @since 7.0.1
	 */
	public function save($data)
	{
		if (parent::save($data))
		{
			$this->setTopics((int) $this->getState($this->getName() . '.id'), $data);

			return true;
		}

		return false;
	}

	/**
	 * Routine to save the topics(tags)
	 *
	 * @param   int    $pks   Is the id of the record being saved.
	 * @param   string $data  from post
	 *
	 * @return boolean
	 *
	 * @since 7.0.2
	 */
	public function setTopics($pks, $data)
	{

		if (empty($pks))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_NO_ITEM_SELECTED'));

			return false;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Clear the tags first
		$query->delete();
		$query->from('#__bsms_studytopics');
		$query->where('study_id = ' . $pks);
		$db->setQuery($query->__toString());

		if (!$db->query())
		{
			return false;
		}
		$query->clear();

		// Add all the tags back
		if ($data['topics'])
		{
			$topics = explode(",", $data['topics']);

			foreach ($topics as $topic)
			{
				$tdata           = new stdClass;
				$tdata->topic_id = $topic;
				$tdata->study_id = $pks;

				if (!$db->insertObject('#__bsms_studytopics', $tdata))
				{
					return false;
				}
			}

		}

		return true;
	}

	/**
	 * Get the form data
	 *
	 * @param   array   $data      Data for the form.
	 * @param   boolean $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_biblestudy.message',
			'message',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		else
		{
			// The back end uses id so we use that the rest of the time and set it to 0 by default.
			$id = $jinput->get('id', 0);
		}

		$user = JFactory::getUser();

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_biblestudy.message.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_biblestudy'))
		)
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');

		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.message.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string $type    The table type to instantiate
	 * @param   string $prefix  A prefix for the table class name. Optional.
	 * @param   array  $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Message', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable $table  A reference to a JTable object.
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->studytitle = htmlspecialchars_decode($table->studytitle, ENT_QUOTES);
		$table->alias      = JApplication::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplication::stringURLSafe($table->studytitle);
		}

		if (empty($table->id))
		{

			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__bsms_studies');
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return $pk;
	}

	/**
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string  $group      The cache group
	 * @param   integer $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_biblestudy');
		parent::cleanCache('mod_biblestudy');
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array   $pks    An array of primary key ids.
	 * @param   integer $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since    11.1
	 */
	public function saveorder($pks = null, $order = null)
	{
		$row        = & $this->getTable();
		$conditions = array();

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$row->load((int) $pk);

			// Track categories
			$groupings[] = $row->id;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($row);
				$found     = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key          = $row->getKeyName();
					$conditions[] = array($row->$key, $condition);
				}
			}
		}

		foreach ($conditions as $cond)
		{
			// $row->reorder('id = ' . (int) $group);
			$row->load($cond[0]);
			$row->reorder($cond[1]);
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array $commands  An array of commands to perform.
	 * @param   array $pks       An array of item ids.
	 * @param   array $contexts  An array of item contexts.
	 *
	 * @return    boolean     Returns true on success, false on failure.
	 *
	 * @since    2.5
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

			return false;
		}

		$done = false;

		if (strlen($commands['teacher']) > 0)
		{
			if (!$this->batchTeacher($commands['teacher'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (strlen($commands['series']) > 0)
		{
			if (!$this->batchSeries($commands['series'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (strlen($commands['messagetype']) > 0)
		{
			if (!$this->batchMessagetype($commands['messagetype'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchTeacher($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->teacher_id = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchMessagetype($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->messagetype = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string $value     The new value matching a client.
	 * @param   array  $pks       An array of row IDs.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchSeries($value, $pks, $contexts)
	{
		// Set the variables
		$user  = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$table->series_id = (int) $value;

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}


}
