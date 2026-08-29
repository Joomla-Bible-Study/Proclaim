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
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeQuota;
use Joomla\CMS\Language\Text;

/**
 * How much of a YouTube server's daily API budget is left.
 *
 * YouTube's quota resets on a fixed daily schedule, so running out is not an
 * error to be retried — everything that needs the API stops until the reset.
 * A sync that stopped half way looks like a sync that found nothing.
 *
 * ⚠️ Passive, and it stays that way because it costs nothing to ask: the
 * budget is a server parameter and the spend is a counter this component keeps
 * itself. Reading it makes no API call and therefore consumes no quota — which
 * a check reporting on quota had better not do.
 *
 * ⚠️ The count is what *we* recorded spending. Anything else using the same
 * API key — another site, another tool — spends against the same allowance and
 * is invisible here, so the real remaining figure can only be lower.
 *
 * @since  10.6.0
 */
final class YoutubeQuotaCheck implements HealthCheckInterface
{
    /**
     * @param   int     $serverId    The server row this reports on
     * @param   string  $serverName  Its name, for the title
     *
     * @since   10.6.0
     */
    public function __construct(
        private readonly int $serverId,
        private readonly string $serverName
    ) {
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'external.youtube-quota-' . $this->serverId;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::ExternalServices;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::sprintf('JBS_HEALTH_YOUTUBE_QUOTA_TITLE', $this->serverName);
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
        try {
            $remaining = CwmyoutubeQuota::getRemaining($this->serverId);
            $budget    = CwmyoutubeQuota::getDailyBudget($this->serverId);
        } catch (\Throwable) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::sprintf('JBS_HEALTH_YOUTUBE_QUOTA_UNREADABLE', $this->serverName)
            );
        }

        if ($remaining === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Warning,
                Text::sprintf('JBS_HEALTH_YOUTUBE_QUOTA_EXHAUSTED', $this->serverName),
                // The date, not the number: spent is spent until the reset, and
                // quietening it should last for today rather than for ever.
                'exhausted:' . gmdate('Y-m-d'),
                'index.php?option=com_proclaim&view=cwmservers',
                Text::_('JBS_HEALTH_YOUTUBE_QUOTA_ACTION')
            );
        }

        // A search is the most expensive thing the sync does regularly. Below
        // its cost the budget is technically non-zero and practically spent.
        if ($remaining < CwmyoutubeQuota::COST_SEARCH) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Notice,
                Text::sprintf(
                    'JBS_HEALTH_YOUTUBE_QUOTA_LOW',
                    $this->serverName,
                    $remaining,
                    CwmyoutubeQuota::COST_SEARCH
                ),
                'low:' . gmdate('Y-m-d'),
                'index.php?option=com_proclaim&view=cwmservers',
                Text::_('JBS_HEALTH_YOUTUBE_QUOTA_ACTION')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Ok,
            Text::sprintf('JBS_HEALTH_YOUTUBE_QUOTA_OK', $this->serverName, $remaining, $budget)
        );
    }
}
