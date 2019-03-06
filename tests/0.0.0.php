<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

/**
 * Update for 0.0.0 class
 *
 * @package  Proclaim.Admin
 * @since    0.0.0
 */
class Migration000
{
	/**
	 * Call Script for Updates of 0.0.0
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return bool
	 *
	 * @since 0.0.0
	 */
	public function up ($db)
	{
		$this->deleteUnexistingFiles();

		return true;
	}

	/**
	 * Remove Old Files and Folders
	 *
	 * @since      0.0.0
	 *
	 * @return   void
	 */
	protected function deleteUnexistingFiles()
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$path = array(
			BIBLESTUDY_PATH_ADMIN . '/models/style.php'
		);

			foreach ($path as $file)
			{
				if (JFile::exists($file))
				{
					JFile::delete($file);
				}
			}

		$folders = array(
			BIBLESTUDY_PATH_ADMIN . '/views/styles');

		foreach ($folders as $folder)
		{
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
	}
}
