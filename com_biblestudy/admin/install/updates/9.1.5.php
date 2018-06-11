<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

/**
 * Update for 9.1.5 class
 *
 * @package  Proclaim.Admin
 * @since    9.1.5
 */
class Migration915
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
	 * Call Script for Updates of 9.1.5
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return bool
	 *
	 * @since 9.0.1
	 * @throws Exception
	 */
	public function up($db)
	{
		$this->deleteUnexistingFiles();


		$message                     = new stdClass;
		$message->title_key          = 'SIMPLEMODEMESSAGE_TITLE';
		$message->description_key    = 'SIMPLEMODEMESSAGE_BODY';
		$message->action_key         = '';
		$message->language_extension = 'com_biblestudy';
		$message->language_client_id = 1;
		$message->type               = 'message';
		$message->action_file        = '';
		$message->action             = '';
		$message->condition_file     = '';
		$message->condition_method   = '';
		$message->version_introduced = '9.1.5';
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
