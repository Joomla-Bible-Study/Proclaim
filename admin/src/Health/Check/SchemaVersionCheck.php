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
use CWM\Component\Proclaim\Administrator\Helper\CwmupgradeHelper;
use Joomla\CMS\Language\Text;

/**
 * Whether `#__schemas` has caught up with the migrations this build ships.
 *
 * ⚠️ A gap here is not cosmetic: a site left behind is running current PHP
 * against an older table shape, so the failure lands wherever a query first
 * names a column the site does not have.
 *
 * @since  __DEPLOY_VERSION__
 */
final class SchemaVersionCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'database.schema-version';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Database;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_SCHEMA_VERSION');
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
        $gap = CwmupgradeHelper::isSchemaOutOfDate();

        // Null covers both "up to date" and "cannot tell" — the helper returns
        // it when the extension id or the recorded version is missing. Those
        // are states of Joomla's own bookkeeping rather than findings of ours,
        // and nothing here could act on them.
        if ($gap === null) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_SCHEMA_VERSION_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            Text::sprintf('JBS_HEALTH_SCHEMA_VERSION_BEHIND', $gap['current'], $gap['expected']),
            // Fingerprint on the pair, so quietening a known gap goes live
            // again the moment either end moves.
            $gap['current'] . '→' . $gap['expected'],
            'index.php?option=com_installer&view=database',
            Text::_('JBS_HEALTH_SCHEMA_VERSION_ACTION')
        );
    }
}
