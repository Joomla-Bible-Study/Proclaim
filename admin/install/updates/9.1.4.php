<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

/**
 * Update for 9.1.4 class
 *
 * @package  Proclaim.Admin
 * @since    9.1.4
 */
class Migration914
{
	// List the functions to go through in order for this migration.
	public $steps = array('up');

	// Count of processes.
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

		return;
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

		$this->updateFA();

		$message                     = new stdClass;
		$message->title_key          = 'JBS_POSTINSTALL_TITLE_FONTAWESOME';
		$message->description_key    = 'JBS_POSTINSTALL_BODY_FONTAWESOME';
		$message->action_key         = '';
		$message->language_extension = 'com_biblestudy';
		$message->language_client_id = 1;
		$message->type               = 'message';
		$message->action_file        = '';
		$message->action             = '';
		$message->condition_file     = '';
		$message->condition_method   = '';
		$message->version_introduced = '9.1.3';
		$message->enabled            = 1;

		$script = new BibleStudyModelInstall;
		$script->postinstall_messages($message);

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
			BIBLESTUDY_PATH_ADMIN . '/models/UploadController.php',
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

	/**
	 * Convert v4 to v5 of Font Awesome
	 *
	 * @since 9.1.3
	 *
	 * @return void
	 */
	protected function updateFA()
	{
		// Convert Font Awesome deceleration in the DB to new version.

		return;
	}
}
