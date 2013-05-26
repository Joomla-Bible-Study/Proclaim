<?php
/**
 * Live Update Package
 *
 * @package   LiveUpdate
 * @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license   GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 */

defined('_JEXEC') or die();

/**
 * Live Update File Storage Class
 * Allows to store the update data to files on disk. Its configuration options are:
 * path            string    The absolute path to the directory where the update data will be stored as INI files
 *
 */
class LiveUpdateStorageFile extends LiveUpdateStorage
{
	/**
	 * File Name
	 *
	 * @var null
	 */
	private static $filename = null;

	/**
	 * Load
	 *
	 * @param $config
	 *
	 * @return void
	 */
	public function load($config)
	{
		$path     = $config['path'];
		$extname  = $config['extensionName'];
		$filename = "$path/$extname.updates.ini";

		self::$filename = $filename;

		jimport('joomla.registry.registry');
		self::$registry = new JRegistry('update');

		jimport('joomla.filesystem.file');

		if (JFile::exists(self::$filename))
		{
			self::$registry->loadFile(self::$filename, 'INI');
		}
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save()
	{
		jimport('joomla.filesystem.file');
		$data = self::$registry->toString('INI');
		JFile::write(self::$filename, $data);
	}
}
