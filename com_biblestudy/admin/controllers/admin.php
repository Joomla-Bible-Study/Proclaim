<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller for Admin
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
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
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

	}

	/**
	 * Tools to change player or popup
	 *
	 * @return void
	 */
	public function tools()
	{
		$tool = JFactory::getApplication()->input->get('tooltype', '', 'post');

		switch ($tool)
		{
			case 'players':
				$this->changePlayers();
				break;

			case 'popups':
				$this->changePopup();
				break;
		}
	}

	/**
	 * Change Player Modes
	 *
	 * @return void
	 */
	public function changePlayers()
	{
		$db   = JFactory::getDBO();
		$msg  = JText::_('JBS_ADM_ERROR_OCCURED');
		$post = $_POST['jform'];
		$reg  = new JRegistry;
		$reg->loadArray($post['params']);
		$from = $reg->get('from');
		$to   = $reg->get('to');
		if ($from != 'x' && $to != 'x')
		{
			switch ($from)
			{
				case '100':
					$query = $db->getQuery(true);
					$query->update('#__bsms_mediafiles')
						->set('player = ' . $db->quote($to))
						->where('player IS NULL');
					break;

				default:
					$query = $db->getQuery(true);
					$query->update('#__bsms_mediafiles')
						->set('player = ' . $db->quote($to))
						->where('player = ' . $db->quote($from));
			}
			$db->setQuery($query);

			if ($db->execute())
			{
				$msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
			}
		}
		else
		{
			$msg .= ': Missed setting the From or Two';
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
	}

	/**
	 * Change Media Popup
	 *
	 * @return void
	 */
	public function changePopup()
	{
		$jinput = JFactory::getApplication()->input;
		$db     = JFactory::getDBO();
		$msg    = null;
		$from   = $jinput->getInt('pfrom', '', 'post');
		$to     = $jinput->getInt('pto', '', 'post');
		$query  = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('popup = ' . $db->q($to))
			->where('popup = ' . $db->q($from));
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
	 * Reset Hits
	 *
	 * @return void
	 */
	public function resetHits()
	{
		$db   = JFactory::getDBO();
		$msg  = null;
		$post = $_POST['jform'];
		$reg  = new JRegistry;
		$reg->loadArray($post['params']);
		$from  = $reg->get(';from');
		$to    = $reg->get(';to');
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('popup = ' . $db->q($to))
			->where('popup = ' . $db->q($from));
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
	 */
	public function resetDownloads()
	{
		$msg   = null;
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('downloads = ' . 0);
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
	 * @return null
	 */
	public function resetPlays()
	{
		$msg   = null;
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('plays = ' . 0);
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
	 */
	public function back()
	{
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1');
	}

	/**
	 * Check Assets
	 *
	 * @return void
	 */
	public function checkassets()
	{
		$asset       = new JBSMAssets;
		$checkassets = $asset->checkAssets();
		JFactory::getApplication()->input->set('checkassets', $checkassets, 'get', JREQUEST_ALLOWRAW);
		parent::display();
	}

	/**
	 * Convert SermonSpeaker to BibleStudy
	 *
	 * @return void
	 */
	public function convertSermonSpeaker()
	{
		$convert      = new JBSMSSConvert;
		$ssconversion = $convert->convertSS();
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $ssconversion);
	}

	/**
	 * Convert PreachIt to BibleStudy
	 *
	 * @return void
	 */
	public function convertPreachIt()
	{
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
	 */
	public function fix()
	{
		$model = $this->getModel('admin');
		$model->fix();
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=database', false));
	}

	/**
	 * Reset Db to install
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	public function dbReset()
	{
		$user = JFactory::getUser();

		if (in_array('8', $user->groups))
		{
			JBSMDbHelper::resetdb();
			self::fixAssets();
			$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=cpanel', false));
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'message');
			$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=cpanel', false));
		}

	}

	/**
	 * Fix Assets
	 *
	 * @param   bool  $dbReset  To check if this is coming from dbReset
	 *
	 * @return void
	 */
	public function fixAssets($dbReset = false)
	{
		$asset = new JBSMAssets;
		$asset->fixAssets();
		if (!$dbReset)
		{
			$this->setRedirect('index.php?option=com_biblestudy&view=assets&task=admin.checkassets');
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
	 */
	public function doimport($parent = true)
	{
		$copysuccess = false;
		$result      = null;

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
		}
		if ($result || $copysuccess)
		{
			$this->setRedirect('index.php?option=com_biblestudy&view=migration&task=migration.browse&jbsimport=1');
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
	 */
	public function copyTables($oldprefix)
	{
		// Create table tablename_new like tablename; -> this will copy the structure...
		// Insert into tablename_new select * from tablename; -> this would copy all the data
		$db     = JFactory::getDBO();
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

				if (!JBSMDbHelper::performdb($query))
				{
					return false;
				}
				$query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;

				if (!JBSMDbHelper::performdb($query))
				{
					return false;
				}
				$query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;

				if (!JBSMDbHelper::performdb($query))
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
	 * @since 7.1.0
	 */
	public function import()
	{
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
	 */
	public function export()
	{
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
	 */
	public function getThumbnailListXHR()
	{
		JSession::checkToken('get') or die('Invalid Token');
		$document = JFactory::getDocument();
		$input    = JFactory::getApplication()->input;

		$document->setMimeEncoding('application/json');

		$image_types = $input->get('images', null, 'array');
		$count       = 0;
		foreach ($image_types as $image_type)
		{
			$images = JFolder::files(JPATH_ROOT . '/' . 'images/BibleStudy/' . $image_type, 'original_', true, true);
			$count += count($images);

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
	 */
	public function createThumbnailXHR()
	{
		JSession::checkToken('get') or die('Invalid Token');
		$document = JFactory::getDocument();
		$input    = JFactory::getApplication()->input;

		$document->setMimeEncoding('application/json');

		$image_path = $input->get('image_path', null, 'string');
		$new_size   = $input->get('new_size', null, 'integer');

		JBSMThumbnail::resize($image_path, $new_size);

		JFactory::getApplication()->close();
	}
}
