<?php
/**
 * Backup html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewBackup extends JViewLegacy
{
	/** @var string CanDo function
	 *
	 * @since 9.0.0 */
	public $canDo;

	/** @var string Temp Destination
	 *
	 * @since 9.0.0 */
	public $tmp_dest;

	/** @var string Lists
	 *
	 * @since 9.0.0 */
	public $lists;

	/** @var array Form
	 *
	 * @since 9.0.0 */
	protected $form;

	/** @var array Item
	 *
	 * @since 9.0.0 */
	protected $item;

	/** @var array State
	 *
	 * @since 9.0.0 */
	protected $state;

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
		$model = JModelLegacy::getInstance('Admin', 'BiblestudyModel');
		$this->setModel($model, true);

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id);

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
				$this->lists['backedupfiles'] = JHtml::_('select.genericlist', $types, 'backuprestore', 'class="inputbox" size="1" ', 'value', 'text', '');
			}
		}
		else
		{
			$this->lists['backedupfiles'] = JText::_('JBS_CMN_NO_FILES_TO_DISPLAY');
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
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('JBS_CMN_ADMINISTRATION'), 'administration');
		JToolbarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolbarHelper::divider();
		JToolbarHelper::help('biblestudy', true);
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
