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
 * One thing the System Health report can check.
 *
 * ⚠️ A check answers about the site, never the caller: no identity, session or
 * request. Authorisation belongs to whatever displays the result.
 *
 * ⚠️ That display is super-admin only, and the checks rely on it. Site-wide
 * findings are safe to state plainly — `content.pending-review` counts every
 * message awaiting review, where the dashboard's own banner is location
 * filtered so a campus editor sees only their own. Putting a check on a
 * surface with a wider audience means rewriting the check, not re-pointing it.
 * HealthContractTest pins the gate.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HealthCheckInterface
{
    /**
     * Stable `group.slug` identifier.
     *
     * ⚠️ Stored as the quieting key, so renaming one un-quietens every site
     * that had cleared it.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getId(): string;

    /**
     * The report section this check appears under.
     *
     * @return  HealthGroup
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup;

    /**
     * Translated name of the check.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTitle(): string;

    /**
     * Whether `run()` is free to call unprompted.
     *
     * ⚠️ False means it reaches the network or spends quota, so only an
     * explicit request may run it.
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
