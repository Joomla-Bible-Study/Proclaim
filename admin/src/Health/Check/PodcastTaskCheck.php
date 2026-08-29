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
use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use Joomla\CMS\Language\Text;

/**
 * Whether the scheduled task that rebuilds podcast feeds is running.
 *
 * ⚠️ Nothing surfaces this failing. Feeds keep serving their last build, so a
 * podcast simply stops gaining episodes — and the directories subscribed to it
 * see a feed that still answers, just never changes.
 *
 * @since  __DEPLOY_VERSION__
 */
final class PodcastTaskCheck implements HealthCheckInterface
{
    /**
     * Task states, as `Cwmstats::getPodcastTaskRawState()` reports them.
     *
     * @var    int
     * @since  __DEPLOY_VERSION__
     */
    private const STATE_ENABLED = 1;

    private const STATE_DISABLED = 0;

    private const STATE_TRASHED = -2;

    private const STATE_MISSING = -3;

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'scheduled.podcast-task';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::ScheduledWork;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_PODCAST_TASK');
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
        // A site publishing no podcasts has nothing for the task to rebuild, so
        // its absence is not a finding. Reported as passing rather than skipped:
        // the report exists to say a thing was looked at.
        if (!Cwmstats::hasPublishedPodcasts()) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_PODCAST_TASK_NOT_NEEDED')
            );
        }

        $state = Cwmstats::getPodcastTaskRawState();

        if ($state === self::STATE_ENABLED) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_PODCAST_TASK_OK')
            );
        }

        // Separate messages, because the fix differs: a missing task has to be
        // created, a disabled or trashed one only re-enabled.
        $detail = match ($state) {
            self::STATE_MISSING  => Text::_('JBS_HEALTH_PODCAST_TASK_MISSING'),
            self::STATE_TRASHED  => Text::_('JBS_HEALTH_PODCAST_TASK_TRASHED'),
            self::STATE_DISABLED => Text::_('JBS_HEALTH_PODCAST_TASK_DISABLED'),
            default              => Text::_('JBS_HEALTH_PODCAST_TASK_UNKNOWN_STATE'),
        };

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            $detail,
            // Fingerprint on the state, so quietening a disabled task raises
            // again if it is later trashed or deleted.
            'state:' . $state,
            'index.php?option=com_scheduler&view=tasks',
            Text::_('JBS_HEALTH_PODCAST_TASK_ACTION')
        );
    }
}
