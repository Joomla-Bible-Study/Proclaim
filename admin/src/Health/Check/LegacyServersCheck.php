<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Health\Check;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use Joomla\CMS\Language\Text;

/**
 * Servers still on the 9.x `legacy` type, and the media rows riding on them.
 *
 * A restored 9.x backup arrives with every server legacy, and most media will
 * not resolve until they are migrated.
 *
 * @since  10.6.0
 */
final class LegacyServersCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'content.legacy-servers';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::ContentIntegrity;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_LEGACY_SERVERS_TITLE');
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function run(): HealthResult
    {
        $pending = CwmserverMigrationHelper::countPendingMigration();

        if ($pending['servers'] === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_LEGACY_SERVERS_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            // ⚠️ The `_N` belongs to the key passed in: plural() appends only
            // the suffix, so the keys are ..._PENDING_N_1 and ..._N_MORE.
            Text::plural('JBS_HEALTH_LEGACY_SERVERS_PENDING_N', $pending['servers'], $pending['media']),
            // Both counts, so migrating some of them resurfaces the notice.
            $pending['servers'] . ':' . $pending['media'],
            'index.php?option=com_proclaim&view=cwmservers',
            Text::_('JBS_HEALTH_LEGACY_SERVERS_ACTION')
        );
    }
}
