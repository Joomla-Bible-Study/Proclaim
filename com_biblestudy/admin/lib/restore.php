<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Restore class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.4
 */
class JBSMRestore
{

	/**
	 * Import DB
	 *
	 * @param   boolean  $parent  Switch to see if it is coming from migration or restore.
	 *
	 * @return boolean
	 */
	public function importdb($parent)
	{
		jimport('joomla.filesystem.file');
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(300);
		}
		$input         = new JInput;
		$installtype   = $input->get('install_directory');
		$backuprestore = $input->getWord('backuprestore', '');

		if (substr_count($backuprestore, '.sql'))
		{
			$restored = self::restoreDB($backuprestore);

			if ($restored)
			{
				$result = true;

				return $result;
			}
		}
		if (substr_count($installtype, 'sql'))
		{
			$uploadresults = self::_getPackageFromFolder();
			$result        = $uploadresults;
		}
		else
		{
			$uploadresults = self::_getPackageFromUpload();
			$result        = $uploadresults;
		}

		if ($result)
		{
			$result     = self::installdb($uploadresults, $parent);
			$inputfiles = new JInputFiles;
			$userfile   = $inputfiles->get('importdb');

			if (JFile::exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name']))
			{
				unlink(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name']);
			}
			if (($parent !== true) && $result)
			{
				$fix = new JBSMAssets;
				$fix->fixassets();
			}
		}

		// Todo: delete uploaded files or have a option to do this??
		return $result;
	}

	/**
	 * Get Package form Upload
	 *
	 * @return boolean
	 */
	public function _getPackageFromUpload()
	{
		$app = JFactory::getApplication();

		// Get the uploaded file information
		$input    = new JInputFiles;
		$userfile = $input->get('importdb');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$app->enqueueMessage(JText::_('WARNINSTALLFILE'), 'warning');

			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			$app->enqueueMessage(JText::_('JBS_CMN_NO_FILE_SELECTED'), 'warning');

			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			$app->enqueueMessage(JText::_('WARNINSTALLUPLOADERROR'), 'warning');

			return false;
		}
		// Build the appropriate paths
		$tmp_dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name'];

		$tmp_src = $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = 0;

		if (JFile::exists($tmp_src))
		{
			if (!JFile::exists($tmp_dest))
			{
				$uploaded = move_uploaded_file($tmp_src, $tmp_dest);
			}
			else
			{
				JFile::delete($tmp_dest);
				$uploaded = move_uploaded_file($tmp_src, $tmp_dest);
			}
		}

		if ($uploaded)
		{
			return $tmp_dest;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Install DB
	 *
	 * @param   string   $tmp_src  Temp info
	 * @param   boolean  $parent   To tell if coming from migration
	 *
	 * @return boolean if db installed correctly.
	 */
	protected static function installdb($tmp_src, $parent = true)
	{
		jimport('joomla.filesystem.file');
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(300);
		}
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();

		$query  = file_get_contents($tmp_src);
		$exists = JFile::exists($tmp_src);

		// Graceful exit and rollback if read not successful
		if ($query === false)
		{
			$app->enqueueMessage(JText::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

			return false;
		}
		// Check if sql file is for Joomla! Bible Studys
		$isold   = substr_count($query, '#__bsms_admin_genesis');
		$isnot   = substr_count($query, '#__bsms_studies');
		$iscernt = substr_count($query, BIBLESTUDY_VERSION_UPDATEFILE);

		if ($isold !== 0 && $isnot === 0)
		{
			$app->enqueueMessage(JText::_('JBS_IBM_OLD_DB'), 'warning');

			return false;
		}
		elseif ($isnot === 0)
		{
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_DB'), 'warning');

			return false;
		}
		elseif (($iscernt !== 0) && ($parent === true))
		{
			$app->enqueueMessage(JText::_('JBS_IBM_MIGRATE_NOT_OLD_DB'), 'warning');

			return false;
		}
		elseif (($iscernt === 0) && ($parent !== true))
		{ // Way to check to see if file came from restore and is current.
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_CURENT_DB'), 'waring');

			return false;
		}
		else
		{
			// First we need to drop the existing JBS tables
			$objects = self::getObjects();

			foreach ($objects as $object)
			{
				$dropquery = 'DROP TABLE IF EXISTS ' . $object['name'] . ';';
				$db->setQuery($dropquery);
				$db->execute();
			}

			// Create an array of queries from the sql file
			$queries = $db->splitSql($query);

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
						$app->enqueueMessage(JText::sprintf('JBS_IBM_INSTALLDB_ERRORS', $db->stderr(true)), 'error');

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Restore DB for exerting Joomla Bible Study
	 *
	 * @param   string  $backuprestore  ?
	 *
	 * @return boolean See if the restore worked.
	 */
	public static function restoreDB($backuprestore)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(300);
		}
		$query = file_get_contents(JPATH_SITE . '/media/com_biblestudy/database/' . $backuprestore);

		// Check to see if this is a backup from an old db and not a migration
		$isold   = substr_count($query, '#__bsms_admin_genesis');
		$isnot   = substr_count($query, '#__bsms_studies');
		$iscernt = substr_count($query, BIBLESTUDY_VERSION_UPDATEFILE);

		if ($isold !== 0 && $isnot === 0)
		{
			$app->enqueueMessage(JText::_('JBS_IBM_OLD_DB'), 'warning');

			return false;
		}
		elseif ($isnot === 0)
		{
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_DB'), 'warning');

			return false;
		}
		elseif (!$iscernt)
		{
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_CURENT_DB'), 'warning');

			return false;
		}
		else
		{
			$queries = $db->splitSql($query);

			foreach ($queries as $querie)
			{
				$db->setQuery($querie);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Get Package from Folder
	 *
	 * @return boolean
	 */
	private static function _getPackageFromFolder()
	{
		$input = new JInput;
		$p_dir = $input->getString('install_directory');

		return $p_dir;
	}

	/**
	 * Get Objects for tables
	 *
	 * @return array
	 */
	protected static function getObjects()
	{
		$db        = JFactory::getDBO();
		$tables    = $db->getTableList();
		$prefix    = $db->getPrefix();
		$prelength = strlen($prefix);
		$bsms      = 'bsms_';
		$objects   = array();

		foreach ($tables as $table)
		{
			if (substr_count($table, $bsms))
			{
				$table     = substr_replace($table, '#__', 0, $prelength);
				$objects[] = array('name' => $table);
			}
		}

		return $objects;
	}

	/**
	 * Alter tables for Blob
	 *
	 * @return boolean
	 */
	protected static function TablestoBlob()
	{
		$backuptables = self::getObjects();

		$db = JFactory::getDBO();

		foreach ($backuptables AS $backuptable)
		{

			if (substr_count($backuptable['name'], 'studies'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext BLOB';
				$db->setQuery($query);
				$db->execute();

				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext2 BLOB';
				$db->setQuery($query);
				$db->execute();
			}
			if (substr_count($backuptable['name'], 'podcast'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description BLOB';
				$db->setQuery($query);
				$db->execute();
			}
			if (substr_count($backuptable['name'], 'series'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description BLOB';
				$db->setQuery($query);
				$db->execute();
			}
			if (substr_count($backuptable['name'], 'teachers'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY information BLOB';
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Modify tables to Text
	 *
	 * @return boolean
	 */
	protected static function TablestoText()
	{
		$backuptables = self::getObjects();

		$db = JFactory::getDBO();

		foreach ($backuptables AS $backuptable)
		{

			if (substr_count($backuptable['name'], 'studies'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext TEXT';
				$db->setQuery($query);
				$db->execute();

				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY studytext2 TEXT';
				$db->setQuery($query);
				$db->execute();
			}
			if (substr_count($backuptable['name'], 'podcast'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description TEXT';
				$db->setQuery($query);
				$db->execute();
			}
			if (substr_count($backuptable['name'], 'series'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY description TEXT';
				$db->setQuery($query);
				$db->execute();
			}
			if (substr_count($backuptable['name'], 'teachers'))
			{
				$query = 'ALTER TABLE ' . $backuptable['name'] . ' MODIFY information TEXT';
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

}
