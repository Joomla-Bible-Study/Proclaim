<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

/**
 * Update for 9.0.1 class
 *
 * @package  Proclaim.Admin
 * @since    9.0.1
 */
class Migration901
{
	/**
	 * Call Script for Updates of 9.0.1
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return bool
	 *
	 * @since 9.0.1
	 */
	public function up($db)
	{
		$this->deleteUnexistingFiles();

		return true;
	}

	/**
	 * Remove Old Files and Folders
	 *
	 * @since      9.0.1
	 *
	 * @return   void
	 */
	protected function deleteUnexistingFiles()
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');

		$path = array(
			BIBLESTUDY_PATH_ADMIN . '/addons/servers/legacy/fields/filesize.php',
			BIBLESTUDY_PATH_ADMIN . '/addons/servers/local/fields/filesize.php',
			BIBLESTUDY_PATH_ADMIN . '/convert1.htm',
			BIBLESTUDY_PATH . '/convert1.htm',
		);

		foreach ($path as $file)
		{
			if (JFile::exists($file))
			{
				JFile::delete($file);
			}
		}
	}
}
