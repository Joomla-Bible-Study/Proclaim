<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmImageCleanup;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression tests for #1563:
 *
 * - deleteOrphans() validated the requested path with a plain str_starts_with()
 *   against the raw, un-cleaned path -- the same bug class as #1537 fixed in
 *   CwmImageMigration. Path::clean() never collapses '../', so a path like
 *   'images/biblestudy/studies/../../<anything>' passed scope validation while
 *   resolving completely outside it. Fixed with the same realpath()-based
 *   authoritative check CwmImageMigration already uses.
 * - extractIdFromFolderName() matched any string ending in "-<digits>" with no
 *   constraint on the rest of the name, so a stray non-app directory could be
 *   misclassified as an orphaned record folder.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmImageCleanupTest extends ProclaimTestCase
{
    private string $biblestudyDir;

    private bool $createdBiblestudyDir = false;

    private string $traversalTargetDir;

    private string $legitTargetDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->biblestudyDir = JPATH_ROOT . '/images/biblestudy';

        if (!is_dir($this->biblestudyDir)) {
            mkdir($this->biblestudyDir, 0777, true);
            $this->createdBiblestudyDir = true;
        }

        $this->traversalTargetDir = JPATH_ROOT . '/cwm_test_traversal_target_' . uniqid();
        mkdir($this->traversalTargetDir, 0777, true);
        file_put_contents($this->traversalTargetDir . '/secret.jpg', 'not really an image');

        $this->legitTargetDir = $this->biblestudyDir . '/studies';
        mkdir($this->legitTargetDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->traversalTargetDir)) {
            @unlink($this->traversalTargetDir . '/secret.jpg');
            @rmdir($this->traversalTargetDir);
        }

        if (is_dir($this->legitTargetDir)) {
            $this->rmrf($this->legitTargetDir);
        }

        if ($this->createdBiblestudyDir && is_dir($this->biblestudyDir)) {
            @rmdir($this->biblestudyDir);

            $imagesDir = \dirname($this->biblestudyDir);

            if (is_dir($imagesDir) && \count(scandir($imagesDir)) === 2) {
                @rmdir($imagesDir);
            }
        }

        parent::tearDown();
    }

    private function rmrf(string $dir): void
    {
        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . '/' . $entry;

            if (is_dir($path)) {
                $this->rmrf($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    public function testDeleteOrphansRejectsPathTraversalEscapingAllowedBase(): void
    {
        $payload = 'images/biblestudy/studies/../../../' . basename($this->traversalTargetDir);

        $result = CwmImageCleanup::deleteOrphans([$payload]);

        $this->assertSame(0, $result['deleted'], 'must not delete anything reached via ../ traversal -- see #1563');
        $this->assertFileExists(
            $this->traversalTargetDir . '/secret.jpg',
            'the file outside the allowed base must survive the traversal attempt -- see #1563'
        );
        $this->assertNotEmpty($result['errors']);
    }

    public function testDeleteOrphansStillDeletesWithinAllowedBase(): void
    {
        $legitFolder = $this->legitTargetDir . '/cwm-test-orphan-999999';
        mkdir($legitFolder, 0777, true);

        $result = CwmImageCleanup::deleteOrphans(['images/biblestudy/studies/cwm-test-orphan-999999']);

        $this->assertSame(1, $result['deleted'], 'a genuinely in-scope, non-orphaned-in-DB folder must still be cleaned up');
        $this->assertDirectoryDoesNotExist($legitFolder);
    }

    #[DataProvider('folderNameProvider')]
    public function testExtractIdFromFolderName(string $folderName, ?int $expected): void
    {
        $method = new \ReflectionMethod(CwmImageCleanup::class, 'extractIdFromFolderName');

        $this->assertSame($expected, $method->invoke(null, $folderName));
    }

    public static function folderNameProvider(): array
    {
        return [
            'plain alias-id'                                       => ['sunday-service-42', 42],
            'bare numeric id'                                      => ['123', 123],
            'non-alias characters rejected'                        => ['weird!name-5', null],
            'space in name rejected'                               => ['my folder 5', null],
            'uppercase rejected (not a real alias-generated name)' => ['Some-Folder-7', null],
        ];
    }

    public function testLooksLikeAppManagedFolderTreatsEmptyFolderAsSafe(): void
    {
        $dir = $this->legitTargetDir . '/cwm-test-empty-1';
        mkdir($dir, 0777, true);

        $method = new \ReflectionMethod(CwmImageCleanup::class, 'looksLikeAppManagedFolder');

        $this->assertTrue($method->invoke(null, $dir));
    }

    public function testLooksLikeAppManagedFolderAcceptsFolderContainingAnImage(): void
    {
        $dir = $this->legitTargetDir . '/cwm-test-image-1';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/photo.jpg', 'not really an image');

        $method = new \ReflectionMethod(CwmImageCleanup::class, 'looksLikeAppManagedFolder');

        $this->assertTrue($method->invoke(null, $dir));
    }

    public function testLooksLikeAppManagedFolderRejectsFolderWithNoImages(): void
    {
        // Regression case from the issue: a stray directory like
        // "export-2026-08-04" satisfies the alias-ID regex just as well as a
        // real record folder does -- what actually excludes it is that its
        // contents aren't images.
        $dir = $this->legitTargetDir . '/export-2026-08-04';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/data.csv', 'id,name');

        $method = new \ReflectionMethod(CwmImageCleanup::class, 'looksLikeAppManagedFolder');

        $this->assertFalse($method->invoke(null, $dir));
    }
}
