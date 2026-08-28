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
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Language\Text;

/**
 * Whether GDPR mode is on, and what it is switching off.
 *
 * Not a fault — someone chose it, and on a site that needs it turning it off
 * would be the fault. It is here because its reach is wider than its name
 * suggests and the effects are all silent ones.
 *
 * With it on: outbound API calls are refused, so scripture providers and server
 * connection tests stop; social sharing is suppressed; and the analytics
 * personal-data tier is switched off. Each of those looks like a broken feature
 * rather than a setting, and the setting lives on a different screen from any
 * of them.
 *
 * @since  __DEPLOY_VERSION__
 */
final class GdprModeCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'configuration.gdpr-mode';
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
        return Text::_('JBS_HEALTH_GDPR_MODE');
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
            // ⚠️ The component's own copy in #__bsms_admin, not the plugin
            // parameter of the same name. CwmanalyticsHelper says so directly,
            // and ServerConnectionCheck already reads it from here.
            $enabled = (bool) Cwmparams::getAdmin()->params->get('gdpr_mode', '0');
        } catch (\Throwable) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_GDPR_MODE_UNREADABLE')
            );
        }

        if (!$enabled) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_GDPR_MODE_OFF')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            Text::_('JBS_HEALTH_GDPR_MODE_ON'),
            // Constant: the finding is the setting, and it has one shape. Only
            // turning it off should clear this.
            'on',
            'index.php?option=com_proclaim&view=cwmadmin',
            Text::_('JBS_HEALTH_GDPR_MODE_ACTION')
        );
    }
}
