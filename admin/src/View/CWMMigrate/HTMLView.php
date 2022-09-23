<?php
/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMigrate;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use CWM\Component\Proclaim\Administrator\Model\CWMAdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Migrate
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $version;

	/**
	 * Can Do
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $canDo;

	/**
	 * Change Set
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $changeSet;

	/**
	 * Errors
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $errors;

	/**
	 * Results
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $results;

	/**
	 * Schema Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 * @since 9.0.0
	 */
	public string $updateVersion;

	/**
	 * Filter Params
	 *
	 * @var Registry
	 * @since    7.0.0
	 */
	public Registry $filterParams;

	/**
	 * Pagination
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $pagination;

	/**
	 * Error Count
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $errorCount;

	/**
	 * Joomla BibleStudy Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $jversion;

	/**
	 * Temp Destination
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $tmp_dest;

	/**
	 * Player Stats
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $playerstats;

	/**
	 * Assets
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $assets;

	/**
	 * Popups
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $popups;

	/**
	 * SS
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $ss;

	/**
	 * Lists
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $lists;

	/**
	 * PI
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $pi;

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
	protected array $item;

	/**
	 * State
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected array $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws  \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$model = new CWMAdminModel;
		$this->setModel($model, true);

		$language = Factory::getApplication()->getLanguage();
		$language->load('com_installer');

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = CWMProclaimHelper::getActions($this->item->id);

		$config         = Factory::getApplication();
		$this->tmp_dest = $config->get('tmp_path');

		// Get the list of backup files
		$path = JPATH_SITE . '/media/com_proclaim/backup';

		if (Folder::exists($path))
		{
			if (!$files = Folder::files($path, '.sql'))
			{
				$this->lists['backedupfiles'] = Text::_('JBS_CMN_NO_FILES_TO_DISPLAY');
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

				$types[]                      = HTMLHelper::_('select.option', '0', Text::_('JBS_IBM_SELECT_DB'));
				$types                        = array_merge($types, $filelist);
				$this->lists['backedupfiles'] = HTMLHelper::_('select.genericlist', $types, 'backuprestore', 'class="inputbox" size="1" ', 'value', 'text', '');
			}
		}
		else
		{
			$this->lists['backedupfiles'] = Text::_('JBS_CMN_NO_FILES_TO_DISPLAY');
		}

		// Check for SermonSpeaker and PreachIt
		$extensions = $this->get('SSorPI');

		foreach ($extensions as $extension)
		{
			if ($extension->element == 'com_sermonspeaker')
			{
				$this->ss = '<a href="index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1&task=cwmadmin.convertSermonSpeaker">'
					. Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER') . '</a>';
			}
			else
			{
				$this->ss = Text::_('JBS_IBM_NO_SERMON_SPEAKER_FOUND');
			}

			if ($extension->element == 'com_preachit')
			{
				$this->pi = '<a href="index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1&task=cwmadmin.convertPreachIt">'
					. Text::_('JBS_IBM_CONVERT_PREACH_IT') . '</a>';
			}
			else
			{
				$this->pi = Text::_('JBS_IBM_NO_PREACHIT_FOUND');
			}
		}

		$jbsversion    = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_proclaim/biblestudy.xml');
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
	 * @throws \Exception
	 * @since  7.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');
		ToolbarHelper::preferences('com_proclaim', '600', '800', 'JBS_ADM_PERMISSIONS');
		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_ADMINISTRATION'));
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
				$data = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_sermonspeaker/sermonspeaker.xml');

				if ($data)
				{
					return $data['version'];
				}

				return false;
				break;

			case 'preachit':
				$data = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_preachit/preachit.xml');

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
