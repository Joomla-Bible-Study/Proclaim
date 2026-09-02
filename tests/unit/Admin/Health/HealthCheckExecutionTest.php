<?php

/**
 * Unit tests that every passive health check can actually run
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Health;

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthRegistry;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * ⚠️ Until this existed, no health check had ever been executed by the suite.
 *
 * HealthContractTest reads the check classes at source level — ids are unique,
 * ids match their group, title keys exist, every class is registered, active
 * checks are not run unprompted. Eleven tests, and none of them calls run().
 * So for every registered check the body that produces the finding — its
 * status, its wording, its quieten key — was never exercised, and a check could
 * be green across the whole run and fail on the Administration screen, which is
 * the only place it is reached.
 *
 * That is CLAUDE.md's warning one level up: php -l and a passing suite do not
 * resolve a symbol on a line that never runs, and here no line ever ran.
 *
 * This does not assert what any check concludes — that belongs with the check.
 * It asserts they can be executed at all, and that executing them is safe.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(HealthRegistry::class)]
class HealthCheckExecutionTest extends ProclaimTestCase
{
    /**
     * The registry builds parameterised checks from the database — one per
     * YouTube server, one per testable server — so the number is site-dependent
     * and only the hand-written floor can be asserted.
     */
    private const MINIMUM_PASSIVE_CHECKS = 15;

    /**
     * @return  list<HealthCheckInterface>
     */
    private function passiveChecks(): array
    {
        return array_values(array_filter(
            HealthRegistry::checks(),
            static fn (HealthCheckInterface $check): bool => $check->isPassive()
        ));
    }

    #[TestDox('Every passive check runs and returns a usable result')]
    public function testEveryPassiveCheckRuns(): void
    {
        $checks = $this->passiveChecks();

        // ⚠️ Positive control. If the registry stops returning checks — or the
        // filter stops matching — every assertion below runs against an empty
        // list and this test reports success while covering nothing. That is
        // the exact failure it was written to end.
        $this->assertGreaterThanOrEqual(
            self::MINIMUM_PASSIVE_CHECKS,
            \count($checks),
            'Far fewer passive checks than the registry hand-registers. This test would be covering almost nothing.'
        );

        $failures = [];

        foreach ($checks as $check) {
            $id = $check->getId();

            try {
                $result = $check->run();
            } catch (\Throwable $e) {
                // The whole point. A check whose body no longer resolves, or
                // that trips over a null on a site shaped unlike the author's,
                // fails here instead of on the Administration screen.
                $failures[] = \sprintf(
                    '%s threw %s: %s (%s:%d)',
                    $id,
                    $e::class,
                    $e->getMessage(),
                    basename($e->getFile()),
                    $e->getLine()
                );

                continue;
            }

            if (!$result instanceof HealthResult) {
                $failures[] = $id . ' did not return a HealthResult.';

                continue;
            }

            if (!$result->status instanceof HealthStatus) {
                $failures[] = $id . ' returned no status.';
            }

            // A result carrying someone else's id, or none, cannot be quietened
            // or rendered against the check it came from.
            if ($result->id !== $id) {
                $failures[] = \sprintf('%s returned a result identified as "%s".', $id, $result->id);
            }
        }

        $this->assertSame(
            [],
            $failures,
            "Health checks that cannot be run:\n  " . implode("\n  ", $failures)
        );
    }

    #[TestDox('Running the checks writes nothing')]
    public function testRunningTheChecksIsReadOnly(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $count = static fn (string $table): int => (int) $db->setQuery(
            $db->createQuery()->select('COUNT(*)')->from($db->quoteName($table))
        )->loadResult();

        // ⚠️ #__assets specifically. Resolving a section's asset id creates the
        // row when it is missing, so a check that reached for it the convenient
        // way would silently populate the table every time the Administration
        // screen was opened. AssetDriftCheck avoids that deliberately; this
        // makes the next check that does not avoid it fail here.
        $before = ['#__assets' => $count('#__assets'), '#__bsms_admin' => $count('#__bsms_admin')];

        foreach ($this->passiveChecks() as $check) {
            try {
                $check->run();
            } catch (\Throwable) {
                // Reported by the test above; here only the side effects matter.
            }
        }

        foreach ($before as $table => $was) {
            $this->assertSame(
                $was,
                $count($table),
                "Running the health checks changed the number of rows in $table. A status report must not write."
            );
        }
    }

    #[TestDox('The active check is left alone')]
    public function testActiveChecksAreNotRunHere(): void
    {
        $active = array_filter(
            HealthRegistry::checks(),
            static fn (HealthCheckInterface $check): bool => !$check->isPassive()
        );

        // Not an assertion that active checks exist — there may one day be
        // none. It records that the filter above is a real distinction and
        // names what it excluded, so a reviewer can see the coverage is
        // partial by design rather than by accident.
        foreach ($active as $check) {
            $this->assertFalse(
                $check->isPassive(),
                'An active check leaked into the passive set; it would be run unprompted.'
            );
        }

        $this->addToAssertionCount(1);
    }
}
