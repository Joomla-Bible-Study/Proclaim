<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Health\Check;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\Language\Text;

/**
 * Proclaim's debug setting left switched on.
 *
 * Not a fault — someone turned it on, usually to answer a question — but it
 * makes the component log verbosely and keep detail it would not otherwise
 * keep, and it is the kind of switch that gets left.
 *
 * ⚠️ Reads the stored setting, not `CwmDebug::isEnabled()`, and the difference
 * matters twice over. That helper reads the `JBSMDEBUG` constant, which
 * `admin/api.php` defines — and `api.php` does not run under a scheduled task,
 * so from cron the constant is simply absent and the answer would always be
 * "off". It is also set to 1 by the `jbsmdbg` query parameter, so on a web
 * request the helper can be true because of *this* request rather than because
 * of the setting, which is a question a check has no business asking.
 *
 * @since  __DEPLOY_VERSION__
 */
final class DebugModeCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'configuration.debug-mode';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Configuration;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_DEBUG_MODE');
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function run(): HealthResult
    {
        try {
            $debug = CwmproclaimHelper::debug();
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_DEBUG_MODE_UNREADABLE')
            );
        }

        if ($debug !== 1) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_DEBUG_MODE_OFF')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            Text::_('JBS_HEALTH_DEBUG_MODE_ON'),
            // Constant: the finding is the setting, and it has one shape. Only
            // turning it off should clear this.
            'on',
            'index.php?option=com_proclaim&view=cwmadmin',
            Text::_('JBS_HEALTH_DEBUG_MODE_ACTION')
        );
    }
}
