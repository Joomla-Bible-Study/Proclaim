<?php

/**
 * Guards the default sort direction in every admin list model.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Every `list.direction` fallback must be a valid SQL sort direction.
 *
 * Two models shipped with 'acs' / 'ACS' — a transposition of 'asc'. It was
 * invisible in the admin UI, where the request always supplies a direction, so
 * the default never applied. The REST API supplies none, so the fallback reached
 * the query as `ORDER BY col ACS`, which is a syntax error: the query returned
 * false and the endpoint 500'd.
 *
 * This scans source rather than executing the models because the bug lives in a
 * default argument that only a request-less caller ever reaches.
 *
 * @since  __DEPLOY_VERSION__
 */
class ListDirectionDefaultTest extends ProclaimTestCase
{
    /**
     * Directions MySQL will accept, plus the empty string some models use to mean
     * "unordered" and let the query builder decide.
     *
     * @var string[]
     */
    private const VALID = ['asc', 'desc', ''];

    public function testEveryListDirectionDefaultIsValidSql(): void
    {
        $offenders = [];

        foreach ($this->modelFiles() as $file) {
            $source = (string) file_get_contents($file);

            // Match the default in get('list.direction', '<default>').
            preg_match_all(
                '/[\'"]list\.direction[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]/',
                $source,
                $matches
            );

            foreach ($matches[1] as $default) {
                if (!\in_array(strtolower($default), self::VALID, true)) {
                    $offenders[] = basename($file) . " => '" . $default . "'";
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Invalid default sort direction(s) — these produce an SQL syntax error when no\n"
            . "direction is supplied, as happens on every API request:\n  "
            . implode("\n  ", $offenders)
        );
    }

    /**
     * The scan must actually find directions, or the test above passes vacuously.
     */
    public function testScanFindsListDirectionDefaults(): void
    {
        $found = 0;

        foreach ($this->modelFiles() as $file) {
            $found += preg_match_all(
                '/[\'"]list\.direction[\'"]\s*,\s*[\'"][^\'"]*[\'"]/',
                (string) file_get_contents($file)
            );
        }

        $this->assertGreaterThan(5, $found, 'Expected several list.direction defaults across the models');
    }

    /**
     * @return string[]
     */
    private function modelFiles(): array
    {
        $dir   = \dirname(__DIR__, 4) . '/admin/src/Model';
        $files = glob($dir . '/*.php') ?: [];

        $this->assertNotEmpty($files, "No admin models found at {$dir}");

        return $files;
    }
}
