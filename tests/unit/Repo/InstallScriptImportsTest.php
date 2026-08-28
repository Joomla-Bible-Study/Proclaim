<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Every class the install script names must resolve to an import.
 *
 * ⚠️ The script has no namespace, so an unimported `Foo::bar()` resolves to a
 * global `\Foo` and PHP raises an **Error** — which `catch (\Exception $e)` does
 * not catch. The failure is a fatal, not a caught one.
 *
 * This is not hypothetical. `DatabaseDriver::splitSql()` was written into
 * dropTablesIfRequested() a month after a refactor removed the import, and sat
 * there from 2026-03-23. That path runs on uninstall when the administrator has
 * opted into dropping tables, and it deletes the `#__assets` rows *before*
 * reaching the fatal — so an uninstall destroyed the ACL rows, then died without
 * dropping a single table.
 *
 * Nothing caught it for five months: it only runs on uninstall, only with
 * drop_tables set, and the install script is not exercised by the unit suite.
 *
 * @since  __DEPLOY_VERSION__
 */
class InstallScriptImportsTest extends ProclaimTestCase
{
    /**
     * Resolvable without an import: PHP's own globals, and classes this file
     * declares. `parent::` and `self::` are language constructs, not classes.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const GLOBALS = [
        'Exception', 'RuntimeException', 'Throwable', 'Error', 'JsonException',
        'stdClass', 'DateTime', 'DateTimeZone', 'SplFileInfo', 'ArrayObject',
        'JLoader', 'self', 'parent', 'static',
    ];

    /**
     * The script source with comments and string literals removed.
     *
     * Tokenised rather than pattern-matched: the docblocks in this file name
     * classes constantly (`CwmtemplatecodeTable::store()`, `FileStorage::…`),
     * and one branch tests for the literal string `'JHtml::addIncludePath'`.
     * A regex over raw text reports all of those and is worse than no test.
     *
     * @return  array<int, array{name: string, line: int}>  Static-call targets
     *
     * @since   __DEPLOY_VERSION__
     */
    private function staticCallTargets(): array
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/proclaim.script.php');
        $tokens = token_get_all($source);
        $found  = [];

        foreach ($tokens as $i => $token) {
            // A class name in a static call is T_STRING followed by T_DOUBLE_COLON.
            if (!\is_array($token) || $token[0] !== \T_STRING) {
                continue;
            }

            $next = $tokens[$i + 1] ?? null;

            if (!\is_array($next) || $next[0] !== \T_DOUBLE_COLON) {
                continue;
            }

            // A leading backslash makes it explicitly global and always valid.
            $prev = $tokens[$i - 1] ?? null;

            if (\is_array($prev) && $prev[0] === \T_NS_SEPARATOR) {
                continue;
            }

            if ($prev === '\\') {
                continue;
            }

            $found[] = ['name' => $token[1], 'line' => $token[2]];
        }

        return $found;
    }

    #[TestDox('Every class named in a static call in the install script is imported')]
    public function testEveryStaticCallTargetIsImported(): void
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/proclaim.script.php');

        preg_match_all('/^use\s+([\w\\\\]+?)(?:\s+as\s+(\w+))?;/m', $source, $matches, \PREG_SET_ORDER);

        $imported = [];

        foreach ($matches as $match) {
            $parts      = explode('\\', $match[1]);
            $imported[] = $match[2] ?? end($parts);
        }

        $known  = array_merge($imported, self::GLOBALS);
        $usages = $this->staticCallTargets();

        $this->assertNotEmpty($usages, 'No static calls found — the tokeniser is not seeing the file.');

        $offenders = [];

        foreach ($usages as $usage) {
            if (\in_array($usage['name'], $known, true)) {
                continue;
            }

            $offenders[] = $usage['name'] . '::  at line ' . $usage['line'];
        }

        $this->assertSame(
            [],
            $offenders,
            "proclaim.script.php has no namespace, so an unimported class resolves to the global one and PHP\n"
            . "raises an Error — which catch (\\Exception) does not catch, making it a fatal. Add the use\n"
            . "statement, or prefix the call with a backslash if the global class is genuinely meant."
        );
    }
}
