<?php

/**
 * What Proclaim needs from lib_cwmscripture, asserted against the pinned library.
 *
 * The library ships its own consumer contract, but it cannot see who calls it,
 * so that list is maintained by hand and covers one class. This is the other
 * half: the consumer declaring what it actually uses, checked against whatever
 * library the submodule is pinned to. It fails in Proclaim's CI at the moment a
 * pin is bumped — which is when the answer is still cheap.
 *
 * lib 1.1.13 removed registerLogger(), setCacheTtl(), isLastErrorTransient(),
 * getDatabase() and two properties in a namespace refactor. Nothing in either
 * repository failed. It surfaced two releases later as a fatal on any front-end
 * page rendering scripture, and took 1.1.14 through 1.1.17 to put back.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Bible;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Library\Scripture\Bible\AbstractBibleProvider;
use CWM\Library\Scripture\Bible\BibleProviderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * What Proclaim needs from lib_cwmscripture, asserted against the pinned library.
 *
 * @since  10.5.9
 */
class ScriptureLibraryContractTest extends ProclaimTestCase
{
    /**
     * Trees that may call into the library.
     *
     * @var    list<string>
     * @since  10.5.9
     */
    private const SOURCE_ROOTS = ['site/src', 'admin/src', 'api/src'];

    /**
     * Vendored trees under those roots. `admin/src/Addons/Servers/Youtube`
     * carries google/apiclient — 598 of the 956 files a naive walk reads, and
     * none of them ours.
     *
     * @var    list<string>
     * @since  10.5.9
     */
    private const SKIP = ['/vendor/', '/node_modules/'];

    /**
     * Instance methods Proclaim calls on a provider with no method_exists()
     * guard, and the type each is declared on.
     *
     * An interface method is the safer kind: removing one breaks the library's
     * own implementations, so its own tests fail first. A method that exists
     * only on AbstractBibleProvider has nothing holding it in place, which is
     * the shape that went missing in 1.1.13.
     *
     * @var    array<string, class-string>
     * @since  10.5.9
     */
    private const UNGUARDED_CALLS = [
        'getPassage'           => BibleProviderInterface::class,
        'getName'              => BibleProviderInterface::class,
        'isLastErrorTransient' => AbstractBibleProvider::class,
    ];

    /**
     * Instance methods called only behind method_exists(), so the component can
     * ship ahead of a library release.
     *
     * These must NOT be asserted to exist — the guard is the contract. What is
     * asserted is that the guard is still there, because deleting it turns a
     * graceful degradation into a fatal.
     *
     * @var    list<string>
     * @since  10.5.9
     */
    private const GUARDED_CALLS = ['setCacheTtl', 'getPassageFor'];

    /**
     * Every `LibraryClass::symbol` Proclaim writes, found in the source.
     *
     * Derived rather than listed: a hand-maintained list is what let the last
     * removal through.
     *
     * @return  array<string, array{0: string, 1: string}>  Each distinct reference's fully qualified class, then its
     *                                                      symbol, keyed by the two joined with ::
     *
     * @since   10.5.9
     */
    public static function staticReferences(): array
    {
        return self::referencesIn(self::sourceFiles());
    }

    /**
     * Every `LibraryClass::symbol` the given files write, each file read against
     * its own imports only.
     *
     * @param   string[]  $files  Paths of the PHP files to read
     *
     * @return  array<string, array{0: string, 1: string}>  Each distinct reference's fully qualified class, then its
     *                                                      symbol, keyed by the two joined with ::
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function referencesIn(array $files): array
    {
        $found = [];

        foreach ($files as $file) {
            $source = self::codeOf($file);

            // Short name => FQCN, from this file's own imports.
            $classes = [];
            preg_match_all('~^use (CWM\\\\Library\\\\Scripture\\\\[\\w\\\\]+);~m', $source, $uses);

            foreach ($uses[1] as $fqcn) {
                $classes[substr((string) strrchr($fqcn, '\\'), 1)] = $fqcn;
            }

            if ($classes === []) {
                continue;
            }

            $names = implode('|', array_map('preg_quote', array_keys($classes)));

            preg_match_all('~\\b(' . $names . ')::(\\w+)~', $source, $refs, PREG_SET_ORDER);

            foreach ($refs as [, $short, $symbol]) {
                if (!isset($classes[$short]) || $symbol === 'class') {
                    continue;
                }

                $found[$classes[$short] . '::' . $symbol] = [$classes[$short], $symbol];
            }
        }

        return $found;
    }

    /**
     * Files under the scanned roots.
     *
     * @return  string[]  Absolute paths of every PHP file under SOURCE_ROOTS, vendored trees skipped
     *
     * @since   10.5.9
     */
    private static function sourceFiles(): array
    {
        $files = [];
        $root  = \dirname(__DIR__, 4);

        foreach (self::SOURCE_ROOTS as $dir) {
            if (!is_dir($root . '/' . $dir)) {
                continue;
            }

            $walker = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root . '/' . $dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($walker as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());

                foreach (self::SKIP as $fragment) {
                    if (str_contains($path, $fragment)) {
                        continue 2;
                    }
                }

                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * A file's code with comments and string literals removed.
     *
     * `Cwmshowscripture` carries a docblock reading `LibraryVersion::VERSION`, to
     * explain why it detects a capability instead of checking a version. Matching
     * raw text would read that as a reference and fail on a class Proclaim does
     * not even import.
     *
     * ⚠️ String literals are kept. `method_exists($provider, 'setCacheTtl')`
     * holds the method name in one, so stripping strings makes every guard
     * invisible and the guard assertion fails against correct code.
     *
     * @param   string  $file  Path to read
     *
     * @return  string  Code tokens only
     *
     * @since   10.5.9
     */
    private static function codeOf(string $file): string
    {
        $out = '';

        foreach (token_get_all((string) file_get_contents($file)) as $token) {
            if (!\is_array($token)) {
                $out .= $token;

                continue;
            }

            if (\in_array($token[0], [T_COMMENT, T_DOC_COMMENT, T_INLINE_HTML], true)) {
                continue;
            }

            $out .= $token[1];
        }

        return $out;
    }

    /**
     * Every `$provider->method(` name the source calls.
     *
     * @return  string[]  Each distinct method called on a variable whose name ends in provider or Provider
     *
     * @since   10.5.9
     */
    private static function providerCalls(): array
    {
        $calls = [];

        foreach (self::sourceFiles() as $file) {
            preg_match_all(
                '~\\$\\w*(?:provider|Provider)\\s*->\\s*(\\w+)\\s*\\(~',
                self::codeOf($file),
                $m
            );

            foreach ($m[1] as $method) {
                $calls[$method] = $method;
            }
        }

        return array_values($calls);
    }

    /**
     * The scan has to find something, or every other test here passes vacuously.
     *
     * @return  void
     *
     * @since   10.5.9
     */
    #[TestDox('the source scan finds the calls it is meant to check')]
    public function testTheScanFoundSomething(): void
    {
        self::assertNotEmpty(self::sourceFiles(), 'No source files were scanned.');
        self::assertNotEmpty(self::staticReferences(), 'No LibraryClass::symbol references were found.');
        self::assertNotEmpty(self::providerCalls(), 'No $provider->method() calls were found.');
    }

    /**
     * A file is read against its own imports only: a short name an earlier file
     * imports from the library is not credited to the library in a later file
     * that doesn't import it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Random\RandomException
     */
    #[TestDox('each file is read against its own imports only')]
    public function testEachFileIsReadAgainstItsOwnImports(): void
    {
        $dir = sys_get_temp_dir() . '/proclaim-scripture-contract-' . bin2hex(random_bytes(4));
        mkdir($dir);

        $importing = $dir . '/importing.php';
        $other     = $dir . '/other.php';

        file_put_contents($importing, "<?php\n\nuse CWM\\Library\\Scripture\\Helper\\ScriptureHelper;\n\nScriptureHelper::getBookName(1);\n");
        file_put_contents($other, "<?php\n\nnamespace Elsewhere;\n\nScriptureHelper::somethingElse();\n");

        try {
            $references = self::referencesIn([$importing, $other]);
        } finally {
            unlink($importing);
            unlink($other);
            rmdir($dir);
        }

        self::assertSame(['CWM\Library\Scripture\Helper\ScriptureHelper::getBookName'], array_keys($references));
    }

    /**
     * Each library class and symbol Proclaim references exists in the pinned
     * library.
     *
     * @param   string  $class   Library class
     * @param   string  $symbol  Method or constant Proclaim references
     *
     * @return  void
     *
     * @since   10.5.9
     */
    #[DataProvider('staticReferences')]
    #[TestDox('$class::$symbol still exists in the pinned library')]
    public function testStaticReferencesResolve(string $class, string $symbol): void
    {
        self::assertTrue(class_exists($class), $class . ' could not be loaded from the pinned library.');

        self::assertTrue(
            method_exists($class, $symbol) || \defined($class . '::' . $symbol),
            $class . '::' . $symbol . ' is written in Proclaim but no longer exists in the pinned library. '
            . 'Either the pin moved past a removal, or the call needs updating.'
        );
    }

    /**
     * Every unguarded provider call exists on the type it is called against.
     *
     * @return  void
     *
     * @since   10.5.9
     */
    #[TestDox('every unguarded provider call exists on the type it is called against')]
    public function testUnguardedCallsExist(): void
    {
        foreach (self::UNGUARDED_CALLS as $method => $type) {
            self::assertTrue(
                method_exists($type, $method),
                $method . '() is called on a provider with no method_exists() guard, so a library that lacks '
                . 'it is a fatal, not a degradation. Either restore it in the library or guard the call.'
            );
        }
    }

    /**
     * Every guarded call keeps its method_exists() guard.
     *
     * @return  void
     *
     * @since   10.5.9
     */
    #[TestDox('every guarded call still has its guard')]
    public function testGuardedCallsKeepTheirGuard(): void
    {
        foreach (self::GUARDED_CALLS as $method) {
            $callSites  = 0;
            $guardSites = 0;

            foreach (self::sourceFiles() as $file) {
                $source      = self::codeOf($file);
                $callSites += preg_match_all('~\\$\\w*(?:provider|Provider)\\s*->\\s*' . $method . '\\s*\\(~', $source);
                $guardSites += preg_match_all('~method_exists\\([^)]*,\\s*\'' . $method . '\'\\s*\\)~', $source);
            }

            self::assertGreaterThanOrEqual(
                $callSites,
                $guardSites,
                $method . '() is called ' . $callSites . ' time(s) but guarded ' . $guardSites . '. It is not on '
                . 'BibleProviderInterface, so a library predating it has no such method and the call is a fatal. '
                . 'The guard is what lets Proclaim ship ahead of a library release.'
            );
        }
    }

    /**
     * Keeps the two lists above honest in both directions.
     *
     * @return  void
     *
     * @since   10.5.9
     */
    #[TestDox('the guarded and unguarded lists match what the source actually calls')]
    public function testTheListsMatchTheSource(): void
    {
        $called   = self::providerCalls();
        $declared = array_merge(array_keys(self::UNGUARDED_CALLS), self::GUARDED_CALLS);

        foreach ($declared as $method) {
            self::assertContains(
                $method,
                $called,
                $method . ' is declared here but nothing calls it any more. Drop it, so this file keeps '
                . 'describing the code rather than its history.'
            );
        }

        foreach ($called as $method) {
            self::assertContains(
                $method,
                $declared,
                $method . '() is called on a provider but is listed neither as guarded nor as unguarded. '
                . 'Add it, so a later library release cannot remove it unnoticed.'
            );
        }
    }
}
