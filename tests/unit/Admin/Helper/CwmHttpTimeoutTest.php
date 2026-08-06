<?php

/**
 * Unit tests asserting outbound HTTP calls carry a timeout.
 *
 * @package    Proclaim.Test
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1587.
 *
 * Joomla's curl transport sets CURLOPT_TIMEOUT only when one is supplied
 * (`if (isset($timeout))` in Transport/Curl.php), so a call that omits the
 * argument gets no timeout at all. These requests run synchronously inside
 * admin requests, so an unresponsive provider holds a PHP worker open with no
 * bound from this code.
 *
 * The issue named CwmpodcastIndexHelper::apiGet(). A sweep found the same
 * omission in CWMAddon::apiRequest(), which is the shared HTTP path for every
 * server addon -- eighteen call sites across Vimeo, Wistia and Resi.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmHttpTimeoutTest extends ProclaimTestCase
{
    /**
     * Files that make outbound HTTP calls through Joomla's HttpFactory.
     *
     * @return array<string, array{0: string}>
     */
    public static function httpCallerProvider(): array
    {
        return [
            'podcast index' => ['admin/src/Helper/CwmpodcastIndexHelper.php'],
            'addon base'    => ['admin/src/Addons/CWMAddon.php'],
            'ai helper'     => ['admin/src/Helper/CwmaiHelper.php'],
        ];
    }

    /**
     * Every HTTP verb call in these files must pass something in the timeout
     * position -- a constant, or a variable carrying one.
     *
     * @param   string  $relative  Repo-relative file path
     */
    #[DataProvider('httpCallerProvider')]
    #[TestDox('Every outbound HTTP call passes a timeout')]
    public function testEveryHttpCallHasATimeout(string $relative): void
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 4) . '/' . $relative);

        // Normalise multi-line calls onto one line so the argument list can be
        // inspected whole; several of these span four or five lines.
        $flat = preg_replace('/\s+/', ' ', $source);

        preg_match_all(
            '/\$http->(get|post|put|patch|delete)\((.*?)\);/',
            (string) $flat,
            $matches,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($matches, 'Expected to find HTTP calls in ' . $relative);

        foreach ($matches as $call) {
            [$whole, $verb, $args] = $call;

            $this->assertMatchesRegularExpression(
                '/(TIMEOUT|\$timeout|,\s*\d+\s*$)/',
                $args,
                $verb . '() in ' . $relative . ' has no timeout — curl then waits forever — see #1587: '
                . substr($whole, 0, 90)
            );
        }
    }

    /**
     * The value has to be a sane bound, not a placeholder.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function timeoutConstantProvider(): array
    {
        return [
            'podcast index' => ['admin/src/Helper/CwmpodcastIndexHelper.php', 'HTTP_TIMEOUT'],
            'addon base'    => ['admin/src/Addons/CWMAddon.php', 'HTTP_TIMEOUT'],
        ];
    }

    /**
     * @param   string  $relative  Repo-relative file path
     * @param   string  $constant  Constant name
     */
    #[DataProvider('timeoutConstantProvider')]
    #[TestDox('The timeout constant is declared and bounded')]
    public function testTimeoutConstantIsSane(string $relative, string $constant): void
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 4) . '/' . $relative);

        $this->assertMatchesRegularExpression(
            '/const int ' . $constant . ' = (\d+);/',
            $source,
            $constant . ' must be declared in ' . $relative
        );

        preg_match('/const int ' . $constant . ' = (\d+);/', $source, $m);
        $seconds = (int) ($m[1] ?? 0);

        $this->assertGreaterThan(0, $seconds, 'A zero timeout means no timeout in curl');
        $this->assertLessThanOrEqual(
            60,
            $seconds,
            'A minute is already long for a synchronous admin request'
        );
    }

    /**
     * The addon helper is shared, so a caller with a slower provider needs to
     * be able to raise the bound without abandoning it.
     */
    #[TestDox('The addon HTTP helper lets callers override the timeout')]
    public function testAddonRequestTimeoutIsOverridable(): void
    {
        $params = (new \ReflectionMethod(
            \CWM\Component\Proclaim\Administrator\Addons\CWMAddon::class,
            'apiRequest'
        ))->getParameters();

        $names = array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $params);

        $this->assertContains('timeout', $names, 'A shared helper must not force one bound on every provider');

        $timeout = $params[array_search('timeout', $names, true)];

        $this->assertTrue($timeout->isDefaultValueAvailable(), 'Existing call sites must keep working unchanged');
        $this->assertSame(20, $timeout->getDefaultValue());
    }
}
