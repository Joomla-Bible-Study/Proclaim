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
 * Images with no WebP sibling built beside them.
 *
 * Nothing is broken without one -- the original still serves -- so this is a
 * notice. It is the derivative that has not been generated yet, not a missing
 * original.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ImageWebPCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'filesystem.image-webp';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Filesystem;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_IMAGE_WEBP_TITLE');
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
        $counts = CwmImageMigration::getWebPMigrationCounts();

        if ($counts['total'] === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_IMAGE_WEBP_OK')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            Text::plural('JBS_HEALTH_IMAGE_WEBP_N', $counts['total']),
            $counts['studies'] . ':' . $counts['teachers'] . ':' . $counts['series'],
            'index.php?option=com_proclaim&task=cwmadmin.edit&id=1#imagetools',
            Text::_('JBS_HEALTH_IMAGE_TOOLS_ACTION')
        );
    }
}
