<?php
/**
 * View html
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

JLoader::register('jbStats', BIBLESTUDY_PATH_ADMIN_LIB . '/biblestudy.stats.class.php');

/**
 * View class for Admin
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewAdmin extends JViewLegacy
{

	/**
	 * Form
	 *
	 * @var array
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var array
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var array
	 */
	protected $state;

	public $version;

	public $canDo;

	public $changeSet;

	public $errors;

	public $results;

	public $schemaVersion;

	public $updateVersion;

	public $filterParams;

	public $pagination;

	public $errorCount;

	public $jversion;

	public $tmp_dest;

	public $playerstats;

	public $assets;

	public $popups;

	public $ss;

	public $lists;

	public $pi;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->loadHelper('dbhelper');
		$language = JFactory::getLanguage();
		$language->load('com_installer');

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id);

		// Get data from the model for database
		$this->changeSet     = $this->get('Items');
		$this->errors        = $this->changeSet->check();
		$this->results       = $this->changeSet->getStatus();
		$this->schemaVersion = $this->get('SchemaVersion');
		$this->updateVersion = $this->get('UpdateVersion');
		$this->filterParams  = $this->get('DefaultTextFilters');
		$this->schemaVersion = ($this->schemaVersion) ? $this->schemaVersion : JText::_('JNONE');
		$this->updateVersion = ($this->updateVersion) ? $this->updateVersion : JText::_('JNONE');
		$this->pagination    = $this->get('Pagination');
		$this->errorCount    = count($this->errors);
		$this->jversion      = $this->get('CompVersion');

		// End for database
		$this->loadHelper('params');
		$config         = JFactory::getApplication();
		$this->tmp_dest = $config->getCfg('tmp_path');

		$stats             = new jbStats;
		$this->playerstats = $stats->players();
		$this->assets      = JFactory::getApplication()->input->get('checkassets', null, 'get', 'array');
		$popups            = $stats->popups();
		$this->popups      = $popups;

		// Get the list of backup files
		jimport('joomla.filesystem.folder');
		$path = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'database';

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

				$types[]                      = JHTML::_('select.option', '0', JTEXT::_('JBS_CMN_SELECT_DB'));
				$types                        = array_merge($types, $filelist);
				$this->lists['backedupfiles'] = JHTML::_('select.genericlist', $types, 'backuprestore', 'class="inputbox" size="1" ', 'value', 'text', '');
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
		return parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return null
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolBarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		JToolBarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolBarHelper::divider();
		JToolBarHelper::save('admin.save');
		JToolBarHelper::apply('admin.apply');
		JToolBarHelper::cancel('admin.cancel', 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();
		JToolBarHelper::custom('admin.resetHits', 'reset.png', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false, false);
		JToolBarHelper::custom('admin.resetDownloads', 'download.png', 'Reset All Download Hits', 'JBS_ADM_RESET_ALL_DOWNLOAD_HITS', false, false);
		JToolBarHelper::custom('admin.resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false, false);
		JToolBarHelper::divider();
		JToolBarHelper::custom('admin.fix', 'refresh', 'refresh', 'JBS_ADM_DB_FIX', false, false);
		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return null
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ADMINISTRATION'));
	}

	/**
	 * Added for Sermonspeaker and preachit.
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
				else
				{
					return false;
				}
				break;

			case 'preachit':
				$data = JInstaller::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_preachit/preachit.xml');

				if ($data)
				{
					return $data['version'];
				}
				else
				{
					return false;
				}
				break;
		}
		return false;
	}

}
