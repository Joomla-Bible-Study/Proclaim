<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

/**
 * Database Helper class for version 7.1.0
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class JBSMDbHelper
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	public static $install_state = null;

	/**
	 * System to Check if Table Exists
	 *
	 * @param   string  $cktable  Table to check for exp:"#__bsms_admin
	 *
	 * @return bool  If table is there True else False if not.
	 *
	 * @since 7.0
	 */
	public static function checkIfTable($cktable)
	{
		$db     = JFactory::getDbo();
		$tables = $db->getTableList();
		$prefix = $db->getPrefix();

		foreach ($tables AS $table)
		{
			$tableAF = str_replace($prefix, "#__", $table);

			if ($tableAF == $cktable)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Alters a table
	 * command is only needed for MODIFY. Can be used to ADD, DROP, MODIFY, or CHANGE tables.
	 *
	 * @param   array   $tables  Tables is an array of tables, fields, type of query and optional command line
	 * @param   string  $from    Where the query is coming from for msg
	 *
	 * @return boolean
	 *
	 * @since   7.0
	 * @throws  \Exception
	 */
	public static function alterDB($tables, $from = null)
	{
		$db = JFactory::getDbo();

		foreach ($tables as $t)
		{
			$type    = strtolower($t['type']);
			$command = $t['command'];
			$table   = $t['table'];
			$field   = $t['field'];

			switch ($type)
			{
				case 'drop':
					if (!$table || !$field)
					{
						break;
					}

					// Check the field to see if it exists first
					if (self::checkTables($table, $field) === true)
					{
						$query = 'ALTER TABLE ' . $db->qn($table) . ' DROP ' . $db->qn($field);

						if (!self::performDB($query, $from))
						{
							return false;
						}
					}
					break;

				case 'index':
					if (!$table || !$field)
					{
						break;
					}

					$query = 'ALTER TABLE ' . $db->qn($table) . ' ADD INDEX ' . $db->qn($field) . ' ' . $command;

					if (!self::performDB($query, $from))
					{
						return false;
					}

					break;

				case 'add':
					if (!$table || !$field)
					{
						break;
					}

					if (self::checkTables($table, $field) !== true)
					{
						$query = 'ALTER TABLE ' . $db->qn($table) . ' ADD ' . $db->qn($field) . ' ' . $command;

						if (!self::performDB($query, $from))
						{
							return false;
						}
					}
					break;

				case 'column':
					if (!$table || !$field)
					{
						break;
					}

					if (self::checkTables($table, $field) !== true)
					{
						$query = 'ALTER TABLE ' . $db->qn($table) . ' ADD COLUMN' . $db->qn($field) . ' ' . $command;

						if (!self::performDB($query, $from))
						{
							return false;
						}
					}
					break;

				case 'modify':
					if (!$table || !$field)
					{
						break;
					}

					if (self::checkTables($table, $field) === true)
					{
						$query = 'ALTER TABLE ' . $db->qn($table) . ' MODIFY ' . $db->qn($field) . ' ' . $command;

						if (!self::performDB($query, $from))
						{
							return false;
						}
					}
					break;

				case 'change':
					if (!$table || !$field)
					{
						break;
					}

					if (self::checkTables($table, $field) === true)
					{
						$query = 'ALTER TABLE ' . $db->qn($table) . ' CHANGE ' . $db->qn($field) . ' ' . $command;

						if (!self::performDB($query, $from))
						{
							return false;
						}
					}
			}
		}

		return true;
	}

	/**
	 * Discover the fields in a table
	 *
	 * @param   string  $table  Is the table you are checking
	 * @param   string  $field  Checking against.
	 *
	 * @return boolean false equals field does not exist
	 *
	 * @since 7.0
	 */
	public static function checkTables($table, $field)
	{
		$db     = JFactory::getDbo();

		$fields = $db->getTableColumns($table, 'false');

		if ($fields)
		{
			if (array_key_exists($field, $fields) === true)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * performs a database query
	 *
	 * @param   string  $query  Is a Joomla ready query
	 * @param   string  $from   Where the source of the query comes from
	 * @param   int     $limit  Set the Limit of the query
	 *
	 * @return boolean true if success, or error string if failed
	 *
	 * @since   7.0
	 * @throws  Exception
	 */
	public static function performDB($query, $from = null, $limit = null)
	{
		if (!$query)
		{
			return false;
		}

		$db = JFactory::getDbo();
		$db->setQuery($query, 0, $limit);

		if (!$db->execute())
		{
			JFactory::getApplication()->enqueueMessage($from . JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

			return false;
		}
		else
		{
			JLog::add($from . $query, JLog::INFO, 'com_biblestudy');

			return true;
		}
	}

	/**
	 * Checks a table for the existence of a field, if it does not find it, runs the Admin model fix()
	 *
	 * @param   string  $table  table is the table you are checking
	 * @param   string  $field  field you are checking
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 * @throws Exception
	 */
	public static function checkDB($table, $field)
	{
		$done = self::checkTables($table, $field);

		if (!$done)
		{
			/** @var BiblestudyModelAdmin $admin */
			$admin = JModelLegacy::getInstance('Admin', 'BiblestudyModel');
			$admin->fix();

			return true;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Get Objects for tables
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public static function getObjects()
	{
		$db        = JFactory::getDbo();
		$tables    = $db->getTableList();
		$prefix    = $db->getPrefix();
		$prelength = strlen($prefix);
		$bsms      = 'bsms_';
		$objects   = array();

		foreach ($tables as $table)
		{
			if (strstr($table, $prefix) && strstr($table, $bsms))
			{
				$table     = substr_replace($table, '#__', 0, $prelength);
				$objects[] = array('name' => $table);
			}
		}

		return $objects;
	}

	/**
	 * Get State of install for Main Admin Controller
	 *
	 * @return  bool false if table exists | true if dos not
	 *
	 * @since 7.1.0
	 */
	public static function getInstallState()
	{
		if (!is_bool(self::$install_state))
		{
			$db = JFactory::getDbo();

			// Check if JBSM can be found from the database
			$table = $db->getPrefix() . 'bsms_admin';
			$db->setQuery("SHOW TABLES LIKE {$db->quote($table)}");

			if ($db->loadResult() != $table)
			{
				self::$install_state = true;
			}
			else
			{
				self::$install_state = false;
			}
		}

		return self::$install_state;
	}

	/**
	 * Fix up css.
	 *
	 * @param   string   $filename  Name of css file
	 * @param   boolean  $parent    if coming form the update script
	 * @param   string   $newcss    New css style
	 * @param   int      $id        this is the id of record to be fixed
	 *
	 * @return boolean
	 *
	 * @since   7.1.0
	 * @throws  \Exception
	 */
	public static function fixupcss($filename, $parent, $newcss, $id = null)
	{
		$app = JFactory::getApplication();
		/* Start by getting existing Style */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__bsms_styles');

		if ($filename)
		{
			$query->where($db->qn('filename') . ' = ' . $db->q($filename));
		}
		else
		{
			$query->where($db->qn('id') . ' = ' . (int) $id);
		}

		$db->setQuery($query);
		$result = $db->loadObject();
		$oldcss = $result->stylecode;

		/* Now the arrays of changes that need to be done. */
		$oldlines = array(
			".bsm_teachertable_list", "#bslisttable", "#bslisttable", "#landing_table", "#landing_separator",
			"#landing_item", "#landing_title", "#landinglist"
		);
		$newlines = array(
			"#bsm_teachertable_list", ".bslisttable", ".bslisttable", ".landing_table", ".landing_separator",
			".landing_item", ".landing_title", ".landinglist"
		);
		$oldcss   = str_replace($oldlines, $newlines, $oldcss);

		/* now see if we are adding new css to the db css */
		if ($parent || $newcss)
		{
			$newcss = $db->escape($newcss) . ' ' . $oldcss;
		}
		else
		{
			$newcss = $oldcss;
		}

		/* no apply the new css back to the table */
		$query = $db->getQuery(true);
		$query->update('#__bsms_styles')->set('stylecode="' . $newcss . '"');

		if ($filename)
		{
			$query->where($db->qn('filename') . ' = ' . $db->q($filename));
		}
		else
		{
			$query->where($db->qn('id') . ' = ' . (int) $id);
		}

		$db->setQuery($query);

		if (!$db->execute())
		{
			$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', ''), 'error');

			return false;
		}

		/* If we are not coming from the upgrade scripts we update the table and let them know what was updated. */
		if (!$parent)
		{
			self::reloadtable($result, 'Style');
			$app->enqueueMessage(JText::_('JBS_STYLE_CSS_FIX_COMPLETE') . ': ' . $result->filename, 'notice');
		}

		return true;
	}

	/**
	 * Set table store()
	 *
	 * @param   object  $result  Object list that we will get the id from.
	 * @param   string  $table   Table to be reloaded.
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 7.0
	 */
	public static function reloadtable($result, $table = 'Style')
	{
		$db = JFactory::getDbo();

		// Store new Recorder so it can be seen.
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance($table, 'Table', array('dbo' => $db));

		try
		{
			$table->load($result->id);

			// This is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
			@$table->store();
		}
		catch (Exception $e)
		{
			throw new Exception('Caught exception: ' . $e->getMessage(), 500);
		}

		return true;
	}

	/**
	 * Reset Database back to defaults
	 *
	 * @param   bool  $install  If coming from the installer true|false not form installer
	 *
	 * @return boolean|int
	 *
	 * @since  7.0
	 * @throws \Exception
	 */
	public static function resetdb($install = false)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_biblestudy/install/sql';

		$files = str_replace('.sql', '', JFolder::files($path, '\.sql$'));
		$files = array_reverse($files, true);

		if ($install == true)
		{
			foreach ($files as $a => $file)
			{
				if (strpos($file, 'uninstall') !== false)
				{
					unset($files[$a]);
				}
			}
		}

		foreach ($files as $value)
		{
			// Get file contents
			$buffer = file_get_contents($path . '/' . $value . '.sql');

			// Graceful exit and rollback if read not successful
			if ($buffer === false)
			{
				$app->enqueueMessage(JText::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

				return false;
			}

			// Create an array of queries from the sql file
			$queries = $db->splitSql($buffer);

			if (count($queries) == 0)
			{
				// No queries to process
				return 0;
			}

			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);

					if (!$db->execute())
					{
						$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', ' in ' . $value), 'error');

						return false;
					}
				}
			}
		}

		// Remove old assets.
		$query = $db->getQuery(true);
		$query->delete('#__assets')
			->where('name LIKE ' . $db->q('com_biblestudy.%'));
		$db->setQuery($query);
		$db->execute();

		if (!$install)
		{
			$app->enqueueMessage(JText::_('JBS_INS_RESETDB'), 'message');
		}

		return true;
	}

	/**
	 * Clean up Study Topics Duplicates
	 *
	 * @since 8.0.0
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public static function CleanStudyTopics()
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__bsms_studies');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results AS $result)
		{
			$query = $db->getQuery(true);
			$query->select('id, topic_id')->from('#__bsms_studytopics')->where('study_id = ' . $result->id);
			$db->setQuery($query);
			$resulta = $db->loadObjectList();
			$c       = count($resulta);

			if ($resulta && $c > 1)
			{
				$t = 1;

				foreach ($resulta AS $study_topics)
				{
					$query = $db->getQuery(true);
					$query->select('id')
						->from('#__bsms_studytopics')
						->where('study_id = ' . $result->id)
						->where('topic_id = ' . $study_topics->topic_id)
						->order('id desc');
					$db->setQuery($query);
					$results = $db->loadObjectList();
					$records = count($results);

					if ($records > 1)
					{
						foreach ($results AS $id)
						{
							if ($t < $records)
							{
								$query = $db->getQuery(true);
								$query->delete('#__bsms_studytopics')
									->where('id = ' . $id->id);
								$db->setQuery($query);

								if (!$db->execute())
								{
									$app->enqueueMessage('Error with Deleting duplicat topics record ' . $id->id, 'error');
								}
								else
								{
									$app->enqueueMessage('Removed Duplicat topic Record ' . $id->id, 'notice');
								}

								$t++;
							}
						}
					}
				}
			}
		}
	}
}
