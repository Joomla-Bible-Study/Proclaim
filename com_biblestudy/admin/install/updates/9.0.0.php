<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
defined('_JEXEC') or die;

JLoader::register('BibleStudyModelMigration', BIBLESTUDY_PATH_ADMIN_MODELS . '/migration.php');
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
			/** @var TableServer $newServer */
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
						if (!substr_count($mediaImage->path2, '/'))
						{
							$mimage = 'images/biblestudy/' . $mediaImage->path2;
						}
						else
						{
							$mimage = $mediaImage->path2;
						}

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
				if ($mediaFile->player == '100')
				{
					$mediaFile->player = '';
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

		/** @var TableServer $newServer */
		$newServer = JTable::getInstance('Server', 'Table', array('dbo' => $db));
		$newServer->server_name = 'Default';
		$newServer->type        = 'legacy';
		$newServer->params      = '{"path":""}';
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
					$mimage = 'images/biblestudy/' . $mediaImage->path2;
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
			if ($mediaFile->player == '100')
			{
				$mediaFile->player = '';
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
		$columns = array('ftphost', 'ftpuser', 'ftppassword', 'ftpport', 'server_path', 'aws_key', 'aws_secret',
			'server_type', 'ftp_username', 'ftp_password');
		$this->deleteColumns('#__bsms_servers', $columns, $db);

		// Modify admin table to add thumbnail default parameters
		/** @type TableAdmin $admin */
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_admin')
			->where('id = 1');
		$db->setQuery($query);
		$admin = $db->loadObject();
		$registry = new Registry;
		$registry->loadString($admin->params);
		$registry->set('server', $newServer->id);
		$admin->params = $registry->toString();
		$query = $db->getQuery(true);
		$query->update('#__bsms_admin')->set('params = ' . $db->q($admin->params))->where('id = 1');
		$db->setQuery($query);
		$db->execute();

		$this->deleteTable('#__bsms_folders', $db);
		$this->deleteTable('#__bsms_media', $db);
		$this->deleteTable('#__bsms_mimetype', $db);

		$this->updatetemplates($db);
		$this->css900();

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

		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');

		if (!JFile::exists(JPATH_SITE . '/images/biblestudy/logo.png'))
		{
			// Copy the images to the new folder
			JFolder::copy('/media/com_biblestudy/images', 'images/biblestudy/', JPATH_SITE, true);
		}

		$script = new BibleStudyModelInstall;
		$script->postinstall_messages($message);
		$this->css900();
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
		$columns2 = $db->getTableColumns($table);
		foreach ($columns as $column)
		{
			if (isset($columns2[$column]))
			{
				$db->setQuery('ALTER TABLE ' . $db->qn($table) . ' DROP COLUMN ' . $db->qn($column));
				$db->execute();
			}
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
		$db->setQuery('DROP TABLE ' . $db->qn($table));
		$db->execute();
	}

	/**
	 * Update Templates to work with 9.0.0 that cannot be don doing normal sql file.
	 *
	 * @param   JDatabaseDriver  $db  Data bass driver
	 *
	 * @return void
	 */
	private function updatetemplates ($db)
	{
		$query = $db->getQuery(true);
		$query->select('*')
				->from('#__bsms_templates');
		$db->setQuery($query);
		$data = $db->loadObjectList();
		foreach ($data as $d)
		{
			$registry = new Registry;
			$registry->loadString($d->params);
			$registry->def('player', $registry->get('media_player'));
			$d->params = $registry->toString();
			$db->updateObject('#__bsms_templates', $d, 'id');
		}
		return;
	}

	/**
	 * Update CSS for 9.0.0
	 *
	 * @return boolean
	 */
	public function css900 ()
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$path = array(BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR. 'style.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR .'styles.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR . 'style.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'style.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'styles.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'index.html',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'view.html.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR .'index.html',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'view.html.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'edit.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'index.html',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'view.html.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR .'index.html',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'view.html.php',
			BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default.php'
		);

		if (JFolder::exists($path))
		{
			foreach ($path as $file)
			{
				if (JFile::exists($file))
				{
					JFile::delete($file);
				}
			}
		}
		$folders = array(BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'styles',
		BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'style');

		foreach ($folders as $folder)
		{
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
		return true;
	}

}
