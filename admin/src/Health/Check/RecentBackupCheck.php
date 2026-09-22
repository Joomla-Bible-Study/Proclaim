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
use Joomla\CMS\Language\Text;

/**
 * How long it has been since Proclaim took a backup of its own.
 *
 * The restore path is the component's most destructive: an import drops all 23
 * tables before it writes anything, so a failure part way leaves less than was
 * there before. That is survivable with a recent backup and not otherwise.
 *
 * ⚠️ A notice, never a warning, and the wording says why. Plenty of sites are
 * backed up at the host or by another extension, and Proclaim cannot see that.
 * Reporting "you have no backup" would be stating something it does not know;
 * reporting "Proclaim has not taken one" is true either way.
 *
 * @since  10.6.0
 */
final class RecentBackupCheck implements HealthCheckInterface
{
    /**
     * Age past which a backup stops counting as recent, in days.
     *
     * A month is long enough not to nag a site that backs up monthly, and short
     * enough that a year-old file is not reported as cover.
     *
     * @since  10.6.0
     */
    private const STALE_AFTER_DAYS = 30;

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'scheduled.recent-backup';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::ScheduledWork;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_RECENT_BACKUP');
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
        $dir = JPATH_SITE . '/media/com_proclaim/backup';

        if (!is_dir($dir)) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Notice,
                Text::_('JBS_HEALTH_RECENT_BACKUP_NONE'),
                'none',
                'index.php?option=com_proclaim&view=cwmbackup',
                Text::_('JBS_HEALTH_RECENT_BACKUP_ACTION')
            );
        }

        $newest = 0;

        foreach (glob($dir . '/*') ?: [] as $file) {
            // Directories and the .htaccess/web.config that keep the folder off
            // the web are not backups and must not read as one.
            if (!is_file($file) || str_starts_with(basename($file), '.') || basename($file) === 'web.config') {
                continue;
            }

            $newest = max($newest, (int) filemtime($file));
        }

        if ($newest === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Notice,
                Text::_('JBS_HEALTH_RECENT_BACKUP_NONE'),
                'none',
                'index.php?option=com_proclaim&view=cwmbackup',
                Text::_('JBS_HEALTH_RECENT_BACKUP_ACTION')
            );
        }

        $days = (int) floor((time() - $newest) / 86400);

        if ($days >= self::STALE_AFTER_DAYS) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Notice,
                Text::sprintf('JBS_HEALTH_RECENT_BACKUP_STALE', $days),
                // Bucketed by month, so an ageing backup raises again rather
                // than staying quiet for ever once cleared.
                'stale:' . (int) floor($days / self::STALE_AFTER_DAYS),
                'index.php?option=com_proclaim&view=cwmbackup',
                Text::_('JBS_HEALTH_RECENT_BACKUP_ACTION')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Ok,
            Text::sprintf('JBS_HEALTH_RECENT_BACKUP_OK', $days)
        );
    }
}
