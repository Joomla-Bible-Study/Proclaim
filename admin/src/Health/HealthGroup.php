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
 * The section of the health report a check belongs to. A new check picks from
 * this list rather than inventing a heading.
 *
 * @since  10.6.0
 */
enum HealthGroup: string
{
    case Environment      = 'environment';
    case Database         = 'database';
    case Filesystem       = 'filesystem';
    case Dependencies     = 'dependencies';
    case ScheduledWork    = 'scheduled';
    case ExternalServices = 'external';
    case Security         = 'security';
    case ContentIntegrity = 'content';
    case Configuration    = 'configuration';

    /**
     * Language key for the section heading.
     *
     * @return  string
     *
     * @since   10.6.0
     */
    public function labelKey(): string
    {
        return 'JBS_HEALTH_GROUP_' . strtoupper($this->value);
    }
}
