<?php

/**
 * Integration tests for Cwmthumbnail's write-before-delete ordering and
 * path allow-listing.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1570.
 *
 * 1. create() deleted the superseded image/thumbnail *before* writing the
 *    replacement. $versionHash is a content hash, so re-uploading a different
 *    photo for the same record produced a different filename -- the live file
 *    was therefore not in $keepFiles and was deleted first. A failed copy or
 *    encode then left the record pointing at files that no longer existed,
 *    because the calling models still call parent::save() after the generic
 *    warning.
 * 2/3. Image::toFile() returns imagejpeg()/imagewebp()'s bool rather than
 *    throwing; none of the four call sites checked it, and resize() returned
 *    true unconditionally. A failed encode was reported as success and the
 *    path was written to the database.
 * 4. resize() had no ALLOWED_PATHS check, unlike its sibling deleteFolder(),
 *    while its only caller passes `image_path` straight from request input.
 *
 * Also fixed here: deleteFolder()'s own allow-list used
 * str_starts_with() + Path::clean(), and Path::clean() does not collapse
 * '..' -- the same defect fixed in CwmImageMigration (#1537) and
 * CwmImageCleanup (#1563). Reachable only via a crafted stored path rather
 * than directly from a request, but the same class.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmthumbnail::class)]
class CwmthumbnailSafetyTest extends IntegrationTestCase
{
    private string $studiesDir;

    private array $createdDirs = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->studiesDir = JPATH_ROOT . '/images/biblestudy/studies';

        foreach ([JPATH_ROOT . '/images', JPATH_ROOT . '/images/biblestudy', $this->studiesDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
                $this->createdDirs[] = $dir;
            }
        }
    }

    protected function tearDown(): void
    {
        // Both the sources and the thumb_-prefixed variants the helper writes.
        foreach (['/cwm1570*', '/thumb_cwm1570*'] as $pattern) {
            foreach (glob($this->studiesDir . $pattern) ?: [] as $leftover) {
                @unlink($leftover);
            }
        }

        foreach (array_reverse($this->createdDirs) as $dir) {
            @rmdir($dir);
        }

        parent::tearDown();
    }

    /**
     * Write a small real JPEG so GD can actually load and resize it.
     *
     * @param   string  $path  Destination
     *
     * @return  void
     */
    private function writeJpeg(string $path): void
    {
        $im = imagecreatetruecolor(40, 30);
        imagefilledrectangle($im, 0, 0, 40, 30, imagecolorallocate($im, 20, 120, 200));
        imagejpeg($im, $path);
        imagedestroy($im);
    }

    // -------------------------------------------------------------------------
    // Finding 4 (+ deleteFolder) -- path allow-listing
    // -------------------------------------------------------------------------

    /**
     * resize() must refuse anything outside the image directories. Its caller
     * takes the path from request input, so this is the boundary.
     *
     * @param   string  $path  Attacker-shaped path
     */
    #[DataProvider('outOfScopePathProvider')]
    #[TestDox('resize() refuses a path outside the allowed image directories')]
    public function testResizeRefusesOutOfScopePaths(string $path): void
    {
        $this->assertFalse(
            Cwmthumbnail::resize($path, 100),
            'resize() deletes every thumb_* sibling and writes files — it must be scoped — see #1570'
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function outOfScopePathProvider(): array
    {
        return [
            'traversal out of the image tree' => ['images/biblestudy/studies/../../../configuration.php'],
            'unrelated images subdirectory'   => ['images/headers/banner.jpg'],
            'absolute outside root'           => ['/etc/hosts'],
            'joomla administrator dir'        => ['administrator/index.php'],
        ];
    }

    /**
     * deleteFolder() previously used str_starts_with() + Path::clean(), and
     * Path::clean() leaves '..' intact — so a path that merely *started* with
     * an allowed prefix could still resolve elsewhere.
     */
    #[TestDox('deleteFolder() refuses a traversal that starts with an allowed prefix')]
    public function testDeleteFolderRefusesTraversalBehindAnAllowedPrefix(): void
    {
        $this->assertFalse(
            Cwmthumbnail::deleteFolder('images/biblestudy/studies/../../../administrator'),
            'The prefix test must not be satisfiable by walking back out — see #1570, same class as #1537/#1563'
        );
    }

    #[TestDox('deleteFolder() still accepts a legitimate in-scope folder')]
    public function testDeleteFolderStillAcceptsInScopePaths(): void
    {
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/studies/cwm1570-nonexistent'),
            'An in-scope folder that does not exist is a no-op success, not a scope rejection'
        );
    }

    // -------------------------------------------------------------------------
    // Findings 2 & 3 -- resize() reports truthfully and keeps the old thumbnail
    // -------------------------------------------------------------------------

    #[TestDox('resize() produces a thumbnail and reports success for a real image')]
    public function testResizeSucceedsForARealImage(): void
    {
        $source = $this->studiesDir . '/cwm1570-source.jpg';
        $this->writeJpeg($source);

        $this->assertTrue(Cwmthumbnail::resize($source, 20));
        $this->assertFileExists($this->studiesDir . '/cwm1570-source.jpg', 'The source must survive');

        // Extension comes from image_type_to_extension(), which yields 'jpeg'
        // rather than 'jpg' -- glob rather than hardcode it.
        $this->assertNotEmpty(
            glob($this->studiesDir . '/thumb_cwm1570-source.*'),
            'A successful resize must actually leave a thumbnail behind'
        );
    }

    /**
     * The ordering guarantee: an unusable source must not cost the existing
     * thumbnail. Pre-fix, old thumbnails were deleted before the encode.
     */
    #[TestDox('A failed resize leaves the previous thumbnail intact')]
    public function testFailedResizeKeepsThePreviousThumbnail(): void
    {
        $source = $this->studiesDir . '/cwm1570-broken.jpg';
        $thumb  = $this->studiesDir . '/thumb_cwm1570-broken.jpg';

        // A file GD cannot decode, plus an existing thumbnail beside it.
        file_put_contents($source, 'this is not a JPEG');
        $this->writeJpeg($thumb);

        try {
            Cwmthumbnail::resize($source, 20);
        } catch (\Throwable) {
            // Joomla's Image may raise on an undecodable source; the ordering
            // guarantee is what matters here, not how the failure surfaces.
        }

        $this->assertFileExists(
            $thumb,
            'The previous thumbnail must survive a failed encode — deleting first left the record with none — see #1570'
        );
    }

    // -------------------------------------------------------------------------
    // Structural pins for the write-before-delete ordering in create()
    // -------------------------------------------------------------------------

    /**
     * create() is hard to drive end-to-end here (it needs model-shaped inputs
     * and a writable destination tree), so the ordering contract is pinned
     * structurally: the superseded files must be collected, and deleted only
     * after the thumbnail write is confirmed.
     */
    #[TestDox('create() defers deletion of superseded files until after the writes')]
    public function testCreateDeletesSupersededFilesOnlyAfterWriting(): void
    {
        $ref   = new \ReflectionMethod(Cwmthumbnail::class, 'create');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            '$supersededFiles[]',
            $body,
            'Old files must be collected rather than deleted inline — see #1570'
        );

        $collectAt = strpos($body, '$supersededFiles[]');
        $writeAt   = strpos($body, 'toFile($thumbPath');
        $deleteAt  = strpos($body, 'foreach ($supersededFiles');

        $this->assertNotFalse($writeAt, 'Expected the thumbnail write to still be present');
        $this->assertNotFalse($deleteAt, 'Expected a deferred deletion loop');

        $this->assertLessThan($writeAt, $collectAt, 'Collection happens while the old names are still known');
        $this->assertGreaterThan(
            $writeAt,
            $deleteAt,
            'Deletion must happen after the replacement is confirmed on disk — see #1570'
        );
    }

    /**
     * Every Image::toFile() call must have its return value considered; the
     * bug was that none of them did.
     */
    #[TestDox('Image::toFile() return values are checked, not discarded')]
    public function testToFileReturnValuesAreChecked(): void
    {
        $source = file_get_contents((new \ReflectionClass(Cwmthumbnail::class))->getFileName());

        preg_match_all('/^\s*\$\w+->toFile\(/m', $source, $bare);

        $this->assertSame(
            [],
            $bare[0],
            'A bare ->toFile() call discards the write result — a failed encode then reports success — see #1570'
        );
    }
}
