<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
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
	 * Alter tables for Blob
	 *
	 * @return boolean
	 *
	 * @since 7.0.0
	 */
	protected static function TablestoBlob()
	{
		$backuptables = self::getObjects();

		$db = JFactory::getDbo();

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
	 * Get Objects for tables
	 *
	 * @return array
	 *
	 * @since 7.0.0
	 */
	protected static function getObjects()
	{
		$db        = JFactory::getDbo();
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
	 * Modify tables to Text
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	protected static function TablestoText()
	{
		$backuptables = self::getObjects();

		$db = JFactory::getDbo();

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

	/**
	 * Import DB
	 *
	 * @param   boolean  $parent  Switch to see if it is coming from migration or restore.
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function importdb($parent)
	{
		jimport('joomla.filesystem.file');

		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}

		$input         = new JInput;
		$installtype   = $input->getPath('install_directory');
		$backuprestore = $input->getWord('backuprestore', '');

		// Restore form prior backup files located on the server.
		if (substr_count($backuprestore, '.sql'))
		{
			$restored = self::restoreDB($backuprestore);

			if ($restored)
			{
				$result = true;

				return $result;
			}
		}

		// Start finding how to restore files.
		if (!empty($installtype) && $installtype !== '/' && $installtype != JFactory::getConfig()->get('tmp_path') . '/')
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
			switch ($result['type'])
			{
				case 'dir':
					$src = JFolder::files($result['dir'], '.', true, true);
					$tmp_src = $src[0];
					break;
				case 'file':
					$tmp_src = $result['dir'];
					break;
				default:
					throw new InvalidArgumentException('Unknown Archive Type');
			}

			$result     = self::installdb($tmp_src, $parent);

			// Cleanup the install files.
			if (!is_file($uploadresults['packagefile']))
			{
				$config = JFactory::getConfig();
				$package['packagefile'] = $config->get('tmp_path') . '/' . $uploadresults['packagefile'];
			}

			JInstallerHelper::cleanupInstall($uploadresults['packagefile'], $uploadresults['extractdir']);

			if (($parent !== true) && $result)
			{
				$controlser = JControllerLegacy::getInstance('Biblestudy');
				$controlser->setRedirect('index.php?option=com_biblestudy&task=admin.fixasset');
				$controlser->redirect();
			}
		}

		return $result;
	}

	/**
	 * Restore DB for exerting Joomla Bible Study
	 *
	 * @param   string  $backuprestore  file name to restore
	 *
	 * @return boolean See if the restore worked.
	 *
	 * @since 9.0.0
	 */
	public static function restoreDB($backuprestore)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}

		$query = file_get_contents(JPATH_SITE . '/media/com_biblestudy/backup/' . $backuprestore);

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
			$app->enqueueMessage(basename($backuprestore), 'warning');
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_CURENT_DB'), 'warning');

			return false;
		}
		else
		{
			$queries = JDatabaseDriver::splitSql($query);

			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		return true;
	}

	/**
	 * Get Package from Folder
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	private static function _getPackageFromFolder()
	{
		$input = JFactory::getApplication()->input;

		// Get the path to the package to install.
		$p_dir = $input->getString('install_directory');
		$p_dir = JPath::clean($p_dir);

		// Did you give us a valid directory?
		if (!is_dir($p_dir))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));

			return false;
		}

		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = 'dir';

		return $package;
	}

	/**
	 * Get Package form Upload
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function _getPackageFromUpload()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		// Get the uploaded file information
		$userfile = $input->files->get('importdb', null, 'raw');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$app->enqueueMessage(JText::_('JBS_IBM_ERROR_PHP_UPLOAD_NOT_ENABLED'), 'warning');

			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked.
		if (!extension_loaded('zlib'))
		{
			JError::raiseWarning('', JText::_('JBS_IBM_ERROR_UPLOAD_FAILED_ZLIB'));

			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			$app->enqueueMessage(JText::_('JBS_CMN_NO_FILE_SELECTED'), 'warning');

			return false;
		}

		// Is the PHP tmp directory missing?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR))
		{
			JError::raiseWarning(
				'',
				JText::_('JBS_IBM_ERROR_UPLOAD_FAILED') . '<br />' . JText::_('JBS_IBM_ERROR_UPLOAD_FAILED_PHPUPLOADNOTSET')
			);

			return false;
		}

		// Is the max upload size too small in php.ini?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE))
		{
			JError::raiseWarning(
				'',
				JText::_('JBS_IBM_ERROR_UPLOAD_FAILED') . '<br />' . JText::_('JBS_IBM_ERROR_UPLOAD_FAILED_SMALLUPLOADSIZE')
			);

			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			$app->enqueueMessage(JText::_('JBS_IBM_ERROR_UPLOAD_FAILED'), 'warning');

			return false;
		}

		// Build the appropriate paths
		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src = $userfile['tmp_name'];

		// Move uploaded file.
		jimport('joomla.filesystem.file');
		JFile::upload($tmp_src, $tmp_dest, false, true);

		if (!JBSMBibleStudyHelper::endsWith($tmp_dest, 'sql'))
		{
			// Unpack the downloaded package file.
			$package = JInstallerHelper::unpack($tmp_dest, true);
			$package['type'] = 'dir';
		}
		else
		{
			$package['packagefile'] = null;
			$package['extractdir'] = null;
			$package['dir'] = $tmp_dest;
			$package['type'] = 'file';
		}

		return $package;
	}

	/**
	 * Install DB
	 *
	 * @param   string   $tmp_src  Temp info
	 * @param   boolean  $parent   To tell if coming from migration
	 *
	 * @return boolean if db installed correctly.
	 *
	 * @since 9.0.0
	 */
	protected static function installdb($tmp_src, $parent = true)
	{
		jimport('joomla.filesystem.file');
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}

		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

		$query  = file_get_contents($tmp_src);

		// Graceful exit and rollback if read not successful
		if ($query === false)
		{
			$app->enqueueMessage(JText::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

			return false;
		}
		// Check if sql file is for Joomla! Bible Studies
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
			$app->enqueueMessage('Extracted file: ' . basename($tmp_src), 'warning');
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_DB'), 'warning');

			return false;
		}
		elseif (($iscernt === 0) && ($parent !== true))
		{
			// Way to check to see if file came from restore and is current.
			$app->enqueueMessage(JText::_('JBS_IBM_NOT_CURENT_DB'), 'warning');

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
			$queries = JDatabaseDriver::splitSql($query);

			if (count($queries) == 0)
			{
				// No queries to process
				return false;
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
}
