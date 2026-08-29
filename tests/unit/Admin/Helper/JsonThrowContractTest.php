<?php

/**
 * Contract test: every JSON call in our own source must be able to fail loudly.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression test for #1558, tier A.
 *
 * `json_decode()` without `JSON_THROW_ON_ERROR` returns `null` on malformed
 * input. Nothing throws, so no `catch` can observe it and no amount of
 * error-handling review will find it — the call simply yields `null` and the
 * surrounding `is_array()` guard turns that into a default. Six sites were in
 * that state, and each one silently discarded real information: a version
 * reported as 0.0.0, media files dropped from playlist sync, a playlist's saved
 * settings replaced by defaults on the next save.
 *
 * The flag is what makes those failures observable at all, so it is the thing
 * worth pinning.
 *
 * ⚠️ This test tokenizes and matches parentheses. Do NOT rewrite it to scan a
 * fixed window of lines: argument lists in this codebase run past twenty lines
 * (`CwmpIconvert::insertMedia()`), and a windowed scan reports sites as
 * unguarded when the flag is simply further down. That exact mistake produced a
 * false finding of "8 encodes destroying data" while #1558 was being scoped.
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonThrowContractTest extends ProclaimTestCase
{
    /**
     * Directories of our own source. The Google/Guzzle/Monolog tree bundled
     * under Addons/Servers/Youtube/vendor is third-party and not ours to change.
     *
     * @var  string[]
     */
    private const ROOTS = ['admin/src', 'site/src', 'api/src'];

    /**
     * Individual files of ours that live outside those trees.
     *
     * ⚠️ The install script is the reason this exists. It sat outside every
     * root, so nothing held it to the contract and three bare calls collected
     * there — two of which wrote `''` over a row it was migrating, because
     * `json_encode()` returns false and `quote(false)` is an empty string.
     *
     * @var  string[]
     */
    private const FILES = ['proclaim.script.php'];

    /**
     * Calls that deliberately omit the flag, each with the reason it is safe.
     *
     * A `json_encode()` cannot fail on a value that PHP just handed us from a
     * successful `json_decode()`: it is already valid UTF-8, non-recursive, free
     * of NAN/INF, and the depth limit is symmetric. Adding the flag at these two
     * sites would suggest a failure mode that cannot occur.
     *
     * @var  array<string, string>
     */
    private const ALLOWED = [
        'admin/src/Table/CwmteacherTable.php' => 'Re-encodes an array produced by a successful json_decode() a few lines above; cannot fail.',
        'admin/src/Table/CwmpodcastTable.php' => 'Re-encodes an array produced by a successful json_decode() a few lines above; cannot fail.',
    ];

    /**
     * Every json_decode/json_encode call in our source, with its arguments
     * resolved by matching parentheses rather than counting lines.
     *
     * @return  array<int, array{file: string, line: int, fn: string, args: string}>
     */
    private function calls(): array
    {
        $root  = \dirname(__DIR__, 4);
        $found = [];

        $paths = [];

        foreach (self::ROOTS as $rel) {
            $dir = $root . '/' . $rel;

            if (!is_dir($dir)) {
                continue;
            }

            /** @var \SplFileInfo $file */
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
                $path = $file->getPathname();

                if ($file->getExtension() !== 'php' || str_contains($path, '/vendor/')) {
                    continue;
                }

                $paths[] = $path;
            }
        }

        foreach (self::FILES as $rel) {
            if (is_file($root . '/' . $rel)) {
                $paths[] = $root . '/' . $rel;
            }
        }

        foreach ($paths as $path) {
            $tokens = token_get_all((string) file_get_contents($path));
            $count  = \count($tokens);

            for ($i = 0; $i < $count; $i++) {
                $token = $tokens[$i];

                if (
                    !\is_array($token)
                    || $token[0] !== \T_STRING
                    || !\in_array($token[1], ['json_decode', 'json_encode'], true)
                ) {
                    continue;
                }

                $depth   = 0;
                $args    = '';
                $started = false;

                for ($j = $i + 1; $j < $count; $j++) {
                    $text = \is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];

                    if ($text === '(') {
                        $depth++;
                        $started = true;
                    } elseif ($text === ')') {
                        $depth--;

                        if ($depth === 0) {
                            break;
                        }
                    } elseif ($started) {
                        $args .= $text;
                    }
                }

                $found[] = [
                    'file' => str_replace($root . '/', '', $path),
                    'line' => $token[2],
                    'fn'   => $token[1],
                    'args' => $args,
                ];
            }
        }

        return $found;
    }

    #[TestDox('Every json_decode in our source can throw, so a malformed value is never silently null')]
    public function testEveryDecodeCanThrow(): void
    {
        $offenders = [];

        foreach ($this->calls() as $call) {
            if ($call['fn'] !== 'json_decode' || str_contains($call['args'], 'JSON_THROW_ON_ERROR')) {
                continue;
            }

            if (isset(self::ALLOWED[$call['file']])) {
                continue;
            }

            $offenders[] = $call['file'] . ':' . $call['line'];
        }

        $this->assertSame(
            [],
            $offenders,
            "json_decode() without JSON_THROW_ON_ERROR returns null on malformed input, which no catch can\n"
            . "observe. Add the flag and handle the failure, or add the file to self::ALLOWED with a reason."
        );
    }

    #[TestDox('Every json_encode whose result is stored can throw')]
    public function testEveryPersistedEncodeCanThrow(): void
    {
        $root      = \dirname(__DIR__, 4);
        $offenders = [];

        foreach ($this->calls() as $call) {
            if ($call['fn'] !== 'json_encode' || str_contains($call['args'], 'JSON_THROW_ON_ERROR')) {
                continue;
            }

            if (isset(self::ALLOWED[$call['file']])) {
                continue;
            }

            // "Stored" means the result reaches something durable: an object
            // property, a file, or a database write. A json_encode() feeding a
            // cache key, an echoed AJAX body or a data- attribute cannot corrupt
            // anything durable, and guarding those would be noise rather than
            // safety.
            //
            // ⚠️ The database arm is not decoration. `json_encode()` returns
            // false, `quote(false)` is `''`, and the row is written anyway — so
            // the damage is a blanked column on a record the code believed it had
            // migrated. Two calls in the install script had exactly that shape.
            $lines = file($root . '/' . $call['file']);
            $line  = $lines[$call['line'] - 1] ?? '';

            if (
                preg_match(
                    '/(\$this->[a-zA-Z_]+|\$[a-zA-Z_]+->[a-zA-Z_]+)\s*=\s*json_encode|file_put_contents\s*\([^,]+,\s*json_encode/',
                    $line
                )
                || preg_match('/->(quote|q)\s*\(\s*json_encode/', $line)
                || $this->encodedLocalIsPersisted($lines, $call['line'], $line)
            ) {
                $offenders[] = $call['file'] . ':' . $call['line'];
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "json_encode() returns false on failure. Assigned straight to a stored property that writes an\n"
            . "empty value, silently. Add the flag and refuse the write, or add the file to self::ALLOWED."
        );
    }

    #[TestDox('The allow-list only names files that exist and still omit the flag')]
    public function testAllowListIsNotStale(): void
    {
        $root  = \dirname(__DIR__, 4);
        $calls = $this->calls();

        foreach (self::ALLOWED as $file => $reason) {
            $this->assertFileExists($root . '/' . $file, 'Allow-listed file has moved or gone');
            $this->assertNotSame('', trim($reason), $file . ' must state why the omission is safe');

            $stillUnguarded = array_filter(
                $calls,
                static fn (array $c): bool => $c['file'] === $file && !str_contains($c['args'], 'JSON_THROW_ON_ERROR')
            );

            $this->assertNotEmpty(
                $stillUnguarded,
                $file . ' now guards every JSON call — remove it from self::ALLOWED so the entry cannot go stale'
            );
        }
    }

    #[TestDox('The scan reaches real calls, so a green result is not an empty sweep')]
    public function testScanIsNotVacuous(): void
    {
        $calls = $this->calls();

        $this->assertGreaterThan(300, \count($calls), 'Expected the scan to reach the whole component');

        $guarded = array_filter($calls, static fn (array $c): bool => str_contains($c['args'], 'JSON_THROW_ON_ERROR'));

        $this->assertGreaterThan(
            100,
            \count($guarded),
            'Expected many calls to carry the flag — if this drops, the argument extraction has broken'
        );
    }

    /**
     * Whether a local `$x = json_encode(...)` is quoted into a query below it.
     *
     * The regex arms above only see one line, so they miss the commonest shape
     * of all: encode into a local, then hand that local to `quote()` a few lines
     * down as part of an UPDATE. Looks ahead a short way for that variable
     * reaching a quote or a set().
     *
     * @param   string[]  $lines   Every line of the file
     * @param   int       $number  1-indexed line the call sits on
     * @param   string    $line    That line's text
     *
     * @return  bool
     */
    private function encodedLocalIsPersisted(array $lines, int $number, string $line): bool
    {
        if (!preg_match('/(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*json_encode/', $line, $m)) {
            return false;
        }

        $variable = preg_quote($m[1], '/');
        $end      = min(\count($lines), $number + 15);

        for ($i = $number; $i < $end; $i++) {
            if (preg_match('/->(quote|q|set|bind)\s*\([^)]*' . $variable . '\b/', $lines[$i])) {
                return true;
            }
        }

        return false;
    }

}
