<?php

/**
 * The rules every System Health check has to obey.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Health;

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthRegistry;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since 10.6.0
 */
class HealthContractTest extends ProclaimTestCase
{
    /**
     * Directory holding every check class.
     *
     * @var    string
     * @since  10.6.0
     */
    private const CHECK_DIR = '/admin/src/Health/Check';

    /**
     * A quietened notice is stored against its check id, so two checks sharing
     * one id would silence each other.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Every registered check has a unique id')]
    public function testIdsAreUnique(): void
    {
        $ids = array_map(static fn (HealthCheckInterface $c) => $c->getId(), HealthRegistry::checks());

        $this->assertNotEmpty($ids, 'The registry is empty — every assertion below would pass on nothing.');
        $this->assertSame(\count($ids), \count(array_unique($ids)), 'Two checks share an id: ' . implode(', ', $ids));
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('A check id is prefixed with the group it reports under')]
    public function testIdMatchesGroup(): void
    {
        foreach (HealthRegistry::checks() as $check) {
            $this->assertStringStartsWith(
                $check->getGroup()->value . '.',
                $check->getId(),
                $check->getId() . ' is filed under ' . $check->getGroup()->value . ' but is not named for it.'
            );
        }
    }

    /**
     * ⚠️ Not "the title is translated" — the component's language is not
     * loaded in this environment, so `Text::_()` legitimately returns the key
     * here and that assertion would fail on correct code. What has to hold is
     * that whatever key a check names is one en-GB actually defines, which is
     * the difference between a heading and `JBS_HEALTH_..._TITLE` on screen.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    public function testTitleKeysExist(): void
    {
        $strings = parse_ini_file(
            \dirname(__DIR__, 4) . '/admin/language/en-GB/en-GB.com_proclaim.ini',
            false,
            \INI_SCANNER_RAW
        );

        $this->assertIsArray($strings, 'en-GB could not be parsed — this test would pass on nothing.');

        foreach (HealthRegistry::checks() as $check) {
            $title = $check->getTitle();

            if (!preg_match('/^[A-Z][A-Z0-9_]+$/', $title)) {
                // Already translated, so the key resolved.
                continue;
            }

            $this->assertArrayHasKey(
                $title,
                $strings,
                $check->getId() . ' names ' . $title . ', which en-GB does not define.'
            );
        }
    }

    /**
     * ⚠️ This is the guarantee the whole two-tier design rests on. An active
     * check reaches the network and, on YouTube, spends metered quota — so
     * opening the health page, or the scheduled re-check firing, must never
     * be what runs one.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('runPassive() never evaluates a check that declares itself active')]
    public function testActiveChecksAreNotRunUnprompted(): void
    {
        $results = HealthRegistry::runPassive();
        $active  = 0;

        foreach (HealthRegistry::checks() as $check) {
            $this->assertArrayHasKey(
                $check->getId(),
                $results,
                $check->getId() . ' is missing from the report; an absent check reads as a passing one.'
            );

            if ($check->isPassive()) {
                continue;
            }

            $active++;

            $this->assertSame(
                HealthStatus::Unknown,
                $results[$check->getId()]->status,
                $check->getId() . ' is active, so an unprompted run must report Unknown rather than a result.'
            );
        }

        // ⚠️ Which checks are registered depends on the site's data: with no
        // API-backed server configured, the loop above has no active check to
        // examine and would report a pass having proven nothing. The spy below
        // exercises the same code path regardless, and asserts the stronger
        // thing -- that run() was never entered, not merely that the status
        // came out Unknown.
        $spy = new class () implements HealthCheckInterface {
            /**
             * @var bool
             * @since 10.6.0
             */
            public bool $wasRun = false;

            /**
             * @return string
             * @since  10.6.0
             */
            public function getId(): string
            {
                return 'external.spy';
            }

            /**
             * @return HealthGroup
             * @since  10.6.0
             */
            public function getGroup(): HealthGroup
            {
                return HealthGroup::ExternalServices;
            }

            /**
             * @return string
             * @since  10.6.0
             */
            public function getTitle(): string
            {
                return 'Spy';
            }

            /**
             * @return bool
             * @since  10.6.0
             */
            public function isPassive(): bool
            {
                return false;
            }

            /**
             * @return HealthResult
             * @since  10.6.0
             */
            public function run(): HealthResult
            {
                $this->wasRun = true;

                return new HealthResult($this->getId(), HealthStatus::Ok, 'ran');
            }
        };

        $spied = HealthRegistry::runPassive([$spy]);

        $this->assertFalse($spy->wasRun, 'runPassive() called run() on a check that declares itself active.');
        $this->assertSame(HealthStatus::Unknown, $spied['external.spy']->status);
        $this->assertGreaterThanOrEqual(0, $active);
    }

    /**
     * A check class nobody registers is dead code that still reads as coverage.
     *
     * ⚠️ Asserted against the registry's source, not against what it returned
     * here. Some checks are per-record -- one connection test per media server
     * -- so a database with no API-backed server legitimately produces none,
     * and comparing instantiated classes would fail on correct code. That is
     * exactly how this test first went red in CI.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Every check class in the Check directory is wired into the registry')]
    public function testEveryCheckClassIsRegistered(): void
    {
        $registry = (string) file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Health/HealthRegistry.php'
        );

        $this->assertNotSame('', $registry, 'HealthRegistry could not be read — this test would pass on nothing.');

        $files = $this->checkFiles();

        $this->assertNotEmpty($files, 'No check sources were found — this test would pass on nothing.');

        foreach ($files as $file) {
            $short = basename($file, '.php');

            $this->assertStringContainsString(
                'new ' . $short . '(',
                $registry,
                $short . ' exists but HealthRegistry never constructs one.'
            );
        }
    }

    /**
     * ⚠️ The same registry answers from an admin page render, from a restore
     * finishing, and from a scheduled task with no user at all. A check that
     * reaches for the identity, the session or the request works in the
     * browser and dies under cron — the failure mode already recorded for
     * api.php.
     *
     * Route and Uri are refused for the same reason: an action link is stored
     * as a raw URL and routed by whatever displays it.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('No check reads the identity, the session or the request')]
    public function testChecksAreContextFree(): void
    {
        $files = $this->checkFiles();

        $this->assertNotEmpty($files, 'No check sources were found — this test would pass on nothing.');

        foreach ($files as $file) {
            $source = (string) file_get_contents($file);

            foreach (['getIdentity', 'getSession', 'getInput', 'enqueueMessage', 'Route::', 'Uri::'] as $forbidden) {
                $this->assertStringNotContainsString(
                    $forbidden,
                    $source,
                    basename($file) . ' uses ' . $forbidden
                    . ', which ties it to a web request. It also has to answer from a scheduled task.'
                );
            }
        }
    }

    /**
     * Absolute paths of every check source file.
     *
     * @return  string[]
     *
     * @since   10.6.0
     */
    private function checkFiles(): array
    {
        $dir = \dirname(__DIR__, 4) . self::CHECK_DIR;

        return glob($dir . '/*.php') ?: [];
    }
}
