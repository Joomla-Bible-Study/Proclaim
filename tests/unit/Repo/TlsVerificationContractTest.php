<?php

/**
 * Nothing Proclaim ships may turn off TLS certificate verification.
 *
 * The podcast enclosure probe did (`CURLOPT_SSL_VERIFYPEER => false`), which let
 * anyone on the path answer for the media host and dictate the enclosure length
 * and type written into the feed. It is a one-line change to make and an easy
 * one to reintroduce while debugging a certificate problem, so it is asserted
 * across the tree rather than at the call site that happened to have it.
 *
 * Raw cURL itself is not banned: CwmmediaStreamer needs CURLOPT_RESOLVE to pin
 * a validated IP against DNS rebinding, which Joomla's HTTP client cannot
 * express. It keeps verification on, which is the property that matters.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class TlsVerificationContractTest extends ProclaimTestCase
{
    /**
     * Source trees that ship.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const ROOTS = ['admin/src', 'site/src', 'api/src', 'plugins', 'modules'];

    /**
     * Third-party code we neither wrote nor ship-audit here.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const SKIP = ['/vendor/', '/node_modules/'];

    /**
     * @return  string[]  Absolute paths of every PHP file to inspect.
     * @since __DEPLOY_VERSION__
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
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('No shipped file disables TLS peer or host verification')]
    public function testNothingDisablesTlsVerification(): void
    {
        $base     = \dirname(__DIR__, 3);
        $offences = [];

        foreach (self::sourceFiles() as $path) {
            $source = file_get_contents($path);

            if ($source === false) {
                continue;
            }

            // Either the setopt form or the array form, set to a falsy value.
            if (
                preg_match('/CURLOPT_SSL_VERIFYPEER\s*(,|=>)\s*(false|0)\b/i', $source)
                || preg_match('/CURLOPT_SSL_VERIFYHOST\s*(,|=>)\s*(false|0)\b/i', $source)
                || preg_match('/[\'"]verify[\'"]\s*=>\s*false\b/i', $source)
            ) {
                $offences[] = str_replace($base . '/', '', $path);
            }
        }

        $this->assertSame(
            [],
            $offences,
            "TLS verification must stay on. Use Joomla's HttpFactory, which inherits the CA bundle and proxy settings."
        );
    }

    /**
     * A scan that matches nothing proves nothing; this pins that the pattern
     * would actually fire.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The scan reads real files and its pattern matches a disabling line')]
    public function testScanIsNotVacuous(): void
    {
        $this->assertGreaterThan(200, \count(self::sourceFiles()), 'The scan should see the shipped source tree');

        $this->assertSame(
            1,
            preg_match('/CURLOPT_SSL_VERIFYPEER\s*(,|=>)\s*(false|0)\b/i', 'curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);'),
            'The pattern must match the form the podcast probe used'
        );
    }
}
