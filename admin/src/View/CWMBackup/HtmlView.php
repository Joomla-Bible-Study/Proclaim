<?php
/**
 * Backup html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMBackup;

// Check to ensure this file is included in Joomla!
use CWM\Component\Proclaim\Administrator\Model\CWMAdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var string CanDo function
	 *
	 * @since 9.0.0
	 */
	public $canDo;

	/** @var string Temp Destination
	 *
	 * @since 9.0.0
	 */
	public $tmp_dest;

	/** @var string Lists
	 *
	 * @since 9.0.0
	 */
	public $lists;

	/** @var array Form
	 *
	 * @since 9.0.0
	 */
	protected $form;

	/** @var array Item
	 *
	 * @since 9.0.0
	 */
	protected $item;

	/** @var array State
	 *
	 * @since 9.0.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$model = new CWMAdminModel;
		$this->setModel($model, true);

		// Get data from the model
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = ContentHelper::getActions('com_proclaim');

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

				$types[]                      = HtmlHelper::_('select.option', '0', Text::_('JBS_IBM_SELECT_DB'));
				$types                        = array_merge($types, $filelist);
				$this->lists['backedupfiles'] = HtmlHelper::_('select.genericlist', $types, 'backuprestore', 'class="inputbox" size="1" ', 'value', 'text', '');
			}
		}
		else
		{
			$this->lists['backedupfiles'] = Text::_('JBS_CMN_NO_FILES_TO_DISPLAY');
		}

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

		ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');
		ToolbarHelper::preferences('com_proclaim', '600', '800', 'JBS_ADM_PERMISSIONS');
		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
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
