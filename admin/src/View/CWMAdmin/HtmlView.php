<?php
/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// Check to ensure this file is included in Joomla!

namespace CWM\Component\Proclaim\Administrator\View\CWMAdmin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use CWM\Component\Proclaim\Administrator\Lib\CWMStats;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
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
	public string $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public string $updateVersion;

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
	 * @var \Joomla\CMS\Form\Form
	 * @since    7.0.0
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var CMSObject
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var CMSObject
	 * @since    7.0.0
	 */
	protected $state;

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
	public function display($tpl = null): void
	{
		$app = Factory::getApplication();
		$language = $app->getLanguage();
		$language->load('com_installer');

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = ContentHelper::getActions('com_proclaim', 'cwmadmin', (int) $this->item->id);

		// End for database
		$this->tmp_dest = $app->get('tmp_path');

		$this->playerstats = CWMStats::players();
		$this->assets      = $app->input->get('checkassets', null, 'get');
		$popups            = CWMStats::popups();
		$this->popups      = $popups;

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

				$types[]                      = JHtml::_('select.option', '0', Text::_('JBS_IBM_SELECT_DB'));
				$types                        = array_merge($types, $filelist);
				$this->lists['backedupfiles'] = JHTML::_('select.genericlist', $types, 'backuprestore',
					'class="inputbox" size="1" ', 'value', 'text', ''
				);
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
			if ($extension->element === 'com_sermonspeaker')
			{
				$this->ss = '<a href="index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1&task=cwmadmin.convertSermonSpeaker">'
					. Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER') . '</a>';
			}
			else
			{
				$this->ss = Text::_('JBS_IBM_NO_SERMON_SPEAKER_FOUND');
			}

			if ($extension->element === 'com_preachit')
			{
				$this->pi = '<a href="index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1&task=cwmadmin.convertPreachIt">'
					. Text::_('JBS_IBM_CONVERT_PREACH_IT') . '</a>';
			}
			else
			{
				$this->pi = Text::_('JBS_IBM_NO_PREACHIT_FOUND');
			}
		}

		$jbsversion    = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml');

		foreach ($jbsversion as $key => $value)
		{
			if ($key == 'version')
			{
				$version = $value;
			}
		}

		$this->version = $version;

		$this->setLayout('edit');

		// Set the toolbar
		$this->addToolbar();

		$this->setDocumentTitle(Text::_('JBS_TITLE_ADMINISTRATION'));

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user       = $this->getCurrentUser();
		$userId     = $user->id;

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'options');
		$toolbar->preferences('com_proclaim','JBS_ADM_PERMISSIONS');
		ToolbarHelper::divider();
		$toolbar->apply('cwmadmin.apply');
		$toolbar->save('cwmadmin.save');
		$toolbar->cancel('cwmadmin.cancel');
		ToolbarHelper::divider();
		ToolbarHelper::custom('cwmadmin.resetHits', 'hits', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false);
		ToolbarHelper::custom('cwmadmin.resetDownloads', 'download.png', 'Reset All Download Hits', 'JBS_ADM_RESET_ALL_DOWNLOAD_HITS', false);
		ToolbarHelper::custom('cwmadmin.resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false);

		$toolbar->divider();

		ToolbarHelper::inlinehelp();

		$toolbar->help('Proclaim', true);
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
