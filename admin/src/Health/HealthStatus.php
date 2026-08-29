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
 * The state a health check reports. `Unknown` is a real answer: nothing was
 * measured, which is not the same as passing.
 *
 * @since  __DEPLOY_VERSION__
 */
enum HealthStatus: string
{
    case Ok      = 'ok';
    case Notice  = 'notice';
    case Warning = 'warning';
    case Unknown = 'unknown';

    /**
     * Sort weight, worst first.
     *
     * @return  int
     *
     * @since   __DEPLOY_VERSION__
     */
    public function weight(): int
    {
        return match ($this) {
            self::Warning => 0,
            self::Notice  => 1,
            self::Unknown => 2,
            self::Ok      => 3,
        };
    }

    /**
     * Bootstrap contextual suffix for the badge and the row.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function contextClass(): string
    {
        return match ($this) {
            self::Warning => 'danger',
            self::Notice  => 'warning',
            self::Unknown => 'secondary',
            self::Ok      => 'success',
        };
    }

    /**
     * Language key for the status label.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function labelKey(): string
    {
        return 'JBS_HEALTH_STATUS_' . strtoupper($this->value);
    }
}
