<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Teacher model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelTeacher extends JModelAdmin
{
	/**
	 * Controller Prefix
	 *
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since    1.7.0
	 */
	public function getTable($name = 'Teacher', $prefix = 'Table', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_users/models/fields');

		// Get the form.
		$form = $this->loadForm('com_biblestudy.teacher', 'teacher', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the
	 *                   component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		$tmp        = (array) $record;
		$db         = JFactory::getDbo();
		$user       = JFactory::getUser();
		$canDoState = $user->authorise('core.edit.state', $this->option);
		$text       = '';

		if (!empty($tmp))
		{
			$query = $db->getQuery(true);
			$query->select('id, studytitle')
				->from('#__bsms_studies')
				->where('teacher_id = ' . $record->id)
				->where('published != ' . $db->q('-2'));
			$db->setQuery($query);
			$studies = $db->loadObjectList();

			if (!$studies && $canDoState)
			{
				return true;
			}

			if ($record->published == '-2' || $record->published == '0')
			{
				foreach ($studies as $studie)
				{
					$text .= ' ' . $studie->id . '-"' . $studie->studytitle . '",';
				}

				JFactory::getApplication()->enqueueMessage(JText::_('JBS_TCH_CAN_NOT_DELETE') . $text);
			}

			return false;
		}
		else
		{
			return $canDoState;
		}
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
		return $pk;
	}

	/**
	 * Saves data creating image thumbnails
	 *
	 * @param   array  $data  Data
	 *
	 * @return bool
	 *
	 * @since 9.0.0
	 */
	public function save($data)
	{
		/** @var Joomla\Registry\Registry $params */
		$params = JBSMParams::getAdmin()->params;

		// If no image uploaded, just save data as usual
		if (empty($data['image']) || strpos($data['image'], 'thumb_') !== false)
		{
			if (empty($data['image']))
			{
				// Modify model data if no image is set.
				$data['teacher_image']     = "";
				$data['teacher_thumbnail'] = "";
			}

			return parent::save($data);
		}

		$path = 'images/biblestudy/teachers/' . $data['id'];
		JBSMThumbnail::create($data['image'], $path, $params->get('thumbnail_teacher_size', 100));

		// Modify model data
		$data['teacher_image']     = JPATH_ROOT . '/' . $data['image'];
		$data['teacher_thumbnail'] = $path . '/thumb_' . basename($data['image']);

		return parent::save($data);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.teacher.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  The id of the primary key.
	 *
	 * @return    mixed    Object on success, false on failure.
	 *
	 * @since    1.7.0
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item)
		{
			// Convert the params field to an array.
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   TableTeacher  $table  A reference to a JTable object.
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		$table->teachername = htmlspecialchars_decode($table->teachername, ENT_QUOTES);
		$table->alias       = JApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplicationHelper::stringURLSafe($table->teachername);
		}

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('MAX(ordering)')->from('#__bsms_teachers');
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
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
}
