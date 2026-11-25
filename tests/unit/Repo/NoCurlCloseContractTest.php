<?php

/**
 * Nothing Proclaim ships may call curl_close().
 *
 * curl_close() has had no effect since PHP 8.0, when cURL handles became
 * objects that are freed once nothing refers to them, and PHP 8.5 deprecates
 * it. CwmmediaStreamer still called it, so every streamed download raised a
 * deprecation on 8.5. It is an idiom older cURL code and snippets still carry,
 * so the rule is checked across the whole tree, not just at the one call
 * site. To close a handle early, unset() the variable holding it.
 *
 * The scan works on PHP tokens, so a comment that names curl_close() is not
 * an offence.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since 10.7.8
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Nothing Proclaim ships calls curl_close(), which PHP 8.5 deprecates.
 *
 * @since  10.7.8
 */
class NoCurlCloseContractTest extends ProclaimTestCase
{
    /**
     * Source trees that ship.
     *
     * @var    string[]
     * @since 10.7.8
     */
    private const ROOTS = ['admin/src', 'site/src', 'api/src', 'plugins', 'modules'];

    /**
     * Third-party code we neither wrote nor ship-audit here.
     *
     * @var    string[]
     * @since 10.7.8
     */
    private const SKIP = ['/vendor/', '/node_modules/'];

    /**
     * Every PHP file the contract covers.
     *
     * @return  string[]  Absolute paths of every PHP file to inspect.
     *
     * @since   10.7.8
     */
    private static function sourceFiles(): array
    {
        $base  = \dirname(__DIR__, 3);
        $files = [];

        foreach (self::ROOTS as $root) {
            if (!is_dir($base . '/' . $root)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . '/' . $root));

            foreach ($it as $file) {
                $path = $file->getPathname();

                if (!str_ends_with($path, '.php')) {
                    continue;
                }

                foreach (self::SKIP as $skip) {
                    if (str_contains($path, $skip)) {
                        continue 2;
                    }
                }

                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * Whether PHP source calls curl_close(), ignoring comments and strings.
     *
     * @param   string  $source  PHP source code.
     *
     * @return  bool  Whether the source calls curl_close(); a same-named method or a declaration does not count.
     *
     * @since   10.7.8
     */
    private static function callsCurlClose(string $source): bool
    {
        $tokens = token_get_all($source);
        $count  = \count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (!\is_array($token) || !\in_array($token[0], [T_STRING, T_NAME_FULLY_QUALIFIED], true)) {
                continue;
            }

            if (strtolower(ltrim($token[1], '\\')) !== 'curl_close') {
                continue;
            }

            // A call to the function: the next code token is "(", and the one
            // before is not ->, ?->, :: or "function" (a method or a declaration).
            $next = $i + 1;

            while ($next < $count && \is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE) {
                $next++;
            }

            $prev = $i - 1;

            while ($prev >= 0 && \is_array($tokens[$prev]) && $tokens[$prev][0] === T_WHITESPACE) {
                $prev--;
            }

            $before = $prev >= 0 && \is_array($tokens[$prev]) ? $tokens[$prev][0] : null;

            if (
                $next < $count
                && $tokens[$next] === '('
                && !\in_array($before, [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * No shipped file calls curl_close().
     *
     * @return  void
     *
     * @since   10.7.8
     */
    #[TestDox('No shipped file calls curl_close(), which PHP 8.5 deprecates')]
    public function testNothingCallsCurlClose(): void
    {
        $base     = \dirname(__DIR__, 3);
        $offences = [];

        foreach (self::sourceFiles() as $path) {
            $source = file_get_contents($path);

            if ($source !== false && self::callsCurlClose($source)) {
                $offences[] = str_replace($base . '/', '', $path);
            }
        }

        $this->assertSame(
            [],
            $offences,
            'curl_close() does nothing since PHP 8.0 and is deprecated in 8.5. unset() the handle to close it early.'
        );
    }

    /**
     * A scan that matches nothing proves nothing; this pins that the detector
     * fires on a call and stays quiet on comments and look-alikes.
     *
     * @return  void
     *
     * @since   10.7.8
     */
    #[TestDox('The scan reads real files and tells a call from a mention')]
    public function testScanIsNotVacuous(): void
    {
        $this->assertGreaterThan(200, \count(self::sourceFiles()), 'The scan should see the shipped source tree');

        $this->assertTrue(self::callsCurlClose('<?php curl_close($ch);'));
        $this->assertTrue(self::callsCurlClose('<?php \curl_close( $ch );'));
        $this->assertFalse(self::callsCurlClose('<?php // curl_close($ch) has had no effect since PHP 8.0'));
        $this->assertFalse(self::callsCurlClose('<?php $note = "curl_close($ch)";'));
        $this->assertFalse(self::callsCurlClose('<?php $pool->curl_close($ch);'));
        $this->assertFalse(self::callsCurlClose('<?php $pool?->curl_close($ch);'));
        $this->assertFalse(self::callsCurlClose('<?php function curl_close($ch) {}'));
    }
}
