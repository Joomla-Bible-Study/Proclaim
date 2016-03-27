<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
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
		$this->removeexpert($db);
		return true;
	}

	private function removeexpert($db)
	{
		jimport('joomla.client.helper');
		jimport('joomla.filesystem.file');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_templates');
		$db->setQuery($query);
		$data = $db->loadObjectList();
		$filenumber = 1;
		foreach ($data as $d)
		{
			$dataitem = '';
			$dataheader = '';
			$registry = new Registry;
			$registry->loadString($d->params);
			if ($registry->get('useexpert_list') > 0)
			{
				$dataheaderlist = $registry->get('headercode');
				$dataitemlist = $registry->get('templatecode');
				$dataheaderlist = $this->itemreplace($dataheaderlist);
				$dataitemlist = $this->itemreplace($dataitemlist);
				$filecontent = '<?php defined(\'_JEXEC\') or die; ?>' . $dataheaderlist .'<?php foreach ($this->items as $study){ ?>'. $dataitemlist.'<?php } ?>';
				$filename = 'default_listtemplate'.$filenumber;
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/sermons/tmpl' . DIRECTORY_SEPARATOR . $filename.'.php';
				$return = JFile::write($file, $filecontent);
				$profile = new stdClass();
				$profile->published = 1;
				$profile->type = 1;
				$profile->filename = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id = '';
				$result = $db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('sermonstemplate', $filename);
			}
			if ($registry->get('useexpert_details') > 0)
			{
				$dataitemlist = $registry->get('study_detailtemplate');
				$dataitemlist = $this->itemreplace($dataitemlist);
				$filecontent = '<?php defined(\'_JEXEC\') or die; $study = $this->item; ?>' . $dataitemlist;
				$filename = 'default_sermontemplate'.$filenumber;
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/sermon/tmpl' . DIRECTORY_SEPARATOR . $filename.'.php';
				$return = JFile::write($file, $filecontent);
				$profile = new stdClass();
				$profile->published = 1;
				$profile->type = 2;
				$profile->filename = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id = '';
				$result = $db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('sermontemplate', $filename);
			}
			if ($registry->get('useexpert_teacherlist') > 0)
			{
				$dataheaderlist = $registry->get('teacher_headercode');
				$dataitemlist = $registry->get('teacher_templatecode');
				$dataheaderlist = $this->itemreplace($dataheaderlist);
				$dataitemlist  = str_replace('{{title}}', '{{teachertitlelist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{teacher}}', '{{teachernamelist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{phone}}', '{{teacherphonelist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{website}}', '{{teacherwebsitelist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{information}}', '{{teacherinformationlist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{image}}', '{{teacherimagelist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{thumbnail}}', '{{teacherthumbnaillist}}', $dataitemlist);
				$dataitemlist  = str_replace('{{short}}', '{{teachershortlist}}', $dataitemlist);
				$dataitemlist = $this->itemreplace($dataitemlist);
				$filecontent = '<?php defined(\'_JEXEC\') or die; ?>' . $dataheaderlist .'<?php foreach ($this->items as $teacher){ ?>'. $dataitemlist . '<?php } ?>';
				$filename = 'default_teacherstemplate'.$filenumber;
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/teachers/tmpl' . DIRECTORY_SEPARATOR . $filename.'.php';
				$return = JFile::write($file, $filecontent);
				$profile = new stdClass();
				$profile->published = 1;
				$profile->type = 3;
				$profile->filename = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id = '';
				$result = $db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('teacherstemplate', $filename);
			}
			if ($registry->get('useexpert_teacherdetail') > 0)
			{
				$dataitemlist = $registry->get('teacher_detailtemplate');
				$dataitemlist  = str_replace('{{title}}', '{{teachertitle}}', $dataitemlist);
				$dataitemlist  = str_replace('{{teacher}}', '{{teachername}}', $dataitemlist);
				$dataitemlist = $this->itemreplace($dataitemlist);
				$filecontent = '<?php defined(\'_JEXEC\') or die; ?>' . $dataitemlist;
				$filename = 'default_teachertemplate'.$filenumber;
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/teacher/tmpl' . DIRECTORY_SEPARATOR . $filename.'.php';
				$return = JFile::write($file, $filecontent);
				$profile = new stdClass();
				$profile->published = 1;
				$profile->type = 4;
				$profile->filename = $filename;
				$profile->templatecode = $filecontent;
				$profile->asset_id = '';
				$result = $db->insertObject('#__bsms_templatecode', $profile);
				$registry->set('teachertemplate', $filename);
			}
			$d->params = $registry->toString();
			$db->updateObject('#__bsms_templates', $d, 'id');
			$filenumber ++;
		}

		return true;
	}

	private function itemreplace($item)
	{
		$item  = str_replace('{{teacher}}', '<?php echo $study->teachername; ?>', $item);
		$item  = str_replace('{{teachertitle}}', '<?php echo $this->item->title; ?>', $item);
		$item  = str_replace('{{teachername}}', '<?php echo $this->item->teachername; ?>', $item);
		$item  = str_replace('{{teachertitlelist}}', '<?php echo $teacher->title; ?>', $item);
		$item  = str_replace('{{teachernamelist}}', '<?php echo $teacher->teachername; ?>', $item);
		$item  = str_replace('{{title}}', '<?php echo $study->studytitle; ?>', $item);
		$item  = str_replace('{{date}}', '<?php echo $study->studydate; ?>', $item);
		$item  = str_replace('{{studyintro}}', '<?php echo $study->studyintro; ?>', $item);
		$item  = str_replace('{{scripture}}', '<?php echo $study->scripture1; ?>', $item);
		$item  = str_replace('{{topics}}', '<?php echo $study->topics; ?>', $item);
		$item  = str_replace('{{scripture}}', '<?php echo $study->scripture1; ?>', $item);
		$item  = str_replace('{{url}}', '<?php echo $study->detailslink; ?>', $item);
		$item  = str_replace('{{mediatime}}', '<?php echo $study->duration; ?>', $item);
		$item  = str_replace('{{thumbnail}}', '<?php echo $study->study_thumbnail; ?>', $item);
		$item  = str_replace('{{seriestext}}', '', $item);
		$item  = str_replace('{{bookname}}', '<?php echo $study->scripture1; ?>', $item);
		$item  = str_replace('{{hits}}', '<?php echo $study->hits;', $item);
		$item  = str_replace('{{location}}', '<?php echo $study->location_text; ?>', $item);
		$item  = str_replace('{{plays}}', '<?php echo $study->totaplays; ?>', $item);
		$item  = str_replace('{{downloads}}', '<?php echo $study->totaldownloads; ?>', $item);
		$item  = str_replace('{{media}}', '<?php echo $study->media; ?>', $item);
		$item  = str_replace('{{messagetype}}', '<?php echo $study->messagetypes; ?>', $item);
		$item  = str_replace('{{studytext}}', '<?php echo $this->item->studytext; ?>', $item);
		$item  = str_replace('{{scipturelink}}', '<?php  echo $this->passage; ?>', $item);
		$item  = str_replace('{{share}}', '<?php echo $this->page->social; ?>', $item);
		$item  = str_replace('{{printview}}', '<?php echo $this->page->print; ?>', $item);
		$item  = str_replace('{{pdfview}}', '', $item);
		$item  = str_replace('{{phone}}', '<?php echo $this->item->phone; ?>', $item);
		$item  = str_replace('{{teacherphonelist}}', '<?php echo $teacher->phone; ?>', $item);
		$item  = str_replace('{{website}}', '<?php echo $this->item->website; ?>', $item);
		$item  = str_replace('{{teacherwebsitelist}}', '<?php echo $teacher->website; ?>', $item);
		$item  = str_replace('{{information}}', '<?php echo $this->item->information; ?>', $item);
		$item  = str_replace('{{teacherinformationlist}}', '<?php echo $teacher->information; ?>', $item);
		$item  = str_replace('{{image}}', '<?php echo $this->item->largeimage; ?>', $item);
		$item  = str_replace('{{teacherimagelist}}', '<?php echo $teacher->largeimage; ?>', $item);
		$item  = str_replace('{{thumbnail}}', '<?php echo $this->item->image; ?>', $item);
		$item  = str_replace('{{teacherthumbnaillist}}', '<?php echo $teacher->image; ?>', $item);
		$item  = str_replace('{{short}}', '<?php echo $this->item->short; ?>', $item);
		$item  = str_replace('{{teachershortlist}}', '<?php echo $teacher->short; ?>', $item);

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

		$path = array(
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

			foreach ($path as $file)
			{
				if (JFile::exists($file))
				{
					JFile::delete($file);
				}
			}

		$folders = array(
			BIBLESTUDY_PATH_ADMIN . '/views/styles',
			BIBLESTUDY_PATH_ADMIN . '/views/style',
			BIBLESTUDY_PATH_ADMIN . '/views/shares',
			BIBLESTUDY_PATH_ADMIN . '/views/share',
			BIBLESTUDY_PATH_ADMIN . '/views/migration');

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
