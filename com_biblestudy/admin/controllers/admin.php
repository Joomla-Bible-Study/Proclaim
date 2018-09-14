<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;

use \Joomla\Registry\Registry;

jimport('joomla.filesystem.folder');

/**
 * Controller for Admin
 *
 * @since  7.0.0
 */
class BiblestudyControllerAdmin extends JControllerForm
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
	 *
	 * @param  string
	 *
	 * @since 7.0
	 */
	protected $view_list = 'cpanel';

	/**
	 * Tools to change player or popup
	 *
	 * @return void
	 *
	 * @since   7.0.0
	 * @throws  \Exception
	 */
	public function tools()
	{
		$tool = JFactory::getApplication()->input->get('tooltype', '', 'post');

		/** @var BiblestudyModelAdmin $model */
		$model = $this->getModel();

		switch ($tool)
		{
			case 'players':
				$this->changePlayers();
				break;

			case 'popups':
				$this->changePopup();
				break;

			case 'playerbymediatype':
				$msg = $model->playerByMediaType();
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				break;
		}
	}

	/**
	 * Change media images from a digital file to css
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function mediaimages()
	{
		$post = $_POST['jform'];
		$decoded = json_decode($post['mediaimage']);
		$db   = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles');
		$db->setQuery($query);
		$images = $db->loadObjectList();
		$error = 0;
		$added = 0;
		$errortext = '';
		$msg = JText::_('JBS_RESULTS') . ': ';

		switch ($decoded->media_use_button_icon)
		{
			case 1:
				// Button only
				$buttontype = $decoded->media_button_type;
				$buttontext = $decoded->media_button_text;

				if (!isset($post['media_icon_type']))
				{
					$post['media_icon_type'] = 0;
				}

				foreach ($images as $media)
				{
					$reg = new Registry;
					$reg->loadString($media->params);

					if ($reg->get('media_button_type') == $buttontype && $reg->get('media_button_text') == $buttontext)
					{
						$query = $db->getQuery(true);
						$reg->set('media_button_color', $post['media_button_color']);
						$reg->set('media_button_text', $post['media_button_text']);
						$reg->set('media_button_type', $post['media_button_type']);
						$reg->set('media_custom_icon', $post['media_custom_icon']);
						$reg->set('media_icon_text_size', $post['media_icon_text_size']);
						$reg->set('media_icon_type', $post['media_icon_type']);
						$reg->set('media_image', $post['media_image']);
						$reg->set('media_use_button_icon', $post['media_use_button_icon']);
						$db->setQuery($query);
						$query->update('#__bsms_mediafiles')
							->set('params = ' . $db->q($reg->toString()))
							->where('id = ' . (int) $media->id);

						try
						{
							$db->setQuery($query);
							$query->update('#__bsms_mediafiles')
								->set('params = ' . $db->q($reg->toString()))
								->where('id = ' . (int) $media->id);
							$db->execute();
							$rows = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= JText::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . JText::_('JBS_RESULTS') .
						': ' . $added . ' ' . JText::_('JBS_SUCCESS');
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				break;
			case 2:
				$buttontype = $decoded->media_button_type;
				$icontype = $decoded->media_icon_type;

				foreach ($images as $media)
				{
					$reg = new Registry;
					$reg->loadString($media->params);

					if ($reg->get('media_button_type') == $buttontype && $reg->get('media_icon_type') == $icontype)
					{
						$query = $db->getQuery(true);
						$reg->set('media_button_color', $post['media_button_color']);
						$reg->set('media_button_text', $post['media_button_text']);
						$reg->set('media_button_type', $post['media_button_type']);
						$reg->set('media_custom_icon', $post['media_custom_icon']);
						$reg->set('media_icon_text_size', $post['media_icon_text_size']);
						$reg->set('media_icon_type', $post['media_icon_type']);
						$reg->set('media_image', $post['media_image']);
						$reg->set('media_use_button_icon', $post['media_use_button_icon']);
						$db->setQuery($query);
						$query->update('#__bsms_mediafiles')
							->set('params = ' . $db->q($reg->toString()))
							->where('id = ' . (int) $media->id);

						try
						{
							$db->setQuery($query);
							$query->update('#__bsms_mediafiles')
								->set('params = ' . $db->q($reg->toString()))
								->where('id = ' . (int) $media->id);
							$db->execute();
							$rows = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= JText::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . JText::_('JBS_RESULTS') .
						': ' . $added . ' ' . JText::_('JBS_SUCCESS');
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				break;
			case 3:
				// Icon only
				$icontype = $decoded->media_icon_type;

				if (!isset($post['media_button_type']))
				{
					$post['media_button_type'] = 0;
				}

				foreach ($images as $media)
				{
					$reg = new Registry;
					$reg->loadString($media->params);

					if ($reg->get('media_icon_type') == $icontype)
					{
						$query = $db->getQuery(true);
						$reg->set('media_button_color', $post['media_button_color']);
						$reg->set('media_button_text', $post['media_button_text']);
						$reg->set('media_button_type', $post['media_button_type']);
						$reg->set('media_custom_icon', $post['media_custom_icon']);
						$reg->set('media_icon_text_size', $post['media_icon_text_size']);
						$reg->set('media_icon_type', $post['media_icon_type']);
						$reg->set('media_image', $post['media_image']);
						$reg->set('media_use_button_icon', $post['media_use_button_icon']);
						$query->update('#__bsms_mediafiles')
							->set('params = ' . $db->q($reg->toString()))
							->where('id = ' . (int) $media->id);
						$db->setQuery($query);

						try
						{
							$db->setQuery($query);
							$query->update('#__bsms_mediafiles')
								->set('params = ' . $db->q($reg->toString()))
								->where('id = ' . (int) $media->id);
							$db->execute();
							$rows = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= JText::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . JText::_('JBS_RESULTS') .
						': ' . $added . ' ' . JText::_('JBS_SUCCESS');
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				break;
			case 0:
				// It's an image
				$mediaimage = $decoded->media_image;

				if (!isset($post['media_icon_type']))
				{
					$post['media_icon_type'] = 0;
				}

				if (!isset($post['media_button_type']))
				{
					$post['media_button_type'] = 0;
				}

				foreach ($images as $media)
				{
					$reg = new Registry;
					$reg->loadString($media->params);

					if ($reg->get('media_image') == $mediaimage)
					{
						$query = $db->getQuery(true);
						$reg->set('media_button_color', $post['media_button_color']);
						$reg->set('media_button_text', $post['media_button_text']);
						$reg->set('media_button_type', $post['media_button_type']);
						$reg->set('media_custom_icon', $post['media_custom_icon']);
						$reg->set('media_icon_text_size', $post['media_icon_text_size']);
						$reg->set('media_icon_type', $post['media_icon_type']);
						$reg->set('media_image', $post['media_image']);
						$reg->set('media_use_button_icon', $post['media_use_button_icon']);

						try
						{
							$db->setQuery($query);
							$query->update('#__bsms_mediafiles')
								->set('params = ' . $db->q($reg->toString()))
								->where('id = ' . (int) $media->id);
							$db->execute();
							$rows = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= JText::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . JText::_('JBS_RESULTS') .
						': ' . $added . ' ' . JText::_('JBS_SUCCESS');
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				break;
			default:
				$msg = JText::_('JBS_NOTHING_MATCHED');
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				break;
		}
	}

	/**
	 * Change Player Modes
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function changePlayers()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db   = JFactory::getDbo();
		$msg  = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
		$post = $_POST['jform'];
		$reg  = new Registry;
		$reg->loadArray($post['params']);
		$from = $reg->get('from', 'x');
		$to   = $reg->get('to', 'x');

		if ($from != 'x' && $to != 'x')
		{
			$query = $db->getQuery(true);
			$query->select('id, params')
				->from('#__bsms_mediafiles');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $media)
			{
				$reg = new Registry;
				$reg->loadString($media->params);

				if ($reg->get('player', 0) == $from)
				{
					$reg->set('player', $to);

					$query = $db->getQuery(true);
					$query->update('#__bsms_mediafiles')
						->set('params = ' . $db->q($reg->toString()))
						->where('id = ' . (int) $media->id);
					$db->setQuery($query);

					if (!$db->execute())
					{
						$msg = JText::_('JBS_ADM_ERROR_OCCURED');
						$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
					}
				}
			}
		}
		else
		{
			$msg = JText::_('JBS_ADM_ERROR_OCCURED') . ': Missed setting the From or Two';
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Change Media Popup
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function changePopup()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db     = JFactory::getDbo();
		$msg    = null;
		$post   = $_POST['jform'];
		$reg    = new Registry;
		$reg->loadArray($post['params']);
		$from   = $reg->get('pFrom', 'x');
		$form2  = '';
		$to     = $reg->get('pTo', 'x');
		$msg    = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
		$query  = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $media)
		{
			$reg = new Registry;
			$reg->loadString($media->params);

			if ($from == '100')
			{
				$from  = '0';
				$form2 = '100';
			}
			elseif ($to == '100')
			{
				$to = '';
			}

			if ($reg->get('popup', 0) == $from || $reg->get('popup', 0) == $form2)
			{
				$reg->set('popup', $to);

				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')
					->set('params = ' . $db->q($reg->toString()))
					->where('id = ' . (int) $media->id);
				$db->setQuery($query);

				if (!$db->execute())
				{
					$msg = JText::_('JBS_ADM_ERROR_OCCURED');
					$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
				}
			}
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Reset Hits
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function resetHits()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db   = JFactory::getDbo();
		$msg  = null;
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('hits = ' . 0)
			->where('hits != 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_ADM_ERROR_OCCURED');
		}
		else
		{
			$msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Reset Downloads
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function resetDownloads()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$msg   = null;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('downloads = ' . 0)
			->where('downloads != 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Reset Players
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function resetPlays()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$msg   = null;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('plays = ' . 0)
			->where('plays != 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = JText::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . JText::_('JBS_CMN_ROWS_RESET');
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Return back to c-panel
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function back()
	{
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1');
	}

	/**
	 * Convert SermonSpeaker to BibleStudy
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function convertSermonSpeaker()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$convert      = new JBSMSSConvert;
		$ssconversion = $convert->convertSS();
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $ssconversion);
	}

	/**
	 * Convert PreachIt to BibleStudy
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function convertPreachIt()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$convert      = new JBSMPIconvert;
		$piconversion = $convert->convertPI();
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $piconversion);
	}

	/**
	 * Tries to fix missing database updates
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 * @throws \Exception
	 */
	public function fix()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Needed for DB fixer
		JLoader::register('BiblestudyModelInstall', BIBLESTUDY_PATH_ADMIN_MODELS . '/install.php');

		/** @var BiblestudyModelAdmin $model */
		$model = $this->getModel('admin');
		$model->fix();
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=database', false));
	}

	/**
	 * Reset Db to install
	 *
	 * @return void
	 *
	 * @since   7.1.0
	 * @throws  \Exception
	 */
	public function dbReset()
	{
		$user = JFactory::getUser();

		if (in_array('8', $user->groups))
		{
			JBSMDbHelper::resetdb();
			$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=assats&task=assets.browse&' . JSession::getFormToken() . '=1', false));
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'eroor');
			$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=cpanel', false));
		}
	}

	/**
	 * Alias Updates
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function aliasUpdate()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$alias  = new JBSMAlias;
		$update = $alias->updateAlias();
		$this->setMessage(JText::_('JBS_ADM_ALIAS_ROWS') . $update);
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', false));
	}

	/**
	 * Do the import
	 *
	 * @param   boolean  $parent  Source of info
	 *
	 * @return void
	 *
	 * @since   7.0.0
	 * @throws  \Exception
	 */
	public function doimport($parent = true)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$copysuccess = false;
		$result      = null;
		$alt         = '';

		// This should be where the form admin/form_migrate comes to with either the file select box or the tmp folder input field
		$app   = JFactory::getApplication();
		$input = new JInput;
		$input->set('view', $input->get('view', 'admin', 'cmd'));

		// Add commands to move tables from old prefix to new
		$oldprefix = $input->get('oldprefix', '', 'string');

		if ($oldprefix)
		{
			if ($this->copyTables($oldprefix))
			{
				$copysuccess = 1;
			}
			else
			{
				$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_COPIED'), 'worning');
				$copysuccess = false;
			}
		}
		else
		{
			$import = new JBSMRestore;
			$result = $import->importdb($parent);
			$alt    = '&jbsmalt=1';
		}

		if ($result || $copysuccess)
		{
			$this->setRedirect('index.php?option=com_biblestudy&view=install&scanstate=start&jbsimport=1' . $alt);
		}
		else
		{
			$this->setRedirect('index.php?option=com_biblestudy&view=migrate');
		}
	}

	/**
	 * Copy Old Tables to new Joomla! Tables
	 *
	 * @param   string  $oldprefix  Old table Prefix
	 *
	 * @return boolean
	 *
	 * @since   7.0.0
	 * @throws  \Exception
	 */
	public function copyTables($oldprefix)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Create table tablename_new like tablename; -> this will copy the structure...
		// Insert into tablename_new select * from tablename; -> this would copy all the data
		$db     = JFactory::getDbo();
		$tables = $db->getTableList();
		$prefix = $db->getPrefix();

		foreach ($tables as $table)
		{
			$isjbs = substr_count($table, $oldprefix . 'bsms');

			if ($isjbs)
			{
				$oldlength       = strlen($oldprefix);
				$newsubtablename = substr($table, $oldlength);
				$newtablename    = $prefix . $newsubtablename;
				$query           = 'DROP TABLE IF EXISTS ' . $newtablename;

				if (!JBSMDbHelper::performDB($query))
				{
					return false;
				}

				$query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;

				if (!JBSMDbHelper::performDB($query))
				{
					return false;
				}

				$query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;

				if (!JBSMDbHelper::performDB($query))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Import function from the backup page
	 *
	 * @return void
	 *
	 * @since   7.1.0
	 * @throws  \Exception
	 */
	public function import()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$application = JFactory::getApplication();
		$import      = new JBSMRestore;
		$parent      = false;
		$result      = $import->importdb($parent);

		if ($result === true)
		{
			$application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
		}
		elseif ($result === false)
		{
			// Do nothing
		}
		else
		{
			$application->enqueueMessage('' . $result . '');
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=backup');
	}

	/**
	 * Export Db
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function export()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$input  = new JInput;
		$run    = $input->get('run', '', 'int');
		$export = new JBSMBackup;

		if (!$result = $export->exportdb($run))
		{
			$msg = JText::_('JBS_CMN_OPERATION_FAILED');
			$this->setRedirect('index.php?option=com_biblestudy&view=backup', $msg);
		}
		elseif ($run == 2)
		{
			if (!$result)
			{
				$msg = $result;
			}
			else
			{
				$msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
			}

			$this->setRedirect('index.php?option=com_biblestudy&view=backup', $msg);
		}
	}

	/**
	 * Get Thumbnail List XHR
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public function getThumbnailListXHR()
	{
		$document     = JFactory::getDocument();
		$input        = JFactory::getApplication()->input;
		$images_paths = array();

		$document->setMimeEncoding('application/json');

		$image_types = $input->get('images', null, 'array');
		$count       = 0;

		foreach ($image_types as $image_type)
		{
			$images = JFolder::files(JPATH_ROOT . '/' . 'images/biblestudy/' . $image_type, 'original_', true, true);

			if ( $images != false)
			{
				$count += count($images);
			}

			$images_paths[] = array(array('type' => $image_type, 'images' => $images));
		}

		echo json_encode(array('total' => $count, 'paths' => $images_paths));

		JFactory::getApplication()->close();
	}

	/**
	 * Create Thumbnail XHR
	 *
	 * @throws Exception
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public function createThumbnailXHR()
	{
		$document = JFactory::getDocument();
		$input    = JFactory::getApplication()->input;

		$document->setMimeEncoding('application/json');

		$image_path = $input->get('image_path', null, 'string');
		$new_size   = $input->get('new_size', null, 'integer');

		JBSMThumbnail::resize($image_path, $new_size);

		JFactory::getApplication()->close();
	}

	/**
	 * Archive Old Message and Media
	 *
	 * @return void
	 *
	 * @since 9.0.1
	 */
	public function doArchive()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var BiblestudyModelArchive $model */
		$model = $this->getModel('archive');
		$msg = $model->doArchive();
		$this->setRedirect('index.php?option=com_biblestudy&view=cpanel', $msg);
	}
}
