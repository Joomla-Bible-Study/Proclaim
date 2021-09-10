<?php
/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewAdmin extends JViewLegacy
{
	/**
	 * Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $version;

	/**
	 * Can Do
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $canDo;

	/**
	 * Change Set
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $changeSet;

	/**
	 * Errors
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $errors;

	/**
	 * Results
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $results;

	/**
	 * Schema Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $updateVersion;

	/**
	 * Filter Params
	 *
	 * @var Registry
	 * @since    7.0.0
	 */
	public $filterParams;

	/**
	 * Pagination
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $pagination;

	/**
	 * Error Count
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $errorCount;

	/**
	 * Joomla BibleStudy Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $jversion;

	/**
	 * Temp Destination
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $tmp_dest;

	/**
	 * Player Stats
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $playerstats;

	/**
	 * Assets
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $assets;

	/**
	 * Popups
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $popups;

	/**
	 * SS
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $ss;

	/**
	 * Lists
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $lists;

	/**
	 * PI
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $pi;

	/**
	 * Form
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @throws  Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$language = JFactory::getLanguage();
		$language->load('com_installer');

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id);

		// End for database
		$config         = JFactory::getApplication();
		$this->tmp_dest = $config->get('tmp_path');

		$stats             = new JBSMStats;
		$this->playerstats = $stats->players();
		$this->assets      = JFactory::getApplication()->input->get('checkassets', null, 'get');
		$popups            = $stats->popups();
		$this->popups      = $popups;

		// Get the list of backup files
		jimport('joomla.filesystem.folder');
		$path = JPATH_SITE . '/media/com_biblestudy/backup';

		if (JFolder::exists($path))
		{
			if (!$files = JFolder::files($path, '.sql'))
			{
				$this->lists['backedupfiles'] = JText::_('JBS_CMN_NO_FILES_TO_DISPLAY');
			}
			else
			{
				asort($files, SORT_STRING);
				$filelist = array();

				foreach ($files as $value)
				{
					$filelisttemp = array('value' => $value, 'text' => $value);
					$filelist[]   = $filelisttemp;
				}

				$types[]                      = JHtml::_('select.option', '0', JText::_('JBS_IBM_SELECT_DB'));
				$types                        = array_merge($types, $filelist);
				$this->lists['backedupfiles'] = JHtml::_('select.genericlist', $types, 'backuprestore',
					'class="inputbox" size="1" ', 'value', 'text', ''
				);
			}
		}
		else
		{
			$this->lists['backedupfiles'] = JText::_('JBS_CMN_NO_FILES_TO_DISPLAY');
		}

		// Check for SermonSpeaker and PreachIt
		$extensions = $this->get('SSorPI');

		foreach ($extensions as $extension)
		{
			if ($extension->element == 'com_sermonspeaker')
			{
				$this->ss = '<a href="index.php?option=com_biblestudy&view=admin&layout=edit&id=1&task=admin.convertSermonSpeaker">'
					. JText::_('JBS_IBM_CONVERT_SERMON_SPEAKER') . '</a>';
			}
			else
			{
				$this->ss = JText::_('JBS_IBM_NO_SERMON_SPEAKER_FOUND');
			}

			if ($extension->element == 'com_preachit')
			{
				$this->pi = '<a href="index.php?option=com_biblestudy&view=admin&layout=edit&id=1&task=admin.convertPreachIt">'
					. JText::_('JBS_IBM_CONVERT_PREACH_IT') . '</a>';
			}
			else
			{
				$this->pi = JText::_('JBS_IBM_NO_PREACHIT_FOUND');
			}
		}

		$jbsversion    = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.xml');
		$this->version = $jbsversion['version'];

		if (!(strncmp($this->schemaVersion, $this->version, 5) === 0))
		{
			$this->errorCount++;
		}

		if (!$this->filterParams)
		{
			$this->errorCount++;
		}

		if (($this->updateVersion != $this->version))
		{
			$this->errorCount++;
		}

		$this->setLayout('edit');

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		ToolbarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'options options');
		ToolbarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		ToolbarHelper::divider();
		ToolbarHelper::apply('admin.apply');
		ToolbarHelper::save('admin.save');
		ToolbarHelper::cancel('admin.cancel', 'JTOOLBAR_CLOSE');
		ToolbarHelper::divider();
		ToolbarHelper::custom('admin.resetHits', 'reset.png', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false);
		ToolbarHelper::custom('admin.resetDownloads', 'download.png', 'Reset All Download Hits', 'JBS_ADM_RESET_ALL_DOWNLOAD_HITS', false);
		ToolbarHelper::custom('admin.resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false);
		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ADMINISTRATION'));
	}

	/**
	 * Added for SermonSpeaker and PreachIt.
	 *
	 * @param   string  $component  Component it is coming from
	 *
	 * @return boolean
	 *
	 * @since 7.1.0
	 */
	protected function versionXML($component)
	{
		switch ($component)
		{
			case 'sermonspeaker':
				$data = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_sermonspeaker/sermonspeaker.xml');

				if ($data)
				{
					return $data['version'];
				}

				return false;
				break;

			case 'preachit':
				$data = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_preachit/preachit.xml');

				if ($data)
				{
					return $data['version'];
				}

				return false;
				break;
		}

		return false;
	}
}
