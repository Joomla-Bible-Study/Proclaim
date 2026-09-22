<?php

/**
 * A template code record must not be able to overwrite a layout the package ships.
 *
 * A record writes `default_<filename>.php` into its type's directory. Name one
 * after a shipped layout and it replaces that file — and deleting the record
 * then removes it. The shipped `default.php` for each view calls its sublayouts
 * unconditionally (`cwmsermon/default.php:70` is `loadTemplate('footer')`) and
 * Joomla answers a missing sublayout by throwing a 500, so the consequence is
 * the front end going down, not a reverted customization.
 *
 * ⚠️ The refusal list only works while it matches what is actually shipped, and
 * a list that drifts fails open — a newly shipped layout would be creatable.
 * The drift test below therefore asserts **both** directions.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class ShippedLayoutsTest extends ProclaimTestCase
{
    /**
     * A site-root layout directory mapped back onto this repository's layout.
     *
     * The map is written as the site sees it — `components/com_proclaim/tmpl/x`
     * and `modules/mod_proclaim/tmpl` — which is `site/tmpl/x` and
     * `modules/site/mod_proclaim/tmpl` in the package.
     *
     * @param   string  $directory  A directory from LAYOUT_DIRECTORIES
     *
     * @return  string  Absolute path in this repository
     *
     * @since __DEPLOY_VERSION__
     */
    private static function repositoryPath(string $directory): string
    {
        $base = \dirname(__DIR__, 4);

        if (str_starts_with($directory, 'components/com_proclaim/tmpl/')) {
            return $base . '/site/tmpl/' . basename($directory);
        }

        if ($directory === 'modules/mod_proclaim/tmpl') {
            return $base . '/modules/site/mod_proclaim/tmpl';
        }

        return $base . '/' . $directory;
    }

    /**
     * What the package actually has on disk, by type.
     *
     * @return  array<int, array<int, string>>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function layoutsOnDisk(): array
    {
        $found = [];

        foreach (CwmtemplatecodeTable::LAYOUT_DIRECTORIES as $type => $directory) {
            $names = [];

            foreach (glob(self::repositoryPath($directory) . '/default_*.php') ?: [] as $file) {
                $names[] = substr(basename($file), \strlen('default_'), -\strlen('.php'));
            }

            sort($names);
            $found[$type] = $names;
        }

        return $found;
    }

    /**
     * ⚠️ Both directions. A shipped layout missing from the list is creatable
     * and can be clobbered; a listed name that is no longer shipped refuses a
     * filename for no reason. The first is how the 2022 folder rename went
     * unnoticed twice.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The shipped-layout list matches what the package actually ships')]
    public function testShippedLayoutListMatchesDisk(): void
    {
        $onDisk = self::layoutsOnDisk();
        $listed = CwmtemplatecodeTable::SHIPPED_LAYOUTS;

        // Not a silent pass over an empty scan.
        $this->assertGreaterThan(
            10,
            \count($onDisk, \COUNT_RECURSIVE) - \count($onDisk),
            'Too few layouts found on disk; the scan is broken, not the list.'
        );

        foreach ($onDisk as $type => $names) {
            $declared = $listed[$type] ?? [];
            sort($declared);

            $this->assertSame(
                $names,
                $declared,
                \sprintf(
                    "SHIPPED_LAYOUTS[%d] has drifted from %s.\n  on disk: %s\n  listed:  %s\n"
                    . 'A name on disk but not listed can be overwritten by a template code record.',
                    $type,
                    CwmtemplatecodeTable::LAYOUT_DIRECTORIES[$type],
                    implode(', ', $names) ?: '(none)',
                    implode(', ', $declared) ?: '(none)'
                )
            );
        }

        $this->assertSame(
            array_keys(CwmtemplatecodeTable::LAYOUT_DIRECTORIES),
            array_keys($listed),
            'Every template type with a layout directory needs an entry, even an empty one.'
        );
    }

    /**
     * Every shipped name, as (type, filename) pairs.
     *
     * @return  array<string, array{0: int, 1: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function shippedNameProvider(): array
    {
        $cases = [];

        foreach (CwmtemplatecodeTable::SHIPPED_LAYOUTS as $type => $names) {
            foreach ($names as $name) {
                $cases[\sprintf('type %d: %s', $type, $name)] = [$type, $name];
            }
        }

        return $cases;
    }

    /**
     * @param   int     $type  Template type
     * @param   string  $name  A layout the package ships for it
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('shippedNameProvider')]
    #[TestDox('A shipped layout name is refused for a new record')]
    public function testShippedNamesAreReserved(int $type, string $name): void
    {
        $this->assertTrue(
            CwmtemplatecodeTable::isShippedLayout($type, $name),
            \sprintf('%s is shipped for type %d but not recognised as such.', $name, $type)
        );

        $this->assertTrue(
            CwmtemplatecodeTable::isReservedFilename($type, $name),
            \sprintf('A new record could be named %s for type %d and overwrite the shipped layout.', $name, $type)
        );
    }

    /**
     * The seven names this issue was actually about — shipped, and creatable
     * before the fix because the hard-coded list of five did not mention them.
     *
     * @return  array<string, array{0: int, 1: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function previouslyCreatableProvider(): array
    {
        return [
            'sermons: simple2'     => [1, 'simple2'],
            'sermon: commentsform' => [2, 'commentsform'],
            'sermon: footer'       => [2, 'footer'],
            'sermon: footerlink'   => [2, 'footerlink'],
            'sermon: header'       => [2, 'header'],
            'teacher: cards'       => [4, 'cards'],
            'teacher: list'        => [4, 'list'],
        ];
    }

    /**
     * ⚠️ Named individually rather than folded into the provider above, so the
     * regression is visible as itself. Each of these could be created, and
     * deleting it removed a file `default.php` loads unconditionally.
     *
     * @param   int     $type  Template type
     * @param   string  $name  The name that used to be creatable
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('previouslyCreatableProvider')]
    #[TestDox('A name that used to be creatable over a shipped layout is refused')]
    public function testPreviouslyCreatableNamesAreNowRefused(int $type, string $name): void
    {
        $this->assertTrue(
            CwmtemplatecodeTable::isReservedFilename($type, $name),
            \sprintf('%s for type %d is still creatable and would overwrite the shipped layout.', $name, $type)
        );

        // And the file it would have overwritten is really there.
        $path = self::repositoryPath(CwmtemplatecodeTable::LAYOUT_DIRECTORIES[$type]) . '/default_' . $name . '.php';

        $this->assertFileExists(
            $path,
            'This test is meaningless if the layout it protects is not shipped.'
        );
    }

    /**
     * The five that have always been refused stay refused for every type,
     * including the types that ship no such file.
     *
     * ⚠️ This is the assertion that stops the list being "simplified" into
     * SHIPPED_LAYOUTS alone. Doing that would permit around twenty names that
     * are refused today — a change in the permissive direction that nothing
     * asked for.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The five long-reserved names stay refused for every type')]
    public function testLongReservedNamesStayReservedForEveryType(): void
    {
        $permitted = [];

        foreach (array_keys(CwmtemplatecodeTable::LAYOUT_DIRECTORIES) as $type) {
            foreach (['main', 'simple', 'custom', 'formheader', 'formfooter'] as $name) {
                if (!CwmtemplatecodeTable::isReservedFilename($type, $name)) {
                    $permitted[] = \sprintf('type %d: %s', $type, $name);
                }
            }
        }

        $this->assertSame(
            [],
            $permitted,
            "These were refused before and are now permitted:\n" . implode("\n", $permitted)
        );
    }

    /**
     * An ordinary name is still fine. Without this the fix could refuse
     * everything and every test above would still pass.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An ordinary layout name is not reserved')]
    public function testOrdinaryNamesAreNotReserved(): void
    {
        foreach (array_keys(CwmtemplatecodeTable::LAYOUT_DIRECTORIES) as $type) {
            foreach (['easy', 'my-layout', 'christmas2026'] as $name) {
                $this->assertFalse(
                    CwmtemplatecodeTable::isReservedFilename($type, $name),
                    \sprintf('%s was refused for type %d; ordinary names must still work.', $name, $type)
                );
            }
        }
    }

    /**
     * An unknown type ships nothing, so only the five apply — and it must not
     * error on the missing key.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An unknown template type ships no layouts')]
    public function testUnknownTypeShipsNothing(): void
    {
        $this->assertFalse(CwmtemplatecodeTable::isShippedLayout(99, 'footer'));
        $this->assertFalse(CwmtemplatecodeTable::isReservedFilename(99, 'footer'));
        $this->assertTrue(CwmtemplatecodeTable::isReservedFilename(99, 'main'));
    }
}
