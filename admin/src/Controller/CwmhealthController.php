<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthQuietStore;
use CWM\Component\Proclaim\Administrator\Health\HealthRegistry;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Actions on the System Health report.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class CwmhealthController extends BaseController
{
    /**
     * Where every task returns to.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const RETURN_VIEW = 'index.php?option=com_proclaim&view=cwmhealth';

    /**
     * Run one check on request, including the ones too expensive to run on load.
     *
     * ⚠️ The only path that evaluates a non-passive check. Nothing else in the
     * component may call it, because everything else runs without someone
     * having asked.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function test(): void
    {
        $this->assertToken();
        $this->assertAdmin();

        $id     = $this->input->getCmd('check', '');
        $result = HealthRegistry::runOne($id);

        if ($result === null) {
            $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_UNKNOWN_CHECK'), 'error');

            return;
        }

        $this->setRedirect(
            Route::_(self::RETURN_VIEW, false),
            $result->detail,
            match ($result->status) {
                HealthStatus::Ok      => 'success',
                HealthStatus::Warning => 'error',
                HealthStatus::Notice  => 'warning',
                HealthStatus::Unknown => 'info',
            }
        );
    }

    /**
     * Stop a finding nagging on the dashboard until its shape changes.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function quieten(): void
    {
        $this->assertToken();
        $this->assertAdmin();

        // Re-run rather than trusting a fingerprint from the request: what is
        // stored has to be the finding as it is now, or a stale value could
        // silence a state that never existed.
        $result = HealthRegistry::runOne($this->input->getCmd('check', ''));

        if ($result === null || $result->fingerprint === '') {
            $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_NOTHING_TO_QUIETEN'), 'warning');

            return;
        }

        HealthQuietStore::quieten($result);

        $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_QUIETENED'), 'message');
    }

    /**
     * Put a quietened finding back on the dashboard.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function restore(): void
    {
        $this->assertToken();
        $this->assertAdmin();

        HealthQuietStore::restore($this->input->getCmd('check', ''));

        $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_RESTORED'), 'message');
    }

    /**
     * Refuse a request that did not come from a Proclaim screen.
     *
     * Every task here is reached from a link on the report, so the token
     * arrives in the query string. `checkToken()` alone looks only at POST and
     * would reject all three -- the POST form is still accepted so a future
     * button does not have to remember this.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    private function assertToken(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403);
        }
    }

    /**
     * Refuse anyone who cannot administer the component.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    private function assertAdmin(): void
    {
        if (!$this->app->getIdentity()?->authorise('core.admin', 'com_proclaim')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
