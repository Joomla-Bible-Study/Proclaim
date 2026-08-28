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
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Language\Text;

/**
 * Whether multi-campus location filtering was switched on and left unfinished.
 *
 * ⚠️ Filtering with no group mapping does not fail loudly — it scopes editors
 * to the locations their groups map to, and an empty map means it can scope
 * them to nothing. The setting reads as configured while doing the opposite of
 * what was intended.
 *
 * @since  __DEPLOY_VERSION__
 */
final class LocationFilteringCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'configuration.location-filtering';
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
        return Text::_('JBS_HEALTH_LOCATION_FILTERING');
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
        // The helper answers the same question the dashboard prompt asks:
        // filtering enabled, not dismissed, and no group mapping yet. It is
        // false both when filtering is off and when it is fully configured —
        // two different kinds of fine, neither of which is a finding.
        if (!CwmlocationHelper::shouldShowWizard()) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_LOCATION_FILTERING_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            Text::_('JBS_HEALTH_LOCATION_FILTERING_UNCONFIGURED'),
            'unmapped',
            'index.php?option=com_proclaim&view=cwmlocationwizard',
            Text::_('JBS_HEALTH_LOCATION_FILTERING_ACTION')
        );
    }
}
