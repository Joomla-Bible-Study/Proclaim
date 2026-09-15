<?php

/**
 * Unit tests for CwmprotectedStorage::containsMedia()
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedStorage;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #2046.
 *
 * This predicate decides whether Proclaim says anything at all about the
 * protected directory. When it answers "empty", the media list suppresses its
 * banner and System Health reports, in as many words, that nothing is exposed.
 *
 * ⚠️ It used to scan one level. Media in a subdirectory counted as nothing, so
 * a folder holding restricted files could be described as empty while the deny
 * rules were unproven — a confident wrong answer, which is worse than not
 * checking. Nothing moves files there yet, so every file in that folder was
 * placed by hand, and hand-placement is exactly what uses subdirectories.
 *
 * Exercised against real directories in a temp tree: the behaviour under test
 * is a filesystem walk, and a walk cannot be asserted against a folder whose
 * location is fixed to JPATH_ROOT.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmprotectedStorage::class)]
class CwmprotectedStorageScanTest extends ProclaimTestCase
{
    private string $dir = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/cwm-protected-' . bin2hex(random_bytes(6));
        mkdir($this->dir, 0o755, true);

        // Every real protected directory carries these and nothing else until
        // someone stores media. They must never count as media themselves.
        file_put_contents($this->dir . '/.htaccess', "Deny from all\n");
        file_put_contents($this->dir . '/web.config', "<configuration/>\n");
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->dir);

        parent::tearDown();
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . '/' . $entry;
            is_dir($path) ? $this->removeTree($path) : unlink($path);
        }

        rmdir($dir);
    }

    #[TestDox('A folder holding only its own guard files is empty')]
    public function testGuardFilesAloneAreNotMedia(): void
    {
        // ⚠️ Positive control for every "holds media" assertion below. If the
        // walk counted the guards, every one of them would pass without the
        // recursion being right, or indeed present.
        $this->assertFalse(
            CwmprotectedStorage::containsMedia($this->dir),
            'The directory\'s own .htaccess and web.config were counted as stored media.'
        );
    }

    #[TestDox('A file at the top level is found')]
    public function testTopLevelFileIsFound(): void
    {
        file_put_contents($this->dir . '/sermon.mp3', 'x');

        $this->assertTrue(CwmprotectedStorage::containsMedia($this->dir));
    }

    #[TestDox('A file in a subdirectory is found')]
    public function testFileInSubdirectoryIsFound(): void
    {
        mkdir($this->dir . '/2026', 0o755, true);
        file_put_contents($this->dir . '/2026/sermon.mp3', 'x');

        $this->assertTrue(
            CwmprotectedStorage::containsMedia($this->dir),
            'Media one folder down was reported as an empty directory. System Health then states '
            . 'that nothing is exposed while restricted files sit there.'
        );
    }

    #[TestDox('A file several folders down is found')]
    public function testDeeplyNestedFileIsFound(): void
    {
        mkdir($this->dir . '/2026/summer/john', 0o755, true);
        file_put_contents($this->dir . '/2026/summer/john/sermon.mp3', 'x');

        $this->assertTrue(CwmprotectedStorage::containsMedia($this->dir));
    }

    #[TestDox('Guard files copied into a subfolder are still not media')]
    public function testGuardsAreSkippedAtEveryDepth(): void
    {
        mkdir($this->dir . '/2026', 0o755, true);
        file_put_contents($this->dir . '/2026/web.config', "<configuration/>\n");
        file_put_contents($this->dir . '/2026/.htaccess', "Deny from all\n");

        $this->assertFalse(CwmprotectedStorage::containsMedia($this->dir));
    }

    #[TestDox('Empty subdirectories are not mistaken for stored media')]
    public function testEmptyDirectoriesAreNotMedia(): void
    {
        mkdir($this->dir . '/2026/summer', 0o755, true);

        // The walk yields directories as well as files, so that the scan bound
        // reflects the traversal. isFile() is what keeps structure from reading
        // as content.
        $this->assertFalse(CwmprotectedStorage::containsMedia($this->dir));
    }

    #[TestDox('A directory that does not exist holds nothing')]
    public function testMissingDirectory(): void
    {
        $this->assertFalse(CwmprotectedStorage::containsMedia($this->dir . '/nope'));
    }

    #[TestDox('A scan cut short reports the folder as occupied, never as empty')]
    public function testScanLimitFailsTowardsReporting(): void
    {
        $limit = (new \ReflectionClass(CwmprotectedStorage::class))->getConstant('SCAN_LIMIT');

        $this->assertIsInt($limit, 'SCAN_LIMIT is gone; this test no longer checks the bound.');

        // Enough empty directories to exhaust the bound before any file is
        // reached. "Empty" is the reassuring answer and the one that silences
        // every warning, so it must not be what an unfinished scan produces.
        for ($i = 0; $i <= $limit; $i++) {
            mkdir($this->dir . '/d' . $i, 0o755, true);
        }

        $this->assertTrue(
            CwmprotectedStorage::containsMedia($this->dir),
            'A scan that gave up reported the folder as empty, which is what suppresses the warning.'
        );
    }
}
