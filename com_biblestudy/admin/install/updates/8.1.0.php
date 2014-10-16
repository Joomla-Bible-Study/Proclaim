<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update for 8.1.0 class
 *
 * @package  BibleStudy.Admin
 * @since    8.1.0
 */
class Migration810
{
	/**
	 * Call Script for Updates of 8.1.0
	 *
	 * @param   JDatabaseDriver $db Joomla Data bass driver
	 *
	 * @return bool
	 */
	public function up($db)
	{
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
				$params['path'] = $server->path;
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

				$newMediaFile = JTable::getInstance('Mediafile', 'Table', array('dbo' => $db));
				$newMediaFile->load($mediaFile->id);
				$params   = array();
				$metadata = array();

				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_media')->where('id = ' . (int) $mediaFile->media_image);
				$db->setQuery($query);

				$mediaImage = $db->loadObject();

				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_mimetype')->where('id = ' . (int) $mediaFile->mime_type);
				$db->setQuery($query);

				$mimtype = $db->loadObject();

				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_folders')->where('id = ' . (int) $mediaFile->path);
				$db->setQuery($query);

				$path   = $db->loadObject();
				$mimage = null;

				// Some people do not have logos set to there media so we have this.
				if (!$mediaImage)
				{
					$mediaImage         = new stdClass;
					$mediaImage->mimage = null;
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
				$params['media_image'] = $mimage;
				$params['media_text']  = $mediaImage->media_alttext;
				$params['mime_type']   = $mimtype->mimetype;
				$params['special']       = $mediaFile->special;
				$params['filename']      = $server->server_path . $path->folderpath . $mediaFile->filename;
				$params['size']          = $mediaFile->size;
				$params['mediacode']     = $mediaFile->mediacode;
				$params['link_type']     = $mediaFile->link_type;
				$params['docMan_id']     = $mediaFile->docMan_id;
				$params['article_id']    = $mediaFile->article_id;
				$params['virtueMart_id'] = $mediaFile->virtueMart_id;
				$params['player']        = $mediaFile->player;
				$params['popup']         = $mediaFile->popup;

				// @todo I don't thing we want to add both hits and plays to gather. I'm under hits a as the one but will need to verify this with tom
				$metadata['hits']      = $mediaFile->hits + $mediaFile->plays;
				$metadata['downloads'] = $mediaFile->downloads;

				$newMediaFile->server_id = $newServer->id;
				$newMediaFile->params    = json_encode($params);
				$newMediaFile->metadata  = json_encode($metadata);
				$newMediaFile->id        = null;
				$newMediaFile->store();

				// Delete old mediafile
				JTable::getInstance('Mediafile', 'Table', array('dbo' => $db))->delete($mediaFile->id);
			}
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

		return true;
	}

	/**
	 * Set del colums
	 *
	 * @param   string          $table   Table
	 * @param   array           $columns Column to drop
	 * @param   JDatabaseDriver $db      Data bass driver
	 *
	 * @return void
	 */
	private function deleteColumns($table, $columns, $db)
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
	 * @param   string          $table Table
	 * @param   JDatabaseDriver $db    Data bass driver
	 *
	 * @return void
	 */
	private function deleteTable($table, $db)
	{
		$db->setQuery('DROP TABLE ' . $table);
		$db->execute();
	}

	/**
	 * Update Templates to work with 8.1.0 that cannot be don doing normal sql file.
	 *
	 * @param   JDatabaseDriver $db Data bass driver
	 *
	 * @return void
	 */
	public function updatetemplates($db)
	{
		$query = $db->getQuery(true);
		$query->select('id, title, prarams')
			->from('#__bsms_templates');
		$db->setQuery($query);
		$data = $db->loadObjectList();
		foreach ($data as $d)
		{
			// Load Table Data.
			JTable::addIncludePath(JPATH_COMPONENT . '/tables');
			$table = JTable::getInstance('Template', 'Table', array('dbo' => $db));

			try
			{
				$table->load($d->id);
			}
			catch (Exception $e)
			{
				echo 'Caught exception: ', $e->getMessage(), "\n";
			}

			// Store the table to invoke defaults of new params
			// Todo need ot add change from page_title to page_headline to not conflict with menu.

			$table->store();
		}
	}

	/**
	 * Update CSS for 8.1.0
	 *
	 * @return boolean
	 */
	public function css810()
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
