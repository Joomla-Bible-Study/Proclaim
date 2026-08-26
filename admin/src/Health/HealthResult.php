<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Health;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/**
 * What one check reports about one thing.
 *
 * The fingerprint is the shape of the finding rather than the fact of it --
 * "10 legacy servers, 2008 media rows", not "there is a problem". Quietening a
 * dashboard notice stores this string, and the notice stays quiet only while
 * the check keeps producing it. An eleventh legacy server changes the
 * fingerprint and the notice comes back on its own.
 *
 * A passing check has no fingerprint. There is nothing to quieten, and storing
 * one would mean a later failure could be silenced by a state that was fine.
 *
 * @since  __DEPLOY_VERSION__
 */
final readonly class HealthResult
{
    /**
     * Constructor.
     *
     * @param   string        $id           The check id this result answers for.
     * @param   HealthStatus  $status       The state reported.
     * @param   string        $detail       Translated sentence describing the state.
     * @param   string        $fingerprint  The shape of the finding; empty when passing.
     * @param   ?string       $actionLink   Route to the screen that resolves it.
     * @param   ?string       $actionLabel  Translated label for that link.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(
        public string $id,
        public HealthStatus $status,
        public string $detail,
        public string $fingerprint = '',
        public ?string $actionLink = null,
        public ?string $actionLabel = null
    ) {
    }

    /**
     * Whether this result is something the dashboard should raise.
     *
     * `Unknown` is deliberately not a nag. An active check that has never been
     * run has not found anything, and a dashboard banner saying so would fire
     * on every site that never pressed the button.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isActionable(): bool
    {
        return $this->status === HealthStatus::Warning || $this->status === HealthStatus::Notice;
    }
}
