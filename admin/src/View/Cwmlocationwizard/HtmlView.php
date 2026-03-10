<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmlocationwizard;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Location Setup Wizard view
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Auto-detected migration scenario ('2A', '2B', or '2C').
     *
     * @var    string
     * @since  10.1.0
     */
    public string $scenario = '2C';

    /**
     * All published locations.
     *
     * @var    array
     * @since  10.1.0
     */
    public array $locations = [];

    /**
     * All Joomla user groups.
     *
     * @var    array
     * @since  10.1.0
     */
    public array $groups = [];

    /**
     * Current group-to-location mapping.
     *
     * @var    array
     * @since  10.1.0
     */
    public array $currentMapping = [];

    /**
     * Current group permissions detected from component asset rules.
     *
     * @var    array<string, string>  groupId → 'full'|'editor'|'viewer'|'none'
     * @since  10.1.0
     */
    public array $currentPermissions = [];

    /**
     * Detection info returned from model::getDetectionInfo().
     *
     * @var    array
     * @since  10.1.0
     */
    public array $detectionInfo = [];

    /**
     * Display the wizard.
     *
     * @param   string|null  $tpl  Layout override (unused).
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmlocationwizardModel $model */
        $model = $this->getModel();

        $this->detectionInfo      = $model->getDetectionInfo();
        $this->scenario           = $this->detectionInfo['scenario'];
        $this->locations          = $model->getLocations();
        $this->groups             = $model->getGroups();
        $this->currentMapping     = $model->getCurrentMapping();
        $this->currentPermissions = $model->getCurrentPermissions();

        // Register JS translation keys
        Text::script('JBS_WIZARD_APPLY_SUCCESS');
        Text::script('JBS_WIZARD_APPLY_ERROR');
        Text::script('JBS_WIZARD_CONFIRM_APPLY');
        Text::script('JBS_WIZARD_CONFIRM_DISMISS');
        Text::script('JBS_WIZARD_PROCESSING');
        Text::script('JERROR_AN_ERROR_HAS_OCCURRED');

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page toolbar.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('JBS_WIZARD_TITLE'), 'location');
        ToolbarHelper::help('proclaim', true);
    }
}
