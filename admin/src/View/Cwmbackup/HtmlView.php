<?php

/**
 * Backup html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmbackup;

// Check to ensure this file is included in Joomla!
use CWM\Component\Proclaim\Administrator\Model\CwmadminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
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
    /** @var Registry CanDo function
     *
     * @since 9.0.0
     */
    public Registry $canDo;

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
    #[\Override]
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
                $types[] = HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_NO_FILES_TO_DISPLAY'));
            } else {
                asort($files, SORT_STRING);
                $fileList = [];

                foreach ($files as $value) {
                    $fileListTemp = ['value' => $value, 'text' => $value];
                    $fileList[]   = $fileListTemp;
                }

                $types[] = HTMLHelper::_('select.option', '0', Text::_('JBS_IBM_SELECT_DB'));
                $types   = array_merge($types, $fileList);
            }
        } else {
            $types[] = HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_NO_FILES_TO_DISPLAY'));
        }

        $this->lists['backedupfiles'] = HTMLHelper::_(
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');

        $toolbar = Toolbar::getInstance();

        // Add back to cpanel button
        $toolbar->linkButton('home', 'JBS_CMN_HOME')
            ->url('index.php?option=com_proclaim&view=cwmcpanel')
            ->icon('fas fa-home')
            ->listCheck(false);

        // Add back to Joomla admin button
        $toolbar->linkButton('back', 'JTOOLBAR_BACK')
            ->url('index.php')
            ->icon('fas fa-arrow-left')
            ->listCheck(false);

        ToolbarHelper::divider();
        ToolbarHelper::help('cwmbackup', true);
    }
}
