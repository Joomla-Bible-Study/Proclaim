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
 * What one check reports.
 *
 * The fingerprint is the shape of the finding ("10 servers, 2008 media"), not
 * the fact of it. A quietened notice returns as soon as it changes.
 *
 * ⚠️ A passing result has no fingerprint: there is nothing to quieten, and
 * storing one would let a later failure be silenced by a state that was fine.
 *
 * @since  10.6.0
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
     * @since   10.6.0
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
     * Whether the dashboard should raise this. ⚠️ `Unknown` is not a nag: an
     * untested active check has not found anything.
     *
     * @return  bool
     *
     * @since   10.6.0
     */
    public function isActionable(): bool
    {
        return $this->status === HealthStatus::Warning || $this->status === HealthStatus::Notice;
    }
}
