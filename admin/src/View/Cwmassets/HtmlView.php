<?php

/**
 * Assets html view
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Administrator\View\Cwmassets;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Asset management
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Asset check results
     *
     * @var array
     * @since 9.0.0
     */
    public $assets;

    /**
     * Model state
     *
     * @var object
     * @since 9.0.0
     */
    public $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app     = Factory::getApplication();
        $session = $app->getSession();

        // Get data from the model
        $this->state  = $this->get("State");
        $this->assets = $session->get('checklists', [], 'CWM');

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

        ToolbarHelper::title(Text::_('JBS_ADM_ASSET_TABLE_NAME'), 'shield-alt');

        $toolbar = Toolbar::getInstance();

        // Add home button to cpanel
        $toolbar->linkButton('home', 'JBS_CMN_HOME')
            ->url('index.php?option=com_proclaim&view=cwmcpanel')
            ->icon('fas fa-home')
            ->listCheck(false);

        // Add back to admin tools button
        $toolbar->linkButton('back', 'JTOOLBAR_BACK')
            ->url('index.php?option=com_proclaim&view=cwmadmin')
            ->icon('fas fa-arrow-left')
            ->listCheck(false);

        ToolbarHelper::divider();
        ToolbarHelper::help('cwmassets', true);
    }
}
