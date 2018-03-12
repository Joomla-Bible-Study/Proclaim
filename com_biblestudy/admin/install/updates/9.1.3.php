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
 * Update for 9.0.1 class
 *
 * @package  Proclaim.Admin
 * @since    9.0.1
 */
class Migration901
{
	// List the functions to go through in order for this migration.
	public $steps = array('servers', 'media' , 'migrateTemplateLists',
		'updateTemplates', 'removeExpert', 'deleteUnexactingFiles', 'up');

	// This is the query holder to pass back a forth between the install and the migration.
	public $query = array();

	// Internal holder
	private $media = array();

	// Internal holder
	private $servers = array();

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
		// Get servers to migrate
		$query = $db->getQuery(true)->select('*')->from('#__bsms_servers');
		$db->setQuery($query);
		$servers = $db->loadObjectList();
		$this->servers = array_merge($this->servers, $servers);

		foreach ($servers as $server)
		{
			// Find media files for server.
			$query = $db->getQuery(true)->select('*')
				->from('#__bsms_mediafiles')
				->where('server = ' . $server->id);
			$db->setQuery($query);
			$mediaFiles   = $db->loadObjectList();
			$this->media = array_merge($this->media, $mediaFiles);
		}

		// No Server related media files to migrate.
		$query = $db->getQuery(true)->select('*')
			->from('#__bsms_mediafiles')
			->where('server <= ' . 0, 'OR')
			->where('server IS NULL');
		$db->setQuery($query);
		$mediaFiles2  = $db->loadObjectList();

		$this->media = array_merge($this->media, $mediaFiles2);
		$this->count  = count($this->media);
		$this->count += count($this->servers);

		$this->query = array_merge(array('servers' => $this->servers), array('media' => $this->media));
	}

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
	 *
	 * @todo Need to do this for Admin and Media;
	 */
	protected function updateFA()
	{
		// Convert Font Awesome files

		return;
	}
}
