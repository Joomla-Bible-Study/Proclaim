<?php

/**
 * Postflight says where the time went.
 *
 * The migrations were instrumented first and turned out to cost nothing —
 * 0.002s of a 6.89s postflight on a production update. That left the part an
 * update actually spends its time in completely unmeasured, so "make updates
 * faster" had nothing to aim at.
 *
 * ⚠️ Two contracts are asserted here that nothing else can see. The timer must
 * re-raise, because the work it wraps is load-bearing and several call sites
 * already choose their own error handling; a swallowing timer would silently
 * turn an installer failure into a success. And the summary line is parsed by
 * build/verify-install-log.php in the release gate, so its leading field is a
 * cross-file contract rather than cosmetic text.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since  __DEPLOY_VERSION__
 */
class PostflightTaskTest extends ProclaimTestCase
{
    /**
     * @return  string  The manifest script's source
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function script(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 4) . '/proclaim.script.php');
    }

    /**
     * One method's body, so an assertion cannot be satisfied by a sibling.
     *
     * @param   string  $signature  The method's declaration line
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function methodBody(string $signature): string
    {
        $source = self::script();
        $start  = strpos($source, $signature);

        self::assertNotFalse($start, $signature . ' could not be found.');

        $end = strpos($source, "\n    }\n", $start);

        self::assertNotFalse($end, 'Could not find the end of ' . $signature);

        return substr($source, $start, $end - $start);
    }

    #[TestDox('The timer re-raises, so wrapping work cannot turn a failed install into a quiet one')]
    public function testTaskDoesNotSwallowFailures(): void
    {
        $body = self::methodBody('private function task(string $name, callable $work): mixed');

        $this->assertStringNotContainsString(
            'catch',
            $body,
            'task() must not catch: step() swallows because a migration is optional, '
            . 'but postflight work is not, and several call sites handle their own errors.'
        );
        $this->assertStringContainsString(
            'finally',
            $body,
            'The timing must be taken in a finally, or work that throws goes unmeasured — '
            . 'which is exactly the work worth measuring.'
        );
    }

    #[TestDox('Every timed task is recorded and logged')]
    public function testTaskRecordsAndLogs(): void
    {
        $body = self::methodBody('private function task(string $name, callable $work): mixed');

        $this->assertStringContainsString(
            '$this->taskLog[]',
            $body,
            'A task that is timed but not recorded contributes nothing to the summary.'
        );
        $this->assertStringContainsString(
            '$this->logInstall(',
            $body,
            'The per-task line is the only durable record of where an update spent its time.'
        );
    }

    #[TestDox('The migration steps and the postflight tasks stay separate')]
    public function testTaskLogIsNotTheStepLog(): void
    {
        $body = self::methodBody('private function task(string $name, callable $work): mixed');

        $this->assertStringNotContainsString(
            '$this->stepLog',
            $body,
            'Tasks written into the migration log would be reported to the administrator '
            . 'as migrations, and counted as migrations by the release gate.'
        );
    }

    #[TestDox('Postflight times more than just the migrations')]
    public function testPostflightIsInstrumented(): void
    {
        $source = self::script();

        preg_match_all("/\\\$this->task\('([^']+)'/", $source, $matches);

        $names = $matches[1];

        $this->assertGreaterThanOrEqual(
            10,
            \count($names),
            'Postflight is where an update spends its time; timing only a couple of its '
            . 'calls leaves the rest unaccounted for.'
        );
        $this->assertSame(
            \count($names),
            \count(array_unique($names)),
            'Two tasks sharing a name cannot be told apart in the log: ' .
            implode(', ', array_diff_assoc($names, array_unique($names)))
        );
    }

    #[TestDox('The summary line still leads with the field the release gate parses')]
    public function testSummaryLineKeepsTheParsedPrefix(): void
    {
        $source = self::script();

        preg_match("/'postflight: [^']*step\(s\)[^']*'/", $source, $found);

        $this->assertNotEmpty($found, 'The postflight summary format string could not be found.');

        $format = trim($found[0], "'");

        // The gate reads the step count out of this line and cross-checks it
        // against the per-step lines. Rendering the format with plausible
        // values proves the real output still matches, rather than trusting
        // that the format string looks close enough.
        $rendered = \sprintf($format, 8, 0.002, 14, 6.5, 6.89, 9.61);

        $this->assertMatchesRegularExpression(
            '/^postflight: (\d+) step\(s\) in /',
            $rendered,
            'build/verify-install-log.php parses this prefix. Anything added to the summary '
            . 'must go after it, or the release gate stops being able to read the step count.'
        );
    }
}
