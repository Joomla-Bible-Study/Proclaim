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
     * Where every task returns to. ⚠️ `task=`, not `view=`: the settings form
     * is set up by the controller.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const RETURN_VIEW = 'index.php?option=com_proclaim&task=cwmadmin.edit&id=1';

    /**
     * Run one check on request.
     *
     * ⚠️ The only path that may evaluate a non-passive check; everything else
     * runs without someone having asked.
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

        // ⚠️ Re-run rather than trust a fingerprint from the request, which
        // could silence a state that never existed.
        $result = HealthRegistry::runOne($this->input->getCmd('check', ''));

        if ($result === null || $result->fingerprint === '') {
            $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_NOTHING_TO_QUIETEN'), 'warning');

            return;
        }

        try {
            HealthQuietStore::quieten($result);
        } catch (\RuntimeException $e) {
            // The store refuses to write over unreadable params; say so
            // rather than report a success that did nothing.
            $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_QUIET_SAVE_FAILED'), 'error');

            return;
        }

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

        try {
            HealthQuietStore::restore($this->input->getCmd('check', ''));
        } catch (\RuntimeException $e) {
            $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_QUIET_SAVE_FAILED'), 'error');

            return;
        }

        $this->setRedirect(Route::_(self::RETURN_VIEW, false), Text::_('JBS_HEALTH_RESTORED'), 'message');
    }

    /**
     * Refuse a request without a valid token.
     *
     * ⚠️ These tasks are reached from links, so the token is in the query
     * string; `checkToken()` alone checks POST only. POST is still accepted.
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
