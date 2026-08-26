<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\View\Cwmhealth;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmhealthModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * The System Health report.
 *
 * ⚠️ Gated on `core.admin`, not on the permission each individual check might
 * imply. The report is meant to be the permanent record of the site's state,
 * and a record with per-user holes in it is worse than one only
 * administrators can open.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Every check and its result, grouped by section.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    public array $report = [];

    /**
     * How many results fell into each status.
     *
     * @var    array<string, int>
     * @since  __DEPLOY_VERSION__
     */
    public array $summary = [];

    /**
     * Execute and display the report.
     *
     * @param   string  $tpl  Template override name.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        if (!$app->getIdentity()?->authorise('core.admin', 'com_proclaim')) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->redirect(Route::_('index.php?option=com_proclaim', false));

            return;
        }

        /** @var CwmhealthModel $model */
        $model = $this->getModel();

        $this->report  = $model->getReport();
        $this->summary = $model->summarise($this->report);

        ToolbarHelper::title(Text::_('JBS_HEALTH_TITLE'), 'health');
        ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_proclaim'));

        parent::display($tpl);
    }
}
