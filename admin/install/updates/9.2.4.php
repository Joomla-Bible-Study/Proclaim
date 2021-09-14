<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

/**
 * Update for 9.2.3 class
 *
 * @package  Proclaim.Admin
 * @since    9.2.3
 */
class Migration924
{
	/**
	 * List the functions to go through in order for this migration.
	 * @var array
	 * @since 9.2.3
	 */
	public $steps = array('up');

	/**
	 * Count of processes.
	 * @var integer
	 * @since 9.2.3
	 */
	public $count = 0;

	/**
	 * Build steps and query
	 *
	 * @param   JDatabaseDriver  $db  Joomla DateBase Driver
	 *
	 * @return  void
	 *
	 * @since 9.0.0
	 */
	public function build($db)
	{
		// Work on this. may need this?
	}

	/**
	 * Call Script for Updates of 9.0.1
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return boolean
	 *
	 * @since 9.0.1
	 * @throws Exception
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
			'/administrator/components/com_biblestudy/addons/servers/legacy/fields/filesize.php',
			'/administrator/components/com_biblestudy/addons/servers/local/fields/filesize.php',
			'/administrator/components/com_biblestudy/models/UploadController.php',
			'/administrator/components/com_biblestudy/convert1.htm',
			'/components/com_biblestudy/convert1.htm',
			'/media/com_biblestudy/js/filesize.js',
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
