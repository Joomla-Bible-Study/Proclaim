<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMDbHelper;
use CWM\Component\Proclaim\Administrator\Lib\CWMBackup;
use CWM\Component\Proclaim\Administrator\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

/**
 * Controller for Admin
 *
 * @since  7.0.0
 */
class CWMAdminController extends FormController
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
	 *
	 * @var  string
	 *
	 * @since 7.0
	 */
	protected $view_list = 'cwmcpanel';

	/**
	 * Tools to change player or popup
	 *
	 * @return void
	 *
	 * @throws  \Exception
	 * @since   7.0.0
	 */
	public function tools()
	{
		$tool = Factory::getApplication()->input->get('tooltype', '', 'post');

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
				$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
				break;
		}
	}

	/**
	 * Change media images from a digital file to css
	 *
	 * @return void
	 *
	 * @throws \JsonException
	 * @since 7.0.0
	 */
	public function mediaimages()
	{
		$post    = $_POST['jform'];
		$decoded = json_decode($post['mediaimage'], true, 512, JSON_THROW_ON_ERROR);
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles');
		$db->setQuery($query);
		$images    = $db->loadObjectList();
		$error     = 0;
		$added     = 0;
		$errortext = '';
		$msg       = Text::_('JBS_RESULTS') . ': ';

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
							$rows  = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (\RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_('JBS_RESULTS') .
					': ' . $added . ' ' . Text::_('JBS_SUCCESS');
				break;
			case 2:
				$buttontype = $decoded->media_button_type;
				$icontype   = $decoded->media_icon_type;

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
							$rows  = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (\RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_('JBS_RESULTS') .
					': ' . $added . ' ' . Text::_('JBS_SUCCESS');
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
							$rows  = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (\RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_('JBS_RESULTS') .
					': ' . $added . ' ' . Text::_('JBS_SUCCESS');
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
							$rows  = $db->getAffectedRows();
							$added = $added + $rows;
						}
						catch (\RuntimeException $e)
						{
							$errortext .= $e->getMessage() . '<br />';
							$error++;
						}
					}
				}

				$msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_('JBS_RESULTS') .
					': ' . $added . ' ' . Text::_('JBS_SUCCESS');
				break;
			default:
				$msg = Text::_('JBS_NOTHING_MATCHED');
				break;
		}

		$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$db   = Factory::getDbo();
		$msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
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
						$msg = Text::_('JBS_ADM_ERROR_OCCURED');
						$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
					}
				}
			}
		}
		else
		{
			$msg = Text::_('JBS_ADM_ERROR_OCCURED') . ': Missed setting the From or Two';
		}

		$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$db   = Factory::getDbo();
		$post = $_POST['jform'];
		$reg  = new Registry;
		$reg->loadArray($post['params']);
		$from  = $reg->get('pFrom', 'x');
		$form2 = '';
		$to    = $reg->get('pTo', 'x');
		$msg   = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
		$query = $db->getQuery(true);
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
					$msg = Text::_('JBS_ADM_ERROR_OCCURED');
					$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
				}
			}
		}

		$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$db    = Factory::getDbo();
		$msg   = null;
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('hits = ' . 0)
			->where('hits != 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = Text::_('JBS_ADM_ERROR_OCCURED');
		}
		else
		{
			$msg = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
		}

		$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$msg   = null;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('downloads = ' . 0)
			->where('downloads != 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = Text::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
		}

		$this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$msg   = null;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('plays = ' . 0)
			->where('plays != 0');
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = Text::_('JBS_CMN_ERROR_RESETTING_PLAYS');
		}
		else
		{
			$updated = $db->getAffectedRows();
			$msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
		}

		$this->setRedirect('index.php?option=com_proclaim&view=ccwmadministration&layout=edit&id=1', $msg);
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
		$this->setRedirect('index.php?option=com_proclaim&view=cwmadministratiion&layout=edit&id=1');
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
		Session::checkToken('get') || Session::checkToken() || jexit(Text::_('JINVALID_TOKEN'));

		$convert      = new CWMSSConvert;
		$ssconversion = $convert->convertSS();
		$this->setRedirect('index.php?option=com_proclaim&view=administrator&layout=edit&id=1', $ssconversion);
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
		Session::checkToken('get') || Session::checkToken() || jexit(Text::_('JINVALID_TOKEN'));

		$convert      = new CWMPIconvert;
		$piconversion = $convert->convertPI();
		$this->setRedirect('index.php?option=com_proclaim&view=administrator&layout=edit&id=1', $piconversion);
	}

	/**
	 * Tries to fix missing database updates
	 *
	 * @return void
	 *
	 * @throws  \Exception
	 * @since   7.1.0
	 */
	public function fix()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Needed for DB fixer
		JLoader::register('BiblestudyModelInstall', BIBLESTUDY_PATH_ADMIN_MODELS . '/InstallController.php');

		/** @var AdminModel $model */
		$model = $this->getModel('administrator');
		$model->fix();
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=database', false));
	}

	/**
	 * Reset Db to install
	 *
	 * @return void
	 *
	 * @throws  \Exception
	 * @since   7.1.0
	 */
	public function dbReset()
	{
		$user = Factory::getUser();

		if (in_array('8', $user->groups, true))
		{
			CWMDbHelper::resetdb();
			$this->setRedirect(Route::_('index.php?option=com_proclaim&view=assats&task=assets.browse&' . Session::getFormToken() . '=1', false));
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'eroor');
			$this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmcpanel', false));
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
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$update = CWMAlias::updateAlias();
		$this->setMessage(Text::_('JBS_ADM_ALIAS_ROWS') . $update);
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=administrator&layout=edit&id=1', false));
	}

	/**
	 * Do the import
	 *
	 * @param   boolean  $parent  Source of info
	 *
	 * @return void
	 *
	 * @throws  \Exception
	 * @since   7.0.0
	 */
	public function doimport($parent = true)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$copysuccess = false;
		$result      = null;
		$alt         = '';

		// This should be where the form administrator/form_migrate comes to with either the file select box or the tmp folder input field
		$app   = Factory::getApplication();
		$input = new Joomla\Input\Input;
		$input->set('view', $input->get('view', 'administrator', 'cmd'));

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
				$app->enqueueMessage(Text::_('JBS_CMN_DATABASE_NOT_COPIED'), 'worning');
				$copysuccess = false;
			}
		}
		else
		{
			$import = new CWMRestore;
			$result = $import->importdb($parent);
			$alt    = '&jbsmalt=1';
		}

		if ($result || $copysuccess)
		{
			$this->setRedirect('index.php?option=com_proclaim&view=install&scanstate=start&jbsimport=1' . $alt);
		}
		else
		{
			$this->setRedirect('index.php?option=com_proclaim&view=migrate');
		}
	}

	/**
	 * Copy Old Tables to new Joomla! Tables
	 *
	 * @param   string  $oldprefix  Old table Prefix
	 *
	 * @return boolean
	 *
	 * @throws  \Exception
	 * @since   7.0.0
	 */
	public function copyTables($oldprefix)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Create table tablename_new like tablename; -> this will copy the structure...
		// Insert into tablename_new select * from tablename; -> this would copy all the data
		$db     = Factory::getDbo();
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

				if (!CWMDbHelper::performDB($query))
				{
					return false;
				}

				$query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;

				if (!CWMDbHelper::performDB($query))
				{
					return false;
				}

				$query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;

				if (!CWMDbHelper::performDB($query))
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
	 * @throws  \Exception
	 * @since   7.1.0
	 */
	public function import()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$application = Factory::getApplication();
		$import      = new CWMRestore;
		$parent      = false;
		$result      = $import->importdb($parent);

		if ($result === true)
		{
			$application->enqueueMessage('' . Text::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
		}
		elseif ($result === false)
		{
			// Do nothing
		}
		else
		{
			$application->enqueueMessage('' . $result . '');
		}

		$this->setRedirect('index.php?option=com_proclaim&view=backup');
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
		Session::checkToken('get') || Session::checkToken() || jexit(Text::_('JINVALID_TOKEN'));

		$input  = new Joomla\Input\Input;
		$run    = (int) $input->get('run', '', 'int');
		$export = new CWMBackup;

		if (!$result = $export->exportdb($run))
		{
			$msg = Text::_('JBS_CMN_OPERATION_FAILED');
			$this->setRedirect('index.php?option=com_proclaim&view=backup', $msg);
		}
		elseif ($run === 2)
		{
			if (!$result)
			{
				$msg = $result;
			}
			else
			{
				$msg = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
			}

			$this->setRedirect('index.php?option=com_proclaim&view=backup', $msg);
		}
	}

	/**
	 * Get Thumbnail List XHR
	 *
	 * @return void
	 *
	 * @throws \Exception
	 *
	 * @since 9.0.0
	 */
	public function getThumbnailListXHR()
	{
		$document     = Factory::getDocument();
		$input        = Factory::getApplication()->input;
		$images_paths = array();

		$document->setMimeEncoding('application/json');

		$image_types = $input->get('images', null, 'array');
		$count       = 0;

		foreach ($image_types as $image_type)
		{
			$images = Folder::files(JPATH_ROOT . '/images/biblestudy/' . $image_type, 'original_', true, true);

			if ($images != false)
			{
				$count += count($images);
			}

			$images_paths[] = array(array('type' => $image_type, 'images' => $images));
		}

		echo json_encode(array('total' => $count, 'paths' => $images_paths), JSON_THROW_ON_ERROR);

		Factory::getApplication()->close();
	}

	/**
	 * Create Thumbnail XHR
	 *
	 * @return void
	 *
	 * @throws \Exception
	 *
	 * @since 9.0.0
	 */
	public function createThumbnailXHR()
	{
		$app      = Factory::getApplication();
		$document = $app->getDocument();
		$input    = Factory::getApplication()->input;

		$document->setMimeEncoding('application/json');

		$image_path = $input->get('image_path', null, 'string');
		$new_size   = $input->get('new_size', null, 'integer');

		CWMThumbnail::resize($image_path, $new_size);

		Factory::getApplication()->close();
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		/** @var ArchiveModel $model */
		$model = $this->getModel('archive');
		$msg   = $model->doArchive();
		$this->setRedirect('index.php?option=com_proclaim&view=cpanel', $msg);
	}

	public function submit($key = null, $urlVar = null)
	{
		$this->checkToken();

		$app   = Factory::getApplication();
		$model = $this->getModel('form');
		$form  = $model->getForm('', false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Name of array 'jform' must match 'control' => 'jform' line in the model code
		$data = $this->input->post->get('jform', array(), 'array');

		// This is validate() from the FormModel class, not the Form class
		// FormModel::validate() calls both Form::filter() and Form::validate() methods
		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors = $model->getErrors();

			foreach ($errors as $error)
			{
				if ($error instanceof \Exception)
				{
					$app->enqueueMessage($error->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($error, 'warning');
				}
			}

			// Save the form data in the session, using a unique identifier
			$app->setUserState('com_proclaim.cwmadmin', $data);
		}
		else
		{
			$app->enqueueMessage("Data successfully validated", 'notice');

			// Clear the form data in the session
			$app->setUserState('com_proclaim.cwmadmin', null);
		}

		// Redirect back to the form in all cases
		$this->setRedirect(Route::_('index.php?option=com_biblestudy&view=administrator&layout=edit', false));
	}
}
