<?php

/**
 * Contracts a translated string has to keep with its en-GB original.
 *
 * A translation can satisfy every check the project already runs — the file
 * parses, the key exists — while having lost the `%s` its call site supplies.
 * `Text::sprintf()` then fills positionally against the placeholders that
 * remain, so the string renders with a number missing, or with the wrong
 * number in a slot that still looks plausible.
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
class PlaceholderParityTest extends TestCase
{
    /**
     * A printf conversion, or a literal `%%`.
     *
     * The flag set deliberately omits the space flag. With a space in it,
     * ordinary prose such as "30% smaller" matches as a `% s` conversion and
     * every locale that words the sentence differently is reported as drift.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private const PLACEHOLDER = '/%%|%(?:\d+\$)?[-+0#]*[\d.]*[bcdeEfFgGosuxX]/';

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private const HTML_TAG = '/<\/?([a-z]+)[^>]*>/i';

    /**
     * Trees that hold shipped translations. Submodules keep their own suites.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const SKIP = ['/libraries/', '/vendor/', '/node_modules/', '/tests/Backupfiles/'];

    /**
     * Every shipped locale file paired with the en-GB file it translates.
     *
     * The en-GB name is resolved the way `Language::load()` resolves it: the
     * unprefixed `mod_foo.ini` first, then `en-GB.mod_foo.ini`. Both forms are
     * legal and both are in use here, so a resolver that only knows the
     * prefixed form silently skips whole extensions.
     *
     * @return  array<string, array{0: string, 1: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function localeFileProvider(): array
    {
        $base  = \dirname(__DIR__, 3);
        $pairs = [];

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            if (!str_ends_with($path, '.ini') || !str_contains($path, '/language/')) {
                continue;
            }

            foreach (self::SKIP as $skip) {
                if (str_contains($path, $skip)) {
                    continue 2;
                }
            }

            $dir = \dirname($path);
            $tag = basename($dir);

            if ($tag === 'en-GB' || !preg_match('/^[a-z]{2}-[A-Z]{2}$/', $tag)) {
                continue;
            }

            $stem  = preg_replace('/^' . preg_quote($tag, '/') . '\./', '', basename($path));
            $enDir = \dirname($dir) . '/en-GB';
            $en    = is_file($enDir . '/' . $stem) ? $enDir . '/' . $stem : $enDir . '/en-GB.' . $stem;

            if (!is_file($en)) {
                continue;
            }

            $pairs[substr($path, \strlen($base) + 1)] = [$path, $en];
        }

        ksort($pairs);

        return $pairs;
    }

    /**
     * The placeholders in a value, sorted so order of appearance is ignored
     * but count and kind are not.
     *
     * @param   string  $value  A raw language string
     *
     * @return  string[]
     *
     * @since __DEPLOY_VERSION__
     */
    private static function placeholders(string $value): array
    {
        preg_match_all(self::PLACEHOLDER, $value, $matches);
        $found = $matches[0];
        sort($found);

        return $found;
    }

    /**
     * The HTML tag names in a value, sorted.
     *
     * @param   string  $value  A raw language string
     *
     * @return  string[]
     *
     * @since __DEPLOY_VERSION__
     */
    private static function tags(string $value): array
    {
        preg_match_all(self::HTML_TAG, $value, $matches);
        $found = array_map('strtolower', $matches[1]);
        sort($found);

        return $found;
    }

    /**
     * Guards the guard. If the discovery above stops finding files — a moved
     * directory, a tightened skip list — every data-driven test below would
     * pass by having nothing to compare, which reads exactly like success.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Locale discovery finds the shipped translations')]
    public function testDiscoveryIsNotEmpty(): void
    {
        $pairs = self::localeFileProvider();

        $this->assertGreaterThan(
            200,
            \count($pairs),
            'Locale discovery collapsed. Every parity test below would pass vacuously.'
        );

        $this->assertArrayHasKey(
            'admin/language/de-DE/de-DE.com_proclaim.ini',
            $pairs,
            'The main admin translation is missing from discovery.'
        );

        $this->assertArrayHasKey(
            'modules/admin/mod_proclaimicon/language/de-DE/de-DE.mod_proclaimicon.ini',
            $pairs,
            'An extension whose en-GB filename is unprefixed dropped out of discovery.'
        );
    }

    /**
     * @param   string  $localePath  Absolute path of the translated file
     * @param   string  $enPath      Absolute path of its en-GB original
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Translated values keep the printf placeholders en-GB declares')]
    #[\PHPUnit\Framework\Attributes\DataProvider('localeFileProvider')]
    public function testPlaceholdersMatchEnGb(string $localePath, string $enPath): void
    {
        $en     = parse_ini_file($enPath, false, \INI_SCANNER_RAW);
        $locale = parse_ini_file($localePath, false, \INI_SCANNER_RAW);

        $this->assertIsArray($en, 'en-GB file failed to parse: ' . $enPath);
        $this->assertIsArray($locale, 'Locale file failed to parse: ' . $localePath);

        $problems = [];

        foreach ($locale as $key => $value) {
            if (!isset($en[$key])) {
                continue;
            }

            $expected = self::placeholders((string) $en[$key]);
            $actual   = self::placeholders((string) $value);

            if ($expected !== $actual) {
                $problems[] = \sprintf(
                    '%s: en-GB has %s, translation has %s',
                    $key,
                    $expected === [] ? 'none' : implode(' ', $expected),
                    $actual === [] ? 'none' : implode(' ', $actual)
                );
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }

    /**
     * @param   string  $localePath  Absolute path of the translated file
     * @param   string  $enPath      Absolute path of its en-GB original
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Translated values keep the HTML tags en-GB declares')]
    #[\PHPUnit\Framework\Attributes\DataProvider('localeFileProvider')]
    public function testHtmlTagsMatchEnGb(string $localePath, string $enPath): void
    {
        $en     = parse_ini_file($enPath, false, \INI_SCANNER_RAW);
        $locale = parse_ini_file($localePath, false, \INI_SCANNER_RAW);

        $this->assertIsArray($en);
        $this->assertIsArray($locale);

        $problems = [];

        foreach ($locale as $key => $value) {
            if (!isset($en[$key])) {
                continue;
            }

            $expected = self::tags((string) $en[$key]);
            $actual   = self::tags((string) $value);

            if ($expected !== $actual) {
                $problems[] = \sprintf(
                    '%s: en-GB has [%s], translation has [%s]',
                    $key,
                    implode(' ', $expected),
                    implode(' ', $actual)
                );
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }

    /**
     * Only orphans fail here, and deliberately so.
     *
     * ⚠️ A key en-GB declares that a translation lacks is the *normal* state of
     * this repository between a string change and the next sync: the convention
     * is that a feature edits en-GB alone and `cwm-sync-languages` catches the
     * rest up later. Joomla falls back to en-GB for anything missing, so the
     * screen stays readable. Failing on that direction would block every PR
     * that adds a string until someone ran a sync.
     *
     * The other direction is a real defect. A key only the translation has is
     * one whose en-GB original was renamed or deleted, so nothing will ever
     * read it again and the sync will not remove it.
     *
     * @param   string  $localePath  Absolute path of the translated file
     * @param   string  $enPath      Absolute path of its en-GB original
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Translations declare no key that en-GB has dropped')]
    #[\PHPUnit\Framework\Attributes\DataProvider('localeFileProvider')]
    public function testTranslationsHaveNoOrphanKeys(string $localePath, string $enPath): void
    {
        $en     = parse_ini_file($enPath, false, \INI_SCANNER_RAW);
        $locale = parse_ini_file($localePath, false, \INI_SCANNER_RAW);

        $this->assertIsArray($en);
        $this->assertIsArray($locale);

        $this->assertSame(
            [],
            array_values(array_diff(array_keys($locale), array_keys($en))),
            'Keys this translation declares that en-GB does not, so nothing reads them: ' . $localePath
        );
    }

    /**
     * `%%` is only meaningful to a string that reaches `sprintf()`. In one
     * passed to `Text::_()` no format processing happens, so the escape is
     * emitted literally and the English screen shows `%%` where it means a
     * percent sign — with every translation that writes a single `%` correct
     * and en-GB the outlier.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('No en-GB string escapes a percent unless something formats it')]
    public function testEscapedPercentOnlyWhereFormatted(): void
    {
        $base    = \dirname(__DIR__, 3);
        $checked = 0;
        $flagged = [];

        foreach (self::localeFileProvider() as $paths) {
            $enPath = $paths[1];
            $en     = parse_ini_file($enPath, false, \INI_SCANNER_RAW);

            if (!\is_array($en)) {
                continue;
            }

            foreach ($en as $key => $value) {
                $checked++;

                if (!str_contains((string) $value, '%%')) {
                    continue;
                }

                $quoted = preg_quote($key, '/');
                $cmd    = \sprintf(
                    'grep -rlE "sprintf\s*\(\s*[\x27\"]%s[\x27\"]" %s --include=*.php --include=*.js 2>/dev/null',
                    $quoted,
                    escapeshellarg($base)
                );

                if (trim((string) shell_exec($cmd)) === '') {
                    $flagged[] = $key . ' (' . substr($enPath, \strlen($base) + 1) . ')';
                }
            }
        }

        $this->assertGreaterThan(0, $checked, 'No en-GB values were inspected.');

        $this->assertSame(
            [],
            array_values(array_unique($flagged)),
            "These en-GB values escape a percent but nothing sprintf()s them, so the escape renders literally:\n"
            . implode("\n", array_unique($flagged))
        );
    }
}
