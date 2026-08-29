<?php

/**
 * A key this component owns has to exist in en-GB, or the screen prints the key.
 *
 * Joomla's Text::_() returns its argument unchanged when the string is not
 * loaded. There is no error and no log line — the raw key simply appears in the
 * interface, which is how JBS_CPL_SERVER_MIGRATION_PENDING_DESC reached a
 * dashboard. The parity test cannot see this: it compares translations against
 * en-GB and never asks whether the code refers to something en-GB lacks.
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
class StringKeyExistsTest extends TestCase
{
    /**
     * What counts as a string this repository must define.
     *
     * Joomla's own families — JGLOBAL_, JHIDE, COM_CONTENT_ and the rest —
     * resolve from the CMS's language files, which are not in this repository,
     * so checking them here would report every one as missing.
     *
     * ⚠️ A bare `PLG_` prefix is not the test: core plugins use it too, and the
     * sermon view legitimately reuses `PLG_CONTENT_PAGEBREAK_PAGE_NUM` from
     * Joomla's own pagebreak plugin. Ownership is the extension name in the
     * key, not the kind of extension.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const array OWNED_MARKERS = ['PROCLAIM', 'SCRIPTURELINKS'];

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const array ROOTS = ['admin', 'site', 'api', 'components', 'modules', 'plugins'];

    /**
     * Every en-GB string this repository ships, from every extension in it.
     *
     * ⚠️ All of them, not just admin/language. The server addons each carry
     * their own en-GB file, and a scan that misses those reports ~70 keys as
     * undefined that are defined perfectly well one directory over.
     *
     * @return  array<string, string>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function englishStrings(): array
    {
        $base    = \dirname(__DIR__, 3);
        $strings = [];
        $files   = 0;

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            if (!str_ends_with($path, '.ini') || !str_contains($path, '/en-GB/')) {
                continue;
            }

            if (str_contains($path, '/node_modules/') || str_contains($path, '/tests/Backupfiles/')) {
                continue;
            }

            $parsed = parse_ini_file($path, false, \INI_SCANNER_RAW);

            if (\is_array($parsed)) {
                $strings += $parsed;
                $files++;
            }
        }

        return $strings;
    }

    /**
     * Every literal key handed to Text::_(), Text::sprintf() or Text::script().
     *
     * ⚠️ A key immediately followed by a `.` is skipped. Several call sites
     * build the key at runtime — `Text::_('JBS_ANA_REF_' . strtoupper($type))`
     * — and the literal prefix is not a key anyone declares. Treating those as
     * missing would make this test permanently red for correct code.
     *
     * @return  array<int, array{0: string, 1: int, 2: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function referencedKeys(): array
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
                    // The trailing (?!\s*\.) is the concatenation guard.
                    if (
                        !preg_match_all(
                            '/Text::(?:_|sprintf|script)\s*\(\s*[\'"]([A-Z][A-Z0-9_]{3,})[\'"](?!\s*\.)/',
                            $line,
                            $matches
                        )
                    ) {
                        continue;
                    }

                    foreach ($matches[1] as $key) {
                        $found[] = [substr($path, \strlen($base) + 1), $i + 1, $key];
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
    #[TestDox('Every Proclaim string key the code asks for is defined in en-GB')]
    public function testOwnedKeysAreDefined(): void
    {
        $strings = self::englishStrings();
        $keys    = self::referencedKeys();

        // ⚠️ Neither side may be empty. A scan that matches nothing would make
        // the assertion below pass over no data at all, which reads exactly
        // like success — the failure shape this project keeps meeting.
        $this->assertGreaterThan(3000, \count($strings), 'Too few en-GB strings loaded; the scan is broken.');
        $this->assertGreaterThan(500, \count($keys), 'Too few Text:: calls found; the scan is broken.');

        $problems = [];

        foreach ($keys as [$file, $line, $key]) {
            $owned = str_starts_with($key, 'JBS_');

            foreach (self::OWNED_MARKERS as $marker) {
                if (str_contains($key, $marker)) {
                    $owned = true;
                    break;
                }
            }

            if (!$owned || isset($strings[$key])) {
                continue;
            }

            $problems[$key] = \sprintf('%s (%s:%d)', $key, $file, $line);
        }

        $this->assertSame(
            [],
            array_values($problems),
            "These keys are referenced but defined nowhere in en-GB, so the interface prints them raw:\n"
            . implode("\n", $problems)
        );
    }
}
