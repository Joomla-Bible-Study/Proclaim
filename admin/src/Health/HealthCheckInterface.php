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
 * One thing the System Health view can report on.
 *
 * ⚠️ A check answers about the site, never about the caller. It must not read
 * the identity, the session or the request, because the same check runs from
 * an admin page render, from a restore finishing, and from a scheduled task
 * with no user at all. Authorisation belongs to whatever displays the result.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HealthCheckInterface
{
    /**
     * Stable identifier, `group.slug`, used as the quieting key.
     *
     * ⚠️ It is stored in the database once a notice is quietened, so renaming
     * one silently un-quietens every site that had.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getId(): string;

    /**
     * The section of the report this check appears under.
     *
     * @return  HealthGroup
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup;

    /**
     * Translated name of the check, shown whatever it reports.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTitle(): string;

    /**
     * Whether running this check is free of side effects and safe unprompted.
     *
     * ⚠️ False means `run()` reaches the network, spends an API quota, or
     * otherwise costs something. Those are evaluated only when a person asks
     * for them by name -- never on a page render, and never from the scheduled
     * re-check, which has no one to consent on its behalf.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isPassive(): bool;

    /**
     * Evaluate the check.
     *
     * @return  HealthResult
     *
     * @since   __DEPLOY_VERSION__
     */
    public function run(): HealthResult;
}
