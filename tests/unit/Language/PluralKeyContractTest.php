<?php

/**
 * Every Text::plural() call has to name a key en-GB actually declares.
 *
 * `Text::plural($string, $n)` appends only a suffix — `$string . '_' . $suffix`
 * — so a call passing `FOO_DESC` looks for `FOO_DESC_ONE` / `FOO_DESC_OTHER`,
 * never `FOO_DESC_N_1`. The `_N` this project uses is part of the key handed in,
 * not something Joomla adds. Get that wrong and the page prints the raw key.
 *
 * Nothing else catches it: the string exists, so the parity test is happy, and
 * the call is syntactically fine.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Unit\Language;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @since __DEPLOY_VERSION__
 */
class PluralKeyContractTest extends TestCase
{
    /**
     * The suffixes en-GB's localise.php offers, in the order Joomla tries them.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const array SUFFIXES = ['0', 'ONE', '1', 'OTHER', 'MORE'];

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const array ROOTS = ['admin', 'site', 'api', 'components', 'modules', 'plugins'];

    /**
     * Every `Text::plural('KEY', …)` in the codebase, as [file, line, key].
     *
     * @return  array<int, array{0: string, 1: int, 2: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function pluralCalls(): array
    {
        $base  = \dirname(__DIR__, 3);
        $found = [];

        foreach (self::ROOTS as $root) {
            if (!is_dir($base . '/' . $root)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . '/' . $root));

            foreach ($it as $file) {
                $path = str_replace('\\', '/', $file->getPathname());

                if (!str_ends_with($path, '.php') || str_contains($path, '/vendor/')) {
                    continue;
                }

                $lines = explode("\n", (string) file_get_contents($path));

                foreach ($lines as $i => $line) {
                    // Both the one-line form and the key on its own line after
                    // a wrapped Text::plural( are covered by scanning for the
                    // call and then the first quoted key at or after it.
                    if (!preg_match('/Text::plural\s*\(/', $line)) {
                        continue;
                    }

                    $window = implode("\n", \array_slice($lines, $i, 4));

                    if (preg_match('/Text::plural\s*\(\s*[\'"]([A-Z0-9_]+)[\'"]/s', $window, $m)) {
                        $found[] = [substr($path, \strlen($base) + 1), $i + 1, $m[1]];
                    }
                }
            }
        }

        return $found;
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Every Text::plural() key resolves to a declared en-GB string')]
    public function testPluralKeysResolve(): void
    {
        $calls = self::pluralCalls();

        // ⚠️ Not a silent pass. If the scan finds nothing the assertions below
        // are vacuous, and this project has been bitten by that shape before.
        $this->assertNotEmpty($calls, 'No Text::plural() calls found — the scan is broken, not the code.');

        $en = [];

        foreach ((array) glob(\dirname(__DIR__, 3) . '/admin/language/en-GB/*.ini') as $file) {
            $parsed = parse_ini_file((string) $file, false, \INI_SCANNER_RAW);

            if (\is_array($parsed)) {
                $en += $parsed;
            }
        }

        $this->assertNotEmpty($en, 'No en-GB strings loaded.');

        $problems = [];

        foreach ($calls as [$file, $line, $key]) {
            $resolved = false;

            foreach (self::SUFFIXES as $suffix) {
                if (isset($en[$key . '_' . $suffix])) {
                    $resolved = true;
                    break;
                }
            }

            // A key with no plural variants at all still renders, as long as the
            // bare key exists — Text::plural() falls back to it.
            if (!$resolved && isset($en[$key])) {
                $resolved = true;
            }

            if (!$resolved) {
                $problems[] = sprintf(
                    '%s:%d  Text::plural(\'%s\') — no %s_{%s} and no bare %s',
                    $file,
                    $line,
                    $key,
                    $key,
                    implode('|', self::SUFFIXES),
                    $key
                );
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }
}
