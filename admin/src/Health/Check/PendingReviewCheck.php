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
use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use Joomla\CMS\Language\Text;

/**
 * Messages submitted for review and still unpublished.
 *
 * ⚠️ Counted for the whole site, not for whoever is looking. The dashboard
 * banner is location-filtered so a campus editor sees only what they can act
 * on; a health check reports the state of the site, and filtering it by an
 * identity would make the answer depend on who asked — which is also what the
 * check contract forbids, since this has to answer from a scheduled task.
 *
 * @since  10.6.0
 */
final class PendingReviewCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'content.pending-review';
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
        return Text::_('JBS_HEALTH_PENDING_REVIEW');
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
        // Null mode: the site-wide count. Any other mode scopes to the current
        // user's locations, and reads the identity to do it.
        $pending = CwmcountHelper::getPendingReviewCount(null);

        if ($pending === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_PENDING_REVIEW_NONE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            $pending === 1
                ? Text::_('JBS_HEALTH_PENDING_REVIEW_1')
                : Text::sprintf('JBS_HEALTH_PENDING_REVIEW_N', $pending),
            // The count, so clearing it at three raises again at four rather
            // than staying quiet while the queue grows.
            (string) $pending,
            'index.php?option=com_proclaim&view=cwmmessages&filter[published]=0',
            Text::_('JBS_HEALTH_PENDING_REVIEW_ACTION')
        );
    }
}
