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
 * Records whose image path points at a file that is not on disk.
 *
 * ⚠️ A strict subset of the records awaiting migration. Migration cannot move
 * a file that is not there, so these fail rather than wait.
 *
 * @since  10.6.0
 */
final class MissingImagesCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'filesystem.missing-images';
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
        return Text::_('JBS_HEALTH_MISSING_IMAGES_TITLE');
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
        // Trashed records excluded: content already thrown away is not work
        // to report. The migration tools keep the default and still process
        // it, so a restored record's images are not left behind.
        $unresolvable = CwmImageMigration::getUnresolvableRecords(true);

        if ($unresolvable['count'] === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_MISSING_IMAGES_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            Text::plural('JBS_HEALTH_MISSING_IMAGES_N', $unresolvable['count']),
            (string) $unresolvable['count'],
            'index.php?option=com_proclaim&task=cwmadmin.edit&id=1#imagetools',
            Text::_('JBS_HEALTH_IMAGE_TOOLS_ACTION')
        );
    }
}
