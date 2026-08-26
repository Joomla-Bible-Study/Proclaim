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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Health\Check\LegacyServersCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\MaxInputVarsCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\PluginEnabledCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\ServerConnectionCheck;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Every check the System Health view knows about.
 *
 * The registry is the one place that decides what runs unprompted. Callers ask
 * for `runPassive()` and get an answer for every check, with the active ones
 * reported as `Unknown` rather than skipped -- a check missing from the report
 * looks like a check that passed.
 *
 * @since  __DEPLOY_VERSION__
 */
final class HealthRegistry
{
    /**
     * Build the list of checks.
     *
     * ⚠️ Not cached. Some checks are per-record -- one connection test per
     * media server -- so the list itself reflects the current site, and a
     * server added since the last page load has to appear.
     *
     * @return  HealthCheckInterface[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function checks(): array
    {
        $checks = [
            new MaxInputVarsCheck(),
            new PluginEnabledCheck('content', 'scripturelinks', 'JBS_HEALTH_PLUGIN_SCRIPTURELINKS'),
            new LegacyServersCheck(),
        ];

        foreach (self::testableServers() as $server) {
            $checks[] = new ServerConnectionCheck((int) $server['id'], (string) $server['server_name']);
        }

        return $checks;
    }

    /**
     * Run everything that is safe to run without being asked.
     *
     * `$checks` exists so the guarantee below can be tested against a check
     * that records whether it was run. Which checks are registered depends on
     * the site's data -- a site with no API-backed server has no active check
     * at all -- and a test that silently exercises nothing is how this
     * guarantee would quietly stop holding.
     *
     * @param   ?HealthCheckInterface[]  $checks  The checks to run, or null for the registry.
     *
     * @return  HealthResult[]  Keyed by check id.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function runPassive(?array $checks = null): array
    {
        $results = [];

        foreach ($checks ?? self::checks() as $check) {
            $results[$check->getId()] = $check->isPassive()
                ? self::evaluate($check)
                : new HealthResult(
                    $check->getId(),
                    HealthStatus::Unknown,
                    Text::_('JBS_HEALTH_NOT_TESTED')
                );
        }

        return $results;
    }

    /**
     * Run one check by id, active or not.
     *
     * Only reached from an explicit request to test something, which is the
     * consent an active check needs.
     *
     * @param   string  $id  The check id.
     *
     * @return  ?HealthResult  Null when no check declares that id.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function runOne(string $id): ?HealthResult
    {
        foreach (self::checks() as $check) {
            if ($check->getId() === $id) {
                return self::evaluate($check);
            }
        }

        return null;
    }

    /**
     * The findings the dashboard should raise: not passing, not quietened.
     *
     * @return  HealthResult[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dashboardNotices(): array
    {
        return array_values(array_filter(
            self::runPassive(),
            static fn (HealthResult $r) => $r->isActionable() && !HealthQuietStore::isQuiet($r)
        ));
    }

    /**
     * Run a check, turning a thrown error into a reportable result.
     *
     * A check that fatals must not take the health page down with it -- the
     * page is what someone opens when the site is already misbehaving.
     *
     * @param   HealthCheckInterface  $check  The check to run.
     *
     * @return  HealthResult
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function evaluate(HealthCheckInterface $check): HealthResult
    {
        try {
            return $check->run();
        } catch (\Throwable $e) {
            Log::add(
                'Proclaim health check ' . $check->getId() . ' failed: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );

            return new HealthResult(
                $check->getId(),
                HealthStatus::Unknown,
                Text::sprintf('JBS_HEALTH_CHECK_ERRORED', $e->getMessage())
            );
        }
    }

    /**
     * Media servers whose addon can test its own connection.
     *
     * @return  array<int, array{id: int, server_name: string, type: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function testableServers(): array
    {
        try {
            return CWMAddon::getConnectionTestableServers();
        } catch (\Throwable) {
            // A missing or unreadable servers table is itself something other
            // checks report on. Returning nothing here keeps the rest of the
            // report available instead of failing the whole page.
            return [];
        }
    }
}
