<?php

/**
 * Section permissions html view
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Administrator\View\Cwmpermissions;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Model\CwmpermissionsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * One section's permissions grid, with a list of the others alongside.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The form carrying this section's grid.
     *
     * @var    Form|null
     * @since  __DEPLOY_VERSION__
     */
    public ?Form $form = null;

    /**
     * Section currently being edited.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public string $section = '';

    /**
     * Every section access.xml declares.
     *
     * @var    list<string>
     * @since  __DEPLOY_VERSION__
     */
    public array $sections = [];

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        // ⚠️ Gated here as well as on the controller: a task-less GET routes to
        // DisplayController, so the controller's check never runs.
        if (!$app->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $app->redirect('index.php?option=com_proclaim&view=cwmcpanel');

            return;
        }

        /** @var CwmpermissionsModel $model */
        $model = $this->getModel();

        $this->sections = Cwmassets::declaredSections();
        $this->section  = $model->getSection();
        $this->form     = $model->getForm() ?: null;

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('JBS_ADM_PERMISSIONS_TITLE'), 'lock');

        /** @var Toolbar $toolbar */
        $toolbar = $this->getDocument()->getToolbar();

        if ($this->section !== '') {
            $toolbar->apply('cwmpermissions.apply');
            $toolbar->save('cwmpermissions.save');
        }

        $toolbar->cancel('cwmpermissions.cancel', 'JTOOLBAR_CLOSE');

        ToolbarHelper::divider();

        // Component-level rules stay in com_config rather than being mirrored here.
        $toolbar->linkButton('options', 'JBS_ADM_PERMISSIONS_COMPONENT_LINK')
            ->url('index.php?option=com_config&view=component&component=com_proclaim')
            ->icon('fa-solid fa-gear')
            ->listCheck(false);

        ToolbarHelper::help('cwmpermissions', true);
    }
}
