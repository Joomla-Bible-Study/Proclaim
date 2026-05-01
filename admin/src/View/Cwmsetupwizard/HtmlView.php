<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\View\Cwmsetupwizard;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlangHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Setup Wizard view — first-run configuration.
 *
 * @since  10.3.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Current wizard state from admin params.
     *
     * @var    array
     * @since  10.3.0
     */
    public array $currentState = [];

    /**
     * Ministry style presets.
     *
     * @var    array
     * @since  10.3.0
     */
    public array $presets = [];

    /**
     * Display the setup wizard.
     *
     * @param   string|null  $tpl  Layout override.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmsetupwizardModel $model */
        $model = $this->getModel();

        $this->currentState = $model->getCurrentState();
        $this->presets      = CwmsetupwizardHelper::PRESETS;

        // Bulk-register all JBS_* language keys for JavaScript
        CwmlangHelper::registerAllForJs();

        // Pass data to JavaScript
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.setup-wizard');
        $wa->useStyle('com_proclaim.setup-wizard');

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page toolbar.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('JBS_WIZARD_SETUP_TITLE'), 'cog');
        ToolbarHelper::help('proclaim', true);
    }

    /**
     * Get the JavaScript initialization data.
     *
     * @return  string  JSON-encoded data for window.ProcSetupWizard
     *
     * @since   10.3.0
     */
    public function getJsData(): string
    {
        return json_encode([
            'token'        => Session::getFormToken(),
            'baseUrl'      => Uri::base(true) . '/index.php',
            'currentState' => $this->currentState,
            'presets'      => $this->presets,
        ], JSON_THROW_ON_ERROR);
    }
}
