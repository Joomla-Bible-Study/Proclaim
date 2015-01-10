<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

/**
 * Update for 9.0.0 class
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class Migration900
{
	/**
	 * Call Script for Updates of 9.0.0
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return bool
	 */
	public function up ($db)
	{
		/**
		 * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
		 */
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3000);
		}
		$registry = new Registry;
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_biblestudy/tables');

		// Migrate servers
		$query = $db->getQuery(true)->select('*')->from('#__bsms_servers');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $server)
		{
			$newServer = JTable::getInstance('Server', 'Table', array('dbo' => $db));
			$newServer->load($server->id);
			$params = array();

			// Migrate FTP server type
			if (!empty($server->ftphost))
			{
				$newServer->type       = "ftp";
				$params['ftphost']     = $server->ftp_username;
				$params['ftpuser']     = $server->ftp_password;
				$params['ftppassword'] = $server->ftp_password;
				$params['ftpport']     = $server->ftp_password;

				// Migrate AWS server type
			}
			elseif (!empty($server->aws_key))
			{
				$newServer->type      = "aws";
				$params['aws_key']    = $server->aws_key;
				$params['aws_secret'] = $server->aws_secret;

				// Migrate to a default lecacy server type
			}
			else
			{
				$newServer->type = "legacy";
				$params['path']  = $server->server_path;
			}

			$newServer->params = json_encode($params);
			$newServer->id     = null;
			$newServer->store();

			// Delete old server
			JTable::getInstance('Server', 'Table', array('dbo' => $db))->delete($server->id);

			// Todo: Migrate media defaults

			// Migrate media files
			$query = $db->getQuery(true)->select('*')
					->from('#__bsms_mediafiles')
					->where('server = ' . $server->id);
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $mediaFile)
			{

				/** @var TableMediafile $newMediaFile */
				$newMediaFile = JTable::getInstance('Mediafile', 'Table', array('dbo' => $db));
				$newMediaFile->load($mediaFile->id);
				$metadata = array();

				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_media')->where('id = ' . $mediaFile->media_image);
				$db->setQuery($query);

				$mediaImage = $db->loadObject();

				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_mimetype')->where('id = ' . $mediaFile->mime_type);
				$db->setQuery($query);

				$mimtype = $db->loadObject();

				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_folders')->where('id = ' . $mediaFile->path);
				$db->setQuery($query);

				$path   = $db->loadObject();
				$mimage = null;

				// Some people do not have logos set to there media so we have this.
				if (!$mediaImage)
				{
					$mediaImage                = new stdClass;
					$mediaImage->media_alttext = '';
				}
				else
				{
					if ($mediaImage->media_image_path)
					{
						$mimage = $mediaImage->media_image_path;
					}
					else
					{
						$mimage = 'media/com_biblestudy/images/' . $mediaImage->path2;
					}
				}
				$registry->loadString($mediaFile->params);
				$params = $registry->toObject();

				$params->media_image   	= $mimage;
				$params->media_text    	= $mediaImage->media_alttext;
				if (!empty($mimtype))
				{
					$params->mime_type = $mimtype->mimetype;
				}
				$params->special      	= $mediaFile->special;
				if (!empty($mediaFile->filename))
				{
					if (@empty($path->folderpath))
					{
						$folderpath = null;
					}
					else
					{
						$folderpath = $path->folderpath;
					}
					$params->filename = $folderpath . $mediaFile->filename;
				}
				else
				{
					$params->filename = '';
				}
				$params->player         = $mediaFile->player;
				$params->size          	= $mediaFile->size;
				$params->mediacode     	= $mediaFile->mediacode;
				$params->link_type     	= $mediaFile->link_type;
				$params->docMan_id     	= $mediaFile->docMan_id;
				$params->article_id    	= $mediaFile->article_id;
				$params->virtueMart_id 	= $mediaFile->virtueMart_id;
				$params->popup	        = $mediaFile->popup;

				$registry->loadObject($params);

				// @todo I don't think we want to add both hits and plays to gather. Will need to verify this with tom
				$metadata['hits']      = $mediaFile->hits + $mediaFile->plays;
				$metadata['downloads'] = $mediaFile->downloads;

				$newMediaFile->server_id = $newServer->id;
				$newMediaFile->params    = $registry->toString();

				// Properly encode the metadata.
				// @todo Not sure if we are still needing to do this Eugen
				$registry = new Registry;
				$registry->loadArray($metadata);
				$newMediaFile->metadata  = $registry->toString();
				$newMediaFile->id        = null;
				$newMediaFile->store();

				// Delete old mediafile
				JTable::getInstance('Mediafile', 'Table', array('dbo' => $db))->delete($mediaFile->id);
			}
		}

		/** @var TableServer $newServer2 */
		$newServer = JTable::getInstance('Server', 'Table', array('dbo' => $db));
		$newServer->server_name = 'Defualt';
		$newServer->type        = "legacy";
		$newServer->id          = null;
		$newServer->store();

		// Migrate media files
		$query = $db->getQuery(true)->select('*')
			->from('#__bsms_mediafiles')
			->where('server <= ' . 0, 'OR')
			->where('server IS NULL');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $mediaFile)
		{

			/** @var TableMediafile $newMediaFile */
			$newMediaFile = JTable::getInstance('Mediafile', 'Table', array('dbo' => $db));
			$newMediaFile->load($mediaFile->id);
			$metadata = array();

			$query = $db->getQuery(true);
			$query->select('*')->from('#__bsms_media')->where('id = ' . $mediaFile->media_image);
			$db->setQuery($query);

			$mediaImage = $db->loadObject();

			$query = $db->getQuery(true);
			$query->select('*')->from('#__bsms_mimetype')->where('id = ' . $mediaFile->mime_type);
			$db->setQuery($query);

			$mimtype = $db->loadObject();

			$mimage = null;

			// Some people do not have logos set to there media so we have this.
			if (!$mediaImage)
			{
				$mediaImage                = new stdClass;
				$mediaImage->media_alttext = '';
			}
			else
			{
				if ($mediaImage->media_image_path)
				{
					$mimage = $mediaImage->media_image_path;
				}
				else
				{
					$mimage = 'media/com_biblestudy/images/' . $mediaImage->path2;
				}
			}
			$registry->loadString($mediaFile->params);
			$params = $registry->toObject();

			$params->media_image   	= $mimage;
			$params->media_text    	= $mediaImage->media_alttext;
			if (!empty($mimtype))
			{
				$params->mime_type = $mimtype->mimetype;
			}
			$params->special      	= $mediaFile->special;
			if (!empty($mediaFile->filename))
			{
				$params->filename = $mediaFile->filename;
			}
			else
			{
				$params->filename = '';
			}
			$params->player         = $mediaFile->player;
			$params->size          	= $mediaFile->size;
			$params->mediacode     	= $mediaFile->mediacode;
			$params->link_type     	= $mediaFile->link_type;
			$params->docMan_id     	= $mediaFile->docMan_id;
			$params->article_id    	= $mediaFile->article_id;
			$params->virtueMart_id 	= $mediaFile->virtueMart_id;
			$params->popup	        = $mediaFile->popup;

			$registry->loadObject($params);

			// @todo I don't think we want to add both hits and plays to gather. Will need to verify this with tom
			$metadata['hits']      = $mediaFile->hits + $mediaFile->plays;
			$metadata['downloads'] = $mediaFile->downloads;

			$newMediaFile->server_id = $newServer->id;
			$newMediaFile->params    = $registry->toString();
			$newMediaFile->metadata  = json_encode($metadata);
			$newMediaFile->id        = null;
			$newMediaFile->store();

			// Delete old mediafile
			JTable::getInstance('Mediafile', 'Table', array('dbo' => $db))->delete($mediaFile->id);
		}

		// Delete unused columns
		$columns = array('media_image', 'special', 'filename', 'size', 'mime_type', 'mediacode', 'link_type',
				'docMan_id', 'article_id', 'virtueMart_id', 'player', 'popup', 'server', 'internal_viewer', 'path');
		$this->deleteColumns('#__bsms_mediafiles', $columns, $db);

		// Delete unused columns
		$columns = array('ftphost', 'ftpuser', 'ftppassword', 'ftpport', 'aws_key', 'aws_secret');
		$this->deleteColumns('#__bsms_servers', $columns, $db);

		// Modify admin table to add thumbnail default parameters
		$admin = JTable::getInstance('Admin', 'Table', array('dbo' => $db));
		$admin->load(1);

		$this->deleteTable('#__bsms_folders', $db);
		$this->deleteTable('#__bsms_media', $db);
		$this->deleteTable('#__bsms_mimetype', $db);

		$message = new stdClass;
		$message->title_key          = 'JBS_POSTINSTALL_TITLE_TEMPLATE';
		$message->description_key    = 'JBS_POSTINSTALL_BODY_TEMPLATE';
		$message->action_key         = 'JBS_POSTINSTALL_ACTION_TEMPLATE';
		$message->language_extension = 'com_biblestudy';
		$message->language_client_id = 1;
		$message->type               = 'action';
		$message->action_file        = 'admin://components/com_biblestudy/postinstall/template.php';
		$message->action             = 'admin_postinstall_template_action';
		$message->condition_file     = "admin://components/com_biblestudy/postinstall/template.php";
		$message->condition_method   = 'admin_postinstall_template_condition';
		$message->version_introduced = '9.0.0';
		$message->enabled = 1;

		$script = new BibleStudyModelMigration;
		$script->postinstall_messages($message, $db);

		return true;
	}

	/**
	 * Set del columns
	 *
	 * @param   string           $table    Table
	 * @param   array            $columns  Column to drop
	 * @param   JDatabaseDriver  $db       Data bass driver
	 *
	 * @return void
	 */
	private function deleteColumns ($table, $columns, $db)
	{
		foreach ($columns as $column)
		{
			$db->setQuery('ALTER TABLE ' . $table . ' DROP ' . $column);
			$db->execute();
		}
	}

	/**
	 * Delete Table
	 *
	 * @param   string           $table  Table
	 * @param   JDatabaseDriver  $db     Data bass driver
	 *
	 * @return void
	 */
	private function deleteTable ($table, $db)
	{
		$db->setQuery('DROP TABLE ' . $table);
		$db->execute();
	}

	/**
	 * Update Templates to work with 9.0.0 that cannot be don doing normal sql file.
	 *
	 * @param   JDatabaseDriver  $db  Data bass driver
	 *
	 * @return void
	 */
	public function updatetemplates ($db)
	{
		$query = $db->getQuery(true);
		$query->select('id, title, params')
				->from('#__bsms_templates');
		$db->setQuery($query);
		$data = $db->loadObjectList();
		foreach ($data as $d)
		{
			/** @var TableTemplate $table */
			// Load Table Data.
			JTable::addIncludePath(JPATH_COMPONENT . '/tables');
			$table = JTable::getInstance('Template', 'Table', array('dbo' => $db));
			$table->load($d->id);

			$table->store();

		}
		return;
	}

	/**
	 * Update CSS for 9.0.0
	 *
	 * @return boolean
	 *
	 * @todo may not be needed.
	 */
	public function css900 ()
	{
		$csscheck = 'display:table-header';

		$dest      = JPATH_SITE . DIRECTORY_SEPARATOR . 'media/com_biblestudy/css/biblestudy.css';
		$cssexists = JFile::exists($dest);

		if ($cssexists)
		{
			$cssread = file_get_contents($dest);

			$csstest = substr_count($cssread, $csscheck);

			if (!$csstest)
			{
				$cssread = str_replace('display:table-header', 'display:table-header-group', $cssread);
			}

			if (!JFile::write($dest, $cssread))
			{
				return false;
			}
		}

		return true;
	}

}
