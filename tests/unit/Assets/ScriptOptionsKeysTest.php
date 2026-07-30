<?php

/**
 * Script-options keys are a contract between PHP and JS, and nothing enforces it.
 *
 * The scripture version switcher shipped broken for exactly this reason: the JS
 * moved into lib_cwmscripture and started reading `cwmscripture.options`, while
 * com_proclaim kept publishing `com_proclaim.scripture`. Joomla.getOptions()
 * returns undefined for an unknown key, the switcher's handler saw no ajaxUrl and
 * returned early, and the control rendered as an inert dropdown. No error, no
 * failing test, no log line outside debug mode.
 *
 * A published key that nothing reads is the signature of that bug, so that is
 * what this asserts.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Assets;

use PHPUnit\Framework\TestCase;

class ScriptOptionsKeysTest extends TestCase
{
    /**
     * Repository root.
     */
    private static function root(): string
    {
        return \dirname(__DIR__, 3);
    }

    /**
     * Keys that are published deliberately without a JS reader in this
     * repository. Add sparingly, with a reason — each entry is a hole in the
     * check.
     *
     * @var array<string, string>
     */
    private const ALLOWED_ORPHANS = [
        // Read by Joomla core's own scripts, not ours.
        'joomla.jtext'           => 'consumed by core',
        'content-select-on-load' => 'consumed by core modal content-select JS',
        'guidedtours'            => 'consumed by com_guidedtours JS',
    ];

    /**
     * PHP roots that publish options.
     *
     * @var string[]
     */
    private const PHP_ROOTS = ['site', 'admin', 'modules', 'plugins'];

    /**
     * JS roots that consume them: our own sources, plus the library's, since the
     * library's scripts are the ones com_proclaim enqueues.
     *
     * @var string[]
     */
    private const JS_ROOTS = [
        'build/media_source/js',
        'libraries/lib_cwmscripture/build/media_source/js',
        'plugins/content/scripturelinks/libraries/lib_cwmscripture/build/media_source/js',
    ];

    /**
     * Every key passed to addScriptOptions() in PHP, mapped to the files doing it.
     *
     * @return array<string, list<string>>
     */
    private static function publishedKeys(): array
    {
        $found = [];

        foreach (self::PHP_ROOTS as $root) {
            $dir = self::root() . '/' . $root;

            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (strtolower($file->getExtension()) !== 'php') {
                    continue;
                }

                $path = $file->getPathname();

                // Skip vendored trees — they are not ours to keep in step.
                if (str_contains($path, '/vendor/') || str_contains($path, '/node_modules/')) {
                    continue;
                }

                $source = (string) file_get_contents($path);

                // Matches addScriptOptions('key', ...) with either quote style.
                // A variable key cannot be checked statically, and there are none.
                if (preg_match_all('/addScriptOptions\(\s*[\'"]([^\'"]+)[\'"]/', $source, $m) === 0) {
                    continue;
                }

                foreach ($m[1] as $key) {
                    $found[$key][] = str_replace(self::root() . '/', '', $path);
                }
            }
        }

        return $found;
    }

    /**
     * Every key read via Joomla.getOptions() in JS.
     *
     * @return list<string>
     */
    private static function consumedKeys(): array
    {
        $found = [];

        // Two shapes count as consuming a key:
        //
        //  1. A direct read: getOptions('k'), getOptions?.('k'),
        //     Joomla?.getOptions?.('k'). The optional-chaining form is real —
        //     layout-editor.es6.js uses it, and a regex without it reported live
        //     keys as orphans.
        //  2. An indirect read: `optionsKey: 'k'` in a field definition, which
        //     layout-editor.es6.js later hands to populateFromOptionsKey() ->
        //     Joomla.getOptions(optionsKey). Static analysis cannot follow the
        //     indirection, but the literal is right there, so match it.
        $patterns = [
            '/getOptions\s*\??\.?\(\s*[\'"]([^\'"]+)[\'"]/',
            '/optionsKey\s*:\s*[\'"]([^\'"]+)[\'"]/',
        ];

        // JS sources, walked recursively.
        foreach (self::JS_ROOTS as $root) {
            $dir = self::root() . '/' . $root;

            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (strtolower($file->getExtension()) !== 'js') {
                    continue;
                }

                $source = (string) file_get_contents($file->getPathname());

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $source, $m) > 0) {
                        foreach ($m[1] as $key) {
                            $found[] = $key;
                        }
                    }
                }
            }
        }

        // PHP too: several admin templates read their own options from an inline
        // <script> block rather than a built asset.
        foreach (self::PHP_ROOTS as $root) {
            $dir = self::root() . '/' . $root;

            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (strtolower($file->getExtension()) !== 'php') {
                    continue;
                }

                $path = $file->getPathname();

                if (str_contains($path, '/vendor/') || str_contains($path, '/node_modules/')) {
                    continue;
                }

                $source = (string) file_get_contents($path);

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $source, $m) > 0) {
                        foreach ($m[1] as $key) {
                            $found[] = $key;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * The library's JS is a submodule. CI checks out recursively, but a working
     * copy without `git submodule update` would report every cwmscripture.* key as
     * an orphan — a failure that reads like a broken feature rather than a missing
     * checkout. Skip instead of lying.
     */
    private static function skipWithoutLibrarySources(): ?string
    {
        foreach (self::JS_ROOTS as $root) {
            if (str_contains($root, 'lib_cwmscripture') && is_dir(self::root() . '/' . $root)) {
                return null;
            }
        }

        return 'lib_cwmscripture sources are not checked out — run: git submodule update --init --recursive';
    }

    public function testEveryPublishedKeyHasAReader(): void
    {
        if (($reason = self::skipWithoutLibrarySources()) !== null) {
            $this->markTestSkipped($reason);
        }

        $published = self::publishedKeys();
        $consumed  = self::consumedKeys();

        $this->assertNotEmpty($published, 'Expected to find addScriptOptions() calls.');
        $this->assertNotEmpty($consumed, 'Expected to find Joomla.getOptions() calls — did the JS roots move?');

        $orphans = [];

        foreach ($published as $key => $files) {
            if (isset(self::ALLOWED_ORPHANS[$key]) || \in_array($key, $consumed, true)) {
                continue;
            }

            $orphans[$key] = $files;
        }

        if ($orphans !== []) {
            $lines = [];

            foreach ($orphans as $key => $files) {
                $lines[] = \sprintf('  %s  (published by %s)', $key, implode(', ', array_unique($files)));
            }

            $this->fail(
                "These script-options keys are published by PHP but read by no JS in this repository:\n"
                . implode("\n", $lines)
                . "\n\nEither the JS reads a different key — which means the feature is silently inert, "
                . "as the scripture switcher was — or the wiring is dead and should be removed."
            );
        }
    }

    /**
     * The switcher's specific contract, spelled out so a rename cannot quietly
     * reintroduce the original bug.
     */
    public function testScriptureOptionsUseTheLibraryKey(): void
    {
        $published = self::publishedKeys();

        $this->assertArrayHasKey(
            'cwmscripture.options',
            $published,
            'Nothing publishes cwmscripture.options, which is the only key '
            . 'lib_cwmscripture\'s scripture-switcher.es6.js reads for its ajaxUrl. '
            . 'Without it the switcher renders and does nothing.'
        );

        $this->assertArrayNotHasKey(
            'com_proclaim.scripture',
            $published,
            'com_proclaim.scripture is the legacy key from before the JS moved into '
            . 'lib_cwmscripture. The switcher does not read it (the tooltip only does via '
            . 'a fallback), so publishing it means the switcher is inert.'
        );
    }
}
