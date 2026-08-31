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
     * Every route into System Health is super-admin only, and has to stay so.
     *
     * ⚠️ This is not a preference. Checks report the state of the *site* —
     * `content.pending-review` counts every message awaiting review, where the
     * dashboard's own banner is location-filtered so a campus editor sees only
     * their own. Site-wide numbers are safe to show precisely because the only
     * audience is a super admin. Widen the audience and the check has to be
     * rewritten, not just re-pointed.
     *
     * Asserted at source level: exercising the ACL needs a booted application
     * and a real user, and the property worth defending is that the guard is
     * written down at all.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Every health task is gated on core.admin')]
    public function testHealthTasksRequireCoreAdmin(): void
    {
        $controller = \dirname(__DIR__, 4) . '/admin/src/Controller/CwmhealthController.php';

        $this->assertFileExists($controller);

        $source = (string) file_get_contents($controller);

        $this->assertStringContainsString(
            "authorise('core.admin'",
            $source,
            'CwmhealthController must check core.admin. Checks report site-wide state on the '
            . 'understanding that only a super admin ever reads it.'
        );

        // Each public task, not just one of them.
        foreach (['test', 'quieten', 'restore'] as $task) {
            $start = strpos($source, 'public function ' . $task . '(');

            $this->assertNotFalse($start, 'CwmhealthController::' . $task . '() should exist.');

            $next = strpos($source, "\n    public function ", $start + 1);
            $body = substr($source, $start, $next === false ? null : $next - $start);

            $this->assertStringContainsString(
                'assertAdmin',
                $body,
                $task . '() does not assert core.admin. Every route into health has to be gated the same way.'
            );
        }
    }

    /**
     * The report is rendered from the Administration screen, which gates every
     * action behind core.admin. A second surface would have to gate itself.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The health report renders only from the core.admin Administration screen')]
    public function testReportRendersOnlyBehindCoreAdmin(): void
    {
        $root = \dirname(__DIR__, 4);

        $renderers = [];

        foreach (glob($root . '/admin/tmpl/*/*.php') ?: [] as $file) {
            if (str_contains((string) file_get_contents($file), "'health.report'")) {
                $renderers[] = basename(\dirname($file));
            }
        }

        $this->assertSame(
            ['cwmadmin'],
            array_values(array_unique($renderers)),
            'The health report is rendered outside the Administration screen. That screen gates every action on '
            . 'core.admin; anywhere else has to gate itself, or site-wide findings reach an audience they were '
            . 'not written for.'
        );
    }

    /**
     * Helpers that read content rows on a check's behalf, and the argument that
     * makes them leave trashed content out.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const CONTENT_HELPERS = [
        'CwmImageMigration::getMigrationCounts'     => 'getMigrationCounts(true)',
        'CwmImageMigration::getUnresolvableRecords' => 'getUnresolvableRecords(true)',
    ];

    /**
     * A check that reads content must say what it thinks of trashed rows.
     *
     * Trashed content is content the administrator has already dealt with.
     * Counting it makes a finding that cannot be cleared by doing what it asks,
     * and in RestrictedMediaCheck's case raised a *security warning* about a
     * sermon that had been thrown away.
     *
     * ⚠️ This reads source rather than running the checks, because the failure
     * is invisible on a clean database: a dev site with no trashed content
     * gives the same answer either way. It is the omission that has to be
     * caught, not its effect on whatever data happens to be present.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A check reading content rows constrains their state')]
    public function testContentChecksExcludeTrashedRows(): void
    {
        $offenders = [];
        $covered   = 0;

        foreach ($this->checkFiles() as $file) {
            $src  = (string) file_get_contents($file);
            $name = basename($file);

            $viaHelper = false;

            foreach (self::CONTENT_HELPERS as $helper => $withFlag) {
                if (str_contains($src, $helper . '(')) {
                    $viaHelper = true;

                    if (!str_contains($src, $withFlag)) {
                        $offenders[] = $name . ' calls ' . $helper . '() without excluding trashed';
                    }
                }
            }

            // Direct reads of a content table. Servers and templatecode rows are
            // configuration rather than content an editor trashes, and
            // #__bsms_admin is a single settings row.
            $readsContent = (bool) preg_match(
                '/#__bsms_(studies|mediafiles|series|teachers|topics|comments|locations|playlists)/',
                $src
            );

            if ($readsContent && !str_contains($src, 'CONDITION_TRASHED')) {
                $offenders[] = $name . ' queries a content table without constraining published state';
            }

            if ($viaHelper || $readsContent) {
                $covered++;
            }
        }

        // ⚠️ Positive control. If the detection stops matching — a rename, a
        // moved directory — every assertion above passes against nothing.
        $this->assertGreaterThanOrEqual(
            4,
            $covered,
            'Fewer content-reading checks were found than exist. The detection above has stopped working, '
            . 'so this test is no longer guarding anything.'
        );

        $this->assertSame(
            [],
            $offenders,
            "A health check counts trashed content as a problem:\n  " . implode("\n  ", $offenders)
            . "\n\nTrashed content is already dealt with. Either constrain the state, or pass the helper's "
            . 'exclude-trashed argument.'
        );
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

    /**
     * Context the checks may not depend on, directly or through a helper.
     *
     * @var  string[]
     * @since   __DEPLOY_VERSION__
     */
    private const CONTEXT_BOUND = ['getIdentity', 'getSession', 'getInput', 'enqueueMessage', 'Route::', 'Uri::'];

    /**
     * Helper methods a check may call despite naming forbidden context, with
     * the reason each is safe.
     *
     * @var  array<string, string>
     * @since   __DEPLOY_VERSION__
     */
    private const CONTEXT_ALLOWED = [
        'CwmmediaProtectionHelper::canResolveSiteRoot' => 'Exists precisely to answer whether Uri::root() is trustworthy off a request, and returns false when it is not.',
        'Cwmhelper::mediaBuildUrl'                     => 'Uses Uri::root() for the protocol, but RestrictedMediaCheck returns Unknown before reaching it unless canResolveSiteRoot() has already said the root is real. The ordering is the guarantee, so moving that guard below the call would break this exemption.',
        'Cwmparams::getAdmin'                          => 'getIdentity() and enqueueMessage() are both reached through $app?-> and the helper documents the CLI case itself; the identity only decorates an extra field and does not change the params a check reads. No caller depends on either running: with no application both are skipped and the params a check reads are the same either way. ⚠️ This deliberately does not rest on which checks call it — GdprModeCheck is passive and calls it too.',
    ];

    /**
     * Static calls a check makes, read from tokens rather than text.
     *
     * ⚠️ Tokenised because these files discuss helpers in their docblocks —
     * `CwmDebug::isEnabled()` and `CwmprotectedStorage::status()` both appear
     * in prose explaining why a check does *not* call them. A regex reports
     * both and is worse than no test.
     *
     * @param   string  $file  A check's path
     *
     * @return  array<string, string>  Class => method
     *
     * @since   __DEPLOY_VERSION__
     */
    private function staticCallsIn(string $file): array
    {
        $tokens = token_get_all((string) file_get_contents($file));
        $calls  = [];

        foreach ($tokens as $i => $t) {
            if (!\is_array($t) || $t[0] !== \T_DOUBLE_COLON) {
                continue;
            }

            $class  = $tokens[$i - 1] ?? null;
            $method = $tokens[$i + 1] ?? null;

            if (\is_array($class) && $class[0] === \T_STRING && \is_array($method) && $method[0] === \T_STRING) {
                $calls[$class[1] . '::' . $method[1]] = $class[1];
            }
        }

        return $calls;
    }

    /**
     * The file a class imported into $file lives in, or null.
     *
     * @param   string  $file   The importing file
     * @param   string  $class  Short class name
     *
     * @return  ?string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function sourceOf(string $file, string $class): ?string
    {
        $root = \dirname(__DIR__, 4);

        if (!preg_match('/^use\s+(CWM\\\\Component\\\\Proclaim\\\\[\w\\\\]*\\\\' . preg_quote($class, '/') . ');/m', (string) file_get_contents($file), $m)) {
            return null;
        }

        $rel  = str_replace(
            ['CWM\\Component\\Proclaim\\Administrator\\', 'CWM\\Component\\Proclaim\\Site\\', '\\'],
            ['admin/src/', 'site/src/', '/'],
            $m[1]
        );
        $path = $root . '/' . $rel . '.php';

        return is_file($path) ? $path : null;
    }

    /**
     * ⚠️ The context rule above reads a check's own source, so a check that
     * calls a helper which reads the identity, the session or the request
     * passes it while breaking the same contract one level down.
     *
     * That is not hypothetical. Three helpers met this session would have
     * defeated it: CwmDebug::isEnabled() reads a constant admin/api.php
     * defines and a scheduled task therefore lacks; CwmsetupwizardHelper's
     * wizard test calls getIdentity() and returns false without one; and
     * isServedByWebServer() compares against Uri::root(), which off a request
     * is invented rather than derived — that one shipped in #1985 and had to
     * be corrected in #1989.
     *
     * One level deep, which is where all three sat.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('No check reaches the identity, the session or the request through a helper')]
    public function testChecksAreContextFreeThroughTheirHelpers(): void
    {
        $offenders = [];
        $examined  = 0;

        foreach ($this->checkFiles() as $file) {
            foreach ($this->staticCallsIn($file) as $call => $class) {
                if (isset(self::CONTEXT_ALLOWED[$call])) {
                    continue;
                }

                $source = $this->sourceOf($file, $class);

                if ($source === null) {
                    continue;
                }

                [, $method] = explode('::', $call);
                $body       = (string) file_get_contents($source);
                $start      = strpos($body, 'function ' . $method . '(');

                if ($start === false) {
                    continue;
                }

                $end  = strpos($body, "\n    }\n", $start);
                $code = substr($body, $start, ($end === false ? \strlen($body) : $end) - $start);
                $examined++;

                foreach (self::CONTEXT_BOUND as $forbidden) {
                    if (str_contains($code, $forbidden)) {
                        $offenders[] = \sprintf(
                            '%s -> %s uses %s',
                            basename($file),
                            $call,
                            $forbidden
                        );
                    }
                }
            }
        }

        $this->assertGreaterThan(
            0,
            $examined,
            'No helper bodies were examined — the resolver is not finding them, so this proves nothing.'
        );

        $this->assertSame(
            [],
            $offenders,
            "A check calls a helper that reads the identity, the session or the request. The check itself\n"
            . "looks context-free and is not: it will answer differently from a scheduled task, and for a\n"
            . "security check that difference is usually a false all-clear. Read the underlying state\n"
            . "instead, or add the helper to CONTEXT_ALLOWED with the reason it is safe.\n\n"
            . implode("\n", $offenders)
        );
    }
}
