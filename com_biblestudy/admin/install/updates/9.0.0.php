<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

/**
 * Update for 9.0.0 class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Migration900
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
	 * Update Servers
	 *
	 * @param   JDatabaseDriver  $db      Joomla database driver
	 * @param   array            $server  Server to process
	 *
	 * @return bool
	 *
	 * @since 9.0.0
	 */
	public function servers($db, $server)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_biblestudy/tables');
		$server = (object) $server;

		if (!isset($server->id))
		{
			return true;
		}

		/** @var TableServer $newServer */
		$newServer = JTable::getInstance('Server', 'Table', array('dbo' => $db));
		$newServer->load($server->id);
		$params = array();

		if (!empty($server->ftphost))
		{
			// Migrate FTP server type
			$newServer->type       = "ftp";
			$params['ftphost']     = $server->ftp_username;
			$params['ftpuser']     = $server->ftp_password;
			$params['ftppassword'] = $server->ftp_password;
			$params['ftpport']     = $server->ftp_password;
		}
		elseif (!empty($server->aws_key))
		{
			// Migrate AWS server type
			$newServer->type      = "aws";
			$params['aws_key']    = $server->aws_key;
			$params['aws_secret'] = $server->aws_secret;
		}
		else
		{
			// Migrate to a default legacy server type
			$newServer->type = "legacy";
			$params['path']  = $server->server_path;
		}

		$newServer->params = json_encode($params);
		$newServer->id     = null;
		$newServer->store();

		// Delete old server
		JTable::getInstance('Server', 'Table', array('dbo' => $db))->delete($server->id);
		JLog::add('Server on: ' . $server->id . ', New ID: ' . $newServer->id, JLog::INFO, 'com_biblestudy');

		$this->query = array_merge($this->query, array('old-' . $server->id => $newServer));

		return true;
	}

	/**
	 * Update Media
	 *
	 * @param   JDatabaseDriver  $db     Joomla database driver
	 * @param   array            $media  Media to process.
	 *
	 * @return bool
	 *
	 * @throws string If problem with media save.
	 *
	 * @since 9.0.0
	 */
	public function media($db, $media)
	{
		if (!isset($media['id']))
		{
			return true;
		}

		$mediaFile = (object) $media;
		$registry = new Registry;
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_biblestudy/tables');

		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->qn('#__bsms_servers'))
			->where($db->qn('type') . " = " . $db->q('legacy'))
			->where($db->qn('server_name') . " = " . $db->q('Default'));
		$db->setQuery($query);
		$newServer = $db->loadObject();

		if ($newServer === null)
		{
			/** @var TableServer $newServer This is the new default server for all media */
			$newServer              = JTable::getInstance('Server', 'Table', array('dbo' => $db));
			$newServer->server_name = 'Default';
			$newServer->type        = 'legacy';
			$newServer->params      = '{"path":""}';
			$newServer->id          = null;
			$newServer->store();

			// Modify admin table to add thumbnail default parameters
			/** @type TableAdmin $admin */
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__bsms_admin')
				->where('id = 1');
			$db->setQuery($query);
			$admin    = $db->loadObject();
			$registry = new Registry;
			$registry->loadString($admin->params);
			$registry->set('server', $newServer->id);
			$admin->params = $registry->toString();
			$query         = $db->getQuery(true);
			$query->update('#__bsms_admin')->set('params = ' . $db->q($admin->params))->where('id = 1');
			$db->setQuery($query);
			$db->execute();
		}

		JLog::add('Media working on: ' . $mediaFile->id, JLog::INFO, 'com_biblestudy');

		/** @var TableMediafile $newMediaFile */
		$newMediaFile = JTable::getInstance('Mediafile', 'Table', array('dbo' => $db));
		$newMediaFile->load($mediaFile->id);
		$metadata = array();

		$query = $db->getQuery(true);
		$query->select('*')->from('#__bsms_media')->where('id = ' . (int) $mediaFile->media_image);
		$db->setQuery($query);

		$mediaImage = $db->loadObject();

		$query = $db->getQuery(true);
		$query->select('*')->from('#__bsms_mimetype')->where('id = ' . (int) $mediaFile->mime_type);
		$db->setQuery($query);

		$mimtype = $db->loadObject();
		$mimage = null;

		$folderpath = '';

		if ($mediaFile->path > 0)
		{
			$query = $db->getQuery(true);
			$query->select('*')->from('#__bsms_folders')->where('id = ' . (int) $mediaFile->path);
			$db->setQuery($query);
			$path = $db->loadObject();
			$folderpath = $path->folderpath;
		}

		// Some people do not have images set to their media so we have this.
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

				if (strpos($mediaImage->path2, '/') !== false)
				{
					$mimage = $mediaImage->path2;
				}
			}
		}

		$registry->loadString(json_encode($mediaFile->params));
		$params = $registry->toObject();

		$params->media_image = $mimage;
		$params->media_text  = $mediaImage->media_alttext;

		if (!empty($mimtype))
		{
			$params->mime_type = $mimtype->mimetype;
		}

		$params->special = $mediaFile->special;

		if (!empty($mediaFile->filename))
		{
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

		$params->player        = $mediaFile->player;
		$params->size          = $mediaFile->size;
		$params->mediacode     = $mediaFile->mediacode;
		$params->link_type     = $mediaFile->link_type;
		$params->docMan_id     = $mediaFile->docMan_id;
		$params->article_id    = $mediaFile->article_id;
		$params->virtueMart_id = $mediaFile->virtueMart_id;
		$params->popup         = $mediaFile->popup;

		$registry->loadObject($params);

		// Use old server ID to find new server ID.
		if (isset($this->query['old-' . $mediaFile->server]) && $mediaFile->server > '0')
		{
			// Cast server query to be object and not array.
			$oldserver = (Object) $this->query['old-' . $mediaFile->server];
			JLog::add('New Server ID status: ' . $oldserver->id, JLog::NOTICE, 'com_biblestudy');
			$newMediaFile->server_id = (int) $oldserver->id;
		}
		else
		{
			// Use default server ID.
			$newMediaFile->server_id = (int) $newServer->id;
			JLog::add('New Lag Server ID status: ' . $newServer->id, JLog::NOTICE, 'com_biblestudy');
		}

		$newMediaFile->params   = $registry->toString();
		$newMediaFile->metadata = json_encode($metadata);
		$newMediaFile->id       = null;

		if (!$newMediaFile->store())
		{
			throw new Exception('Bad update of media');
		}

		// Delete old mediaFile
		JTable::getInstance('Mediafile', 'Table', array('dbo' => $db))->delete((int) $mediaFile->id);

		return true;
	}

	/**
	 * Call Script for Updates of 9.0.0
	 *
	 * @param   JDatabaseDriver  $db  Joomla Database driver
	 *
	 * @return bool
	 *
	 * @since 9.0.0
	 */
	public function up($db)
	{
		// Delete unused columns
		$columns = array('media_image', 'special', 'filename', 'size', 'mime_type', 'mediacode', 'link_type',
			'docMan_id', 'article_id', 'virtueMart_id', 'player', 'popup', 'server', 'internal_viewer', 'path');
		$this->deleteColumns('#__bsms_mediafiles', $columns, $db);

		// Delete unused columns
		$columns = array('ftphost', 'ftpuser', 'ftppassword', 'ftpport', 'server_path', 'aws_key', 'aws_secret',
			'server_type', 'ftp_username', 'ftp_password');
		$this->deleteColumns('#__bsms_servers', $columns, $db);

		$this->deleteTable('#__bsms_folders', $db);
		$this->deleteTable('#__bsms_media', $db);
		$this->deleteTable('#__bsms_mimetype', $db);

		$db->setQuery("ALTER TABLE `#__bsms_servers` MODIFY COLUMN `type` CHAR(255) NOT NULL");
		$db->execute();
		$db->setQuery("ALTER TABLE `#__bsms_mediafiles` MODIFY COLUMN `hits` INT (10) DEFAULT '0'");
		$db->execute();

		$message                     = new stdClass;
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
		$message->enabled            = 1;

		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');

		if (!JFile::exists(JPATH_SITE . '/images/biblestudy/logo.png'))
		{
			// Copy the images to the new folder
			JFolder::copy('/media/com_biblestudy/images', 'images/biblestudy/', JPATH_SITE, true);
		}

		$script = new BibleStudyModelInstall;
		$script->postinstall_messages($message);

		return true;
	}

	/**
	 * Migrate Template Lists
	 *
	 * @param   JDatabaseDriver  $db  Joomla Database driver
	 *
	 * @return bool
	 *
	 * @since 9.0.0
	 */
	public function migrateTemplateLists($db)
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

			if ($registry->get('row1col1') > 0)
			{
				$row      = 1;
				$col      = 1;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row1col2') > 0)
			{
				$row      = 1;
				$col      = 2;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row1col3') > 0)
			{
				$row      = 1;
				$col      = 3;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row1col4') > 0)
			{
				$row      = 1;
				$col      = 4;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row2col1') > 0)
			{
				$row      = 2;
				$col      = 1;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row2col2') > 0)
			{
				$row      = 2;
				$col      = 2;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row2col3') > 0)
			{
				$row      = 2;
				$col      = 3;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row2col4') > 0)
			{
				$row      = 2;
				$col      = 4;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row3col1') > 0)
			{
				$row      = 3;
				$col      = 1;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row3col2') > 0)
			{
				$row      = 3;
				$col      = 2;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row3col3') > 0)
			{
				$row      = 3;
				$col      = 3;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row3col4') > 0)
			{
				$row      = 3;
				$col      = 4;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row4col1') > 0)
			{
				$row      = 4;
				$col      = 1;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row4col2') > 0)
			{
				$row      = 4;
				$col      = 2;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row4col3') > 0)
			{
				$row      = 4;
				$col      = 3;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('row4col4') > 0)
			{
				$row      = 4;
				$col      = 4;
				$return   = $this->changeSetting($row, $col, $registry);
				$registry = $return;
			}

			if ($registry->get('serieselement1') > 0)
			{
				$element     = $registry->get('serieselement1');
				$elementname = $this->serieselement($element);
				$registry->set($elementname . 'row', 1);
				$registry->set($elementname . 'col', 1);
				$registry->set($elementname . 'colspan', 6);
				$registry->set($elementname . 'linktype', $registry->get('serieslink1'));
			}

			if ($registry->get('serieselement2') > 0)
			{
				$element     = $registry->get('serieselement2');
				$elementname = $this->serieselement($element);
				$registry->set($elementname . 'row', 1);
				$registry->set($elementname . 'col', 2);
				$registry->set($elementname . 'colspan', 6);
				$registry->set($elementname . 'linktype', $registry->get('serieslink2'));
			}

			if ($registry->get('serieselement3') > 0)
			{
				$element     = $registry->get('serieselement3');
				$elementname = $this->serieselement($element);
				$registry->set($elementname . 'row', 2);
				$registry->set($elementname . 'col', 1);
				$registry->set($elementname . 'colspan', 6);
				$registry->set($elementname . 'linktype', $registry->get('serieslink3'));
			}

			if ($registry->get('serieselement4') > 0)
			{
				$element     = $registry->get('serieselement4');
				$elementname = $this->serieselement($element);
				$registry->set($elementname . 'row', 2);
				$registry->set($elementname . 'col', 2);
				$registry->set($elementname . 'colspan', 6);
				$registry->set($elementname . 'linktype', $registry->get('serieslink4'));
			}

			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$table = JTable::getInstance('template', 'Table');
			$table->load($d->id, false);
			$table->bind(array('params' => $registry->toString()));
			$table->store();
		}

		return true;
	}

	/**
	 * T Settings Migration
	 *
	 * @param   int  $element  ?
	 *
	 * @return bool|string
	 *
	 * @since 9.0.0
	 */
	public function testingMigration($element)
	{
		$elementText = $this->element($element);

		return $elementText;
	}

	/**
	 * Elements workings
	 *
	 * @param   int  $elementNumber  Number to stirng
	 *
	 * @return bool|string
	 *
	 * @since 9.0.0
	 */
	private function element($elementNumber)
	{
		switch ($elementNumber)
		{
			case 1:
				$element = 'scripture1';
				break;
			case 2:
				$element = 'scripture2';
				break;
			case 3:
				$element = 'secondary';
				break;
			case 4:
				$element = 'duration';
				break;
			case 5:
				$element = 'title';
				break;
			case 6:
				$element = 'studyintro';
				break;
			case 7:
				$element = 'teacher';
				break;
			case 8:
				$element = 'teacher-title';
				break;
			case 9:
				$element = 'series';
				break;
			case 10:
				$element = 'date';
				break;
			case 11:
				$element = 'submitted';
				break;
			case 12:
				$element = 'hits';
				break;
			case 13:
				$element = 'studynumber';
				break;
			case 14:
				$element = 'topic';
				break;
			case 15:
				$element = 'locations';
				break;
			case 16:
				$element = 'messagetype';
				break;
			case 17:
				$element = 'studyintro';
				break;
			case 20:
				$element = 'jbsmedia';
				break;
			case 25:
				$element = 'thumbnail';
				break;
			case 29:
				$element = 'downloads';
				break;
			case 24:
				$element = 'customtext';
				break;
		}

		if (isset($element))
		{
			return $element;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Seriese Elements
	 *
	 * @param   int  $element  ID
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	private function serieselement($element)
	{
		$return = null;

		switch ($element)
		{
			case 1:
				$return = 'sseries';
				break;
			case 2:
				$return = 'sseriesthumbnail';
				break;
			case 3:
				$return = 'sseriesthumbnail';
				break;
			case 4:
				$return = 'steacher';
				break;
			case 5:
				$return = 'steacherimage';
				break;
			case 6:
				$return = 'steacher-title';
				break;
			case 7:
				$return = 'sdescription';
				break;
		}

		return $return;
	}

	/**
	 * Change Settings
	 *
	 * @param   int       $row       ID
	 * @param   int       $col       ID
	 * @param   Registry  $registry  Strings for Registry
	 *
	 * @return Registry
	 *
	 * @since 9.0.0
	 */
	private function changeSetting($row, $col, $registry)
	{
		$element = $registry->get('row' . $row . 'col' . $col);
		$return  = $this->testingMigration($element);
		$registry->set($return . 'row', $row);
		$registry->set($return . 'col', $col);
		$registry->set($return . 'colspan', $registry->get('r' . $row . 'c' . $col . 'span'));
		$registry->set($return . 'linktype', $registry->get('linkr' . $row . 'c' . $col));

		return $registry;
	}

	/**
	 * Remove Export function to TemplateFiles
	 *
	 * @param   JDatabaseDriver  $db  Joomla Database driver
	 *
	 * @return bool
	 *
	 * @since 9.0.0
	 */
	public function removeExpert($db)
	{
		jimport('joomla.client.helper');
		jimport('joomla.filesystem.file');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_templates');
		$db->setQuery($query);
		$data       = $db->loadObjectList();
		$filenumber = 1;

		foreach ($data as $d)
		{
			$registry = new Registry;
			$registry->loadString($d->params);

			if ($registry->get('useexpert_list') > 0)
			{
				$dataheaderlist = $registry->get('headercode');
				$dataitemlist   = $registry->get('templatecode');
				$dataheaderlist = $this->itemReplace($dataheaderlist);
				$dataitemlist   = $this->itemReplace($dataitemlist);
				$filecontent    = '<?php defined(\'_JEXEC\') or die; ?>' . $dataheaderlist . '<?php foreach ($this->items as $study){ ?>' .
					$dataitemlist . '<?php } ?>';
				$filename       = 'default_listtemplate' . $filenumber;
				$file           = JPATH_ROOT . '/components/com_biblestudy/views/sermons/tmpl/' . $filename . '.php';
				JFile::write($file, $filecontent);
				$profile               = new stdClass;
				$profile->published    = 1;
				$profile->type         = 1;
				$profile->filename     = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id     = '';
				$db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('sermonstemplate', $filename);
			}

			if ($registry->get('useexpert_details') > 0)
			{
				$dataitemlist = $registry->get('study_detailtemplate');
				$dataitemlist = $this->itemReplace($dataitemlist);
				$filecontent  = '<?php defined(\'_JEXEC\') or die; $study = $this->item; ?>' . $dataitemlist;
				$filename     = 'default_sermontemplate' . $filenumber;
				$file         = JPATH_ROOT . '/components/com_biblestudy/views/sermon/tmpl/' . $filename . '.php';
				JFile::write($file, $filecontent);
				$profile               = new stdClass;
				$profile->published    = 1;
				$profile->type         = 2;
				$profile->filename     = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id     = '';
				$db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('sermontemplate', $filename);
			}

			if ($registry->get('useexpert_teacherlist') > 0)
			{
				$dataheaderlist = $registry->get('teacher_headercode');
				$dataitemlist   = $registry->get('teacher_templatecode');
				$dataheaderlist = $this->itemReplace($dataheaderlist);
				$dataitemlist   = str_replace('{{title}}', '{{teachertitlelist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{teacher}}', '{{teachernamelist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{phone}}', '{{teacherphonelist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{website}}', '{{teacherwebsitelist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{information}}', '{{teacherinformationlist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{image}}', '{{teacherimagelist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{thumbnail}}', '{{teacherthumbnaillist}}', $dataitemlist);
				$dataitemlist   = str_replace('{{short}}', '{{teachershortlist}}', $dataitemlist);
				$dataitemlist   = $this->itemReplace($dataitemlist);
				$filecontent    = '<?php defined(\'_JEXEC\') or die; ?>' . $dataheaderlist . '<?php foreach ($this->items as $teacher){ ?>' .
					$dataitemlist . '<?php } ?>';
				$filename       = 'default_teacherstemplate' . $filenumber;
				$file           = JPATH_ROOT . '/components/com_biblestudy/views/teachers/tmpl/' . $filename . '.php';
				JFile::write($file, $filecontent);
				$profile               = new stdClass;
				$profile->published    = 1;
				$profile->type         = 3;
				$profile->filename     = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id     = '';
				$db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('teacherstemplate', $filename);
			}

			if ($registry->get('useexpert_teacherdetail') > 0)
			{
				$dataitemlist = $registry->get('teacher_detailtemplate');
				$dataitemlist = str_replace('{{title}}', '{{teachertitle}}', $dataitemlist);
				$dataitemlist = str_replace('{{teacher}}', '{{teachername}}', $dataitemlist);
				$dataitemlist = $this->itemReplace($dataitemlist);
				$filecontent  = '<?php defined(\'_JEXEC\') or die; ?>' . $dataitemlist;
				$filename     = 'default_teachertemplate' . $filenumber;
				$file         = JPATH_ROOT . '/components/com_biblestudy/views/teacher/tmpl/' . $filename . '.php';
				JFile::write($file, $filecontent);
				$profile               = new stdClass;
				$profile->published    = 1;
				$profile->type         = 4;
				$profile->filename     = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id     = '';
				$db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('teachertemplate', $filename);
			}

			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$table = JTable::getInstance('template', 'Table');
			$table->load($d->id, false);
			$table->bind(array('params' => $registry->toString()));
			$table->store();
			$filenumber++;
		}

		return true;
	}

	/**
	 * Replace Strings to actual code
	 *
	 * @param   string  $item  String to replace
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	private function itemReplace($item)
	{
		$item = str_replace('{{teacher}}', '<?php echo $study->teachername; ?>', $item);
		$item = str_replace('{{teachertitle}}', '<?php echo $this->item->title; ?>', $item);
		$item = str_replace('{{teachername}}', '<?php echo $this->item->teachername; ?>', $item);
		$item = str_replace('{{teachertitlelist}}', '<?php echo $teacher->title; ?>', $item);
		$item = str_replace('{{teachernamelist}}', '<?php echo $teacher->teachername; ?>', $item);
		$item = str_replace('{{title}}', '<?php echo $study->studytitle; ?>', $item);
		$item = str_replace('{{date}}', '<?php echo $study->studydate; ?>', $item);
		$item = str_replace('{{studyintro}}', '<?php echo $study->studyintro; ?>', $item);
		$item = str_replace('{{scripture}}', '<?php echo $study->scripture1; ?>', $item);
		$item = str_replace('{{topics}}', '<?php echo $study->topics; ?>', $item);
		$item = str_replace('{{scripture}}', '<?php echo $study->scripture1; ?>', $item);
		$item = str_replace('{{url}}', '<?php echo $study->detailslink; ?>', $item);
		$item = str_replace('{{mediatime}}', '<?php echo $study->duration; ?>', $item);
		$item = str_replace('{{thumbnail}}', '<?php echo $study->study_thumbnail; ?>', $item);
		$item = str_replace('{{seriestext}}', '', $item);
		$item = str_replace('{{bookname}}', '<?php echo $study->scripture1; ?>', $item);
		$item = str_replace('{{hits}}', '<?php echo $study->hits;', $item);
		$item = str_replace('{{location}}', '<?php echo $study->location_text; ?>', $item);
		$item = str_replace('{{plays}}', '<?php echo $study->totaplays; ?>', $item);
		$item = str_replace('{{downloads}}', '<?php echo $study->totaldownloads; ?>', $item);
		$item = str_replace('{{media}}', '<?php echo $study->media; ?>', $item);
		$item = str_replace('{{messagetype}}', '<?php echo $study->messagetypes; ?>', $item);
		$item = str_replace('{{studytext}}', '<?php echo $this->item->studytext; ?>', $item);
		$item = str_replace('{{scipturelink}}', '<?php  echo $this->passage; ?>', $item);
		$item = str_replace('{{share}}', '<?php echo $this->page->social; ?>', $item);
		$item = str_replace('{{printview}}', '<?php echo $this->page->print; ?>', $item);
		$item = str_replace('{{pdfview}}', '', $item);
		$item = str_replace('{{phone}}', '<?php echo $this->item->phone; ?>', $item);
		$item = str_replace('{{teacherphonelist}}', '<?php echo $teacher->phone; ?>', $item);
		$item = str_replace('{{website}}', '<?php echo $this->item->website; ?>', $item);
		$item = str_replace('{{teacherwebsitelist}}', '<?php echo $teacher->website; ?>', $item);
		$item = str_replace('{{information}}', '<?php echo $this->item->information; ?>', $item);
		$item = str_replace('{{teacherinformationlist}}', '<?php echo $teacher->information; ?>', $item);
		$item = str_replace('{{image}}', '<?php echo $this->item->largeimage; ?>', $item);
		$item = str_replace('{{teacherimagelist}}', '<?php echo $teacher->largeimage; ?>', $item);
		$item = str_replace('{{thumbnail}}', '<?php echo $this->item->image; ?>', $item);
		$item = str_replace('{{teacherthumbnaillist}}', '<?php echo $teacher->image; ?>', $item);
		$item = str_replace('{{short}}', '<?php echo $this->item->short; ?>', $item);
		$item = str_replace('{{teachershortlist}}', '<?php echo $teacher->short; ?>', $item);

		return $item;
	}

	/**
	 * Set del columns
	 *
	 * @param   string           $table    Table
	 * @param   array            $columns  Column to drop
	 * @param   JDatabaseDriver  $db       Data bass driver
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	private function deleteColumns($table, $columns, $db)
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
	 * @param   JDatabaseDriver  $db     Joomla Database driver
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	private function deleteTable($table, $db)
	{
		$db->setQuery('DROP TABLE IF EXISTS ' . $db->qn($table));
		$db->execute();
	}

	/**
	 * Update Templates to work with 9.0.0 that cannot be don doing normal sql file.
	 *
	 * @param   JDatabaseDriver  $db  Joomla Database driver
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public function updateTemplates($db)
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
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$table = JTable::getInstance('template', 'Table');
			$table->load($d->id, false);
			$table->bind(array('params' => $registry->toString()));
			$table->store();
		}

		return;
	}

	/**
	 * Remove Old Files and Folders
	 *
	 * @since      9.0.1
	 *
	 * @return   void
	 */
	public function deleteUnexactingFiles()
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$files = array(
			'/media/com_biblestudy/css/biblestudy.css.dist',
			'/images/textfile24.png',
			'/components/com_biblestudy/biblestudy.css',
			'/components/com_biblestudy/class.biblestudydownload.php',
			'/components/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
			'/administrator/components/com_biblestudy/Snoopy.class.php',
			'/administrator/components/com_biblestudy/admin.biblestudy.php',
			'/components/com_biblestudy/helpers/updatesef.php',
			'/components/com_biblestudy/helpers/image.php',
			'/components/com_biblestudy/helpers/helper.php',
			'/components/com_biblestudy/views/messages/tmpl/modal16.php',
			'/components/com_biblestudy/controllers/teacherlist.php',
			'/components/com_biblestudy/controllers/teacherdisplay.php',
			'/components/com_biblestudy/controllers/studydetails.php',
			'/components/com_biblestudy/controllers/studieslist.php',
			'/components/com_biblestudy/controllers/serieslist.php',
			'/components/com_biblestudy/controllers/seriesdetail.php',
			'/components/com_biblestudy/models/teacherlist.php',
			'/components/com_biblestudy/models/teacherdisplay.php',
			'/components/com_biblestudy/models/studydetails.php',
			'/components/com_biblestudy/models/studieslist.php',
			'/components/com_biblestudy/models/seriesdetail.php',
			'/components/com_biblestudy/models/serieslist.php',
			'/components/com_biblestudy/views/mediafile/tmpl/form.php',
			'/components/com_biblestudy/views/mediafile/tmpl/form.xml',
			'/language/en-GB/en-GB.com_biblestudy.ini',
			'/language/cs-CZ/cs-CZ.com_biblestudy.ini',
			'/language/de-DE/de-DE.com_biblestudy.ini',
			'/language/es-ES/es-ES.com_biblestudy.ini',
			'/language/hu-HU/hu-HU.com_biblestudy.ini',
			'/language/nl-NL/nl-NL.com_biblestudy.ini',
			'/language/no-NO/no-NO.com_biblestudy.ini',
			'/language/en-GB/en-GB.mod_biblestudy.ini',
			'/language/en-GB/en-GB.mod_biblestudy.sys.ini',
			'/administrator/components/com_biblestudy/install/biblestudy.assets.php',
			'/administrator/components/com_biblestudy/install/sql/jbs7.0.0.sql',
			'/administrator/components/com_biblestudy/install/sql/updates/mysql/20100101.sql',
			'/administrator/components/com_biblestudy/lib/biblestudy.podcast.class.php',
			'/administrator/components/com_biblestudy/controllers/commentsedit.php',
			'/administrator/components/com_biblestudy/controllers/commentslist.php',
			'/administrator/components/com_biblestudy/controllers/cssedit.php',
			'/administrator/components/com_biblestudy/controllers/folderslist.php',
			'/administrator/components/com_biblestudy/controllers/foldersedit.php',
			'/administrator/components/com_biblestudy/controllers/folder.php',
			'/administrator/components/com_biblestudy/controllers/folders.php',
			'/administrator/components/com_biblestudy/controllers/locationslist.php',
			'/administrator/components/com_biblestudy/controllers/locationsedit.php',
			'/administrator/components/com_biblestudy/controllers/mediaedit.php',
			'/administrator/components/com_biblestudy/controllers/mediafilesedit.php',
			'/administrator/components/com_biblestudy/controllers/mediafileslist.php',
			'/administrator/components/com_biblestudy/controllers/mediaimage.php',
			'/administrator/components/com_biblestudy/controllers/mediaimages.php',
			'/administrator/components/com_biblestudy/controllers/medialist.php',
			'/administrator/components/com_biblestudy/controllers/messagetypelist.php',
			'/administrator/components/com_biblestudy/controllers/messagetypeedit.php',
			'/administrator/components/com_biblestudy/controllers/mimetypelist.php',
			'/administrator/components/com_biblestudy/controllers/mimetypeedit.php',
			'/administrator/components/com_biblestudy/controllers/mimetype.php',
			'/administrator/components/com_biblestudy/controllers/mimetypes.php',
			'/administrator/components/com_biblestudy/controllers/podcastlist.php',
			'/administrator/components/com_biblestudy/controllers/podcastedit.php',
			'/administrator/components/com_biblestudy/controllers/serieslist.php',
			'/administrator/components/com_biblestudy/controllers/seriesedit.php',
			'/administrator/components/com_biblestudy/controllers/serverslist.php',
			'/administrator/components/com_biblestudy/controllers/serversedit.php',
			'/administrator/components/com_biblestudy/controllers/sharelist.php',
			'/administrator/components/com_biblestudy/controllers/shareedit.php',
			'/administrator/components/com_biblestudy/controllers/studieslist.php',
			'/administrator/components/com_biblestudy/controllers/studiesedit.php',
			'/administrator/components/com_biblestudy/controllers/teacherlist.php',
			'/administrator/components/com_biblestudy/controllers/teacheredit.php',
			'/administrator/components/com_biblestudy/controllers/templateedit.php',
			'/administrator/components/com_biblestudy/controllers/templateslist.php',
			'/administrator/components/com_biblestudy/controllers/topicslist.php',
			'/administrator/components/com_biblestudy/controllers/topicsedit.php',
			'/administrator/components/com_biblestudy/models/forms/commentsedit.xml',
			'/administrator/components/com_biblestudy/models/forms/foldersedit.xml',
			'/administrator/components/com_biblestudy/models/forms/folder.xml',
			'/administrator/components/com_biblestudy/models/forms/locationsedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediaedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediafilesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mediaimage.xml',
			'/administrator/components/com_biblestudy/models/forms/messagetypeedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mimetypeedit.xml',
			'/administrator/components/com_biblestudy/models/forms/mimetype.xml',
			'/administrator/components/com_biblestudy/models/forms/podcastedit.xml',
			'/administrator/components/com_biblestudy/models/forms/seriesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/serversedit.xml',
			'/administrator/components/com_biblestudy/models/forms/shareedit.xml',
			'/administrator/components/com_biblestudy/models/forms/studiesedit.xml',
			'/administrator/components/com_biblestudy/models/forms/teacheredit.xml',
			'/administrator/components/com_biblestudy/models/forms/templateedit.xml',
			'/administrator/components/com_biblestudy/models/forms/topicsedit.xml',
			'/administrator/components/com_biblestudy/models/episodelist.php',
			'/administrator/components/com_biblestudy/models/commentsedit.php',
			'/administrator/components/com_biblestudy/models/commentslist.php',
			'/administrator/components/com_biblestudy/models/cssedit.php',
			'/administrator/components/com_biblestudy/models/folderslist.php',
			'/administrator/components/com_biblestudy/models/foldersedit.php',
			'/administrator/components/com_biblestudy/models/folder.php',
			'/administrator/components/com_biblestudy/models/folders.php',
			'/administrator/components/com_biblestudy/models/locationslist.php',
			'/administrator/components/com_biblestudy/models/locationsedit.php',
			'/administrator/components/com_biblestudy/models/mediaedit.php',
			'/administrator/components/com_biblestudy/models/mediafilesedit.php',
			'/administrator/components/com_biblestudy/models/mediafileslist.php',
			'/administrator/components/com_biblestudy/models/mediaimage.php',
			'/administrator/components/com_biblestudy/models/mediaimages.php',
			'/administrator/components/com_biblestudy/models/medialist.php',
			'/administrator/components/com_biblestudy/models/messagetypelist.php',
			'/administrator/components/com_biblestudy/models/messagetypeedit.php',
			'/administrator/components/com_biblestudy/models/mimetypelist.php',
			'/administrator/components/com_biblestudy/models/mimetypeedit.php',
			'/administrator/components/com_biblestudy/models/mimetype.php',
			'/administrator/components/com_biblestudy/models/mimetypes.php',
			'/administrator/components/com_biblestudy/models/podcastlist.php',
			'/administrator/components/com_biblestudy/models/podcastedit.php',
			'/administrator/components/com_biblestudy/models/serieslist.php',
			'/administrator/components/com_biblestudy/models/seriesedit.php',
			'/administrator/components/com_biblestudy/models/serverslist.php',
			'/administrator/components/com_biblestudy/models/serversedit.php',
			'/administrator/components/com_biblestudy/models/sharelist.php',
			'/administrator/components/com_biblestudy/models/shareedit.php',
			'/administrator/components/com_biblestudy/models/studieslist.php',
			'/administrator/components/com_biblestudy/models/studiesedit.php',
			'/administrator/components/com_biblestudy/models/teacherlist.php',
			'/administrator/components/com_biblestudy/models/teacheredit.php',
			'/administrator/components/com_biblestudy/models/templateedit.php',
			'/administrator/components/com_biblestudy/models/templateslist.php',
			'/administrator/components/com_biblestudy/models/topicslist.php',
			'/administrator/components/com_biblestudy/models/topicsedit.php',
			'/administrator/components/com_biblestudy/tables/biblestudy.php',
			'/administrator/components/com_biblestudy/tables/booksedit.php',
			'/administrator/components/com_biblestudy/tables/commentsedit.php',
			'/administrator/components/com_biblestudy/tables/foldersedit.php',
			'/administrator/components/com_biblestudy/tables/locationsedit.php',
			'/administrator/components/com_biblestudy/tables/mediaedit.php',
			'/administrator/components/com_biblestudy/tables/mediafilesedit.php',
			'/administrator/components/com_biblestudy/tables/messagetypeedit.php',
			'/administrator/components/com_biblestudy/tables/mimetypeedit.php',
			'/administrator/components/com_biblestudy/tables/podcastedit.php',
			'/administrator/components/com_biblestudy/tables/seriesedit.php',
			'/administrator/components/com_biblestudy/tables/serversedit.php',
			'/administrator/components/com_biblestudy/tables/shareedit.php',
			'/administrator/components/com_biblestudy/tables/studiesedit.php',
			'/administrator/components/com_biblestudy/tables/teacheredit.php',
			'/administrator/components/com_biblestudy/tables/topicsedit.php',
			'/administrator/components/com_biblestudy/tables/templateedit.php',
			'/administrator/components/com_biblestudy/helpers/version.php',
			'/administrator/language/en-GB/en-GB.com_biblestudy.ini',
			'/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
			'/administrator/language/cs-CZ/cs-CZ.com_biblestudy.ini',
			'/administrator/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini',
			'/administrator/language/de-DE/de-DE.com_biblestudy.ini',
			'/administrator/language/de-DE/de-DE.com_biblestudy.sys.ini',
			'/administrator/language/es-ES/es-ES.com_biblestudy.ini',
			'/administrator/language/es-ES/es-ES.com_biblestudy.sys.ini',
			'/administrator/language/hu-HU/hu-HU.com_biblestudy.ini',
			'/administrator/language/hu-HU/hu-HU.com_biblestudy.sys.ini',
			'/administrator/language/nl-NL/nl-NL.com_biblestudy.ini',
			'/administrator/language/nl-NL/no-NO.com_biblestudy.ini',
			'/administrator/language/no-NO/no-NO.com_biblestudy.sys.ini',
			// JBSM 8.0.0
			// Site:
			'/components/com_biblestudy/controllers/commentsedit.php',
			'/components/com_biblestudy/controllers/commentslist.php',
			'/components/com_biblestudy/controllers/mediafile.php',
			'/components/com_biblestudy/controllers/mediafiles.php',
			'/components/com_biblestudy/controllers/message.php',
			'/components/com_biblestudy/controllers/messages.php',
			'/components/com_biblestudy/helpers/book.php',
			'/components/com_biblestudy/helpers/date.php',
			'/components/com_biblestudy/helpers/duration.php',
			'/components/com_biblestudy/helpers/editlink.php',
			'/components/com_biblestudy/helpers/editlisting.php',
			'/components/com_biblestudy/helpers/filepath.php',
			'/components/com_biblestudy/helpers/filesize.php',
			'/components/com_biblestudy/helpers/header.php',
			'/components/com_biblestudy/helpers/listing.php',
			'/components/com_biblestudy/helpers/location.php',
			'/components/com_biblestudy/helpers/mediatable.php',
			'/components/com_biblestudy/helpers/messagetype.php',
			'/components/com_biblestudy/helpers/params.php',
			'/components/com_biblestudy/helpers/passage.php',
			'/components/com_biblestudy/helpers/scripture.php',
			'/components/com_biblestudy/helpers/share.php',
			'/components/com_biblestudy/helpers/store.php',
			'/components/com_biblestudy/helpers/textlink.php',
			'/components/com_biblestudy/helpers/title.php',
			'/components/com_biblestudy/helpers/toolbar.php',
			'/components/com_biblestudy/helpers/topics.php',
			'/components/com_biblestudy/helpers/year.php',
			'/components/com_biblestudy/lib/biblestudy.admin.class.php',
			'/components/com_biblestudy/lib/biblestudy.defines.php',
			'/components/com_biblestudy/lib/biblestudy.stats.class.php',
			'/components/com_biblestudy/models/forms/commentsedit.xml',
			'/components/com_biblestudy/models/commentsedit.php',
			'/components/com_biblestudy/models/commentslist.php',
			'/components/com_biblestudy/models/mediafile.php',
			'/components/com_biblestudy/models/mediafiles.php',
			'/components/com_biblestudy/models/message.php',
			'/components/com_biblestudy/models/messages.php',
			// Admin:
			'/administrator/components/com_biblestudy/controllers/ajax.php',
			'/administrator/components/com_biblestudy/helpers/cleanurl.php',
			'/administrator/components/com_biblestudy/helpers/toolbar.php',
			'/administrator/components/com_biblestudy/lib/biblestudy.admin.class.php',
			'/administrator/components/com_biblestudy/lib/biblestudy.migrate.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.611.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.612.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.613.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.614.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.622.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.623.upgrade.php',
			'/administrator/components/com_biblestudy/migration/biblestudy.700.upgrade.php',
			'/administrator/components/com_biblestudy/migration/update701.php',
			'/administrator/components/com_biblestudy/models/fields/locationordering.php',
			'/administrator/components/com_biblestudy/models/fields/mediaordering.php',
			'/administrator/components/com_biblestudy/models/fields/messagetypeordering.php',
			'/administrator/components/com_biblestudy/models/fields/shareordering.php',
			'/administrator/components/com_biblestudy/models/fields/teacherordering.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_assets.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_backup.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_database.php',
			'/administrator/components/com_biblestudy/views/admin/tmpl/form_migrate.php',
			'/administrator/components/com_biblestudy/views/comment/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/folder/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/folders/tmpl/index.html',
			'/administrator/components/com_biblestudy/views/location/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/mediaimage/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/message/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/messagetype/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/mimetype/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/podcast/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/serie/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/server/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/server/tmpl/index.html',
			'/administrator/components/com_biblestudy/views/share/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/teacher/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/template/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/templatecode/tmpl/form.php',
			'/administrator/components/com_biblestudy/views/topic/tmpl/form.php',
				BIBLESTUDY_PATH_ADMIN . '/models/style.php',
				BIBLESTUDY_PATH_ADMIN . '/models/styles.php',
				BIBLESTUDY_PATH_ADMIN . '/tables/style.php',
				BIBLESTUDY_PATH_ADMIN . '/controllers/style.php',
				BIBLESTUDY_PATH_ADMIN . '/controllers/styles.php',
				BIBLESTUDY_PATH_ADMIN . '/views/style/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/style/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/style/tmpl/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/style/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/style/tmpl/edit.php',
				BIBLESTUDY_PATH_ADMIN . '/views/styles/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/styles/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/styles/tmpl/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/styles/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/styles/tmpl/default.php',
				BIBLESTUDY_PATH_ADMIN . '/models/share.php',
				BIBLESTUDY_PATH_ADMIN . '/models/shares.php',
				BIBLESTUDY_PATH_ADMIN . '/tables/share.php',
				BIBLESTUDY_PATH_ADMIN . '/controllers/share.php',
				BIBLESTUDY_PATH_ADMIN . '/controllers/shares.php',
				BIBLESTUDY_PATH_ADMIN . '/views/share/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/share/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/share/tmpl/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/share/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/share/tmpl/edit.php',
				BIBLESTUDY_PATH_ADMIN . '/views/shares/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/shares/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/shares/tmpl/index.html',
				BIBLESTUDY_PATH_ADMIN . '/views/shares/view.html.php',
				BIBLESTUDY_PATH_ADMIN . '/views/shares/tmpl/default.php',
				BIBLESTUDY_PATH_ADMIN . '/moduels/migration.php',
				BIBLESTUDY_PATH_ADMIN . '/controllers/migration.php',
				BIBLESTUDY_PATH . '/views/sermons/tmpl/default_custom.php',
				BIBLESTUDY_PATH . '/views/sermon/tmpl/default_custom.php',
				BIBLESTUDY_PATH . '/views/teachers/tmpl/default_custom.php',
				BIBLESTUDY_PATH . '/views/teacher/tmpl/default_custom.php'
		);

		$folders = array(
			'/components/com_biblestudy/assets',
			'/components/com_biblestudy/images',
			'/components/com_biblestudy/views/teacherlist',
			'/components/com_biblestudy/views/teacherdisplay',
			'/components/com_biblestudy/views/studieslist',
			'/components/com_biblestudy/views/studydetails',
			'/components/com_biblestudy/views/serieslist',
			'/components/com_biblestudy/views/seriesdetail',
			'/administrator/media',
			'/administrator/components/com_biblestudy/migration',
			'/administrator/components/com_biblestudy/assets',
			'/administrator/components/com_biblestudy/images',
			'/administrator/components/com_biblestudy/css',
			'/administrator/components/com_biblestudy/js',
			'/administrator/components/com_biblestudy/views/commentsedit',
			'/administrator/components/com_biblestudy/views/commentslist',
			'/administrator/components/com_biblestudy/views/cssedit',
			'/administrator/components/com_biblestudy/views/folderslist',
			'/administrator/components/com_biblestudy/views/foldersedit',
			'/administrator/components/com_biblestudy/views/folder',
			'/administrator/components/com_biblestudy/views/folders',
			'/administrator/components/com_biblestudy/views/locationslist',
			'/administrator/components/com_biblestudy/views/locationsedit',
			'/administrator/components/com_biblestudy/views/mediaedit',
			'/administrator/components/com_biblestudy/views/mediafilesedit',
			'/administrator/components/com_biblestudy/views/mediafileslist',
			'/administrator/components/com_biblestudy/views/mediaimage',
			'/administrator/components/com_biblestudy/views/mediaimages',
			'/administrator/components/com_biblestudy/views/medialist',
			'/administrator/components/com_biblestudy/views/messagetypelist',
			'/administrator/components/com_biblestudy/views/messagetypeedit',
			'/administrator/components/com_biblestudy/views/mimetypelist',
			'/administrator/components/com_biblestudy/views/mimetypeedit',
			'/administrator/components/com_biblestudy/views/mimetype',
			'/administrator/components/com_biblestudy/views/mimetypes',
			'/administrator/components/com_biblestudy/views/podcastlist',
			'/administrator/components/com_biblestudy/views/podcastedit',
			'/administrator/components/com_biblestudy/views/serieslist',
			'/administrator/components/com_biblestudy/views/seriesedit',
			'/administrator/components/com_biblestudy/views/serverslist',
			'/administrator/components/com_biblestudy/views/serversedit',
			'/administrator/components/com_biblestudy/views/sharelist',
			'/administrator/components/com_biblestudy/views/shareedit',
			'/administrator/components/com_biblestudy/views/studieslist',
			'/administrator/components/com_biblestudy/views/studiesedit',
			'/administrator/components/com_biblestudy/views/teacherlist',
			'/administrator/components/com_biblestudy/views/teacheredit',
			'/administrator/components/com_biblestudy/views/templateedit',
			'/administrator/components/com_biblestudy/views/templateslist',
			'/administrator/components/com_biblestudy/views/topicslist',
			'/administrator/components/com_biblestudy/views/topicsedit',
			// JBSM 8.0.0
			'/components/com_biblestudy/views/messages',
			'/components/com_biblestudy/views/message',
			'/components/com_biblestudy/views/mediafiles',
			'/components/com_biblestudy/views/mediafile',
			'/components/com_biblestudy/views/commentslist',
			'/components/com_biblestudy/views/commentsedit',
				BIBLESTUDY_PATH_ADMIN . '/views/styles',
				BIBLESTUDY_PATH_ADMIN . '/views/style',
				BIBLESTUDY_PATH_ADMIN . '/views/shares',
				BIBLESTUDY_PATH_ADMIN . '/views/share',
				BIBLESTUDY_PATH_ADMIN . '/views/migration'
		);

		foreach ($files as $file)
		{
			if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br />';
			}
		}

		foreach ($folders as $folder)
		{
			if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br />';
			}
		}

		return;
	}
}
