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
use CWM\Component\Proclaim\Administrator\Health\Check\AssetDriftCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\ImageMigrationCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\ImageWebPCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\LegacyServersCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\LocationFilteringCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\MaxInputVarsCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\MissingImagesCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\PendingReviewCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\PluginEnabledCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\PodcastTaskCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\SchemaVersionCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\ServerConnectionCheck;
use CWM\Component\Proclaim\Administrator\Health\Check\SimpleModeCheck;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Every check the System Health report knows about, and the only place that
 * decides what runs unprompted.
 *
 * @since  __DEPLOY_VERSION__
 */
final class HealthRegistry
{
    /**
     * Build the list of checks.
     *
     * ⚠️ Not cached: some are per-record, so a server added since the last
     * page load has to appear.
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
            new SchemaVersionCheck(),
            new PodcastTaskCheck(),
            new SimpleModeCheck(),
            new AssetDriftCheck(),
            new LocationFilteringCheck(),
            new LegacyServersCheck(),
            new PendingReviewCheck(),
            // ⚠️ These three stat every study, teacher and series image —
            // ~50ms per 1,000 records. Measure the next filesystem check too.
            new ImageMigrationCheck(),
            new MissingImagesCheck(),
            new ImageWebPCheck(),
        ];

        foreach (self::testableServers() as $server) {
            $checks[] = new ServerConnectionCheck(
                (int) $server['id'],
                (string) $server['server_name'],
                (string) $server['type']
            );
        }

        return $checks;
    }

    /**
     * Run everything safe to run unprompted; report the rest as `Unknown`.
     *
     * `$checks` is a seam for tests: which checks exist depends on site data,
     * so a test using the registry can exercise nothing and still pass.
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
     * Run one check by id, active or not. ⚠️ Only reachable from an explicit
     * request, which is the consent an active check needs.
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
     * Run a check, turning a throw into a reportable result. ⚠️ A check that
     * fatals must not take the report down with it.
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
            return [];
        }
    }
}
