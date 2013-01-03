<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Update on All upgrades
 *
 * @package  BibleStudy.Admin
 * @since    7.0.3
 */
class UpdatejbsALL
{

	/**
	 * Function to do updates
	 *
	 * @return array
	 *
	 * @since 7.0.4
	 */
	public function doALLupdate()
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path = JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/sql/updates/mysql';

		$files = str_replace('.sql', '', JFolder::files($path, '\.sql$'));
		usort($files, 'version_compare');

		/* Finde Extension ID of component */
		$query = $db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('`name` = "com_biblestudy"');
		$db->setQuery($query);
		$eid = $db->loadResult();

		foreach ($files as $i => $value)
		{

			/* Find Last updated Version in Update table */
			$query = $db->getQuery(true);
			$query
				->select('version')
				->from('#__bsms_update');
			$db->setQuery($query);
			$updates = $db->loadResult();
			$update  = end($updates);

			if ($update)
			{
				/* Set new Schema Version */
				$this->setSchemaVersion($update, $eid);
			}
			else
			{
				$value = '7.0.0';
			}

			if (version_compare($value, $update) <= 0)
			{
				unset($files[$i]);
			}
			elseif ($files)
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
							$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

							return false;
						}
					}
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('JBS_INS_NO_UPDATE_SQL_FILES'), 'warning');

				return false;
			}
			/* Find Last updated Version in Update table */
			$query = $db->getQuery(true);
			$query
				->select('version')
				->from('#__bsms_update');
			$db->setQuery($query);
			$updates = $db->loadResult();
			$update  = end($updates);

			if ($update)
			{
				/* Set new Schema Version */
				$this->setSchemaVersion($update, $eid);
			}
			else
			{
				$app->enqueueMessage('no update table', 'error');
			}
		}

		return true;
	}

	/**
	 * Set the schema version for an extension by looking at its latest update
	 *
	 * @param   string   $version  Version number
	 * @param   integer  $eid      Extension ID
	 *
	 * @return  boolean|string
	 *
	 * @since   7.1.0
	 */
	public function setSchemaVersion($version, $eid)
	{
		if ($version && $eid)
		{
			$db = JFactory::getDBO();

			// Update the database
			$query = $db->getQuery(true);
			$query
				->delete()
				->from('#__schemas')
				->where('extension_id = ' . $eid);
			$db->setQuery($query);

			if ($db->execute())
			{
				$query->clear();
				$query->insert($db->quoteName('#__schemas'));
				$query->columns(array($db->quoteName('extension_id'), $db->quoteName('version_id')));
				$query->values($eid . ', ' . $db->quote($version));
				$db->setQuery($query);
				$db->execute();

				return true;
			}
			else
			{
				return 'Could not locate extension id in schemas table';
			}
		}

		return 'No Version and eid';
	}

}
