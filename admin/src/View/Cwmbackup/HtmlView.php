<?php

/**
 * Backup html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMBackup;

// Check to ensure this file is included in Joomla!
use CWM\Component\Proclaim\Administrator\Model\CwmadminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

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
    /** @var CMSObject|Registry CanDo function
     *
     * @since 9.0.0
     */
    public CMSObject|Registry $canDo;

    /** @var string Temp Destination
     *
     * @since 9.0.0
     */
    public string $tmp_dest = '';

    /** @var array Lists
     *
     * @since 9.0.0
     */
    public array $lists;

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
     * @return  void
     *
     * @throws  \Exception
     * @see     \ViewLegacy::loadTemplate()
     * @since   3.0
     */
    public function display($tpl = null): void
    {
        $model = new CwmadminModel();
        $this->setModel($model, true);

        // Get data from the model
        $this->form  = $this->get("Form");
        $this->item  = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim');

        // Get the list of backup files
        $path = JPATH_SITE . '/media/com_proclaim/backup';

        if (is_dir($path)) {
            if (!$files = Folder::files($path, '.sql')) {
                $types[] = HtmlHelper::_('select.option', '0', Text::_('JBS_CMN_NO_FILES_TO_DISPLAY'));
            } else {
                asort($files, SORT_STRING);
                $fileList = [];

                foreach ($files as $value) {
                    $fileListTemp = ['value' => $value, 'text' => $value];
                    $fileList[]   = $fileListTemp;
                }

                $types[] = HtmlHelper::_('select.option', '0', Text::_('JBS_IBM_SELECT_DB'));
                $types   = array_merge($types, $fileList);
            }
        } else {
            $types[] = HtmlHelper::_('select.option', '0', Text::_('JBS_CMN_NO_FILES_TO_DISPLAY'));
        }

        $this->lists['backedupfiles'] = HtmlHelper::_(
            'select.genericlist',
            $types,
            'backuprestore',
            'class="form-select valid form-control-success" size="1" ',
            'value',
            'text',
            ''
        );

        $this->setLayout('edit');

        // Set the toolbar
        $this->addToolbar();

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
    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');
        ToolbarHelper::preferences('com_proclaim', '600', '800', 'JBS_ADM_PERMISSIONS');
        ToolbarHelper::divider();
        ToolbarHelper::help('proclaim', true);
    }

    /**
     * Added for SermonSpeaker and PreachIt.
     *
     * @param   string  $component  The Component it is coming from
     *
     * @return boolean
     *
     * @since 7.1.0
     */
    protected function versionXML(string $component): bool
    {
        switch ($component) {
            case 'sermonspeaker':
                $data = Installer::parseXMLInstallFile(
                    JPATH_ADMINISTRATOR . '/components/com_sermonspeaker/sermonspeaker.xml'
                );

                if ($data) {
                    return $data['version'];
                }

                return false;

            case 'preachit':
                $data = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_preachit/preachit.xml');

                if ($data) {
                    return $data['version'];
                }

                return false;
        }

        return false;
    }
}
