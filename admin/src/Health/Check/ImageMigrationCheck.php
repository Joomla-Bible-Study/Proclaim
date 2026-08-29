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
use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use Joomla\CMS\Language\Text;

/**
 * Records whose images still sit outside the structured
 * `images/biblestudy/{type}/{alias}-{id}/` folders. Not a fault: the old
 * paths keep resolving.
 *
 * @since  10.6.0
 */
final class ImageMigrationCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'filesystem.image-migration';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Filesystem;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_IMAGE_MIGRATION_TITLE');
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
        $counts = CwmImageMigration::getMigrationCounts();

        if ($counts['total'] === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_IMAGE_MIGRATION_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            Text::plural('JBS_HEALTH_IMAGE_MIGRATION_N', $counts['total']),
            // Per type, so migrating one kind of record resurfaces the notice.
            $counts['studies'] . ':' . $counts['teachers'] . ':' . $counts['series'],
            'index.php?option=com_proclaim&task=cwmadmin.edit&id=1#imagetools',
            Text::_('JBS_HEALTH_IMAGE_TOOLS_ACTION')
        );
    }
}
