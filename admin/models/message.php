<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Message model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyModelMessage extends JModelAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_biblestudy.message';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
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
	 * Duplicate Check
	 *
	 * @param   int  $study_id  Study ID
	 * @param   int  $topic_id  Topic ID
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 */
	public function isDuplicate($study_id, $topic_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_studytopics')
			->where('study_id = ' . (int) $study_id)
			->where('topic_id = ' . (int) $topic_id);
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
		$id = $input->get('a_id', 0, 'int');

		if (!$id)
		{
			$id = $input->get('id', 0, 'int');
		}

		if ($id > 0)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			$query->select('topic.id, topic.topic_text, topic.params AS topic_params');
			$query->from('#__bsms_studytopics AS studytopics');

			$query->join('LEFT', '#__bsms_topics AS topic ON topic.id = studytopics.topic_id');
			$query->where('studytopics.study_id = ' . $id);

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

		$query->select('m.id, m.language, m.published, m.createdate, m.params');
		$query->from('#__bsms_mediafiles AS m');
		$query->where('m.study_id = ' . (int) $this->getItem()->id);
		$query->where('(m.published = 0 OR m.published = 1)');
		$query->order('m.createdate DESC');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = m.access');

		$db->setQuery($query->__toString());
		$mediafiles = $db->loadObjectList();

		foreach ($mediafiles AS $i => $mediafile)
		{
			$reg = new Registry;
			$reg->loadString($mediafile->params);
			$mediafiles[$i]->params = $reg;
		}

		return $mediafiles;
	}

	/**
	 * Overrides the JModelAdmin save routine to save the topics(tags)
	 *
	 * @param   string  $data  The form data.
	 *
	 * @return boolean
	 *
	 * @since 7.0.1
	 */
	public function save($data)
	{
		/** @var Joomla\Registry\Registry $params */
		$params = JBSMParams::getAdmin()->params;
		$input  = JFactory::getApplication()->input;
		$path   = 'images/biblestudy/studies/' . $data['id'];

		$this->cleanCache();

		if ($input->get('a_id'))
		{
			$data['id'] = $input->get('a_id');
		}

		// If no image uploaded, just save data as usual
		if (empty($data['image']) || strpos($data['image'], 'thumb_') !== false)
		{
			if (empty($data['image']))
			{
				// Modify model data if no image is set.
				$data['thumbnailm']     = "";
			}
			elseif (!JBSMBibleStudyHelper::startsWith(basename($data['image']), 'thumb_'))
			{
				// Modify model data
				$data['thumbnailm'] = $path . '/thumb_' . basename($data['image']);
			}

			return parent::save($data);
		}

		JBSMThumbnail::create($data['image'], $path, $params->get('thumbnail_study_size', 100));

		// Modify model data
		$data['thumbnailm'] = $path . '/thumb_' . basename($data['image']);

		return parent::save($data);
	}

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_biblestudy.message', 'message', array('control' => 'jform', 'load_data' => $loadData));

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
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_biblestudy')))
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
	 * Method to get media item
	 *
	 * @param   int  $pk  int
	 *
	 * @return  mixed|void
	 *
	 * @since   9.0.0
	 */
	public function getItem($pk = null)
	{
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$pk = $jinput->get('a_id', 0);
		}
		else
		{
			// The back end uses id so we use that the rest of the time and set it to 0 by default.
			$pk = $jinput->get('id', 0);
		}

		if (!empty($this->data))
		{
			return $this->data;
		}

		return parent::getItem($pk);
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since    11.1
	 */
	public function saveorder($pks = null, $order = null)
	{
		/** @var TableMessage $row */
		$row        = $this->getTable();
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
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
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
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return    boolean     Returns true on success, false on failure.
	 *
	 * @since    2.5
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

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

		return $done;
	}

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string  $value     The new value matching a client.
	 * @param   array   $pks       An array of row IDs.
	 * @param   array   $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchTeacher($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		/** @var TableMessage $table */
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
	 * @param   string  $value     The new value matching a client.
	 * @param   array   $pks       An array of row IDs.
	 * @param   array   $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchSeries($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		/** @var TableMessage $table */
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

	/**
	 * Batch popup changes for a group of media files.
	 *
	 * @param   string  $value     The new value matching a client.
	 * @param   array   $pks       An array of row IDs.
	 * @param   array   $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchMessagetype($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		/** @var TableMessage $table */
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
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   TableMessage  $table  A reference to a JTable object.
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		$date          = JFactory::getDate();
		$user          = JFactory::getUser();

		jimport('joomla.filter.output');

		$table->studytitle = htmlspecialchars_decode($table->studytitle, ENT_QUOTES);
		$table->alias      = JApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplicationHelper::stringURLSafe($table->studytitle);
		}

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__bsms_studies'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			// Set the values
			$table->modified    = $date->toSql();
			$table->modified_by = $user->get('id');
		}
	}
}
